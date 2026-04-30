<?php
/**
 * /templates/rent/php/get-customer-profile.php
 * AJAX endpoint: return VikRentCar customer profile data
 * for the currently logged-in user.
 *
 * Returns JSON: { exists: true/false, first_name, last_name, phone, email }
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

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

$user = Factory::getUser();
if ($user->guest || $user->id <= 0) {
    echo json_encode(['exists' => false]);
    exit;
}

$userId = (int) $user->id;
$db     = Factory::getDbo();

$q = $db->getQuery(true)
    ->select($db->quoteName(['first_name', 'last_name', 'phone', 'email']))
    ->from($db->quoteName('#__vikrentcar_customers'))
    ->where($db->quoteName('ujid') . ' = ' . $userId)
    ->setLimit(1);
$db->setQuery($q);
$row = $db->loadAssoc();

if ($row && !empty($row['first_name'])) {
    echo json_encode([
        'exists'     => true,
        'first_name' => $row['first_name'],
        'last_name'  => $row['last_name'],
        'phone'      => $row['phone'] ?? '',
        'email'      => $row['email'] ?? $user->email,
    ]);
} else {
    echo json_encode([
        'exists'     => false,
        'first_name' => '',
        'last_name'  => '',
        'phone'      => '',
        'email'      => $user->email,
    ]);
}
