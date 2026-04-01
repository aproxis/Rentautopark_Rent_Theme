<?php
/**
 * templates/rent/php/reset-complete-ajax.php
 *
 * Accepts: POST JSON { password1, password2 }
 * Returns: JSON { ok: true } | { ok: false, error, error_code }
 *
 * IMPORTANT: This file MUST NOT write to the Joomla session.
 * Any call to $app->setUserState() or $app->enqueueMessage() causes
 * J5's session token to regenerate, which queues JINVALID_TOKEN on the
 * next real Joomla page — the CSRF warning the user sees on the homepage.
 *
 * All success/error feedback is handled client-side in complete.php.
 */

set_exception_handler(function (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'error_code' => 'EXCEPTION']);
    exit;
});

ob_start();

// ── Bootstrap Joomla (read-only session use) ──────────────────────────────
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

// ── Basic checks ──────────────────────────────────────────────────────────
if ($password1 === '') {
    sendError(Text::_('JGLOBAL_PASSWORD') . ' is required.', 'EMPTY_PASSWORD');
}
if ($password1 !== $password2) {
    sendError(Text::_('COM_USERS_PROFILES_PASSMATCH_ERROR'), 'PASSWORD_MISMATCH');
}

// ── Read session (READ-ONLY — no writes) ──────────────────────────────────
// getUserState() only reads from session; it does not write.
$userId = (int) $app->getUserState('com_users.reset.user', 0);
if ($userId <= 0) {
    sendError(
        Text::_('COM_USERS_RESET_STEP_ERROR') ?: 'Session expired. Please restart the password reset.',
        'SESSION_EXPIRED'
    );
}

// ── Validate complexity against com_users params ──────────────────────────
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

// ── Save hashed password ──────────────────────────────────────────────────
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

// ── Invalidate other sessions for this user (security) ───────────────────
try {
    $db->setQuery(
        $db->getQuery(true)
            ->delete($db->quoteName('#__session'))
            ->where($db->quoteName('userid') . ' = ' . (int) $userId)
    );
    $db->execute();
} catch (\RuntimeException $e) { /* non-fatal */ }

// ── Return success — NO session writes ───────────────────────────────────
// Do NOT call $app->setUserState() or $app->enqueueMessage() here.
// Those calls write to the Joomla session, causing J5 to regenerate its
// internal form token. The next real Joomla request then finds a token
// mismatch and queues JINVALID_TOKEN — the CSRF warning on the homepage.
//
// The activation field is now '' so the token cannot be reused.
// com_users.reset.user will expire naturally with the session.
// Success feedback is shown client-side in complete.php.
sendJson(['ok' => true]);