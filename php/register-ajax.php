<?php
/**
 * /templates/rent/php/register-ajax.php
 *
 * Phase 3 — Zero-friction Joomla user registration during checkout.
 *
 * Behaviour:
 *  - Receives { reg_name, reg_email } via POST/JSON (no password from client).
 *  - Generates a secure 16-character random password server-side.
 *  - Creates a Joomla user that is immediately active (block=0, no activation token).
 *  - Joomla's built-in new-user email is triggered, delivering the credentials to the user.
 *  - Logs the new user in for the current session so the booking is tied to the account.
 *  - Duplicate email → returns { ok: false, error_code: "EMAIL_EXISTS" }.
 *    The JS caller treats this as a soft error and proceeds with guest checkout.
 *  - All other errors → { ok: false, error: "...", error_code: "..." }.
 *
 * Prerequisites (Joomla admin):
 *  User Manager → Options → User Registration: Allowed
 *  User Manager → Options → New User Account Activation: None
 *  User Manager → Options → Send Password: Yes  (so Joomla includes it in the welcome email)
 *
 * CORS / origin:
 *  Called via same-origin AJAX from oconfirm/default.php — no additional CORS headers needed.
 */

// ── Bootstrap Joomla ──────────────────────────────────────────────────────
define('_JEXEC', 1);

// Locate Joomla's base path (script is 3 levels deep: templates/rent/php/)
$joomlaBase = realpath(dirname(__FILE__) . '/../../../');
if (!$joomlaBase || !file_exists($joomlaBase . '/includes/defines.php')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Joomla base not found.', 'error_code' => 'BOOT_FAIL']);
    exit;
}

require_once $joomlaBase . '/includes/defines.php';
require_once $joomlaBase . '/includes/framework.php';

$mainframe = JFactory::getApplication('site');
$mainframe->initialise();

// ── Response helpers ──────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');

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

// ── Accept POST only ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed.', 'METHOD', 405);
}

// ── Parse input (JSON body) ───────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$input   = json_decode($rawBody, true);

if (!is_array($input)) {
    sendError('Invalid JSON body.', 'BAD_REQUEST', 400);
}

$regName        = trim($input['reg_name']     ?? '');
$regEmail       = trim($input['reg_email']    ?? '');
$regUsernameHint = trim($input['reg_username'] ?? '');  // optional client suggestion

// ── Validate ──────────────────────────────────────────────────────────────
if (empty($regName)) {
    sendError('Name is required.', 'MISSING_NAME');
}

if (empty($regEmail) || !filter_var($regEmail, FILTER_VALIDATE_EMAIL)) {
    sendError('A valid email address is required.', 'INVALID_EMAIL');
}

// ── Duplicate email check ─────────────────────────────────────────────────
$db    = JFactory::getDbo();
$query = $db->getQuery(true)
    ->select($db->quoteName('id'))
    ->from($db->quoteName('#__users'))
    ->where($db->quoteName('email') . ' = ' . $db->quote($regEmail));
$db->setQuery($query);
$existingId = $db->loadResult();

if ($existingId) {
    // Soft error — JS caller will proceed with guest checkout
    sendJson(['ok' => false, 'error' => 'An account with this email already exists.', 'error_code' => 'EMAIL_EXISTS']);
}

// ── Generate secure password ──────────────────────────────────────────────
/**
 * 16-character password mixing uppercase, lowercase, digits and safe symbols.
 * Uses random_int() (CSPRNG) — requires PHP 7+.
 * Includes at least one character from each character class to satisfy
 * common password-strength requirements.
 */
function generateSecurePassword(int $length = 16): string
{
    $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';   // no I/O (look-alike)
    $lower   = 'abcdefghjkmnpqrstuvwxyz';     // no i/l/o
    $digits  = '23456789';                    // no 0/1
    $symbols = '@#!%';

    $all      = $upper . $lower . $digits . $symbols;
    $password = '';

    // Guarantee one of each class
    $password .= $upper[random_int(0, strlen($upper) - 1)];
    $password .= $lower[random_int(0, strlen($lower) - 1)];
    $password .= $digits[random_int(0, strlen($digits) - 1)];
    $password .= $symbols[random_int(0, strlen($symbols) - 1)];

    // Fill the remainder
    for ($i = 4; $i < $length; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }

    // Shuffle so the guaranteed chars aren't always first
    return str_shuffle($password);
}

$password = generateSecurePassword(16);

// ── Build username from client hint → email local-part → fallback 'user' ────
if (!empty($regUsernameHint)) {
    // Sanitise: keep alphanumeric, dots, underscores, hyphens; max 60 chars
    $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9._\-]/', '', $regUsernameHint));
    $baseUsername = substr($baseUsername, 0, 60);
}
if (empty($baseUsername)) {
    $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9._\-]/', '', strstr($regEmail, '@', true)));
}
if (empty($baseUsername)) {
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
    if (!$db->loadResult()) {
        break;
    }
    $username = $baseUsername . $suffix;
    $suffix++;
}

// ── Build name (first + last from full name string) ───────────────────────
$nameParts = preg_split('/\s+/', $regName, 2);
$firstName = $nameParts[0];
$lastName  = isset($nameParts[1]) ? $nameParts[1] : '';

// ── Create Joomla user ────────────────────────────────────────────────────
$user = JFactory::getUser();  // blank user object for new user

$userData = [
    'name'      => $regName,
    'username'  => $username,
    'password'  => $password,
    'password2' => $password,   // confirmation
    'email'     => $regEmail,
    'email2'    => $regEmail,   // confirmation (some Joomla versions check this)
    'groups'    => [2],         // 2 = Registered group (default front-end group)
    'block'     => 0,           // immediately active
    'activation' => '',         // no activation token (requires "None" activation in J! config)
    'sendEmail'  => 0,          // we trigger the email ourselves below for more control
    'params'    => [],
];

if (!$user->bind($userData)) {
    sendError('Failed to bind user data: ' . $user->getError(), 'BIND_FAIL');
}

if (!$user->save()) {
    $errMsg = $user->getError();
    sendError('Failed to create account: ' . $errMsg, 'SAVE_FAIL');
}

$newUserId = (int) $user->id;

if ($newUserId <= 0) {
    sendError('User creation returned no ID.', 'NO_ID');
}

// ── Send welcome email with credentials ───────────────────────────────────
/**
 * We call JUserHelper to send the "new account" email.
 * This uses the Joomla mail template defined in:
 *   User Manager → Options → Mail → "New User Account" mail template
 *
 * If Joomla's built-in mail fails silently we still return success —
 * the booking should never be blocked by an email delivery issue.
 */
try {
    // Reload the fully-saved user so all fields are populated
    $newUser = JFactory::getUser($newUserId);

    // JUserHelper::sendMail expects the cleartext password for the welcome email.
    // We pass it via the user's `password_clear` property (Joomla internal convention).
    $newUser->password_clear = $password;

    JUserHelper::sendMail(
        $newUser,           // JUser object
        $mainframe,         // JApplication
        $regEmail,          // recipient
        false               // not a reminder (this is a new account)
    );
} catch (Exception $e) {
    // Email failure is non-fatal — log it and continue
    JFactory::getLogger()->warning('register-ajax: welcome email failed for ' . $regEmail . ': ' . $e->getMessage());
}

// ── Log the new user in for the current session ───────────────────────────
/**
 * This ties the subsequent booking form submission to the new account.
 * Requires the site's session cookie to persist across the iframe boundary.
 */
try {
    $options = [
        'remember'  => false,
        'silent'    => true,   // suppress login events if desired
    ];

    // Joomla 3 login API
    $credentials = [
        'username' => $username,
        'password' => $password,
    ];

    $mainframe->login($credentials, $options);
} catch (Exception $e) {
    // Non-fatal — the account was created; we just couldn't auto-log in
    JFactory::getLogger()->warning('register-ajax: auto-login failed for ' . $username . ': ' . $e->getMessage());
}

// ── Return success ────────────────────────────────────────────────────────
sendJson([
    'ok'         => true,
    'user_id'    => $newUserId,
    'username'   => $username,
    'first_name' => $firstName,
    'last_name'  => $lastName,
    // Do NOT return the password in the response — it was sent by email
]);