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

/* ── Mobile hamburger ──────────────────────────────────────────── */
.ar-mobile-toggle {
	display: none;
	align-items: center;
	justify-content: center;
	width: 40px; height: 40px;
	border: none; background: none;
	cursor: pointer;
	border-radius: 8px;
	transition: background .2s;
	color: #374151; padding: 0;
}
.ar-mobile-toggle:hover { background: #f3f4f6; }
.ar-mobile-toggle svg { width: 22px; height: 22px; }

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

			<!-- Mobile Hamburger -->
			<button class="ar-mobile-toggle" onclick="arToggleMobile()" aria-label="Menu">
				<svg id="ar-menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
				<svg id="ar-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
			</button>
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