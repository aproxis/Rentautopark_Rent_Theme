<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/cardetails/default.php
 * AutoRent Figma Design — v1
 * Based on cardetailsbk (modern VRC logic) + React CarDetailPage.tsx styling
 *
 * Sections:
 *   1. Breadcrumb
 *   2. Gallery (thumbnails left + main image) + Title & Specs (right)
 *   3. Booking Card (full width — locations, dates, times)
 *   4. Description (Joomla content plugins)
 *   5. Availability Calendars (styled grid)
 *   6. Hourly Calendar (optional)
 *   7. Request Info Modal
 */

defined('_JEXEC') OR die('Restricted Area');
use Joomla\CMS\Language\Text;

$car        = $this->car;
$car_params = $this->car_params;
$busy       = $this->busy;
$vrc_tn     = $this->vrc_tn;

$vrc_app = VikRentCar::getVrcApplication();

// Try modern config access
$config = null;
if (class_exists('VRCFactory')) {
	$config = VRCFactory::getConfig();
}

// JS text strings for location break messages
if (method_exists('Joomla\CMS\Language\Text', 'script')) {
	Text::script('VRC_LOC_WILL_OPEN_TIME');
	Text::script('VRC_LOC_WILL_CLOSE_TIME');
	Text::script('VRC_PICKLOC_IS_ON_BREAK_TIME_FROM_TO');
	Text::script('VRC_DROPLOC_IS_ON_BREAK_TIME_FROM_TO');
}

// Load jQuery & Fancybox
$document = JFactory::getDocument();
if (VikRentCar::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
}
$document->addStyleSheet(VRC_SITE_URI . 'resources/jquery.fancybox.css');
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

// Category name
$categoryName = VikRentCar::sayCategory($car['idcat'], $vrc_tn);

// Prepare images
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

// Price
$showPrice = ($car['cost'] > 0);
$priceVal  = strlen($car['startfrom']) > 0 ? VikRentCar::numberFormat($car['startfrom']) : VikRentCar::numberFormat($car['cost']);

// Joomla Content Plugins for description
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

// Parse individual carats for the specs grid
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

// Pre-compute disabled dates from busy records (needed BEFORE booking card JS)
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

// Carslist URL for breadcrumb
$carslistUrl = JRoute::_('index.php?option=com_vikrentcar&view=carslist' . (!empty($pitemid) ? '&Itemid=' . $pitemid : ''));
?>

<style>
/* ================================================================
   AutoRent CarDetails v1 — Figma Design Override
   ================================================================ */

/* Breadcrumb */
.cd-breadcrumb {
	background: #f9fafb;
	border-bottom: 1px solid #e5e7eb;
	padding: 14px 0;
	margin: -20px -15px 0;
	padding-left: 15px;
	padding-right: 15px;
}
.cd-breadcrumb-inner {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 13px;
	color: #6b7280;
	max-width: 1200px;
	margin: 0 auto;
}
.cd-breadcrumb-inner a {
	color: #6b7280;
	text-decoration: none;
	transition: color .2s;
}
.cd-breadcrumb-inner a:hover { color: #FE5001; }
.cd-breadcrumb-inner .cd-bc-sep { color: #d1d5db; }
.cd-breadcrumb-inner .cd-bc-current { color: #0a0a0a; font-weight: 600; }

/* Main container */
.cd-container {
	max-width: 1200px;
	margin: 0 auto;
	padding: 24px 0 48px;
}

/* Top section: Gallery + Specs */
.cd-top {
	display: grid;
	grid-template-columns: 2fr 1fr;
	gap: 20px;
	margin-bottom: 8px;
}
@media (max-width: 900px) {
	.cd-top {
		grid-template-columns: 1fr;
	}
}

/* ================================================================
   GALLERY
   ================================================================ */
.cd-gallery {
	display: flex;
	gap: 10px;
	align-items: stretch;
}
.cd-thumbs {
	display: flex;
	flex-direction: column;
	gap: 8px;
	width: 90px;
	flex-shrink: 0;
}
.cd-thumb {
	position: relative;
	aspect-ratio: 16/10;
	border-radius: 8px;
	overflow: hidden;
	border: 2px solid transparent;
	cursor: pointer;
	transition: border-color .2s, transform .2s;
}
.cd-thumb:hover { border-color: #d1d5db; }
.cd-thumb.active {
	border-color: #FE5001;
	transform: scale(1.05);
}
.cd-thumb img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}
.cd-thumb-more {
	position: absolute;
	inset: 0;
	background: rgba(0,0,0,.6);
	backdrop-filter: blur(2px);
	display: flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	font-weight: 700;
	font-size: 18px;
}

.cd-main-img {
	flex: 1;
	position: relative;
	border-radius: 16px;
	overflow: hidden;
	background: #f3f4f6;
	aspect-ratio: 16/10;
}
.cd-main-img img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
	transition: transform .4s;
	cursor: zoom-in;
}
.cd-main-img:hover img { transform: scale(1.03); }

.cd-main-img .cd-fav {
	position: absolute;
	top: 14px;
	right: 14px;
	width: 44px;
	height: 44px;
	background: rgba(255,255,255,.9);
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	border: none;
	cursor: pointer;
	box-shadow: 0 2px 10px rgba(0,0,0,.12);
	transition: background .2s;
	z-index: 2;
}
.cd-main-img .cd-fav:hover { background: #fff; }
.cd-main-img .cd-fav svg { width: 20px; height: 20px; color: #6b7280; }

@media (max-width: 600px) {
	.cd-gallery { flex-direction: column-reverse; }
	.cd-thumbs { flex-direction: row; width: 100%; overflow-x: auto; }
	.cd-thumb { width: 70px; flex-shrink: 0; aspect-ratio: 16/10; }
	.cd-main-img { aspect-ratio: 16/9; }
}

/* ================================================================
   RIGHT COLUMN: Title + Specs
   ================================================================ */
.cd-info {
	display: flex;
	flex-direction: column;
	gap: 16px;
}
.cd-car-name {
	font-size: clamp(1.6rem, 3vw, 2.2rem);
	font-weight: 800;
	color: #0a0a0a;
	margin: 0;
	line-height: 1.15;
}
.cd-car-cat {
	display: inline-block;
	padding: 4px 12px;
	background: #f3f4f6;
	border-radius: 20px;
	font-size: 12px;
	font-weight: 600;
	color: #6b7280;
	text-transform: uppercase;
	letter-spacing: .04em;
}

/* Specs grid 2×3 */
.cd-specs {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 10px;
}
.cd-spec {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 12px;
	background: #f9fafb;
	border-radius: 10px;
}
.cd-spec-icon {
	width: 22px;
	height: 22px;
	flex-shrink: 0;
	color: #FE5001;
	display: flex;
	align-items: center;
	justify-content: center;
}
.cd-spec-icon i {
	font-size: 18px;
	color: #FE5001;
}
.cd-spec-icon img {
	width: 22px;
	height: 22px;
	object-fit: contain;
}
.cd-spec-text { display: flex; flex-direction: column; }
.cd-spec-label { font-size: 11px; color: #9ca3af; }
.cd-spec-value { font-size: 13px; font-weight: 600; color: #0a0a0a; }

/* Price */
.cd-price-block {
	display: flex;
	align-items: baseline;
	gap: 6px;
	padding: 16px 0;
	border-top: 1px solid #f3f4f6;
}
.cd-price-from { font-size: 13px; color: #9ca3af; }
.cd-price-val { font-size: 2rem; font-weight: 800; color: #0a0a0a; }
.cd-price-cur { font-size: 1.2rem; font-weight: 700; color: #0a0a0a; }
.cd-price-per { font-size: 13px; color: #9ca3af; }

/* Request info btn */
.cd-reqinfo-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	padding: 12px 24px;
	background: #fff;
	color: #374151;
	border: 2px solid #e5e7eb;
	border-radius: 10px;
	font-size: 14px;
	font-weight: 700;
	cursor: pointer;
	transition: border-color .2s, color .2s;
	text-decoration: none;
	width: 100%;
	text-align: center;
}
.cd-reqinfo-btn:hover { border-color: #FE5001; color: #FE5001; }
.cd-reqinfo-btn i { font-size: 16px; }

/* ================================================================
   BOOKING CARD (full width)
   ================================================================ */
.cd-booking-card {
	background: linear-gradient(135deg, #f9fafb 0%, #fff 100%);
	border: 2px solid #e5e7eb;
	border-radius: 16px;
	padding: 24px;
	box-shadow: 0 8px 32px rgba(0,0,0,.06);
	margin-bottom: 32px;
}
.cd-booking-title {
	font-size: 1.25rem;
	font-weight: 800;
	color: #0a0a0a;
	margin: 0 0 20px;
	padding-bottom: 14px;
	border-bottom: 1px solid #e5e7eb;
}

/* Override VRC form styles inside booking card */
.cd-booking-card .vrcdivsearch { margin: 0; padding: 0; }
.cd-booking-card .vrcdivsearch-inner { margin: 0; padding: 0; }

.cd-booking-card .vrc-searchf-section-locations {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 16px;
	margin-bottom: 20px;
}
.cd-booking-card .vrc-searchf-section-datetimes {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 16px;
	margin-bottom: 20px;
}
@media (max-width: 700px) {
	.cd-booking-card .vrc-searchf-section-locations,
	.cd-booking-card .vrc-searchf-section-datetimes {
		grid-template-columns: 1fr;
	}
}

.cd-booking-card .vrcsfentrycont {
	display: flex;
	flex-direction: column;
	gap: 4px;
}
.cd-booking-card .vrcsfentrycont > label,
.cd-booking-card .vrcsfentrylabsel > label {
	font-size: 12px;
	font-weight: 600;
	color: #374151;
	margin-bottom: 4px;
}
.cd-booking-card .vrcsfentryselect select,
.cd-booking-card select {
	width: 100%;
	padding: 10px 36px 10px 12px;
	border: 2px solid #e5e7eb;
	border-radius: 10px;
	font-size: 13px;
	font-weight: 500;
	color: #0a0a0a;
	background: #fff;
	appearance: none;
	-webkit-appearance: none;
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
	background-repeat: no-repeat;
	background-position: right 12px center;
	cursor: pointer;
	transition: border-color .2s, box-shadow .2s;
}
.cd-booking-card select:focus {
	outline: none;
	border-color: #FE5001;
	box-shadow: 0 0 0 3px rgba(254,80,1,.12);
}

.cd-booking-card .vrcsfentrylabsel {
	display: flex;
	flex-direction: column;
	gap: 4px;
	margin-bottom: 8px;
}
/* On desktop: date + time inline in one row */
@media (min-width: 701px) {
	.cd-booking-card .vrc-searchf-section-datetimes .vrcsfentrycont {
		display: flex;
		flex-wrap: wrap;
		align-items: flex-end;
		gap: 0 10px;
	}
	.cd-booking-card .vrc-searchf-section-datetimes .vrcsfentrycont > .vrcsfentrylabsel > label {
		display: block;
		width: 100%;
		margin-bottom: 6px;
	}
	.cd-booking-card .vrc-searchf-section-datetimes .vrcsfentrylabsel {
		flex: 1;
		min-width: 0;
		margin-bottom: 0;
	}
	.cd-booking-card .vrc-searchf-section-datetimes .vrcsfentrytime {
		flex-shrink: 0;
		margin-top: 0;
	}
}
.cd-booking-card .vrcsfentrydate {
	display: flex;
	align-items: center;
	gap: 0;
	position: relative;
}
.cd-booking-card .vrcsfentrydate input {
	width: 100%;
	padding: 10px 36px 10px 12px;
	border: 2px solid #e5e7eb;
	border-radius: 10px;
	font-size: 13px;
	font-weight: 500;
	color: #0a0a0a;
	background: #fff;
	cursor: pointer;
	transition: border-color .2s, box-shadow .2s;
}
.cd-booking-card .vrcsfentrydate input:focus {
	outline: none;
	border-color: #FE5001;
	box-shadow: 0 0 0 3px rgba(254,80,1,.12);
}
.cd-booking-card .vrcsfentrydate i,
.cd-booking-card .vrc-caltrigger {
	position: absolute;
	right: 12px;
	top: 50%;
	transform: translateY(-50%);
	color: #9ca3af;
	cursor: pointer;
	font-size: 16px;
}

.cd-booking-card .vrcsfentrytime {
	margin-top: 4px;
}
@media (min-width: 701px) {
	.cd-booking-card .vrcsfentrytime {
		margin-top: 0;
	}
}
.cd-booking-card .vrcsfentrytime > label {
	font-size: 11px;
	font-weight: 600;
	color: #6b7280;
	display: block;
	margin-bottom: 4px;
}
@media (min-width: 701px) {
	.cd-booking-card .vrcsfentrytime > label {
		display: none;
	}
}
.cd-booking-card .vrc-sf-time-container {
	display: flex;
	align-items: center;
	gap: 4px;
}
.cd-booking-card .vrc-sf-time-container select {
	padding: 8px 28px 8px 10px;
	font-size: 13px;
	min-width: 60px;
}
.cd-booking-card .vrctimesep {
	font-weight: 700;
	color: #6b7280;
	font-size: 14px;
}

/* Submit button */
.cd-booking-card .vrc-searchf-section-sbmt {
	text-align: center;
	padding-top: 8px;
}
.cd-booking-card .vrcdetbooksubmit {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	max-width: 420px;
	padding: 14px 32px;
	background: #FE5001 !important;
	color: #fff !important;
	border: none;
	border-radius: 12px;
	font-size: 15px;
	font-weight: 700;
	cursor: pointer;
	transition: background .2s, box-shadow .2s;
	box-shadow: 0 4px 16px rgba(254,80,1,.25);
}
.cd-booking-card .vrcdetbooksubmit:hover {
	background: #E54801 !important;
	box-shadow: 0 6px 24px rgba(254,80,1,.35);
}

/* Locations map link */
.cd-booking-card .vrclocationsbox {
	text-align: center;
	margin-top: 12px;
}
.cd-booking-card .vrclocationsmapdiv a {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	color: #FE5001;
	font-size: 13px;
	font-weight: 600;
	text-decoration: none;
	transition: color .2s;
}
.cd-booking-card .vrclocationsmapdiv a:hover { color: #E54801; }
.cd-booking-card .vrclocationsmapdiv i { font-size: 16px; }

/* Shield info line */
.cd-shield-info {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 12px;
	color: #6b7280;
	padding-top: 14px;
	margin-top: 14px;
	border-top: 1px solid #e5e7eb;
	text-align: center;
	justify-content: center;
}
.cd-shield-info i,
.cd-shield-info svg { color: #FE5001; flex-shrink: 0; }

/* ================================================================
   DESCRIPTION
   ================================================================ */
.cd-description {
	margin-bottom: 32px;
}
.cd-description h2 {
	font-size: 1.5rem;
	font-weight: 800;
	color: #0a0a0a;
	margin: 0 0 16px;
}
.cd-description-text {
	font-size: 14px;
	line-height: 1.7;
	color: #4b5563;
}
.cd-description-text p { margin: 0 0 12px; }
.cd-description-text img { max-width: 100%; height: auto; border-radius: 8px; }

/* ================================================================
   AVAILABILITY CALENDARS
   ================================================================ */
.cd-avail-section {
	margin-bottom: 32px;
}
.cd-avail-section h2 {
	font-size: 1.5rem;
	font-weight: 800;
	color: #0a0a0a;
	margin: 0 0 16px;
}

/* Legend & month selector */
.cd-legend-bar {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 16px;
	margin-bottom: 16px;
	padding: 12px 16px;
	background: #f9fafb;
	border-radius: 12px;
	border: 1px solid #f3f4f6;
}
.cd-legend-bar .vrcselectm {
	padding: 8px 32px 8px 12px;
	border: 2px solid #e5e7eb;
	border-radius: 8px;
	font-size: 13px;
	font-weight: 600;
	color: #0a0a0a;
	background: #fff;
	appearance: none;
	-webkit-appearance: none;
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
	background-repeat: no-repeat;
	background-position: right 10px center;
	cursor: pointer;
}
.cd-legend-items {
	display: flex;
	align-items: center;
	gap: 14px;
	flex-wrap: wrap;
}
.cd-legend-item {
	display: flex;
	align-items: center;
	gap: 6px;
	font-size: 12px;
	color: #6b7280;
}
.cd-leg-dot {
	width: 14px;
	height: 14px;
	border-radius: 4px;
	display: inline-block;
}
.cd-leg-free { background: #22c55e; }
.cd-leg-warn { background: #f59e0b; }
.cd-leg-busy { background: #ef4444; }

/* Calendar grid */
.cd-cals-grid {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 16px;
}
@media (max-width: 900px) { .cd-cals-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .cd-cals-grid { grid-template-columns: 1fr; } }

.cd-cals-grid .vrccaldivcont {
	background: #fff;
	border: 1.5px solid #f3f4f6;
	border-radius: 12px;
	padding: 12px;
	box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.cd-cals-grid .vrccal {
	width: 100%;
	border-collapse: collapse;
	font-size: 12px;
}
.cd-cals-grid .vrccal td {
	padding: 6px 2px;
	text-align: center;
	border-radius: 4px;
}
.cd-cals-grid .vrccal tr:first-child td {
	font-size: 13px;
	font-weight: 700;
	color: #0a0a0a;
	padding-bottom: 10px;
	border-bottom: 1px solid #f3f4f6;
}
.cd-cals-grid .vrccaldays td {
	font-size: 10px;
	font-weight: 700;
	color: #9ca3af;
	text-transform: uppercase;
	padding: 8px 2px 4px;
}
.cd-cals-grid .vrctdfree {
	color: #0a0a0a;
	background: #f0fdf4;
}
.cd-cals-grid .vrctdwarning {
	color: #92400e;
	background: #fef3c7;
}
.cd-cals-grid .vrctdbusy {
	color: #991b1b;
	background: #fee2e2;
}
.cd-cals-grid .vrctdbusy.vrctdbusyforcheckin {
	background: linear-gradient(135deg, #fef3c7 50%, #fee2e2 50%);
}
.cd-cals-grid .vrc-cdetails-cal-pickday {
	cursor: pointer;
	text-decoration: underline;
	transition: color .15s;
}
.cd-cals-grid .vrc-cdetails-cal-pickday:hover { color: #FE5001; }

/* Hourly calendar */
.cd-hourly-cal {
	margin-top: 20px;
	overflow-x: auto;
}
.cd-hourly-cal h4 {
	font-size: 1rem;
	font-weight: 700;
	color: #0a0a0a;
	margin: 0 0 12px;
}
.cd-hourly-cal .vrc-hourly-cal {
	border-collapse: collapse;
	font-size: 11px;
}
.cd-hourly-cal .vrc-hourly-cal td {
	padding: 6px 4px;
	text-align: center;
	border: 1px solid #f3f4f6;
	min-width: 36px;
}

/* ================================================================
   REQUEST INFO MODAL
   ================================================================ */
#vrcdialog-overlay {
	position: fixed;
	inset: 0;
	background: rgba(0,0,0,.5);
	backdrop-filter: blur(4px);
	-webkit-backdrop-filter: blur(4px);
	z-index: 9999;
	display: flex;
	align-items: center;
	justify-content: center;
}
.vrcdialog-inner.vrcdialog-reqinfo {
	background: #fff;
	border-radius: 16px;
	padding: 32px;
	max-width: 500px;
	width: 90%;
	max-height: 90vh;
	overflow-y: auto;
	box-shadow: 0 20px 60px rgba(0,0,0,.2);
	position: relative;
}
.vrcdialog-inner h3 {
	font-size: 1.25rem;
	font-weight: 800;
	color: #0a0a0a;
	margin: 0 0 20px;
}
.vrcdialog-reqinfo-formcont {
	display: flex;
	flex-direction: column;
	gap: 14px;
}
.vrcdialog-reqinfo-formentry { display: flex; flex-direction: column; gap: 4px; }
.vrcdialog-reqinfo-formentry label {
	font-size: 13px;
	font-weight: 600;
	color: #374151;
}
.vrcdialog-reqinfo-formentry input[type="text"],
.vrcdialog-reqinfo-formentry textarea {
	padding: 10px 14px;
	border: 2px solid #e5e7eb;
	border-radius: 10px;
	font-size: 14px;
	color: #0a0a0a;
	transition: border-color .2s, box-shadow .2s;
	font-family: inherit;
}
.vrcdialog-reqinfo-formentry input[type="text"]:focus,
.vrcdialog-reqinfo-formentry textarea:focus {
	outline: none;
	border-color: #FE5001;
	box-shadow: 0 0 0 3px rgba(254,80,1,.12);
}
.vrcdialog-reqinfo-formentry textarea {
	min-height: 100px;
	resize: vertical;
}
.vrcdialog-reqinfo-formentry-ckbox {
	flex-direction: row !important;
	align-items: center;
	gap: 8px !important;
}
.vrcdialog-reqinfo-formentry-ckbox input[type="checkbox"] {
	width: 18px;
	height: 18px;
	accent-color: #FE5001;
}
.vrcdialog-reqinfo-formentry-ckbox label,
.vrcdialog-reqinfo-formentry-ckbox a {
	font-size: 13px;
	color: #374151;
}
.vrcdialog-reqinfo-formentry-ckbox a { color: #FE5001; text-decoration: underline; }
.vrcdialog-reqinfo-formsubmit { padding-top: 6px; }
.vrcdialog-reqinfo-formsubmit button {
	width: 100%;
	padding: 12px;
	background: #FE5001 !important;
	color: #fff !important;
	border: none;
	border-radius: 10px;
	font-size: 14px;
	font-weight: 700;
	cursor: pointer;
	transition: background .2s;
}
.vrcdialog-reqinfo-formsubmit button:hover { background: #E54801 !important; }

/* Disable rent message */
.cd-disabled-rent {
	text-align: center;
	padding: 40px 20px;
	color: #6b7280;
	font-size: 14px;
}

/* jQuery UI Datepicker overrides */
.ui-datepicker {
	border-radius: 12px !important;
	border: 2px solid #e5e7eb !important;
	box-shadow: 0 8px 30px rgba(0,0,0,.1) !important;
	padding: 12px !important;
	font-family: inherit !important;
}
.ui-datepicker-header {
	background: none !important;
	border: none !important;
	border-bottom: 1px solid #f3f4f6 !important;
	padding-bottom: 10px !important;
	margin-bottom: 8px !important;
}
.ui-datepicker .ui-datepicker-title { font-weight: 700 !important; color: #0a0a0a !important; }
.ui-datepicker .ui-datepicker-prev,
.ui-datepicker .ui-datepicker-next {
	border-radius: 8px !important;
	transition: background .15s !important;
}
.ui-datepicker .ui-datepicker-prev:hover,
.ui-datepicker .ui-datepicker-next:hover { background: #f3f4f6 !important; }
.ui-datepicker td a,
.ui-datepicker td span {
	text-align: center !important;
	border-radius: 6px !important;
	transition: background .15s, color .15s !important;
}
.ui-datepicker td a:hover { background: #FE5001 !important; color: #fff !important; }
.ui-datepicker .ui-datepicker-current-day a {
	background: #FE5001 !important;
	color: #fff !important;
	font-weight: 700 !important;
}

/* Hide old VRC legacy styles */
.vrc-cardetails-legend { display: none !important; }
.vrc-avcals-container { display: none !important; }
.vrc-cardetails-book-wrap > h4 { display: none !important; }
</style>

<?php /* ================================================================
   1. BREADCRUMB
   ================================================================ */ ?>
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

<?php /* ================================================================
   2. GALLERY + INFO (Title, Specs, Price)
   ================================================================ */ ?>
<div class="cd-top">

	<!-- Gallery -->
	<div class="cd-gallery">
		<?php if (!empty($mainImgSrc) || !empty($moreImages)): ?>
		<!-- Thumbnails -->
		<div class="cd-thumbs" id="cd-thumbs">
			<?php
			$allImages = array();
			if (!empty($mainImgSrc)) {
				$allImages[] = array(
					'thumb' => $mainImgSrc,
					'big'   => $mainImgSrc,
				);
			}
			foreach ($moreImages as $mi) {
				$allImages[] = $mi;
			}
			$maxThumbs = min(count($allImages), 5);
			for ($ti = 0; $ti < $maxThumbs; $ti++):
				$isLast = ($ti === 4 && count($allImages) > 5);
			?>
			<div class="cd-thumb <?php echo $ti === 0 ? 'active' : ''; ?>"
			     onclick="cdSetImage(<?php echo $ti; ?>)"
			     data-idx="<?php echo $ti; ?>">
				<img src="<?php echo $allImages[$ti]['thumb']; ?>" alt="<?php echo htmlspecialchars($car['name']); ?> <?php echo ($ti+1); ?>" loading="lazy"/>
				<?php if ($isLast): ?>
				<div class="cd-thumb-more">+<?php echo (count($allImages) - 5); ?></div>
				<?php endif; ?>
			</div>
			<?php endfor; ?>
		</div>

		<!-- Main Image -->
		<div class="cd-main-img">
			<?php if (!empty($allImages)): ?>
			<a href="<?php echo $allImages[0]['big']; ?>" class="vrcmodal" data-fancybox="gallery" id="cd-main-link">
				<img src="<?php echo $allImages[0]['big']; ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" id="cd-main-img-el"/>
			</a>
			<?php endif; ?>
		</div>

		<?php
		// Hidden fancybox links for gallery
		if (count($allImages) > 1):
			for ($gi = 1; $gi < count($allImages); $gi++): ?>
		<a href="<?php echo $allImages[$gi]['big']; ?>" class="vrcmodal" data-fancybox="gallery" style="display:none;"></a>
			<?php endfor;
		endif; ?>
		<?php endif; ?>
	</div>

	<!-- Info Column -->
	<div class="cd-info">
		<?php if (!empty($categoryName)): ?>
		<span class="cd-car-cat"><?php echo $categoryName; ?></span>
		<?php endif; ?>

		<h1 class="cd-car-name"><?php echo $car['name']; ?></h1>

		<!-- Specs Grid -->
		<?php if (!empty($caratDefs)): ?>
		<div class="cd-specs">
			<?php 
			// SVG icon map keyed by carat name keywords (same as carslist template)
			$svgIcons = array(
				'automat' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path></svg>',
				'manual'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"></path><path d="M14 17H5"></path><circle cx="17" cy="17" r="3"></circle><circle cx="7" cy="7" r="3"></circle></svg>',
				'diesel'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>',
				'benzin'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="15" y1="22" y2="22"></line><line x1="4" x2="14" y1="9" y2="9"></line><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"></path><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"></path></svg>',
				'petrol'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="15" y1="22" y2="22"></line><line x1="4" x2="14" y1="9" y2="9"></line><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"></path><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"></path></svg>',
				'loc'     => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
				'seat'    => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
				'luggage' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 20a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2"></path><path d="M8 18V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v14"></path><path d="M10 20h4"></path><circle cx="16" cy="20" r="2"></circle><circle cx="8" cy="20" r="2"></circle></svg>',
				'door'    => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="6" y="6" width="12" height="6" rx="1"></rect><line x1="16" y1="15" x2="18" y2="15"></line></svg>',
				'color'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path></svg>',
			);
			// Default icon (info circle) for unrecognised carats
			$svgDefault = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>';
			
			$specItems = array();
			foreach ($caratDefs as $cid => $carat):
				$rawLabel = !empty($carat['textimg']) ? $carat['textimg'] : $carat['name'];
				$label = Text::_($rawLabel) ?: $rawLabel;
				$key = strtolower($label);
				$svg = $svgDefault;
				
				// Match icon by keyword
				foreach ($svgIcons as $keyword => $iconSvg) {
					if (strpos($key, $keyword) !== false) {
						$svg = $iconSvg;
						break;
					}
				}
				
				$specItems[] = array('svg' => $svg, 'label' => $label);
			endforeach;
			
			// Display specs in 2x3 grid
			foreach ($specItems as $spec):
			?>
			<div class="cd-spec">
				<div class="cd-spec-icon"><?php echo $spec['svg']; ?></div>
				<div class="cd-spec-text">
					<span class="cd-spec-value"><?php echo htmlspecialchars($spec['label']); ?></span>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<!-- Price -->
		<?php if ($showPrice): ?>
		<div class="cd-price-block">
			<span class="cd-price-from"><?php echo Text::_('VRCLISTSFROM'); ?></span>
			<span class="cd-price-cur"><?php echo $currencysymb; ?></span>
			<span class="cd-price-val"><?php echo $priceVal; ?></span>
			<span class="cd-price-per">/ <?php echo Text::_('VRCPERDAY') ?: 'zi'; ?></span>
		</div>
		<?php endif; ?>

		<!-- Request Info Button -->
		<?php if (isset($car_params['reqinfo']) && (bool)$car_params['reqinfo']): ?>
		<a href="javascript:void(0);" onclick="vrcShowRequestInfo();" class="cd-reqinfo-btn">
			<i class="fas fa-envelope"></i>
			<?php echo Text::_('VRCCARREQINFOBTN'); ?>
		</a>
		<?php endif; ?>
	</div>
</div>

<?php /* ================================================================
   3. BOOKING CARD
   ================================================================ */ ?>
<div id="vrc-bookingpart-init"></div>
<div class="cd-booking-card">
	<h3 class="cd-booking-title">
		<i class="fas fa-calendar-check" style="color:#FE5001;margin-right:8px;"></i>
		<?php echo Text::_('VRCSELECTPDDATES'); ?>
	</h3>
<?php
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

	$selform  = "<div class=\"vrcdivsearch\"><div class=\"vrcdivsearch-inner\">";
	$selform .= "<form action=\"" . JRoute::_('index.php?option=com_vikrentcar' . (!empty($pitemid) ? '&Itemid=' . $pitemid : '')) . "\" method=\"get\" onsubmit=\"return vrcValidateSearch();\">\n";
	$selform .= "<input type=\"hidden\" name=\"option\" value=\"com_vikrentcar\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"task\" value=\"search\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"cardetail\" value=\"" . $car['id'] . "\"/>\n";
	if ($ptmpl == 'component') {
		$selform .= "<input type=\"hidden\" name=\"tmpl\" value=\"component\"/>\n";
	}

	$places = '';
	$diffopentime = false;
	$closingdays = array();
	$declclosingdays = '';

	if (VikRentCar::showPlacesFront()) {
		$q = "SELECT * FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`ordering` ASC, `#__vikrentcar_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$places = $dbo->loadAssocList();
			$vrc_tn->translateContents($places, '#__vikrentcar_places');
			$selform .= "<div class=\"vrc-searchf-section-locations\">\n";
			$plapick = explode(';', $car['idplace']);
			$pladrop = explode(';', $car['idretplace']);
			foreach ($places as $kpla => $pla) {
				if (!in_array($pla['id'], $plapick) && !in_array($pla['id'], $pladrop)) {
					unset($places[$kpla]);
				}
			}
			if (count($places) == 0) {
				$places = '';
			}
		} else {
			$places = '';
		}
		if (is_array($places)) {
			foreach ($places as $kpla => $pla) {
				if (!empty($pla['opentime'])) {
					$diffopentime = true;
				}
				if (!empty($pla['closingdays'])) {
					$closingdays[$pla['id']] = $pla['closingdays'];
				}
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
			$onchangeplaces = $diffopentime ? " onchange=\"javascript: vrcSetLocOpenTime(this.value, 'pickup');\"" : "";
			$onchangeplacesdrop = $diffopentime ? " onchange=\"javascript: vrcSetLocOpenTime(this.value, 'dropoff');\"" : "";
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

			$check_pick_locs = count($plapick) && !empty($plapick[0]);
			$check_drop_locs = count($pladrop) && !empty($pladrop[0]);

			$selform .= "<div class=\"vrcsfentrycont\"><label for=\"place\">" . Text::_('VRPPLACE') . "</label><div class=\"vrcsfentryselect\"><select name=\"place\" id=\"place\"" . $onchangeplaces . ">";
			foreach ($places as $pla) {
				if (!empty($pla['lat']) && !empty($pla['lng'])) { $coordsplaces[] = $pla; }
				if ($check_pick_locs && !in_array($pla['id'], $plapick)) { continue; }
				$selform .= "<option value=\"" . $pla['id'] . "\" id=\"place" . $pla['id'] . "\">" . $pla['name'] . "</option>\n";
			}
			$selform .= "</select></div></div>\n";
			$selform .= "<div class=\"vrcsfentrycont\"><label for=\"returnplace\">" . Text::_('VRRETURNCARORD') . "</label><div class=\"vrcsfentryselect\"><select name=\"returnplace\" id=\"returnplace\"" . (strlen($onchangeplacesdrop) > 0 ? $onchangeplacesdrop : "") . ">";
			foreach ($places as $pla) {
				if ($check_drop_locs && !in_array($pla['id'], $pladrop)) { continue; }
				$selform .= "<option value=\"" . $pla['id'] . "\" id=\"returnplace" . $pla['id'] . "\">" . $pla['name'] . "</option>\n";
			}
			$selform .= "</select></div></div>\n";
			$selform .= "</div>\n";
		}
	}

	// Hours/Minutes
	if ($diffopentime && is_array($places) && isset($places[$indvrcplace]) && !empty($places[$indvrcplace]['opentime'])) {
		$parts = explode("-", $places[$indvrcplace]['opentime']);
		if (is_array($parts) && $parts[0] != $parts[1]) {
			$opent = VikRentCar::getHoursMinutes($parts[0]);
			$closet = VikRentCar::getHoursMinutes($parts[1]);
			$i = $opent[0]; $imin = $opent[1]; $j = $closet[0];
		} else { $i = 0; $imin = 0; $j = 23; }
	} else {
		$timeopst = VikRentCar::getTimeOpenStore();
		if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
			$opent = VikRentCar::getHoursMinutes($timeopst[0]);
			$closet = VikRentCar::getHoursMinutes($timeopst[1]);
			$i = $opent[0]; $imin = $opent[1]; $j = $closet[0];
		} else { $i = 0; $imin = 0; $j = 23; }
	}

	$hours = "";
	$pickhdeftime = isset($places[$indvrcplace]) && !empty($places[$indvrcplace]['defaulttime']) ? ((int)$places[$indvrcplace]['defaulttime'] / 3600) : '';
	if (!($i < $j)) {
		while (intval($i) != (int)$j) {
			$sayi = $i < 10 ? "0" . $i : $i;
			if ($nowtf != 'H:i') { $ampm = $i < 12 ? ' am' : ' pm'; $ampmh = $i > 12 ? ($i - 12) : $i; $sayh = $ampmh < 10 ? "0" . $ampmh . $ampm : $ampmh . $ampm; } else { $sayh = $sayi; }
			$hours .= "<option value=\"" . (int)$i . "\"" . ($pickhdeftime == (int)$i ? ' selected="selected"' : '') . ">" . $sayh . "</option>\n";
			$i++; $i = $i > 23 ? 0 : $i;
		}
		$sayi = $i < 10 ? "0" . $i : $i;
		if ($nowtf != 'H:i') { $ampm = $i < 12 ? ' am' : ' pm'; $ampmh = $i > 12 ? ($i - 12) : $i; $sayh = $ampmh < 10 ? "0" . $ampmh . $ampm : $ampmh . $ampm; } else { $sayh = $sayi; }
		$hours .= "<option value=\"" . (int)$i . "\">" . $sayh . "</option>\n";
	} else {
		while ($i <= $j) {
			$sayi = $i < 10 ? "0" . $i : $i;
			if ($nowtf != 'H:i') { $ampm = $i < 12 ? ' am' : ' pm'; $ampmh = $i > 12 ? ($i - 12) : $i; $sayh = $ampmh < 10 ? "0" . $ampmh . $ampm : $ampmh . $ampm; } else { $sayh = $sayi; }
			$hours .= "<option value=\"" . (int)$i . "\"" . ($pickhdeftime == (int)$i ? ' selected="selected"' : '') . ">" . $sayh . "</option>\n";
			$i++;
		}
	}
	$minutes = "";
	for ($i = 0; $i < 60; $i += 15) {
		$i = $i < 10 ? "0" . $i : $i;
		$minutes .= "<option value=\"" . (int)$i . "\"" . ((int)$i == $imin ? " selected=\"selected\"" : "") . ">" . $i . "</option>\n";
	}

	// jQuery UI datepicker
	if ($calendartype == "jqueryui" || true) {
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
		monthNamesShort: ["' . mb_substr(Text::_('VRMONTHONE'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHTWO'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHTHREE'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHFOUR'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHFIVE'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHSIX'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHSEVEN'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHEIGHT'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHNINE'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHTEN'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHELEVEN'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRMONTHTWELVE'), 0, 3, 'UTF-8') . '"],
		dayNames: ["' . Text::_('VRCJQCALSUN') . '","' . Text::_('VRCJQCALMON') . '","' . Text::_('VRCJQCALTUE') . '","' . Text::_('VRCJQCALWED') . '","' . Text::_('VRCJQCALTHU') . '","' . Text::_('VRCJQCALFRI') . '","' . Text::_('VRCJQCALSAT') . '"],
		dayNamesShort: ["' . mb_substr(Text::_('VRCJQCALSUN'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALMON'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALTUE'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALWED'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALTHU'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALFRI'), 0, 3, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALSAT'), 0, 3, 'UTF-8') . '"],
		dayNamesMin: ["' . mb_substr(Text::_('VRCJQCALSUN'), 0, 2, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALMON'), 0, 2, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALTUE'), 0, 2, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALWED'), 0, 2, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALTHU'), 0, 2, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALFRI'), 0, 2, 'UTF-8') . '","' . mb_substr(Text::_('VRCJQCALSAT'), 0, 2, 'UTF-8') . '"],
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

		// Restrictions
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

		// Closing days JS
		if (strlen($declclosingdays) > 0) {
			$declclosingdays .= '
function pickupClosingDays(date){var dmy=date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate();var wday=date.getDay().toString();var arrlocclosd=jQuery("#place").val();var checklocarr=window["loc"+arrlocclosd+"closingdays"];if(jQuery.inArray(dmy,checklocarr)==-1&&jQuery.inArray(wday,checklocarr)==-1){return [true,""];}else{return [false,"","' . addslashes(Text::_('VRCLOCDAYCLOSED')) . '"];}}
function dropoffClosingDays(date){var dmy=date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate();var wday=date.getDay().toString();var arrlocclosd=jQuery("#returnplace").val();var checklocarr=window["loc"+arrlocclosd+"closingdays"];if(jQuery.inArray(dmy,checklocarr)==-1&&jQuery.inArray(wday,checklocarr)==-1){return [true,""];}else{return [false,"","' . addslashes(Text::_('VRCLOCDAYCLOSED')) . '"];}}';
			$document->addScriptDeclaration($declclosingdays);
		}

		// Min days of rental
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
function vrcLocationWopening(mode){if(typeof vrc_wopening_pick==='undefined'){return true;}if(mode=='pickup'){vrc_mopening_pick=null;}else{vrc_mopening_drop=null;}var loc_data=mode=='pickup'?vrc_wopening_pick:vrc_wopening_drop;var def_loc_hours=mode=='pickup'?vrc_hopening_pick:vrc_hopening_drop;var sel_d=jQuery((mode=='pickup'?'#pickupdate':'#releasedate')).datepicker('getDate');if(!sel_d){return true;}var sel_wday=sel_d.getDay();if(!vrcFullObject(loc_data)||!loc_data.hasOwnProperty(sel_wday)||!loc_data[sel_wday].hasOwnProperty('fh')){if(def_loc_hours!==null){jQuery((mode=='pickup'?'#vrccomselph':'#vrccomseldh')).html(def_loc_hours);}return true;}if(mode=='pickup'){vrc_mopening_pick=new Array(loc_data[sel_wday]['fh'],loc_data[sel_wday]['fm'],loc_data[sel_wday]['th'],loc_data[sel_wday]['tm']);}else{vrc_mopening_drop=new Array(loc_data[sel_wday]['th'],loc_data[sel_wday]['tm'],loc_data[sel_wday]['fh'],loc_data[sel_wday]['fm']);}var hlim=loc_data[sel_wday]['fh']<loc_data[sel_wday]['th']?loc_data[sel_wday]['th']:(24+loc_data[sel_wday]['th']);hlim=loc_data[sel_wday]['fh']==0&&loc_data[sel_wday]['th']==0?23:hlim;var hopts='';var def_hour=jQuery((mode=='pickup'?'#vrccomselph':'#vrccomseldh')).find('select').val();def_hour=def_hour&&def_hour.length>1&&def_hour.substr(0,1)=='0'?def_hour.substr(1):def_hour;def_hour=parseInt(def_hour);for(var h=loc_data[sel_wday]['fh'];h<=hlim;h++){var viewh=h>23?(h-24):h;hopts+='<option value=\"'+viewh+'\"'+(viewh==def_hour?' selected':'')+'>'+(viewh<10?'0'+viewh:viewh)+'</option>';}jQuery((mode=='pickup'?'#vrccomselph':'#vrccomseldh')).find('select').html(hopts);if(mode=='pickup'){setTimeout(function(){vrcLocationWopening('dropoff');},750);}}
function vrcValidateCta(date){var m=date.getMonth(),wd=date.getDay();if(vrcFullObject(vrcrestrctarange)){for(var rk in vrcrestrctarange){if(vrcrestrctarange.hasOwnProperty(rk)){var wdrangeinit=vrcGetDateObject(vrcrestrctarange[rk][0]);if(date>=wdrangeinit){var wdrangeend=vrcGetDateObject(vrcrestrctarange[rk][1]);if(date<=wdrangeend){if(jQuery.inArray('-'+wd+'-',vrcrestrctarange[rk][2])>=0){return false;}}}}}}if(vrcFullObject(vrcrestrcta)){if(vrcrestrcta.hasOwnProperty(m)&&jQuery.inArray('-'+wd+'-',vrcrestrcta[m])>=0){return false;}}return true;}
function vrcValidateCtd(date){var m=date.getMonth(),wd=date.getDay();if(vrcFullObject(vrcrestrctdrange)){for(var rk in vrcrestrctdrange){if(vrcrestrctdrange.hasOwnProperty(rk)){var wdrangeinit=vrcGetDateObject(vrcrestrctdrange[rk][0]);if(date>=wdrangeinit){var wdrangeend=vrcGetDateObject(vrcrestrctdrange[rk][1]);if(date<=wdrangeend){if(jQuery.inArray('-'+wd+'-',vrcrestrctdrange[rk][2])>=0){return false;}}}}}}if(vrcFullObject(vrcrestrctd)){if(vrcrestrctd.hasOwnProperty(m)&&jQuery.inArray('-'+wd+'-',vrcrestrctd[m])>=0){return false;}}return true;}
function vrcInitElems(){if(typeof vrc_wopening_pick==='undefined'){return true;}vrc_hopening_pick=jQuery('#vrccomselph').find('select').clone();vrc_hopening_drop=jQuery('#vrccomseldh').find('select').clone();}
jQuery(function(){
	vrcInitElems();
	jQuery('#pickupdate').datepicker({showOn:'focus'," . (count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "beforeShowDay:vrcIsDayDisabled," : "beforeShowDay:vrcIsDayFullIn,") . "onSelect:function(selectedDate){" . ($totrestrictions > 0 ? "vrcSetMinDropoffDate();" : $forcedropday) . "vrcLocationWopening('pickup');}});
	jQuery('#pickupdate').datepicker('option','dateFormat','" . $juidf . "');
	jQuery('#pickupdate').datepicker('option','minDate','" . VikRentCar::getMinDaysAdvance() . "d');
	jQuery('#pickupdate').datepicker('option','maxDate','" . VikRentCar::getMaxDateFuture() . "');
	jQuery('#releasedate').datepicker({showOn:'focus'," . (count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "beforeShowDay:vrcIsDayDisabledDropoff," : "beforeShowDay:vrcIsDayFullOut,") . "onSelect:function(selectedDate){vrcLocationWopening('dropoff');}});
	jQuery('#releasedate').datepicker('option','dateFormat','" . $juidf . "');
	jQuery('#releasedate').datepicker('option','minDate','" . VikRentCar::getMinDaysAdvance() . "d');
	jQuery('#releasedate').datepicker('option','maxDate','" . VikRentCar::getMaxDateFuture() . "');
	jQuery('#pickupdate').datepicker('option',jQuery.datepicker.regional['vikrentcar']);
	jQuery('#releasedate').datepicker('option',jQuery.datepicker.regional['vikrentcar']);
	jQuery('.vr-cal-img,.vrc-caltrigger').click(function(){var jdp=jQuery(this).prev('input.hasDatepicker');if(jdp.length){jdp.focus();}});
});";
		$document->addScriptDeclaration($sdecl);

		// Forced time check
		$forced_pickup = $config ? $config->get('forced_pickup', '') : '';
		$forced_dropoff = $config ? $config->get('forced_dropoff', '') : '';

		// Date-time fields
		$selform .= "<div class=\"vrc-searchf-section-datetimes\">\n";
		$selform .= '<div class="vrcsfentrycont"><div class="vrcsfentrylabsel"><label for="pickupdate">' . Text::_('VRPICKUPCAR') . '</label><div class="vrcsfentrydate"><input type="text" name="pickupdate" id="pickupdate" size="10" autocomplete="off" onfocus="this.blur();" readonly/><i class="' . VikRentCarIcons::i('calendar', 'vrc-caltrigger') . '"></i></div></div>';
		if (!strlen($forced_pickup)) {
			$selform .= '<div class="vrcsfentrytime"><label>' . Text::_('VRALLE') . '</label><div class="vrc-sf-time-container"><span id="vrccomselph"><select name="pickuph">' . $hours . '</select></span><span class="vrctimesep">:</span><span id="vrccomselpm"><select name="pickupm">' . $minutes . '</select></span></div></div>';
		} else {
			$fp = (int)$forced_pickup; $fph = floor($fp / 3600); $fpm = floor(($fp - ($fph * 3600)) / 60);
			$selform .= '<input type="hidden" name="pickuph" value="' . $fph . '"/><input type="hidden" name="pickupm" value="' . $fpm . '"/>';
		}
		$selform .= "</div>\n";

		$selform .= '<div class="vrcsfentrycont"><div class="vrcsfentrylabsel"><label for="releasedate">' . Text::_('VRRETURNCAR') . '</label><div class="vrcsfentrydate"><input type="text" name="releasedate" id="releasedate" size="10" autocomplete="off" onfocus="this.blur();" readonly/><i class="' . VikRentCarIcons::i('calendar', 'vrc-caltrigger') . '"></i></div></div>';
		if (!strlen($forced_dropoff)) {
			$selform .= '<div class="vrcsfentrytime"><label>' . Text::_('VRALLEDROP') . '</label><div class="vrc-sf-time-container"><span id="vrccomseldh"><select name="releaseh">' . $hours . '</select></span><span class="vrctimesep">:</span><span id="vrccomseldm"><select name="releasem">' . $minutes . '</select></span></div></div>';
		} else {
			$fd = (int)$forced_dropoff; $fdh = floor($fd / 3600); $fdm = floor(($fd - ($fdh * 3600)) / 60);
			$selform .= '<input type="hidden" name="releaseh" value="' . $fdh . '"/><input type="hidden" name="releasem" value="' . $fdm . '"/>';
		}
		$selform .= "</div>\n";
		$selform .= "</div>\n";
	}

	// Submit
	$selform .= '<div class="vrc-searchf-section-sbmt"><div class="vrcsfentrycont"><div class="vrcsfentrysubmit"><input type="submit" name="search" value="' . Text::_('VRCBOOKTHISCAR') . '" class="btn vrcdetbooksubmit vrc-pref-color-btn"/></div></div></div>';
	$selform .= (!empty($pitemid) ? '<input type="hidden" name="Itemid" value="' . $pitemid . '"/>' : '') . '</form></div>';

	// Map link
	if (count($coordsplaces) > 0) {
		$selform .= '<div class="vrclocationsbox"><div class="vrclocationsmapdiv"><a href="' . VikRentCar::externalRoute('index.php?option=com_vikrentcar&view=locationsmap&tmpl=component') . '" class="vrcmodalframe" target="_blank"><i class="' . VikRentCarIcons::i('map-marked-alt') . '"></i><span>' . Text::_('VRCLOCATIONSMAP') . '</span></a></div></div>';
	}
	$selform .= "</div>";
	echo $selform;

	// Shield info
	echo '<div class="cd-shield-info"><i class="fas fa-shield-alt"></i><span>' . (Text::_('VRCSHIELDINFO') ?: 'Anulare gratuită cu 72h înainte • Suport 24/7 • Mașini asigurate') . '</span></div>';
?>
	<script type="text/javascript">
	function vrcCleanNumber(snum) { if (snum.length > 1 && snum.substr(0, 1) == '0') { return parseInt(snum.substr(1)); } return parseInt(snum); }
	function vrcValidateSearch() {
		if (typeof jQuery === 'undefined' || typeof vrc_wopening_pick === 'undefined') return true;
		if (vrc_mopening_pick !== null) {
			var pickh = jQuery('#vrccomselph').find('select').val(), pickm = jQuery('#vrccomselpm').find('select').val();
			if (!pickh || !pickh.length || !pickm) return true;
			pickh = vrcCleanNumber(pickh); pickm = vrcCleanNumber(pickm);
			if (pickh == vrc_mopening_pick[0] && pickm < vrc_mopening_pick[1]) { jQuery('#vrccomselpm').find('select').html('<option value="'+vrc_mopening_pick[1]+'">'+(vrc_mopening_pick[1]<10?'0'+vrc_mopening_pick[1]:vrc_mopening_pick[1])+'</option>').val(vrc_mopening_pick[1]); }
			if (pickh == vrc_mopening_pick[2] && pickm > vrc_mopening_pick[3]) { jQuery('#vrccomselpm').find('select').html('<option value="'+vrc_mopening_pick[3]+'">'+(vrc_mopening_pick[3]<10?'0'+vrc_mopening_pick[3]:vrc_mopening_pick[3])+'</option>').val(vrc_mopening_pick[3]); }
		}
		if (vrc_mopening_drop !== null) {
			var droph = jQuery('#vrccomseldh').find('select').val(), dropm = jQuery('#vrccomseldm').find('select').val();
			if (!droph || !droph.length || !dropm) return true;
			droph = vrcCleanNumber(droph); dropm = vrcCleanNumber(dropm);
			if (droph == vrc_mopening_drop[0] && dropm > vrc_mopening_drop[1]) { jQuery('#vrccomseldm').find('select').html('<option value="'+vrc_mopening_drop[1]+'">'+(vrc_mopening_drop[1]<10?'0'+vrc_mopening_drop[1]:vrc_mopening_drop[1])+'</option>').val(vrc_mopening_drop[1]); }
			if (droph == vrc_mopening_drop[2] && dropm < vrc_mopening_drop[3]) { jQuery('#vrccomseldm').find('select').html('<option value="'+vrc_mopening_drop[3]+'">'+(vrc_mopening_drop[3]<10?'0'+vrc_mopening_drop[3]:vrc_mopening_drop[3])+'</option>').val(vrc_mopening_drop[3]); }
		}
		return true;
	}
	jQuery(document).ready(function() {
	<?php
	if (!empty($ppickup)) {
		if ($calendartype == "jqueryui") { ?>
		jQuery("#pickupdate").datepicker("setDate", new Date(<?php echo date('Y', $ppickup); ?>, <?php echo ((int)date('n', $ppickup) - 1); ?>, <?php echo date('j', $ppickup); ?>));
		jQuery(".ui-datepicker-current-day").click();
		<?php } else { ?>
		jQuery("#pickupdate").val("<?php echo date($df, $ppickup); ?>");
		<?php }
	}

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
	});
	</script>
<?php
} else {
	echo '<div class="cd-disabled-rent">' . VikRentCar::getDisabledRentMsg() . '</div>';
}
?>
</div><!-- /.cd-booking-card -->

<?php /* ================================================================
   4. DESCRIPTION
   ================================================================ */ ?>
<?php if (!empty($car['info'])): ?>
<div class="cd-description">
	<h2><?php echo Text::_('VRCDESCRIPTION') ?: 'Descriere'; ?></h2>
	<div class="cd-description-text">
		<?php echo $car['info']; ?>
	</div>
</div>
<?php endif; ?>

<?php /* ================================================================
   5. AVAILABILITY CALENDARS
   ================================================================ */ ?>
<?php
$pmonth = VikRequest::getInt('month', '', 'request');
$pday   = VikRequest::getInt('dt', '', 'request');
$viewingdayts = !empty($pday) && $pday >= $nowts ? $pday : $nowts;
$show_hourly_cal = (array_key_exists('shourlycal', $car_params) && intval($car_params['shourlycal']) > 0);

$arr = getdate();
$mon = $arr['mon'];
$realmon = ($mon < 10 ? "0" . $mon : $mon);
$year = $arr['year'];
$day = $realmon . "/01/" . $year;
$dayts = strtotime($day);
$validmonth = false;
if ($pmonth > 0 && $pmonth >= $dayts) { $validmonth = true; }

$moptions = "";
for ($i = 0; $i < 12; $i++) {
	$moptions .= "<option value=\"" . $dayts . "\"" . ($validmonth && $pmonth == $dayts ? " selected=\"selected\"" : "") . ">" . VikRentCar::sayMonth($arr['mon']) . " " . $arr['year'] . "</option>\n";
	$next = $arr['mon'] + 1;
	$dayts = mktime(0, 0, 0, $next, 1, $arr['year']);
	$arr = getdate($dayts);
}

if ($numcalendars > 0):
?>
<div class="cd-avail-section">
	<h2><?php echo Text::_('VRCAVAILABILITY') ?: 'Disponibilitate'; ?></h2>

	<div class="cd-legend-bar">
		<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid=' . $car['id'] . (!empty($pitemid) ? '&Itemid=' . $pitemid : '')); ?>" method="post" name="vrcmonths" style="margin:0;">
			<select name="month" onchange="javascript: document.vrcmonths.submit();" class="vrcselectm"><?php echo $moptions; ?></select>
		</form>
		<div class="cd-legend-items">
			<span class="cd-legend-item"><span class="cd-leg-dot cd-leg-free"></span> <?php echo Text::_('VRLEGFREE'); ?></span>
			<?php if ($showpartlyres): ?>
			<span class="cd-legend-item"><span class="cd-leg-dot cd-leg-warn"></span> <?php echo Text::_('VRLEGWARNING'); ?></span>
			<?php endif; ?>
			<span class="cd-legend-item"><span class="cd-leg-dot cd-leg-busy"></span> <?php echo Text::_(($show_hourly_cal ? 'VRLEGBUSYCHECKH' : 'VRLEGBUSY')); ?></span>
		</div>
	</div>

<?php
$check = is_array($busy);
if ($validmonth) {
	$arr = getdate($pmonth); $mon = $arr['mon']; $realmon = ($mon < 10 ? "0" . $mon : $mon);
	$year = $arr['year']; $day = $realmon . "/01/" . $year; $dayts = strtotime($day); $newarr = getdate($dayts);
} else {
	$arr = getdate(); $mon = $arr['mon']; $realmon = ($mon < 10 ? "0" . $mon : $mon);
	$year = $arr['year']; $day = $realmon . "/01/" . $year; $dayts = strtotime($day); $newarr = getdate($dayts);
}
$first_month_info = $newarr;
$firstwday = (int)VikRentCar::getFirstWeekDay();
$days_labels = array(Text::_('VRSUN'), Text::_('VRMON'), Text::_('VRTUE'), Text::_('VRWED'), Text::_('VRTHU'), Text::_('VRFRI'), Text::_('VRSAT'));
$days_indexes = array();
for ($i = 0; $i < 7; $i++) { $days_indexes[$i] = (6 - ($firstwday - $i) + 1) % 7; }
?>

	<div class="cd-cals-grid">
<?php
for ($jj = 1; $jj <= $numcalendars; $jj++) {
	$d_count = 0;
	$cal = "";
?>
		<div class="vrccaldivcont">
			<table class="vrccal">
				<tr><td colspan="7" align="center"><strong><?php echo VikRentCar::sayMonth($newarr['mon']) . " " . $newarr['year']; ?></strong></td></tr>
				<tr class="vrccaldays">
				<?php for ($i = 0; $i < 7; $i++) { $d_ind = ($i + $firstwday) < 7 ? ($i + $firstwday) : ($i + $firstwday - 7); echo '<td>' . $days_labels[$d_ind] . '</td>'; } ?>
				</tr>
				<tr>
<?php
	for ($i = 0, $n = $days_indexes[$newarr['wday']]; $i < $n; $i++, $d_count++) { $cal .= "<td>&nbsp;</td>"; }
	while ($newarr['mon'] == $mon) {
		if ($d_count > 6) { $d_count = 0; $cal .= "</tr>\n<tr>"; }
		$dclass = "vrctdfree"; $totfound = 0;
		if ($check) {
			$ischeckinday = false; $ischeckoutday = false;
			$lastfoundritts = 0; $lastfoundconts = -1; $lasttotfound = 0;
			foreach ($busy as $b) {
				$tmpone = getdate($b['ritiro']); $ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
				$tmptwo = getdate($b['realback']); $conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
				if ($newarr[0] >= $ritts && $newarr[0] <= $conts) {
					$totfound++;
					if ($newarr[0] == $ritts) { $lastfoundritts = $ritts; $lastfoundconts = $conts; if ($lastfoundritts != $lastfoundconts) $lasttotfound++; $ischeckinday = true; }
					elseif ($newarr[0] == $conts) { $ischeckoutday = true; $lastdropoff = $b['realback']; }
					if ($ischeckinday && !empty($lastdropoff) && $lastdropoff <= $b['ritiro']) { $unitsadjuster++; }
					if ($b['stop_sales'] == 1) { $totfound = $car['units']; $unitsadjuster = 0; break; }
				}
			}
			if ($totfound >= $car['units']) {
				$dclass = "vrctdbusy";
				if ($ischeckinday && $previousdayclass != "vrctdbusy") { $dclass = "vrctdbusy vrctdbusyforcheckin"; }
			} elseif ($totfound > 0 && $showpartlyres) { $dclass = "vrctdwarning"; }
		}
		$previousdayclass = $dclass;
		$useday = ($newarr['mday'] < 10 ? "0" . $newarr['mday'] : $newarr['mday']);
		if ($newarr[0] >= $nowts) {
			if ($show_hourly_cal) {
				$useday = '<a href="' . JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid=' . $car['id'] . '&dt=' . $newarr[0] . (!empty($pmonth) && $validmonth ? '&month=' . $pmonth : '') . (!empty($pitemid) ? '&Itemid=' . $pitemid : '')) . '">' . $useday . '</a>';
			} else {
				$useday = '<span class="vrc-cdetails-cal-pickday" data-daydate="' . date($df, $newarr[0]) . '">' . $useday . '</span>';
			}
		}
		$cal .= "<td class=\"" . $dclass . "\">" . $useday . "</td>\n";
		$dayts = mktime(0, 0, 0, $newarr['mon'], ($newarr['mday'] + 1), $newarr['year']);
		$newarr = getdate($dayts);
		$d_count++;
	}
	for ($i = $d_count; $i <= 6; $i++) { $cal .= "<td>&nbsp;</td>"; }
	echo $cal;
?>
				</tr>
			</table>
		</div>
<?php
	if ($mon == 12) { $mon = 1; $year += 1; } else { $mon += 1; }
	$dayts = mktime(0, 0, 0, $mon, 1, $year);
	$newarr = getdate($dayts);
}
?>
	</div><!-- /.cd-cals-grid -->
</div><!-- /.cd-avail-section -->
<?php endif; ?>

<?php /* ================================================================
   5b. Extra year disabled dates for datepicker (hidden logic)
   ================================================================ */
if (is_array($busy)) {
	$missing_intervals = array();
	$months_parsed = true;
	if (!isset($newarr)) {
		$now_info = getdate();
		$newarr = getdate(mktime(0, 0, 0, $now_info['mon'], $now_info['mday'], $now_info['year']));
		$months_parsed = false;
	}
	$max_ts = mktime(23, 59, 59, $newarr['mon'], $newarr['mday'], ($newarr['year'] + 1));
	$missing_intervals[$max_ts] = $newarr;
	if ($months_parsed && isset($first_month_info) && $first_month_info[0] > time() && date('Y-m', $first_month_info[0]) != date('Y-m')) {
		$missing_intervals[$first_month_info[0]] = getdate();
	}
	foreach ($missing_intervals as $max_ts => $day_start_info) {
		$newarr = $day_start_info; $lastdropoff = 0; $unitsadjuster = 0;
		while ($newarr[0] < $max_ts) {
			$totfound = 0; $ischeckinday = false; $ischeckoutday = false;
			$lastfoundritts = 0; $lastfoundconts = -1; $lasttotfound = 0;
			foreach ($busy as $b) {
				$info_in = getdate($b['ritiro']); $checkin_ts = mktime(0, 0, 0, $info_in['mon'], $info_in['mday'], $info_in['year']);
				$info_out = getdate($b['realback']); $checkout_ts = mktime(0, 0, 0, $info_out['mon'], $info_out['mday'], $info_out['year']);
				if ($newarr[0] >= $checkin_ts && $newarr[0] < $checkout_ts) {
					$totfound++;
					if ($newarr[0] == $checkin_ts) { $lastfoundritts = $checkin_ts; $lastfoundconts = $checkout_ts; if ($lastfoundritts != $lastfoundconts) $lasttotfound++; $ischeckinday = true; }
					elseif ($newarr[0] == $checkout_ts) { $ischeckoutday = true; $lastdropoff = $b['realback']; }
					if ($ischeckinday && !empty($lastdropoff) && $lastdropoff <= $b['ritiro']) { $unitsadjuster++; }
					if ($b['stop_sales'] == 1) { $totfound = $car['units']; $unitsadjuster = 0; break; }
				}
			}
			if ($totfound >= $car['units']) {
				if ($ischeckinday || !$ischeckoutday) { if ($lasttotfound > 1 || $lastfoundritts != $lastfoundconts) { if (($totfound - $unitsadjuster) >= $car['units']) { $push_disabled_in[] = '"' . date('Y-m-d', $newarr[0]) . '"'; } } }
				if (!$ischeckinday && !$ischeckoutday) { $push_disabled_out[] = '"' . date('Y-m-d', $newarr[0]) . '"'; }
			}
			$newarr = getdate(mktime(0, 0, 0, $newarr['mon'], ($newarr['mday'] + 1), $newarr['year']));
		}
	}
}
?>

<?php /* ================================================================
   6. HOURLY CALENDAR
   ================================================================ */
if ($show_hourly_cal):
	$picksondrops = VikRentCar::allowPickOnDrop();
?>
<div class="cd-hourly-cal">
	<h4><?php echo Text::sprintf('VRCAVAILSINGLEDAY', date($df, $viewingdayts)); ?></h4>
	<div class="table-responsive">
		<table class="vrc-hourly-cal table">
			<tr><td style="text-align:center;"><?php echo Text::_('VRCLEGH'); ?></td>
<?php for ($h = 0; $h <= 23; $h++) {
	if ($nowtf == 'H:i') { $sayh = $h < 10 ? "0" . $h : $h; }
	else { $ampm = $h < 12 ? ' am' : ' pm'; $ampmh = $h > 12 ? ($h - 12) : $h; $sayh = $ampmh < 10 ? "0" . $ampmh . $ampm : $ampmh . $ampm; }
	echo '<td style="text-align:center;">' . $sayh . '</td>';
} ?>
			</tr>
			<tr class="vrc-hourlycal-rowavail"><td style="text-align:center;"> </td>
<?php for ($h = 0; $h <= 23; $h++) {
	$checkhourts = ($viewingdayts + ($h * 3600));
	$dclass = "vrctdfree";
	if ($check) {
		$totfound = 0;
		foreach ($busy as $b) {
			$tmpone = getdate($b['ritiro']); $ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
			$tmptwo = getdate($b['realback']); $conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
			if ($viewingdayts >= $ritts && $viewingdayts <= $conts) {
				if ($b['stop_sales'] == 1) { $totfound = $car['units']; break; }
				if ($checkhourts >= $b['ritiro'] && $checkhourts <= $b['realback']) {
					if ($picksondrops && !($checkhourts > $b['ritiro'] && $checkhourts < $b['realback']) && $checkhourts == $b['realback']) continue;
					$totfound++;
				}
			}
		}
		if ($totfound >= $car['units']) { $dclass = "vrctdbusy"; }
		elseif ($totfound > 0 && $showpartlyres) { $dclass = "vrctdwarning"; }
	}
	echo '<td style="text-align:center;" class="' . $dclass . '"> </td>';
} ?>
			</tr>
		</table>
	</div>
</div>
<?php endif; ?>

<?php /* ================================================================
   7. REQUEST INFO MODAL
   ================================================================ */
if (isset($car_params['reqinfo']) && (bool)$car_params['reqinfo']):
	$cur_user = JFactory::getUser();
	$cur_email = (property_exists($cur_user, 'email') && !empty($cur_user->email)) ? $cur_user->email : '';
?>
<div id="vrcdialog-overlay" style="display: none;">
	<a class="vrcdialog-overlay-close" href="javascript:void(0);"></a>
	<div class="vrcdialog-inner vrcdialog-reqinfo">
		<h3><?php echo Text::sprintf('VRCCARREQINFOTITLE', $car['name']); ?></h3>
		<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=reqinfo' . (!empty($pitemid) ? '&Itemid=' . $pitemid : '')); ?>" method="post" onsubmit="return vrcValidateReqInfo();">
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
						? '<a href="' . $this->terms_fields['poplink'] . '" target="_blank" class="vrcmodalframe">' . Text::_($this->terms_fields['name']) . '</a>'
						: '<label for="vrcf-inp" style="display:inline-block;">' . Text::_($this->terms_fields['name']) . '</label>';
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
				<div class="vrcdialog-reqinfo-formentry vrcdialog-reqinfo-formentry-captcha">
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

</div><!-- /.cd-container -->

<script type="text/javascript">
/* Gallery thumbnail switching */
var cdAllImages = <?php echo json_encode(array_map(function($img) { return $img['big']; }, $allImages)); ?>;
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

<?php
VikRentCar::printTrackingCode(isset($this) ? $this : null);
