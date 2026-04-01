<?php
/**
 * AJAX endpoint: validate and save new password via J5's own model.
 * Path: templates/rent/php/reset-complete-ajax.php
 *
 * Accepts: POST JSON { password1: string, password2: string }
 * Returns: JSON { ok: true, email: string }
 *       or JSON { ok: false, error: string, error_code: string }
 *
 * Uses J5's actual com_users ResetModel + form validation so all rules
 * (minimum_length, minimum_integers, etc.) and error messages come from
 * the same source as normal Joomla form submission.
 */

set_exception_handler(function (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'error_code' => 'EXCEPTION']);
    exit;
});

ob_start();

// ── Bootstrap Joomla ─────────────────────────────────────────────────────
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

use Joomla\CMS\Factory;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Language\Text;
use Joomla\Session\SessionInterface;

$container = Factory::getContainer();
$container->alias(SessionInterface::class, 'session.web.site');
$app = $container->get(SiteApplication::class);
Factory::$application = $app;

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

// ── Helpers ──────────────────────────────────────────────────────────────
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

// ── Method guard ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed.', 'METHOD', 405);
}

// ── Parse input ──────────────────────────────────────────────────────────
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    sendError('Invalid JSON.', 'BAD_REQUEST', 400);
}

$password1 = (string) ($input['password1'] ?? '');
$password2 = (string) ($input['password2'] ?? '');

// ── Quick pre-checks (cheap, before loading MVC) ─────────────────────────
if ($password1 === '') {
    sendError(Text::_('JGLOBAL_PASSWORD') . ' required.', 'EMPTY_PASSWORD');
}

if ($password1 !== $password2) {
    // Use J5's own translated string for mismatch
    sendError(Text::_('COM_USERS_PROFILES_PASSMATCH_ERROR'), 'PASSWORD_MISMATCH');
}

// ── Verify session state ─────────────────────────────────────────────────
// processResetConfirm() set com_users.reset.user after successful token verify.
// If it's empty, the session expired between step 2 and step 3.
$sessionUserId = (int) $app->getUserState('com_users.reset.user', 0);

if ($sessionUserId <= 0) {
    sendError(
        Text::_('COM_USERS_RESET_STEP_ERROR') ?: 'Session expired. Please start the password reset again.',
        'SESSION_EXPIRED'
    );
}

// ── Get user email BEFORE processResetComplete clears the session ─────────
// processResetComplete() sets com_users.reset.user = null on success,
// so we capture the email now.
$userEmail = '';
try {
    $u = Factory::getUser($sessionUserId);
    if ($u && $u->id > 0) {
        $userEmail = $u->email;
    }
} catch (\Exception $e) {
    // Non-fatal — autologin will fall back to homepage + modal trigger
}

// ── Load J5 com_users ResetModel and run processResetComplete ─────────────
// This uses J5's actual form XML (reset_complete.xml) and UserPasswordRule validator,
// so all complexity checks, translated error messages and password saving
// are 100% native — no reimplementation needed.
try {
    $mvcFactory = $app->bootComponent('com_users')->getMVCFactory();
    $model      = $mvcFactory->createModel('Reset', 'Site', ['ignore_request' => true]);
} catch (\Throwable $e) {
    sendError('Could not load password model: ' . $e->getMessage(), 'MODEL_BOOT_ERROR', 500);
}

$data   = ['password1' => $password1, 'password2' => $password2];
$result = $model->processResetComplete($data);

// ── Handle result ─────────────────────────────────────────────────────────
if ($result instanceof \Exception) {
    // Hard exception from processResetComplete (e.g. DB error, missing token)
    sendError($result->getMessage(), 'PROCESS_EXCEPTION', 500);
}

if ($result === false) {
    // Validation errors — collect translated messages from the model
    $msgs = [];
    foreach ($model->getErrors() as $modelError) {
        $msgs[] = is_object($modelError) ? $modelError->getMessage() : (string) $modelError;
    }
    $errorMsg = implode(' ', array_filter($msgs));
    sendError($errorMsg ?: Text::_('COM_USERS_RESET_COMPLETE_ERROR'), 'VALIDATION_ERROR');
}

// ── Success ───────────────────────────────────────────────────────────────
// processResetComplete: saved hashed password, cleared activation,
// destroyed other user sessions, cleared com_users.reset.* session keys.
// Return email so JS can autologin.
sendJson(['ok' => true, 'email' => $userEmail]);
