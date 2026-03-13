<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Features Intro Style 3: "How It Works"
 * ------------------------------------------------------------------------
 * ACM layout: 4 numbered steps in a 2×2 grid with large orange circles.
 * No owl carousel. Pure CSS grid layout.
 *
 * Extra Fields used (same as style-1):
 *   data.font-icon    (FontAwesome class, e.g. "fa fa-key")
 *   data.title        (step title)
 *   data.description  (step description text)
 *
 * Expects exactly 4 rows (or more — extras rendered in the same grid).
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$count = $helper->getRows('data.title');
$uid   = 'ar-hiw-' . $module->id;
?>

<style>
/* ═══ AutoRent Features Intro Modern Gray Theme (style-3) ═════════════════ */
#<?php echo $uid; ?> {
	position: relative;
	padding: 48px 20px;
	background: #f9fafb;
}
.<?php echo $uid; ?>-container {
	max-width: 1440px;
	margin: 0 auto;
	padding: 0 20px;
}
@media (min-width: 576px) {
	.<?php echo $uid; ?>-container { padding: 0 30px; }
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-container { padding: 0 40px; }
}
@media (min-width: 1024px) {
	.<?php echo $uid; ?>-container { padding: 0 50px; }
}
.<?php echo $uid; ?>-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 24px;
}
.<?php echo $uid; ?>-step {
	background: #fff;
	border-radius: 16px;
	padding: 24px;
	box-shadow: 0 4px 6px rgba(0,0,0,0.05);
	transition: all 0.3s ease;
}
.<?php echo $uid; ?>-step:hover {
	box-shadow: 0 8px 24px rgba(0,0,0,0.1);
	transform: translateY(-2px);
}
.<?php echo $uid; ?>-number {
	width: 64px;
	height: 64px;
	background: #FE5001;
	border-radius: 16px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 24px;
}
.<?php echo $uid; ?>-number i {
	font-size: 24px;
	color: #fff;
}
.<?php echo $uid; ?>-content h3 {
	font-size: 18px;
	font-weight: 700;
	color: #222;
	margin: 0 0 12px 0;
}
.<?php echo $uid; ?>-content p {
	font-size: 14px;
	color: #666;
	line-height: 1.6;
	margin: 0;
}

/* Responsive */
@media (max-width: 1024px) {
	.<?php echo $uid; ?>-grid {
		grid-template-columns: repeat(2, 1fr);
	}
}
@media (max-width: 768px) {
	.<?php echo $uid; ?>-grid {
		grid-template-columns: 1fr;
	}
	.<?php echo $uid; ?>-number {
		width: 64px;
		height: 64px;
	}
	.<?php echo $uid; ?>-number i {
		font-size: 24px;
	}
}
@media (max-width: 640px) {
	#<?php echo $uid; ?> {
		padding: 32px 20px;
	}
	.<?php echo $uid; ?>-step {
		padding: 20px;
	}
	.<?php echo $uid; ?>-number {
		width: 56px;
		height: 56px;
		margin-bottom: 20px;
	}
	.<?php echo $uid; ?>-number i {
		font-size: 22px;
	}
}
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<div id="<?php echo $uid; ?>" class="acm-features-intro style-3">
	<div class="<?php echo $uid; ?>-container">
		<div class="text-center mb-12 md:mb-16" style="opacity: 1; transform: none;">
			<h2 class="font-bold text-[28px] sm:text-[32px] md:text-[36px] lg:text-[40px] leading-[1.2] text-[#222] mb-4"><?php echo $helper->get('services-title'); ?></h2>
			<p class="text-[15px] md:text-[16px] lg:text-[17px] text-[#666] max-w-2xl mx-auto"><?php echo $helper->get('services-desc'); ?></p>
		</div>
		<div class="<?php echo $uid; ?>-grid">
		<?php for ($i = 0; $i < $count; $i++): ?>
		<div class="<?php echo $uid; ?>-step">

			<div class="<?php echo $uid; ?>-number">
				<?php
				$icon = $helper->get('data.font-icon', $i);
				if (!empty($icon)): ?>
				<i class="<?php echo htmlspecialchars($icon); ?>"></i>
				<?php else: ?>
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star">
						<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
					</svg>
				<?php endif; ?>
			</div>

			<div class="<?php echo $uid; ?>-content">
				<h3><?php echo $helper->get('data.title', $i); ?></h3>
				<p><?php echo $helper->get('data.description', $i); ?></p>
			</div>

		</div>
		<?php endfor; ?>
		</div><!-- /.grid -->
	</div><!-- /.container -->
</div>
