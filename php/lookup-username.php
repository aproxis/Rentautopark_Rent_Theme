<?php
set_exception_handler(function(\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'error_code' => 'EXCEPTION']);
    exit;
});

ob_start();

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
use Joomla\Session\SessionInterface;

$container = Factory::getContainer();
$container->alias(SessionInterface::class, 'session.web.site');
$app = $container->get(SiteApplication::class);
Factory::$application = $app;

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

function sendJson(array $data): void { echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
function sendError(string $msg, string $code = 'GENERIC', int $status = 200): void {
    http_response_code($status);
    sendJson(['ok' => false, 'error' => $msg, 'error_code' => $code]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed.', 'METHOD', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    sendError('Invalid JSON.', 'BAD_REQUEST', 400);
}

// Mirror Email plugin: lowercase both sides, use LIKE
$email = strtolower(trim($input['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError('A valid email address is required.', 'INVALID_EMAIL');
}

$db    = Factory::getDbo();
$query = $db->getQuery(true)
    ->select($db->quoteName('username'))
    ->from($db->quoteName('#__users'))
    ->where('LOWER(' . $db->quoteName('email') . ') LIKE :email')
    ->bind(':email', $email);
$db->setQuery($query);
$username = $db->loadResult();

if (!$username) {
    sendError('No account found for this email address.', 'NOT_FOUND');
}

sendJson(['ok' => true, 'username' => $username]);