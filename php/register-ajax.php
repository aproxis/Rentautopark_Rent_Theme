<?php
/**
 * AJAX endpoint — Register a new Joomla user and log them in.
 * Called from the booking modal (oconfirm override) before submitting the order.
 * Compatible with Joomla 5 (uses namespaced classes, no JFactory, no initialise()).
 *
 * POST JSON: { reg_name, reg_email, reg_password }
 * Response:  { "ok": true, "userid": 123 }
 *         or { "error": "message" }
 */

// Buffer ALL output from the Joomla bootstrap so nothing leaks before our JSON headers
ob_start();

// ── Bootstrap Joomla 5 ───────────────────────────────────────────────────────
define('_JEXEC', 1);

$jbase = dirname(dirname(dirname(dirname(__FILE__))));
if (!file_exists($jbase . '/includes/defines.php')) {
	ob_end_clean();
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['error' => 'Joomla root not found.']);
	exit;
}

define('JPATH_BASE', $jbase);
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

try {
	// Joomla 5: use fully-qualified Factory, no $app->initialise()
	$app = \Joomla\CMS\Factory::getApplication('site');
} catch (Throwable $e) {
	ob_end_clean();
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['error' => 'Joomla bootstrap failed: ' . $e->getMessage()]);
	exit;
}

// Discard any output Joomla produced during bootstrap
ob_end_clean();

// ── JSON headers ──────────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Parse input ───────────────────────────────────────────────────────────────
$raw   = file_get_contents('php://input');
$input = $raw ? json_decode($raw, true) : null;
if (!is_array($input)) {
	$input = $_POST;
}

$name     = isset($input['reg_name'])     ? trim($input['reg_name'])     : '';
$email    = isset($input['reg_email'])    ? trim($input['reg_email'])    : '';
$password = isset($input['reg_password']) ? trim($input['reg_password']) : '';

// ── Validate ──────────────────────────────────────────────────────────────────
if (!$name || !$email || !$password) {
	echo json_encode(['error' => 'Toate câmpurile sunt obligatorii.']);
	exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	echo json_encode(['error' => 'Adresa de email nu este validă.']);
	exit;
}
if (strlen($password) < 6) {
	echo json_encode(['error' => 'Parola trebuie să aibă cel puțin 6 caractere.']);
	exit;
}

// ── Check for duplicate email ─────────────────────────────────────────────────
try {
	$db = \Joomla\CMS\Factory::getDbo();
	$db->setQuery(
		'SELECT id FROM ' . $db->quoteName('#__users')
		. ' WHERE ' . $db->quoteName('email') . ' = ' . $db->quote($email) . ' LIMIT 1'
	);
	$existing = $db->loadResult();
	if ($existing) {
		echo json_encode(['error' => 'Această adresă de email este deja înregistrată. Vă rugăm să vă autentificați.']);
		exit;
	}
} catch (Throwable $e) {
	echo json_encode(['error' => 'Eroare la verificarea email-ului: ' . $e->getMessage()]);
	exit;
}

// ── Build a unique username from email ────────────────────────────────────────
$baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '', explode('@', $email)[0]));
if (strlen($baseUsername) < 3) {
	$baseUsername = 'user' . $baseUsername;
}
$username = $baseUsername;
$suffix   = 1;
try {
	while (true) {
		$db->setQuery(
			'SELECT id FROM ' . $db->quoteName('#__users')
			. ' WHERE ' . $db->quoteName('username') . ' = ' . $db->quote($username) . ' LIMIT 1'
		);
		if (!$db->loadResult()) {
			break;
		}
		$username = $baseUsername . $suffix;
		$suffix++;
		if ($suffix > 999) {
			break;
		}
	}
} catch (Throwable $e) {
	$username = $baseUsername . rand(100, 999);
}

// ── Create the Joomla user ────────────────────────────────────────────────────
try {
	// Joomla 5: \Joomla\CMS\Component\ComponentHelper instead of JComponentHelper
	$params      = \Joomla\CMS\Component\ComponentHelper::getParams('com_users');
	$newUsertype = $params->get('new_usertype', 2);

	// Joomla 5: \Joomla\CMS\User\User instead of JUser
	$userObj  = new \Joomla\CMS\User\User();
	$userdata = [
		'name'      => $name,
		'username'  => $username,
		'password'  => $password,
		'password2' => $password,
		'email'     => $email,
		'email1'    => $email,
		'groups'    => [$newUsertype],
		'block'     => 0,
		'sendEmail' => 0,
	];

	if (!$userObj->bind($userdata)) {
		echo json_encode(['error' => 'Eroare la crearea contului: ' . $userObj->getError()]);
		exit;
	}
	if (!$userObj->save()) {
		echo json_encode(['error' => 'Eroare la salvarea contului: ' . $userObj->getError()]);
		exit;
	}

	// ── Log the user in ───────────────────────────────────────────────────────
	$credentials = ['username' => $username, 'password' => $password];
	$result = $app->login($credentials, ['action' => 'core.login.site']);

	if ($result === false || $result instanceof \Exception || $result instanceof \Throwable) {
		echo json_encode(['error' => 'Contul a fost creat dar autentificarea a eșuat. Vă rugăm să vă autentificați manual.']);
		exit;
	}

	echo json_encode(['ok' => true, 'userid' => (int)$userObj->id]);

} catch (Throwable $e) {
	echo json_encode(['error' => 'Eroare neașteptată: ' . $e->getMessage()]);
}
exit;
