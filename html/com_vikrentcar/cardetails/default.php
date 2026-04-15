<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/cardetails/default.php
 * AutoRent Figma Design — v3 REDESIGN
 * Changes:
 *  - NEW INLINE CALENDAR with date range selection
 *  - Real time price summary & tier discount notifications
 *  - Grace period progress bar
 *  - Modern UI with dark mode support
 *  - Full mobile optimization
 *  - Original PHP logic 100% preserved
 *  - All language variables unchanged
 *  - Git version switchable between original / v3
 */

defined('_JEXEC') OR die('Restricted Area');
use Joomla\CMS\Language\Text;

$car        = $this->car;
$car_params = $this->car_params;
$busy       = $this->busy;
$vrc_tn     = $this->vrc_tn;

$vrc_app = VikRentCar::getVrcApplication();

$config = null;
if (class_exists('VRCFactory')) {
	$config = VRCFactory::getConfig();
}

if (method_exists('Joomla\CMS\Language\Text', 'script')) {
	Text::script('VRC_LOC_WILL_OPEN_TIME');
	Text::script('VRC_LOC_WILL_CLOSE_TIME');
	Text::script('VRC_PICKLOC_IS_ON_BREAK_TIME_FROM_TO');
	Text::script('VRC_DROPLOC_IS_ON_BREAK_TIME_FROM_TO');
}

$document = JFactory::getDocument();
if (VikRentCar::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
}
$document->addStyleSheet(VRC_SITE_URI . 'resources/jquery.fancybox.css');
$document->addStyleSheet(JURI::root() . 'templates/rent/css/cardetails.css');
JHtml::_('script', VRC_SITE_URI . 'resources/jquery.fancybox.js');
$document->addScript(JURI::root() . 'templates/rent/js/booking-modal.js');

// Grace period inline styles (extends .cd-info-notice pattern)
$document->addStyleDeclaration('
.cd-grace-notice {
    background: rgba(16, 185, 129, 0.08) !important;
    border-left: 3px solid #10b981 !important;
}
.cd-grace-notice .cd-notice-icon { color: #10b981; }
.cd-grace-returnby {
    display: block;
    font-size: 12px;
    color: #047857;
    margin-top: 3px;
}
.cd-grace-returnby strong { font-weight: 700; }
.cd-grace-exceeded {
    display: none;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 14px;
    margin: 0 0 4px;
    background: rgba(245, 158, 11, 0.08);
    border-left: 3px solid #f59e0b;
    border-radius: 4px;
    font-size: 13px;
    color: #92400e;
}
.cd-grace-exceeded.is-visible { display: flex; }
.cd-grace-exceeded .cd-grace-exc-icon { flex-shrink: 0; color: #f59e0b; margin-top: 1px; }
.cd-grace-exceeded .cd-grace-exc-body { display: flex; flex-direction: column; gap: 3px; }
.cd-grace-exceeded .cd-grace-exc-label { font-weight: 600; }
.cd-grace-exceeded .cd-grace-exc-hint { font-size: 12px; color: #b45309; }
');

$navdecl = '
jQuery.noConflict();
jQuery(document).ready(function() {
	jQuery(".vrcmodal[data-fancybox=\"gallery\"]").fancybox({
		"helpers": {"overlay": {"locked": false}},
		"padding": 0
	});
	jQuery(".vrcmodalframe").fancybox({
		"helpers": {"overlay": {"locked": false}},
		"width": "75%",
		"height": "75%",
		"autoScale": false,
		"transitionIn": "none",
		"transitionOut": "none",
		"padding": 0,
		"type": "iframe"
	});
});';
$document->addScriptDeclaration($navdecl);

$currencysymb   = VikRentCar::getCurrencySymb();
$showpartlyres  = VikRentCar::showPartlyReserved();
$numcalendars   = VikRentCar::numCalendars();
$carats         = VikRentCar::getCarCaratOriz($car['idcarat'], array(), $vrc_tn);

// ── Conditional Deposit Notice ─────────────────────────────────────────
$showDeposit    = false;
$depositAmount  = 0;
$depositCurrency = $currencysymb;

try {
    $_dbo = JFactory::getDbo();
    $_dbo->setQuery(
        "SELECT `charge`, `val_pcent` 
         FROM `#__vikrentcar_gpayments`
         WHERE `file` = 'maibpayment' AND `published` = '1'
         LIMIT 1"
    );
    $maibPayment = $_dbo->loadAssoc();

    if (!empty($maibPayment) && floatval($maibPayment['charge']) > 0) {
        $depositAmount = floatval($maibPayment['charge']);
        
        // If percentage-based (val_pcent = 2), calculate from car price
        if ($maibPayment['val_pcent'] == 2 && !empty($car['cost']) && $car['cost'] > 0) {
            $depositAmount = ($car['cost'] * $depositAmount) / 100;
        }
        
        $depositAmount = round($depositAmount);
        
        if ($depositAmount > 0) {
            $showDeposit = true;
        }
    }
} catch (Exception $e) {
    $showDeposit = false;
}
// ────────────────────────────────────────────────────────────────────────

$pitemid        = VikRequest::getInt('Itemid', '', 'request');
$vrcdateformat  = VikRentCar::getDateFormat();
$nowtf          = VikRentCar::getTimeFormat();

if ($vrcdateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vrcdateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$nowts = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

// ── Gratuity / Grace period detection ──────────────────────────────────────
// hoursmorerentback = number of grace hours after the rental end time
// (can be > 24; e.g. 28 = 1 day + 4 hours extra allowed return without charge)
$graceHours = 0;
if (method_exists('VikRentCar', 'getHoursMoreRb')) {
	$graceHours = (int)VikRentCar::getHoursMoreRb();
}
$hasGracePeriod = ($graceHours > 0);
// ────────────────────────────────────────────────────────────────────────────

$categoryName = VikRentCar::sayCategory($car['idcat'], $vrc_tn);

$mainImgSrc = '';
if (!empty($car['img'])) {
	$mainImgSrc = JURI::root() . 'administrator/components/com_vikrentcar/resources/' . $car['img'];
}
$moreImages = array();
if (!empty($car['moreimgs'])) {
	foreach (explode(';;', $car['moreimgs']) as $mimg) {
		$mimg = trim($mimg);
		if (!empty($mimg)) {
			$moreImages[] = array(
				'thumb' => JURI::root() . 'administrator/components/com_vikrentcar/resources/thumb_' . $mimg,
				'big'   => JURI::root() . 'administrator/components/com_vikrentcar/resources/big_' . $mimg,
			);
		}
	}
}

$showPrice = ($car['cost'] > 0);
$priceVal  = strlen($car['startfrom']) > 0 ? VikRentCar::numberFormat($car['startfrom']) : VikRentCar::numberFormat($car['cost']);

JPluginHelper::importPlugin('content');
$myItem = JTable::getInstance('content');
$myItem->text = $car['info'];
if (class_exists('JDispatcher')) {
	$dispatcher = JDispatcher::getInstance();
	$dispatcher->trigger('onContentPrepare', array('com_vikrentcar.cardetails', &$myItem, &$params, 0));
} else {
	$dispatcher = JFactory::getApplication();
	if (method_exists($dispatcher, 'triggerEvent')) {
		$dispatcher->triggerEvent('onContentPrepare', array('com_vikrentcar.cardetails', &$myItem, &$params, 0));
	}
}
$car['info'] = $myItem->text;

$caratDefs = array();
if (!empty($car['idcarat'])) {
	$caratIds = array_filter(explode(';', $car['idcarat']));
	if (!empty($caratIds)) {
		$dbo = JFactory::getDbo();
		try {
			if (method_exists('VikRentCar', 'loadCharacteristics')) {
				$caratDefs = VikRentCar::loadCharacteristics(array_map('intval', $caratIds), $vrc_tn);
			}
		} catch (Exception $e) {}
		if (empty($caratDefs)) {
			try {
				$ids = array_map('intval', $caratIds);
				$q = "SELECT `id`,`name`,`icon`,`textimg` FROM `#__vikrentcar_caratteristiche`"
				   . " WHERE `id` IN (" . implode(',', $ids) . ") ORDER BY `ordering` ASC;";
				$dbo->setQuery($q);
				$rows = $dbo->loadAssocList('id');
				if ($rows) {
					$vrc_tn->translateContents($rows, '#__vikrentcar_caratteristiche');
					$caratDefs = $rows;
				}
			} catch (Exception $e2) {}
		}
	}
}

// raw key snapshot — lookup original textimg/name BEFORE translation
$caratOrigKeys = array();
if (!empty($caratDefs)) {
	try {
		$dbo2 = JFactory::getDbo();
		$rawIds = array_map('intval', array_keys($caratDefs));
		$dbo2->setQuery(
			"SELECT `id`,`name`,`textimg` FROM `#__vikrentcar_caratteristiche`"
			. " WHERE `id` IN (" . implode(',', $rawIds) . ")"
		);
		$rawRows = $dbo2->loadAssocList('id');
		foreach ($rawRows as $rawId => $rawRow) {
			$caratOrigKeys[$rawId] = strtolower(
				!empty($rawRow['textimg']) ? $rawRow['textimg'] : $rawRow['name']
			);
		}
	} catch (Exception $e) {}
}

$push_disabled_in = array();
$push_disabled_out = array();
if (is_array($busy) && count($busy) > 0) {
	$now_info_pre = getdate();
	$pre_newarr = getdate(mktime(0, 0, 0, $now_info_pre['mon'], $now_info_pre['mday'], $now_info_pre['year']));
	$pre_max_ts = mktime(23, 59, 59, $pre_newarr['mon'], $pre_newarr['mday'], ($pre_newarr['year'] + 1));
	$pre_lastdropoff = 0;
	$pre_unitsadjuster = 0;
	while ($pre_newarr[0] < $pre_max_ts) {
		$totfound = 0;
		$ischeckinday = false;
		$ischeckoutday = false;
		$lastfoundritts = 0;
		$lastfoundconts = -1;
		$lasttotfound = 0;
		foreach ($busy as $b) {
			$info_in = getdate($b['ritiro']);
			$checkin_ts = mktime(0, 0, 0, $info_in['mon'], $info_in['mday'], $info_in['year']);
			$info_out = getdate($b['realback']);
			$checkout_ts = mktime(0, 0, 0, $info_out['mon'], $info_out['mday'], $info_out['year']);
			if ($pre_newarr[0] >= $checkin_ts && $pre_newarr[0] <= $checkout_ts) {
				$totfound++;
				if ($pre_newarr[0] == $checkin_ts) {
					$lastfoundritts = $checkin_ts;
					$lastfoundconts = $checkout_ts;
					if ($lastfoundritts != $lastfoundconts) $lasttotfound++;
					$ischeckinday = true;
				} elseif ($pre_newarr[0] == $checkout_ts) {
					$ischeckoutday = true;
					$pre_lastdropoff = $b['realback'];
				}
				if ($ischeckinday && !empty($pre_lastdropoff) && $pre_lastdropoff <= $b['ritiro']) {
					$pre_unitsadjuster++;
				}
				if ($b['stop_sales'] == 1) {
					$totfound = $car['units'];
					$pre_unitsadjuster = 0;
					break;
				}
			}
		}
		if ($totfound >= $car['units']) {
			if ($ischeckinday || !$ischeckoutday) {
				if ($lasttotfound > 1 || $lastfoundritts != $lastfoundconts) {
					if (($totfound - $pre_unitsadjuster) >= $car['units']) {
						$push_disabled_in[] = '"' . date('Y-m-d', $pre_newarr[0]) . '"';
					}
				}
			}
			if (!$ischeckinday && !$ischeckoutday) {
				$push_disabled_out[] = '"' . date('Y-m-d', $pre_newarr[0]) . '"';
			}
		}
		$pre_newarr = getdate(mktime(0, 0, 0, $pre_newarr['mon'], ($pre_newarr['mday'] + 1), $pre_newarr['year']));
	}
}

$carslistUrl = JRoute::_('index.php?option=com_vikrentcar&view=carslist' . (!empty($pitemid) ? '&Itemid=' . $pitemid : ''));

// ── Price tiers ─────────────────────────────────────────────────────────────
$priceTiers    = array();
$minRentalDays = 1;
$_rateByDay    = array();

$_fixedRanges = array(
	array('from' => 1,  'to' => 3),
	array('from' => 4,  'to' => 7),
	array('from' => 8,  'to' => 14),
	array('from' => 15, 'to' => 21),
	array('from' => 22, 'to' => 29),
	array('from' => 30, 'to' => 60),
);

try {
	$_dbo = JFactory::getDbo();
	$_dbo->setQuery(
		"SELECT `days`, `cost`, `idprice`"
		. " FROM `#__vikrentcar_dispcost`"
		. " WHERE `idcar` = " . (int)$car['id']
		. " AND `cost` > 0"
		. " ORDER BY `idprice` ASC, `days` ASC"
	);
	$_rows = $_dbo->loadAssocList();

	if (!empty($_rows)) {
		$_firstIdprice = (int)$_rows[