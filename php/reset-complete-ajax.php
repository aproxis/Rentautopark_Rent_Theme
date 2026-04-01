<?php
/**
 * AJAX endpoint: validate + save new password + autologin.
 * Path: templates/rent/php/reset-complete-ajax.php
 *
 * Accepts: POST JSON { password1: string, password2: string }
 * Returns: JSON { ok: true, redirect: string }
 *       or JSON { ok: false, error: string, error_code: string }
 *
 * Why no client-side autologin:
 *   After this endpoint modifies session state (setUserState clears reset keys),
 *   J5 saves + may regenerate the session token. The browser's CSRF meta-tag
 *   value becomes stale, so a follow-up JS fetch(task=user.login) gets
 *   JINVALID_TOKEN → queued flash message → shown on the next page load.
 *   Solution: $app->login() here, same request/session cycle, then JS just
 *   does window.location.href = res.redirect.
 */

set_exception_handler(function (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'error_code' => 'EXCEPTION']);
    exit;
});

ob_start();

// ── Bootstrap Joomla ──────────────────────────────────────────────────────
$joomlaBase = realpath(dirname(__FILE__) . '/../../../');
if (!$joomlaBase || !file_exists($joomlaBase . '/includes/defines.php')) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Joomla root not found.', 'error_code' => 'BOOT_FAIL']);
    exit;
}

define('_JEXEC', 1);
define('JPATH_BASE', $joomlaBase);
require_once $joomlaBase . '/includes/defines.php';
require_once $joomlaBase . '/includes/framework.php';

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\Session\SessionInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Uri\Uri;

$container = Factory::getContainer();
$container->alias(SessionInterface::class, 'session.web.site');
$app = $container->get(SiteApplication::class);
Factory::$application = $app;

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

// ── Helpers ───────────────────────────────────────────────────────────────
function sendJson(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function sendError(string $msg, string $code = 'GENERIC', int $status = 200): void
{
    http_response_code($status);
    sendJson(['ok' => false, 'error' => $msg, 'error_code' => $code]);
}

// ── Method guard ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed.', 'METHOD', 405);
}

// ── Parse body ────────────────────────────────────────────────────────────
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    sendError('Invalid JSON.', 'BAD_REQUEST', 400);
}

$password1 = (string) ($input['password1'] ?? '');
$password2 = (string) ($input['password2'] ?? '');

// ── Quick pre-checks ──────────────────────────────────────────────────────
if ($password1 === '') {
    sendError(Text::_('JGLOBAL_PASSWORD') . ' is required.', 'EMPTY_PASSWORD');
}
if ($password1 !== $password2) {
    sendError(Text::_('COM_USERS_PROFILES_PASSMATCH_ERROR'), 'PASSWORD_MISMATCH');
}

// ── Session state — written by processResetConfirm() ─────────────────────
$userId = (int) $app->getUserState('com_users.reset.user', 0);
if ($userId <= 0) {
    sendError(
        Text::_('COM_USERS_RESET_STEP_ERROR') ?: 'Session expired. Please request a new reset link.',
        'SESSION_EXPIRED'
    );
}

// ── Validate against com_users complexity rules ───────────────────────────
// Mirrors UserPasswordRule exactly — same Text::plural() strings as J5.
$params   = ComponentHelper::getParams('com_users');
$minLen   = max(1, (int) $params->get('minimum_length',   12));
$minInts  = (int) $params->get('minimum_integers',  0);
$minSyms  = (int) $params->get('minimum_symbols',   0);
$minUpper = (int) $params->get('minimum_uppercase', 0);
$minLower = (int) $params->get('minimum_lowercase', 0);

if (strlen($password1) < $minLen) {
    sendError(Text::plural('JLIB_USER_ERROR_MINIMUM_LENGTH_N',    $minLen),  'RULE_LENGTH');
}
if ($minInts > 0 && preg_match_all('/[0-9]/', $password1) < $minInts) {
    sendError(Text::plural('JLIB_USER_ERROR_MINIMUM_INTEGERS_N',  $minInts), 'RULE_INTEGERS');
}
if ($minSyms > 0 && preg_match_all('/[^a-zA-Z0-9]/', $password1) < $minSyms) {
    sendError(Text::plural('JLIB_USER_ERROR_MINIMUM_SYMBOLS_N',   $minSyms), 'RULE_SYMBOLS');
}
if ($minUpper > 0 && preg_match_all('/[A-Z]/', $password1) < $minUpper) {
    sendError(Text::plural('JLIB_USER_ERROR_MINIMUM_UPPERCASE_N', $minUpper), 'RULE_UPPERCASE');
}
if ($minLower > 0 && preg_match_all('/[a-z]/', $password1) < $minLower) {
    sendError(Text::plural('JLIB_USER_ERROR_MINIMUM_LOWERCASE_N', $minLower), 'RULE_LOWERCASE');
}

// ── Load user ─────────────────────────────────────────────────────────────
try {
    $user = Factory::getUser($userId);
    if (!$user || $user->id <= 0) {
        sendError('User not found.', 'USER_NOT_FOUND', 404);
    }
} catch (\Exception $e) {
    sendError('Could not load user: ' . $e->getMessage(), 'USER_LOAD_ERROR', 500);
}

$username  = $user->username;
$userEmail = $user->email;

// ── Hash + save new password ──────────────────────────────────────────────
// Direct DB update — no bootComponent/MVCFactory needed.
$hashedPw = UserHelper::hashPassword($password1);
$db       = Factory::getDbo();

try {
    $db->setQuery(
        $db->getQuery(true)
            ->update($db->quoteName('#__users'))
            ->set($db->quoteName('password')     . ' = ' . $db->quote($hashedPw))
            ->set($db->quoteName('activation')   . ' = ' . $db->quote(''))
            ->set($db->quoteName('requireReset') . ' = 0')
            ->where($db->quoteName('id')         . ' = ' . (int) $userId)
    );
    $db->execute();
} catch (\RuntimeException $e) {
    sendError('Could not save password: ' . $e->getMessage(), 'DB_ERROR', 500);
}

// ── Invalidate all other sessions for this user ───────────────────────────
// Same step as J5 processResetComplete — prevents stale authenticated sessions.
// We do this BEFORE login so the new session created by login is kept.
try {
    $db->setQuery(
        $db->getQuery(true)
            ->delete($db->quoteName('#__session'))
            ->where($db->quoteName('userid') . ' = ' . (int) $userId)
    );
    $db->execute();
} catch (\RuntimeException $e) { /* non-fatal */ }

// ── Clear reset session state ─────────────────────────────────────────────
$app->setUserState('com_users.reset.token', null);
$app->setUserState('com_users.reset.user',  null);

// ── Server-side autologin ─────────────────────────────────────────────────
// Login here (same request/session cycle) so the browser receives the updated
// session cookie in THIS response's Set-Cookie header. JS then just redirects.
// No second client-side POST → no stale CSRF token issue.
$loginOk = false;
try {
    $loginOk = $app->login(
        ['username' => $username, 'password' => $password1],
        ['action'   => 'core.login.site', 'silent' => true]
    );
} catch (\Exception $e) {
    // Login failed (e.g. account blocked, plugin error) — not fatal.
    // User will land on homepage and can log in manually.
    $loginOk = false;
}

// ── Build redirect URL ────────────────────────────────────────────────────
// On login success → homepage.
// On login failure → homepage with #open-login-modal anchor so the template
//   can auto-open the login modal for the user to sign in manually.
$siteUrl  = Uri::base();
$redirect = $loginOk
    ? $siteUrl
    : $siteUrl . '#open-login-modal';

sendJson(['ok' => true, 'redirect' => $redirect, 'loggedIn' => (bool) $loginOk]);