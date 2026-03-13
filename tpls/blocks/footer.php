<?php
/**
 * Footer override — AutoRent Figma Design
 * Module-based: footer-1..footer-4 columns + footer (copyright) bottom bar
 */
defined('_JEXEC') or die;

// Logo params (same as header)
$sitename  = $this->params->get('sitename');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';

if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}

$hasCol1 = $this->checkSpotlight('footer-col1', 'footer-1');
$hasCol2 = $this->checkSpotlight('footer-col2', 'footer-2');
$hasCol3 = $this->checkSpotlight('footer-col3', 'footer-3');
$hasCol4 = $this->checkSpotlight('footer-col4', 'footer-4');
$hasFooter = $this->countModules('footer');
?>

<!-- FOOTER -->
<footer class="bg-[#0a0a0a] text-white">
	<div class="container mx-auto px-4 py-12">

		<!-- 4-column grid -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">

			<!-- Column 1: footer-1 -->
			<div>
				<?php if ($hasCol1): ?>
					<?php $this->spotlight('footer-col1', 'footer-1') ?>
				<?php else: ?>
					<!-- Logo fallback if no module assigned -->
					<a class="flex items-center mb-4" href="<?php echo JURI::base(true); ?>">
						<?php if ($logotype == 'image' && !empty($logoimage)): ?>
							<img src="<?php echo JURI::base(true) . '/' . $logoimage; ?>"
							     alt="<?php echo strip_tags($sitename); ?>"
							     class="h-10 w-auto"
							     style="filter: brightness(0) invert(1);">
						<?php else: ?>
							<span class="text-xl font-extrabold text-white"><?php echo strip_tags($sitename); ?></span>
						<?php endif; ?>
					</a>
				<?php endif; ?>
			</div>

			<!-- Column 2: footer-2 -->
			<div>
				<?php if ($hasCol2): ?>
					<?php $this->spotlight('footer-col2', 'footer-2') ?>
				<?php endif; ?>
			</div>

			<!-- Column 3: footer-3 -->
			<div>
				<?php if ($hasCol3): ?>
					<?php $this->spotlight('footer-col3', 'footer-3') ?>
				<?php endif; ?>
			</div>

			<!-- Column 4: footer-4 -->
			<div>
				<?php if ($hasCol4): ?>
					<?php $this->spotlight('footer-col4', 'footer-4') ?>
				<?php endif; ?>
			</div>

		</div>

		<!-- Bottom bar -->
		<div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
			<?php if ($hasFooter): ?>
				<jdoc:include type="modules" name="<?php $this->_p('footer') ?>" style="raw" />
			<?php else: ?>
				<p class="text-gray-400 text-sm">© <?php echo date('Y'); ?> <?php echo strip_tags($sitename); ?>. Toate drepturile rezervate.</p>
			<?php endif; ?>
		</div>

	</div>
</footer>
<!-- //FOOTER -->

<style>
/* ================================================================
   Footer module overrides — ensure modules render correctly
   inside the dark footer layout
   ================================================================ */

/* Reset any white backgrounds from module wrappers */
#t3-footer .moduletable,
footer.bg-\[\#0a0a0a\] .moduletable {
	background: transparent !important;
	border: none !important;
	box-shadow: none !important;
	padding: 0 !important;
	margin: 0 !important;
}

/* Module titles inside footer */
footer.bg-\[\#0a0a0a\] .moduletable h3,
footer.bg-\[\#0a0a0a\] .moduletable .module-title {
	color: #fff;
	font-size: 1rem;
	font-weight: 700;
	margin-bottom: 1rem;
}

/* Links inside footer modules */
footer.bg-\[\#0a0a0a\] .moduletable a {
	color: #9ca3af;
	text-decoration: none;
	font-size: 0.875rem;
	transition: color .2s;
}
footer.bg-\[\#0a0a0a\] .moduletable a:hover {
	color: #FE5001;
}

/* Lists inside footer modules */
footer.bg-\[\#0a0a0a\] .moduletable ul {
	list-style: none;
	margin: 0;
	padding: 0;
}
footer.bg-\[\#0a0a0a\] .moduletable ul li {
	margin-bottom: 0.5rem;
}

/* Paragraphs / text inside footer modules */
footer.bg-\[\#0a0a0a\] .moduletable p {
	color: #9ca3af;
	font-size: 0.875rem;
	margin-bottom: 1rem;
}

/* Copyright / bottom bar text */
footer.bg-\[\#0a0a0a\] .border-t .moduletable p,
footer.bg-\[\#0a0a0a\] .border-t p {
	color: #9ca3af;
	font-size: 0.875rem;
	margin: 0;
}

/* Logo image in footer col-1 module */
footer.bg-\[\#0a0a0a\] .moduletable img.footer-logo {
	height: 40px;
	width: auto;
	filter: brightness(0) invert(1);
	margin-bottom: 1rem;
}

/* Social icon buttons (if module outputs them) */
footer.bg-\[\#0a0a0a\] .footer-social {
	display: flex;
	gap: 0.75rem;
	margin-top: 0.75rem;
}
footer.bg-\[\#0a0a0a\] .footer-social a {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	background: rgba(255,255,255,.1);
	border-radius: 8px;
	color: #fff;
	transition: background .2s;
}
footer.bg-\[\#0a0a0a\] .footer-social a:hover {
	background: #FE5001;
	color: #fff;
}

/* Schedule / hours table */
footer.bg-\[\#0a0a0a\] .footer-hours li {
	display: flex;
	justify-content: space-between;
	color: #9ca3af;
	font-size: 0.875rem;
	margin-bottom: 0.5rem;
}
footer.bg-\[\#0a0a0a\] .footer-hours li span.value {
	color: #fff;
}

/* NON-STOP badge */
footer.bg-\[\#0a0a0a\] .footer-nonstop {
	margin-top: 1rem;
	padding: 0.75rem;
	background: rgba(254,80,1,.1);
	border: 1px solid rgba(254,80,1,.2);
	border-radius: 0.5rem;
	font-size: 0.75rem;
	color: #9ca3af;
}
footer.bg-\[\#0a0a0a\] .footer-nonstop strong {
	color: #FE5001;
}

/* Contact items with icon */
footer.bg-\[\#0a0a0a\] .footer-contact li {
	display: flex;
	align-items: flex-start;
	gap: 0.75rem;
	margin-bottom: 0.75rem;
}
footer.bg-\[\#0a0a0a\] .footer-contact li svg,
footer.bg-\[\#0a0a0a\] .footer-contact li i {
	color: #FE5001;
	flex-shrink: 0;
	margin-top: 2px;
	width: 20px;
	height: 20px;
}

/* Bottom bar privacy/terms links */
footer.bg-\[\#0a0a0a\] .footer-bottom-links {
	display: flex;
	gap: 1.5rem;
}
footer.bg-\[\#0a0a0a\] .footer-bottom-links a {
	color: #9ca3af;
	font-size: 0.875rem;
	text-decoration: none;
	transition: color .2s;
}
footer.bg-\[\#0a0a0a\] .footer-bottom-links a:hover {
	color: #FE5001;
}
</style>
