<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/cardetails/default.php
 * AutoRent Figma Design — v3
 * Changes:
 *  - Mobile layout: Photos → Form → Specs → Description/Calendars
 *  - Time select: single hour select, minutes fixed at :00
 *  - Price strip + summary update on date select
 *  - OOH fee labels use VRCPVIEWOOHFEESTEN/ELEVEN/TWELVE with real times
 *  - Total uses VRTOTAL, fixed EUR symbol
 *  - No decimals in prices
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
$document->addStyleSheet(JURI::root() . 'templates/rent/css/cardetails-v3.css');
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
		$_firstIdprice = (int)$_rows[0]['idprice'];
		$_rows = array_values(array_filter($_rows, function($r) use ($_firstIdprice) {
			return (int)$r['idprice'] === $_firstIdprice;
		}));

		$minRentalDays = (int)$_rows[0]['days'];

		foreach ($_rows as $_row) {
			$_d = (int)$_row['days'];
			$_rateByDay[$_d] = round((float)$_row['cost'] / $_d, 2);
		}
		$_maxDay = max(array_keys($_rateByDay));

		foreach ($_fixedRanges as $_range) {
			$_from = $_range['from'];
			$_to   = $_range['to'];
			$_bestDay = null;
			for ($_d = min($_to, $_maxDay); $_d >= $_from; $_d--) {
				if (isset($_rateByDay[$_d])) {
					$_bestDay = $_d;
					break;
				}
			}
			if ($_bestDay === null) continue;
			$priceTiers[] = array(
				'from' => $_from,
				'to'   => $_to,
				'rate' => round($_rateByDay[$_bestDay]),  // integer, no decimals
			);
		}
	}
} catch (Exception $_e) {
	$priceTiers = array();
}

// ── OOH fees ────────────────────────────────────────────────────────────────
$oohFees = array();
try {
	$_dbo2 = JFactory::getDbo();
	$_dbo2->setQuery(
		"SELECT `oohname`,`pickcharge`,`dropcharge`,`maxcharge`,`from`,`to`,`type`,`idcars`"
		. " FROM `#__vikrentcar_oohfees`"
		. " ORDER BY `id` ASC"
	);
	$_oohRows = $_dbo2->loadAssocList();
	if (!empty($_oohRows)) {
		foreach ($_oohRows as $_ooh) {
			$_idcars = $_ooh['idcars'];
			if (!empty($_idcars) && strpos($_idcars, '-' . $car['id'] . '-') === false) {
				continue;
			}
			$_fromH = floor((int)$_ooh['from'] / 3600);
			$_fromM = floor(((int)$_ooh['from'] % 3600) / 60);
			$_toH   = floor((int)$_ooh['to'] / 3600);
			$_toM   = floor(((int)$_ooh['to'] % 3600) / 60);
			$oohFees[] = array(
				'name'        => $_ooh['oohname'],
				'from'        => (int)$_ooh['from'],
				'to'          => (int)$_ooh['to'],
				'fromLabel'   => sprintf('%02d:%02d', $_fromH, $_fromM),
				'toLabel'     => sprintf('%02d:%02d', $_toH, $_toM),
				'pickcharge'  => (float)$_ooh['pickcharge'],
				'dropcharge'  => (float)$_ooh['dropcharge'],
				'maxcharge'   => (float)$_ooh['maxcharge'],
				'type'        => (int)$_ooh['type'],
			);
		}
	}
} catch (Exception $_e2) {
	$oohFees = array();
}

// ── Optional extras ──────────────────────────────────────────────────────────
$carOptionals = array();
try {
	if (!empty($car['idopt'])) {
		$_opts = VikRentCar::getCarOptionals($car['idopt'], $vrc_tn);
		if (is_array($_opts)) {
			$carOptionals = $_opts;
		}
	}
} catch (Exception $_e3) {
	$carOptionals = array();
}
?>

<div class="cd-container">
<div id="vrc-bookingpart-init"></div>
<div class="cd-page-grid">

<!-- ═══════ LEFT COLUMN ═══════ -->
<div class="cd-left">

	<!-- Gallery wrapper (mobile order 1) -->
	<div class="cd-gallery-wrap">
		<div class="cd-gallery">
			<?php if (!empty($mainImgSrc) || !empty($moreImages)):
			$allImages = array();
			if (!empty($mainImgSrc)) { $allImages[] = array('thumb' => $mainImgSrc, 'big' => $mainImgSrc); }
			foreach ($moreImages as $mi) { $allImages[] = $mi; }
			$maxThumbs = min(count($allImages), 5);
			?>
			<div class="cd-thumbs" id="cd-thumbs">
				<?php for ($ti = 0; $ti < $maxThumbs; $ti++): $isLast = ($ti === 4 && count($allImages) > 5); ?>
				<div class="cd-thumb <?php echo $ti === 0 ? 'active' : ''; ?>" onclick="cdSetImage(<?php echo $ti; ?>)" data-idx="<?php echo $ti; ?>">
					<img src="<?php echo $allImages[$ti]['thumb']; ?>" alt="<?php echo htmlspecialchars($car['name']); ?> <?php echo ($ti+1); ?>" loading="lazy"/>
					<?php if ($isLast): ?><div class="cd-thumb-more">+<?php echo (count($allImages) - 5); ?></div><?php endif; ?>
				</div>
				<?php endfor; ?>
			</div>
			<div class="cd-main-img">
				<?php if (!empty($allImages)): ?>
				<a href="<?php echo $allImages[0]['big']; ?>" class="vrcmodal" data-fancybox="gallery" id="cd-main-link">
					<img src="<?php echo $allImages[0]['big']; ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" id="cd-main-img-el"/>
				</a>
				<?php endif; ?>
			</div>
			<?php if (count($allImages) > 1): for ($gi = 1; $gi < count($allImages); $gi++): ?>
			<a href="<?php echo $allImages[$gi]['big']; ?>" class="vrcmodal" data-fancybox="gallery" style="display:none;"></a>
			<?php endfor; endif; endif; ?>
		</div>
	</div><!-- /.cd-gallery-wrap -->

	<!-- Desktop: price strip (also shown on mobile via .cd-price-tiers-wrap inside booking card area) -->
	<?php if (!empty($priceTiers)): ?>
	<div class="cd-price-tiers-wrap" style="margin-top:12px;">
		<div class="cd-price-tiers" id="cd-price-tiers">
			<div class="cd-price-tiers-grid">
				<?php foreach ($priceTiers as $_ti => $tier):
					$_isLast = ($_ti === count($priceTiers) - 1);
					$_fromN = (int)$tier['from']; $_toN = (int)$tier['to'];
					$_dw = Text::_('VRCSEARCHDAYS') ?: 'дней';
					if ($_fromN === $_toN) { $_dl = $_fromN . ' ' . $_dw; }
					// elseif ($_isLast && $_toN >= 45) { $_dl = '> ' . ($_fromN - 1) . ' ' . $_dw; }
					else { $_dl = $_fromN . '–' . $_toN . ' ' . $_dw; }
				?>
				<div class="cd-price-tier" data-from="<?php echo $_fromN; ?>" data-to="<?php echo $_toN; ?>" data-idx="<?php echo $_ti; ?>">
					<span class="cd-price-tier-days"><?php echo $_dl; ?></span>
					<div class="cd-price-tier-value">
						<span class="cd-price-tier-cost"><?php echo $tier['rate']; ?></span>
						<span class="cd-price-tier-cur"> <?php echo $currencysymb; ?></span>
						<span class="cd-price-tier-per">/<?php echo Text::_('VRCSEARCHDAY') ?: 'день'; ?></span>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- Mobile car name: shown between price tiers and booking form on mobile -->
	<div class="cd-mobile-car-name">
		<h2 class="cd-car-name"><?php echo htmlspecialchars($car['name']); ?></h2>
	</div>

	<!-- Desktop meta: category, name, spec pills -->
	<div class="cd-desktop-meta">
		<!-- <h1 class="cd-car-name-desktop"><?php echo htmlspecialchars($car['name']); ?> — <?php echo Text::_('VRCTITLECARDESCR'); ?></h1> -->
		<?php
		$svgIcons = array(
			'automat'=>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path></svg>',
			'manual' =>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"></path><path d="M14 17H5"></path><circle cx="17" cy="17" r="3"></circle><circle cx="7" cy="7" r="3"></circle></svg>',
			'diesel' =>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>',
			'benzin' =>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="15" y1="22" y2="22"></line><line x1="4" x2="14" y1="9" y2="9"></line><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"></path><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"></path></svg>',
			'petrol' =>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="15" y1="22" y2="22"></line><line x1="4" x2="14" y1="9" y2="9"></line><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"></path><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"></path></svg>',
			'loc'    =>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
			'seat'   =>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
			'luggage'=>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 20a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2"></path><path d="M8 18V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v14"></path><path d="M10 20h4"></path><circle cx="16" cy="20" r="2"></circle><circle cx="8" cy="20" r="2"></circle></svg>',
			'door'   =>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="6" y="6" width="12" height="6" rx="1"></rect><line x1="16" y1="15" x2="18" y2="15"></line></svg>',
			'color'  =>'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path></svg>',
		);
		$svgDefault = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>';
		?>
		<?php if (!empty($caratDefs)): ?>
		<div class="cd-specs-below">
			<?php foreach ($caratDefs as $cid => $carat):
				$rawLabel = !empty($carat['textimg']) ? $carat['textimg'] : $carat['name'];
				$label = Text::_($rawLabel) ?: $rawLabel;
				$key = isset($caratOrigKeys[$cid]) ? $caratOrigKeys[$cid] : strtolower($label);
				$svg = $svgDefault;
				foreach ($svgIcons as $kw => $is) { if (strpos($key, $kw) !== false) { $svg = $is; break; } }
			?>
			<div class="cd-spec-pill"><?php echo $svg; ?><?php echo htmlspecialchars($label); ?></div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div><!-- /.cd-desktop-meta -->

	<!-- Mobile info: name, category, specs grid (mobile order 4) -->
	<div class="cd-mobile-info-wrap" style="margin-top:16px;" style="display:none;">
		<div class="cd-info">
			<?php if (!empty($categoryName)): ?><span class="cd-car-cat"><?php echo $categoryName; ?></span><?php endif; ?>
			<h1 class="cd-car-name"><?php echo htmlspecialchars($car['name']); ?> — <?php echo Text::_('VRCTITLECARDESCR'); ?></h1>
			<?php if (!empty($caratDefs)): ?>
			<div class="cd-specs">
				<?php foreach ($caratDefs as $cid => $carat):
					$rawLabel = !empty($carat['textimg']) ? $carat['textimg'] : $carat['name'];
					$label = Text::_($rawLabel) ?: $rawLabel;
					$key = isset($caratOrigKeys[$cid]) ? $caratOrigKeys[$cid] : strtolower($label);
					$svg = $svgDefault;
					foreach ($svgIcons as $kw => $is) { if (strpos($key, $kw) !== false) { $svg = $is; break; } }
				?>
				<div class="cd-spec"><div class="cd-spec-icon"><?php echo $svg; ?></div><div class="cd-spec-text"><span class="cd-spec-value"><?php echo htmlspecialchars($label); ?></span></div></div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if (isset($car_params['reqinfo']) && (bool)$car_params['reqinfo']): ?>
			<a href="javascript:void(0);" onclick="vrcShowRequestInfo();" class="cd-reqinfo-btn"><i class="fas fa-envelope"></i> <?php echo Text::_('VRCCARREQINFOBTN'); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<!-- Description (mobile order 5) -->
	<?php if (!empty($car['info'])): ?>
	<div class="cd-description" style="margin-top:24px;">
		<div class="cd-description-text"><?php echo $car['info']; ?></div>
	</div>
	<?php endif; ?>

	<!-- Availability calendars (mobile order 6) -->
	<?php
	$pmonth = VikRequest::getInt('month', '', 'request');
	$pday   = VikRequest::getInt('dt', '', 'request');
	$viewingdayts = !empty($pday) && $pday >= $nowts ? $pday : $nowts;
	$show_hourly_cal = (array_key_exists('shourlycal', $car_params) && intval($car_params['shourlycal']) > 0);
	$numcalendars = VikRentCar::numCalendars();
	$arr = getdate(); $mon=$arr['mon']; $year=$arr['year'];
	$day=($mon<10?"0".$mon:$mon)."/01/".$year; $dayts=strtotime($day);
	$validmonth=false; if($pmonth>0&&$pmonth>=$dayts){$validmonth=true;}
	$moptions="";
	for($i=0;$i<12;$i++){
		$moptions.="<option value=\"".$dayts."\"".($validmonth&&$pmonth==$dayts?" selected=\"selected\"":"").">".VikRentCar::sayMonth($arr['mon'])." ".$arr['year']."</option>\n";
		$next=$arr['mon']+1; $dayts=mktime(0,0,0,$next,1,$arr['year']); $arr=getdate($dayts);
	}
	$showpartlyres=VikRentCar::showPartlyReserved();
	if ($numcalendars > 0):
	?>
	<div class="cd-avail-section" style="margin-top:24px;">
		<h2><?php echo Text::_('VRCAVAILABILITY') ?: 'Disponibilitate'; ?></h2>
		<div class="cd-legend-bar">
			<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$car['id'].(!empty($pitemid)?'&Itemid='.$pitemid:'')); ?>" method="post" name="vrcmonths" style="margin:0;">
				<select name="month" onchange="javascript: document.vrcmonths.submit();" class="vrcselectm"><?php echo $moptions; ?></select>
			</form>
			<div class="cd-legend-items">
				<span class="cd-legend-item"><span class="cd-leg-dot cd-leg-free"></span> <?php echo Text::_('VRLEGFREE'); ?></span>
				<?php if($showpartlyres): ?><span class="cd-legend-item"><span class="cd-leg-dot cd-leg-warn"></span> <?php echo Text::_('VRLEGWARNING'); ?></span><?php endif; ?>
				<span class="cd-legend-item"><span class="cd-leg-dot cd-leg-busy"></span> <?php echo Text::_($show_hourly_cal?'VRLEGBUSYCHECKH':'VRLEGBUSY'); ?></span>
			</div>
		</div>
		<?php
		$check=is_array($busy);
		if($validmonth){$arr=getdate($pmonth);$mon=$arr['mon'];$year=$arr['year'];$day=($mon<10?"0".$mon:$mon)."/01/".$year;$dayts=strtotime($day);$newarr=getdate($dayts);}
		else{$arr=getdate();$mon=$arr['mon'];$year=$arr['year'];$day=($mon<10?"0".$mon:$mon)."/01/".$year;$dayts=strtotime($day);$newarr=getdate($dayts);}
		$first_month_info=$newarr;
		$firstwday=(int)VikRentCar::getFirstWeekDay();
		$days_labels=array(Text::_('VRSUN'),Text::_('VRMON'),Text::_('VRTUE'),Text::_('VRWED'),Text::_('VRTHU'),Text::_('VRFRI'),Text::_('VRSAT'));
		$days_indexes=array(); for($i=0;$i<7;$i++){$days_indexes[$i]=(6-($firstwday-$i)+1)%7;}
		?>
		<div class="cd-cals-grid">
		<?php for($jj=1;$jj<=$numcalendars;$jj++){$d_count=0;$cal="";$previousdayclass='';$unitsadjuster=0;$lastdropoff=0; ?>
			<div class="vrccaldivcont"><table class="vrccal">
				<tr><td colspan="7" align="center"><strong><?php echo VikRentCar::sayMonth($newarr['mon'])." ".$newarr['year']; ?></strong></td></tr>
				<tr class="vrccaldays"><?php for($i=0;$i<7;$i++){$d_ind=($i+$firstwday)<7?($i+$firstwday):($i+$firstwday-7);echo '<td>'.$days_labels[$d_ind].'</td>';} ?></tr>
				<tr>
		<?php
			for($i=0,$n=$days_indexes[$newarr['wday']];$i<$n;$i++,$d_count++){$cal.="<td>&nbsp;</td>";}
			while($newarr['mon']==$mon){
				if($d_count>6){$d_count=0;$cal.="</tr>\n<tr>";}
				$dclass="vrctdfree";$totfound=0;
				if($check){$ischeckinday=false;$ischeckoutday=false;$lastfoundritts=0;$lastfoundconts=-1;$lasttotfound=0;
					foreach($busy as $b){$to=getdate($b['ritiro']);$rt=mktime(0,0,0,$to['mon'],$to['mday'],$to['year']);$tw=getdate($b['realback']);$ct=mktime(0,0,0,$tw['mon'],$tw['mday'],$tw['year']);
						if($newarr[0]>=$rt&&$newarr[0]<=$ct){$totfound++;if($newarr[0]==$rt){$lastfoundritts=$rt;$lastfoundconts=$ct;if($rt!=$ct)$lasttotfound++;$ischeckinday=true;}elseif($newarr[0]==$ct){$ischeckoutday=true;$lastdropoff=$b['realback'];}if($ischeckinday&&!empty($lastdropoff)&&$lastdropoff<=$b['ritiro']){$unitsadjuster++;}if($b['stop_sales']==1){$totfound=$car['units'];$unitsadjuster=0;break;}}}
					if($totfound>=$car['units']){$dclass="vrctdbusy";if($ischeckinday&&$previousdayclass!="vrctdbusy"){$dclass="vrctdbusy vrctdbusyforcheckin";}}elseif($totfound>0&&$showpartlyres){$dclass="vrctdwarning";}}
				$previousdayclass=$dclass;$useday=($newarr['mday']<10?"0".$newarr['mday']:$newarr['mday']);
				if($newarr[0]>=$nowts){if($show_hourly_cal){$useday='<a href="'.JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$car['id'].'&dt='.$newarr[0].(!empty($pmonth)&&$validmonth?'&month='.$pmonth:'').(!empty($pitemid)?'&Itemid='.$pitemid:'')).'">'.$useday.'</a>';}else{$useday='<span class="vrc-cdetails-cal-pickday" data-daydate="'.date($df,$newarr[0]).'">'.$useday.'</span>';}}
				$cal.="<td class=\"".$dclass."\">".$useday."</td>\n";
				$dayts=mktime(0,0,0,$newarr['mon'],($newarr['mday']+1),$newarr['year']);$newarr=getdate($dayts);$d_count++;}
			for($i=$d_count;$i<=6;$i++){$cal.="<td>&nbsp;</td>";}echo $cal;
		?></tr></table></div>
		<?php if($mon==12){$mon=1;$year+=1;}else{$mon+=1;}$dayts=mktime(0,0,0,$mon,1,$year);$newarr=getdate($dayts);}?>
		</div>
	</div>
	<?php endif; ?>

	<!-- Hourly calendar (mobile order 7) -->
	<?php if ($show_hourly_cal && isset($newarr)):
		$picksondrops = VikRentCar::allowPickOnDrop(); ?>
	<div class="cd-hourly-cal-wrap"><div class="cd-hourly-cal">
		<h4><?php echo Text::sprintf('VRCAVAILSINGLEDAY', date($df, $viewingdayts)); ?></h4>
		<div class="table-responsive"><table class="vrc-hourly-cal table">
			<tr><td style="text-align:center;"><?php echo Text::_('VRCLEGH'); ?></td>
			<?php for($h=0;$h<=23;$h++){if($nowtf=='H:i'){$sh=$h<10?"0".$h:$h;}else{$ap=$h<12?' am':' pm';$am=$h>12?($h-12):$h;$sh=$am<10?"0".$am.$ap:$am.$ap;}echo '<td style="text-align:center;">'.$sh.'</td>';} ?></tr>
			<tr class="vrc-hourlycal-rowavail"><td style="text-align:center;"> </td>
			<?php for($h=0;$h<=23;$h++){$cht=($viewingdayts+($h*3600));$dc="vrctdfree";
				if($check){$tf=0;foreach($busy as $b){$to=getdate($b['ritiro']);$rt=mktime(0,0,0,$to['mon'],$to['mday'],$to['year']);$tw=getdate($b['realback']);$ct=mktime(0,0,0,$tw['mon'],$tw['mday'],$tw['year']);if($viewingdayts>=$rt&&$viewingdayts<=$ct){if($b['stop_sales']==1){$tf=$car['units'];break;}if($cht>=$b['ritiro']&&$cht<=$b['realback']){if($picksondrops&&!($cht>$b['ritiro']&&$cht<$b['realback'])&&$cht==$b['realback'])continue;$tf++;}}}if($tf>=$car['units']){$dc="vrctdbusy";}elseif($tf>0&&$showpartlyres){$dc="vrctdwarning";}}
				echo '<td style="text-align:center;" class="'.$dc.'"> </td>';} ?>
			</tr>
		</table></div>
	</div></div>
	<?php endif; ?>

	<!-- Extended disabled-date computation -->
	<?php
	if (is_array($busy)) {
		if (!isset($newarr)) { $ni=getdate(); $newarr=getdate(mktime(0,0,0,$ni['mon'],$ni['mday'],$ni['year'])); }
		$max_ts2=mktime(23,59,59,$newarr['mon'],$newarr['mday'],($newarr['year']+1));
		$newarr2=$newarr; $ld2=0; $ua2=0;
		while($newarr2[0]<$max_ts2){
			$tf2=0;$icd=false;$iod=false;$lfr=0;$lfc=-1;$ltf=0;
			foreach($busy as $b){$ii=getdate($b['ritiro']);$ci=mktime(0,0,0,$ii['mon'],$ii['mday'],$ii['year']);$io=getdate($b['realback']);$co=mktime(0,0,0,$io['mon'],$io['mday'],$io['year']);
				if($newarr2[0]>=$ci&&$newarr2[0]<$co){$tf2++;if($newarr2[0]==$ci){$lfr=$ci;$lfc=$co;if($ci!=$co)$ltf++;$icd=true;}elseif($newarr2[0]==$co){$iod=true;$ld2=$b['realback'];}if($icd&&!empty($ld2)&&$ld2<=$b['ritiro']){$ua2++;}if($b['stop_sales']==1){$tf2=$car['units'];$ua2=0;break;}}}
			if($tf2>=$car['units']){if($icd||!$iod){if($ltf>1||$lfr!=$lfc){if(($tf2-$ua2)>=$car['units']){$push_disabled_in[]='"'.date('Y-m-d',$newarr2[0]).'"';}}}if(!$icd&&!$iod){$push_disabled_out[]='"'.date('Y-m-d',$newarr2[0]).'"';}}
			$newarr2=getdate(mktime(0,0,0,$newarr2['mon'],($newarr2['mday']+1),$newarr2['year']));
		}
	}
	?>

</div><!-- /.cd-left -->

<!-- ═══════ RIGHT COLUMN (mobile order 2 — booking card) ═══════ -->
<div class="cd-right">
<div id="vrc-bookingpart-init"></div>
<div class="cd-booking-card v3">

	<?php if (VikRentCar::allowRent()) { ?>

	<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar' . (!empty($pitemid) ? '&Itemid=' . $pitemid : '')); ?>"
			method="get"
			onsubmit="return vrcValidateSearch();">
		<input type="hidden" name="option"  value="com_vikrentcar"/>
		<input type="hidden" name="task"    value="oconfirm"/>
		<input type="hidden" name="carid"   value="<?php echo $car['id']; ?>"/>
		<input type="hidden" name="priceid" id="vrc-priceid" value="<?php echo (int)$_firstIdprice; ?>"/>
		<input type="hidden" name="pickup"  id="vrc-pickup"  value=""/>
		<input type="hidden" name="release" id="vrc-release" value=""/>
		<input type="hidden" name="days"    id="vrc-days"    value=""/>
		<?php if (VikRequest::getString('tmpl','','request') == 'component'): ?>
		<input type="hidden" name="tmpl" value="component"/>
		<?php endif; ?>
		<?php if (!empty($pitemid)): ?>
		<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
		<?php endif; ?>

		<!-- Must be type="text" for jQuery UI datepicker to initialize -->
		<!-- Visually hidden via CSS .vrc-dp-hidden -->
		<input type="text" name="pickupdate"  id="pickupdate"  autocomplete="off" readonly
			class="vrc-dp-hidden" aria-hidden="true" tabindex="-1"/>
		<input type="text" name="releasedate" id="releasedate" autocomplete="off" readonly
			class="vrc-dp-hidden" aria-hidden="true" tabindex="-1"/>

		<?php if (!strlen($forced_pickup)): ?>
		<!-- Pickup time select (existing #vrccomselph — kept for JS hooks) -->
		<?php else: $fp = (int)$forced_pickup; $fph = floor($fp/3600); ?>
		<input type="hidden" name="pickuph" value="<?php echo $fph; ?>"/>
		<input type="hidden" name="pickupm" value="0"/>
		<?php endif; ?>
		<?php if (!strlen($forced_dropoff)): ?>
		<?php else: $fd = (int)$forced_dropoff; $fdh = floor($fd/3600); ?>
		<input type="hidden" name="releaseh" value="<?php echo $fdh; ?>"/>
		<input type="hidden" name="releasem" value="0"/>
		<?php endif; ?>
		<input type="hidden" name="pickupm"  id="vrccomselpm"  value="0"/>
		<input type="hidden" name="releasem" id="vrccomseldm" value="0"/>

		<?php
		$_firstDropId = '';
		if (is_array($places) && count($places) > 0) {
			foreach ($places as $_pl) {
				if (!$check_drop_locs || in_array($_pl['id'], $pladrop_ids)) {
					$_firstDropId = $_pl['id']; break;
				}
			}
		}
		?>

		<!-- ═══ SECTION: Trip Dates ═══ -->
		<div class="v3-section">
		<div class="v3-section-label"><?php echo Text::_('VRCSEARCHDATES') ?: 'Trip dates'; ?></div>

		<!-- Inline calendar -->
		<div class="v3-cal-wrap">
			<div class="v3-cal-header">
			<button type="button" class="v3-cal-nav" id="v3-prev-m">&#8249;</button>
			<span class="v3-cal-month" id="v3-cal-title"></span>
			<button type="button" class="v3-cal-nav" id="v3-next-m">&#8250;</button>
			</div>
			<div class="v3-cal-grid">
			<div class="v3-cal-dows" id="v3-cal-dows"></div>
			<div class="v3-cal-days" id="v3-cal-days"></div>
			</div>
		</div>

		<!-- Date summary strip -->
		<div class="v3-date-summary">
			<div class="v3-ds-block">
			<div class="v3-ds-label"><?php echo Text::_('VRPICKUP') ?: 'Pickup'; ?></div>
			<div class="v3-ds-val" id="v3-ds-start"><?php echo Text::_('VRCHOOSEDATE') ?: 'Select start'; ?></div>
			</div>
			<div class="v3-ds-sep">&#8594;</div>
			<div class="v3-ds-block">
			<div class="v3-ds-label"><?php echo Text::_('VRRETURN') ?: 'Return'; ?></div>
			<div class="v3-ds-val" id="v3-ds-end"><?php echo Text::_('VRCHOOSEDATE') ?: 'Select end'; ?></div>
			</div>
			<div class="v3-dur-pill" id="v3-dur-pill" style="display:none;"></div>
		</div>

		<!-- Savings nudge -->
		<div class="v3-save-nudge" id="v3-save-nudge" style="display:none;">
			<div class="v3-sn-top">
			<svg class="v3-sn-icon" viewBox="0 0 14 14" fill="none"><path d="M7 1l1.5 3 3.5.5-2.5 2.5.6 3.5L7 9l-3.1 1.5.6-3.5L2 4.5l3.5-.5z" fill="#085041"/></svg>
			<!-- Tier savings nudge — shown when 1 day short of a cheaper rate tier -->
			<span class="v3-sn-title" id="v3-sn-title"></span>
			</div>
			<div class="v3-sn-body" id="v3-sn-body"></div>
			<button type="button" class="v3-sn-btn" id="v3-sn-btn"></button>
		</div>
		</div>

		<!-- ═══ SECTION: Times ═══ -->
		<?php
		// Build hours-only select (minutes will be hidden input = 0)
		$hours = "";
		$pickhdeftime = isset($places[$indvrcplace]) && !empty($places[$indvrcplace]['defaulttime']) ? ((int)$places[$indvrcplace]['defaulttime'] / 3600) : 12;
		if (!($i < $j)) {
			while (intval($i) != (int)$j) {
				$sayi = $i < 10 ? "0".$i : $i;
				if ($nowtf != 'H:i') { $ampm = $i < 12 ? ' am' : ' pm'; $ampmh = $i > 12 ? ($i - 12) : $i; $sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm; } else { $sayh = $sayi . ':00'; }
				$hours .= "<option value=\"".(int)$i."\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">".$sayh."</option>\n";
				$i++; $i = $i > 23 ? 0 : $i;
			}
			$sayi = $i < 10 ? "0".$i : $i;
			if ($nowtf != 'H:i') { $ampm = $i < 12 ? ' am' : ' pm'; $ampmh = $i > 12 ? ($i - 12) : $i; $sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm; } else { $sayh = $sayi . ':00'; }
			$hours .= "<option value=\"".(int)$i."\">".$sayh."</option>\n";
		} else {
			while ($i <= $j) {
				$sayi = $i < 10 ? "0".$i : $i;
				if ($nowtf != 'H:i') { $ampm = $i < 12 ? ' am' : ' pm'; $ampmh = $i > 12 ? ($i - 12) : $i; $sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm; } else { $sayh = $sayi . ':00'; }
				$hours .= "<option value=\"".(int)$i."\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">".$sayh."</option>\n";
				$i++;
			}
		}
		?>
		<?php if (!strlen($forced_pickup) || !strlen($forced_dropoff)): ?>
		<div class="v3-section v3-section-times">
		<?php if (!strlen($forced_pickup)): ?>
		<div class="v3-time-group">
			<div class="v3-time-label"><?php echo Text::_('VRCPICKUPTIME') ?: 'Pickup time'; ?></div>
			<div class="v3-th" id="vrccomselph">
			<select name="pickuph" onchange="setTimeout(cdUpdateSummary,50);setTimeout(cdCheckOoh,50);"><?php echo $hours; ?></select>
			</div>
		</div>
		<?php endif; ?>
		<?php if (!strlen($forced_dropoff)): ?>
		<div class="v3-time-group">
			<div class="v3-time-label"><?php echo Text::_('VRCRETURNTIME') ?: 'Return time'; ?></div>
			<div class="v3-th" id="vrccomseldh">
			<select name="releaseh" onchange="setTimeout(cdUpdateSummary,50);setTimeout(cdCheckOoh,50);"><?php echo $hours; ?></select>
			</div>
		</div>
		<?php endif; ?>
		</div>
		<?php endif; ?>

		<!-- Grace period progress bar -->
		<?php if ($hasGracePeriod): ?>
		<div class="v3-grace-bar" id="v3-grace-bar">
		<div class="v3-grace-top">
			<span class="v3-grace-label"><?php echo Text::_('VRCGRACEPERIOD') ?: 'Grace period'; ?></span>
			<span class="v3-grace-time" id="v3-grace-time">
			<?php
				// Build a human-readable label: "28h" → "1 zi și 4 ore" style
				if ($graceHours >= 24) {
					$_gDays  = floor($graceHours / 24);
					$_gExtra = $graceHours % 24;
					if ($_gExtra > 0) {
						$_graceSummary = $_gDays . (Text::_('VRC_GRACE_DAYS') ?: ' zi') . ' ' . Text::_('VRC_GRACE_AND') . ' ' . $_gExtra . (Text::_('VRC_GRACE_HRS') ?: ' h');
					} else {
						$_graceSummary = $_gDays . (Text::_('VRC_GRACE_DAYS') ?: ' zi');
					}
				} else {
					$_graceSummary = $graceHours . (Text::_('VRC_GRACE_HRS') ?: ' h');
				}
				echo $_graceSummary;
			?>
			</span>
		</div>
		<div class="v3-grace-track"><div class="v3-grace-fill" id="v3-grace-fill"></div></div>
		<span class="v3-grace-hint cd-grace-returnby" id="cd-grace-returnby" style="display:none;"></span>
		</div>
		<?php endif; ?>

		<!-- Grace exceeded warning (shown by JS when overage > grace window) -->
		<?php if ($hasGracePeriod): ?>
		<div class="cd-grace-exceeded" id="cd-grace-exceeded" style="display:none;">
			<svg class="cd-grace-exc-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
			     viewBox="0 0 24 24" fill="none" stroke="currentColor"
			     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
				<line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
			</svg>
			<div class="cd-grace-exc-body">
				<span class="cd-grace-exc-label" id="cd-grace-exc-label"></span>
				<span class="cd-grace-exc-hint"><?php echo htmlspecialchars(
					Text::_('VRC_GRACE_EXCEEDED_HINT') ?: 'Returnați în limita perioadei de grație pentru a evita taxa suplimentară.'
				); ?></span>
			</div>
		</div>
		<?php endif; ?>

		<!-- OOH warning -->
		<?php if (!empty($oohFees)): ?>
		<div class="v3-offhours-banner" id="cd-ooh-warning" style="display:none;">
		<span class="v3-ooh-dot"></span>
		<span id="cd-ooh-text"></span>
		</div>
		<?php endif; ?>

		<!-- ═══ SECTION: Location ═══ -->
		<?php if (is_array($places) && count($places) > 0): ?>
		<div class="v3-section">
		<div class="v3-section-label"><?php echo Text::_('VRPPLACE') ?: 'Location'; ?></div>

		<!-- Pickup location -->
		<div class="v3-loc-field">
			<svg class="v3-loc-icon" viewBox="0 0 16 16" fill="none"><path d="M8 1.5C5.5 1.5 3.5 3.5 3.5 6c0 3.5 4.5 8.5 4.5 8.5s4.5-5 4.5-8.5c0-2.5-2-4.5-4.5-4.5zm0 6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z" fill="currentColor" opacity=".5"/></svg>
			<select name="place" id="place" class="v3-loc-sel"
			<?php echo $onchangeplaces; ?>>
			<?php foreach ($places as $pla):
				if ($check_pick_locs && !in_array($pla['id'], $plapick_ids)) continue;
				if (!empty($pla['lat']) && !empty($pla['lng'])) $coordsplaces[] = $pla;
			?>
			<option value="<?php echo $pla['id']; ?>" id="place<?php echo $pla['id']; ?>"><?php echo $pla['name']; ?></option>
			<?php endforeach; ?>
			</select>
		</div>

		<!-- Different return toggle -->
		<div class="v3-diff-row" onclick="v3ToggleDiffReturn();">
			<div class="v3-diff-cb" id="v3-diff-cb">
			<svg width="10" height="10" viewBox="0 0 12 12" fill="none"><polyline points="2,6 5,9 10,3" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</div>
			<label class="v3-diff-label" for="cd-diff-return-chk">
			<?php echo Text::_('VRPDIFFRETURN') ?: 'Return to a different location'; ?>
			</label>
			<input type="checkbox" id="cd-diff-return-chk" style="display:none;"/>
		</div>

		<!-- Return location (hidden until toggled) -->
		<div id="cd-return-location-wrap" style="display:none; margin-top:10px;">
			<div class="v3-loc-field">
			<svg class="v3-loc-icon" viewBox="0 0 16 16" fill="none"><path d="M8 1.5C5.5 1.5 3.5 3.5 3.5 6c0 3.5 4.5 8.5 4.5 8.5s4.5-5 4.5-8.5c0-2.5-2-4.5-4.5-4.5zm0 6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z" fill="#FE5001" opacity=".7"/></svg>
			<select id="returnplace_visible" name="returnplace_visible" class="v3-loc-sel"
				<?php echo $onchangeplacesdrop ?? ''; ?>>
				<?php foreach ($places as $pla):
				if ($check_drop_locs && !in_array($pla['id'], $pladrop_ids)) continue;
				?>
				<option value="<?php echo $pla['id']; ?>"><?php echo $pla['name']; ?></option>
				<?php endforeach; ?>
			</select>
			</div>
			<div class="v3-drop-fee-hint"><?php echo Text::_('VRPDROPFEEHINT') ?: 'Different return location may incur a drop-off fee'; ?></div>
		</div>
		</div>
		<input type="hidden" name="returnplace" id="returnplace" value="<?php echo htmlspecialchars($_firstDropId); ?>"/>
		<?php endif; ?>

		<!-- ═══ SECTION: Options ═══ -->
		<?php if (!empty($carOptionals)): ?>
		<div class="v3-section">
		<div class="v3-section-label"><?php echo Text::_('VRACCOPZ') ?: 'Options'; ?></div>
		<?php foreach ($carOptionals as $_opt):
			$_optPerDay   = intval($_opt['perday']) === 1;
			$_optBaseCost = (float)$_opt['cost'];
			$_optMax      = !empty($_opt['maxprice']) && $_opt['maxprice'] > 0 ? (float)$_opt['maxprice'] : 0;
			$_optCostDisp = (floor($_optBaseCost) == $_optBaseCost) ? (int)$_optBaseCost : $_optBaseCost;
			$_optPriceLabel = $currencysymb . $_optCostDisp . ($_optPerDay ? '/' . (Text::_('VRCSEARCHDAY') ?: 'day') : '');
			$_optId = (int)$_opt['id'];
		?>
		<div class="v3-opt-row" id="cd-opt-row-<?php echo $_optId; ?>">
			<div class="v3-opt-info">
			<div class="v3-opt-name"><?php echo htmlspecialchars($_opt['name']); ?></div>
			<div class="v3-opt-price"><?php echo $_optPriceLabel; ?><?php echo (int)$_opt['hmany'] === 1 ? ' · <span id="cd-opt-qty-'.$_optId.'">0</span> added' : ''; ?></div>
			</div>
			<?php if ((int)$_opt['hmany'] === 1): ?>
			<div class="v3-counter">
			<button type="button" class="v3-cbtn" onclick="cdSetOptionalQty(<?php echo $_optId; ?>, -1)">&#8722;</button>
			<span class="v3-cval" id="cd-opt-qty-<?php echo $_optId; ?>">0</span>
			<button type="button" class="v3-cbtn" onclick="cdSetOptionalQty(<?php echo $_optId; ?>, 1)">&#43;</button>
			</div>
			<input type="hidden" name="optid<?php echo $_optId; ?>" id="cd-opt-input-<?php echo $_optId; ?>" value="0"/>
			<?php else: ?>
			<label class="v3-toggle">
			<input type="checkbox" id="cd-opt-toggle-<?php echo $_optId; ?>"
				onchange="cdToggleOptional(<?php echo $_optId; ?>, <?php echo $_optBaseCost; ?>, <?php echo (int)$_optPerDay; ?>, <?php echo $_optMax; ?>)"/>
			<span class="v3-ttrack"></span>
			<span class="v3-tthumb"></span>
			</label>
			<input type="hidden" name="optid<?php echo $_optId; ?>" id="cd-opt-input-<?php echo $_optId; ?>" value="0"/>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<!-- ═══ Good to know ═══ -->
		<div class="v3-section">
		<div class="v3-section-label"><?php echo Text::_('VRCGOODTOKNOW') ?: 'Good to know'; ?></div>
		<div class="v3-notes-grid">
			<?php if ($hasGracePeriod): ?>
			<div class="v3-ni">
			<div class="v3-ni-k"><?php echo Text::_('VRCGRACEPERIOD') ?: 'Grace period'; ?></div>
			<div class="v3-ni-v"><?php echo $graceHours; ?>h <?php echo Text::_('VRCAFTERRETURN') ?: 'after return'; ?></div>
			</div>
			<?php endif; ?>
			<div class="v3-ni" id="cd-km-notice">
			<div class="v3-ni-k"><?php echo Text::_('VRCKMLIMIT_LABEL') ?: 'Mileage'; ?></div>
			<div class="v3-ni-v">200 <?php echo Text::_('VRCKM_PER_DAY') ?: 'km/day'; ?></div>
			</div>
			<div class="v3-ni">
			<div class="v3-ni-k"><?php echo Text::_('VRCKM_EXTRA_LABEL') ?: 'Over limit'; ?></div>
			<div class="v3-ni-v">€0.25/km</div>
			</div>
		</div>
		</div>

		<!-- ═══ SECTION: Price Breakdown ═══ -->
		<div class="v3-section" id="cd-summary" style="display:none;">
		<div class="v3-section-label"><?php echo Text::_('VRPRICE') ?: 'Price breakdown'; ?></div>
		<!-- Description sentence (existing logic) -->
		<div class="cd-summary-desc v3-summary-desc" id="cd-summary-desc"></div>

		<!-- Savings tip (existing HTML — same IDs, new styling) -->
		<div id="cd-savings-tip" style="display:none;" class="v3-savings-tip">
			<div class="v3-sn-top">
			<span>💰</span>
			<span class="cd-savings-tip-title v3-sn-title"><?php echo Text::_('VRCSAVINGSTIP_TITLE') ?: 'Save more!'; ?></span>
			</div>
			<div class="cd-savings-tip-text v3-sn-body">
			<?php echo Text::_('VRCSAVINGSTIP_PRE') ?: 'Extend to'; ?>
			<strong class="cd-tip-days"></strong> <?php echo Text::_('VRCSEARCHDAYS') ?: 'days'; ?>
			<?php echo Text::_('VRCSAVINGSTIP_MID') ?: 'and save'; ?>
			<strong class="cd-tip-savings"></strong>!
			<?php echo Text::_('VRCSAVINGSTIP_TOTAL') ?: 'Total:'; ?>
			<strong class="cd-tip-newtotal"></strong>
			<?php echo Text::_('VRCSAVINGSTIP_INSTEAD') ?: 'instead of'; ?>
			<s class="cd-tip-oldtotal"></s>.
			</div>
		</div>

		<div id="cd-summary-rows" class="v3-pr-rows"></div>
		<div class="v3-pr-row v3-pr-total">
			<span><?php echo Text::_('VRTOTAL') ?: 'Total'; ?></span>
			<span class="v3-pr-total-amt" id="cd-summary-total"></span>
		</div>
		</div>

		<!-- Deposit notice -->
		<?php if ($showDeposit): ?>
		<div class="v3-deposit-notice">
		<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
		<span><?php echo Text::_('VRCDEPOSITLABEL') ?: 'Security deposit:'; ?> <strong><?php echo $depositAmount . ' ' . $depositCurrency; ?></strong> — <?php echo Text::_('VRCDEPOSITRETURNED') ?: 'returned on car return'; ?></span>
		</div>
		<?php endif; ?>

		<!-- Coupon -->
		<?php if (VikRentCar::couponsEnabled()): ?>
		<div class="v3-promo-row">
		<input type="text" id="vrc-coupon-code" name="_couponcode"
				class="v3-promo-in"
				placeholder="<?php echo Text::_('VRCHAVEACOUPON') ?: 'Promo code'; ?>"
				autocomplete="off"/>
		<button type="button" id="vrc-coupon-apply" class="v3-promo-ap"
				data-original-text="<?php echo htmlspecialchars(Text::_('VRCSUBMITCOUPON') ?: 'Apply'); ?>">
			<?php echo Text::_('VRCSUBMITCOUPON') ?: 'Apply'; ?>
		</button>
		</div>
		<div id="vrc-coupon-feedback" class="cd-coupon-feedback"></div>
		<?php endif; ?>

		<!-- ═══ SECTION: Payment method ═══ -->
		<div class="v3-section">
		<div class="v3-section-label"><?php echo Text::_('VRCPAYMETHOD') ?: 'Payment'; ?></div>
		<div class="v3-pay-opt v3-pay-sel" id="v3-pay-full" onclick="v3SelPay('full')">
			<div class="v3-pay-left">
			<div class="v3-pay-radio"><div class="v3-pay-dot"></div></div>
			<div>
				<div class="v3-pay-name"><?php echo Text::_('VRCPAYFULL') ?: 'Pay in full'; ?></div>
				<div class="v3-pay-desc"><?php echo Text::_('VRCPAYFULL_DESC') ?: 'Charged now'; ?></div>
			</div>
			</div>
			<span class="v3-pay-amt" id="v3-pay-full-amt">—</span>
		</div>
		<?php if ($showDeposit && $depositAmount > 0): ?>
		<div class="v3-pay-opt" id="v3-pay-reserve" onclick="v3SelPay('reserve')">
			<div class="v3-pay-left">
			<div class="v3-pay-radio"><div class="v3-pay-dot"></div></div>
			<div>
				<div class="v3-pay-name"><?php echo Text::_('VRCPAYRESERVE') ?: 'Reserve'; ?> <span class="v3-pay-tag"><?php echo Text::_('VRCPAYRESERVE_TAG') ?: 'Popular'; ?></span></div>
				<div class="v3-pay-desc" id="v3-pay-res-desc"><?php echo $depositCurrency . $depositAmount; ?> <?php echo Text::_('VRCPAYNOW_REST') ?: 'now · rest on pickup'; ?></div>
			</div>
			</div>
			<span class="v3-pay-amt"><?php echo $depositCurrency . $depositAmount; ?></span>
		</div>
		<?php endif; ?>
		</div>

		<!-- Submit -->
		<button type="button"
				onclick="return vrcOpenBookingModal();"
				class="v3-book-btn btn vrcdetbooksubmit vrc-pref-color-btn">
		<?php echo Text::_('VRCBOOKTHISCAR'); ?>
		</button>

		<!-- Trust indicators -->
		<div class="v3-trust-row">
		<div class="v3-ti">
			<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#1D9E75" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
			<?php echo Text::_('VRCTRUST_CANCEL') ?: 'Free cancellation 72h'; ?>
		</div>
		<div class="v3-ti">
			<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#1D9E75" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
			<?php echo Text::_('VRCTRUST_SUPPORT') ?: '24/7 support'; ?>
		</div>
		<div class="v3-ti">
			<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#1D9E75" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
			<?php echo Text::_('VRCTRUST_INSURED') ?: 'Insured vehicles'; ?>
		</div>
		</div>

	</form>

	<!-- v3 Calendar JS Bridge — connects inline calendar to jQuery UI datepicker validation -->
	<script type="text/javascript">
	(function(){
		var v3StartDate = null, v3EndDate = null, v3Selecting = false;
		var v3ViewYear, v3ViewMonth;
		var v3Today = new Date(); v3Today.setHours(0,0,0,0);
		v3ViewYear = v3Today.getFullYear(); v3ViewMonth = v3Today.getMonth();

		var v3DisabledIn  = [<?php echo implode(',', $push_disabled_in); ?>];
		var v3DisabledOut = [<?php echo implode(',', $push_disabled_out); ?>];

		function v3Fmt(d){ if(!d)return''; return d.getDate()+'/'+(d.getMonth()+1)+'/'+d.getFullYear(); }
		function v3FmtDisp(d){ if(!d)return''; var mo=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; return mo[d.getMonth()]+' '+d.getDate(); }
		function v3YMD(d){ return d.getFullYear()+'-'+(d.getMonth()<9?'0':'')+(d.getMonth()+1)+'-'+(d.getDate()<10?'0':'')+d.getDate(); }

		function v3IsDisabledIn(d){
		var s=v3YMD(d);
		return v3DisabledIn.indexOf('"'+s+'"')>=0 || v3DisabledIn.indexOf(s)>=0;
		}
		function v3IsDisabledOut(d){
		var s=v3YMD(d);
		return v3DisabledOut.indexOf('"'+s+'"')>=0 || v3DisabledOut.indexOf(s)>=0;
		}

		function v3RenderCal(){
		var mn=['<?php echo Text::_("VRMONTHONE"); ?>','<?php echo Text::_("VRMONTHTWO"); ?>','<?php echo Text::_("VRMONTHTHREE"); ?>','<?php echo Text::_("VRMONTHFOUR"); ?>','<?php echo Text::_("VRMONTHFIVE"); ?>','<?php echo Text::_("VRMONTHSIX"); ?>','<?php echo Text::_("VRMONTHSEVEN"); ?>','<?php echo Text::_("VRMONTHEIGHT"); ?>','<?php echo Text::_("VRMONTHNINE"); ?>','<?php echo Text::_("VRMONTHTEN"); ?>','<?php echo Text::_("VRMONTHELEVEN"); ?>','<?php echo Text::_("VRMONTHTWELVE"); ?>'];
		document.getElementById('v3-cal-title').textContent = mn[v3ViewMonth]+' '+v3ViewYear;

		var dows = document.getElementById('v3-cal-dows');
		dows.innerHTML = '';
		var dowLabels = ['<?php echo mb_substr(Text::_("VRCJQCALMON"),0,2,"UTF-8"); ?>','<?php echo mb_substr(Text::_("VRCJQCALTUE"),0,2,"UTF-8"); ?>','<?php echo mb_substr(Text::_("VRCJQCALWED"),0,2,"UTF-8"); ?>','<?php echo mb_substr(Text::_("VRCJQCALTHU"),0,2,"UTF-8"); ?>','<?php echo mb_substr(Text::_("VRCJQCALFRI"),0,2,"UTF-8"); ?>','<?php echo mb_substr(Text::_("VRCJQCALSAT"),0,2,"UTF-8"); ?>','<?php echo mb_substr(Text::_("VRCJQCALSUN"),0,2,"UTF-8"); ?>'];
		dowLabels.forEach(function(l){ var el=document.createElement('div'); el.className='v3-cal-dow'; el.textContent=l; dows.appendChild(el); });

		var grid = document.getElementById('v3-cal-days');
		grid.innerHTML = '';
		var first = new Date(v3ViewYear, v3ViewMonth, 1);
		var dow = first.getDay(); dow = (dow+6)%7; // Monday-first
		for(var i=0;i<dow;i++){ var el=document.createElement('div'); el.className='v3-cal-day v3-disabled'; grid.appendChild(el); }
		var dim = new Date(v3ViewYear, v3ViewMonth+1, 0).getDate();
		var minAdv = <?php echo (int)VikRentCar::getMinDaysAdvance(); ?>;
		var minDate = new Date(v3Today.getTime()); minDate.setDate(minDate.getDate()+minAdv);

		for(var d=1;d<=dim;d++){
			var date = new Date(v3ViewYear, v3ViewMonth, d);
			var el = document.createElement('div');
			el.className = 'v3-cal-day';
			el.textContent = d;
			var isPast = date < minDate;
			var isDisIn = v3IsDisabledIn(date);
			var isDisOut = v3IsDisabledOut(date);
			if(isPast || (isDisIn && isDisOut)){ el.classList.add('v3-disabled'); }
			else {
			if(v3StartDate && date.getTime()===v3StartDate.getTime()) el.classList.add('v3-start');
			if(v3EndDate   && date.getTime()===v3EndDate.getTime())   el.classList.add('v3-end');
			if(v3StartDate && v3EndDate && date>v3StartDate && date<v3EndDate) el.classList.add('v3-in-range');
			if(date.getTime()===v3Today.getTime()) el.classList.add('v3-today');
			(function(dt){ el.addEventListener('click', function(){ v3PickDay(dt); }); })(new Date(date));
			}
			grid.appendChild(el);
		}
		}

		function v3PickDay(date){
		if(!v3StartDate || v3Selecting===false){
			v3StartDate=new Date(date); v3EndDate=null; v3Selecting=true;
		} else {
			if(date<=v3StartDate){ v3StartDate=new Date(date); v3EndDate=null; v3Selecting=true; }
			else { v3EndDate=new Date(date); v3Selecting=false; v3SyncToJQ(); }
		}
		v3RenderCal(); v3UpdateStrip();
		}

		function v3SyncToJQ(){
			/* Format date string to match vrcdateformat (d/m/Y, m/d/Y, or Y-m-d) */
			function fmtDate(d){
				if(!d) return '';
				var dd=(d.getDate()<10?'0':'')+d.getDate();
				var mm=((d.getMonth()+1)<10?'0':'')+(d.getMonth()+1);
				var yyyy=d.getFullYear();
				var fmt=(typeof vrcdateformat!=='undefined')?vrcdateformat:'dmY';
				if(fmt==='dmY') return dd+'/'+mm+'/'+yyyy;
				if(fmt==='mdY') return mm+'/'+dd+'/'+yyyy;
				return yyyy+'-'+mm+'-'+dd;
			}
			if(v3StartDate){
				jQuery('#pickupdate').val(fmtDate(v3StartDate)).trigger('change');
				try{ jQuery('#pickupdate').datepicker('setDate', v3StartDate); }catch(e){}
			}
			if(v3EndDate){
				jQuery('#releasedate').val(fmtDate(v3EndDate)).trigger('change');
				try{ jQuery('#releasedate').datepicker('setDate', v3EndDate); }catch(e){}
			}
			if(typeof vrcSetMinDropoffDate!=='undefined') vrcSetMinDropoffDate();
			if(typeof vrcLocationWopening!=='undefined') vrcLocationWopening('pickup');
			setTimeout(cdUpdateSummary, 100);
			setTimeout(cdCheckOoh, 100);
		}

		function v3UpdateStrip(){
		var startEl = document.getElementById('v3-ds-start');
		var endEl   = document.getElementById('v3-ds-end');
		var pill    = document.getElementById('v3-dur-pill');
		startEl.textContent = v3StartDate ? v3FmtDisp(v3StartDate) : '<?php echo Text::_("VRCHOOSEDATE") ?: "Select start"; ?>';
		endEl.textContent   = v3EndDate   ? v3FmtDisp(v3EndDate)   : '<?php echo Text::_("VRCHOOSEDATE") ?: "Select end"; ?>';
		if(v3StartDate && v3EndDate){
			var days = Math.round((v3EndDate-v3StartDate)/864e5);
			pill.textContent = days+(days===1?' <?php echo Text::_("VRCSEARCHDAY"); ?>'  :' <?php echo Text::_("VRCSEARCHDAYS"); ?>');
			pill.style.display='';
			v3CheckSavingsNudge(days);
			v3UpdateGraceBar();
		} else {
			pill.style.display='none';
			document.getElementById('v3-save-nudge').style.display='none';
			var gb = document.getElementById('v3-grace-bar');
			if(gb) gb.style.display='none';
		}
		}

		function v3UpdateGraceBar(){
		<?php if ($hasGracePeriod): ?>
		var gb = document.getElementById('v3-grace-bar');
		if(!gb || !v3EndDate) return;
		gb.style.display = 'block';
		var rt = parseInt(jQuery('#vrccomseldh select').val()) || 12;
		var graceMs = <?php echo $graceHours; ?> * 3600000;
		var deadlineH = rt + <?php echo $graceHours; ?>;
		var dh = deadlineH>23 ? deadlineH-24 : deadlineH;
		var dhStr = (dh<10?'0':'')+dh+':00';
		var graceStart = new Date(v3EndDate); graceStart.setHours(rt,0,0,0);
		var now = new Date();
		var elapsed = now - graceStart;
		var pct = Math.max(0, Math.min(100, 100-(elapsed/graceMs*100)));
		var fill = document.getElementById('v3-grace-fill');
		if(fill){
			fill.style.width = pct+'%';
			fill.style.background = elapsed>graceMs ? '#E24B4A' : '#1D9E75';
		}
		var hint = document.getElementById('cd-grace-returnby');
		if(hint){ hint.textContent = '<?php echo addslashes(Text::_("VRC_GRATUITY_RETURNBY") ?: "Return by %s to avoid extra day"); ?>'.replace('%s', dhStr); hint.style.display='block'; }
		<?php endif; ?>
		}

		function v3CheckSavingsNudge(days){
		if(!window.cdRateByDay) return;
		var keys = Object.keys(cdRateByDay).map(Number).sort(function(a,b){return a-b;});
		var nextTier = null;
		for(var i=0;i<keys.length;i++){ if(keys[i]>days){ nextTier={days:keys[i],price:cdRateByDay[keys[i]]}; break; } }
		var nudge = document.getElementById('v3-save-nudge');
		if(!nudge) return;
		if(!nextTier){ nudge.style.display='none'; return; }
		var extra = nextTier.days - days;
		var curRate = cdRateByDay[keys.filter(function(k){return k<=days;}).pop()||keys[0]];
		var curTotal = curRate * days;
		var newTotal = nextTier.price * nextTier.days;
		if(newTotal < curTotal){
			document.getElementById('v3-sn-title').textContent = '<?php echo addslashes(Text::_("VRCSAVINGSTIP_TITLE") ?: "Rent more, pay less"); ?>';
			document.getElementById('v3-sn-body').textContent = 'Add '+extra+' more day'+(extra>1?'s':'')+' — €'+nextTier.price+'/day rate. Total: €'+newTotal+' (save €'+(curTotal-newTotal)+')';
			var btn = document.getElementById('v3-sn-btn');
			btn.textContent = 'Add '+extra+' day'+(extra>1?'s':'')+' → €'+newTotal+' total';
			btn.onclick = function(){ v3ApplyNudge(nextTier.days); };
			nudge.style.display='block';
		} else { nudge.style.display='none'; }
		}

		function v3ApplyNudge(targetDays){
		if(!v3StartDate) return;
		v3EndDate = new Date(v3StartDate); v3EndDate.setDate(v3EndDate.getDate()+targetDays);
		v3Selecting = false; v3RenderCal(); v3UpdateStrip(); v3SyncToJQ();
		}

		window.v3ToggleDiffReturn = function(){
		var cb  = document.getElementById('v3-diff-cb');
		var chk = document.getElementById('cd-diff-return-chk');
		var wrap = document.getElementById('cd-return-location-wrap');
		chk.checked = !chk.checked;
		if(chk.checked){ cb.classList.add('v3-checked'); wrap.style.display='block'; }
		else            { cb.classList.remove('v3-checked'); wrap.style.display='none'; }
		jQuery('#cd-diff-return-chk').trigger('change');
		};

		window.v3SelPay = function(mode){
		document.querySelectorAll('.v3-pay-opt').forEach(function(o){ o.classList.remove('v3-pay-sel'); });
		document.getElementById('v3-pay-'+(mode==='full'?'full':'reserve')).classList.add('v3-pay-sel');
		};


                jQuery(document).ready(function(){
            // Find first available dates
            var minAdv = Math.max(1, <?php echo (int)VikRentCar::getMinDaysAdvance(); ?>);
            var minLos = <?php echo max(1, intval($def_min_los) > 0 ? intval($def_min_los) : 1); ?>;
            var cand = new Date(v3Today); cand.setDate(cand.getDate()+minAdv);
            for(var i=0;i<365;i++){
                if(!v3IsDisabledIn(cand)){
                    var drop = new Date(cand); drop.setDate(drop.getDate()+minLos);
                    for(var j=0;j<30;j++){
                        if(!v3IsDisabledOut(drop)){ v3StartDate=new Date(cand); v3EndDate=new Date(drop); break; }
                        drop.setDate(drop.getDate()+1);
                    }
                    if(v3StartDate) break;
                }
                cand.setDate(cand.getDate()+1);
            }
            // Render calendar and wire nav buttons
            v3RenderCal();
            v3UpdateStrip();
            if(v3StartDate && v3EndDate) setTimeout(v3SyncToJQ, 300);
            var prev = document.getElementById('v3-prev-m');
            var next = document.getElementById('v3-next-m');
            if(prev){
                prev.addEventListener('click', function(){
                    v3ViewMonth--; if(v3ViewMonth<0){v3ViewMonth=11;v3ViewYear--;} v3RenderCal();
                });
            }
            if(next){
                next.addEventListener('click', function(){
                    v3ViewMonth++; if(v3ViewMonth>11){v3ViewMonth=0;v3ViewYear++;} v3RenderCal();
                });
            }
        });

		// Hook: also update grace bar when time changes
		jQuery(document).on('change','#vrccomseldh select',function(){ v3UpdateGraceBar(); });
	})();
	</script>


		<script type="text/javascript">
		/* Sync returnplace logic */
		jQuery(function() {
			var labelPickupReturn = <?php echo json_encode(Text::_('VRPPICKUPRETURN') ?: 'Получение и возврат'); ?>;
			var labelPickup       = <?php echo json_encode(Text::_('VRPPLACE') ?: 'Место получения'); ?>;

			jQuery('#place').on('change', function() {
				// Only mirror if checkbox is unchecked
				if (!jQuery('#cd-diff-return-chk').is(':checked')) {
					jQuery('#returnplace').val(jQuery(this).val());
				}
				cdUpdateSummary();
				<?php if ($diffopentime): ?>
				vrcSetLocOpenTime(jQuery(this).val(), 'pickup');
				if (!jQuery('#cd-diff-return-chk').is(':checked')) {
					vrcSetLocOpenTime(jQuery(this).val(), 'dropoff');
				}
				<?php endif; ?>
			});

			jQuery('#returnplace_visible').on('change', function() {
				jQuery('#returnplace').val(jQuery(this).val());
				cdUpdateSummary();
				<?php if ($diffopentime): ?>
				vrcSetLocOpenTime(jQuery(this).val(), 'dropoff');
				<?php endif; ?>
			});

			jQuery('#cd-diff-return-chk').on('change', function() {
				var checked = jQuery(this).is(':checked');
				jQuery('#cd-return-location-wrap').toggle(checked);
				if (checked) {
					jQuery('#cd-pickup-label').text(labelPickup);
					// set returnplace to current drop select value
					jQuery('#returnplace').val(jQuery('#returnplace_visible').val());
				} else {
					jQuery('#cd-pickup-label').text(labelPickupReturn);
					// mirror pickup again
					jQuery('#returnplace').val(jQuery('#place').val());
					<?php if ($diffopentime): ?>
					vrcSetLocOpenTime(jQuery('#place').val(), 'dropoff');
					<?php endif; ?>
				}
				cdUpdateSummary();
			});
		});

		/* ── OOH + Optionals + Live Summary JS ───────────────────────── */
		var cdOohFees = <?php echo json_encode($oohFees); ?>;
		var cdCurrency = '€';
		var cdGraceHours = <?php echo (int)$graceHours; ?>;
		var cdGraceReturnByLabel = '<?php echo addslashes(Text::_('VRC_GRATUITY_RETURNBY') ?: 'Returnați până la %s fără costuri suplimentare'); ?>';
		var cdCarName = '<?php echo addslashes($car['name']); ?>';
		var cdPlacesMap = <?php
			$_pm = array();
			if (is_array($places)) { foreach ($places as $_p) { $_pm[(int)$_p['id']] = $_p['name']; } }
			echo json_encode($_pm);
		?>;
		var cdCouponAjaxUrl = '<?php echo rtrim(JURI::root(), '/'); ?>/templates/rent/php/coupon-ajax.php';
		var cdDescPrefix   = '<?php echo addslashes(Text::_('VRCSUMDESC_PREFIX')); ?>';
		var cdDescCarInfix = '<?php echo addslashes(Text::_('VRCSUMDESC_CAR_INFIX')); ?>';
		var cdDescLocInfix  = '<?php echo addslashes(Text::_('VRCSUMDESC_LOC_INFIX')); ?>';
		var cdDescLocPickup = '<?php echo addslashes(Text::_('VRCSUMDESCLOC_PICKUP') ?: ', получение в'); ?>';
		var cdDescLocReturn = '<?php echo addslashes(Text::_('VRCSUMDESCLOC_RETURN') ?: ', возврат в'); ?>';
		var cdDescDayWord  = '<?php echo addslashes(Text::_('VRCSEARCHDAY')); ?>';
		var cdDescDaysWord = '<?php echo addslashes(Text::_('VRCSEARCHDAYS')); ?>';
			var cdRateByDay = <?php
			$_jsRbd = array();
			foreach ($_rateByDay as $_d => $_r) { $_jsRbd[(int)$_d] = $_r; }
			echo json_encode($_jsRbd);
		?>;
		var cdOptionals = {};
		<?php foreach ($carOptionals as $_opt): ?>
		cdOptionals[<?php echo (int)$_opt['id']; ?>] = {perday: <?php echo (int)$_opt['perday']; ?>, cost: <?php echo (float)$_opt['cost']; ?>, max: <?php echo (!empty($_opt['maxprice'])?(float)$_opt['maxprice']:0); ?>, checked: false, hmany: <?php echo (int)$_opt['hmany']; ?>, qty: 0};
		<?php endforeach; ?>

		/* Format integer (no decimals) */
		function cdFmt(n) {
			return Math.round(parseFloat(n)).toString();
		}

		function cdGetDays() {
			var p = jQuery('#pickupdate').val(), r = jQuery('#releasedate').val();
			if (!p || !r) return null;
			try {
				var fmt = <?php if($df==='d/m/Y') echo "'dd/mm/yy'"; elseif($df==='m/d/Y') echo "'mm/dd/yy'"; else echo "'yy/mm/dd'"; ?>;
				var d1 = jQuery.datepicker.parseDate(fmt, p);
				var d2 = jQuery.datepicker.parseDate(fmt, r);
				
				// Get hour values from selects
				var pickHour = parseInt(jQuery('#vrccomselph select').val()) || 0;
				var dropHour = parseInt(jQuery('#vrccomseldh select').val()) || 0;
				
				// Set the hours on the date objects
				d1.setHours(pickHour, 0, 0, 0);
				d2.setHours(dropHour, 0, 0, 0);

				var diffMs   = d2 - d1;
				var diffDays = Math.ceil(diffMs / 86400000);

				// Grace logic only applies when the rental duration has a fractional-day
				// overage — i.e. diffMs is NOT an exact multiple of 24h.
				// Exact multiples (same pickup/return hour) need no grace adjustment.
				window.cdGraceState = 'none'; // 'none' | 'active' | 'exceeded'
				if (cdGraceHours > 0 && diffDays >= 1) {
					var exactDays = diffMs / 86400000; // e.g. 1.083 for 26h, 4.0 for 96h
					var hasFraction = (Math.abs(exactDays - Math.round(exactDays)) > 0.0001);

					if (hasFraction && diffDays > 1) {
						// There is a fractional overage beyond a whole number of days
						var floorDays = diffDays - 1;
						var overageMs = diffMs - (floorDays * 86400000);
						if (overageMs <= cdGraceHours * 3600000) {
							// Overage fits within grace → reduce billed days by 1
							diffDays = floorDays;
							window.cdGraceState = 'active';
						} else {
							// Overage exceeds grace → warn user
							window.cdGraceState = 'exceeded';
						}
					} else {
						// Exact day count or single day — grace window is available for return
						window.cdGraceState = 'active';
					}
				}

				return diffDays > 0 ? diffDays : null;
			} catch(e) { return null; }
		}
		

		function cdGetRate(days) {
			if (!days || !cdRateByDay) return null;
			var best = null;
			var keys = Object.keys(cdRateByDay).map(Number).sort(function(a, b) { return a - b; });
			for (var i = 0; i < keys.length; i++) {
				if (keys[i] <= days) { best = cdRateByDay[keys[i]]; }
			}
			return best;
		}

		/* Read hour select value → seconds since midnight */
		function cdGetHourSecs(selectOrWrapperId) {
			var $el = jQuery('#' + selectOrWrapperId);
			// For the new single select inside #vrccomselph / #vrccomseldh
			var $sel = $el.is('select') ? $el : $el.find('select');
			if (!$sel.length) return 0;
			return (parseInt($sel.val()) || 0) * 3600;
		}

		function cdIsOoh(secs, fee) {
			if (fee.from > fee.to) { return secs >= fee.from || secs < fee.to; }
			return secs >= fee.from && secs < fee.to;
		}

		function cdCheckOoh() {
			if (!cdOohFees || !cdOohFees.length) return;
			var pickSecs = cdGetHourSecs('vrccomselph');
			var dropSecs = cdGetHourSecs('vrccomseldh');
			var messages = [];
			for (var i = 0; i < cdOohFees.length; i++) {
				var f = cdOohFees[i];
				var pickOoh = (f.type === 1 || f.type === 3) && cdIsOoh(pickSecs, f);
				var dropOoh = (f.type === 2 || f.type === 3) && cdIsOoh(dropSecs, f);
				if (pickOoh || dropOoh) {
					var timeRange = ' (' + f.fromLabel + '\u2013' + f.toLabel + ')';
					var label;
					if (pickOoh && dropOoh) {
						label = '<?php echo addslashes(Text::_('VRCPVIEWOOHFEESNINE') ?: "Получение и возврат"); ?>' + timeRange;
					} else if (pickOoh) {
						label = '<?php echo addslashes(Text::_('VRCPVIEWOOHFEESFOUR') ?: "Только получение"); ?>' + timeRange;
					} else {
						label = '<?php echo addslashes(Text::_('VRCPVIEWOOHFEESFIVE') ?: "Только возврат"); ?>' + timeRange;
					}
					var parts = [];
					if (pickOoh) parts.push(cdCurrency + cdFmt(f.pickcharge));
					if (dropOoh) parts.push(cdCurrency + cdFmt(f.dropcharge));
					messages.push(label + ': ' + parts.join(' + '));
				}
			}
			var $w = jQuery('#cd-ooh-warning');
			if (messages.length) {
				jQuery('#cd-ooh-text').text(messages.join(' | '));
				$w.addClass('is-visible');
			} else {
				$w.removeClass('is-visible');
			}
		}

		function cdOohTotal() {
			var pickSecs = cdGetHourSecs('vrccomselph');
			var dropSecs = cdGetHourSecs('vrccomseldh');
			var total = 0;
			for (var i = 0; i < cdOohFees.length; i++) {
				var f = cdOohFees[i];
				var pickOoh = (f.type === 1 || f.type === 3) && cdIsOoh(pickSecs, f);
				var dropOoh = (f.type === 2 || f.type === 3) && cdIsOoh(dropSecs, f);
				var rowTotal = 0;
				if (pickOoh) rowTotal += parseFloat(f.pickcharge);
				if (dropOoh) rowTotal += parseFloat(f.dropcharge);
				if (f.maxcharge > 0 && rowTotal > f.maxcharge) rowTotal = parseFloat(f.maxcharge);
				total += rowTotal;
			}
			return total;
		}

		function cdToggleOptional(id, cost, perday, maxprice) {
			cdOptionals[id].checked = !cdOptionals[id].checked;
			var $row = jQuery('#cd-opt-row-' + id);
			if (cdOptionals[id].checked) {
				$row.addClass('is-checked');
				jQuery('#cd-opt-input-' + id).val('1');
			} else {
				$row.removeClass('is-checked');
				jQuery('#cd-opt-input-' + id).val('0');
			}
			cdUpdateSummary();
		}

		function cdSetOptionalQty(id, delta) {
			if (!cdOptionals[id]) return;
			var newQty = (cdOptionals[id].qty || 0) + delta;
			if (newQty < 0) newQty = 0;
			cdOptionals[id].qty = newQty;
			cdOptionals[id].checked = (newQty > 0);
			jQuery('#cd-opt-input-' + id).val(newQty);
			jQuery('#cd-opt-qty-' + id).text(newQty);
			var $row = jQuery('#cd-opt-row-' + id);
			if (newQty > 0) { $row.addClass('is-checked'); } else { $row.removeClass('is-checked'); }
			cdUpdateSummary();
		}

		function cdUpdateSummary() {
			var days = cdGetDays();
			var $sum = jQuery('#cd-summary');

			/* ── Update grace notice / exceeded warning ── */
			if (cdGraceHours > 0) {
				var $graceBy  = jQuery('#cd-grace-returnby');
				var $exceeded = jQuery('#cd-grace-exceeded');
				var graceState = (typeof window.cdGraceState !== 'undefined') ? window.cdGraceState : 'none';

				if (days && graceState !== 'none') {
					// Compute grace deadline: pickup + days×24h + graceHours
					var pickStr  = jQuery('#pickupdate').val();
					var pickHour = parseInt(jQuery('#vrccomselph select').val()) || 0;
					var pickTs   = vrcDateToUnixTs(pickStr, pickHour);
					if (pickTs > 0) {
						var graceEndTs = pickTs + (days * 86400) + (cdGraceHours * 3600);
						var graceDate  = new Date(graceEndTs * 1000);
						var gd = graceDate.getUTCDate(), gm = graceDate.getUTCMonth()+1, gy = graceDate.getUTCFullYear();
						var gh = graceDate.getUTCHours();
						var gdStr = (gd<10?'0'+gd:gd)+'/'+(gm<10?'0'+gm:gm)+'/'+gy;
						var gtStr = (gh<10?'0'+gh:gh)+':00';

						if (graceState === 'active') {
							// Show green notice with "return by" hint, hide warning
							var label = cdGraceReturnByLabel.replace('%s', '<strong>' + gdStr + ' ' + gtStr + '</strong>');
							$graceBy.html(label).show();
							$exceeded.removeClass('is-visible');
						} else {
							// graceState === 'exceeded': hide return-by hint, show amber warning
							$graceBy.hide();
							var excLabel = '<?php echo addslashes(Text::_('VRC_GRACE_EXCEEDED_LABEL') ?: 'Perioadă de grație depășită — se adaugă o zi suplimentară'); ?>';
							jQuery('#cd-grace-exc-label').text(excLabel);
							$exceeded.addClass('is-visible');
						}
					}
				} else {
					$graceBy.hide();
					$exceeded.removeClass('is-visible');
				}
			}

			/* ── Update KM notice ── */
			var $kmNotice = jQuery('#cd-km-notice');
			if (days) {
				var totalKm = days * 200;
				jQuery('#cd-km-total').text(totalKm);
				jQuery('#cd-km-days').text(days);
				$kmNotice.show();
			} else {
				$kmNotice.hide();
			}

			if (!days) { $sum.removeClass('is-visible'); cdCheckSavingsTip(null); return; }

			var rate = cdGetRate(days);
			if (!rate) { $sum.removeClass('is-visible'); cdCheckSavingsTip(null); return; }

			// Description sentence (i18n via cdDesc* vars)
			var _dw2 = days === 1 ? cdDescDayWord : cdDescDaysWord;
			var _locId = jQuery('#place').val() || '';
			var _locName = (_locId && typeof cdPlacesMap !== 'undefined' && cdPlacesMap[_locId]) ? cdPlacesMap[_locId] : '';
			var _diffReturn = jQuery('#cd-diff-return-chk').is(':checked');
			var _retLocName = '';
			if (_diffReturn) {
				var _retLocId = jQuery('#returnplace_visible').val();
				_retLocName = (_retLocId && typeof cdPlacesMap !== 'undefined' && cdPlacesMap[_retLocId]) ? cdPlacesMap[_retLocId] : '';
			}

			var _desc = cdDescPrefix + ' <strong>' + days + '\u00A0' + _dw2 + '</strong>';
			if (typeof cdCarName !== 'undefined' && cdCarName) { _desc += cdDescCarInfix + '<strong>' + cdCarName + '</strong>'; }

			if (_diffReturn && _locName && _retLocName) {
				_desc += ' ' + cdDescLocPickup + ' <strong>' + _locName + '</strong>'
					+ ' ' + cdDescLocReturn + ' <strong>' + _retLocName + '</strong>';
			} else if (_locName) {
				_desc += cdDescLocInfix + '<strong>' + _locName + '</strong>';
			}

			_desc += '.';
			jQuery('#cd-summary-desc').html(_desc);

			var baseTotal = rate * days;
			var dayWord = '<?php echo addslashes(Text::_("VRCSEARCHDAYS") ?: "дней"); ?>';
			var rows = '<div class="cd-summary-row"><span><?php echo addslashes(Text::_("VRPRICE") ?: "Preț de bază"); ?></span>'
				+ '<span class="cd-summary-row-val">' + cdCurrency + cdFmt(rate) + ' &times; ' + days + ' ' + dayWord + '</span></div>';

			var optTotal = 0;
			for (var id in cdOptionals) {
				var o = cdOptionals[id];
				var qty = (o.hmany === 1) ? (o.qty || 0) : (o.checked ? 1 : 0);
				if (!qty) continue;
				var oc = o.perday ? o.cost * days * qty : o.cost * qty;
				if (o.max > 0 && oc > o.max) oc = o.max;
				optTotal += oc;
				var name = jQuery('#cd-opt-row-' + id + ' .cd-optional-name').text();
				var qtyLabel = (o.hmany === 1 && qty > 1) ? ' \u00d7 ' + qty : '';
				rows += '<div class="cd-summary-row"><span>' + name + qtyLabel + '</span>'
					+ '<span class="cd-summary-row-val">' + cdCurrency + cdFmt(oc) + '</span></div>';
			}

			var oohTotal = cdOohTotal();
			if (oohTotal > 0) {
				var oohLabel;
				// Determine OOH label from active fees
				for (var i = 0; i < cdOohFees.length; i++) {
					var f = cdOohFees[i];
					var pickSecs = cdGetHourSecs('vrccomselph');
					var dropSecs = cdGetHourSecs('vrccomseldh');
					var pickOoh = (f.type === 1 || f.type === 3) && cdIsOoh(pickSecs, f);
					var dropOoh = (f.type === 2 || f.type === 3) && cdIsOoh(dropSecs, f);
					if (pickOoh || dropOoh) {
						var timeRange = ' (' + f.fromLabel + '\u2013' + f.toLabel + ')';
						if (pickOoh && dropOoh) oohLabel = '<?php echo addslashes(Text::_('VRCPVIEWOOHFEESNINE') ?: "Получение и возврат"); ?>' + timeRange;
						else if (pickOoh) oohLabel = '<?php echo addslashes(Text::_('VRCPVIEWOOHFEESFOUR') ?: "Только получение"); ?>' + timeRange;
						else oohLabel = '<?php echo addslashes(Text::_('VRCPVIEWOOHFEESFIVE') ?: "Только возврат"); ?>' + timeRange;
						break;
					}
				}
				rows += '<div class="cd-summary-row"><span>' + (oohLabel || 'OOH') + '</span>'
					+ '<span class="cd-summary-row-val">' + cdCurrency + cdFmt(oohTotal) + '</span></div>';
			}

			// Coupon discount row
			var couponDiscount = 0;
			if (window.vrcActiveCoupon) {
				var _ac = window.vrcActiveCoupon;
				var _subtotal = baseTotal + optTotal + oohTotal;
				if (_ac.type === 1) {
					couponDiscount = Math.round(_subtotal * parseFloat(_ac.value) / 100);
				} else {
					couponDiscount = Math.min(parseFloat(_ac.value), _subtotal);
				}
				if (couponDiscount > 0) {
					rows += '<div class="cd-summary-row cd-summary-row-discount"><span>' + (_ac.label || 'Reducere') + '</span>'
						+ '<span class="cd-summary-row-val cd-discount-val">\u2212' + cdCurrency + cdFmt(couponDiscount) + '</span></div>';
				}
			}

			var total = baseTotal + optTotal + oohTotal - couponDiscount;
			jQuery('#cd-summary-rows').html(rows);
			jQuery('#cd-summary-total').text(cdCurrency + cdFmt(total));
			$sum.addClass('is-visible');

			cdHighlightTier(days);
			cdCheckSavingsTip(days);
		}

		jQuery(function($) {
			var _lp='', _lr='', _lph='', _ldh='';
			function cdPoll() {
				var p  = $('#pickupdate').val(),  r  = $('#releasedate').val();
				var ph = $('#vrccomselph select').val() || '';
				var dh = $('#vrccomseldh select').val() || '';
				if (p!==_lp || r!==_lr || ph!==_lph || dh!==_ldh) {
					_lp=p; _lr=r; _lph=ph; _ldh=dh;
					cdUpdateSummary();
					cdCheckOoh();
				}
			}
			setInterval(cdPoll, 300);

			$(document.body).on('change', '#vrccomselph select, #vrccomseldh select', function() {
				setTimeout(cdCheckOoh, 50);
				setTimeout(cdUpdateSummary, 50);
			});

			$(document.body).on('click', '.vrc-cdetails-cal-pickday', function() {
				setTimeout(cdUpdateSummary, 400);
			});
		});
		</script>

		<script type="text/javascript">
		function vrcCleanNumber(snum) { if (snum.length > 1 && snum.substr(0,1) == '0') { return parseInt(snum.substr(1)); } return parseInt(snum); }

		/* Convert date string + hour → unix timestamp (seconds) */
		function vrcDateToUnixTs(dateStr, hour) {
			if (!dateStr) return 0;
			var p = dateStr.split('/');
			var y, m, d;
			<?php if ($df === 'd/m/Y'): ?>
			d = parseInt(p[0], 10); m = parseInt(p[1], 10) - 1; y = parseInt(p[2], 10);
			<?php elseif ($df === 'm/d/Y'): ?>
			m = parseInt(p[0], 10) - 1; d = parseInt(p[1], 10); y = parseInt(p[2], 10);
			<?php else: ?>
			y = parseInt(p[0], 10); m = parseInt(p[1], 10) - 1; d = parseInt(p[2], 10);
			<?php endif; ?>
			return Math.floor(Date.UTC(y, m, d, parseInt(hour, 10) || 0, 0, 0) / 1000);
		}

		function vrcValidateSearch() {
			var pickDate = jQuery('#pickupdate').val();
			var relDate  = jQuery('#releasedate').val();
			if (!pickDate || !relDate) {
				return false;
			}

			var pickH = parseInt(jQuery('#vrccomselph select').val(), 10) || 0;
			var relH  = parseInt(jQuery('#vrccomseldh select').val(), 10) || 0;

			var pickTs = vrcDateToUnixTs(pickDate, pickH);
			var relTs  = vrcDateToUnixTs(relDate, relH);

			if (!pickTs || !relTs || relTs <= pickTs) {
				return false;
			}

			var days = Math.round((relTs - pickTs) / 86400);
			jQuery('#vrc-pickup').val(pickTs);
			jQuery('#vrc-release').val(relTs);
			jQuery('#vrc-days').val(days);

			return true;
		}
		jQuery(document).ready(function() {
		<?php if (!empty($ppickup)): ?>
			jQuery("#pickupdate").datepicker("setDate", new Date(<?php echo date('Y', $ppickup); ?>, <?php echo ((int)date('n', $ppickup) - 1); ?>, <?php echo date('j', $ppickup); ?>));
			jQuery(".ui-datepicker-current-day").click();
		<?php endif; ?>
		<?php
		$pday = VikRequest::getInt('dt', '', 'request');
		$viewingdayts = !empty($pday) && $pday >= $nowts ? $pday : $nowts;
		if (!empty($viewingdayts) && !empty($pday) && $viewingdayts >= $nowts) {
			if (!count($push_disabled_in) || !in_array('"' . date('Y-m-d', $viewingdayts) . '"', $push_disabled_in)) { ?>
			jQuery("#pickupdate").datepicker("setDate", new Date(<?php echo date('Y', $viewingdayts); ?>, <?php echo ((int)date('n', $viewingdayts) - 1); ?>, <?php echo date('j', $viewingdayts); ?>));
			<?php } ?>
			if (jQuery("#vrc-bookingpart-init").length) { jQuery('html,body').animate({ scrollTop: (jQuery("#vrc-bookingpart-init").offset().top - 5) }, { duration: 'slow' }); }
		<?php } ?>
			jQuery(document.body).on('click', '.vrc-cdetails-cal-pickday', function() {
				if (!jQuery("#pickupdate").length) return;
				var tdday = jQuery(this).attr('data-daydate');
				if (!tdday || !tdday.length) return;
				jQuery('#pickupdate').datepicker('setDate', tdday);
				if (jQuery("#vrc-bookingpart-init").length) {
					jQuery('html,body').animate({ scrollTop: (jQuery('#vrc-bookingpart-init').offset().top - 5) }, 600, function() {
						if (typeof vrcSetMinDropoffDate !== "undefined") vrcSetMinDropoffDate();
						if (typeof vrcLocationWopening !== "undefined") vrcLocationWopening('pickup');
						jQuery('#releasedate').focus();
					});
				}
			});
			// Default pickup: find the first available date (and a valid dropoff after it)
			(function() {
				if (!jQuery('#pickupdate').val()) {
					var minAdvance  = Math.max(1, <?php echo (int)VikRentCar::getMinDaysAdvance(); ?>);
					var minLos      = <?php echo max(1, intval($def_min_los) > 0 ? intval($def_min_los) : 1); ?>;
					var maxLookAhead = 365; // never scan more than a year ahead

					function isPickupAvailable(date) {
						var validator = (typeof vrcIsDayDisabled !== 'undefined')
							? vrcIsDayDisabled
							: (typeof vrcIsDayFullIn !== 'undefined' ? vrcIsDayFullIn : null);
						if (!validator) return true;
						try {
							var result = validator(date);
							return !!(result && result[0]);
						} catch(e) { return true; }
					}

					function isDropoffAvailable(date) {
						var validator = (typeof vrcIsDayDisabledDropoff !== 'undefined')
							? vrcIsDayDisabledDropoff
							: (typeof vrcIsDayFullOut !== 'undefined' ? vrcIsDayFullOut : null);
						if (!validator) return true;
						try {
							var result = validator(date);
							return !!(result && result[0]);
						} catch(e) { return true; }
					}

					function findNearestAvailableDates() {
						var candidate = new Date();
						candidate.setHours(0, 0, 0, 0);
						candidate.setDate(candidate.getDate() + minAdvance);

						for (var i = 0; i < maxLookAhead; i++) {
							if (isPickupAvailable(candidate)) {
								var dropCandidate = new Date(candidate.getTime());
								dropCandidate.setDate(dropCandidate.getDate() + minLos);

								for (var j = 0; j < 30; j++) {
									if (isDropoffAvailable(dropCandidate)) {
										return { pickup: candidate, dropoff: dropCandidate };
									}
									dropCandidate.setDate(dropCandidate.getDate() + 1);
								}
							}
							candidate.setDate(candidate.getDate() + 1);
						}
						return null;
					}

					var dates = findNearestAvailableDates();
					if (dates) {
						try{ jQuery('#pickupdate').datepicker('setDate', dates.pickup); }catch(e){}
						try{ jQuery('#releasedate').datepicker('setDate', dates.dropoff); }catch(e){}
						if (typeof vrcSetMinDropoffDate !== 'undefined') { vrcSetMinDropoffDate(); }
					}
					setTimeout(cdUpdateSummary, 200);
				}
				jQuery('#vrccomselph select').val(12).trigger('chosen:updated');
				jQuery('#vrccomseldh select').val(12).trigger('chosen:updated');
			})();
		});
		</script>

	<?php
	} else {
		echo '<div class="cd-disabled-rent">' . VikRentCar::getDisabledRentMsg() . '</div>';
	}
	?>


</div><!-- /.cd-booking-card -->



	<div class="cd-mobile-info-wrap" style="margin-top:16px;">
		<div class="cd-info">
			<?php if (!empty($categoryName)): ?><span class="cd-car-cat"><?php echo $categoryName; ?></span><?php endif; ?>
			<h1 class="cd-car-name"><?php echo htmlspecialchars($car['name']); ?> — <?php echo Text::_('VRCTITLECARDESCR'); ?></h1>
			<?php if (!empty($caratDefs)): ?>
			<div class="cd-specs">
			<?php foreach ($caratDefs as $cid => $carat):
					$rawLabel = !empty($carat['textimg']) ? $carat['textimg'] : $carat['name'];
					$label = Text::_($rawLabel) ?: $rawLabel;
					$key = isset($caratOrigKeys[$cid]) ? $caratOrigKeys[$cid] : strtolower($label);
					$svg = $svgDefault;
					foreach ($svgIcons as $kw => $is) { if (strpos($key, $kw) !== false) { $svg = $is; break; } }
				?>
				<div class="cd-spec"><div class="cd-spec-icon"><?php echo $svg; ?></div><div class="cd-spec-text"><span class="cd-spec-value"><?php echo htmlspecialchars($label); ?></span></div></div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if (isset($car_params['reqinfo']) && (bool)$car_params['reqinfo']): ?>
			<a href="javascript:void(0);" onclick="vrcShowRequestInfo();" class="cd-reqinfo-btn"><i class="fas fa-envelope"></i> <?php echo Text::_('VRCCARREQINFOBTN'); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<!-- Description (mobile order 5) -->
	<?php if (!empty($car['info'])): ?>
	<div class="cd-description" style="margin-top:24px;">
		<div class="cd-description-text"><?php echo $car['info']; ?></div>
	</div>
	<?php endif; ?>
</div>



</div><!-- /.cd-right -->

</div><!-- /.cd-page-grid -->


<?php
/* ── Recommended Cars (similar price ±30%) ─────────────────────── */
$recommendedCars = array();
try {
    $_refPrice = strlen($car['startfrom']) > 0
        ? (float)$car['startfrom']
        : (float)$car['cost'];

    if ($_refPrice > 0) {
        $_priceMin = $_refPrice * 0.70;
        $_priceMax = $_refPrice * 1.30;
        $_dboRec = JFactory::getDbo();
		$_dboRec->setQuery(
			"SELECT `id`, `name`, `img`, `startfrom`, `alias`"
			. " FROM `#__vikrentcar_cars`"
			. " WHERE `avail` = 1"
			. " AND `id` != " . (int)$car['id']
			. " AND CHAR_LENGTH(`startfrom`) > 0"
			. " AND CAST(`startfrom` AS DECIMAL(10,2)) > 0"
			. " AND CAST(`startfrom` AS DECIMAL(10,2))"
			.     " BETWEEN " . (float)$_priceMin . " AND " . (float)$_priceMax
			. " ORDER BY ABS(CAST(`startfrom` AS DECIMAL(10,2)) - " . (float)$_refPrice . ") ASC"
			. " LIMIT 3"
		);
        $recommendedCars = (array)$_dboRec->loadAssocList();
    }
} catch (Exception $_eRec) {
    $recommendedCars = array();
}
?>

<?php if (!empty($recommendedCars)) : ?>
<div class="cd-recommended-section">
    <h2 class="cd-recommended-title">Mașini recomandate</h2>
    <div class="cd-recommended-grid">
        <?php foreach ($recommendedCars as $_rec) :
    $_recImg = !empty($_rec['img'])
        ? JURI::root() . 'administrator/components/com_vikrentcar/resources/' . $_rec['img']
        : '';
    $_recPrice = (float)$_rec['startfrom'];
    $_recPriceDisp = floor($_recPrice) == $_recPrice
        ? (int)$_recPrice
        : VikRentCar::numberFormat($_recPrice);
$_recSlug = !empty($_rec['alias']) ? $_rec['alias'] : (int)$_rec['id'];

$_baseCarUrl = str_replace('/' . $car['alias'], '', JURI::current());
$_recUrl = rtrim($_baseCarUrl, '/') . '/' . $_rec['alias'];

?>
        <a href="<?php echo $_recUrl; ?>" class="cd-rec-card">
            <div class="cd-rec-img-wrap">
                <?php if ($_recImg) : ?>
                <img src="<?php echo htmlspecialchars($_recImg); ?>"
                     alt="<?php echo htmlspecialchars($_rec['name']); ?>"
                     loading="lazy">
                <?php else : ?>
                <div class="cd-rec-img-placeholder"></div>
                <?php endif; ?>
            </div>
            <div class="cd-rec-info">
                <div class="cd-rec-name"><?php echo htmlspecialchars($_rec['name']); ?></div>
                <div class="cd-rec-price">
                    <span class="cd-rec-from">De la</span>
                    <span class="cd-rec-amount"><?php echo $currencysymb . $_recPriceDisp; ?></span>
                    <span class="cd-rec-per">/zi</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>


</div><!-- /.cd-container -->

<?php /* Request info modal */
if (isset($car_params['reqinfo']) && (bool)$car_params['reqinfo']):
	$cur_user = JFactory::getUser();
	$cur_email = (property_exists($cur_user, 'email') && !empty($cur_user->email)) ? $cur_user->email : '';
?>
<div id="vrcdialog-overlay" style="display: none;">
	<a class="vrcdialog-overlay-close" href="javascript:void(0);"></a>
	<div class="vrcdialog-inner vrcdialog-reqinfo">
		<h3><?php echo Text::sprintf('VRCCARREQINFOTITLE', $car['name']); ?></h3>
		<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=reqinfo'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post" onsubmit="return vrcValidateReqInfo();">
			<input type="hidden" name="carid" value="<?php echo $car['id']; ?>" />
			<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>" />
			<div class="vrcdialog-reqinfo-formcont">
				<div class="vrcdialog-reqinfo-formentry">
					<label for="reqname"><?php echo Text::_('VRCCARREQINFONAME'); ?></label>
					<input type="text" name="reqname" id="reqname" value="" placeholder="<?php echo Text::_('VRCCARREQINFONAME'); ?>" />
				</div>
				<div class="vrcdialog-reqinfo-formentry">
					<label for="reqemail"><?php echo Text::_('VRCCARREQINFOEMAIL'); ?></label>
					<input type="text" name="reqemail" id="reqemail" value="<?php echo $cur_email; ?>" placeholder="<?php echo Text::_('VRCCARREQINFOEMAIL'); ?>" />
				</div>
				<div class="vrcdialog-reqinfo-formentry">
					<label for="reqmess"><?php echo Text::_('VRCCARREQINFOMESS'); ?></label>
					<textarea name="reqmess" id="reqmess" placeholder="<?php echo Text::_('VRCCARREQINFOMESS'); ?>"></textarea>
				</div>
				<?php
				if (count($this->terms_fields)) {
					$fname = !empty($this->terms_fields['poplink'])
						? '<a href="'.$this->terms_fields['poplink'].'" target="_blank" class="vrcmodalframe">'.Text::_($this->terms_fields['name']).'</a>'
						: '<label for="vrcf-inp" style="display:inline-block;">'.Text::_($this->terms_fields['name']).'</label>';
				?>
				<div class="vrcdialog-reqinfo-formentry vrcdialog-reqinfo-formentry-ckbox">
					<?php echo $fname; ?>
					<input type="checkbox" name="vrcf" id="vrcf-inp" value="<?php echo Text::_('VRYES'); ?>"/>
				</div>
				<?php } else { ?>
				<div class="vrcdialog-reqinfo-formentry vrcdialog-reqinfo-formentry-ckbox">
					<label for="vrcf-inp" style="display:inline-block;"><?php echo Text::_('ORDER_TERMSCONDITIONS'); ?></label>
					<input type="checkbox" name="vrcf" id="vrcf-inp" value="<?php echo Text::_('VRYES'); ?>"/>
				</div>
				<?php }
				if ($vrc_app->isCaptcha()) { ?>
				<div class="vrcdialog-reqinfo-formentry">
					<div><?php echo $vrc_app->reCaptcha(); ?></div>
				</div>
				<?php } ?>
				<div class="vrcdialog-reqinfo-formentry vrcdialog-reqinfo-formsubmit">
					<button type="submit" class="btn vrc-pref-color-btn"><?php echo Text::_('VRCCARREQINFOSEND'); ?></button>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
var vrcdialog_on = false;
function vrcShowRequestInfo() { jQuery("#vrcdialog-overlay").fadeIn(); vrcdialog_on = true; }
function vrcHideRequestInfo() { jQuery("#vrcdialog-overlay").fadeOut(); vrcdialog_on = false; }
jQuery(function() {
	jQuery(document).mouseup(function(e) { if (!vrcdialog_on) return false; var c = jQuery(".vrcdialog-inner"); if (!c.is(e.target) && c.has(e.target).length === 0) vrcHideRequestInfo(); });
	jQuery(document).keyup(function(e) { if (e.keyCode == 27 && vrcdialog_on) vrcHideRequestInfo(); });
});
function vrcValidateReqInfo() { if (document.getElementById('vrcf-inp').checked) return true; alert('<?php echo addslashes(Text::_('VRFILLALL')); ?>'); return false; }
</script>
<?php endif; ?>

<script type="text/javascript">
/* Gallery */
var cdAllImages = <?php echo json_encode(array_map(function($img) { return $img['big']; }, !empty($allImages) ? $allImages : array())); ?>;
function cdSetImage(idx) {
	if (idx >= cdAllImages.length) return;
	var mainEl = document.getElementById('cd-main-img-el');
	var mainLink = document.getElementById('cd-main-link');
	if (mainEl) mainEl.src = cdAllImages[idx];
	if (mainLink) mainLink.href = cdAllImages[idx];
	document.querySelectorAll('.cd-thumb').forEach(function(t) { t.classList.remove('active'); });
	var thumb = document.querySelector('.cd-thumb[data-idx="' + idx + '"]');
	if (thumb) thumb.classList.add('active');
}
</script>

<?php if (!empty($priceTiers)): ?>
<script type="text/javascript">
(function($) {
	window.cdTiers = <?php
		$_jsRanges = array();
		foreach ($priceTiers as $_t) { $_jsRanges[] = array('from' => (int)$_t['from'], 'to' => (int)$_t['to'], 'rate' => (int)$_t['rate']); }
		echo json_encode($_jsRanges);
	?>;

	window.cdHighlightTier = function(days) {
		var $cells = $('#cd-price-tiers .cd-price-tier');
		$cells.removeClass('is-active is-active-prev');
		if (!days) return;
		var activeIdx = -1;
		for (var i = 0; i < cdTiers.length; i++) {
			if (days >= cdTiers[i].from && days <= cdTiers[i].to) { activeIdx = i; break; }
		}
		if (activeIdx === -1 && days > 0) { activeIdx = cdTiers.length - 1; }
		if (activeIdx >= 0) {
			$cells.eq(activeIdx).addClass('is-active');
			if (activeIdx > 0) { $cells.eq(activeIdx - 1).addClass('is-active-prev'); }
			// Scroll active tier into center on mobile
			var wrap = document.getElementById('cd-price-tiers');
			if (wrap) {
				var el = $cells.eq(activeIdx).get(0);
				if (el) {
					var scrollTo = el.offsetLeft - (wrap.offsetWidth - el.offsetWidth) / 2;
					wrap.scrollTo({ left: scrollTo, behavior: 'smooth' });
				}
			}
		}
	};

	window.cdCheckSavingsTip = function(days) {
		var tipEl = document.getElementById('cd-savings-tip');
		if (!tipEl) return;
		if (!days || !window.cdTiers || !cdTiers.length) {
			tipEl.style.display = 'none';
			return;
		}

		// Find which tier the current day count falls in
		var currentTierIdx = -1;
		for (var i = 0; i < cdTiers.length; i++) {
			if (days >= cdTiers[i].from && days <= cdTiers[i].to) {
				currentTierIdx = i;
				break;
			}
		}

		// No match or already in the last tier
		if (currentTierIdx === -1 || currentTierIdx >= cdTiers.length - 1) {
			tipEl.style.display = 'none';
			return;
		}

		var nextTier = cdTiers[currentTierIdx + 1];

		// Only show when exactly 1 day away from next tier boundary
		if (days + 1 !== nextTier.from) {
			tipEl.style.display = 'none';
			return;
		}

		var currentRate = cdGetRate(days);
		var nextRate = nextTier.rate;
		var currentTotal = currentRate * nextTier.from;  // same days as nextTier for fair comparison
		var nextTotal = nextRate * nextTier.from;
		var savings = Math.round(currentTotal - nextTotal);

		if (savings <= 0) { tipEl.style.display = 'none'; return; }

		tipEl.querySelector('.cd-tip-days').textContent     = nextTier.from;
		tipEl.querySelector('.cd-tip-savings').textContent  = cdCurrency + cdFmt(savings);
		tipEl.querySelector('.cd-tip-newtotal').textContent = cdCurrency + cdFmt(Math.round(nextTotal));
		tipEl.querySelector('.cd-tip-oldtotal').textContent = cdCurrency + cdFmt(Math.round(currentTotal));
		tipEl.style.display = 'flex';
	};
})(jQuery);
</script>
<?php endif; ?>

<?php
// ── Booking modal overlay ────────────────────────────────────────────────────
$_oconfirmUrl = JRoute::_(
	'index.php?option=com_vikrentcar&task=oconfirm' . (!empty($pitemid) ? '&Itemid=' . $pitemid : ''),
	false
);
?>
<!-- ═══ Booking Modal Overlay ═══ -->
<div id="vrc-booking-modal-overlay"
     data-oconfirm-url="<?php echo htmlspecialchars($_oconfirmUrl); ?>">
	<div id="vrc-booking-modal">
		<button id="vrc-booking-modal-close" type="button" aria-label="Închide">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
			     fill="none" stroke="currentColor" stroke-width="2.5"
			     stroke-linecap="round" stroke-linejoin="round">
				<line x1="18" y1="6" x2="6" y2="18"/>
				<line x1="6" y1="6" x2="18" y2="18"/>
			</svg>
		</button>
		<div class="vrc-booking-modal-loading" id="vrc-booking-modal-loading">
			<div class="vrc-booking-modal-spinner"></div>
			<span><?php echo Text::_('VRC_LOADING_BOOKING') ?: 'Se încarcă...'; ?></span>
		</div>
		<iframe id="vrc-booking-modal-iframe"
		        src="about:blank"
		        frameborder="0"
		        scrolling="auto"
		        onload="document.getElementById('vrc-booking-modal-loading').style.display='none';"
		        title="<?php echo Text::_('VRCBOOKTHISCAR'); ?>">
		</iframe>
	</div>
</div>

<?php VikRentCar::printTrackingCode(isset($this) ? $this : null); ?>