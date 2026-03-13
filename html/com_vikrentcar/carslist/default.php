<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/carslist/default.php
 * AutoRent Figma — v4
 * Fixes:
 *   ✓ Mobile: "Filtre" button opens slide-in drawer
 *   ✓ Sidebar carats via VikRentCar::loadCharacteristics() (proper API)
 *   ✓ Specs on card: 3-per-row icon grid
 *   ✓ AND between groups, OR within group
 *   ✓ All strings translatable via Language Overrides
 */

defined('_JEXEC') OR die('Restricted Area');
use Joomla\CMS\Language\Text;

$cars         = $this->cars;
$category     = $this->category;
$vrc_tn       = $this->vrc_tn;
$navig        = $this->navig;
$currencysymb = VikRentCar::getCurrencySymb();
$pitemid      = VikRequest::getString('Itemid', '', 'request');
$dbo          = JFactory::getDbo();

/* ================================================================
   1. COLLECT carat IDs used by cars on this page  [id => count]
   ================================================================ */
$usedCaratCounts = array();
foreach ($cars as $c) {
	if (empty($c['idcarat'])) continue;
	foreach (array_filter(explode(';', $c['idcarat'])) as $cid) {
		$cid = trim($cid);
		if ($cid === '') continue;
		$usedCaratCounts[$cid] = isset($usedCaratCounts[$cid]) ? $usedCaratCounts[$cid] + 1 : 1;
	}
}

/* ================================================================
   2. LOAD carat definitions via VRC public API
      VikRentCar::loadCharacteristics(ids[], translator)
      Returns: [ id => ['name'=>…,'icon'=>…,'textimg'=>…, …] ]
   ================================================================ */
$caratDefs = array();
if (!empty($usedCaratCounts)) {
	try {
		$caratDefs = VikRentCar::loadCharacteristics(array_keys($usedCaratCounts), $vrc_tn);
	} catch (Exception $e) {
		// fallback: direct query
		try {
			$ids = array_map('intval', array_keys($usedCaratCounts));
			$q   = "SELECT `id`,`name`,`icon`,`textimg` FROM `#__vikrentcar_carattr`"
			     . " WHERE `id` IN (" . implode(',', $ids) . ") ORDER BY `ordering` ASC, `name` ASC;";
			$dbo->setQuery($q);
			$rows = $dbo->loadAssocList('id');
			if ($rows) {
				$vrc_tn->translateContents($rows, '#__vikrentcar_carattr');
				$caratDefs = $rows;
			}
		} catch (Exception $e2) {}
	}
}

/* ================================================================
   3. LOAD CATEGORIES
   ================================================================ */
$sidebarCats = array();
if (VikRentCar::showCategoriesFront()) {
	try {
		$dbo->setQuery("SELECT `id`,`name` FROM `#__vikrentcar_categories` ORDER BY `ordering` ASC, `name` ASC;");
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$sidebarCats = $dbo->loadAssocList();
			$vrc_tn->translateContents($sidebarCats, '#__vikrentcar_categories');
		}
	} catch (Exception $e) {}
}

// Count cars per category
$catCounts = array();
foreach ($cars as $c) {
	foreach (array_filter(explode(';', $c['idcat'])) as $cid) {
		$cid = trim($cid);
		if ($cid === '') continue;
		$catCounts[$cid] = isset($catCounts[$cid]) ? $catCounts[$cid] + 1 : 1;
	}
}
?>
<style>
/* ================================================================
   AutoRent carslist v4
   ================================================================ */

/* ── Page wrapper ─────────────────────────────────────────────── */
.ar-page-wrap {
	width: 100%;
	padding: 0 20px;
	box-sizing: border-box;
}
@media (min-width: 640px)  { .ar-page-wrap { padding: 0 30px; } }
@media (min-width: 768px)  { .ar-page-wrap { padding: 0 40px; } }
@media (min-width: 1024px) { .ar-page-wrap { padding: 0 50px; } }
.ar-page-inner {
	max-width: 1440px;
	margin: 0 auto;
}

/* Hero — always 100% viewport width */
.ar-hero {
	background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
	color: #fff;
	padding: 52px 20px 44px;
	text-align: center;
	margin-bottom: 32px;
	width: 100vw;
	position: relative;
	left: 50%;
	right: 50%;
	margin-left: -50vw;
	margin-right: -50vw;
	box-sizing: border-box;
}
.ar-hero h1 {
	font-size: clamp(2rem, 4.5vw, 3.2rem);
	font-weight: 800;
	margin: 0 0 10px;
	line-height: 1.15;
}
.ar-hero p { font-size: 1rem; color: #9ca3af; margin: 0; }

/* Toolbar */
.ar-toolbar {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 10px;
	margin-bottom: 14px;
}
.ar-search-wrap { position: relative; flex: 1; min-width: 140px; }
.ar-search-wrap svg {
	position: absolute; left: 13px; top: 50%;
	transform: translateY(-50%);
	width: 17px; height: 17px;
	color: #9ca3af; pointer-events: none;
}
.ar-search-wrap input {
	width: 100%;
	padding: 11px 14px 11px 40px;
	background: #fff;
	border: 1.5px solid #e5e7eb;
	border-radius: 10px;
	font-size: 14px; color: #0a0a0a;
	transition: border-color .2s, box-shadow .2s;
}
.ar-search-wrap input:focus {
	outline: none;
	border-color: #FE5001;
	box-shadow: 0 0 0 3px rgba(254,80,1,.12);
}

/* View toggle */
.ar-vtoggle {
	display: flex;
	background: #fff;
	border: 1.5px solid #e5e7eb;
	border-radius: 10px;
	padding: 3px; gap: 2px;
}
.ar-vbtn {
	display: flex; align-items: center; justify-content: center;
	width: 38px; height: 38px; border-radius: 7px;
	border: none; background: transparent; color: #6b7280;
	cursor: pointer; transition: background .2s, color .2s;
	text-decoration: none; line-height: 1;
}
.ar-vbtn.active, .ar-vbtn:hover { background: #FE5001 !important; color: #fff !important; }
.ar-vbtn i { font-size: 15px; }

/* Sort */
.ar-sort { position: relative; }
.ar-sort-btn {
	display: flex; align-items: center; gap: 8px;
	padding: 10px 14px;
	background: #fff; border: 1.5px solid #e5e7eb;
	border-radius: 10px; font-size: 13px; color: #374151;
	cursor: pointer; white-space: nowrap;
	transition: border-color .2s;
}
.ar-sort-btn:hover { border-color: #FE5001; }
.ar-sort-btn i { color: #9ca3af; }
.ar-sort-dd {
	display: none; position: absolute; top: calc(100% + 5px); right: 0;
	min-width: 195px; background: #fff;
	border: 1.5px solid #e5e7eb; border-radius: 12px;
	box-shadow: 0 8px 30px rgba(0,0,0,.1);
	overflow: hidden; z-index: 300;
}
.ar-sort-dd.open { display: block; }
.ar-sort-opt {
	display: block; width: 100%; padding: 10px 16px;
	text-align: left; background: none; border: none;
	font-size: 13px; color: #374151; cursor: pointer;
	transition: background .15s;
}
.ar-sort-opt:hover { background: #f5f5f5; }
.ar-sort-opt.sel { color: #FE5001; font-weight: 700; background: rgba(254,80,1,.06); }

/* Mobile filter button */
.ar-mob-filter-btn {
	display: none;
	align-items: center; justify-content: center; gap: 8px;
	padding: 10px 20px;
	background: #FE5001; color: #fff;
	border: none; border-radius: 10px;
	font-size: 14px; font-weight: 700;
	cursor: pointer; transition: background .2s;
	width: 100%;
}
.ar-mob-filter-btn:hover { background: #E54801; }
.ar-mob-filter-btn i { font-size: 14px; }
@media (max-width: 900px) {
	.ar-mob-filter-btn { display: flex; }
}

/* Active filter chips */
.ar-chips {
	display: flex; flex-wrap: wrap; gap: 6px;
	margin-bottom: 14px;
}
.ar-chip {
	display: inline-flex; align-items: center; gap: 5px;
	padding: 4px 10px; background: #FE5001; color: #fff;
	border-radius: 20px; font-size: 12px; font-weight: 600;
	cursor: pointer; transition: background .15s;
}
.ar-chip:hover { background: #E54801; }
.ar-chip-x { font-size: 14px; line-height: 1; }

/* Count */
.ar-count { font-size: 13px; color: #9ca3af; margin-bottom: 16px; }

/* ================================================================
   MOBILE DRAWER
   ================================================================ */
.ar-drawer-overlay {
	display: none;
	position: fixed; inset: 0;
	background: rgba(0,0,0,.5);
	z-index: 1000;
	opacity: 0;
	transition: opacity .3s;
}
.ar-drawer-overlay.open {
	display: block;
	opacity: 1;
}
.ar-drawer {
	position: fixed;
	top: 0; left: 0; bottom: 0;
	width: min(320px, 90vw);
	background: #fff;
	z-index: 1001;
	transform: translateX(-100%);
	transition: transform .3s cubic-bezier(.4,0,.2,1);
	overflow-y: auto;
	padding: 0;
	box-shadow: 4px 0 30px rgba(0,0,0,.15);
}
.ar-drawer.open { transform: translateX(0); }

.ar-drawer-header {
	display: flex; align-items: center; justify-content: space-between;
	padding: 18px 20px;
	border-bottom: 1px solid #f3f4f6;
	position: sticky; top: 0; background: #fff; z-index: 1;
}
.ar-drawer-header h3 { font-size: 1.1rem; font-weight: 700; color: #0a0a0a; margin: 0; }
.ar-drawer-close {
	width: 32px; height: 32px; border-radius: 8px;
	border: none; background: #f5f5f5; cursor: pointer;
	display: flex; align-items: center; justify-content: center;
	font-size: 18px; color: #374151;
	transition: background .15s;
}
.ar-drawer-close:hover { background: #e5e7eb; }
.ar-drawer-body { padding: 16px 20px 24px; }

/* ================================================================
   SIDEBAR (desktop) + DRAWER SHARED FILTER STYLES
   ================================================================ */
.ar-sidebar {
	width: 240px; flex-shrink: 0;
	background: #fff; border-radius: 16px;
	box-shadow: 0 4px 20px rgba(0,0,0,.07);
	padding: 18px;
	position: sticky; top: 100px;
}
@media (max-width: 900px) { .ar-sidebar { display: none; } }

.ar-sb-title { font-size: 1rem; font-weight: 700; color: #0a0a0a; margin: 0 0 16px; }

.ar-fsec { border-bottom: 1px solid #f3f4f6; padding-bottom: 14px; margin-bottom: 14px; }
.ar-fsec:last-child { border-bottom: none; padding-bottom: 0; margin-bottom: 0; }

.ar-ftitle {
	display: flex; align-items: center; justify-content: space-between;
	margin-bottom: 10px; cursor: pointer; user-select: none;
}
.ar-ftitle-left {
	display: flex; align-items: center; gap: 7px;
	font-size: 11px; font-weight: 700; color: #374151;
	text-transform: uppercase; letter-spacing: .05em;
}
.ar-ftitle-left i { font-size: 13px; color: #0a0a0a; }
.ar-ftitle-arrow {
	font-size: 11px; color: #9ca3af;
	transition: transform .2s;
}
.ar-ftitle-arrow.collapsed { transform: rotate(-90deg); }

.ar-fopts { display: flex; flex-direction: column; gap: 7px; }
/* 2-col layout for options */
.ar-fopts-2col {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 7px 12px;
}

.ar-fopt {
	display: flex; align-items: flex-start; gap: 8px;
	cursor: pointer; font-size: 13px; color: #374151;
	user-select: none; line-height: 1.3;
}
.ar-fopt input[type=checkbox] { display: none; }
.ar-fbox {
	width: 16px; height: 16px; flex-shrink: 0;
	margin-top: 1px;
	border: 2px solid #d1d5db; border-radius: 4px; background: #fff;
	display: flex; align-items: center; justify-content: center;
	transition: border-color .15s, background .15s;
}
.ar-fopt input:checked + .ar-fbox { border-color: #FE5001; background: #FE5001; }
.ar-fchk { display: none; }
.ar-fopt input:checked + .ar-fbox .ar-fchk { display: block; }
.ar-fcnt { color: #9ca3af; font-size: 11px; white-space: nowrap; }

/* ================================================================
   MAIN LAYOUT
   ================================================================ */
.ar-main { display: flex; gap: 24px; align-items: flex-start; }
.ar-cars-area { flex: 1; min-width: 0; }

/* Grid — explicit breakpoints, no stepped jumps */
.ar-grid {
	display: grid;
	grid-template-columns: 1fr;
	gap: 18px;
}
@media (min-width: 480px) {
	.ar-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 900px) {
	/* sidebar visible: 2 cols in cars area */
	.ar-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1100px) {
	.ar-grid { grid-template-columns: repeat(3, 1fr); }
}
/* List */
.ar-list { display: flex; flex-direction: column; gap: 14px; }
.ar-list .ar-card { flex-direction: row !important; }
.ar-list .ar-card-img {
	width: 220px !important; height: auto !important;
	min-height: 170px; flex-shrink: 0;
	aspect-ratio: unset !important;
	border-radius: 14px 0 0 14px !important;
}
.ar-list .ar-card-img img { height: 100% !important; border-radius: 14px 0 0 14px !important; }
.ar-list .ar-card-body { flex: 1; }
@media (max-width: 640px) {
	.ar-list .ar-card { flex-direction: column !important; }
	.ar-list .ar-card-img {
		width: 100% !important; height: 200px !important;
		border-radius: 14px 14px 0 0 !important;
	}
	.ar-list .ar-card-img img { border-radius: 14px 14px 0 0 !important; }
}

/* ================================================================
   CAR CARD
   ================================================================ */
.ar-card {
	background: #fff; border-radius: 16px; overflow: hidden;
	box-shadow: 0 2px 12px rgba(0,0,0,.06);
	border: 1.5px solid #f3f4f6;
	display: flex; flex-direction: column;
	transition: transform .25s, box-shadow .25s, border-color .25s;
}
.ar-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 12px 40px rgba(0,0,0,.12);
	border-color: #FE5001;
}

/* Image */
.ar-card-img {
	position: relative; aspect-ratio: 4/3;
	overflow: hidden; background: #f3f4f6;
}
.ar-card-img a { display: block; width: 100%; height: 100%; }
.ar-card-img img {
	width: 100%; height: 100%; object-fit: cover;
	display: block; transition: transform .4s;
}
.ar-card:hover .ar-card-img img { transform: scale(1.05); }
.ar-img-ph {
	width: 100%; height: 100%;
	display: flex; align-items: center; justify-content: center; color: #d1d5db;
}
.ar-img-ph svg { width: 52px; height: 52px; }

/* Price badge */
.ar-badge {
	position: absolute; bottom: 10px; left: 10px;
	background: rgba(10,10,10,.72);
	backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
	border-radius: 8px; padding: 5px 10px; line-height: 1.2;
	pointer-events: none; max-width: calc(100% - 54px);
}
.ar-badge-from { display: block; font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: .04em; }
.ar-badge-val  { font-size: 1.05rem; font-weight: 800; color: #fff; }
.ar-badge-per  { font-size: 9px; color: #9ca3af; }

/* Fav */
.ar-fav {
	position: absolute; top: 10px; right: 10px;
	width: 34px; height: 34px; border-radius: 50%;
	background: rgba(255,255,255,.9);
	display: flex; align-items: center; justify-content: center;
	border: none; cursor: pointer;
	box-shadow: 0 2px 8px rgba(0,0,0,.12);
	transition: background .2s;
}
.ar-fav:hover { background: #fff; }
.ar-fav svg { width: 16px; height: 16px; color: #6b7280; }

/* Card body */
.ar-card-body { padding: 16px; display: flex; flex-direction: column; gap: 10px; }
.ar-card-name {
	font-size: 1.1rem; font-weight: 700; color: #0a0a0a;
	text-decoration: none; display: block; transition: color .15s;
}
.ar-card-name:hover { color: #FE5001; }

/* ================================================================
   SPECS — exactly 3 per row, icon above label
   ================================================================ */
.ar-specs {
	display: grid !important;
	grid-template-columns: repeat(3, 1fr) !important;
	gap: 4px !important;
	padding-bottom: 12px;
	border-bottom: 1px solid #f3f4f6;
}
/* VRC wraps each carat in a span or .vrc-car-carat-item */
.ar-specs > span,
.ar-specs .vrc-car-carat-item,
.ar-specs > div {
	display: flex !important;
	flex-direction: column !important;
	align-items: center !important;
	justify-content: flex-start !important;
	text-align: center !important;
	gap: 3px !important;
	padding: 6px 4px !important;
	font-size: 11px !important;
	color: #6b7280 !important;
	background: none !important;
	border: none !important;
	border-radius: 0 !important;
	line-height: 1.3 !important;
}
.ar-specs > span i,
.ar-specs .vrc-car-carat-item i,
.ar-specs > div i {
	font-size: 18px !important;
	color: #9ca3af !important;
	display: block !important;
	margin-bottom: 2px !important;
}
.ar-specs > span img,
.ar-specs .vrc-car-carat-item img,
.ar-specs > div img {
	width: 22px !important; height: 22px !important;
	object-fit: contain !important;
	margin-bottom: 2px !important;
}
.ar-specs br { display: none !important; }

/* Custom SVG spec items */
.ar-spec-item {
	display: flex; flex-direction: column;
	align-items: center; justify-content: flex-start;
	text-align: center; gap: 3px;
	padding: 6px 4px; font-size: 11px;
	color: #6b7280;
}
.ar-spec-item svg {
	width: 20px; height: 20px;
	color: #9ca3af; flex-shrink: 0;
}

/* Price + buttons */
.ar-card-footer { display: flex; flex-direction: column; gap: 10px; }
.ar-price-row   { display: flex; align-items: baseline; gap: 4px; }
.ar-price-from  { font-size: 12px; color: #9ca3af; }
.ar-price-val   { font-size: 1.5rem; font-weight: 800; color: #0a0a0a; }
.ar-price-per   { font-size: 12px; color: #9ca3af; }

.ar-btns { display: flex; gap: 8px; }
.ar-btn-p {
	flex: 1; display: flex; align-items: center; justify-content: center;
	padding: 10px 12px; background: #FE5001; color: #fff;
	border-radius: 8px; font-size: 13px; font-weight: 700;
	text-decoration: none; border: none; cursor: pointer;
	transition: background .2s; text-align: center;
}
.ar-btn-p:hover { background: #E54801; color: #fff; }
.ar-btn-o {
	flex: 1; display: flex; align-items: center; justify-content: center;
	padding: 10px 12px; background: #fff; color: #374151;
	border: 1.5px solid #e5e7eb; border-radius: 8px;
	font-size: 13px; font-weight: 600;
	text-decoration: none; cursor: pointer;
	transition: border-color .2s, color .2s; text-align: center;
}
.ar-btn-o:hover { border-color: #FE5001; color: #FE5001; }

/* Empty */
.ar-empty { text-align: center; padding: 60px 20px; display: none; }
.ar-empty svg { width: 48px; height: 48px; color: #d1d5db; margin-bottom: 14px; }
.ar-empty h3 { font-size: 1.2rem; font-weight: 700; color: #0a0a0a; margin-bottom: 6px; }
.ar-empty p { color: #6b7280; }

/* Pagination */
.vrc-pagination { margin-top: 36px; text-align: center; }
.vrc-pagination .pagination {
	display: inline-flex; gap: 5px; list-style: none; padding: 0; margin: 0;
}
.vrc-pagination .pagination li > a,
.vrc-pagination .pagination li > span {
	display: flex; align-items: center; justify-content: center;
	min-width: 36px; height: 36px; padding: 0 8px;
	border-radius: 8px; border: 1.5px solid #e5e7eb;
	font-size: 13px; font-weight: 600; color: #374151;
	text-decoration: none; transition: background .15s, border-color .15s, color .15s;
}
.vrc-pagination .pagination li > a:hover { border-color: #FE5001; color: #FE5001; }
.vrc-pagination .pagination li.active > a,
.vrc-pagination .pagination li.active > span,
.vrc-pagination .pagination li > span.current {
	background: #FE5001; border-color: #FE5001; color: #fff;
}

/* Mobile responsive */
@media (max-width: 600px) {
	.ar-vtoggle { display: none; }
	.ar-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
	.ar-card-body { padding: 12px; }
	.ar-card-name { font-size: 1rem; }
	.ar-price-val { font-size: 1.3rem; }
}
@media (max-width: 400px) {
	.ar-grid { grid-template-columns: 1fr; }
}
</style>

<?php
/* Page header */
if (is_array($category)) {
	echo '<div class="ar-page-wrap"><div class="ar-page-inner">';
	echo '<h3 class="vrcclistheadt">' . $category['name'] . '</h3>';
	if (strlen($category['descr']) > 0) echo '<div class="vrccatdescr">' . $category['descr'] . '</div>';
	echo '</div></div>';
} else {
?>
<div class="ar-hero">
	<h1><?php echo Text::_('VRCALLCARSHEADING') ?: 'Toate Automobilele'; ?></h1>
	<p><?php echo Text::_('VRCALLCARSDESCR') ?: 'Descoperă întreaga noastră colecție de automobile premium'; ?></p>
</div>
<?php } ?>

<div class="ar-page-wrap">
<div class="ar-page-inner">

<!-- Toolbar -->
<div class="ar-toolbar">
	<div class="ar-search-wrap">
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
		<input type="text" id="ar-q" placeholder="<?php echo Text::_('VRCSEARCHCARS') ?: 'Caută mașină...'; ?>"/>
	</div>
	<div class="ar-vtoggle">
		<a href="javascript:void(0)" id="ar-grid-btn" class="ar-vbtn active" title="<?php echo Text::_('VRCVIEWGRID') ?: 'Grilă'; ?>"><i class="fa fa-th"></i></a>
		<a href="javascript:void(0)" id="ar-list-btn" class="ar-vbtn" title="<?php echo Text::_('VRCVIEWLIST') ?: 'Listă'; ?>"><i class="fa fa-th-list"></i></a>
	</div>
	<div class="ar-sort">
		<button class="ar-sort-btn" onclick="arToggleSort()" type="button">
			<i class="fa fa-sort"></i> <span id="ar-sort-lbl"><?php echo Text::_('VRCSORTPRICELH') ?: 'Preț: Mic > Mare'; ?></span>
		</button>
		<div class="ar-sort-dd" id="ar-sort-dd">
			<button class="ar-sort-opt sel" onclick="arSort('price-asc',this)" type="button"><?php echo Text::_('VRCSORTPRICELH') ?: 'Preț: Mic > Mare'; ?></button>
			<button class="ar-sort-opt" onclick="arSort('price-desc',this)" type="button"><?php echo Text::_('VRCSORTPRICEHL') ?: 'Preț: Mare > Mic'; ?></button>
		</div>
	</div>

</div>

<!-- Mobile filter button -->
<button class="ar-mob-filter-btn" onclick="arOpenDrawer()" type="button">
	<i class="fa fa-sliders"></i>
	<?php echo Text::_('VRCFILTERS') ?: 'Filtre'; ?>
</button>

<!-- Active chips -->
<div class="ar-chips" id="ar-chips"></div>
<div class="ar-count" id="ar-count"><?php echo count($cars); ?> <?php echo Text::_('VRCAUTOMOBILEFOUND') ?: 'automobile găsite'; ?></div>

<!-- ================================================================
     MOBILE DRAWER
     ================================================================ -->
<div class="ar-drawer-overlay" id="ar-overlay" onclick="arCloseDrawer()"></div>
<div class="ar-drawer" id="ar-drawer">
	<div class="ar-drawer-header">
		<h3><?php echo Text::_('VRCFILTERS') ?: 'Filtre'; ?></h3>
		<button class="ar-drawer-close" onclick="arCloseDrawer()" type="button">×</button>
	</div>
	<div class="ar-drawer-body" id="ar-drawer-body">
		<!-- filled by JS from sidebar content -->
	</div>
</div>

<!-- ================================================================
     MAIN
     ================================================================ -->
<div class="ar-main">

<!-- Desktop sidebar -->
<aside class="ar-sidebar" id="ar-sidebar-desktop">
	<div class="ar-sb-title"><?php echo Text::_('VRCFILTERS') ?: 'Filtre'; ?></div>
	<?php echo arBuildFilterHTML($caratDefs, $usedCaratCounts, $sidebarCats, $catCounts, $vrc_tn); ?>
</aside>

<!-- Cars area -->
<div class="ar-cars-area">
<div class="ar-grid" id="ar-grid">

<?php
/* ── SVG icon map keyed by carat name keywords ───────────────────────── */
$svgIcons = array(
	'automat' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path></svg>',
	'manual'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"></path><path d="M14 17H5"></path><circle cx="17" cy="17" r="3"></circle><circle cx="7" cy="7" r="3"></circle></svg>',
	'diesel'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>',
	'benzin'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="15" y1="22" y2="22"></line><line x1="4" x2="14" y1="9" y2="9"></line><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"></path><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"></path></svg>',
	'petrol'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="15" y1="22" y2="22"></line><line x1="4" x2="14" y1="9" y2="9"></line><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"></path><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"></path></svg>',
	'loc'     => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
	'seat'    => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
);
$svgDefault = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>';

foreach ($cars as $c):
	$detailUrl = JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid=' . $c['id'] . (!empty($pitemid) ? '&Itemid=' . $pitemid : ''));
	$showPrice = $c['cost'] > 0;
	$priceVal  = strlen($c['startfrom']) > 0 ? VikRentCar::numberFormat($c['startfrom']) : VikRentCar::numberFormat($c['cost']);

	/* ── Build custom SVG specs from carat IDs ─────────────────── */
	$specItems = array();
	if (!empty($c['idcarat'])) {
		$caratIds = array_filter(array_map('intval', explode(';', trim($c['idcarat'], ';'))));
		if (!empty($caratIds)) {
			$dbo->setQuery(
				"SELECT `id`,`name`,`textimg` FROM `#__vikrentcar_caratteristiche` WHERE `id` IN (" . implode(',', $caratIds) . ")"
			);
			$dbo->execute();
			$caratRows = $dbo->getNumRows() > 0 ? $dbo->loadAssocList('id') : array();
			foreach ($caratIds as $cid) {
				if (!isset($caratRows[$cid])) continue;
				$cr    = $caratRows[$cid];
				$label = !empty($cr['textimg']) ? $cr['textimg'] : $cr['name'];
				$key   = strtolower($label);
				$svg   = $svgDefault;
				foreach ($svgIcons as $keyword => $iconSvg) {
					if (strpos($key, $keyword) !== false) { $svg = $iconSvg; break; }
				}
				$specItems[] = array('svg' => $svg, 'label' => $label);
			}
		}
	}
?>
<div class="ar-card"
     data-name="<?php echo htmlspecialchars(mb_strtolower($c['name'])); ?>"
     data-price="<?php echo (int)$c['cost']; ?>"
     data-caratids="<?php echo htmlspecialchars($c['idcarat']); ?>"
     data-catids="<?php echo htmlspecialchars($c['idcat']); ?>">

	<div class="ar-card-img">
		<a href="<?php echo $detailUrl; ?>">
		<?php if (!empty($c['img'])):
			$vt  = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_vikrentcar' . DS . 'resources' . DS . 'vthumb_' . $c['img'];
			$src = file_exists($vt)
				? JURI::root() . 'administrator/components/com_vikrentcar/resources/vthumb_' . $c['img']
				: JURI::root() . 'administrator/components/com_vikrentcar/resources/' . $c['img'];
		?><img src="<?php echo $src; ?>" alt="<?php echo htmlspecialchars($c['name']); ?>" loading="lazy"/>
		<?php else: ?>
			<div class="ar-img-ph"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9l3-3 3 3 3-3 3 3 3-3"/></svg></div>
		<?php endif; ?>
		</a>
		<button class="ar-fav" type="button">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
		</button>
		<?php if ($showPrice): ?>
		<div class="ar-badge">
			<span class="ar-badge-from"><?php echo Text::_('VRCLISTSFROM') ?: 'DE LA'; ?></span>
			<span class="ar-badge-val"><?php echo $currencysymb . $priceVal; ?></span>
			<span class="ar-badge-per"><?php echo '/ ' . (Text::_('VRCPERDAY') ?: 'zi'); ?></span>
		</div>
		<?php endif; ?>
	</div>

	<div class="ar-card-body">
		<a href="<?php echo $detailUrl; ?>" class="ar-card-name"><?php echo $c['name']; ?></a>
		<?php if (!empty($specItems)): ?>
		<div class="ar-specs">
			<?php foreach ($specItems as $spec): ?>
			<div class="ar-spec-item">
				<?php echo $spec['svg']; ?>
				<span><?php echo htmlspecialchars($spec['label']); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<div class="ar-card-footer">
			<?php if ($showPrice): ?>
			<div class="ar-price-row">
				<span class="ar-price-from"><?php echo Text::_('VRCLISTSFROM') ?: 'de la'; ?></span>
				<span class="ar-price-val"><?php echo $currencysymb . $priceVal; ?></span>
				<span class="ar-price-per"><?php echo '/ ' . (Text::_('VRCPERDAY') ?: 'zi'); ?></span>
			</div>
			<?php endif; ?>
			<div class="ar-btns">
				<a href="<?php echo $detailUrl; ?>" class="ar-btn-p"><?php echo Text::_('VRCRENTBTN') ?: 'Închiriază'; ?></a>
				<a href="<?php echo $detailUrl; ?>" class="ar-btn-o"><?php echo Text::_('VRCDETAILSBTN') ?: 'Vezi detalii'; ?></a>
			</div>
		</div>
	</div>
</div>
<?php endforeach; ?>

</div><!-- /#ar-grid -->

<div class="ar-empty" id="ar-empty">
	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
	<h3><?php echo Text::_('VRCNOCARS') ?: 'Nu am găsit mașini'; ?></h3>
	<p><?php echo Text::_('VRCNOCARSSUBT') ?: 'Modifică filtrele sau căutarea'; ?></p>
</div>
</div><!-- /.ar-cars-area -->
</div><!-- /.ar-main -->

<?php if (!empty($navig)): ?>
<div class="vrc-pagination"><?php echo $navig; ?></div>
<?php endif; ?>

</div><!-- /.ar-page-inner -->
</div><!-- /.ar-page-wrap -->

<?php
/**
 * Helper: build the filter HTML (used for both desktop sidebar and mobile drawer)
 */
function arBuildFilterHTML($caratDefs, $usedCaratCounts, $sidebarCats, $catCounts, $vrc_tn) {
	$html = '';

	if (!empty($caratDefs)) {
		$html .= '<div class="ar-fsec">';
		$html .= '<div class="ar-ftitle" onclick="arToggleSection(this)">';
		$html .= '<span class="ar-ftitle-left"><i class="fa fa-sliders"></i> ' . Text::_('VRCCHARACTERISTICS') . '</span>';
		$html .= '<i class="fa fa-chevron-up ar-ftitle-arrow"></i>';
		$html .= '</div>';
		$html .= '<div class="ar-fopts ar-fopts-2col">';
		foreach ($caratDefs as $cid => $carat) {
			if (!isset($usedCaratCounts[$cid])) continue;
			
			// Получаем и переводим название
			$rawLabel = !empty($carat['textimg']) ? $carat['textimg'] : $carat['name'];
			$rawLabel = trim($rawLabel);
			$label = Text::_($rawLabel) ?: $rawLabel;
			$cnt = $usedCaratCounts[$cid];
			
			// Определяем логическую группу
			$ll = mb_strtolower($rawLabel);
			if ($ll === 'automat' || $ll === 'manual') {
				$group = 'transmission'; // Логика исключения (только один)
			} elseif ($ll === 'diesel' || $ll === 'benzină' || $ll === 'benzina' || $ll === 'hybrid') {
				$group = 'fuel';         // Логика ИЛИ внутри группы
			} else {
				$group = 'carat_' . (int)$cid; // Логика И со всем остальным
			}

			$html .= '<label class="ar-fopt">';
			$html .= '<input type="checkbox" class="ar-cb" data-group="' . $group . '"'
			       . ' data-caratid="' . (int)$cid . '"'
			       . ' data-label="' . htmlspecialchars($label) . '"'
			       . ' onchange="arFilter(this)"/>';
			$html .= '<span class="ar-fbox"><svg class="ar-fchk" viewBox="0 0 12 12" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>';
			$html .= '<span>' . htmlspecialchars($label) . ' <span class="ar-fcnt">(' . $cnt . ')</span></span>';
			$html .= '</label>';
		}
		$html .= '</div></div>';
	}

	if (!empty($sidebarCats)) {
		$html .= '<div class="ar-fsec">';
		$html .= '<div class="ar-ftitle" onclick="arToggleSection(this)">';
		$html .= '<span class="ar-ftitle-left"><i class="fa fa-car"></i> ' . (Text::_('VRCARCAT') ?: 'Caroserie') . '</span>';
		$html .= '<i class="fa fa-chevron-up ar-ftitle-arrow"></i>';
		$html .= '</div>';
		$html .= '<div class="ar-fopts ar-fopts-2col">';
		foreach ($sidebarCats as $cat) {
			$cnt = isset($catCounts[$cat['id']]) ? $catCounts[$cat['id']] : 0;
			if ($cnt === 0) continue;
			$catName = Text::_($cat['name']) ?: $cat['name'];
			
			$html .= '<label class="ar-fopt">';
			$html .= '<input type="checkbox" class="ar-cb" data-group="category"'
			       . ' data-catid="' . (int)$cat['id'] . '"'
			       . ' data-label="' . htmlspecialchars($catName) . '"'
			       . ' onchange="arFilter(this)"/>';
			$html .= '<span class="ar-fbox"><svg class="ar-fchk" viewBox="0 0 12 12" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="2,6 5,9 10,3"/></svg></span>';
			$html .= '<span>' . htmlspecialchars($catName) . ' <span class="ar-fcnt">(' . $cnt . ')</span></span>';
			$html .= '</label>';
		}
		$html .= '</div></div>';
	}

	return $html;
}

?>

<script>
jQuery(document).ready(function ($) {
	/* ---- View toggle + cookie ---- */
	var gridEl = document.getElementById('ar-grid');
	$('.ar-vbtn').on('click', function () {
		if ($(this).hasClass('active')) return false;
		$('.ar-vbtn').toggleClass('active');
		if ($('#ar-list-btn').hasClass('active')) {
			gridEl.className = 'ar-list';
			$.cookie && $.cookie('gridlist', 'list', { path: '/' });
		} else {
			gridEl.className = 'ar-grid';
			$.cookie && $.cookie('gridlist', 'grid', { path: '/' });
		}
		return false;
	});
	if ($.cookie && $.cookie('gridlist') === 'list') {
		$('#ar-list-btn').addClass('active');
		$('#ar-grid-btn').removeClass('active');
		gridEl.className = 'ar-list';
	}

	/* ---- Live search ---- */
	$('#ar-q').on('input', arFilter);

	/* ---- Sort dropdown: close outside ---- */
	$(document).on('click', function (e) {
		if (!$(e.target).closest('.ar-sort').length)
			document.getElementById('ar-sort-dd').classList.remove('open');
	});

	/* ---- Populate drawer from sidebar HTML ---- */
	var sidebarContent = document.getElementById('ar-sidebar-desktop');
	if (sidebarContent) {
		// Clone sidebar filter HTML into drawer
		var drawerBody = document.getElementById('ar-drawer-body');
		if (drawerBody) {
			// Deep-clone sidebar filter sections
			var sections = sidebarContent.querySelectorAll('.ar-fsec');
			sections.forEach(function (sec) {
				var clone = sec.cloneNode(true);
				// Make checkboxes in drawer trigger the same arFilter
				clone.querySelectorAll('.ar-cb').forEach(function (cb) {
					cb.addEventListener('change', function () {
						// Sync state to desktop sidebar counterpart
						var caratid = this.getAttribute('data-caratid');
						var catid   = this.getAttribute('data-catid');
						var desktopCb = caratid
							? sidebarContent.querySelector('.ar-cb[data-caratid="'+caratid+'"]')
							: sidebarContent.querySelector('.ar-cb[data-catid="'+catid+'"]');
						if (desktopCb) desktopCb.checked = this.checked;
						arFilter();
					});
				});
				drawerBody.appendChild(clone);
			});
		}
	}

	setTimeout(function() { arFilter(); }, 100);

});

/* Sort */
	var arCurrentSort = 'price-asc'; // Сортировка по цене по умолчанию

	function arToggleSort() {
		document.getElementById('ar-sort-dd').classList.toggle('open');
	}
	function arSort(key, btn) {
		arCurrentSort = key;
		document.getElementById('ar-sort-lbl').textContent = btn.textContent.trim();
		document.querySelectorAll('.ar-sort-opt').forEach(function (b) { b.classList.remove('sel'); });
		btn.classList.add('sel');
		document.getElementById('ar-sort-dd').classList.remove('open');
		arFilter();
	}

	// Collapsible sections & Drawer ... (оставьте ваши функции arToggleSection, arOpenDrawer, arCloseDrawer как были)
	function arToggleSection(titleEl) {
		var opts = titleEl.nextElementSibling;
		var arrow = titleEl.querySelector('.ar-ftitle-arrow');
		if (!opts) return;
		var hidden = (opts.style.display === 'none');
		opts.style.display = hidden ? '' : 'none';
		if (arrow) arrow.classList.toggle('collapsed', !hidden);
	}
	function arOpenDrawer() {
		document.getElementById('ar-drawer').classList.add('open');
		document.getElementById('ar-overlay').classList.add('open');
		document.body.style.overflow = 'hidden';
	}
	function arCloseDrawer() {
		document.getElementById('ar-drawer').classList.remove('open');
		document.getElementById('ar-overlay').classList.remove('open');
		document.body.style.overflow = '';
	}

	/* --- ФИЛЬТРАЦИЯ --- */
	function arFilter(clickedEl) {
		var qEl = document.getElementById('ar-q');
		var q = (qEl ? qEl.value : '').toLowerCase().trim();

		// 1. Взаимоисключение: Automat и Manual
		if (clickedEl && clickedEl.checked) {
			var grp = clickedEl.getAttribute('data-group');
			if (grp === 'transmission') {
				document.querySelectorAll('.ar-cb[data-group="transmission"]').forEach(function(cb) {
					// Снимаем галочку с другой КПП
					if (cb.getAttribute('data-caratid') !== clickedEl.getAttribute('data-caratid')) {
						cb.checked = false;
					}
				});
			}
		}

		var groups = {};
		document.querySelectorAll('.ar-cb:checked').forEach(function (cb) {
			var grp = cb.getAttribute('data-group');
			var caratid = cb.getAttribute('data-caratid');
			var catid = cb.getAttribute('data-catid');
			var key = caratid ? 'c' + caratid : 'k' + catid;

			if (!groups[grp]) groups[grp] = {};
			groups[grp][key] = { type: caratid ? 'carat' : 'cat', id: caratid || catid };
		});

		var groupKeys = Object.keys(groups);
		var cards = Array.from(document.querySelectorAll('.ar-card'));
		var grid = document.getElementById('ar-grid');
		var visible = [];
		
		// Для пересчета доступных фильтров
		var visibleCaratIds = {};
		var visibleCatIds = {};

		cards.forEach(function (card) {
			var name = card.getAttribute('data-name') || '';
			var caratIds = (card.getAttribute('data-caratids') || '').replace(/;+$/, '').split(';').filter(Boolean);
			var catIds = (card.getAttribute('data-catids') || '').replace(/;+$/, '').split(';').filter(Boolean);

			if (q && name.indexOf(q) === -1) { card.style.display = 'none'; return; }

			var passAll = groupKeys.every(function (grp) {
				return Object.values(groups[grp]).some(function (item) {
					if (item.type === 'carat') return caratIds.indexOf(item.id) !== -1;
					if (item.type === 'cat')   return catIds.indexOf(item.id) !== -1;
					return false;
				});
			});

			if (passAll) { 
				card.style.display = ''; 
				visible.push(card); 
				// Собираем id характеристик у видимых машин
				caratIds.forEach(function(id) { visibleCaratIds[id] = (visibleCaratIds[id] || 0) + 1; });
				catIds.forEach(function(id) { visibleCatIds[id] = (visibleCatIds[id] || 0) + 1; });
			}
			else { card.style.display = 'none'; }
		});

		/* Сортировка */
		visible.sort(function (a, b) {
			if (arCurrentSort === 'price-asc') return parseInt(a.getAttribute('data-price')||0) - parseInt(b.getAttribute('data-price')||0);
			if (arCurrentSort === 'price-desc') return parseInt(b.getAttribute('data-price')||0) - parseInt(a.getAttribute('data-price')||0);
			return 0;
		});
		visible.forEach(function (card) { grid.appendChild(card); });

		/* Пересчет цифр в чекбоксах и отключение недоступных */
		document.querySelectorAll('.ar-fopt').forEach(function(opt) {
			var cb = opt.querySelector('.ar-cb');
			if (!cb) return;

			var isCarat = cb.hasAttribute('data-caratid');
			var id = isCarat ? cb.getAttribute('data-caratid') : cb.getAttribute('data-catid');
			var count = isCarat ? (visibleCaratIds[id] || 0) : (visibleCatIds[id] || 0);
			
			var cntSpan = opt.querySelector('.ar-fcnt');
			if (cntSpan) cntSpan.textContent = '(' + count + ')';

			// Если машин 0 и чекбокс не нажат - делаем полупрозрачным
			if (count === 0 && !cb.checked) {
				opt.style.opacity = '0.4';
				cb.disabled = true;
			} else {
				opt.style.opacity = '1';
				cb.disabled = false;
			}
		});

		var cEl = document.getElementById('ar-count');
		if (cEl) cEl.textContent = visible.length + ' <?php echo addslashes(Text::_('VRCAUTOMOBILEFOUND') ?: 'automobile găsite'); ?>';
		document.getElementById('ar-empty').style.display = visible.length === 0 ? 'block' : 'none';

		arUpdateChips();
	}


function arUpdateChips() {
	var chips = document.getElementById('ar-chips');
	chips.innerHTML = '';
	// Use only desktop sidebar checkboxes as source of truth
	document.querySelectorAll('#ar-sidebar-desktop .ar-cb:checked').forEach(function (cb) {
		var label = cb.getAttribute('data-label') || '';
		var chip  = document.createElement('span');
		chip.className = 'ar-chip';
		chip.innerHTML = label + '<span class="ar-chip-x">×</span>';
		var caratid = cb.getAttribute('data-caratid');
		var catid   = cb.getAttribute('data-catid');
		chip.onclick = function () {
			// Uncheck both desktop + drawer
			if (caratid) {
				document.querySelectorAll('.ar-cb[data-caratid="'+caratid+'"]')
					.forEach(function(c){ c.checked = false; });
			} else if (catid) {
				document.querySelectorAll('.ar-cb[data-catid="'+catid+'"]')
					.forEach(function(c){ c.checked = false; });
			}
			arFilter();
		};
		chips.appendChild(chip);
	});
}
</script>