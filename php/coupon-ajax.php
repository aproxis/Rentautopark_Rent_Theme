<?php
/**
 * coupon-ajax.php
 * Validates a VikRentCar coupon code server-side and returns discount info as JSON.
 *
 * POST params:
 *   couponcode  — the code to validate
 *   carid       — car ID (int)
 *   days        — number of rental days (int), used for min-days check
 */
define('_JEXEC', 1);

// templates/rent/php/ → templates/rent/ → templates/ → [Joomla root]
$_jpath = dirname(dirname(dirname(dirname(__FILE__))));
if (!file_exists($_jpath . '/includes/defines.php')) {
	header('Content-Type: application/json');
	echo json_encode(['valid' => false, 'error' => 'Server configuration error.']);
	exit;
}
define('JPATH_BASE', $_jpath);
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Suppress Joomla redirect on AJAX
JFactory::getApplication('site');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$code  = trim((string)(isset($_POST['couponcode']) ? $_POST['couponcode'] : ''));
$carid = (int)(isset($_POST['carid'])  ? $_POST['carid']  : 0);
$days  = (int)(isset($_POST['days'])   ? $_POST['days']   : 0);

if (empty($code)) {
	echo json_encode(['valid' => false, 'error' => 'Introduceți un cod promoțional.']);
	exit;
}

try {
	$dbo = JFactory::getDbo();

	$dbo->setQuery(
		"SELECT * FROM `#__vikrentcar_coupons`"
		. " WHERE `code` = " . $dbo->quote($code)
		. " AND `published` = 1"
		. " LIMIT 1"
	);
	$coupon = $dbo->loadAssoc();

	if (empty($coupon)) {
		echo json_encode(['valid' => false, 'error' => 'Cod promoțional invalid.']);
		exit;
	}

	// ── Car restriction ─────────────────────────────────────────────
	if (!empty($coupon['idcars'])) {
		$_rawIds = str_replace(';', ',', (string)$coupon['idcars']);
		$_allowed = array_filter(array_map('intval', explode(',', $_rawIds)));
		if (!empty($_allowed) && $carid > 0 && !in_array($carid, $_allowed)) {
			echo json_encode(['valid' => false, 'error' => 'Codul nu este valabil pentru această mașină.']);
			exit;
		}
	}

	// ── Date validity ────────────────────────────────────────────────
	$now = time();
	if (!empty($coupon['validfrom']) && (int)$coupon['validfrom'] > 0 && $now < (int)$coupon['validfrom']) {
		echo json_encode(['valid' => false, 'error' => 'Codul nu este încă activ.']);
		exit;
	}
	if (!empty($coupon['validto']) && (int)$coupon['validto'] > 0 && $now > (int)$coupon['validto']) {
		echo json_encode(['valid' => false, 'error' => 'Codul promoțional a expirat.']);
		exit;
	}

	// ── Min rental days ──────────────────────────────────────────────
	$_minDays = isset($coupon['minrentaldays']) ? (int)$coupon['minrentaldays'] : 0;
	if ($_minDays > 0 && $days > 0 && $days < $_minDays) {
		echo json_encode([
			'valid' => false,
			'error' => 'Sunt necesare minimum ' . $_minDays . ' zile de închiriere pentru acest cod.',
		]);
		exit;
	}

	// ── Build response ───────────────────────────────────────────────
	$type  = (int)$coupon['type'];    // 1 = percent, 2 = fixed amount
	$value = (float)$coupon['value'];

	if ($type === 1) {
		$label = '-' . (int)round($value) . '%';
	} else {
		$label = '-€' . (int)round($value);
	}

	echo json_encode([
		'valid' => true,
		'type'  => $type,
		'value' => $value,
		'label' => $label,
	]);

} catch (Exception $_e) {
	echo json_encode(['valid' => false, 'error' => 'Eroare de server. Încercați din nou.']);
}
exit;
