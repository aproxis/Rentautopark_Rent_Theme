<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Conditions Style 1: Rental Conditions
 * ------------------------------------------------------------------------
 * ACM layout: Clean cards for rental conditions (age, documents, experience, km limit).
 * Orange accent styling with icons, consistent with AutoRent design system.
 *
 * Extra Fields used:
 *   condition-item.icon     (icon class, e.g. "fa fa-user")
 *   condition-item.title    (condition title)
 *   condition-item.text     (condition description)
 *
 * Background: Via JA Extra Fields background image support
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$count = $helper->getRows('condition-item.title');
$uid   = 'ar-conditions-' . $module->id;
?>

<style>
/* ═══ AutoRent Rental Conditions (conditions/style-1) ═══════════════════ */
#<?php echo $uid; ?> {
	padding: 40px 20px;
	background-color: #f3f4f6; /* Gray-50 background */
}
.<?php echo $uid; ?>-container {
	max-width: 1440px;
	margin: 0 auto;
	padding: 0 20px;
}
.<?php echo $uid; ?>-wrapper {
	background: #fff;
	border-radius: 16px;
	box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
	padding: 32px;
}
.<?php echo $uid; ?>-heading {
	font-weight: 700;
	color: #0a0a0a;
	margin-bottom: 32px;
	text-align: center;
	font-size: 1.5rem; /* text-2xl (24px) mobile */
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-heading {
		font-size: 1.875rem; /* text-3xl (30px) desktop */
	}
}
.<?php echo $uid; ?>-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 24px;
}
.<?php echo $uid; ?>-card {
	display: flex;
	flex-direction: column;
	align-items: center;
	text-align: center;
	gap: 16px;
	padding: 16px;
	border-radius: 12px;
	transition: transform 0.2s ease;
}
.<?php echo $uid; ?>-icon-wrapper {
	background: #eff6ff; /* Blue-50 */
	padding: 16px;
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
}
.<?php echo $uid; ?>-icon {
	width: 32px;
	height: 32px;
	font-size: 32px;
	line-height: 1;
	color: #2563eb; /* Blue-600 */
}
.<?php echo $uid; ?>-content h3 {
	color: #6b7280; /* text-muted-foreground */
	font-size: 14px; /* text-sm */
	margin: 0 0 8px 0;
	font-weight: 400;
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-content h3 { font-size: 16px; } /* md:text-base */
}
.<?php echo $uid; ?>-content p {
	font-weight: 600; /* font-semibold */
	color: #0a0a0a; /* text-foreground */
	font-size: 16px; /* text-base */
	margin: 0;
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-content p { font-size: 18px; } /* md:text-lg */
}

/* Icon color overrides */
.<?php echo $uid; ?>-card:nth-child(1) .<?php echo $uid; ?>-icon-wrapper { background: #eff6ff; }
.<?php echo $uid; ?>-card:nth-child(1) .<?php echo $uid; ?>-icon { color: #2563eb; }
.<?php echo $uid; ?>-card:nth-child(2) .<?php echo $uid; ?>-icon-wrapper { background: #f3e8ff; }
.<?php echo $uid; ?>-card:nth-child(2) .<?php echo $uid; ?>-icon { color: #7c3aed; }
.<?php echo $uid; ?>-card:nth-child(3) .<?php echo $uid; ?>-icon-wrapper { background: #ecfdf5; }
.<?php echo $uid; ?>-card:nth-child(3) .<?php echo $uid; ?>-icon { color: #16a34a; }
.<?php echo $uid; ?>-card:nth-child(4) .<?php echo $uid; ?>-icon-wrapper { background: #fff7ed; }
.<?php echo $uid; ?>-card:nth-child(4) .<?php echo $uid; ?>-icon { color: #ea580c; }

/* Responsive */
@media (max-width: 1024px) {
	.<?php echo $uid; ?>-grid {
		grid-template-columns: repeat(2, 1fr);
	}
}
@media (max-width: 768px) {
	.<?php echo $uid; ?>-grid {
		grid-template-columns: 1fr;
		gap: 20px;
	}
	.<?php echo $uid; ?>-wrapper {
		padding: 24px;
	}
}
@media (max-width: 640px) {
	.<?php echo $uid; ?>-container {
		padding: 0 16px;
	}
	.<?php echo $uid; ?>-wrapper {
		padding: 20px;
	}
}
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<div id="<?php echo $uid; ?>" class="acm-conditions style-1">
	<div class="<?php echo $uid; ?>-container">
		<div class="<?php echo $uid; ?>-wrapper">
			<h3 class="<?php echo $uid; ?>-heading"><?php echo $helper->get('block-title'); ?></h3>
			<div class="<?php echo $uid; ?>-grid">
		<?php for ($i = 0; $i < $count; $i++): 
			$icon = $helper->get('condition-item.icon', $i);
			$title = $helper->get('condition-item.title', $i);
			$text = $helper->get('condition-item.text', $i);
			if (empty($title) && empty($text)) continue;
		?>
		<div class="<?php echo $uid; ?>-card">
			<div class="<?php echo $uid; ?>-icon-wrapper">
				<?php if (!empty($icon)): ?>
				<i class="<?php echo htmlspecialchars($icon); ?> <?php echo $uid; ?>-icon"></i>
				<?php else: ?>
				<i class="fa fa-check <?php echo $uid; ?>-icon"></i>
				<?php endif; ?>
			</div>
			<div class="<?php echo $uid; ?>-content">
				<?php if (!empty($title)): ?>
				<h3><?php echo $title; ?></h3>
				<?php endif; ?>
				<?php if (!empty($text)): ?>
				<p><?php echo $text; ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php endfor; ?>
	</div>
</div>