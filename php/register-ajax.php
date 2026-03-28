<?php
/**
 * /templates/rent/php/register-ajax.php
 * Joomla 4/5 compatible — uses the official standalone-script bootstrap pattern
 * from https://manual.joomla.org/docs/4.4/building-extensions/custom-script/basic-script/
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Last-resort JSON error output (before Joomla is loaded)
set_exception_handler(function (\Throwable $e): void {
    if (ob_get_level()) { ob_end_clean(); }
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'         => false,
        'error'      => $e->getMessage(),
        'error_code' => 'EXCEPTION',
        'file'       => basename($e->getFile()),
        'line'       => $e->getLine(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

ob_start();

// ── Joomla 4/5 bootstrap ──────────────────────────────────────────────────
// Script lives at: templates/rent/php/register-ajax.php
// Joomla root is 3 directories up
$joomlaBase = realpath(dirname(__FILE__) . '/../../../');

if (!$joomlaBase || !file_exists($joomlaBase . '/includes/defines.php')) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'         => false,
        'error'      => 'Joomla root not found at: ' . dirname(__FILE__) . '/../../..',
        'error_code' => 'BOOT_FAIL',
    ]);
    exit;
}

define('_JEXEC', 1);
define('JPATH_BASE', $joomlaBase);

require_once $joomlaBase . '/includes/defines.php';
require_once $joomlaBase . '/includes/framework.php';

// ── Official J4/5 standalone-script bootstrap pattern ─────────────────────
// Source: https://manual.joomla.org/docs/building-extensions/custom-script/basic-script/
//
// framework.php loads the service providers but does NOT resolve SiteApplication.
// SessionInterface is registered as 'session.web.site' — we must alias it before
// asking the container to build SiteApplication, otherwise the DI graph fails.
use Joomla\CMS\Factory;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Session\SessionInterface;

// 1. Get the fully-booted DI container (service providers already registered by framework.php)
$container = Factory::getContainer();

// 2. Register the missing alias that SiteApplication's constructor depends on
$container->alias(SessionInterface::class, 'session.web.site');

// 3. Now the full DI graph resolves cleanly
$app = $container->get(SiteApplication::class);

// 4. Make it available to all subsequent Factory::getApplication() calls
Factory::$application = $app;

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

// ── Helpers ───────────────────────────────────────────────────────────────
function sendJson(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function sendError(string $message, string $code = 'GENERIC', int $httpStatus = 200): void
{
    http_response_code($httpStatus);
    sendJson(['ok' => false, 'error' => $message, 'error_code' => $code]);
}

// ── POST only ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed.', 'METHOD', 405);
}

// ── Parse JSON body ───────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$input   = json_decode($rawBody, true);

if (!is_array($input)) {
    sendError('Invalid JSON body.', 'BAD_REQUEST', 400);
}

$regName         = trim($input['reg_name']     ?? '');
$regEmail        = trim($input['reg_email']    ?? '');
$regUsernameHint = trim($input['reg_username'] ?? '');

// ── Validate ──────────────────────────────────────────────────────────────
if ($regName === '') {
    sendError('Name is required.', 'MISSING_NAME');
}
if ($regEmail === '' || !filter_var($regEmail, FILTER_VALIDATE_EMAIL)) {
    sendError('A valid email address is required.', 'INVALID_EMAIL');
}

// ── Duplicate email check ─────────────────────────────────────────────────
$db    = Factory::getDbo();
$query = $db->getQuery(true)
    ->select($db->quoteName('id'))
    ->from($db->quoteName('#__users'))
    ->where($db->quoteName('email') . ' = ' . $db->quote($regEmail));
$db->setQuery($query);
$existingId = $db->loadResult();

if ($existingId) {
    sendJson(['ok' => false, 'error' => 'Email already registered.', 'error_code' => 'EMAIL_EXISTS']);
}

// ── Secure password ───────────────────────────────────────────────────────
function generateSecurePassword(int $length = 16): string
{
    $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $lower   = 'abcdefghjkmnpqrstuvwxyz';
    $digits  = '23456789';
    $symbols = '@#!%';
    $all     = $upper . $lower . $digits . $symbols;

    $pw  = $upper[random_int(0, strlen($upper) - 1)];
    $pw .= $lower[random_int(0, strlen($lower) - 1)];
    $pw .= $digits[random_int(0, strlen($digits) - 1)];
    $pw .= $symbols[random_int(0, strlen($symbols) - 1)];
    for ($i = 4; $i < $length; $i++) {
        $pw .= $all[random_int(0, strlen($all) - 1)];
    }
    return str_shuffle($pw);
}

$password = generateSecurePassword(16);

// ── Unique username ───────────────────────────────────────────────────────
$baseUsername = '';
if ($regUsernameHint !== '') {
    $baseUsername = substr(strtolower(preg_replace('/[^a-zA-Z0-9._\-]/', '', $regUsernameHint)), 0, 60);
}
if ($baseUsername === '') {
    $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9._\-]/', '', strstr($regEmail, '@', true)));
}
if ($baseUsername === '') {
    $baseUsername = 'user';
}

$username = $baseUsername;
$suffix   = 1;
while (true) {
    $q = $db->getQuery(true)
        ->select($db->quoteName('id'))
        ->from($db->quoteName('#__users'))
        ->where($db->quoteName('username') . ' = ' . $db->quote($username));
    $db->setQuery($q);
    if (!$db->loadResult()) { break; }
    $username = $baseUsername . $suffix++;
}

// ── Name parts ────────────────────────────────────────────────────────────
$nameParts = preg_split('/\s+/', $regName, 2);
$firstName = $nameParts[0];
$lastName  = $nameParts[1] ?? '';

// ── Register plugin PSR-4 autoloader paths & import user plugins ────────
// In a standalone script, Joomla's normal boot never calls JLoader::registerNamespace
// for plugins. We must do it manually so plg_user_joomla's Extension class is found.
$pluginBasePath = $joomlaBase . '/plugins/user';
if (is_dir($pluginBasePath)) {
    foreach (new \DirectoryIterator($pluginBasePath) as $pluginDir) {
        if ($pluginDir->isDot() || !$pluginDir->isDir()) { continue; }
        $srcPath   = $pluginDir->getPathname() . '/src';
        $pluginName = ucfirst($pluginDir->getFilename());
        if (is_dir($srcPath)) {
            \JLoader::registerNamespace(
                'Joomla\\Plugin\\User\\' . $pluginName . '\\',
                $srcPath,
                false,
                false,
                'psr4'
            );
        }
    }
}
\Joomla\CMS\Plugin\PluginHelper::importPlugin('user');

// ── Insert user directly via DB (avoids plugin autoloader issues) ─────────
// Using UserHelper::addUserToGroup + a raw INSERT is the safest approach in
// a standalone script: it skips onUserBeforeSave/onUserAfterSave events
// (which require fully-booted plugin autoloaders) while still producing a
// fully valid Joomla user record.
$passwordHash = UserHelper::hashPassword($password);
$now          = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

$insertQuery = $db->getQuery(true)
    ->insert($db->quoteName('#__users'))
    ->columns($db->quoteName([
        'name', 'username', 'email', 'password',
        'block', 'sendEmail', 'registerDate', 'lastvisitDate',
        'activation', 'params', 'lastResetTime', 'resetCount',
        'otpKey', 'otep', 'requireReset',
    ]))
    ->values(implode(',', [
        $db->quote($regName),
        $db->quote($username),
        $db->quote($regEmail),
        $db->quote($passwordHash),
        0, 0,
        $db->quote($now),
        $db->quote('0000-00-00 00:00:00'),
        $db->quote(''),
        $db->quote('{}'),
        $db->quote('0000-00-00 00:00:00'),
        0,
        $db->quote(''),
        $db->quote(''),
        0,
    ]));

$db->setQuery($insertQuery);
$db->execute();
$newUserId = (int) $db->insertid();

if ($newUserId <= 0) {
    sendError('User insert failed — no ID returned.', 'NO_ID', 500);
}

// Add to the "Registered" group (group ID 2)
UserHelper::addUserToGroup($newUserId, 2);

// ── Welcome email via Factory::getMailer() (J4/5 way) ────────────────────
$emailSent  = false;
$emailError = '';
try {
    $config   = Factory::getConfig();
    $siteName = $config->get('sitename', 'Our site');
    $fromEmail = $config->get('mailfrom');
    $fromName  = $config->get('fromname', $siteName);

    $subject = 'Your account on ' . $siteName;
    $body    = "Hello " . $regName . ",\n\n"
             . "Your account has been created.\n\n"
             . "Username : " . $username . "\n"
             . "Password : " . $password . "\n\n"
             . "Please log in and change your password as soon as possible.\n\n"
             . "Regards,\n" . $siteName;

    $mailer = Factory::getMailer();
    $mailer->setSender([$fromEmail, $fromName]);
    $mailer->addRecipient($regEmail, $regName);
    $mailer->setSubject($subject);
    $mailer->setBody($body);
    $result = $mailer->Send();

    if ($result === true) {
        $emailSent = true;
    } else {
        $emailError = is_string($result) ? $result : 'Mailer returned false';
    }
} catch (\Throwable $e) {
    $emailError = $e->getMessage();
    // Email failure is never fatal — user was already created successfully
}

// ── Auto-login via pending-login token ───────────────────────────────────
// $app->login() doesn't work reliably in a standalone script context because
// the session cookie has already been sent. Instead we store a one-time token
// in the DB and set a cookie. The plg_system_vrcuserjoin plugin reads it on
// the very next page load and performs the login inside the real Joomla cycle.
try {
    // Ensure the helper table exists
    $db->setQuery("CREATE TABLE IF NOT EXISTS `#__vrcuserjoin_pending_login` (
        `token`    VARCHAR(64)  NOT NULL PRIMARY KEY,
        `username` VARCHAR(150) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `created`  DATETIME     NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $db->execute();

    // Write the token row
    $loginToken = bin2hex(random_bytes(24)); // 48-char hex token
    $now        = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    $q = $db->getQuery(true)
        ->insert($db->quoteName('#__vrcuserjoin_pending_login'))
        ->columns($db->quoteName(['token', 'username', 'password', 'created']))
        ->values(implode(',', [
            $db->quote($loginToken),
            $db->quote($username),
            $db->quote($password),
            $db->quote($now),
        ]));
    $db->setQuery($q);
    $db->execute();

    // Set a cookie so the plugin can find this token on the next request.
    // HttpOnly=true so JS can't read it; SameSite=Lax is fine for same-origin.
    $cookiePath   = '/';
    $cookieDomain = '';
    $isSecure     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('vrc_pending_login', $loginToken, [
        'expires'  => time() + 300,   // 5 minutes — matches plugin TTL
        'path'     => $cookiePath,
        'domain'   => $cookieDomain,
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
} catch (\Throwable $e) {
    // Non-fatal — user was created, login deferred token just didn't save
}

// ── Success ───────────────────────────────────────────────────────────────
sendJson([
    'ok'          => true,
    'user_id'     => $newUserId,
    'username'    => $username,
    'first_name'  => $firstName,
    'last_name'   => $lastName,
    'email_sent'  => $emailSent,
    'email_error' => $emailError,
]);