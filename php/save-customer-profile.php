<?php
/**
 * /templates/rent/php/save-customer-profile.php
 * AJAX endpoint: save VikRentCar customer profile fields
 * (first_name, last_name, phone) for the currently logged-in user.
 *
 * Expects JSON body: { first_name, last_name, phone }
 * Returns JSON:     { ok: true/false, error: "..." }
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Last-resort JSON error output
set_exception_handler(function (\Throwable $e): void {
    if (ob_get_level()) { ob_end_clean(); }
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

ob_start();

// ── Joomla 4/5 bootstrap ──────────────────────────────────────────────────
$joomlaBase = realpath(dirname(__FILE__) . '/../../../');
if (!$joomlaBase || !file_exists($joomlaBase . '/includes/defines.php')) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Joomla root not found.']);
    exit;
}

define('_JEXEC', 1);
define('JPATH_BASE', $joomlaBase);

require_once $joomlaBase . '/includes/defines.php';
require_once $joomlaBase . '/includes/framework.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Application\SiteApplication;
use Joomla\Session\SessionInterface;

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

function sendError(string $message): void
{
    sendJson(['ok' => false, 'error' => $message]);
}

// ── POST only ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed.');
}

// ── Parse JSON body ───────────────────────────────────────────────────────
$rawBody = file_get_contents('php://input');
$input   = json_decode($rawBody, true);

if (!is_array($input)) {
    sendError('Invalid JSON body.');
}

$firstName = trim($input['first_name'] ?? '');
$lastName  = trim($input['last_name'] ?? '');
$phone     = trim($input['phone'] ?? '');

// ── Validate ──────────────────────────────────────────────────────────────
if ($firstName === '') {
    sendError('First name is required.');
}
if ($lastName === '') {
    sendError('Last name is required.');
}
if ($phone === '') {
    sendError('Phone is required.');
}

// ── Check logged-in user ──────────────────────────────────────────────────
$user = Factory::getUser();
if ($user->guest || $user->id <= 0) {
    sendError('You must be logged in to save your profile.');
}

$userId  = (int) $user->id;
$email   = $user->email;
$db      = Factory::getDbo();

// ── Look for existing VikRentCar customer record ──────────────────────────
$q = $db->getQuery(true)
    ->select($db->quoteName('id'))
    ->from($db->quoteName('#__vikrentcar_customers'))
    ->where($db->quoteName('ujid') . ' = ' . $userId);
$db->setQuery($q);
$existingId = (int) $db->loadResult();

try {
    if ($existingId > 0) {
        // UPDATE existing record
        $q = $db->getQuery(true)
            ->update($db->quoteName('#__vikrentcar_customers'))
            ->set($db->quoteName('first_name') . ' = ' . $db->quote($firstName))
            ->set($db->quoteName('last_name') . ' = ' . $db->quote($lastName))
            ->set($db->quoteName('phone') . ' = ' . $db->quote($phone))
            ->set($db->quoteName('email') . ' = ' . $db->quote($email))
            ->where($db->quoteName('id') . ' = ' . $existingId);
        $db->setQuery($q);
        $db->execute();
    } else {
        // INSERT new customer record
        $q = $db->getQuery(true)
            ->insert($db->quoteName('#__vikrentcar_customers'))
            ->columns($db->quoteName(['first_name', 'last_name', 'email', 'phone', 'ujid']))
            ->values(implode(',', [
                $db->quote($firstName),
                $db->quote($lastName),
                $db->quote($email),
                $db->quote($phone),
                $userId,
            ]));
        $db->setQuery($q);
        $db->execute();
    }
} catch (\Throwable $e) {
    sendError('Database error: ' . $e->getMessage());
}

// ── Success ───────────────────────────────────────────────────────────────
sendJson([
    'ok'         => true,
    'first_name' => $firstName,
    'last_name'  => $lastName,
    'phone'      => $phone,
]);
