<?php
/**
 * /templates/rent/php/whoami.php
 * Lightweight session check — returns whether the current visitor is logged in.
 * Called by default.php Phase 3 JS after an AJAX login attempt to confirm success.
 */

ini_set('display_errors', 0);
error_reporting(0);

ob_start();

$joomlaBase = realpath(dirname(__FILE__) . '/../../../');
if (!$joomlaBase || !file_exists($joomlaBase . '/includes/defines.php')) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['logged_in' => false, 'error' => 'boot_fail']);
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

// Prevent any caching — this must always reflect the live session state
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$user = Factory::getUser();

if ($user->guest) {
    echo json_encode(['logged_in' => false]);
} else {
    $nameParts = preg_split('/\s+/', trim($user->name), 2);
    echo json_encode([
        'logged_in' => true,
        'user_id'   => (int) $user->id,
        'username'  => $user->username,
        'name'      => $user->name,
        'email'     => $user->email,
        'first_name'=> $nameParts[0] ?? '',
        'last_name' => $nameParts[1] ?? '',
    ]);
}