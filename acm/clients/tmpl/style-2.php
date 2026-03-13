<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Clients Style 2: Auto-Scroll Ticker
 * ------------------------------------------------------------------------
 * ACM layout for the "clients" module type.
 * Logos scroll infinitely from right to left, no pagination, no arrows.
 * Grayscale by default, color on hover.
 *
 * Extra Fields used:
 *   client-item.client-logo  (image URL)
 *   client-item.client-name  (alt text / title)
 *   client-item.client-link  (optional href)
 *   img-gray                 (checkbox: 1 = apply grayscale CSS)
 *   ticker-speed             (optional: seconds per full cycle, default 30)
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$count      = $helper->getRows('client-item.client-logo');
$gray       = $helper->get('img-gray');
$speed      = (int)$helper->get('ticker-speed');
if ($speed < 5) { $speed = 30; }

// Duplicate items so the ticker loop is seamless
$items = array();
for ($i = 0; $i < $count; $i++) {
	$items[] = array(
		'name' => $helper->get('client-item.client-name', $i),
		'link' => $helper->get('client-item.client-link', $i),
		'logo' => $helper->get('client-item.client-logo', $i),
	);
}
// Duplicate for seamless loop
$loopItems = array_merge($items, $items);
$uid = 'ar-ticker-' . $module->id;
?>

<style>
/* ═══ AutoRent Brand Ticker (style-2) ══════════════════════════════════ */
#<?php echo $uid; ?> {
	overflow: hidden;
	padding: 24px 0;
	position: relative;
}
#<?php echo $uid; ?>::before,
#<?php echo $uid; ?>::after {
	content: '';
	position: absolute;
	top: 0; bottom: 0;
	width: 80px;
	z-index: 2;
	pointer-events: none;
}
#<?php echo $uid; ?>::before {
	left: 0;
	background: linear-gradient(to right, var(--ar-ticker-fade, #fff) 0%, transparent 100%);
}
#<?php echo $uid; ?>::after {
	right: 0;
	background: linear-gradient(to left, var(--ar-ticker-fade, #fff) 0%, transparent 100%);
}
.<?php echo $uid; ?>-track {
	display: flex;
	align-items: center;
	width: max-content;
	animation: arTicker<?php echo $module->id; ?> <?php echo $speed; ?>s linear infinite;
	gap: 0;
}
.<?php echo $uid; ?>-track:hover {
	animation-play-state: paused;
}
@keyframes arTicker<?php echo $module->id; ?> {
	0%   { transform: translateX(0); }
	100% { transform: translateX(-50%); }
}
.<?php echo $uid; ?>-item {
	flex: 0 0 auto;
	padding: 0 40px;
	display: flex;
	align-items: center;
	justify-content: center;
}
.<?php echo $uid; ?>-item img {
	max-height: 52px;
	max-width: 130px;
	width: auto;
	object-fit: contain;
	transition: filter .3s, opacity .3s, transform .25s;
	<?php if ($gray): ?>
	filter: grayscale(100%);
	opacity: .55;
	<?php else: ?>
	filter: none;
	opacity: .8;
	<?php endif; ?>
}
.<?php echo $uid; ?>-item:hover img {
	filter: none;
	opacity: 1;
	transform: scale(1.08);
}
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<div id="<?php echo $uid; ?>" class="ar-brand-ticker <?php echo $style ?? ''; ?>">
	<div class="<?php echo $uid; ?>-track">
		<?php foreach ($loopItems as $item): ?>
		<div class="<?php echo $uid; ?>-item">
			<?php if (!empty($item['link'])): ?>
			<a href="<?php echo htmlspecialchars($item['link']); ?>" title="<?php echo htmlspecialchars($item['name']); ?>" rel="noopener">
			<?php endif; ?>
				<img src="<?php echo htmlspecialchars($item['logo']); ?>"
				     alt="<?php echo htmlspecialchars($item['name']); ?>"
				     title="<?php echo htmlspecialchars($item['name']); ?>"
				     loading="lazy">
			<?php if (!empty($item['link'])): ?>
			</a>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
</div>
