<?php
/**
 * Header override — AutoRent Figma Design
 * Logo left | Nav center (desktop) | Icons right (mobile) | Fixed bugs: globe + account
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

// Logo params from T3
$sitename  = $this->params->get('sitename');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true)) : false;

if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}

// Account URL: guest → login page, logged in → orders page
$_jUser      = JFactory::getUser();
$_base       = JURI::base(true);
$_accountUrl = $_jUser->guest
	? $_base . '/index.php?option=com_users&view=login'
	: $_base . '/index.php?option=com_vikrentcar&view=orderslist';

// Build language list for mobile globe dropdown
$_mobileLangs    = array();
$_currentLangTag = JFactory::getLanguage()->getTag();
try {
	$_allLangs = JLanguageHelper::getLanguages();
	foreach ($_allLangs as $_l) {
		$_mobileLangs[] = array(
			'tag'    => $_l->lang_code,
			'sef'    => $_l->sef,
			'name'   => $_l->title,
			'active' => ($_l->lang_code === $_currentLangTag),
			'image'  => !empty($_l->image) ? $_l->image : '',
		);
	}
} catch (Exception $_e) {
	// silently skip if helper not available
}
?>
<style>
/* =================================================================
   AutoRent Header v2 — mobile icon fix
   Logo LEFT | Desktop nav CENTER | Auth+Lang RIGHT (desktop)
   Mobile: Logo LEFT | Icon row RIGHT (Contact/Cars/Language/Account)
   ================================================================= */

/* Hide old topbar */
#t3-topbar { display: none !important; }

/* Header shell */
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

/* Inner layout */
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

/* ── Logo ────────────────────────────────────────────────────────── */
.ar-logo { flex-shrink: 0; }
.ar-logo a {
	display: flex;
	align-items: center;
	gap: 10px;
	text-decoration: none;
}
.ar-logo img { height: 40px; width: auto; object-fit: contain; }
.ar-logo .ar-logo-text {
	font-size: 1.25rem;
	font-weight: 800;
	color: #0a0a0a;
	white-space: nowrap;
}
.ar-logo .ar-logo-text span { color: #FE5001; }
.ar-logo small { display: none; }

/* ── Desktop nav (center) ────────────────────────────────────────── */
.ar-nav-desktop {
	display: flex;
	align-items: center;
	gap: 4px;
	flex: 1;
	justify-content: center;
}
.ar-nav-desktop ul {
	list-style: none; margin: 0; padding: 0;
	display: flex; align-items: center; gap: 4px;
}
.ar-nav-desktop li { margin: 0; padding: 0; }
.ar-nav-desktop li a {
	display: flex; align-items: center;
	padding: 8px 16px;
	font-size: 14px; font-weight: 500; color: #6b7280;
	text-decoration: none; border-radius: 8px;
	transition: color .2s, background .2s; white-space: nowrap;
}
.ar-nav-desktop li a:hover { color: #0a0a0a; background: #f3f4f6; }
.ar-nav-desktop li.active > a,
.ar-nav-desktop li.current > a,
.ar-nav-desktop li.alias-parent-active > a {
	color: #FE5001; background: rgba(254,80,1,.06); font-weight: 600;
}
/* hide megamenu sub-panels on desktop */
.ar-nav-desktop .t3-megamenu .mega-dropdown-menu,
.ar-nav-desktop .dropdown-menu,
.ar-nav-desktop .caret,
.ar-nav-desktop .nav-child,
.ar-nav-desktop .mega-nav .mega-group,
.ar-nav-desktop .dropdown-submenu { display: none !important; }
.ar-nav-desktop .t3-megamenu .nav > li > a { padding: 8px 16px; }

/* ── Right group ──────────────────────────────────────────────────── */
.ar-right {
	display: flex;
	align-items: center;
	gap: 10px;
	flex-shrink: 0;
}

/* ── Language switcher (desktop only) ─────────────────────────────── */
.ar-lang { position: relative; }
.ar-lang .mod-languages { position: relative; }
.ar-lang .dropdown-toggle {
	display: flex; align-items: center; gap: 6px;
	padding: 7px 12px;
	background: #f3f4f6; border: 1.5px solid #e5e7eb;
	border-radius: 8px; font-size: 13px; font-weight: 600;
	color: #374151; cursor: pointer; text-decoration: none;
	transition: border-color .2s, background .2s; white-space: nowrap;
}
.ar-lang .dropdown-toggle:hover { border-color: #d1d5db; background: #e5e7eb; }
.ar-lang .dropdown-toggle img { width: 18px; height: 14px; object-fit: cover; border-radius: 2px; }
.ar-lang .dropdown-toggle .fa-caret-down { font-size: 10px; color: #9ca3af; margin-left: 2px; }
.ar-lang .dropdown-menu {
	position: absolute; top: calc(100% + 6px); right: 0; left: auto;
	min-width: 140px; background: #fff;
	border: 1.5px solid #e5e7eb; border-radius: 10px;
	box-shadow: 0 8px 24px rgba(0,0,0,.1); padding: 6px; margin: 0;
	z-index: 1050; list-style: none; display: none;
}
.ar-lang .mod-languages.open .dropdown-menu,
.ar-lang .dropdown-menu.show { display: block; }
.ar-lang .dropdown-menu li { margin: 0; padding: 0; }
.ar-lang .dropdown-menu li a {
	display: flex; align-items: center; gap: 8px;
	padding: 8px 12px; font-size: 13px; font-weight: 500;
	color: #374151; text-decoration: none; border-radius: 6px;
	transition: background .15s, color .15s;
}
.ar-lang .dropdown-menu li a:hover { background: #f3f4f6; color: #0a0a0a; }
.ar-lang .dropdown-menu li.lang-active a {
	color: #FE5001; font-weight: 600; background: rgba(254,80,1,.06);
}
.ar-lang .dropdown-menu li a img { width: 20px; height: 15px; object-fit: cover; border-radius: 2px; }
.ar-lang .dropdown-menu li a span { flex: 1; }

/* ── Mobile icon buttons (logo left, icons right in header) ───────── */
.ar-mobile-actions {
	display: none;        /* hidden on desktop */
	align-items: center;
	gap: 2px;
}

.ar-mobile-icon-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 44px;
	height: 44px;
	padding: 0;
	color: #374151;
	background: transparent;
	border: none;
	border-radius: 8px;
	text-decoration: none;
	cursor: pointer;
	flex-shrink: 0;
	-webkit-tap-highlight-color: transparent;
	transition: color .2s, background .2s;
}
.ar-mobile-icon-btn:hover,
.ar-mobile-icon-btn:focus-visible {
	color: #FE5001;
	background: rgba(254,80,1,.08);
	text-decoration: none;
	outline: none;
}
.ar-mobile-icon-btn:active { opacity: .75; }

/* SVG inside icon buttons — always visible, always stroked */
.ar-mobile-icon-btn svg {
	width: 22px;
	height: 22px;
	stroke: currentColor;
	stroke-width: 2;
	fill: none;
	stroke-linecap: round;
	stroke-linejoin: round;
	display: block;            /* prevents inline baseline gap */
	overflow: visible;
	flex-shrink: 0;
	pointer-events: none;      /* click goes to the button, not the SVG */
}

/* ── Mobile language dropdown ─────────────────────────────────────── */
.ar-mobile-lang-wrap {
	position: relative;
}
.ar-mobile-lang-dropdown {
	display: none;
	position: absolute;
	top: calc(100% + 6px);
	right: 0;
	min-width: 150px;
	background: #fff;
	border: 1.5px solid #e5e7eb;
	border-radius: 10px;
	box-shadow: 0 8px 24px rgba(0,0,0,.12);
	padding: 6px;
	z-index: 2000;
	list-style: none;
	margin: 0;
}
.ar-mobile-lang-wrap.open .ar-mobile-lang-dropdown { display: block; }

.ar-mobile-lang-dropdown li { margin: 0; padding: 0; }
.ar-mobile-lang-dropdown li a {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 8px 12px;
	font-size: 13px;
	font-weight: 500;
	color: #374151;
	text-decoration: none;
	border-radius: 6px;
	transition: background .15s;
	white-space: nowrap;
}
.ar-mobile-lang-dropdown li a:hover { background: #f3f4f6; }
.ar-mobile-lang-dropdown li.lang-active a {
	color: #FE5001;
	font-weight: 700;
	background: rgba(254,80,1,.06);
}
.ar-mobile-lang-dropdown li a img {
	width: 20px;
	height: 15px;
	object-fit: cover;
	border-radius: 2px;
	flex-shrink: 0;
}

/* ── Mobile hamburger ─────────────────────────────────────────────── */
.ar-mobile-toggle {
	display: none;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	background: none;
	border: none;
	cursor: pointer;
	color: #374151;
	border-radius: 8px;
	transition: background .2s, color .2s;
	-webkit-tap-highlight-color: transparent;
}
.ar-mobile-toggle:hover { background: #f3f4f6; }

/* ── Mobile menu panel ─────────────────────────────────────────────── */
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

/* ── Responsive breakpoints ────────────────────────────────────────── */
@media (max-width: 991px) {
	/* Hide desktop-only elements */
	.ar-nav-desktop { display: none; }
	.ar-auth        { display: none; }
	.ar-lang        { display: none; }

	/* Show mobile elements */
	.ar-mobile-actions { display: flex; }
	.ar-mobile-toggle  { display: flex; }

	/* Tighten header */
	.ar-header-inner { height: 60px; gap: 8px; padding: 0 12px; }

	/* Remove old bottom-bar body padding (from previous version) */
	body { padding-bottom: 0 !important; }
}

@media (max-width: 480px) {
	.ar-mobile-icon-btn { width: 40px; height: 40px; }
	.ar-mobile-icon-btn svg { width: 20px; height: 20px; }
	.ar-header-inner { padding: 0 8px; gap: 4px; }
}
</style>

<!-- HEADER -->
<header id="ar-header">
	<div class="ar-header-inner">

		<!-- LOGO — always left -->
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

		<!-- DESKTOP NAVIGATION (center) -->
		<nav class="ar-nav-desktop">
			<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu'); ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu'); ?>" />
		</nav>

		<!-- RIGHT GROUP -->
		<div class="ar-right">

			<!-- Auth (desktop only — hidden on mobile via CSS) -->
			<?php if ($this->countModules('loginload')): ?>
			<div class="ar-auth">
				<jdoc:include type="modules" name="<?php $this->_p('loginload'); ?>" style="raw" />
			</div>
			<?php endif; ?>

			<!-- Language switcher (desktop only — hidden on mobile via CSS) -->
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
			<?php endif; endif; ?>

			<!-- ╔══════════════════════════════════════════════╗
			     ║  MOBILE ICON ROW — logo left, icons right   ║
			     ╚══════════════════════════════════════════════╝ -->
			<div class="ar-mobile-actions">

				<!-- Contact -->
				<a href="<?php echo $_base; ?>/index.php?option=com_contact"
				   class="ar-mobile-icon-btn"
				   aria-label="<?php echo htmlspecialchars(JText::_('TPL_CONTACT')); ?>"
				   title="<?php echo htmlspecialchars(JText::_('TPL_CONTACT')); ?>">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.53 2 2 0 0 1 3.6 1.32h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
					</svg>
				</a>

				<!-- Cars -->
				<a href="<?php echo $_base; ?>/index.php?option=com_vikrentcar&view=carslist"
				   class="ar-mobile-icon-btn"
				   aria-label="<?php echo htmlspecialchars(JText::_('TPL_CARS')); ?>"
				   title="<?php echo htmlspecialchars(JText::_('TPL_CARS')); ?>">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v5"/>
						<circle cx="6.5" cy="17.5" r="2.5"/>
						<circle cx="16.5" cy="17.5" r="2.5"/>
						<polyline points="14 3 14 8 19 8"/>
						<line x1="3" y1="11" x2="19" y2="11"/>
					</svg>
				</a>

				<!-- Language globe — opens dropdown -->
				<?php if (!empty($_mobileLangs)): ?>
				<div class="ar-mobile-lang-wrap">
					<button type="button"
					        class="ar-mobile-icon-btn ar-mobile-lang-toggle"
					        aria-label="<?php echo htmlspecialchars(JText::_('TPL_LANGUAGE')); ?>"
					        title="<?php echo htmlspecialchars(JText::_('TPL_LANGUAGE')); ?>"
					        aria-expanded="false"
					        aria-haspopup="listbox">
						<!-- Globe SVG — always rendered, always visible -->
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<circle cx="12" cy="12" r="10"/>
							<line x1="2" y1="12" x2="22" y2="12"/>
							<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
						</svg>
					</button>
					<ul class="ar-mobile-lang-dropdown" role="listbox" aria-label="Select language">
						<?php foreach ($_mobileLangs as $_ml): ?>
						<li class="<?php echo $_ml['active'] ? 'lang-active' : ''; ?>" role="option" aria-selected="<?php echo $_ml['active'] ? 'true' : 'false'; ?>">
							<a href="<?php echo $_base; ?>/index.php?lang=<?php echo $_ml['sef']; ?>"
							   hreflang="<?php echo htmlspecialchars($_ml['tag']); ?>">
								<?php if (!empty($_ml['image'])): ?>
								<img src="<?php echo JURI::base(true); ?>/media/mod_languages/images/<?php echo htmlspecialchars($_ml['image']); ?>.gif"
								     alt="<?php echo htmlspecialchars($_ml['name']); ?>"
								     width="20" height="15"
								     loading="lazy" />
								<?php endif; ?>
								<span><?php echo htmlspecialchars($_ml['name']); ?></span>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>

				<!-- Account — direct link (guest → login, logged-in → orders) -->
				<a href="<?php echo htmlspecialchars($_accountUrl); ?>"
				   class="ar-mobile-icon-btn"
				   aria-label="<?php echo htmlspecialchars(JText::_('TPL_ACCOUNT')); ?>"
				   title="<?php echo htmlspecialchars(JText::_('TPL_ACCOUNT')); ?>">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
						<circle cx="12" cy="7" r="4"/>
					</svg>
				</a>

			</div>
			<!-- /MOBILE ICON ROW -->

			<!-- Hamburger — toggles nav menu on mobile -->
			<button class="ar-mobile-toggle"
			        onclick="arToggleMobile()"
			        aria-label="Toggle navigation"
			        aria-expanded="false"
			        aria-controls="ar-mobile-menu"
			        id="ar-mobile-toggle-btn">
				<svg id="ar-menu-icon" width="22" height="22" viewBox="0 0 24 24"
				     fill="none" stroke="currentColor" stroke-width="2"
				     stroke-linecap="round" stroke-linejoin="round">
					<line x1="3" y1="6"  x2="21" y2="6"/>
					<line x1="3" y1="12" x2="21" y2="12"/>
					<line x1="3" y1="18" x2="21" y2="18"/>
				</svg>
				<svg id="ar-close-icon" width="22" height="22" viewBox="0 0 24 24"
				     fill="none" stroke="currentColor" stroke-width="2"
				     stroke-linecap="round" stroke-linejoin="round" style="display:none">
					<line x1="18" y1="6"  x2="6"  y2="18"/>
					<line x1="6"  y1="6"  x2="18" y2="18"/>
				</svg>
			</button>

		</div>
		<!-- /RIGHT GROUP -->

	</div>

	<!-- MOBILE NAV PANEL (slides down from header) -->
	<div class="ar-mobile-menu" id="ar-mobile-menu" role="navigation">
		<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu'); ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu'); ?>" />
	</div>

</header>
<!-- //HEADER -->

<script>
(function () {
	// Sticky shadow on scroll
	var header = document.getElementById('ar-header');
	if (header) {
		window.addEventListener('scroll', function () {
			header.classList.toggle('scrolled', window.scrollY > 10);
		}, { passive: true });
	}
}());

/* ── Mobile nav toggle ──────────────────────────────────────────── */
function arCloseMobileMenu() {
	var menu  = document.getElementById('ar-mobile-menu');
	var btn   = document.getElementById('ar-mobile-toggle-btn');
	var iconM = document.getElementById('ar-menu-icon');
	var iconC = document.getElementById('ar-close-icon');
	if (menu)  menu.classList.remove('open');
	if (btn)   btn.setAttribute('aria-expanded', 'false');
	if (iconM) iconM.style.display = '';
	if (iconC) iconC.style.display = 'none';
}

function arToggleMobile() {
	var menu   = document.getElementById('ar-mobile-menu');
	var btn    = document.getElementById('ar-mobile-toggle-btn');
	var iconM  = document.getElementById('ar-menu-icon');
	var iconC  = document.getElementById('ar-close-icon');
	if (!menu) return;
	var isOpen = menu.classList.contains('open');
	menu.classList.toggle('open', !isOpen);
	if (btn)   btn.setAttribute('aria-expanded', String(!isOpen));
	if (iconM) iconM.style.display = isOpen ? '' : 'none';
	if (iconC) iconC.style.display = isOpen ? 'none' : '';
}

document.addEventListener('DOMContentLoaded', function () {

	// Close nav on any menu link click
	document.querySelectorAll('.ar-mobile-menu a').forEach(function (link) {
		link.addEventListener('click', arCloseMobileMenu);
	});

	// Move login modal out of sticky header to avoid z-index stacking context trap
	var loginModal = document.getElementById('ja-login-form');
	if (loginModal) document.body.appendChild(loginModal);

	/* ── Desktop language dropdown ─────────────────────────────── */
	var langContainer = document.querySelector('.ar-lang .mod-languages');
	var langToggle    = document.querySelector('.ar-lang .dropdown-toggle');
	if (langToggle && langContainer) {
		langToggle.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			langContainer.classList.toggle('open');
		});
		document.addEventListener('click', function (e) {
			if (!langContainer.contains(e.target)) {
				langContainer.classList.remove('open');
			}
		});
	}

	/* ── Mobile language globe toggle ──────────────────────────── */
	document.querySelectorAll('.ar-mobile-lang-toggle').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var wrap   = btn.closest('.ar-mobile-lang-wrap');
			if (!wrap) return;
			var isOpen = wrap.classList.contains('open');

			// Close any other open language dropdowns
			document.querySelectorAll('.ar-mobile-lang-wrap.open').forEach(function (w) {
				w.classList.remove('open');
				var b = w.querySelector('.ar-mobile-lang-toggle');
				if (b) b.setAttribute('aria-expanded', 'false');
			});

			if (!isOpen) {
				wrap.classList.add('open');
				btn.setAttribute('aria-expanded', 'true');
			}
		});
	});

	// Close mobile language dropdown on outside click
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.ar-mobile-lang-wrap')) {
			document.querySelectorAll('.ar-mobile-lang-wrap.open').forEach(function (w) {
				w.classList.remove('open');
				var b = w.querySelector('.ar-mobile-lang-toggle');
				if (b) b.setAttribute('aria-expanded', 'false');
			});
		}
	});

	// Close mobile language dropdown on Escape key
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			document.querySelectorAll('.ar-mobile-lang-wrap.open').forEach(function (w) {
				w.classList.remove('open');
				var b = w.querySelector('.ar-mobile-lang-toggle');
				if (b) { b.setAttribute('aria-expanded', 'false'); b.focus(); }
			});
		}
	});

});
</script>