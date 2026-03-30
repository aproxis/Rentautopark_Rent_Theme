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
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root() . 'templates/rent/css/carslist.css');
$document->addScript(JURI::root() . 'templates/rent/js/carslist.js');

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
<!-- Header Section with Dynamic Title -->
<section class="relative py-20 bg-gradient-to-br from-[#0a0a0a] via-[#1a1a1a] to-[#0a0a0a] text-white overflow-hidden">
	<div class="absolute inset-0 opacity-10">
		<div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0px); background-size: 40px 40px;"></div>
	</div>
	<div class="relative container mx-auto px-4">
		<div class="max-w-4xl mx-auto text-center">
			<?php
			/* Page header */
			if (is_array($category)) {
				echo '<h1 class="text-5xl md:text-6xl font-bold mb-6">' . $category['name'] . '</h1>';
				echo '<p class="text-xl text-gray-300">' . $category['descr'] . '</p>';
			} else {
				?>
			<h1 class="text-5xl md:text-6xl font-bold mb-6"><?php echo is_array($category) ? $category['name'] : (Text::_('VRCALLCARSHEADING') ?: 'Toate Automobilele'); ?></h1>
			<p class="text-xl text-gray-300"><?php echo is_array($category) ? $category['descr'] : (Text::_('VRCALLCARSDESCR') ?: 'Descoperă întreaga noastră colecție de automobile premium'); ?></p>
			<?php } ?>
		</div>
	</div>
</section>

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
		<!-- Search -->
		<div class="ar-sb-search" style="margin-top:4px;">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
			<input type="text" id="ar-q-mob" placeholder="<?php echo Text::_('VRCSEARCHCARS') ?: 'Caută mașină...'; ?>"/>
		</div>
		<!-- Sort by price -->
		<div class="ar-sb-sort">
			<div class="ar-sb-sort-label"><i class="fa fa-sort"></i> <?php echo Text::_('VRCSORTBY') ?: 'Sortare'; ?></div>
			<div class="ar-sb-sort-opts">
				<div class="ar-sb-sort-opt sel" onclick="arSort('price-asc',this)"><span class="ar-sb-sort-dot"></span><?php echo Text::_('VRCSORTPRICELH') ?: 'Preț: Mic > Mare'; ?></div>
				<div class="ar-sb-sort-opt" onclick="arSort('price-desc',this)"><span class="ar-sb-sort-dot"></span><?php echo Text::_('VRCSORTPRICEHL') ?: 'Preț: Mare > Mic'; ?></div>
			</div>
		</div>
		<!-- Filter sections filled by JS from sidebar -->
	</div>
</div>

<!-- ================================================================
     MAIN
     ================================================================ -->
<div class="ar-main">

<!-- Desktop sidebar -->
<aside class="ar-sidebar" id="ar-sidebar-desktop">
	<div class="ar-sb-title"><?php echo Text::_('VRCFILTERS') ?: 'Filtre'; ?></div>
	<!-- Search -->
	<div class="ar-sb-search">
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
		<input type="text" id="ar-q" placeholder="<?php echo Text::_('VRCSEARCHCARS') ?: 'Caută mașină...'; ?>"/>
	</div>
	<!-- Sort by price -->
	<div class="ar-sb-sort">
		<div class="ar-sb-sort-label"><i class="fa fa-sort"></i> <?php echo Text::_('VRCSORTBY') ?: 'Sortare'; ?></div>
		<div class="ar-sb-sort-opts">
			<div class="ar-sb-sort-opt sel" onclick="arSort('price-asc',this)"><span class="ar-sb-sort-dot"></span><?php echo Text::_('VRCSORTPRICELH') ?: 'Preț: Mic > Mare'; ?></div>
			<div class="ar-sb-sort-opt" onclick="arSort('price-desc',this)"><span class="ar-sb-sort-dot"></span><?php echo Text::_('VRCSORTPRICEHL') ?: 'Preț: Mare > Mic'; ?></div>
		</div>
	</div>
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
			if (!empty($caratRows)) { $vrc_tn->translateContents($caratRows, '#__vikrentcar_caratteristiche'); }
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

	// ── CATEGORIES FIRST ──────────────────────────────────────────────────
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

	// ── CHARACTERISTICS SECOND ────────────────────────────────────────────
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

	return $html;
}

?>


