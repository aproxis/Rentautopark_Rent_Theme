<?php
/**
 * coupon-ajax.php
 * Validates a VikRentCar coupon code and returns discount info as JSON.
 * Uses raw PDO — no Joomla bootstrap required.
 *
 * Real mb1ii_vikrentcar_coupons schema:
 *   id, code, type (1=reusable/2=single-use), percentot (1=%/2=fixed),
 *   value, datevalid ("fromTs-toTs" or ""), allvehicles (1=all/0=specific),
 *   idcars (";1;2;3;"), mintotord
 *
 * POST params:
 *   couponcode  — the code to validate
 *   carid       — car ID (int)
 *   total       — current order total €, used for mintotord check (optional)
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

// ── DB credentials from Joomla configuration.php ─────────────────────────────
$_cfg_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/configuration.php';
if (!file_exists($_cfg_path)) {
	echo json_encode(['valid' => false, 'error' => 'Server configuration error.']);
	exit;
}
require_once $_cfg_path;
$_cfg    = new JConfig();
$_prefix = $_cfg->dbprefix;

try {
	$_pdo = new PDO(
		'mysql:host=' . $_cfg->host . ';dbname=' . $_cfg->db . ';charset=utf8mb4',
		$_cfg->user,
		$_cfg->password,
		[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
	);
} catch (Exception $_e) {
	echo json_encode(['valid' => false, 'error' => 'Database connection failed.']);
	exit;
}

// ── Input ─────────────────────────────────────────────────────────────────────
$code  = trim((string)(isset($_POST['couponcode']) ? $_POST['couponcode'] : ''));
$carid = (int)(isset($_POST['carid'])  ? $_POST['carid']  : 0);
$total = (float)(isset($_POST['total']) ? $_POST['total'] : 0);

if (empty($code)) {
	echo json_encode(['valid' => false, 'error' => 'Introduceți un cod promoțional.']);
	exit;
}

try {
	// VikRentCar's own getCouponInfo() has NO published filter — just match by code
	$_stmt = $_pdo->prepare("SELECT * FROM `{$_prefix}vikrentcar_coupons` WHERE `code` = ? LIMIT 1");
	$_stmt->execute([$code]);
	$coupon = $_stmt->fetch();

	if (empty($coupon)) {
		echo json_encode(['valid' => false, 'error' => 'Cod promoțional invalid.']);
		exit;
	}

	// ── Date validity ────────────────────────────────────────────────────────
	// datevalid stores "fromUnixTs-toUnixTs" or empty string
	if (!empty($coupon['datevalid'])) {
		$_parts = explode('-', $coupon['datevalid']);
		if (count($_parts) === 2 && is_numeric($_parts[0]) && is_numeric($_parts[1])) {
			$_now = time();
			if ($_now < (int)$_parts[0] || $_now > (int)$_parts[1]) {
				echo json_encode(['valid' => false, 'error' => 'Codul promoțional nu este activ în această perioadă.']);
				exit;
			}
		}
	}

	// ── Car restriction ──────────────────────────────────────────────────────
	// allvehicles=1 → all cars; allvehicles=0 → only cars in idcars (";1;2;3;")
	if ((int)$coupon['allvehicles'] === 0 && $carid > 0) {
		if (!preg_match('/;' . (int)$carid . ';/i', (string)$coupon['idcars'])) {
			echo json_encode(['valid' => false, 'error' => 'Codul nu este valabil pentru această mașină.']);
			exit;
		}
	}

	// ── Minimum order total ───────────────────────────────────────────────────
	$_minTotal = (float)$coupon['mintotord'];
	if ($_minTotal > 0 && $total > 0 && $total < $_minTotal) {
		echo json_encode([
			'valid' => false,
			'error' => 'Suma minimă pentru acest cod este €' . (int)round($_minTotal) . '.',
		]);
		exit;
	}

	// ── Build response ───────────────────────────────────────────────────────
	// percentot: 1 = percentage discount, 2 = fixed amount discount
	// We return percentot as "type" so JS (type===1 → %, type===2 → fixed) matches
	$percentot = (int)$coupon['percentot'];
	$value     = (float)$coupon['value'];
	$label     = ($percentot === 1)
		? '-' . (int)round($value) . '%'
		: '-€' . (int)round($value);

	echo json_encode([
		'valid' => true,
		'type'  => $percentot,  // 1=percentage, 2=fixed (matches cdUpdateSummary JS)
		'value' => $value,
		'label' => $label,
	]);

} catch (Exception $_e) {
	echo json_encode(['valid' => false, 'error' => 'Eroare de server: ' . $_e->getMessage()]);
}
exit;
