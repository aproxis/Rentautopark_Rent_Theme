<?php
/**
 * AJAX endpoint: validate + save new password.
 * Path: templates/rent/php/reset-complete-ajax.php
 *
 * Accepts: POST JSON { password1: string, password2: string }
 * Returns: JSON { ok: true, email: string }
 *       or JSON { ok: false, error: string, error_code: string }
 *
 * Does NOT use bootComponent() / getMVCFactory() — that resolves to the
 * Administrator UsersComponent class which is not on the autoloader path
 * when bootstrapped as SiteApplication from a standalone file.
 *
 * Instead we replicate exactly what processResetComplete() does:
 *   1. Read session state set by processResetConfirm()
 *   2. Validate password against com_users params (same rules as UserPasswordRule)
 *   3. UserHelper::hashPassword() + direct DB UPDATE
 *   4. DELETE #__session rows for that user (same as core model)
 *   5. Clear com_users.reset.* session keys
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

// ── Quick mismatch pre-check ──────────────────────────────────────────────
if ($password1 === '') {
    sendError(Text::_('JGLOBAL_PASSWORD') . ' is required.', 'EMPTY_PASSWORD');
}
if ($password1 !== $password2) {
    sendError(Text::_('COM_USERS_PROFILES_PASSMATCH_ERROR'), 'PASSWORD_MISMATCH');
}

// ── Session state — set by processResetConfirm() ─────────────────────────
$userId = (int) $app->getUserState('com_users.reset.user', 0);
if ($userId <= 0) {
    sendError(
        Text::_('COM_USERS_RESET_STEP_ERROR') ?: 'Session expired. Please start the password reset again.',
        'SESSION_EXPIRED'
    );
}

// ── Validate password against com_users complexity rules ─────────────────
// Mirrors J5's UserPasswordRule exactly — same Text::plural() strings.
$params    = ComponentHelper::getParams('com_users');
$minLen    = max(1, (int) $params->get('minimum_length',   12));
$minInts   = (int) $params->get('minimum_integers',  0);
$minSyms   = (int) $params->get('minimum_symbols',   0);
$minUpper  = (int) $params->get('minimum_uppercase', 0);
$minLower  = (int) $params->get('minimum_lowercase', 0);

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
    sendError(Text::plural('JLIB_USER_ERROR_MINIMUM_UPPERCASE_N', $minUpper),'RULE_UPPERCASE');
}
if ($minLower > 0 && preg_match_all('/[a-z]/', $password1) < $minLower) {
    sendError(Text::plural('JLIB_USER_ERROR_MINIMUM_LOWERCASE_N', $minLower),'RULE_LOWERCASE');
}

// ── Get user email before we clear the session ────────────────────────────
$userEmail = '';
try {
    $u = Factory::getUser($userId);
    if ($u && $u->id > 0) {
        $userEmail = $u->email;
    }
} catch (\Exception $e) { /* non-fatal */ }

// ── Hash password + UPDATE #__users ──────────────────────────────────────
// Exactly what processResetComplete() does: hash → store → clear activation.
$hashedPw = UserHelper::hashPassword($password1);

$db = Factory::getDbo();

try {
    $q = $db->getQuery(true)
        ->update($db->quoteName('#__users'))
        ->set($db->quoteName('password')         . ' = ' . $db->quote($hashedPw))
        ->set($db->quoteName('activation')       . ' = ' . $db->quote(''))
        ->set($db->quoteName('requireReset')     . ' = 0')
        ->where($db->quoteName('id')             . ' = ' . (int) $userId);
    $db->setQuery($q);
    $db->execute();
} catch (\RuntimeException $e) {
    sendError('Could not save password: ' . $e->getMessage(), 'DB_ERROR', 500);
}

// ── Invalidate all existing sessions for this user (security) ────────────
// Same step as J5 processResetComplete() — prevents old sessions staying valid.
try {
    $sq = $db->getQuery(true)
        ->delete($db->quoteName('#__session'))
        ->where($db->quoteName('userid') . ' = ' . (int) $userId);
    $db->setQuery($sq);
    $db->execute();
} catch (\RuntimeException $e) { /* non-fatal */ }

// ── Clear reset session keys ──────────────────────────────────────────────
$app->setUserState('com_users.reset.token', null);
$app->setUserState('com_users.reset.user',  null);

// ── Done ──────────────────────────────────────────────────────────────────
sendJson(['ok' => true, 'email' => $userEmail]);