<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/search/default.php
 * AutoRent Design — Search Results v1
 * Matches carslist/default.php design (ar-* tokens)
 */

defined('_JEXEC') OR die('Restricted Area');
use Joomla\CMS\Language\Text;
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root() . 'templates/rent/css/search.css');
$document->addScript(JURI::root() . 'templates/rent/js/search.js');

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

	// ── CATEGORIES FIRST ──────────────────────────────────────────────────
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

	// ── CHARACTERISTICS SECOND ────────────────────────────────────────────
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

	return $html;
}
?>
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
<div class="ar-drawer-body" id="ar-drawer-body">
		<!-- Search -->
		<div class="ar-sb-search" style="margin-top:4px;">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
			<input type="text" id="ar-q-mob" placeholder="<?php echo Text::_('VRCSEARCHCARS') ?: 'Caută mașină...'; ?>"/>
		</div>
		<!-- Sort by price -->
		<div class="ar-sb-sort">
			<div class="ar-sb-sort-label"><i class="fa fa-sort"></i> <?php echo Text::_('VRCSORTBY') ?: 'Sortare'; ?></div>
			<div class="ar-sb-sort-opts">
				<div class="ar-sb-sort-opt sel" onclick="arSrSort('price-asc',this)"><span class="ar-sb-sort-dot"></span><?php echo Text::_('VRCSORTPRICELH') ?: 'Preț: Mic > Mare'; ?></div>
				<div class="ar-sb-sort-opt" onclick="arSrSort('price-desc',this)"><span class="ar-sb-sort-dot"></span><?php echo Text::_('VRCSORTPRICEHL') ?: 'Preț: Mare > Mic'; ?></div>
			</div>
		</div>
		<!-- Filter sections filled by JS from sidebar -->
	</div>
</div>
<?php } ?>

<!-- Main layout -->
<div class="ar-main">

<?php if ($hasSidebar) { ?>
<!-- Desktop sidebar -->
<aside class="ar-sidebar" id="ar-sidebar-desktop">
	<div class="ar-sb-title"><?php echo Text::_('VRCFILTERS'); ?></div>
	<!-- Search -->
	<div class="ar-sb-search">
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
		<input type="text" id="ar-q" placeholder="<?php echo Text::_('VRCSEARCHCARS'); ?>"/>
	</div>
	<!-- Sort by price -->
	<div class="ar-sb-sort">
		<div class="ar-sb-sort-label"><i class="fa fa-sort"></i> <?php echo Text::_('VRCSORTBY') ?: 'Sortare'; ?></div>
		<div class="ar-sb-sort-opts">
			<div class="ar-sb-sort-opt sel" onclick="arSrSort('price-asc',this)"><span class="ar-sb-sort-dot"></span><?php echo Text::_('VRCSORTPRICELH'); ?></div>
			<div class="ar-sb-sort-opt" onclick="arSrSort('price-desc',this)"><span class="ar-sb-sort-dot"></span><?php echo Text::_('VRCSORTPRICEHL'); ?></div>
		</div>
	</div>
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

