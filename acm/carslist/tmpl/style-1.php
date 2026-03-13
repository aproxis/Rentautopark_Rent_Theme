<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Cars List Style 1: Featured Cars Grid
 * ------------------------------------------------------------------------
 * ACM layout: Displays up to N cars from VikRentCar in a responsive 3-column
 * grid matching the AutoRent design system, with a "View All" CTA button.
 *
 * Fields:
 *   section-title    (heading above the grid)
 *   section-subtitle (sub-heading)
 *   limit            (number of cars to show, default 9)
 *   view-all-url     (URL for the "View All" button, empty = hide)
 *   view-all-label   (button label)
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$uid = 'ar-carslist-' . $module->id;

/* ── ACM fields ─────────────────────────────────────────────────────── */
$sectionTitle    = $helper->get('section-title');
$sectionSubtitle = $helper->get('section-subtitle');
$limit           = (int)$helper->get('limit');
if ($limit <= 0) $limit = 9;
$viewAllUrl      = $helper->get('view-all-url');
$viewAllLabel    = $helper->get('view-all-label');
if (empty($viewAllLabel)) $viewAllLabel = 'Vezi toate mașinile';

/* ── Bootstrap VikRentCar library ───────────────────────────────────── */
$vrcLibPath = JPATH_SITE . '/components/com_vikrentcar/helpers/lib.vikrentcar.php';
if (!class_exists('VikRentCar') && file_exists($vrcLibPath)) {
    // Load defines first
    $definesPath = JPATH_SITE . '/components/com_vikrentcar/helpers/adapter/defines.php';
    if (file_exists($definesPath)) {
        include_once $definesPath;
    }
    require_once $vrcLibPath;
}

/* ── Fetch cars ─────────────────────────────────────────────────────── */
$cars = array();
if (class_exists('VikRentCar')) {
    $dbo    = JFactory::getDbo();
    $vrc_tn = VikRentCar::getTranslator();

    $q = "SELECT `id`,`name`,`img`,`idcat`,`idcarat`,`startfrom` FROM `#__vikrentcar_cars` WHERE `avail`='1' ORDER BY `ordering` ASC, `id` ASC LIMIT " . $limit . ";";
    $dbo->setQuery($q);
    $dbo->execute();
    if ($dbo->getNumRows() > 0) {
        $cars = $dbo->loadAssocList();
        $vrc_tn->translateContents($cars, '#__vikrentcar_cars');

        // Resolve per-day price for each car
        foreach ($cars as $k => $c) {
            if (!empty($c['startfrom']) && floatval($c['startfrom']) > 0) {
                $cars[$k]['cost'] = floatval($c['startfrom']);
                continue;
            }
            $dbo->setQuery("SELECT `cost` FROM `#__vikrentcar_dispcost` WHERE `idcar`=" . (int)$c['id'] . " AND `days`='1' ORDER BY `cost` ASC LIMIT 1;");
            $dbo->execute();
            if ($dbo->getNumRows() > 0) {
                $row = $dbo->loadAssoc();
                $cars[$k]['cost'] = floatval($row['cost']);
            } else {
                $dbo->setQuery("SELECT `cost`,`days` FROM `#__vikrentcar_dispcost` WHERE `idcar`=" . (int)$c['id'] . " ORDER BY `cost` ASC LIMIT 1;");
                $dbo->execute();
                if ($dbo->getNumRows() > 0) {
                    $row = $dbo->loadAssoc();
                    $cars[$k]['cost'] = $row['days'] > 0 ? floatval($row['cost']) / floatval($row['days']) : 0;
                } else {
                    $cars[$k]['cost'] = 0;
                }
            }
        }
    }

    $currencysymb = VikRentCar::getCurrencySymb();
} else {
    $currencysymb = '';
    $vrc_tn       = null;
}
?>

<style>
/* ═══ AutoRent Cars List ACM (carslist/style-1) ═════════════════════════ */
#<?php echo $uid; ?> {
	padding: 48px 20px;
	background: #f9fafb;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> { padding: 64px 30px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> { padding: 80px 40px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> { padding: 80px 50px; } }

.<?php echo $uid; ?>-inner {
	max-width: 1440px;
	margin: 0 auto;
}

/* Section header */
.<?php echo $uid; ?>-header {
	text-align: center;
	margin-bottom: 48px;
}
.<?php echo $uid; ?>-title {
	font-size: 1.75rem;
	font-weight: 800;
	color: #0a0a0a;
	margin: 0 0 12px;
	line-height: 1.2;
}
@media (min-width: 640px)  { .<?php echo $uid; ?>-title { font-size: 2rem; } }
@media (min-width: 768px)  { .<?php echo $uid; ?>-title { font-size: 2.25rem; } }
@media (min-width: 1024px) { .<?php echo $uid; ?>-title { font-size: 2.5rem; } }
.<?php echo $uid; ?>-subtitle {
	font-size: 1rem;
	color: #6b7280;
	margin: 0;
}

/* Grid */
.<?php echo $uid; ?>-grid {
	display: grid;
	grid-template-columns: 1fr;
	gap: 18px;
}
@media (min-width: 640px) {
	.<?php echo $uid; ?>-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
	.<?php echo $uid; ?>-grid { grid-template-columns: repeat(3, 1fr); }
}

/* Card */
.<?php echo $uid; ?>-card {
	background: #fff;
	border-radius: 16px;
	overflow: hidden;
	box-shadow: 0 2px 12px rgba(0,0,0,.06);
	border: 1.5px solid #f3f4f6;
	display: flex;
	flex-direction: column;
	transition: transform .25s, box-shadow .25s, border-color .25s;
}
.<?php echo $uid; ?>-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 12px 40px rgba(0,0,0,.12);
	border-color: #FE5001;
}

/* Image */
.<?php echo $uid; ?>-img {
	position: relative;
	aspect-ratio: 4/3;
	overflow: hidden;
	background: #f3f4f6;
}
.<?php echo $uid; ?>-img a { display: block; width: 100%; height: 100%; }
.<?php echo $uid; ?>-img img {
	width: 100%; height: 100%;
	object-fit: cover; display: block;
	transition: transform .4s;
}
.<?php echo $uid; ?>-card:hover .<?php echo $uid; ?>-img img { transform: scale(1.05); }
.<?php echo $uid; ?>-img-ph {
	width: 100%; height: 100%;
	display: flex; align-items: center; justify-content: center;
	color: #d1d5db;
}
.<?php echo $uid; ?>-img-ph svg { width: 52px; height: 52px; }

/* Price badge */
.<?php echo $uid; ?>-badge {
	position: absolute; bottom: 10px; left: 10px;
	background: rgba(10,10,10,.72);
	backdrop-filter: blur(4px);
	border-radius: 8px; padding: 5px 10px; line-height: 1.2;
	pointer-events: none;
}
.<?php echo $uid; ?>-badge-from { display: block; font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: .04em; }
.<?php echo $uid; ?>-badge-val  { font-size: 1.05rem; font-weight: 800; color: #fff; }
.<?php echo $uid; ?>-badge-per  { font-size: 9px; color: #9ca3af; }

/* Body */
.<?php echo $uid; ?>-body {
	padding: 16px;
	display: flex; flex-direction: column; gap: 10px;
	flex: 1;
}
.<?php echo $uid; ?>-name {
	font-size: 1.1rem; font-weight: 700; color: #0a0a0a;
	text-decoration: none; display: block;
	transition: color .15s;
}
.<?php echo $uid; ?>-name:hover { color: #FE5001; }

/* Specs */
.<?php echo $uid; ?>-specs {
	display: grid !important;
	grid-template-columns: repeat(3, 1fr) !important;
	gap: 4px !important;
	padding-bottom: 12px;
	border-bottom: 1px solid #f3f4f6;
}
.<?php echo $uid; ?>-specs > span,
.<?php echo $uid; ?>-specs .vrc-car-carat-item,
.<?php echo $uid; ?>-specs > div {
	display: flex !important; flex-direction: column !important;
	align-items: center !important; justify-content: flex-start !important;
	text-align: center !important; gap: 3px !important;
	padding: 6px 4px !important; font-size: 11px !important;
	color: #6b7280 !important;
}
.<?php echo $uid; ?>-specs > span i,
.<?php echo $uid; ?>-specs .vrc-car-carat-item i,
.<?php echo $uid; ?>-specs > div i {
	font-size: 18px !important; color: #9ca3af !important;
	display: block !important; margin-bottom: 2px !important;
}
.<?php echo $uid; ?>-specs > span img,
.<?php echo $uid; ?>-specs .vrc-car-carat-item img,
.<?php echo $uid; ?>-specs > div img {
	width: 22px !important; height: 22px !important;
	object-fit: contain !important; margin-bottom: 2px !important;
}
.<?php echo $uid; ?>-specs br { display: none !important; }

/* Footer */
.<?php echo $uid; ?>-footer { display: flex; flex-direction: column; gap: 10px; }
.<?php echo $uid; ?>-price-row { display: flex; align-items: baseline; gap: 4px; }
.<?php echo $uid; ?>-price-from { font-size: 12px; color: #9ca3af; }
.<?php echo $uid; ?>-price-val  { font-size: 1.5rem; font-weight: 800; color: #0a0a0a; }
.<?php echo $uid; ?>-price-per  { font-size: 12px; color: #9ca3af; }

.<?php echo $uid; ?>-btns { display: flex; gap: 8px; }
.<?php echo $uid; ?>-btn-p {
	flex: 1; display: flex; align-items: center; justify-content: center;
	padding: 10px 12px; background: #FE5001; color: #fff;
	border-radius: 8px; font-size: 13px; font-weight: 700;
	text-decoration: none; transition: background .2s; text-align: center;
}
.<?php echo $uid; ?>-btn-p:hover { background: #E54801; color: #fff; }
.<?php echo $uid; ?>-btn-o {
	flex: 1; display: flex; align-items: center; justify-content: center;
	padding: 10px 12px; background: #fff; color: #374151;
	border: 1.5px solid #e5e7eb; border-radius: 8px;
	font-size: 13px; font-weight: 600;
	text-decoration: none; transition: border-color .2s, color .2s; text-align: center;
}
.<?php echo $uid; ?>-btn-o:hover { border-color: #FE5001; color: #FE5001; }

/* View All CTA */
.<?php echo $uid; ?>-cta {
	text-align: center;
	margin-top: 48px;
}
.<?php echo $uid; ?>-cta-btn {
	display: inline-flex; align-items: center; gap: 8px;
	padding: 14px 32px;
	background: #FE5001; color: #fff;
	border-radius: 8px; font-size: 1rem; font-weight: 700;
	text-decoration: none; transition: background .2s;
}
.<?php echo $uid; ?>-cta-btn:hover { background: #E54801; color: #fff; }
.<?php echo $uid; ?>-cta-btn svg { width: 18px; height: 18px; }
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<section id="<?php echo $uid; ?>" class="acm-carslist style-1">
	<div class="<?php echo $uid; ?>-inner">

		<?php if (!empty($sectionTitle) || !empty($sectionSubtitle)): ?>
		<div class="<?php echo $uid; ?>-header">
			<?php if (!empty($sectionTitle)): ?>
			<h2 class="<?php echo $uid; ?>-title"><?php echo htmlspecialchars($sectionTitle); ?></h2>
			<?php endif; ?>
			<?php if (!empty($sectionSubtitle)): ?>
			<p class="<?php echo $uid; ?>-subtitle"><?php echo htmlspecialchars($sectionSubtitle); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if (!empty($cars)): ?>
		<div class="<?php echo $uid; ?>-grid">
			<?php foreach ($cars as $c):
				$detailUrl = JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid=' . (int)$c['id']);
				$showPrice = $c['cost'] > 0;
				$priceVal  = $showPrice ? VikRentCar::numberFormat($c['cost']) : '';
				$carats    = VikRentCar::getCarCaratOriz($c['idcarat'], array(), $vrc_tn);

				// Image src
				$imgSrc = '';
				if (!empty($c['img'])) {
					$vt = JPATH_ADMINISTRATOR . '/components/com_vikrentcar/resources/vthumb_' . $c['img'];
					$imgSrc = file_exists($vt)
						? JURI::root() . 'administrator/components/com_vikrentcar/resources/vthumb_' . $c['img']
						: JURI::root() . 'administrator/components/com_vikrentcar/resources/' . $c['img'];
				}
			?>
			<div class="<?php echo $uid; ?>-card">
				<div class="<?php echo $uid; ?>-img">
					<a href="<?php echo $detailUrl; ?>">
						<?php if (!empty($imgSrc)): ?>
						<img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($c['name']); ?>" loading="lazy">
						<?php else: ?>
						<div class="<?php echo $uid; ?>-img-ph">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9l3-3 3 3 3-3 3 3 3-3"/></svg>
						</div>
						<?php endif; ?>
					</a>
					<?php if ($showPrice): ?>
					<div class="<?php echo $uid; ?>-badge">
						<span class="<?php echo $uid; ?>-badge-from"><?php echo Text::_('VRCLISTSFROM') ?: 'DE LA'; ?></span>
						<span class="<?php echo $uid; ?>-badge-val"><?php echo $currencysymb . $priceVal; ?></span>
						<span class="<?php echo $uid; ?>-badge-per"><?php echo '/ ' . (Text::_('VRCPERDAY') ?: 'zi'); ?></span>
					</div>
					<?php endif; ?>
				</div>

				<div class="<?php echo $uid; ?>-body">
					<a href="<?php echo $detailUrl; ?>" class="<?php echo $uid; ?>-name"><?php echo htmlspecialchars($c['name']); ?></a>

					<?php if (!empty($carats)): ?>
					<div class="<?php echo $uid; ?>-specs"><?php echo $carats; ?></div>
					<?php endif; ?>

					<div class="<?php echo $uid; ?>-footer">
						<?php if ($showPrice): ?>
						<div class="<?php echo $uid; ?>-price-row">
							<span class="<?php echo $uid; ?>-price-from"><?php echo Text::_('VRCLISTSFROM') ?: 'de la'; ?></span>
							<span class="<?php echo $uid; ?>-price-val"><?php echo $currencysymb . $priceVal; ?></span>
							<span class="<?php echo $uid; ?>-price-per"><?php echo '/ ' . (Text::_('VRCPERDAY') ?: 'zi'); ?></span>
						</div>
						<?php endif; ?>
						<div class="<?php echo $uid; ?>-btns">
							<a href="<?php echo $detailUrl; ?>" class="<?php echo $uid; ?>-btn-p"><?php echo Text::_('VRCRENTBTN') ?: 'Închiriază'; ?></a>
							<a href="<?php echo $detailUrl; ?>" class="<?php echo $uid; ?>-btn-o"><?php echo Text::_('VRCDETAILSBTN') ?: 'Detalii'; ?></a>
						</div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if (!empty($viewAllUrl)): ?>
		<div class="<?php echo $uid; ?>-cta">
			<a href="<?php echo htmlspecialchars($viewAllUrl); ?>" class="<?php echo $uid; ?>-cta-btn">
				<?php echo htmlspecialchars($viewAllLabel); ?>
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
			</a>
		</div>
		<?php endif; ?>

	</div>
</section>
