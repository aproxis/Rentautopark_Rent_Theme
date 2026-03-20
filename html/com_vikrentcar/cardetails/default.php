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
$document->addStyleSheet(JURI::root() . 'templates/rent/css/cardetails.css');
JHtml::_('script', VRC_SITE_URI . 'resources/jquery.fancybox.js');

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

<?php /* Breadcrumb */ ?>
<div class="cd-breadcrumb">
	<div class="cd-breadcrumb-inner">
		<a href="<?php echo JURI::root(); ?>"><?php echo Text::_('VRCBCHOME') ?: 'Acasă'; ?></a>
		<span class="cd-bc-sep">/</span>
		<a href="<?php echo $carslistUrl; ?>"><?php echo Text::_('VRCBCCARS') ?: 'Automobile'; ?></a>
		<span class="cd-bc-sep">/</span>
		<span class="cd-bc-current"><?php echo $car['name']; ?></span>
	</div>
</div>

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
					elseif ($_isLast && $_toN >= 45) { $_dl = '> ' . ($_fromN - 1) . ' ' . $_dw; }
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

	<!-- Desktop meta: category, name, spec pills -->
	<div class="cd-desktop-meta">
		<?php if (!empty($categoryName)): ?><span class="cd-car-cat-desktop"><?php echo $categoryName; ?></span><?php endif; ?>
		<h1 class="cd-car-name-desktop"><?php echo $car['name']; ?></h1>
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
				$key = strtolower($label); $svg = $svgDefault;
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
			<h1 class="cd-car-name"><?php echo $car['name']; ?></h1>
			<?php if (!empty($caratDefs)): ?>
			<div class="cd-specs">
				<?php foreach ($caratDefs as $cid => $carat):
					$rawLabel = !empty($carat['textimg']) ? $carat['textimg'] : $carat['name'];
					$label = Text::_($rawLabel) ?: $rawLabel;
					$key = strtolower($label); $svg = $svgDefault;
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
		<h2><?php echo Text::_('VRCDESCRIPTION') ?: 'Описание'; ?></h2>
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
<div class="cd-booking-card">
	<h3 class="cd-booking-title"><?php echo Text::_('VRCBOOKTHISCAR') ?: 'Забронировать'; ?></h3>

<?php
$_ico_chev_sm = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="cd-arrow"><path d="m6 9 6 6 6-6"></path></svg>';

if (VikRentCar::allowRent()) {
	$dbo = JFactory::getDbo();
	$calendartype = VikRentCar::calendarType();
	$indvrcplace = 0;
	$indvrcreturnplace = 0;
	$restrictions = VikRentCar::loadRestrictions(true, array($car['id']));
	$def_min_los = VikRentCar::setDropDatePlus();

	if ($calendartype == "jqueryui") {
		$document->addStyleSheet(VRC_SITE_URI . 'resources/jquery-ui.min.css');
		JHtml::_('script', VRC_SITE_URI . 'resources/jquery-ui.min.js');
	}

	$ptmpl   = VikRequest::getString('tmpl', '', 'request');
	$ppickup = VikRequest::getInt('pickup', 0, 'request');
	$ppromo  = VikRequest::getInt('promo', 0, 'request');
	$coordsplaces = array();

	$places = '';
	$diffopentime = false;
	$closingdays = array();
	$declclosingdays = '';
	$plapick_ids = array();
	$pladrop_ids = array();

	if (VikRentCar::showPlacesFront()) {
		$q = "SELECT * FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`ordering` ASC, `#__vikrentcar_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$places = $dbo->loadAssocList();
			$vrc_tn->translateContents($places, '#__vikrentcar_places');
			$plapick_ids = explode(';', $car['idplace']);
			$pladrop_ids = explode(';', $car['idretplace']);
			foreach ($places as $kpla => $pla) {
				if (!in_array($pla['id'], $plapick_ids) && !in_array($pla['id'], $pladrop_ids)) {
					unset($places[$kpla]);
				}
			}
			if (count($places) == 0) { $places = ''; }
		} else { $places = ''; }

		if (is_array($places)) {
			foreach ($places as $kpla => $pla) {
				if (!empty($pla['opentime'])) { $diffopentime = true; }
				if (!empty($pla['closingdays'])) { $closingdays[$pla['id']] = $pla['closingdays']; }
				if (empty($indvrcplace) && !isset($places[$indvrcplace])) {
					$indvrcplace = $kpla;
					$indvrcreturnplace = $kpla;
				}
			}
			$wopening_pick = array();
			if (isset($places[$indvrcplace]) && !empty($places[$indvrcplace]['wopening'])) {
				$wopening_pick = json_decode($places[$indvrcplace]['wopening'], true);
				$wopening_pick = !is_array($wopening_pick) ? array() : $wopening_pick;
			}
			$wopening_drop = array();
			if (isset($places[$indvrcreturnplace]) && !empty($places[$indvrcreturnplace]['wopening'])) {
				$wopening_drop = json_decode($places[$indvrcreturnplace]['wopening'], true);
				$wopening_drop = !is_array($wopening_drop) ? array() : $wopening_drop;
			}
			if (count($closingdays) > 0) {
				foreach ($closingdays as $idpla => $clostr) {
					$jsclosingdstr = VikRentCar::formatLocationClosingDays($clostr);
					if (count($jsclosingdstr) > 0) {
						$declclosingdays .= 'var loc' . $idpla . 'closingdays = [' . implode(", ", $jsclosingdstr) . '];' . "\n";
					}
				}
			}
			if ($diffopentime) {
				$ajaxUrl = method_exists('VikRentCar', 'ajaxUrl')
					? VikRentCar::ajaxUrl(JRoute::_('index.php?option=com_vikrentcar&task=ajaxlocopentime&tmpl=component' . (!empty($pitemid) ? '&Itemid=' . $pitemid : ''), false))
					: JRoute::_('index.php?option=com_vikrentcar&task=ajaxlocopentime&tmpl=component', false);
				$onchangedecl = '
var vrc_location_change = false;
var vrc_wopening_pick = ' . json_encode($wopening_pick) . ';
var vrc_wopening_drop = ' . json_encode($wopening_drop) . ';
var vrc_hopening_pick = null;
var vrc_hopening_drop = null;
var vrc_mopening_pick = null;
var vrc_mopening_drop = null;
function vrcSetLocOpenTime(loc, where) {
	if (where == "dropoff") { vrc_location_change = true; }
	jQuery.ajax({
		type: "POST",
		url: "' . $ajaxUrl . '",
		data: { idloc: loc, pickdrop: where }
	}).done(function(res) {
		var vrcobj = JSON.parse(res);
		if (where == "pickup") {
			jQuery("#vrccomselph").html(vrcobj.hours);
			jQuery("#vrccomselpm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) { vrc_wopening_pick = vrcobj.wopening; vrc_hopening_pick = vrcobj.hours; }
		} else {
			jQuery("#vrccomseldh").html(vrcobj.hours);
			jQuery("#vrccomseldm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) { vrc_wopening_drop = vrcobj.wopening; vrc_hopening_drop = vrcobj.hours; }
		}
		if (typeof vrcLocationWopening !== "undefined") { vrcLocationWopening(where); }
		if (where == "pickup" && vrc_location_change === false) {
			jQuery("#returnplace").val(loc).trigger("change");
			vrc_location_change = false;
		}
	});
}';
				$document->addScriptDeclaration($onchangedecl);
			}
		}
	}

	/* ── Hours select (single, :00 minutes hidden) ──────────────── */
	if ($diffopentime && is_array($places) && isset($places[$indvrcplace]) && !empty($places[$indvrcplace]['opentime'])) {
		$parts = explode("-", $places[$indvrcplace]['opentime']);
		if (is_array($parts) && $parts[0] != $parts[1]) {
			$opent  = VikRentCar::getHoursMinutes($parts[0]);
			$closet = VikRentCar::getHoursMinutes($parts[1]);
			$i = $opent[0]; $j = $closet[0];
		} else { $i = 0; $j = 23; }
	} else {
		$timeopst = VikRentCar::getTimeOpenStore();
		if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
			$opent  = VikRentCar::getHoursMinutes($timeopst[0]);
			$closet = VikRentCar::getHoursMinutes($timeopst[1]);
			$i = $opent[0]; $j = $closet[0];
		} else { $i = 0; $j = 23; }
	}

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

	/* ── jQuery UI datepicker locale & restrictions JS ──────────── */
	if ($vrcdateformat == "%d/%m/%Y") { $juidf = 'dd/mm/yy'; }
	elseif ($vrcdateformat == "%m/%d/%Y") { $juidf = 'mm/dd/yy'; }
	else { $juidf = 'yy/mm/dd'; }

	$ldecl = '
jQuery(function($){
	$.datepicker.regional["vikrentcar"] = {
		closeText: "' . Text::_('VRCJQCALDONE') . '",
		prevText: "' . Text::_('VRCJQCALPREV') . '",
		nextText: "' . Text::_('VRCJQCALNEXT') . '",
		currentText: "' . Text::_('VRCJQCALTODAY') . '",
		monthNames: ["' . Text::_('VRMONTHONE') . '","' . Text::_('VRMONTHTWO') . '","' . Text::_('VRMONTHTHREE') . '","' . Text::_('VRMONTHFOUR') . '","' . Text::_('VRMONTHFIVE') . '","' . Text::_('VRMONTHSIX') . '","' . Text::_('VRMONTHSEVEN') . '","' . Text::_('VRMONTHEIGHT') . '","' . Text::_('VRMONTHNINE') . '","' . Text::_('VRMONTHTEN') . '","' . Text::_('VRMONTHELEVEN') . '","' . Text::_('VRMONTHTWELVE') . '"],
		monthNamesShort: ["' . mb_substr(Text::_('VRMONTHONE'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHTWO'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHTHREE'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHFOUR'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHFIVE'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHSIX'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHSEVEN'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHEIGHT'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHNINE'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHTEN'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHELEVEN'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRMONTHTWELVE'),0,3,'UTF-8') . '"],
		dayNames: ["' . Text::_('VRCJQCALSUN') . '","' . Text::_('VRCJQCALMON') . '","' . Text::_('VRCJQCALTUE') . '","' . Text::_('VRCJQCALWED') . '","' . Text::_('VRCJQCALTHU') . '","' . Text::_('VRCJQCALFRI') . '","' . Text::_('VRCJQCALSAT') . '"],
		dayNamesShort: ["' . mb_substr(Text::_('VRCJQCALSUN'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALMON'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALTUE'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALWED'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALTHU'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALFRI'),0,3,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALSAT'),0,3,'UTF-8') . '"],
		dayNamesMin: ["' . mb_substr(Text::_('VRCJQCALSUN'),0,2,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALMON'),0,2,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALTUE'),0,2,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALWED'),0,2,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALTHU'),0,2,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALFRI'),0,2,'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALSAT'),0,2,'UTF-8') . '"],
		weekHeader: "' . Text::_('VRCJQCALWKHEADER') . '",
		dateFormat: "' . $juidf . '",
		firstDay: ' . VikRentCar::getFirstWeekDay() . ',
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ""
	};
	$.datepicker.setDefaults($.datepicker.regional["vikrentcar"]);
});
function vrcGetDateObject(dstring) { var dparts = dstring.split("-"); return new Date(dparts[0], (parseInt(dparts[1]) - 1), parseInt(dparts[2]), 0, 0, 0, 0); }
function vrcFullObject(obj) { var jk; for(jk in obj) { return obj.hasOwnProperty(jk); } }
var vrcrestrctarange, vrcrestrctdrange, vrcrestrcta, vrcrestrctd;';
	$document->addScriptDeclaration($ldecl);

	/* ── Restrictions ───────────────────────────────────────────── */
	$totrestrictions = count($restrictions);
	$wdaysrestrictions = array(); $wdaystworestrictions = array(); $wdaysrestrictionsrange = array();
	$wdaysrestrictionsmonths = array(); $ctarestrictionsrange = array(); $ctarestrictionsmonths = array();
	$ctdrestrictionsrange = array(); $ctdrestrictionsmonths = array(); $monthscomborestr = array();
	$minlosrestrictions = array(); $minlosrestrictionsrange = array();
	$maxlosrestrictions = array(); $maxlosrestrictionsrange = array(); $notmultiplyminlosrestrictions = array();

	if ($totrestrictions > 0) {
		foreach ($restrictions as $rmonth => $restr) {
			if ($rmonth != 'range') {
				if (strlen($restr['wday']) > 0) {
					$wdaysrestrictions[] = "'" . ($rmonth - 1) . "': '" . $restr['wday'] . "'";
					$wdaysrestrictionsmonths[] = $rmonth;
					if (strlen($restr['wdaytwo']) > 0) {
						$wdaystworestrictions[] = "'" . ($rmonth - 1) . "': '" . $restr['wdaytwo'] . "'";
						$monthscomborestr[($rmonth - 1)] = VikRentCar::parseJsDrangeWdayCombo($restr);
					}
				} elseif (!empty($restr['ctad']) || !empty($restr['ctdd'])) {
					if (!empty($restr['ctad'])) { $ctarestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctad']); }
					if (!empty($restr['ctdd'])) { $ctdrestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctdd']); }
				}
				if ($restr['multiplyminlos'] == 0) { $notmultiplyminlosrestrictions[] = $rmonth; }
				$minlosrestrictions[] = "'" . ($rmonth - 1) . "': '" . $restr['minlos'] . "'";
				if (!empty($restr['maxlos']) && $restr['maxlos'] > 0 && $restr['maxlos'] > $restr['minlos']) {
					$maxlosrestrictions[] = "'" . ($rmonth - 1) . "': '" . $restr['maxlos'] . "'";
				}
			} else {
				foreach ($restr as $kr => $drestr) {
					if (strlen($drestr['wday']) > 0) {
						$wdaysrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']); $wdaysrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
						$wdaysrestrictionsrange[$kr][2] = $drestr['wday']; $wdaysrestrictionsrange[$kr][3] = $drestr['multiplyminlos'];
						$wdaysrestrictionsrange[$kr][4] = strlen($drestr['wdaytwo']) > 0 ? $drestr['wdaytwo'] : -1;
						$wdaysrestrictionsrange[$kr][5] = VikRentCar::parseJsDrangeWdayCombo($drestr);
					} elseif (!empty($drestr['ctad']) || !empty($drestr['ctdd'])) {
						$ctfrom = date('Y-m-d', $drestr['dfrom']); $ctto = date('Y-m-d', $drestr['dto']);
						if (!empty($drestr['ctad'])) { $ctarestrictionsrange[$kr] = array($ctfrom, $ctto, explode(',', $drestr['ctad'])); }
						if (!empty($drestr['ctdd'])) { $ctdrestrictionsrange[$kr] = array($ctfrom, $ctto, explode(',', $drestr['ctdd'])); }
					}
					$minlosrestrictionsrange[$kr] = array(date('Y-m-d', $drestr['dfrom']), date('Y-m-d', $drestr['dto']), $drestr['minlos']);
					if (!empty($drestr['maxlos']) && $drestr['maxlos'] > 0 && $drestr['maxlos'] > $drestr['minlos']) {
						$maxlosrestrictionsrange[$kr] = $drestr['maxlos'];
					}
				}
				unset($restrictions['range']);
			}
		}

		$resdecl = "
var vrcrestrmonthswdays = [" . implode(", ", $wdaysrestrictionsmonths) . "];
var vrcrestrmonths = [" . implode(", ", array_keys($restrictions)) . "];
var vrcrestrmonthscombojn = JSON.parse('" . json_encode($monthscomborestr) . "');
var vrcrestrminlos = {" . implode(", ", $minlosrestrictions) . "};
var vrcrestrminlosrangejn = JSON.parse('" . json_encode($minlosrestrictionsrange) . "');
var vrcrestrmultiplyminlos = [" . implode(", ", $notmultiplyminlosrestrictions) . "];
var vrcrestrmaxlos = {" . implode(", ", $maxlosrestrictions) . "};
var vrcrestrmaxlosrangejn = JSON.parse('" . json_encode($maxlosrestrictionsrange) . "');
var vrcrestrwdaysrangejn = JSON.parse('" . json_encode($wdaysrestrictionsrange) . "');
var vrcrestrcta = JSON.parse('" . json_encode($ctarestrictionsmonths) . "');
var vrcrestrctarange = JSON.parse('" . json_encode($ctarestrictionsrange) . "');
var vrcrestrctd = JSON.parse('" . json_encode($ctdrestrictionsmonths) . "');
var vrcrestrctdrange = JSON.parse('" . json_encode($ctdrestrictionsrange) . "');
var vrccombowdays = {};
function vrcRefreshDropoff(darrive) {
	if (vrcFullObject(vrccombowdays)) { var vrctosort = new Array(); for(var vrci in vrccombowdays) { if (vrccombowdays.hasOwnProperty(vrci)) { var vrcusedate = darrive; vrctosort[vrci] = vrcusedate.setDate(vrcusedate.getDate() + (vrccombowdays[vrci] - 1 - vrcusedate.getDay() + 7) % 7 + 1); } } vrctosort.sort(function(da, db) { return da > db ? 1 : -1; }); for(var vrcnext in vrctosort) { if (vrctosort.hasOwnProperty(vrcnext)) { var vrcfirstnextd = new Date(vrctosort[vrcnext]); jQuery('#releasedate').datepicker('option','minDate',vrcfirstnextd); jQuery('#releasedate').datepicker('setDate',vrcfirstnextd); break; } } }
}
var vrcDropMaxDateSet = false;
function vrcSetMinDropoffDate() {
	var vrcDropMaxDateSetNow = false; var minlos = " . (intval($def_min_los) > 0 ? $def_min_los : '0') . "; var maxlosrange = 0;
	var nowpickup = jQuery('#pickupdate').datepicker('getDate'); var nowd = nowpickup.getDay(); var nowpickupdate = new Date(nowpickup.getTime()); vrccombowdays = {};
	if (vrcFullObject(vrcrestrminlosrangejn)) { for (var rk in vrcrestrminlosrangejn) { if (vrcrestrminlosrangejn.hasOwnProperty(rk)) { var minldrangeinit = vrcGetDateObject(vrcrestrminlosrangejn[rk][0]); if (nowpickupdate >= minldrangeinit) { var minldrangeend = vrcGetDateObject(vrcrestrminlosrangejn[rk][1]); if (nowpickupdate <= minldrangeend) { minlos = parseInt(vrcrestrminlosrangejn[rk][2]); if (vrcFullObject(vrcrestrmaxlosrangejn)) { if (rk in vrcrestrmaxlosrangejn) { maxlosrange = parseInt(vrcrestrmaxlosrangejn[rk]); } } if (rk in vrcrestrwdaysrangejn && nowd in vrcrestrwdaysrangejn[rk][5]) { vrccombowdays = vrcrestrwdaysrangejn[rk][5][nowd]; } } } } } }
	var nowm = nowpickup.getMonth();
	if (vrcFullObject(vrcrestrmonthscombojn) && vrcrestrmonthscombojn.hasOwnProperty(nowm)) { if (nowd in vrcrestrmonthscombojn[nowm]) { vrccombowdays = vrcrestrmonthscombojn[nowm][nowd]; } }
	if (jQuery.inArray((nowm+1), vrcrestrmonths) != -1) { minlos = parseInt(vrcrestrminlos[nowm]); }
	nowpickupdate.setDate(nowpickupdate.getDate() + minlos);
	jQuery('#releasedate').datepicker('option','minDate',nowpickupdate);
	if (maxlosrange > 0) { var diffmaxminlos = maxlosrange - minlos; var maxdropoffdate = new Date(nowpickupdate.getTime()); maxdropoffdate.setDate(maxdropoffdate.getDate() + diffmaxminlos); jQuery('#releasedate').datepicker('option','maxDate',maxdropoffdate); vrcDropMaxDateSet = true; vrcDropMaxDateSetNow = true; }
	if (nowm in vrcrestrmaxlos) { var diffmaxminlos = parseInt(vrcrestrmaxlos[nowm]) - minlos; var maxdropoffdate = new Date(nowpickupdate.getTime()); maxdropoffdate.setDate(maxdropoffdate.getDate() + diffmaxminlos); jQuery('#releasedate').datepicker('option','maxDate',maxdropoffdate); vrcDropMaxDateSet = true; vrcDropMaxDateSetNow = true; }
	if (!vrcFullObject(vrccombowdays)) { jQuery('#releasedate').datepicker('setDate',nowpickupdate); if (!vrcDropMaxDateSetNow && vrcDropMaxDateSet === true) { jQuery('#releasedate').datepicker('option','maxDate',null); } } else { vrcRefreshDropoff(nowpickup); }
}";

		if (count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0) {
			$resdecl .= "
var vrcrestrwdays = {" . implode(", ", $wdaysrestrictions) . "};
var vrcrestrwdaystwo = {" . implode(", ", $wdaystworestrictions) . "};
function vrcIsDayDisabled(date) { if (!vrcValidateCta(date)){return [false];} var actd=jQuery.datepicker.formatDate('yy-mm-dd',date); " . (strlen($declclosingdays) > 0 ? "var loc_closing=pickupClosingDays(date); if(!loc_closing[0]){return loc_closing;}" : "") . " " . (count($push_disabled_in) ? "var vrc_fulldays=[" . implode(',', $push_disabled_in) . "]; if(jQuery.inArray(actd,vrc_fulldays)>=0){return [false];}" : "") . " var m=date.getMonth(),wd=date.getDay(); if(vrcFullObject(vrcrestrwdaysrangejn)){for(var rk in vrcrestrwdaysrangejn){if(vrcrestrwdaysrangejn.hasOwnProperty(rk)){var wdrangeinit=vrcGetDateObject(vrcrestrwdaysrangejn[rk][0]);if(date>=wdrangeinit){var wdrangeend=vrcGetDateObject(vrcrestrwdaysrangejn[rk][1]);if(date<=wdrangeend){if(wd!=vrcrestrwdaysrangejn[rk][2]){if(vrcrestrwdaysrangejn[rk][4]==-1||wd!=vrcrestrwdaysrangejn[rk][4]){return [false];}}}}}}} if(vrcFullObject(vrcrestrwdays)){if(jQuery.inArray((m+1),vrcrestrmonthswdays)==-1){return [true];}if(wd==vrcrestrwdays[m]){return [true];}if(vrcFullObject(vrcrestrwdaystwo)){if(wd==vrcrestrwdaystwo[m]){return [true];}}return [false];} return [true]; }
function vrcIsDayDisabledDropoff(date) { if(!vrcValidateCtd(date)){return [false];} var actd=jQuery.datepicker.formatDate('yy-mm-dd',date); " . (strlen($declclosingdays) > 0 ? "var loc_closing=dropoffClosingDays(date);if(!loc_closing[0]){return loc_closing;}" : "") . " " . (count($push_disabled_out) ? "var vrc_fulldays=[" . implode(',', $push_disabled_out) . "];if(jQuery.inArray(actd,vrc_fulldays)>=0){return [false];}" : "") . " var m=date.getMonth(),wd=date.getDay(); if(vrcFullObject(vrccombowdays)){if(jQuery.inArray(wd,vrccombowdays)!=-1){return [true];}else{return [false];}} if(vrcFullObject(vrcrestrwdaysrangejn)){for(var rk in vrcrestrwdaysrangejn){if(vrcrestrwdaysrangejn.hasOwnProperty(rk)){var wdrangeinit=vrcGetDateObject(vrcrestrwdaysrangejn[rk][0]);if(date>=wdrangeinit){var wdrangeend=vrcGetDateObject(vrcrestrwdaysrangejn[rk][1]);if(date<=wdrangeend){if(wd!=vrcrestrwdaysrangejn[rk][2]&&vrcrestrwdaysrangejn[rk][3]==1){return [false];}}}}}} if(vrcFullObject(vrcrestrwdays)){if(jQuery.inArray((m+1),vrcrestrmonthswdays)==-1||jQuery.inArray((m+1),vrcrestrmultiplyminlos)!=-1){return [true];}if(wd==vrcrestrwdays[m]){return [true];}return [false];} return [true]; }";
		}
		$document->addScriptDeclaration($resdecl);
	}

	if (strlen($declclosingdays) > 0) {
		$declclosingdays .= '
function pickupClosingDays(date){var dmy=date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate();var wday=date.getDay().toString();var arrlocclosd=jQuery("#place").val();var checklocarr=window["loc"+arrlocclosd+"closingdays"];if(jQuery.inArray(dmy,checklocarr)==-1&&jQuery.inArray(wday,checklocarr)==-1){return [true,""];}else{return [false,"","' . addslashes(Text::_('VRCLOCDAYCLOSED')) . '"];}}
function dropoffClosingDays(date){var dmy=date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate();var wday=date.getDay().toString();var arrlocclosd=jQuery("#returnplace").val();var checklocarr=window["loc"+arrlocclosd+"closingdays"];if(jQuery.inArray(dmy,checklocarr)==-1&&jQuery.inArray(wday,checklocarr)==-1){return [true,""];}else{return [false,"","' . addslashes(Text::_('VRCLOCDAYCLOSED')) . '"];}}';
		$document->addScriptDeclaration($declclosingdays);
	}

	$dropdayplus = $def_min_los;
	$forcedropday = "jQuery('#releasedate').datepicker('option','minDate',selectedDate);";
	if (strlen($dropdayplus) > 0 && intval($dropdayplus) > 0) {
		$forcedropday = "var nowpick=jQuery(this).datepicker('getDate');if(nowpick){var nowpickdate=new Date(nowpick.getTime());nowpickdate.setDate(nowpickdate.getDate()+" . $dropdayplus . ");jQuery('#releasedate').datepicker('option','minDate',nowpickdate);jQuery('#releasedate').datepicker('setDate',nowpickdate);}";
	}

	$sdecl = "
var vrc_fulldays_in = [" . implode(', ', $push_disabled_in) . "];
var vrc_fulldays_out = [" . implode(', ', $push_disabled_out) . "];
function vrcIsDayFullIn(date){if(!vrcValidateCta(date)){return [false];}var actd=jQuery.datepicker.formatDate('yy-mm-dd',date);if(jQuery.inArray(actd,vrc_fulldays_in)==-1){return " . (strlen($declclosingdays) > 0 ? 'pickupClosingDays(date)' : '[true]') . ";}return [false];}
function vrcIsDayFullOut(date){if(!vrcValidateCtd(date)){return [false];}var actd=jQuery.datepicker.formatDate('yy-mm-dd',date);if(jQuery.inArray(actd,vrc_fulldays_out)==-1){return " . (strlen($declclosingdays) > 0 ? 'dropoffClosingDays(date)' : '[true]') . ";}return [false];}
function vrcLocationWopening(mode){if(typeof vrc_wopening_pick==='undefined'){return true;}if(mode=='pickup'){vrc_mopening_pick=null;}else{vrc_mopening_drop=null;}var loc_data=mode=='pickup'?vrc_wopening_pick:vrc_wopening_drop;var def_loc_hours=mode=='pickup'?vrc_hopening_pick:vrc_hopening_drop;var sel_d=jQuery((mode=='pickup'?'#pickupdate':'#releasedate')).datepicker('getDate');if(!sel_d){return true;}var sel_wday=sel_d.getDay();if(!vrcFullObject(loc_data)||!loc_data.hasOwnProperty(sel_wday)||!loc_data[sel_wday].hasOwnProperty('fh')){if(def_loc_hours!==null){jQuery((mode=='pickup'?'#vrccomselph':'#vrccomseldh')).html(def_loc_hours);}return true;}if(mode=='pickup'){vrc_mopening_pick=new Array(loc_data[sel_wday]['fh'],loc_data[sel_wday]['fm'],loc_data[sel_wday]['th'],loc_data[sel_wday]['tm']);}else{vrc_mopening_drop=new Array(loc_data[sel_wday]['th'],loc_data[sel_wday]['tm'],loc_data[sel_wday]['fh'],loc_data[sel_wday]['fm']);}var hlim=loc_data[sel_wday]['fh']<loc_data[sel_wday]['th']?loc_data[sel_wday]['th']:(24+loc_data[sel_wday]['th']);hlim=loc_data[sel_wday]['fh']==0&&loc_data[sel_wday]['th']==0?23:hlim;var hopts='';var def_hour=jQuery((mode=='pickup'?'#vrccomselph':'#vrccomseldh')).find('select').val();def_hour=def_hour&&def_hour.length>1&&def_hour.substr(0,1)=='0'?def_hour.substr(1):def_hour;def_hour=parseInt(def_hour);for(var h=loc_data[sel_wday]['fh'];h<=hlim;h++){var viewh=h>23?(h-24):h;hopts+='<option value=\"'+viewh+'\"'+(viewh==def_hour?' selected':'')+'>'+(viewh<10?'0'+viewh:viewh)+':00</option>';}jQuery((mode=='pickup'?'#vrccomselph':'#vrccomseldh')).find('select').html(hopts);if(mode=='pickup'){setTimeout(function(){vrcLocationWopening('dropoff');},750);}}
function vrcValidateCta(date){var m=date.getMonth(),wd=date.getDay();if(vrcFullObject(vrcrestrctarange)){for(var rk in vrcrestrctarange){if(vrcrestrctarange.hasOwnProperty(rk)){var wdrangeinit=vrcGetDateObject(vrcrestrctarange[rk][0]);if(date>=wdrangeinit){var wdrangeend=vrcGetDateObject(vrcrestrctarange[rk][1]);if(date<=wdrangeend){if(jQuery.inArray('-'+wd+'-',vrcrestrctarange[rk][2])>=0){return false;}}}}}}if(vrcFullObject(vrcrestrcta)){if(vrcrestrcta.hasOwnProperty(m)&&jQuery.inArray('-'+wd+'-',vrcrestrcta[m])>=0){return false;}}return true;}
function vrcValidateCtd(date){var m=date.getMonth(),wd=date.getDay();if(vrcFullObject(vrcrestrctdrange)){for(var rk in vrcrestrctdrange){if(vrcrestrctdrange.hasOwnProperty(rk)){var wdrangeinit=vrcGetDateObject(vrcrestrctdrange[rk][0]);if(date>=wdrangeinit){var wdrangeend=vrcGetDateObject(vrcrestrctdrange[rk][1]);if(date<=wdrangeend){if(jQuery.inArray('-'+wd+'-',vrcrestrctdrange[rk][2])>=0){return false;}}}}}}if(vrcFullObject(vrcrestrctd)){if(vrcrestrctd.hasOwnProperty(m)&&jQuery.inArray('-'+wd+'-',vrcrestrctd[m])>=0){return false;}}return true;}
function vrcInitElems(){if(typeof vrc_wopening_pick==='undefined'){return true;}vrc_hopening_pick=jQuery('#vrccomselph').find('select').clone();vrc_hopening_drop=jQuery('#vrccomseldh').find('select').clone();}
jQuery(function(){
	vrcInitElems();
	jQuery('#pickupdate').datepicker({showOn:'focus'," . (count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "beforeShowDay:vrcIsDayDisabled," : "beforeShowDay:vrcIsDayFullIn,") . "onSelect:function(selectedDate){" . ($totrestrictions > 0 ? "vrcSetMinDropoffDate();" : $forcedropday) . "vrcLocationWopening('pickup'); setTimeout(cdUpdateSummary, 100); setTimeout(cdCheckOoh, 100);}});
	jQuery('#pickupdate').datepicker('option','dateFormat','" . $juidf . "');
	jQuery('#pickupdate').datepicker('option','minDate','" . VikRentCar::getMinDaysAdvance() . "d');
	jQuery('#pickupdate').datepicker('option','maxDate','" . VikRentCar::getMaxDateFuture() . "');
	jQuery('#releasedate').datepicker({showOn:'focus'," . (count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "beforeShowDay:vrcIsDayDisabledDropoff," : "beforeShowDay:vrcIsDayFullOut,") . "onSelect:function(selectedDate){vrcLocationWopening('dropoff'); setTimeout(cdUpdateSummary, 100); setTimeout(cdCheckOoh, 100);}});
	jQuery('#releasedate').datepicker('option','dateFormat','" . $juidf . "');
	jQuery('#releasedate').datepicker('option','minDate','" . VikRentCar::getMinDaysAdvance() . "d');
	jQuery('#releasedate').datepicker('option','maxDate','" . VikRentCar::getMaxDateFuture() . "');
	jQuery('#pickupdate').datepicker('option',jQuery.datepicker.regional['vikrentcar']);
	jQuery('#releasedate').datepicker('option',jQuery.datepicker.regional['vikrentcar']);
	jQuery('.vr-cal-img,.vrc-caltrigger').click(function(){var jdp=jQuery(this).prev('input.hasDatepicker');if(jdp.length){jdp.focus();}});
});";
	$document->addScriptDeclaration($sdecl);

	$forced_pickup  = $config ? $config->get('forced_pickup', '')  : '';
	$forced_dropoff = $config ? $config->get('forced_dropoff', '') : '';

	$check_pick_locs = is_array($places) && count($plapick_ids) && !empty($plapick_ids[0]);
	$check_drop_locs = is_array($places) && count($pladrop_ids) && !empty($pladrop_ids[0]);
	$onchangeplaces     = $diffopentime ? " onchange=\"javascript: vrcSetLocOpenTime(this.value, 'pickup');\"" : "";
	$onchangeplacesdrop = $diffopentime ? " onchange=\"javascript: vrcSetLocOpenTime(this.value, 'dropoff');\"" : "";
?>

	<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar' . (!empty($pitemid) ? '&Itemid=' . $pitemid : '')); ?>"
	      method="get"
	      onsubmit="return vrcValidateSearch();">
		<input type="hidden" name="option" value="com_vikrentcar"/>
		<input type="hidden" name="task" value="search"/>
		<input type="hidden" name="cardetail" value="<?php echo $car['id']; ?>"/>
		<?php if (VikRequest::getString('tmpl','','request') == 'component'): ?>
		<input type="hidden" name="tmpl" value="component"/>
		<?php endif; ?>
		<?php if (!empty($pitemid)): ?>
		<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
		<?php endif; ?>

		<?php
		$_firstDropId = '';
		if (is_array($places) && count($places) > 0) {
			foreach ($places as $_pl) {
				if (!$check_drop_locs || in_array($_pl['id'], $pladrop_ids)) {
					$_firstDropId = $_pl['id'];
					break;
				}
			}
		}
		?>

		<!-- Pickup row -->
		<div class="cd-datetime-row">
			<div class="cd-dt-date">
				<input type="text" name="pickupdate" id="pickupdate"
				       autocomplete="off" onfocus="this.blur();" readonly
				       placeholder="<?php $t=Text::_('VRCHOOSEDATE'); echo ($t==='VRCHOOSEDATE'?'дд.мм.гггг':$t); ?>"/>
			</div>
			<div class="cd-dt-sep" onclick="jQuery('#pickupdate').focus();"><?php echo $_ico_chev_sm; ?></div>
			<?php if (!strlen($forced_pickup)): ?>
			<div class="cd-dt-time">
				<div class="cd-dt-time-inner">
					<div class="cd-th" id="vrccomselph">
						<select name="pickuph"><?php echo $hours; ?></select>
					</div>
					<span class="cd-dt-time-chevron" onclick="jQuery('#vrccomselph select').trigger('chosen:open');"><?php echo $_ico_chev_sm; ?></span>
				</div>
			</div>
			<!-- Minutes fixed at 0 -->
			<input type="hidden" name="pickupm" id="vrccomselpm" value="0"/>
			<?php else:
				$fp = (int)$forced_pickup; $fph = floor($fp/3600);
			?>
			<input type="hidden" name="pickuph" value="<?php echo $fph; ?>"/>
			<input type="hidden" name="pickupm" value="0"/>
			<?php endif; ?>
		</div>

		<!-- Return row -->
		<div class="cd-datetime-row" style="margin-top:8px;">
			<div class="cd-dt-date">
				<input type="text" name="releasedate" id="releasedate"
				       autocomplete="off" onfocus="this.blur();" readonly
				       placeholder="<?php $t=Text::_('VRCHOOSEDATE'); echo ($t==='VRCHOOSEDATE'?'дд.мм.гггг':$t); ?>"/>
			</div>
			<div class="cd-dt-sep" onclick="jQuery('#releasedate').focus();"><?php echo $_ico_chev_sm; ?></div>
			<?php if (!strlen($forced_dropoff)): ?>
			<div class="cd-dt-time">
				<div class="cd-dt-time-inner">
					<div class="cd-th" id="vrccomseldh">
						<select name="releaseh"><?php echo $hours; ?></select>
					</div>
					<span class="cd-dt-time-chevron" onclick="jQuery('#vrccomseldh select').trigger('chosen:open');"><?php echo $_ico_chev_sm; ?></span>
				</div>
			</div>
			<!-- Minutes fixed at 0 -->
			<input type="hidden" name="releasem" id="vrccomseldm" value="0"/>
			<?php else:
				$fd = (int)$forced_dropoff; $fdh = floor($fd/3600);
			?>
			<input type="hidden" name="releaseh" value="<?php echo $fdh; ?>"/>
			<input type="hidden" name="releasem" value="0"/>
			<?php endif; ?>
		</div>

		<!-- Location -->
		<?php if (is_array($places) && count($places) > 0): ?>
		<div class="cd-location-row" style="margin-top:10px;">
			<div class="cd-row-label"><?php echo Text::_('VRPPLACE') ?: 'Место получения'; ?></div>
			<div class="cd-select-wrap">
				<select name="place" id="place"<?php echo $onchangeplaces; ?>>
					<?php foreach ($places as $pla):
						if ($check_pick_locs && !in_array($pla['id'], $plapick_ids)) { continue; }
						if (!empty($pla['lat']) && !empty($pla['lng'])) { $coordsplaces[] = $pla; }
					?>
					<option value="<?php echo $pla['id']; ?>" id="place<?php echo $pla['id']; ?>"><?php echo $pla['name']; ?></option>
					<?php endforeach; ?>
				</select>
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="cd-arrow" onclick="jQuery('#place').trigger('chosen:open');"><path d="m6 9 6 6 6-6"/></svg>
			</div>
		</div>
		<input type="hidden" name="returnplace" id="returnplace" value="<?php echo htmlspecialchars($_firstDropId); ?>"/>
		<?php endif; ?>

		<!-- OOH warning -->
		<?php if (!empty($oohFees)): ?>
		<div class="cd-ooh-warning" id="cd-ooh-warning">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
			<span id="cd-ooh-text"></span>
		</div>
		<?php endif; ?>

		<!-- Optionals -->
		<?php if (!empty($carOptionals)): ?>
		<div class="cd-optionals-section">
			<div class="cd-optionals-title"><?php echo Text::_('VRACCOPZ') ?: 'Alte opțiuni'; ?> <span style="font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span></div>
			<?php foreach ($carOptionals as $_opt):
				$_optPerDay = intval($_opt['perday']) === 1;
				$_optBaseCost = (float)$_opt['cost'];
				$_optMax = !empty($_opt['maxprice']) && $_opt['maxprice'] > 0 ? (float)$_opt['maxprice'] : 0;
				// No decimals in price label
				$_optCostDisplay = (floor($_optBaseCost) == $_optBaseCost) ? (int)$_optBaseCost : $_optBaseCost;
				$_optPriceLabel = $currencysymb . $_optCostDisplay . ($_optPerDay ? '/'.( Text::_('VRCSEARCHDAY') ?: 'день') : '');
				$_optId = (int)$_opt['id'];
			?>
			<div class="cd-optional-row" id="cd-opt-row-<?php echo $_optId; ?>" onclick="cdToggleOptional(<?php echo $_optId; ?>, <?php echo $_optBaseCost; ?>, <?php echo (int)$_optPerDay; ?>, <?php echo $_optMax; ?>)">
				<div class="cd-optional-check" id="cd-opt-check-<?php echo $_optId; ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
				</div>
				<span class="cd-optional-name"><?php echo htmlspecialchars($_opt['name']); ?></span>
				<span class="cd-optional-price">+<?php echo $_optPriceLabel; ?></span>
				<input type="hidden" name="optid<?php echo $_optId; ?>" id="cd-opt-input-<?php echo $_optId; ?>" value="0"/>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<!-- Live summary -->
		<div class="cd-summary-section" id="cd-summary">
			<div class="cd-summary-title"><?php echo Text::_('VRPRICE') ?: 'Rezumat'; ?></div>
			<div id="cd-summary-rows"></div>
			<div class="cd-summary-total">
				<span class="cd-summary-total-label"><?php echo Text::_('VRTOTAL') ?: 'Итого'; ?>:</span>
				<span class="cd-summary-total-val" id="cd-summary-total"></span>
			</div>
		</div>

		<!-- Submit -->
		<div class="cd-booking-submit-row">
			<input type="submit" name="search"
			       value="<?php echo Text::_('VRCBOOKTHISCAR'); ?>"
			       class="btn vrcdetbooksubmit vrc-pref-color-btn"/>
			<div class="cd-shield-info">
				<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
				<span><?php echo Text::_('VRCSHIELDINFO') ?: 'Anulare gratuită • Suport 24/7 • Mașini asigurate'; ?></span>
			</div>
		</div>

	</form>

	<script type="text/javascript">
	/* Sync returnplace to mirror pickup */
	jQuery(function() {
		jQuery('#place').on('change', function() {
			jQuery('#returnplace').val(jQuery(this).val());
			<?php if ($diffopentime): ?>
			vrcSetLocOpenTime(jQuery(this).val(), 'pickup');
			vrcSetLocOpenTime(jQuery(this).val(), 'dropoff');
			<?php endif; ?>
		});
	});

	/* ── OOH + Optionals + Live Summary JS ───────────────────────── */
	var cdOohFees = <?php echo json_encode($oohFees); ?>;
	var cdCurrency = '€';
		var cdRateByDay = <?php
		$_jsRbd = array();
		foreach ($_rateByDay as $_d => $_r) { $_jsRbd[(int)$_d] = $_r; }
		echo json_encode($_jsRbd);
	?>;
	var cdOptionals = {};
	<?php foreach ($carOptionals as $_opt): ?>
	cdOptionals[<?php echo (int)$_opt['id']; ?>] = {perday: <?php echo (int)$_opt['perday']; ?>, cost: <?php echo (float)$_opt['cost']; ?>, max: <?php echo (!empty($_opt['maxprice'])?(float)$_opt['maxprice']:0); ?>, checked: false};
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
			var diff = Math.round((d2 - d1) / 86400000);
			return diff > 0 ? diff : null;
		} catch(e) { return null; }
	}

	function cdGetRate(days) {
		if (!days || !cdRateByDay) return null;
		var best = null;
		for (var d in cdRateByDay) {
			if (parseInt(d) <= days) { best = cdRateByDay[d]; }
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

	function cdUpdateSummary() {
		var days = cdGetDays();
		var $sum = jQuery('#cd-summary');
		if (!days) { $sum.removeClass('is-visible'); return; }

		var rate = cdGetRate(days);
		if (!rate) { $sum.removeClass('is-visible'); return; }

		var baseTotal = rate * days;
		var dayWord = '<?php echo addslashes(Text::_("VRCSEARCHDAYS") ?: "дней"); ?>';
		var rows = '<div class="cd-summary-row"><span><?php echo addslashes(Text::_("VRPRICE") ?: "Preț de bază"); ?></span>'
			+ '<span class="cd-summary-row-val">' + cdCurrency + cdFmt(rate) + ' &times; ' + days + ' ' + dayWord + '</span></div>';

		var optTotal = 0;
		for (var id in cdOptionals) {
			var o = cdOptionals[id];
			if (!o.checked) continue;
			var oc = o.perday ? o.cost * days : o.cost;
			if (o.max > 0 && oc > o.max) oc = o.max;
			optTotal += oc;
			var name = jQuery('#cd-opt-row-' + id + ' .cd-optional-name').text();
			rows += '<div class="cd-summary-row"><span>' + name + '</span>'
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

		var total = baseTotal + optTotal + oohTotal;
		jQuery('#cd-summary-rows').html(rows);
		jQuery('#cd-summary-total').text(cdCurrency + cdFmt(total));
		$sum.addClass('is-visible');

		cdHighlightTier(days);
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
	function vrcValidateSearch() {
		if (typeof jQuery === 'undefined' || typeof vrc_wopening_pick === 'undefined') return true;
		if (vrc_mopening_pick !== null) {
			var pickh = jQuery('#vrccomselph').find('select').val();
			if (!pickh) return true;
			pickh = vrcCleanNumber(pickh);
			// minutes are always 0 so no minute validation needed
		}
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
		// Default pickup = tomorrow; sync Chosen time selects to show 12:00
		(function() {
			if (!jQuery('#pickupdate').val()) {
				var defPickup = new Date();
				defPickup.setDate(defPickup.getDate() + Math.max(1, <?php echo (int)VikRentCar::getMinDaysAdvance(); ?>));
				jQuery('#pickupdate').datepicker('setDate', defPickup);
				var defDrop = new Date(defPickup.getTime());
				defDrop.setDate(defDrop.getDate() + <?php echo max(1, intval($def_min_los) > 0 ? intval($def_min_los) : 1); ?>);
				jQuery('#releasedate').datepicker('setDate', defDrop);
				if (typeof vrcSetMinDropoffDate !== 'undefined') { vrcSetMinDropoffDate(); }
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
			<h1 class="cd-car-name"><?php echo $car['name']; ?></h1>
			<?php if (!empty($caratDefs)): ?>
			<div class="cd-specs">
				<?php foreach ($caratDefs as $cid => $carat):
					$rawLabel = !empty($carat['textimg']) ? $carat['textimg'] : $carat['name'];
					$label = Text::_($rawLabel) ?: $rawLabel;
					$key = strtolower($label); $svg = $svgDefault;
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
	

	<!-- Description (mobile order 5) -->
	<?php if (!empty($car['info'])): ?>
	<div class="cd-description" style="margin-top:24px;">
		<h2><?php echo Text::_('VRCDESCRIPTION') ?: 'Описание'; ?></h2>
		<div class="cd-description-text"><?php echo $car['info']; ?></div>
	</div>
	<?php endif; ?>
</div>



</div><!-- /.cd-right -->

</div><!-- /.cd-page-grid -->
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
	var tiers = <?php
		$_jsRanges = array();
		foreach ($priceTiers as $_t) { $_jsRanges[] = array('from' => (int)$_t['from'], 'to' => (int)$_t['to']); }
		echo json_encode($_jsRanges);
	?>;

	window.cdHighlightTier = function(days) {
		var $cells = $('#cd-price-tiers .cd-price-tier');
		$cells.removeClass('is-active is-active-prev');
		if (!days) return;
		var activeIdx = -1;
		for (var i = 0; i < tiers.length; i++) {
			if (days >= tiers[i].from && days <= tiers[i].to) { activeIdx = i; break; }
		}
		if (activeIdx === -1 && days > 0) { activeIdx = tiers.length - 1; }
		if (activeIdx >= 0) {
			$cells.eq(activeIdx).addClass('is-active');
			if (activeIdx > 0) { $cells.eq(activeIdx - 1).addClass('is-active-prev'); }
		}
	};
})(jQuery);
</script>
<?php endif; ?>

<?php VikRentCar::printTrackingCode(isset($this) ? $this : null); ?>