<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Accordion Style 2: Clean FAQ
 * ------------------------------------------------------------------------
 * ACM layout: standalone FAQ accordion without a tabs wrapper.
 * AutoRent design: clean lines, orange +/− toggle icon, smooth animation.
 *
 * Extra Fields used (same as style-1):
 *   data.accordion-name   (question text)
 *   data.accordion-desc   (answer HTML)
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$count = $helper->getRows('data.accordion-name');
$uid   = 'ar-faq-' . $module->id;
?>

<style>
/* ═══ AutoRent FAQ Accordion (accordion/style-2) ════════════════════════ */
#<?php echo $uid; ?> {
	max-width: 860px;
	margin: 0 auto;
	padding: 32px 40px;
}
.<?php echo $uid; ?>-item {
	margin-bottom: 12px;
	border-radius: 10px;
	overflow: hidden;
	border: 1px solid #e5e7eb;
	transition: border-color .3s ease, box-shadow .3s ease;
}
.<?php echo $uid; ?>-item.ar-faq-open {
	border-color: #FE5001;
	box-shadow: 0 0 0 2px rgba(254,80,1,.12);
}
.<?php echo $uid; ?>-trigger {
	display: flex;
	align-items: center;
	justify-content: space-between;
	width: 100%;
	background: #fff;
	border: none;
	padding: 20px;
	cursor: pointer;
	text-align: left;
	transition: background .2s ease;
	border-radius: 0;
}
.<?php echo $uid; ?>-trigger:hover {
	background: #fafafa;
}
.<?php echo $uid; ?>-trigger .ar-faq-q {
	font-weight: 600;
	font-size: 15px;
	color: #222;
	line-height: 1.4;
	flex: 1;
	text-align: left;
}
.<?php echo $uid; ?>-trigger .ar-faq-icon {
	flex-shrink: 0;
	width: 32px;
	height: 32px;
	background: #FE5001;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: transform 0.3s ease;
	margin-left: 16px;
	color: #fff;
}
.<?php echo $uid; ?>-item.ar-faq-open .<?php echo $uid; ?>-trigger .ar-faq-icon {
	transform: rotate(45deg);
}
.<?php echo $uid; ?>-trigger .ar-faq-icon span {
	color: #fff;
	font-size: 20px;
	font-weight: 700;
	line-height: 1;
}
.<?php echo $uid; ?>-body {
	overflow: hidden;
	transition: max-height .3s ease, opacity .3s ease;
	max-height: 0;
	opacity: 0;
}
.<?php echo $uid; ?>-item.ar-faq-open .<?php echo $uid; ?>-body {
	max-height: 600px;
	opacity: 1;
}
.<?php echo $uid; ?>-body-inner {
	padding: 0 20px 20px;
	color: #666;
	line-height: 1.6;
	font-size: 14px;
	background: #fff;
	border-top: 1px solid #f3f4f6;
}
.<?php echo $uid; ?>-body-inner p:last-child { margin-bottom: 0; }

@media (max-width: 768px) {
	.<?php echo $uid; ?>-trigger {
		padding: 20px;
	}
	.<?php echo $uid; ?>-trigger .ar-faq-q {
		font-size: 15px;
	}
	.<?php echo $uid; ?>-trigger .ar-faq-icon {
		flex: 0 0 32px;
		width: 32px;
		height: 32px;
	}
	.<?php echo $uid; ?>-body-inner {
		padding: 20px;
		font-size: 15px;
	}
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-trigger .ar-faq-q {
		font-size: 16px;
	}
	.<?php echo $uid; ?>-body-inner {
		font-size: 15px;
	}
}
@media (min-width: 1024px) {
	.<?php echo $uid; ?>-trigger .ar-faq-q {
		font-size: 17px;
	}
	.<?php echo $uid; ?>-body-inner {
		font-size: 16px;
	}
}
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<div id="<?php echo $uid; ?>" class="acm-accordion style-2" role="list">
	<div class="text-center mb-12 md:mb-16" style="opacity: 1; transform: none;">
		<h2 class="font-bold text-[28px] sm:text-[32px] md:text-[36px] lg:text-[40px] leading-[1.2] text-[#222] mb-4"><?php echo $helper->get('block-intro'); ?></h2>
	</div>
	<?php for ($i = 0; $i < $count; $i++): ?>
	<div class="<?php echo $uid; ?>-item<?php if ($i === 0): ?> ar-faq-open<?php endif; ?>" role="listitem">

		<button class="<?php echo $uid; ?>-trigger"
		        aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>"
		        aria-controls="<?php echo $uid; ?>-body-<?php echo $i; ?>"
		        onclick="arFaqToggle<?php echo $module->id; ?>(this)">
			<span class="ar-faq-q"><?php echo $helper->get('data.accordion-name', $i); ?></span>
			<span class="ar-faq-icon" aria-hidden="true">+</span>
		</button>

		<div class="<?php echo $uid; ?>-body"
		     id="<?php echo $uid; ?>-body-<?php echo $i; ?>"
		     role="region">
			<div class="<?php echo $uid; ?>-body-inner">
				<?php echo $helper->get('data.accordion-desc', $i); ?>
			</div>
		</div>

	</div>
	<?php endfor; ?>
</div>

<script>
(function() {
	function arFaqToggle<?php echo $module->id; ?>(btn) {
		var item    = btn.parentElement;
		var faqWrap = document.getElementById('<?php echo $uid; ?>');
		var isOpen  = item.classList.contains('ar-faq-open');

		// Close all open items in this accordion
		var allItems = faqWrap.querySelectorAll('.<?php echo $uid; ?>-item');
		allItems.forEach(function(el) {
			el.classList.remove('ar-faq-open');
			el.querySelector('button').setAttribute('aria-expanded', 'false');
		});

		// Open clicked item if it was closed
		if (!isOpen) {
			item.classList.add('ar-faq-open');
			btn.setAttribute('aria-expanded', 'true');
		}
	}
	// Expose to global scope (called via onclick attribute)
	window.arFaqToggle<?php echo $module->id; ?> = arFaqToggle<?php echo $module->id; ?>;
})();
</script>
