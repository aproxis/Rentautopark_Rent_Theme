<?php
/**
 * /templates/rent/php/register-ajax.php
 *
 * Phase 3 — Zero-friction Joomla user registration during checkout.
 */

// ── Output buffering — catches any stray PHP notices before our JSON header ──
ob_start();

define('_JEXEC', 1);

// Script lives at templates/rent/php/ — root is 3 levels up
$joomlaBase = realpath(dirname(__FILE__) . '/../../../');

if (!$joomlaBase || !file_exists($joomlaBase . '/includes/defines.php')) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Joomla root not found.', 'error_code' => 'BOOT_FAIL']);
    exit;
}

require_once $joomlaBase . '/includes/defines.php';
require_once $joomlaBase . '/includes/framework.php';

// Bootstrap — wrap in try/catch so any fatal becomes a clean JSON 500
try {
    $mainframe = JFactory::getApplication('site');
    $mainframe->initialise();
} catch (Throwable $bootErr) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'         => false,
        'error'      => 'Bootstrap failed: ' . $bootErr->getMessage(),
        'error_code' => 'BOOT_FAIL',
    ]);
    exit;
}

// Discard bootstrap noise; set JSON header
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

// ── DB: duplicate email check ─────────────────────────────────────────────
try {
    $db    = JFactory::getDbo();
    $query = $db->getQuery(true)
        ->select($db->quoteName('id'))
        ->from($db->quoteName('#__users'))
        ->where($db->quoteName('email') . ' = ' . $db->quote($regEmail));
    $db->setQuery($query);
    $existingId = $db->loadResult();
} catch (Throwable $e) {
    sendError('Database error: ' . $e->getMessage(), 'DB_ERROR', 500);
}

if ($existingId) {
    sendJson(['ok' => false, 'error' => 'An account with this email already exists.', 'error_code' => 'EMAIL_EXISTS']);
}

// ── Secure random password ────────────────────────────────────────────────
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
    $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9._\-]/', '', $regUsernameHint));
    $baseUsername = substr($baseUsername, 0, 60);
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
// Use "new JUser()" NOT "JFactory::getUser()" — the latter returns the
// current guest session object; binding to it corrupts the active session.
$user = new JUser();

$userData = [
    'name'       => $regName,
    'username'   => $username,
    'password'   => $password,
    'password2'  => $password,
    'email'      => $regEmail,
    'email2'     => $regEmail,
    'groups'     => [2],
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
    $newUser = JFactory::getUser($newUserId);
    $newUser->password_clear = $password;
    JUserHelper::sendMail($newUser, $mainframe, $regEmail, false);
} catch (Throwable $e) {
    JLog::add('register-ajax: email failed for ' . $regEmail . ': ' . $e->getMessage(), JLog::WARNING, 'jerror');
}

// ── Auto-login (non-fatal) ────────────────────────────────────────────────
try {
    $mainframe->login(
        ['username' => $username, 'password' => $password],
        ['remember' => false, 'silent' => true]
    );
} catch (Throwable $e) {
    JLog::add('register-ajax: auto-login failed for ' . $username . ': ' . $e->getMessage(), JLog::WARNING, 'jerror');
}

// ── Return success ────────────────────────────────────────────────────────
sendJson([
    'ok'         => true,
    'user_id'    => $newUserId,
    'username'   => $username,
    'first_name' => $firstName,
    'last_name'  => $lastName,
]);