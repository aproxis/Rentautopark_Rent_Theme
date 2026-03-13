<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Simple Steps Style 1: Booking Process
 * ------------------------------------------------------------------------
 * ACM layout: Horizontal timeline or vertical steps for the booking process.
 * Clean design with icons, titles, and descriptions for each step.
 *
 * Extra Fields used:
 *   step-item.icon        (icon class, e.g. "fa fa-search")
 *   step-item.title       (step title)
 *   step-item.description (step description)
 *
 * Background: Via JA Extra Fields background image support
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$count = $helper->getRows('step-item.title');
$uid   = 'ar-simple-steps-' . $module->id;
?>

<style>
/* ═══ AutoRent Simple Steps (simple-steps/style-1) ══════════════════════ */
#<?php echo $uid; ?> {
	padding: 64px 20px;
	background: #fff;
	position: relative;
	overflow: hidden;
}
.<?php echo $uid; ?>-bg-overlay {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	pointer-events: none;
	opacity: 0.35;
}
.<?php echo $uid; ?>-bg-overlay .bg-gradient {
	width: 100%;
	height: 100%;
}
.<?php echo $uid; ?>-watermark {
	position: absolute;
	inset: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	pointer-events: none;
	opacity: 0.03;
	overflow: hidden;
}
.<?php echo $uid; ?>-watermark p {
	font-size: 180px;
	font-weight: 900;
	color: #222;
	white-space: nowrap;
}
.<?php echo $uid; ?>-container {
	max-width: 1400px;
	margin: 0 auto;
	padding: 0 20px;
	position: relative;
	z-index: 10;
}
.<?php echo $uid; ?>-heading {
	text-align: center;
	margin-bottom: 64px;
}
.<?php echo $uid; ?>-heading h2 {
	font-weight: 700;
	font-size: 36px;
	line-height: 1.2;
	color: #222;
}
@media (min-width: 576px) {
	.<?php echo $uid; ?>-heading h2 { font-size: 42px; }
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-heading h2 { font-size: 48px; }
}
@media (min-width: 1024px) {
	.<?php echo $uid; ?>-heading h2 { font-size: 56px; }
}
.<?php echo $uid; ?>-steps-wrapper {
	position: relative;
	max-width: 1200px;
	margin: 0 auto;
	padding: 48px 0;
}
.<?php echo $uid; ?>-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 48px;
}
.<?php echo $uid; ?>-step {
	position: relative;
	display: flex;
	flex-direction: column;
	align-items: center;
}
.<?php echo $uid; ?>-step-content {
	display: flex;
	flex-direction: row;
	align-items: center;
	gap: 16px;
	margin-bottom: 48px;
	order: 1;
}
.<?php echo $uid; ?>-step-wheel {
	width: 60px;
	height: 60px;
	flex-shrink: 0;
	transform-origin: center center;
	animation: wheelRotate 20s linear infinite;
}
.<?php echo $uid; ?>-step-title {
	font-weight: 500;
	font-size: 28px;
	line-height: 1.2;
	color: #222;
}
.<?php echo $uid; ?>-step-line {
	display: none;
	position: absolute;
	top: 50%;
	left: 50%;
	width: 100%;
	height: 8px;
	transform: translateY(-50%);
}
.<?php echo $uid; ?>-step-line .line-bg {
	position: absolute;
	left: -10%;
	top: -50%;
	transform: translateY(-50%);
	width: 20px;
	height: 20px;
	z-index: 20;
}
.<?php echo $uid; ?>-step-line .line-bg .dot {
	position: absolute;
	inset: 0;
	background: #FE5001;
	border-radius: 50%;
	box-shadow: 0 0 12px 4px rgba(254,80,1,0.6);
}
.<?php echo $uid; ?>-step-line .line-track {
	position: absolute;
	left: 50%;
	transform: translateX(-50%);
	width: 70%;
	height: 100%;
	display: flex;
	flex-direction: column;
	justify-content: center;
	gap: 2px;
}
.<?php echo $uid; ?>-step-line .line-track .track-line {
	width: 100%;
	height: 2px;
	background: #222;
	opacity: 0.3;
}
.<?php echo $uid; ?>-step-line .line-track .track-dashed {
	width: 100%;
	height: 3px;
	overflow: hidden;
	background: repeating-linear-gradient(to right, #FE5001 0px, #FE5001 10px, transparent 10px, transparent 20px);
	opacity: 0.4;
	animation: dashMove 2s linear infinite;
}
.<?php echo $uid; ?>-step-line .line-bg:nth-child(1) .dot {
	transform: scale(0.95);
}
.<?php echo $uid; ?>-step-line .line-bg:nth-child(2) .dot {
	transform: scale(0.99);
}
.<?php echo $uid; ?>-step-line .line-bg:nth-child(3) .dot {
	transform: scale(0.90);
}

/* Last step — dot only, centered */
.<?php echo $uid; ?>-step-dot-only {
	display: none;
	width: 20px;
	height: 20px;
	position: relative;
}
.<?php echo $uid; ?>-step-dot-only .dot {
	position: absolute;
	inset: 0;
	background: #FE5001;
	border-radius: 50%;
	box-shadow: 0 0 12px 4px rgba(254,80,1,0.6);
}
@media (min-width: 1024px) {
	.<?php echo $uid; ?>-step-dot-only {
		display: block;
	}
}

/* Responsive */
@media (max-width: 1024px) {
	.<?php echo $uid; ?>-grid {
		grid-template-columns: repeat(2, 1fr);
		gap: 32px;
	}
	.<?php echo $uid; ?>-step-content {
		margin-bottom: 32px;
	}
	.<?php echo $uid; ?>-step-wheel {
		width: 70px;
		height: 70px;
	}
	.<?php echo $uid; ?>-step-title {
		font-size: 32px;
	}
}
@media (max-width: 768px) {
	.<?php echo $uid; ?>-grid {
		grid-template-columns: 1fr;
		gap: 24px;
	}
	.<?php echo $uid; ?>-step {
		padding-top: 24px;
		padding-bottom: 24px;
	}
	.<?php echo $uid; ?>-step-content {
		flex-direction: column;
		gap: 12px;
		margin-bottom: 24px;
	}
	.<?php echo $uid; ?>-step-wheel {
		width: 60px;
		height: 60px;
	}
	.<?php echo $uid; ?>-step-title {
		font-size: 36px;
	}
	.<?php echo $uid; ?>-step-line {
		display: none !important;
	}
}
@media (max-width: 640px) {
	.<?php echo $uid; ?>-container {
		padding: 0 16px;
	}
	.<?php echo $uid; ?>-heading {
		margin-bottom: 48px;
	}
	.<?php echo $uid; ?>-watermark p {
		font-size: 240px;
	}
}
@media (min-width: 1024px) {
	.<?php echo $uid; ?>-step-line {
		display: block;
	}
	.<?php echo $uid; ?>-step:nth-child(1) {
		padding-top: 0;
		padding-bottom: 96px;
	}
	.<?php echo $uid; ?>-step:nth-child(2) {
		padding-top: 96px;
		padding-bottom: 0;
	}
	.<?php echo $uid; ?>-step:nth-child(2) .<?php echo $uid; ?>-step-content {
		margin: 48px 0 0 0;
	}
	.<?php echo $uid; ?>-step:nth-child(3) {
		padding-top: 0;
		padding-bottom: 96px;
	}
	.<?php echo $uid; ?>-step:nth-child(4) {
		padding-top: 96px;
		padding-bottom: 0;
	}
	.<?php echo $uid; ?>-step:nth-child(4) .<?php echo $uid; ?>-step-content {
		margin: 48px 0 0 0;
	}
}

/* Animation */
@keyframes wheelRotate {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}

@keyframes dashMove {
	from { background-position: 20px 0; }
	to { background-position: 0 0; }
}
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<div id="<?php echo $uid; ?>" class="acm-simple-steps style-1">
	<div class="<?php echo $uid; ?>-bg-overlay">
		<div class="bg-gradient" style="background-image: url('data:image/svg+xml;utf8,<svg viewBox=\'0 0 1365 820\' xmlns=\'http://www.w3.org/2000/svg\' preserveAspectRatio=\'none\'><rect x=\'0\' y=\'0\' height=\'100%\' width=\'100%\' fill=\'url(%23grad)\' opacity=\'1\'/><defs><radialGradient id=\'grad\' gradientUnits=\'userSpaceOnUse\' cx=\'0\' cy=\'0\' r=\'10\' gradientTransform=\'matrix(68.25 0 0 41 682.5 410)\'><stop stop-color=\'rgba(254,80,1,0.52)\' offset=\'0\'/><stop stop-color=\'rgba(254,80,1,0)\' offset=\'1\'/></radialGradient></defs></svg>');"></div>
	</div>
	<div class="<?php echo $uid; ?>-watermark">
		<p><?php echo $helper->get('watermark-text'); ?></p>
	</div>
	<div class="<?php echo $uid; ?>-container">
		<div class="<?php echo $uid; ?>-heading">
			<h2><?php echo $helper->get('block-title'); ?></h2>
		</div>
		<div class="<?php echo $uid; ?>-steps-wrapper">
			<div class="<?php echo $uid; ?>-grid">
		<?php for ($i = 0; $i < $count; $i++): 
			$wheel_image = $helper->get('step-item.wheel-image', $i);
			$title = $helper->get('step-item.title', $i);
			$description = $helper->get('step-item.description', $i);
			if (empty($title) && empty($description)) continue;
		?>
		<div class="<?php echo $uid; ?>-step">
			<div class="<?php echo $uid; ?>-step-content">
				<?php if (!empty($wheel_image)): ?>
				<img src="<?php echo htmlspecialchars($wheel_image); ?>" alt="Tire" class="<?php echo $uid; ?>-step-wheel" style="transform-origin: center center; transform: rotate(<?php echo (200 + ($i * 20)); ?>deg);">
				<?php else: ?>
				<img src="/_assets/v11/32b1aa55adfe6667b1b66f2064776f17779e4f4d.png" alt="Tire" class="<?php echo $uid; ?>-step-wheel" style="transform-origin: center center; transform: rotate(<?php echo (200 + ($i * 20)); ?>deg);">
				<?php endif; ?>
				<h3 class="<?php echo $uid; ?>-step-title"><?php echo $title; ?></h3>
			</div>
			<?php if ($i < ($count - 1)): ?>
			<div class="<?php echo $uid; ?>-step-line">
				<div class="line-bg" style="transform: scale(<?php echo (0.95 + ($i * 0.05)); ?>);">
					<div class="dot"></div>
				</div>
				<div class="line-track">
					<div class="track-line"></div>
					<div class="track-dashed"></div>
					<div class="track-line"></div>
				</div>
				<div class="line-bg" style="right: -10%; transform: scale(<?php echo (0.99 + ($i * 0.01)); ?>);">
					<div class="dot"></div>
				</div>
			</div>
			<?php else: ?>
			<div class="<?php echo $uid; ?>-step-line">
				<div class="line-bg" style="transform: scale(0.95);">
					<div class="dot"></div>
				</div>
				<div class="line-track" style="display:none;"></div>
				<div class="line-bg" style="display:none;"></div>
			</div>
			<?php endif; ?>
		</div>
		<?php endfor; ?>
	</div>
</div>