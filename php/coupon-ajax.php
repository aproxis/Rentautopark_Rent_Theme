<?php
/**
 * coupon-ajax.php
 * Validates a VikRentCar coupon code server-side and returns discount info as JSON.
 * Uses raw PDO (no Joomla bootstrap) — works on Joomla 4/5.
 *
 * POST params:
 *   couponcode  — the code to validate
 *   carid       — car ID (int)
 *   days        — number of rental days (int), used for min-days check
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

// ── Load DB credentials from Joomla configuration.php ────────────────────────
$_cfg_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/configuration.php';
if (!file_exists($_cfg_path)) {
	echo json_encode(['valid' => false, 'error' => 'Server configuration error (cfg not found).']);
	exit;
}
// configuration.php defines class JConfig — safe to include standalone
require_once $_cfg_path;
$_cfg    = new JConfig();
$_prefix = $_cfg->dbprefix; // e.g. "mb1ii_"

try {
	$_pdo = new PDO(
		'mysql:host=' . $_cfg->host . ';dbname=' . $_cfg->db . ';charset=utf8mb4',
		$_cfg->user,
		$_cfg->password,
		[
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]
	);
} catch (Exception $_e) {
	echo json_encode(['valid' => false, 'error' => 'Database connection failed.']);
	exit;
}

// ── Input ─────────────────────────────────────────────────────────────────────
$code  = trim((string)(isset($_POST['couponcode']) ? $_POST['couponcode'] : ''));
$carid = (int)(isset($_POST['carid'])  ? $_POST['carid']  : 0);
$days  = (int)(isset($_POST['days'])   ? $_POST['days']   : 0);

if (empty($code)) {
	echo json_encode(['valid' => false, 'error' => 'Introduceți un cod promoțional.']);
	exit;
}

try {
	$_stmt = $_pdo->prepare(
		"SELECT * FROM `{$_prefix}vikrentcar_coupons`"
		. " WHERE `code` = ? AND `published` = 1 LIMIT 1"
	);
	$_stmt->execute([$code]);
	$coupon = $_stmt->fetch();

	if (empty($coupon)) {
		echo json_encode(['valid' => false, 'error' => 'Cod promoțional invalid.']);
		exit;
	}

	// ── Car restriction ──────────────────────────────────────────────────────
	if (!empty($coupon['idcars'])) {
		$_rawIds  = str_replace(';', ',', (string)$coupon['idcars']);
		$_allowed = array_filter(array_map('intval', explode(',', $_rawIds)));
		if (!empty($_allowed) && $carid > 0 && !in_array($carid, $_allowed)) {
			echo json_encode(['valid' => false, 'error' => 'Codul nu este valabil pentru această mașină.']);
			exit;
		}
	}

	// ── Date validity ────────────────────────────────────────────────────────
	$now = time();
	if (!empty($coupon['validfrom']) && (int)$coupon['validfrom'] > 0 && $now < (int)$coupon['validfrom']) {
		echo json_encode(['valid' => false, 'error' => 'Codul nu este încă activ.']);
		exit;
	}
	if (!empty($coupon['validto']) && (int)$coupon['validto'] > 0 && $now > (int)$coupon['validto']) {
		echo json_encode(['valid' => false, 'error' => 'Codul promoțional a expirat.']);
		exit;
	}

	// ── Min rental days ──────────────────────────────────────────────────────
	$_minDays = isset($coupon['minrentaldays']) ? (int)$coupon['minrentaldays'] : 0;
	if ($_minDays > 0 && $days > 0 && $days < $_minDays) {
		echo json_encode([
			'valid' => false,
			'error' => 'Sunt necesare minimum ' . $_minDays . ' zile de închiriere pentru acest cod.',
		]);
		exit;
	}

	// ── Build response ───────────────────────────────────────────────────────
	$type  = (int)$coupon['type'];   // 1 = percent, 2 = fixed amount
	$value = (float)$coupon['value'];
	$label = $type === 1 ? '-' . (int)round($value) . '%' : '-€' . (int)round($value);

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
