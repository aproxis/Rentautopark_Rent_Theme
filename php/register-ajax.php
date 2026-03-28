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

// ── Create user ───────────────────────────────────────────────────────────
$user = new User();

$userData = [
    'name'       => $regName,
    'username'   => $username,
    'password'   => $password,
    'password2'  => $password,
    'email'      => $regEmail,
    'email2'     => $regEmail,
    'groups'     => [2],   // Registered
    'block'      => 0,
    'activation' => '',
    'sendEmail'  => 0,
    'params'     => [],
];

if (!$user->bind($userData)) {
    sendError('User bind failed: ' . $user->getError(), 'BIND_FAIL', 500);
}

if (!$user->save()) {
    sendError('User save failed: ' . $user->getError(), 'SAVE_FAIL', 500);
}

$newUserId = (int) $user->id;
if ($newUserId <= 0) {
    sendError('User created but no ID returned.', 'NO_ID', 500);
}

// ── Welcome email (non-fatal) ─────────────────────────────────────────────
try {
    $newUser = Factory::getUser($newUserId);
    $newUser->password_clear = $password;

    if (method_exists(UserHelper::class, 'sendMail')) {
        UserHelper::sendMail($newUser, $app, $regEmail, false);
    } else {
        $app->triggerEvent('onUserAfterSave', [$userData, true, true, null]);
    }
} catch (\Throwable $e) {
    // Email failure is never fatal
}

// ── Auto-login (non-fatal) ────────────────────────────────────────────────
try {
    $app->login(
        ['username' => $username, 'password' => $password],
        ['remember' => false]
    );
} catch (\Throwable $e) {
    // Non-fatal — session/cookie may not work in all contexts
}

// ── Success ───────────────────────────────────────────────────────────────
sendJson([
    'ok'         => true,
    'user_id'    => $newUserId,
    'username'   => $username,
    'first_name' => $firstName,
    'last_name'  => $lastName,
]);