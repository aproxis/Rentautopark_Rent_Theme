<?php
/**
 * Header override — AutoRent Figma Design
 * Single "My Account" auth button | Logo | Nav Center | LangSwitcher
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

// Logo params from T3
$sitename  = $this->params->get('sitename');
$slogan    = $this->params->get('slogan', '');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true)) : false;

if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}
?>

<style>
/* ================================================================
   AutoRent Header
   ================================================================ */

/* Hide old topbar completely */
#t3-topbar { display: none !important; }

/* Header container */
#ar-header {
	position: sticky;
	top: 0;
	z-index: 1000;
	background: rgba(255,255,255,.97);
	backdrop-filter: blur(12px);
	-webkit-backdrop-filter: blur(12px);
	border-bottom: 1px solid #e5e7eb;
	transition: box-shadow .3s;
}
#ar-header.scrolled {
	box-shadow: 0 4px 20px rgba(0,0,0,.08);
}

.ar-header-inner {
	max-width: 1200px;
	margin: 0 auto;
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0 16px;
	height: 68px;
	gap: 24px;
}

/* ── Logo ──────────────────────────────────────────────────────── */
.ar-logo a {
	display: flex;
	align-items: center;
	gap: 10px;
	text-decoration: none;
	flex-shrink: 0;
}
.ar-logo img {
	height: 40px;
	width: auto;
	object-fit: contain;
}
.ar-logo .ar-logo-text {
	font-size: 1.25rem;
	font-weight: 800;
	color: #0a0a0a;
	white-space: nowrap;
}
.ar-logo .ar-logo-text span { color: #FE5001; }
.ar-logo small { display: none; }

/* ── Desktop Navigation (center) ──────────────────────────────── */
.ar-nav-desktop {
	display: flex;
	align-items: center;
	gap: 4px;
	flex: 1;
	justify-content: center;
}
.ar-nav-desktop ul {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	align-items: center;
	gap: 4px;
}
.ar-nav-desktop li { margin: 0; padding: 0; }
.ar-nav-desktop li a {
	display: flex;
	align-items: center;
	padding: 8px 16px;
	font-size: 14px;
	font-weight: 500;
	color: #6b7280;
	text-decoration: none;
	border-radius: 8px;
	transition: color .2s, background .2s;
	white-space: nowrap;
}
.ar-nav-desktop li a:hover { color: #0a0a0a; background: #f3f4f6; }
.ar-nav-desktop li.active > a,
.ar-nav-desktop li.current > a,
.ar-nav-desktop li.alias-parent-active > a {
	color: #FE5001;
	background: rgba(254,80,1,.06);
	font-weight: 600;
}
/* Hide megamenu submenus */
.ar-nav-desktop .t3-megamenu .mega-dropdown-menu,
.ar-nav-desktop .dropdown-menu,
.ar-nav-desktop .caret,
.ar-nav-desktop .nav-child,
.ar-nav-desktop .mega-nav .mega-group,
.ar-nav-desktop .dropdown-submenu { display: none !important; }
.ar-nav-desktop .t3-megamenu .nav > li > a { padding: 8px 16px; }

/* ── Right group ───────────────────────────────────────────────── */
.ar-right {
	display: flex;
	align-items: center;
	gap: 10px;
	flex-shrink: 0;
}

/* ── Language switcher ─────────────────────────────────────────── */
.ar-lang { position: relative; }
.ar-lang .mod-languages { position: relative; }
.ar-lang .dropdown-toggle {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 7px 12px;
	background: #f3f4f6;
	border: 1.5px solid #e5e7eb;
	border-radius: 8px;
	font-size: 13px;
	font-weight: 600;
	color: #374151;
	cursor: pointer;
	text-decoration: none;
	transition: border-color .2s, background .2s;
	white-space: nowrap;
}
.ar-lang .dropdown-toggle:hover { border-color: #d1d5db; background: #e5e7eb; }
.ar-lang .dropdown-toggle img {
	width: 18px; height: 14px;
	object-fit: cover; border-radius: 2px;
}
.ar-lang .dropdown-toggle .fa-caret-down { font-size: 10px; color: #9ca3af; margin-left: 2px; }
.ar-lang .dropdown-menu {
	position: absolute;
	top: calc(100% + 6px);
	right: 0; left: auto;
	min-width: 140px;
	background: #fff;
	border: 1.5px solid #e5e7eb;
	border-radius: 10px;
	box-shadow: 0 8px 24px rgba(0,0,0,.1);
	padding: 6px;
	margin: 0;
	z-index: 1050;
	list-style: none;
	display: none;
}
.ar-lang .mod-languages.open .dropdown-menu,
.ar-lang .dropdown-menu.show { display: block; }
.ar-lang .dropdown-menu li { margin: 0; padding: 0; }
.ar-lang .dropdown-menu li a {
	display: flex; align-items: center; gap: 8px;
	padding: 8px 12px;
	font-size: 13px; font-weight: 500; color: #374151;
	text-decoration: none; border-radius: 6px;
	transition: background .15s, color .15s;
}
.ar-lang .dropdown-menu li a:hover { background: #f3f4f6; color: #0a0a0a; }
.ar-lang .dropdown-menu li.lang-active a {
	color: #FE5001; font-weight: 600;
	background: rgba(254,80,1,.06);
}
.ar-lang .dropdown-menu li a img {
	width: 20px; height: 15px;
	object-fit: cover; border-radius: 2px;
}
.ar-lang .dropdown-menu li a span { flex: 1; }

/* ── Mobile Icon Navigation ────────────────────────────────────── */
.ar-mobile-icon-nav {
	display: none;
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	background: #fff;
	border-top: 1px solid #e5e7eb;
	box-shadow: 0 -4px 12px rgba(0,0,0,.08);
	z-index: 9999;
	padding: 8px 0;
	justify-content: space-around;
	align-items: center;
}

.ar-mobile-icon-btn {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 8px 12px;
	color: #6b7280;
	text-decoration: none;
	transition: color .2s, background .2s;
	border-radius: 8px;
	position: relative;
	min-width: 60px;
}

.ar-mobile-icon-btn:hover,
.ar-mobile-icon-btn:focus {
	color: #FE5001;
	background: rgba(254,80,1,.08);
	text-decoration: none;
}

.ar-mobile-icon-btn svg {
	margin-bottom: 4px;
}

.ar-mobile-icon-label {
	font-size: 10px;
	font-weight: 600;
	white-space: nowrap;
}

/* Language button - hide the dropdown text, show only icon */
.ar-mobile-lang-btn .dropdown,
.ar-mobile-lang-btn .mod-languages {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	opacity: 0;
	cursor: pointer;
}

.ar-mobile-lang-btn .dropdown-toggle,
.ar-mobile-lang-btn a.dropdown-toggle {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	opacity: 0;
	cursor: pointer;
}

.ar-mobile-lang-btn .dropdown-menu {
	bottom: 100% !important;
	top: auto !important;
	left: 50% !important;
	right: auto !important;
	transform: translateX(-50%) !important;
	margin-bottom: 8px;
}

/* Account button - hide module content, show only icon */
.ar-mobile-account-btn .ja-login-form,
.ar-mobile-account-btn .mod_jalogin,
.ar-mobile-account-btn > div {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	opacity: 0;
	cursor: pointer;
}

.ar-mobile-account-btn .ar-myaccount-btn,
.ar-mobile-account-btn .ar-account-btn {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	opacity: 0;
	cursor: pointer;
	padding: 0;
	border: none;
	background: none;
}

.ar-mobile-account-btn .ar-myaccount-btn .ar-myaccount-label,
.ar-mobile-account-btn .ar-account-btn .ar-account-name,
.ar-mobile-account-btn .ar-account-btn .ar-account-caret {
	display: none !important;
}

.ar-mobile-account-btn .ar-myaccount-btn svg,
.ar-mobile-account-btn .ar-account-btn svg {
	display: none !important;
}

/* Show mobile icons on small screens */
@media (max-width: 991px) {
	.ar-mobile-icon-nav {
		display: flex;
	}
	
	/* Adjust body padding for fixed bottom nav */
	body {
		padding-bottom: 70px;
	}
	
	/* Hide account dropdown username on small screens */
	.ar-mobile-account-btn .ar-account-name {
		display: none !important;
	}
}

/* Extra small screens */
@media (max-width: 480px) {
	.ar-mobile-icon-btn {
		min-width: 50px;
		padding: 6px 8px;
	}
	
	.ar-mobile-icon-btn svg {
		width: 18px;
		height: 18px;
	}
	
	.ar-mobile-icon-label {
		font-size: 9px;
	}
}

/* ── Mobile hamburger (hidden on mobile) ───────────────────────── */
.ar-mobile-toggle {
	display: none;
}

/* ── Mobile menu panel ─────────────────────────────────────────── */
.ar-mobile-menu {
	display: none;
	position: absolute;
	top: 100%; left: 0; right: 0;
	background: #fff;
	border-bottom: 2px solid #e5e7eb;
	box-shadow: 0 8px 30px rgba(0,0,0,.1);
	padding: 16px;
	z-index: 999;
}
.ar-mobile-menu.open { display: block; }
.ar-mobile-menu ul { list-style: none; margin: 0; padding: 0; }
.ar-mobile-menu li { margin: 0; }
.ar-mobile-menu li a {
	display: block;
	padding: 12px 16px;
	font-size: 15px; font-weight: 500; color: #374151;
	text-decoration: none; border-radius: 8px;
	transition: color .15s, background .15s;
}
.ar-mobile-menu li a:hover { background: #f3f4f6; color: #0a0a0a; }
.ar-mobile-menu li.active > a,
.ar-mobile-menu li.current > a { color: #FE5001; font-weight: 600; }

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 991px) {
	.ar-nav-desktop { display: none; }
	.ar-mobile-toggle { display: flex; }
	.ar-header-inner { height: 60px; }
}
/* Auth button always visible — even on small screens */
@media (max-width: 600px) {
	.ar-header-inner { padding: 0 12px; gap: 10px; }
	.ar-logo img { height: 34px; }
	.ar-logo .ar-logo-text { font-size: 1.05rem; }
}
</style>

<!-- HEADER -->
<header id="ar-header">
	<div class="ar-header-inner">

		<!-- LOGO -->
		<div class="ar-logo">
			<a href="<?php echo JURI::base(true); ?>" title="<?php echo strip_tags($sitename); ?>">
				<?php if ($logotype == 'image' && !empty($logoimage)): ?>
					<img class="logo-img" src="<?php echo JURI::base(true) . '/' . $logoimage; ?>" alt="<?php echo strip_tags($sitename); ?>" />
				<?php endif; ?>
				<?php if ($logoimgsm): ?>
					<img class="logo-img-sm" src="<?php echo JURI::base(true) . '/' . $logoimgsm; ?>" alt="<?php echo strip_tags($sitename); ?>" />
				<?php endif; ?>
				<?php if ($logotype != 'image' || empty($logoimage)): ?>
					<span class="ar-logo-text">Rent<span>Auto</span>Park</span>
				<?php endif; ?>
			</a>
		</div>

		<!-- DESKTOP NAVIGATION -->
		<nav class="ar-nav-desktop">
			<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu'); ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu'); ?>" />
		</nav>

		<!-- RIGHT GROUP -->
		<div class="ar-right">

			<!-- Auth module (My Account button or logged-in dropdown — rendered by mod_jalogin) -->
			<?php if ($this->countModules('loginload')): ?>
			<div class="ar-auth">
				<jdoc:include type="modules" name="<?php $this->_p('loginload'); ?>" style="raw" />
			</div>
			<?php endif; ?>

			<!-- Language Switcher -->
			<?php if ($this->countModules('languageswitcherload')): ?>
			<div class="ar-lang">
				<jdoc:include type="modules" name="<?php $this->_p('languageswitcherload'); ?>" style="raw" />
			</div>
			<?php else: ?>
			<?php
				$_langMod = JModuleHelper::getModule('mod_languages');
				if ($_langMod && $_langMod->id):
			?>
			<div class="ar-lang">
				<?php echo JModuleHelper::renderModule($_langMod, array('style' => 'raw')); ?>
			</div>
			<?php endif; ?>
			<?php endif; ?>

			<!-- Mobile Icon Navigation -->
			<div class="ar-mobile-icon-nav">
				<a href="<?php echo JURI::base(true); ?>/index.php?option=com_contact" class="ar-mobile-icon-btn" title="<?php echo JText::_('TPL_CONTACT'); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M19 1.5H5a2 2 0 00-2 2v14a2 2 0 002 2h4l3 3 3-3h4c1.1 0 2-.9 2-2v-14c0-1.1-.9-2-2-2zm-6 16h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 11.4 13 12 13 13.5h-2V13c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
					</svg>
					<span class="ar-mobile-icon-label"><?php echo JText::_('TPL_CONTACT'); ?></span>
				</a>
				<a href="<?php echo JURI::base(true); ?>/index.php?option=com_vikrentcar&view=carslist" class="ar-mobile-icon-btn" title="<?php echo JText::_('TPL_CARS'); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
					</svg>
					<span class="ar-mobile-icon-label"><?php echo JText::_('TPL_CARS'); ?></span>
				</a>
				<div class="ar-mobile-icon-btn ar-mobile-lang-btn">
					<?php if ($this->countModules('languageswitcherload')): ?>
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95a15.65 15.65 0 00-1.38-3.56A8.03 8.03 0 0118.92 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56A7.987 7.987 0 015.08 16zm2.95-8H5.08a7.987 7.987 0 014.33-3.56A15.65 15.65 0 008.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95a8.03 8.03 0 01-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/>
						</svg>
						<span class="ar-mobile-icon-label"><?php echo JText::_('TPL_LANGUAGE'); ?></span>
						<jdoc:include type="modules" name="<?php $this->_p('languageswitcherload'); ?>" style="raw" />
					<?php endif; ?>
				</div>
				<div class="ar-mobile-icon-btn ar-mobile-account-btn">
					<?php if ($this->countModules('loginload')): ?>
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
						</svg>
						<span class="ar-mobile-icon-label"><?php echo JText::_('TPL_ACCOUNT'); ?></span>
						<jdoc:include type="modules" name="<?php $this->_p('loginload'); ?>" style="raw" />
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- MOBILE MENU (nav only — auth button already in header, always visible) -->
	<div class="ar-mobile-menu" id="ar-mobile-menu">
		<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu'); ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu'); ?>" />
	</div>
</header>
<!-- //HEADER -->

<script>
(function(){
	// Sticky shadow on scroll
	var header = document.getElementById('ar-header');
	if (header) {
		window.addEventListener('scroll', function() {
			header.classList.toggle('scrolled', window.scrollY > 10);
		}, { passive: true });
	}
})();

// Mobile menu
function arCloseMobileMenu() {
	var menu = document.getElementById('ar-mobile-menu');
	var iconMenu  = document.getElementById('ar-menu-icon');
	var iconClose = document.getElementById('ar-close-icon');
	if (menu)      menu.classList.remove('open');
	if (iconMenu)  iconMenu.style.display  = '';
	if (iconClose) iconClose.style.display = 'none';
}
function arToggleMobile() {
	var menu = document.getElementById('ar-mobile-menu');
	var iconMenu  = document.getElementById('ar-menu-icon');
	var iconClose = document.getElementById('ar-close-icon');
	if (!menu) return;
	var isOpen = menu.classList.contains('open');
	menu.classList.toggle('open', !isOpen);
	if (iconMenu)  iconMenu.style.display  = isOpen ? ''     : 'none';
	if (iconClose) iconClose.style.display = isOpen ? 'none' : '';
}

document.addEventListener('DOMContentLoaded', function() {
	// Close mobile menu on any nav link click
	document.querySelectorAll('.ar-mobile-menu a').forEach(function(link) {
		link.addEventListener('click', arCloseMobileMenu);
	});

	// Move login modal out of sticky header → body
	// (sticky header creates a stacking context that traps z-index)
	var loginModal = document.getElementById('ja-login-form');
	if (loginModal) document.body.appendChild(loginModal);

	// Language switcher dropdown — manual toggle fallback
	var langContainer = document.querySelector('.ar-lang .mod-languages');
	var langToggle    = document.querySelector('.ar-lang .dropdown-toggle');
	if (langToggle && langContainer) {
		langToggle.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			langContainer.classList.toggle('open');
		});
		document.addEventListener('click', function(e) {
			if (!langContainer.contains(e.target)) langContainer.classList.remove('open');
		});
	}
});
</script>