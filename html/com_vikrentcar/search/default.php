<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/search/default.php
 * AutoRent Design — Search Results v1
 * Matches carslist/default.php design (ar-* tokens)
 */

defined('_JEXEC') OR die('Restricted Area');
use Joomla\CMS\Language\Text;

$res                 = $this->res;
$days                = $this->days;
$pickup              = $this->pickup;
$release             = $this->release;
$place               = $this->place;
$all_characteristics = $this->all_characteristics;
$navig               = $this->navig;

$characteristics_map = vikrentcar::loadCharacteristics(
	(count($all_characteristics) > 0 ? array_keys($all_characteristics) : array()),
	$vrc_tn
);
$currencysymb     = vikrentcar::getCurrencySymb();
$usecharatsfilter = vikrentcar::useCharatsFilter();
$returnplace      = JFactory::getApplication()->input->get('returnplace', '', 'request');
$pitemid          = JFactory::getApplication()->input->get('Itemid', '', 'request');
$ptmpl            = JFactory::getApplication()->input->get('tmpl', '', 'request');

// Sort characteristics (only those in results)
if ($usecharatsfilter === true && empty($navig) && count($all_characteristics) > 0) {
	$all_characteristics = vikrentcar::sortCharacteristics($all_characteristics, $characteristics_map);
} else {
	$usecharatsfilter = false;
}

// Format timestamps to readable dates for display
$pickupDisplay  = (is_numeric($pickup)  && $pickup  > 0) ? date('d.m.Y', (int)$pickup)  : htmlspecialchars($pickup);
$releaseDisplay = (is_numeric($release) && $release > 0) ? date('d.m.Y', (int)$release) : htmlspecialchars($release);

// Resolve place ID → name (VikRentCar stores numeric IDs as $place)
$placeDisplay = htmlspecialchars($place);
if (is_numeric($place) && (int)$place > 0) {
	try {
		$_dbo = JFactory::getDbo();
		$_dbo->setQuery("SELECT `name` FROM `#__vikrentcar_places` WHERE `id` = " . (int)$place . " LIMIT 1;");
		$_placeName = $_dbo->loadResult();
		if (!empty($_placeName)) {
			$placeDisplay = htmlspecialchars($_placeName);
		}
	} catch (Exception $_e) {}
}

// Pre-load all car data
$carDataMap = array();
foreach ($res as $k => $r) {
	$getcar        = vikrentcar::getCarInfo($k, $vrc_tn);
	$car_params    = (!empty($getcar['params'])) ? json_decode($getcar['params'], true) : array();
	$has_promotion = array_key_exists('promotion', $r[0]);
	$car_cost      = vikrentcar::sayCostPlusIva($r[0]['cost'], $r[0]['idprice']);
	$vthumb        = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_vikrentcar'.DS.'resources'.DS.'vthumb_'.$getcar['img'];
	$imgpath       = file_exists($vthumb)
		? 'administrator/components/com_vikrentcar/resources/vthumb_'.$getcar['img']
		: 'administrator/components/com_vikrentcar/resources/'.$getcar['img'];
	$carDataMap[$k] = array(
		'car'           => $getcar,
		'car_params'    => $car_params,
		'has_promotion' => $has_promotion,
		'car_cost'      => $car_cost,
		'imgpath'       => $imgpath,
		'r'             => $r,
	);
}

// Load categories for sidebar (same as carslist)
$sidebarCats = array();
$dbo = JFactory::getDbo();
if (VikRentCar::showCategoriesFront()) {
	try {
		$dbo->setQuery("SELECT `id`,`name` FROM `#__vikrentcar_categories` ORDER BY `ordering` ASC, `name` ASC;");
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$sidebarCats = $dbo->loadAssocList();
			if (isset($vrc_tn) && is_object($vrc_tn) && method_exists($vrc_tn, 'translateContents')) {
				$vrc_tn->translateContents($sidebarCats, '#__vikrentcar_categories');
			}
		}
	} catch (Exception $e) {}
}

// Count categories in results
$catCounts = array();
foreach ($carDataMap as $d) {
	$idcat = isset($d['car']['idcat']) ? $d['car']['idcat'] : '';
	foreach (array_filter(explode(';', $idcat)) as $cid) {
		$cid = trim($cid);
		if ($cid === '') continue;
		$catCounts[$cid] = isset($catCounts[$cid]) ? $catCounts[$cid] + 1 : 1;
	}
}

// Has sidebar = always show (characteristics OR categories present)
$hasSidebar = ($usecharatsfilter && count($all_characteristics) > 0) || count($sidebarCats) > 0;

/**
 * Helper: build sidebar filter HTML (characteristics + categories)
 * Adapted from carslist/default.php arBuildFilterHTML()
 */
function arSrBuildFilterHTML($all_characteristics, $characteristics_map, $sidebarCats, $catCounts) {
	$html = '';

	if (!empty($all_characteristics)) {
		$html .= '<div class="ar-fsec">';
		$html .= '<div class="ar-ftitle" onclick="arSrToggleSection(this)">';
		$html .= '<span class="ar-ftitle-left"><i class="fa fa-sliders"></i> ' . Text::_('VRCCHARACTERISTICS') . '</span>';
		$html .= '<i class="fa fa-chevron-up ar-ftitle-arrow"></i>';
		$html .= '</div>';
		$html .= '<div class="ar-fopts ar-fopts-2col">';
		foreach ($all_characteristics as $chk => $chv) {
			if (empty($characteristics_map[$chk])) continue;
			$rawLabel = !empty($characteristics_map[$chk]['textimg'])
				? $characteristics_map[$chk]['textimg']
				: $characteristics_map[$chk]['name'];
			$rawLabel = trim($rawLabel);
			$label = (strlen(Text::_($rawLabel)) > 0) ? Text::_($rawLabel) : $rawLabel;

			$ll = mb_strtolower($rawLabel);
			if ($ll === 'automat' || $ll === 'manual') {
				$group = 'transmission';
			} elseif ($ll === 'diesel' || $ll === 'benzină' || $ll === 'benzina' || $ll === 'hybrid') {
				$group = 'fuel';
			} else {
				$group = 'carat_' . (int)$chk;
			}

			$html .= '<label class="ar-fopt">';
			$html .= '<input type="checkbox" class="ar-cb" data-group="' . $group . '"'
			       . ' data-caratid="' . (int)$chk . '"'
			       . ' data-label="' . htmlspecialchars($label) . '"'
			       . ' onchange="arSrFilter(this)"/>';
			$html .= '<span class="ar-fbox"><svg class="ar-fchk" viewBox="0 0 12 12" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>';
			$html .= '<span>' . htmlspecialchars($label) . ' <span class="ar-fcnt">(' . (int)$chv . ')</span></span>';
			$html .= '</label>';
		}
		$html .= '</div></div>';
	}

	if (!empty($sidebarCats)) {
		$html .= '<div class="ar-fsec">';
		$html .= '<div class="ar-ftitle" onclick="arSrToggleSection(this)">';
		$html .= '<span class="ar-ftitle-left"><i class="fa fa-car"></i> ' . (Text::_('VRCARCAT') ?: 'Caroserie') . '</span>';
		$html .= '<i class="fa fa-chevron-up ar-ftitle-arrow"></i>';
		$html .= '</div>';
		$html .= '<div class="ar-fopts ar-fopts-2col">';
		foreach ($sidebarCats as $cat) {
			$cnt = isset($catCounts[$cat['id']]) ? (int)$catCounts[$cat['id']] : 0;
			if ($cnt === 0) continue;
			$catName = Text::_($cat['name']) ?: $cat['name'];
			$html .= '<label class="ar-fopt">';
			$html .= '<input type="checkbox" class="ar-cb" data-group="category"'
			       . ' data-catid="' . (int)$cat['id'] . '"'
			       . ' data-label="' . htmlspecialchars($catName) . '"'
			       . ' onchange="arSrFilter(this)"/>';
			$html .= '<span class="ar-fbox"><svg class="ar-fchk" viewBox="0 0 12 12" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>';
			$html .= '<span>' . htmlspecialchars($catName) . ' <span class="ar-fcnt">(' . $cnt . ')</span></span>';
			$html .= '</label>';
		}
		$html .= '</div></div>';
	}

	return $html;
}
?>
<style>
/* ================================================================
   AutoRent Search Results — design v1  (ar-* tokens)
   ================================================================ */

.ar-hero {
	background: linear-gradient(135deg,#0a0a0a 0%,#1a1a1a 50%,#0a0a0a 100%);
	color:#fff; padding:44px 24px 36px; text-align:center;
	margin-bottom:28px; border-radius:0 0 16px 16px;
}
.ar-hero h1 {
	font-size:clamp(1.6rem,3.5vw,2.4rem); font-weight:800;
	margin:0 0 8px; line-height:1.15;
}
.ar-hero-meta {
	display:flex; flex-wrap:wrap; justify-content:center;
	gap:10px; margin-top:12px;
}
.ar-hero-meta-item {
	display:inline-flex; align-items:center; gap:6px;
	background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15);
	border-radius:20px; padding:5px 13px;
	font-size:13px; color:#d1d5db;
}
.ar-hero-meta-item i { color:#FE5001; font-size:12px; }
.ar-hero-meta-item strong { color:#fff; }

/* Toolbar */
.ar-toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin-bottom:14px; }
.ar-search-wrap { position:relative; flex:1; min-width:140px; }
.ar-search-wrap svg {
	position:absolute; left:13px; top:50%; transform:translateY(-50%);
	width:17px; height:17px; color:#9ca3af; pointer-events:none;
}
.ar-search-wrap input {
	width:100%; padding:11px 14px 11px 40px;
	background:#fff; border:1.5px solid #e5e7eb; border-radius:10px;
	font-size:14px; color:#0a0a0a; transition:border-color .2s,box-shadow .2s;
}
.ar-search-wrap input:focus { outline:none; border-color:#FE5001; box-shadow:0 0 0 3px rgba(254,80,1,.12); }

/* View toggle */
.ar-vtoggle { display:flex; background:#fff; border:1.5px solid #e5e7eb; border-radius:10px; padding:3px; gap:2px; }
.ar-vbtn {
	display:flex; align-items:center; justify-content:center;
	width:38px; height:38px; border-radius:7px; border:none;
	background:transparent; color:#6b7280; cursor:pointer;
	transition:background .2s,color .2s; text-decoration:none; line-height:1;
}
.ar-vbtn.active,.ar-vbtn:hover { background:#FE5001 !important; color:#fff !important; }
.ar-vbtn i { font-size:15px; }

/* Sort */
.ar-sort { position:relative; }
.ar-sort-btn {
	display:flex; align-items:center; gap:8px; padding:10px 14px;
	background:#fff; border:1.5px solid #e5e7eb; border-radius:10px;
	font-size:13px; color:#374151; cursor:pointer; white-space:nowrap;
	transition:border-color .2s;
}
.ar-sort-btn:hover { border-color:#FE5001; }
.ar-sort-btn i { color:#9ca3af; }
.ar-sort-dd {
	display:none; position:absolute; top:calc(100% + 5px); right:0;
	min-width:210px; background:#fff; border:1.5px solid #e5e7eb;
	border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,.1);
	overflow:hidden; z-index:300;
}
.ar-sort-dd.open { display:block; }
.ar-sort-opt {
	display:block; width:100%; padding:10px 16px; text-align:left;
	background:none; border:none; font-size:13px; color:#374151;
	cursor:pointer; transition:background .15s;
}
.ar-sort-opt:hover { background:#f5f5f5; }
.ar-sort-opt.sel { color:#FE5001; font-weight:700; background:rgba(254,80,1,.06); }

/* Mobile filter */
.ar-mob-filter-btn {
	display:none; align-items:center; justify-content:center; gap:8px;
	padding:10px 20px; background:#FE5001; color:#fff;
	border:none; border-radius:10px; font-size:14px; font-weight:700;
	cursor:pointer; transition:background .2s; width:100%;
}
.ar-mob-filter-btn:hover { background:#E54801; }
.ar-mob-filter-btn i { font-size:14px; }
@media(max-width:900px){.ar-mob-filter-btn{display:flex;}}

/* Chips */
.ar-chips { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px; }
.ar-chip {
	display:inline-flex; align-items:center; gap:5px;
	padding:4px 10px; background:#FE5001; color:#fff;
	border-radius:20px; font-size:12px; font-weight:600;
	cursor:pointer; transition:background .15s;
}
.ar-chip:hover { background:#E54801; }
.ar-chip-x { font-size:14px; line-height:1; }
.ar-count { font-size:13px; color:#9ca3af; margin-bottom:16px; }

/* Drawer */
.ar-drawer-overlay {
	display:none; position:fixed; inset:0;
	background:rgba(0,0,0,.5); z-index:1000;
	opacity:0; transition:opacity .3s;
}
.ar-drawer-overlay.open { display:block; opacity:1; }
.ar-drawer {
	position:fixed; top:0; left:0; bottom:0;
	width:min(320px,90vw); background:#fff; z-index:1001;
	transform:translateX(-100%);
	transition:transform .3s cubic-bezier(.4,0,.2,1);
	overflow-y:auto; box-shadow:4px 0 30px rgba(0,0,0,.15);
}
.ar-drawer.open { transform:translateX(0); }
.ar-drawer-header {
	display:flex; align-items:center; justify-content:space-between;
	padding:18px 20px; border-bottom:1px solid #f3f4f6;
	position:sticky; top:0; background:#fff; z-index:1;
}
.ar-drawer-header h3 { font-size:1.1rem; font-weight:700; color:#0a0a0a; margin:0; }
.ar-drawer-close {
	width:32px; height:32px; border-radius:8px; border:none;
	background:#f5f5f5; cursor:pointer; display:flex;
	align-items:center; justify-content:center;
	font-size:18px; color:#374151; transition:background .15s;
}
.ar-drawer-close:hover { background:#e5e7eb; }
.ar-drawer-body { padding:16px 20px 24px; }

/* Sidebar */
.ar-sidebar {
	width:240px; flex-shrink:0; background:#fff;
	border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,.07);
	padding:18px; position:sticky; top:100px;
}
@media(max-width:900px){.ar-sidebar{display:none;}}
.ar-sb-title { font-size:1rem; font-weight:700; color:#0a0a0a; margin:0 0 16px; }
.ar-fsec { border-bottom:1px solid #f3f4f6; padding-bottom:14px; margin-bottom:14px; }
.ar-fsec:last-child { border-bottom:none; padding-bottom:0; margin-bottom:0; }
.ar-ftitle {
	display:flex; align-items:center; justify-content:space-between;
	margin-bottom:10px; cursor:pointer; user-select:none;
}
.ar-ftitle-left {
	display:flex; align-items:center; gap:7px;
	font-size:11px; font-weight:700; color:#374151;
	text-transform:uppercase; letter-spacing:.05em;
}
.ar-ftitle-left i { font-size:13px; color:#0a0a0a; }
.ar-ftitle-arrow { font-size:11px; color:#9ca3af; transition:transform .2s; }
.ar-ftitle-arrow.collapsed { transform:rotate(-90deg); }
.ar-fopts { display:flex; flex-direction:column; gap:7px; }
.ar-fopts-2col { display:grid; grid-template-columns:1fr 1fr; gap:7px 12px; }
.ar-fopt {
	display:flex; align-items:flex-start; gap:8px;
	cursor:pointer; font-size:13px; color:#374151;
	user-select:none; line-height:1.3;
}
.ar-fopt input[type=checkbox] { display:none; }
.ar-fbox {
	width:16px; height:16px; flex-shrink:0; margin-top:1px;
	border:2px solid #d1d5db; border-radius:4px; background:#fff;
	display:flex; align-items:center; justify-content:center;
	transition:border-color .15s,background .15s;
}
.ar-fopt input:checked + .ar-fbox { border-color:#FE5001; background:#FE5001; }
.ar-fchk { display:none; }
.ar-fopt input:checked + .ar-fbox .ar-fchk { display:block; }
.ar-fcnt { color:#9ca3af; font-size:11px; white-space:nowrap; }

/* Layout */
.ar-main { display:flex; gap:24px; align-items:flex-start; }
.ar-cars-area { flex:1; min-width:0; }
.ar-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(270px,1fr)); gap:18px; }
.ar-list { display:flex; flex-direction:column; gap:14px; }
.ar-list .ar-card { flex-direction:row !important; }
.ar-list .ar-card-img {
	width:220px !important; height:auto !important;
	min-height:170px; flex-shrink:0; aspect-ratio:unset !important;
	border-radius:14px 0 0 14px !important;
}
.ar-list .ar-card-img img { height:100% !important; border-radius:14px 0 0 14px !important; }
.ar-list .ar-card-body { flex:1; }
@media(max-width:640px){
	.ar-list .ar-card { flex-direction:column !important; }
	.ar-list .ar-card-img { width:100% !important; height:200px !important; border-radius:14px 14px 0 0 !important; }
	.ar-list .ar-card-img img { border-radius:14px 14px 0 0 !important; }
}

/* Card */
.ar-card {
	background:#fff; border-radius:16px; overflow:hidden;
	box-shadow:0 2px 12px rgba(0,0,0,.06); border:1.5px solid #f3f4f6;
	display:flex; flex-direction:column;
	transition:transform .25s,box-shadow .25s,border-color .25s;
}
.ar-card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(0,0,0,.12); border-color:#FE5001; }
.ar-card.vrc-promotion-price { border-color:#FE5001; }
.ar-card-img { position:relative; aspect-ratio:4/3; overflow:hidden; background:#f3f4f6; }
.ar-card-img a { display:block; width:100%; height:100%; }
.ar-card-img img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s; }
.ar-card:hover .ar-card-img img { transform:scale(1.05); }
.ar-img-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#d1d5db; }
.ar-img-ph svg { width:52px; height:52px; }
.ar-badge {
	position:absolute; bottom:10px; left:10px;
	background:rgba(10,10,10,.72); backdrop-filter:blur(4px);
	border-radius:8px; padding:5px 10px; line-height:1.2;
	pointer-events:none; max-width:calc(100% - 20px);
}
.ar-badge-from { display:block; font-size:9px; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em; }
.ar-badge-val  { font-size:1.05rem; font-weight:800; color:#fff; }
.ar-badge-per  { font-size:9px; color:#9ca3af; }
.ar-promo-badge {
	position:absolute; top:10px; left:10px;
	background:#FE5001; color:#fff; border-radius:6px;
	padding:3px 9px; font-size:11px; font-weight:700;
	text-transform:uppercase; letter-spacing:.04em;
}
.ar-card-body { padding:16px; display:flex; flex-direction:column; gap:10px; }
.ar-card-name { font-size:1.05rem; font-weight:700; color:#0a0a0a; text-decoration:none; display:block; transition:color .15s; }
.ar-card-name:hover { color:#FE5001; }
.ar-card-category { font-size:11px; color:#9ca3af; margin-top:-6px; }
.ar-specs {
	display:grid !important; grid-template-columns:repeat(3,1fr) !important;
	gap:4px !important; padding-bottom:12px; border-bottom:1px solid #f3f4f6;
}
.ar-specs > span,
.ar-specs .vrc-car-carat-item,
.ar-specs > div {
	display:flex !important; flex-direction:column !important;
	align-items:center !important; justify-content:flex-start !important;
	text-align:center !important; gap:3px !important;
	padding:6px 4px !important; font-size:11px !important;
	color:#6b7280 !important; background:none !important;
	border:none !important; border-radius:0 !important; line-height:1.3 !important;
}
.ar-specs > span i,.ar-specs .vrc-car-carat-item i,.ar-specs > div i {
	font-size:18px !important; color:#9ca3af !important; display:block !important; margin-bottom:2px !important;
}
.ar-specs > span img,.ar-specs .vrc-car-carat-item img,.ar-specs > div img {
	width:22px !important; height:22px !important; object-fit:contain !important; margin-bottom:2px !important;
}
.ar-specs br { display:none !important; }
.ar-promo-block {
	background:rgba(254,80,1,.07); border:1px solid rgba(254,80,1,.2);
	border-radius:8px; padding:8px 12px; font-size:12px; color:#374151;
}
.ar-card-footer { display:flex; flex-direction:column; gap:10px; }
.ar-price-row { display:flex; align-items:baseline; gap:4px; flex-wrap:wrap; }
.ar-price-label { font-size:12px; color:#9ca3af; }
.ar-price-total { font-size:1.5rem; font-weight:800; color:#0a0a0a; }
.ar-price-cur   { font-size:12px; color:#9ca3af; }
.ar-price-daily {
	font-size:11px; color:#6b7280; background:#f3f4f6;
	border-radius:5px; padding:2px 7px; display:inline-block;
}
.ar-btns { display:flex; gap:8px; }
.ar-btn-p {
	flex:1; display:flex; align-items:center; justify-content:center;
	padding:10px 12px; background:#FE5001; color:#fff;
	border-radius:8px; font-size:13px; font-weight:700;
	text-decoration:none; border:none; cursor:pointer;
	transition:background .2s; text-align:center;
}
.ar-btn-p:hover { background:#E54801; color:#fff; }
.ar-btn-o {
	flex:1; display:flex; align-items:center; justify-content:center;
	padding:10px 12px; background:#fff; color:#374151;
	border:1.5px solid #e5e7eb; border-radius:8px;
	font-size:13px; font-weight:600; text-decoration:none; cursor:pointer;
	transition:border-color .2s,color .2s; text-align:center;
}
.ar-btn-o:hover { border-color:#FE5001; color:#FE5001; }
.ar-goback { margin-top:32px; text-align:center; }
.ar-goback a {
	display:inline-flex; align-items:center; gap:8px;
	padding:11px 24px; background:#fff; color:#374151;
	border:1.5px solid #e5e7eb; border-radius:10px;
	font-size:14px; font-weight:600; text-decoration:none;
	transition:border-color .2s,color .2s;
}
.ar-goback a:hover { border-color:#FE5001; color:#FE5001; }
.ar-goback a i { font-size:15px; }
.ar-empty { text-align:center; padding:60px 20px; display:none; }
.ar-empty svg { width:48px; height:48px; color:#d1d5db; margin-bottom:14px; }
.ar-empty h3 { font-size:1.2rem; font-weight:700; color:#0a0a0a; margin-bottom:6px; }
.ar-empty p { color:#6b7280; }
.vrc-pagination { margin-top:36px; text-align:center; }
.vrc-pagination .pagination { display:inline-flex; gap:5px; list-style:none; padding:0; margin:0; }
.vrc-pagination .pagination li > a,
.vrc-pagination .pagination li > span {
	display:flex; align-items:center; justify-content:center;
	min-width:36px; height:36px; padding:0 8px;
	border-radius:8px; border:1.5px solid #e5e7eb;
	font-size:13px; font-weight:600; color:#374151; text-decoration:none;
	transition:background .15s,border-color .15s,color .15s;
}
.vrc-pagination .pagination li > a:hover { border-color:#FE5001; color:#FE5001; }
.vrc-pagination .pagination li.active > a,
.vrc-pagination .pagination li.active > span,
.vrc-pagination .pagination li > span.current { background:#FE5001; border-color:#FE5001; color:#fff; }
@media(max-width:600px){
	.ar-vtoggle { display:none; }
	.ar-grid { grid-template-columns:1fr 1fr; gap:12px; }
	.ar-card-body { padding:12px; }
	.ar-card-name { font-size:.95rem; }
	.ar-price-total { font-size:1.25rem; }
}
@media(max-width:400px){ .ar-grid { grid-template-columns:1fr; } }
</style>

<!-- HERO -->
<div class="ar-hero">
	<h1><?php echo Text::_('VRCSEARCHRESULTSHEADING'); ?></h1>
	<div class="ar-hero-meta">
		<?php if (!empty($place)) { ?>
		<span class="ar-hero-meta-item">
			<i class="fa fa-map-marker"></i>
			<strong><?php echo $placeDisplay; ?></strong>
		</span>
		<?php } ?>
		<?php if ($days > 0) { ?>
		<span class="ar-hero-meta-item">
			<i class="fa fa-calendar"></i>
			<strong><?php echo (int)$days; ?></strong>&nbsp;<?php echo ((int)$days === 1 ? Text::_('VRCSEARCHDAY') : Text::_('VRCSEARCHDAYS')); ?>
		</span>
		<?php } ?>
		<?php if (!empty($pickupDisplay)) { ?>
		<span class="ar-hero-meta-item">
			<i class="fa fa-clock-o"></i>
			<?php echo $pickupDisplay; ?>
			<?php if (!empty($releaseDisplay)) { echo '&nbsp;&rarr;&nbsp;' . $releaseDisplay; } ?>
		</span>
		<?php } ?>
	</div>
</div>

<!-- Toolbar -->
<div class="ar-toolbar">
	<div class="ar-search-wrap">
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
		<input type="text" id="ar-q" placeholder="<?php echo Text::_('VRCSEARCHCARS'); ?>"/>
	</div>
	<div class="ar-vtoggle">
		<a href="javascript:void(0)" id="ar-grid-btn" class="ar-vbtn active" title="<?php echo Text::_('VRCVIEWGRID'); ?>"><i class="fa fa-th"></i></a>
		<a href="javascript:void(0)" id="ar-list-btn" class="ar-vbtn" title="<?php echo Text::_('VRCVIEWLIST'); ?>"><i class="fa fa-th-list"></i></a>
	</div>
	<div class="ar-sort">
		<button class="ar-sort-btn" onclick="arSrToggleSort()" type="button">
			<i class="fa fa-sort"></i> <span id="ar-sort-lbl"><?php echo Text::_('VRCSORTPRICELH'); ?></span>
		</button>
		<div class="ar-sort-dd" id="ar-sort-dd">
			<button class="ar-sort-opt sel" onclick="arSrSort('price-asc',this)" type="button"><?php echo Text::_('VRCSORTPRICELH'); ?></button>
			<button class="ar-sort-opt" onclick="arSrSort('price-desc',this)" type="button"><?php echo Text::_('VRCSORTPRICEHL'); ?></button>
		</div>
	</div>
</div>

<?php if ($hasSidebar) { ?>
<!-- Mobile filter button -->
<button class="ar-mob-filter-btn" onclick="arSrOpenDrawer()" type="button">
	<i class="fa fa-sliders"></i> <?php echo Text::_('VRCFILTERS'); ?>
</button>
<?php } ?>

<!-- Chips + count -->
<div class="ar-chips" id="ar-chips"></div>
<div class="ar-count" id="ar-count"><?php echo count($res); ?> <?php echo Text::_('VRCAUTOMOBILEFOUND'); ?></div>

<?php if ($hasSidebar) { ?>
<!-- Mobile drawer -->
<div class="ar-drawer-overlay" id="ar-overlay" onclick="arSrCloseDrawer()"></div>
<div class="ar-drawer" id="ar-drawer">
	<div class="ar-drawer-header">
		<h3><?php echo Text::_('VRCFILTERS'); ?></h3>
		<button class="ar-drawer-close" onclick="arSrCloseDrawer()" type="button">&times;</button>
	</div>
	<div class="ar-drawer-body" id="ar-drawer-body"></div>
</div>
<?php } ?>

<!-- Main layout -->
<div class="ar-main">

<?php if ($hasSidebar) { ?>
<!-- Desktop sidebar -->
<aside class="ar-sidebar" id="ar-sidebar-desktop">
	<div class="ar-sb-title"><?php echo Text::_('VRCFILTERS'); ?></div>
	<?php echo arSrBuildFilterHTML($all_characteristics, $characteristics_map, $sidebarCats, $catCounts); ?>
</aside>
<?php } ?>

<!-- Cars area -->
<div class="ar-cars-area">
<div class="ar-grid" id="ar-grid">

<?php
foreach ($res as $k => $r) {
	$d             = $carDataMap[$k];
	$getcar        = $d['car'];
	$car_params    = $d['car_params'];
	$has_promotion = $d['has_promotion'];
	$car_cost      = $d['car_cost'];
	$imgpath       = $d['imgpath'];

	$carats     = vikrentcar::getCarCaratOriz($getcar['idcarat'], $characteristics_map);
	$vcategory  = vikrentcar::sayCategory($getcar['idcat'], $vrc_tn);
	$detailUrl  = JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$k.(!empty($pitemid) ? '&Itemid='.$pitemid : ''));
	$caratIds   = rtrim(isset($getcar['idcarat']) ? $getcar['idcarat'] : '', ';');
	$catIds     = rtrim(isset($getcar['idcat'])   ? $getcar['idcat']   : '', ';');
	$costperday = ($days > 1) ? ($car_cost / $days) : 0;
	$showDaily  = (!empty($car_params['sdailycost']) && $car_params['sdailycost'] == 1 && $days > 1);
?>
<div class="ar-card<?php echo $has_promotion ? ' vrc-promotion-price' : ''; ?>"
     data-name="<?php echo htmlspecialchars(mb_strtolower($getcar['name'])); ?>"
     data-price="<?php echo (int)$car_cost; ?>"
     data-caratids="<?php echo htmlspecialchars($caratIds); ?>"
     data-catids="<?php echo htmlspecialchars($catIds); ?>">

	<div class="ar-card-img">
		<a href="<?php echo $detailUrl; ?>">
			<?php if (!empty($getcar['img'])) { ?>
			<img src="<?php echo JURI::root().$imgpath; ?>" alt="<?php echo htmlspecialchars($getcar['name']); ?>" loading="lazy"/>
			<?php } else { ?>
			<div class="ar-img-ph"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9l3-3 3 3 3-3 3 3 3-3"/></svg></div>
			<?php } ?>
		</a>
		<?php if ($has_promotion) { ?>
		<span class="ar-promo-badge"><?php echo Text::_('VRCPROMOPIN'); ?></span>
		<?php } ?>
		<div class="ar-badge">
			<span class="ar-badge-from"><?php echo Text::_('VRSTARTFROM'); ?></span>
			<span class="ar-badge-val"><?php echo $currencysymb . vikrentcar::numberFormat($car_cost); ?></span>
			<span class="ar-badge-per">/ <?php echo (int)$days; ?> <?php echo ((int)$days === 1 ? Text::_('VRCSEARCHDAY') : Text::_('VRCSEARCHDAYS')); ?></span>
		</div>
	</div>

	<div class="ar-card-body">
		<a href="<?php echo $detailUrl; ?>" class="ar-card-name"><?php echo htmlspecialchars($getcar['name']); ?></a>
		<?php if (!empty($vcategory)) { ?>
		<div class="ar-card-category"><?php echo htmlspecialchars($vcategory); ?></div>
		<?php } ?>

		<?php if (!empty($carats)) { ?>
		<div class="ar-specs"><?php echo $carats; ?></div>
		<?php } ?>

		<?php if ($has_promotion && !empty($r[0]['promotion']['promotxt'])) { ?>
		<div class="ar-promo-block"><?php echo $r[0]['promotion']['promotxt']; ?></div>
		<?php } ?>

		<div class="ar-card-footer">
			<div class="ar-price-row">
				<span class="ar-price-label"><?php echo Text::_('VRSTARTFROM'); ?></span>
				<span class="ar-price-total"><?php echo $currencysymb . vikrentcar::numberFormat($car_cost); ?></span>
				<span class="ar-price-cur">/ <?php echo (int)$days; ?> <?php echo ((int)$days === 1 ? Text::_('VRCSEARCHDAY') : Text::_('VRCSEARCHDAYS')); ?></span>
			</div>
			<?php if ($showDaily) { ?>
			<div>
				<span class="ar-price-daily">
					<?php echo $currencysymb . vikrentcar::numberFormat($costperday); ?> / <?php echo Text::_('VRCPERDAYCOST'); ?>
				</span>
			</div>
			<?php } ?>

			<div class="ar-btns">
				<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'); ?>" method="get" style="flex:1;display:flex;">
					<input type="hidden" name="option" value="com_vikrentcar"/>
					<input type="hidden" name="caropt" value="<?php echo $k; ?>"/>
					<input type="hidden" name="days" value="<?php echo $days; ?>"/>
					<input type="hidden" name="pickup" value="<?php echo $pickup; ?>"/>
					<input type="hidden" name="release" value="<?php echo $release; ?>"/>
					<input type="hidden" name="place" value="<?php echo $place; ?>"/>
					<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>"/>
					<input type="hidden" name="task" value="showprc"/>
					<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
					<?php if ($ptmpl === 'component') { ?>
					<input type="hidden" name="tmpl" value="component"/>
					<?php } ?>
					<button type="submit" class="ar-btn-p" style="width:100%;"><?php echo Text::_('VRPROSEGUI'); ?></button>
				</form>
				<a href="<?php echo $detailUrl; ?>" class="ar-btn-o"><?php echo Text::_('VRCDETAILSBTN'); ?></a>
			</div>
		</div>
	</div>
</div>
<?php } ?>

</div><!-- /#ar-grid -->

<div class="ar-empty" id="ar-empty">
	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
	<h3><?php echo Text::_('VRCNOCARS'); ?></h3>
	<p><?php echo Text::_('VRCNOCARSSUBT'); ?></p>
</div>

<div class="ar-goback">
	<a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=vikrentcar&pickup='.$pickup.'&return='.$release); ?>">
		<i class="fa fa-calendar"></i> <?php echo Text::_('VRCHANGEDATES'); ?>
	</a>
</div>

</div><!-- /.ar-cars-area -->
</div><!-- /.ar-main -->

<?php if (!empty($navig)) { ?>
<div class="vrc-pagination"><?php echo $navig; ?></div>
<?php } ?>

<script>
jQuery(document).ready(function ($) {
	var gridEl = document.getElementById('ar-grid');

	/* View toggle */
	$('.ar-vbtn').on('click', function () {
		if ($(this).hasClass('active')) return false;
		$('.ar-vbtn').toggleClass('active');
		gridEl.className = $('#ar-list-btn').hasClass('active') ? 'ar-list' : 'ar-grid';
		if ($.cookie) $.cookie('gridlist', gridEl.className === 'ar-list' ? 'list' : 'grid', {path:'/'});
		return false;
	});
	if ($.cookie && $.cookie('gridlist') === 'list') {
		$('#ar-list-btn').addClass('active');
		$('#ar-grid-btn').removeClass('active');
		gridEl.className = 'ar-list';
	}

	/* Live search */
	$('#ar-q').on('input', function() { arSrFilter(null); });

	/* Close sort on outside click */
	$(document).on('click', function (e) {
		if (!$(e.target).closest('.ar-sort').length)
			document.getElementById('ar-sort-dd').classList.remove('open');
	});

	/* Populate drawer from sidebar (deep-clone, sync both caratid + catid) */
	var sidebar = document.getElementById('ar-sidebar-desktop');
	var drawerBody = document.getElementById('ar-drawer-body');
	if (sidebar && drawerBody) {
		sidebar.querySelectorAll('.ar-fsec').forEach(function (sec) {
			var clone = sec.cloneNode(true);
			clone.querySelectorAll('.ar-cb').forEach(function (cb) {
				cb.addEventListener('change', function () {
					var caratid = this.getAttribute('data-caratid');
					var catid   = this.getAttribute('data-catid');
					var desktopCb = caratid
						? sidebar.querySelector('.ar-cb[data-caratid="'+caratid+'"]')
						: sidebar.querySelector('.ar-cb[data-catid="'+catid+'"]');
					if (desktopCb) desktopCb.checked = this.checked;
					arSrFilter(null);
				});
			});
			drawerBody.appendChild(clone);
		});
	}

	setTimeout(function(){ arSrFilter(null); }, 100);
});

var arSrCurrentSort = 'price-asc';

function arSrToggleSort() {
	document.getElementById('ar-sort-dd').classList.toggle('open');
}
function arSrSort(key, btn) {
	arSrCurrentSort = key;
	document.getElementById('ar-sort-lbl').textContent = btn.textContent.trim();
	document.querySelectorAll('.ar-sort-opt').forEach(function(b){ b.classList.remove('sel'); });
	btn.classList.add('sel');
	document.getElementById('ar-sort-dd').classList.remove('open');
	arSrFilter(null);
}
function arSrToggleSection(titleEl) {
	var opts  = titleEl.nextElementSibling;
	var arrow = titleEl.querySelector('.ar-ftitle-arrow');
	if (!opts) return;
	var hidden = (opts.style.display === 'none');
	opts.style.display = hidden ? '' : 'none';
	if (arrow) arrow.classList.toggle('collapsed', !hidden);
}
function arSrOpenDrawer() {
	document.getElementById('ar-drawer').classList.add('open');
	document.getElementById('ar-overlay').classList.add('open');
	document.body.style.overflow = 'hidden';
}
function arSrCloseDrawer() {
	document.getElementById('ar-drawer').classList.remove('open');
	document.getElementById('ar-overlay').classList.remove('open');
	document.body.style.overflow = '';
}

function arSrFilter(clickedEl) {
	var qEl = document.getElementById('ar-q');
	var q = (qEl ? qEl.value : '').toLowerCase().trim();

	/* Transmission mutual exclusion */
	if (clickedEl && clickedEl.checked) {
		var grp = clickedEl.getAttribute('data-group');
		if (grp === 'transmission') {
			document.querySelectorAll('.ar-cb[data-group="transmission"]').forEach(function(cb) {
				if (cb.getAttribute('data-caratid') !== clickedEl.getAttribute('data-caratid')) {
					cb.checked = false;
				}
			});
		}
	}

	/* Build groups map: { groupKey: { itemKey: {type, id} } }
	   OR within group, AND between groups */
	var groups = {};
	var sidebar = document.getElementById('ar-sidebar-desktop');
	if (sidebar) {
		sidebar.querySelectorAll('.ar-cb:checked').forEach(function(cb) {
			var grp    = cb.getAttribute('data-group');
			var caratid = cb.getAttribute('data-caratid');
			var catid   = cb.getAttribute('data-catid');
			var key     = caratid ? 'c' + caratid : 'k' + catid;
			if (!groups[grp]) groups[grp] = {};
			groups[grp][key] = { type: caratid ? 'carat' : 'cat', id: caratid || catid };
		});
	}

	var groupKeys = Object.keys(groups);
	var cards = Array.from(document.querySelectorAll('.ar-card'));
	var grid  = document.getElementById('ar-grid');
	var visible = [];

	/* For recounting available filter options */
	var visibleCaratIds = {};
	var visibleCatIds   = {};

	cards.forEach(function(card) {
		var name     = card.getAttribute('data-name') || '';
		var caratIds = (card.getAttribute('data-caratids') || '').replace(/;+$/, '').split(';').filter(Boolean);
		var catIds   = (card.getAttribute('data-catids')   || '').replace(/;+$/, '').split(';').filter(Boolean);

		if (q && name.indexOf(q) === -1) { card.style.display = 'none'; return; }

		/* AND between groups, OR within group */
		var passAll = groupKeys.every(function(grp) {
			return Object.values(groups[grp]).some(function(item) {
				if (item.type === 'carat') return caratIds.indexOf(item.id) !== -1;
				if (item.type === 'cat')   return catIds.indexOf(item.id)   !== -1;
				return false;
			});
		});

		if (passAll) {
			card.style.display = '';
			visible.push(card);
			caratIds.forEach(function(id) { visibleCaratIds[id] = (visibleCaratIds[id] || 0) + 1; });
			catIds.forEach(function(id)   { visibleCatIds[id]   = (visibleCatIds[id]   || 0) + 1; });
		} else {
			card.style.display = 'none';
		}
	});

	/* Sort */
	visible.sort(function(a, b) {
		var pa = parseInt(a.getAttribute('data-price') || 0);
		var pb = parseInt(b.getAttribute('data-price') || 0);
		return arSrCurrentSort === 'price-desc' ? pb - pa : pa - pb;
	});
	visible.forEach(function(c){ grid.appendChild(c); });

	/* Recount filter option labels & dim unavailable */
	document.querySelectorAll('.ar-fopt').forEach(function(opt) {
		var cb = opt.querySelector('.ar-cb');
		if (!cb) return;
		var isCarat = cb.hasAttribute('data-caratid');
		var id      = isCarat ? cb.getAttribute('data-caratid') : cb.getAttribute('data-catid');
		var count   = isCarat ? (visibleCaratIds[id] || 0) : (visibleCatIds[id] || 0);
		var cntSpan = opt.querySelector('.ar-fcnt');
		if (cntSpan) cntSpan.textContent = '(' + count + ')';
		if (count === 0 && !cb.checked) {
			opt.style.opacity = '0.4';
			cb.disabled = true;
		} else {
			opt.style.opacity = '1';
			cb.disabled = false;
		}
	});

	var cEl = document.getElementById('ar-count');
	if (cEl) cEl.textContent = visible.length + ' <?php echo addslashes(Text::_('VRCAUTOMOBILEFOUND')); ?>';
	var emEl = document.getElementById('ar-empty');
	if (emEl) emEl.style.display = visible.length === 0 ? 'block' : 'none';

	arSrUpdateChips();
}

function arSrUpdateChips() {
	var chips = document.getElementById('ar-chips');
	if (!chips) return;
	chips.innerHTML = '';
	var sidebar = document.getElementById('ar-sidebar-desktop');
	if (!sidebar) return;
	sidebar.querySelectorAll('.ar-cb:checked').forEach(function(cb) {
		var label   = cb.getAttribute('data-label') || '';
		var caratid = cb.getAttribute('data-caratid');
		var catid   = cb.getAttribute('data-catid');
		var chip    = document.createElement('span');
		chip.className = 'ar-chip';
		chip.innerHTML = label + '<span class="ar-chip-x">&times;</span>';
		chip.onclick = function() {
			if (caratid) {
				document.querySelectorAll('.ar-cb[data-caratid="'+caratid+'"]')
					.forEach(function(c){ c.checked = false; });
			} else if (catid) {
				document.querySelectorAll('.ar-cb[data-catid="'+catid+'"]')
					.forEach(function(c){ c.checked = false; });
			}
			arSrFilter(null);
		};
		chips.appendChild(chip);
	});
}
</script>
