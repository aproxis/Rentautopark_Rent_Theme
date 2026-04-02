<?php
/**
 * Header override — AutoRent Figma Design
 * Logo LEFT | Nav center (desktop) | Icons RIGHT (mobile)
 * v3 — mobile URLs from real menu items + JS language sync from desktop switcher
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

// ── Logo params ───────────────────────────────────────────────────
$sitename  = $this->params->get('sitename');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0))
	? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true))
	: false;
if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}

// ── Current language & menu object ───────────────────────────────
$_lang = JFactory::getLanguage()->getTag();   // e.g. "ro-RO"
$_base = JURI::base(true);
$_menu = JFactory::getApplication()->getMenu();

// ── Resolve mobile icon URLs from real menu items ─────────────────
// We iterate all component-type menu items so we pick up correct
// SEF routes for the active language — no raw component URLs.
$_contactUrl = '';
$_carsUrl    = '';
$_ordersUrl  = '';
$_loginUrl   = '';

foreach ((array) $_menu->getItems('type', 'component') as $_mi) {
	$_link   = isset($_mi->link) ? $_mi->link : '';
	$_mlang  = isset($_mi->language) ? $_mi->language : '*';
	$_langOk = ($_mlang === $_lang || $_mlang === '*' || $_mlang === '');

	if (!$_contactUrl
		&& strpos($_link, 'option=com_contact') !== false
		&& $_langOk) {
		$_contactUrl = JRoute::_('index.php?Itemid=' . $_mi->id, false);
	}
	if (!$_carsUrl
		&& strpos($_link, 'option=com_vikrentcar') !== false
		&& strpos($_link, 'view=carslist') !== false
		&& $_langOk) {
		$_carsUrl = JRoute::_('index.php?Itemid=' . $_mi->id, false);
	}
	// "Orders" page = Joomla user profile (com_users, view=profile, Itemid=458)
	// VikRentCar renders the orders list inside the profile view
	if (!$_ordersUrl
		&& strpos($_link, 'option=com_users') !== false
		&& strpos($_link, 'view=profile') !== false
		&& $_langOk) {
		$_ordersUrl = JRoute::_('index.php?Itemid=' . $_mi->id, false);
	}
	if (!$_loginUrl
		&& strpos($_link, 'option=com_users') !== false
		&& strpos($_link, 'view=login') !== false
		&& $_langOk) {
		$_loginUrl = JRoute::_('index.php?Itemid=' . $_mi->id, false);
	}
}

// Fallbacks if menu items not found
if (!$_contactUrl) $_contactUrl = JRoute::_('index.php?option=com_contact', false);
if (!$_carsUrl)    $_carsUrl    = JRoute::_('index.php?option=com_vikrentcar&view=carslist', false);
if (!$_ordersUrl)  $_ordersUrl  = JRoute::_('index.php?option=com_users&view=profile', false);
if (!$_loginUrl)   $_loginUrl   = JRoute::_('index.php?option=com_users&view=login', false);

// ── Account URL: guest → login, logged-in → orders ───────────────
$_jUser      = JFactory::getUser();
$_accountUrl = $_jUser->guest ? $_loginUrl : $_ordersUrl;

// ── Language list for mobile globe dropdown ───────────────────────
// Only flag images and names here — correct current-page hrefs
// are injected by JS from the already-working desktop switcher.
$_mobileLangs = array();
try {
	foreach (JLanguageHelper::getLanguages() as $_l) {
		$_mobileLangs[] = array(
			'tag'    => $_l->lang_code,
			'sef'    => $_l->sef,
			'name'   => $_l->title,
			'active' => ($_l->lang_code === $_lang),
			'image'  => !empty($_l->image) ? $_l->image : '',
		);
	}
} catch (Exception $_e) { /* silently skip */ }
?>
<style>
/* =================================================================
   AutoRent Header v3
   Logo LEFT | Desktop nav CENTER | Auth+Lang RIGHT (desktop)
   Mobile: Logo LEFT | Icon row RIGHT (Contact / Cars / Lang / Account)
   ================================================================= */

#t3-topbar { display: none !important; }

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
#ar-header.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.08); }

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
.ar-logo a { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.ar-logo img { height: 40px; width: auto; object-fit: contain; }
.ar-logo .ar-logo-text { font-size: 1.25rem; font-weight: 800; color: #0a0a0a; white-space: nowrap; }
.ar-logo .ar-logo-text span { color: #FE5001; }
.ar-logo small { display: none; }

/* ── Desktop nav (center) ────────────────────────────────────────── */
.ar-nav-desktop { display: flex; align-items: center; gap: 4px; flex: 1; justify-content: center; }
.ar-nav-desktop ul { list-style: none; margin: 0; padding: 0; display: flex; align-items: center; gap: 4px; }
.ar-nav-desktop li { margin: 0; padding: 0; }
.ar-nav-desktop li a {
	display: flex; align-items: center; padding: 8px 16px;
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
.ar-nav-desktop .t3-megamenu .mega-dropdown-menu,
.ar-nav-desktop .dropdown-menu,
.ar-nav-desktop .caret,
.ar-nav-desktop .nav-child,
.ar-nav-desktop .mega-nav .mega-group,
.ar-nav-desktop .dropdown-submenu { display: none !important; }
.ar-nav-desktop .t3-megamenu .nav > li > a { padding: 8px 16px; }

/* ── Right group ─────────────────────────────────────────────────── */
.ar-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

/* ── Language switcher (desktop) ─────────────────────────────────── */
.ar-lang { position: relative; }
.ar-lang .mod-languages { position: relative; }
.ar-lang .dropdown-toggle {
	display: flex; align-items: center; gap: 6px; padding: 7px 12px;
	background: #f3f4f6; border: 1.5px solid #e5e7eb; border-radius: 8px;
	font-size: 13px; font-weight: 600; color: #374151;
	cursor: pointer; text-decoration: none;
	transition: border-color .2s, background .2s; white-space: nowrap;
}
.ar-lang .dropdown-toggle:hover { border-color: #d1d5db; background: #e5e7eb; }
.ar-lang .dropdown-toggle img { width: 18px; height: 14px; object-fit: cover; border-radius: 2px; }
.ar-lang .dropdown-toggle .fa-caret-down { font-size: 10px; color: #9ca3af; margin-left: 2px; }
.ar-lang .dropdown-menu {
	position: absolute; top: calc(100% + 6px); right: 0; left: auto;
	min-width: 140px; background: #fff; border: 1.5px solid #e5e7eb;
	border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.1);
	padding: 6px; margin: 0; z-index: 1050; list-style: none; display: none;
}
.ar-lang .mod-languages.open .dropdown-menu,
.ar-lang .dropdown-menu.show { display: block; }
.ar-lang .dropdown-menu li { margin: 0; padding: 0; }
.ar-lang .dropdown-menu li a {
	display: flex; align-items: center; gap: 8px; padding: 8px 12px;
	font-size: 13px; font-weight: 500; color: #374151;
	text-decoration: none; border-radius: 6px; transition: background .15s, color .15s;
}
.ar-lang .dropdown-menu li a:hover { background: #f3f4f6; color: #0a0a0a; }
.ar-lang .dropdown-menu li.lang-active a { color: #FE5001; font-weight: 600; background: rgba(254,80,1,.06); }
.ar-lang .dropdown-menu li a img { width: 20px; height: 15px; object-fit: cover; border-radius: 2px; }
.ar-lang .dropdown-menu li a span { flex: 1; }

/* ── Mobile icon buttons ─────────────────────────────────────────── */
.ar-mobile-actions { display: none; align-items: center; gap: 2px; }

.ar-mobile-icon-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 44px; height: 44px; padding: 0;
	color: #374151; background: transparent;
	border: none; border-radius: 8px;
	text-decoration: none; cursor: pointer; flex-shrink: 0;
	-webkit-tap-highlight-color: transparent;
	transition: color .2s, background .2s;
}
.ar-mobile-icon-btn:hover,
.ar-mobile-icon-btn:focus-visible {
	color: #FE5001; background: rgba(254,80,1,.08);
	text-decoration: none; outline: none;
}
.ar-mobile-icon-btn:active { opacity: .75; }

/* SVG — always visible, always stroked, never clipped */
.ar-mobile-icon-btn svg {
	width: 22px; height: 22px;
	stroke: currentColor; stroke-width: 2;
	fill: none; stroke-linecap: round; stroke-linejoin: round;
	display: block; overflow: visible; flex-shrink: 0;
	pointer-events: none;
}

/* ── Mobile language dropdown ────────────────────────────────────── */
.ar-mobile-lang-wrap { position: relative; }

.ar-mobile-lang-dropdown {
	display: none;
	position: absolute;
	top: calc(100% + 6px); right: 0;
	min-width: 150px; background: #fff;
	border: 1.5px solid #e5e7eb; border-radius: 10px;
	box-shadow: 0 8px 24px rgba(0,0,0,.12);
	padding: 6px; z-index: 2000; list-style: none; margin: 0;
}
.ar-mobile-lang-wrap.open .ar-mobile-lang-dropdown { display: block; }

.ar-mobile-lang-dropdown li { margin: 0; padding: 0; }
.ar-mobile-lang-dropdown li a {
	display: flex; align-items: center; gap: 8px; padding: 8px 12px;
	font-size: 13px; font-weight: 500; color: #374151;
	text-decoration: none; border-radius: 6px;
	transition: background .15s; white-space: nowrap;
}
.ar-mobile-lang-dropdown li a:hover { background: #f3f4f6; }
.ar-mobile-lang-dropdown li.lang-active a { color: #FE5001; font-weight: 700; background: rgba(254,80,1,.06); }
.ar-mobile-lang-dropdown li a img { width: 20px; height: 15px; object-fit: cover; border-radius: 2px; flex-shrink: 0; }

/* ── Mobile hamburger ────────────────────────────────────────────── */
.ar-mobile-toggle {
	display: none; align-items: center; justify-content: center;
	width: 40px; height: 40px; background: none; border: none;
	cursor: pointer; color: #374151; border-radius: 8px;
	transition: background .2s, color .2s; -webkit-tap-highlight-color: transparent;
}
.ar-mobile-toggle:hover { background: #f3f4f6; }

/* ── Mobile nav panel ────────────────────────────────────────────── */
.ar-mobile-menu {
	display: none; position: absolute;
	top: 100%; left: 0; right: 0; background: #fff;
	border-bottom: 2px solid #e5e7eb; box-shadow: 0 8px 30px rgba(0,0,0,.1);
	padding: 16px; z-index: 999;
}
.ar-mobile-menu.open { display: block; }
.ar-mobile-menu ul { list-style: none; margin: 0; padding: 0; }
.ar-mobile-menu li { margin: 0; }
.ar-mobile-menu li a {
	display: block; padding: 12px 16px; font-size: 15px; font-weight: 500;
	color: #374151; text-decoration: none; border-radius: 8px;
	transition: color .15s, background .15s;
}
.ar-mobile-menu li a:hover { background: #f3f4f6; color: #0a0a0a; }
.ar-mobile-menu li.active > a,
.ar-mobile-menu li.current > a { color: #FE5001; font-weight: 600; }

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 991px) {
	.ar-nav-desktop  { display: none; }
	.ar-auth         { display: none; }
	.ar-lang         { display: none; }
	.ar-mobile-actions { display: flex; }
	/* .ar-mobile-toggle  { display: flex; } */ /* hamburger disabled */
	.ar-header-inner   { height: 60px; gap: 8px; padding: 0 12px; }
	body { padding-bottom: 0 !important; }
}
/* Logout icon — red tint on hover */
.ar-mob-logout-icon:hover,
.ar-mob-logout-icon:focus-visible {
	color: #ef4444;
	background: rgba(239,68,68,.08);
}

@media (max-width: 480px) {
	/* Shrink icons so 5 fit next to logo */
	.ar-mobile-icon-btn { width: 36px; height: 36px; }
	.ar-mobile-icon-btn svg { width: 19px; height: 19px; }
	.ar-mobile-actions { gap: 0; }
	.ar-header-inner { padding: 0 8px; gap: 4px; }
	/* Scale logo down */
	.ar-logo img { height: 26px; }
	.ar-logo .ar-logo-text { font-size: 0.95rem; }
}

/* ── Mobile account dropdown (logged-in) ─────────────────────────── */
.ar-mob-acct-wrap { position: relative; }

.ar-mob-acct-dropdown {
	display: none;
	position: absolute;
	top: calc(100% + 6px); right: 0;
	min-width: 160px; background: #fff;
	border: 1.5px solid #e5e7eb; border-radius: 10px;
	box-shadow: 0 8px 24px rgba(0,0,0,.12);
	padding: 6px; z-index: 2000; list-style: none; margin: 0;
}
.ar-mob-acct-wrap.open .ar-mob-acct-dropdown {
	display: block;
	animation: arDropIn .15s ease;
}
@keyframes arDropIn {
	from { opacity: 0; transform: translateY(-4px); }
	to   { opacity: 1; transform: translateY(0); }
}

.ar-mob-acct-item {
	display: flex; align-items: center; gap: 8px;
	padding: 9px 12px; width: 100%;
	font-size: 13px; font-weight: 500; color: #374151;
	background: none; border: none; border-radius: 6px;
	text-decoration: none; cursor: pointer; text-align: left;
	white-space: nowrap; transition: background .15s;
	-webkit-tap-highlight-color: transparent;
}
.ar-mob-acct-item:hover { background: #f3f4f6; color: #0a0a0a; }

.ar-mob-logout-btn { color: #ef4444; }
.ar-mob-logout-btn:hover { background: #fee2e2; color: #dc2626; }

.ar-mob-acct-item svg {
	width: 15px; height: 15px; flex-shrink: 0;
	stroke: currentColor; stroke-width: 2;
	fill: none; stroke-linecap: round; stroke-linejoin: round;
}

</style>

<!-- HEADER -->
<header id="ar-header">
	<div class="ar-header-inner">

		<!-- LOGO — always left -->
		<div class="ar-logo">
			<a href="<?php echo $_base; ?>" title="<?php echo strip_tags($sitename); ?>">
				<?php if ($logotype == 'image' && !empty($logoimage)): ?>
					<img class="logo-img" src="<?php echo $_base . '/' . $logoimage; ?>" alt="<?php echo strip_tags($sitename); ?>" />
				<?php endif; ?>
				<?php if ($logoimgsm): ?>
					<img class="logo-img-sm" src="<?php echo $_base . '/' . $logoimgsm; ?>" alt="<?php echo strip_tags($sitename); ?>" />
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

			<!-- Auth — desktop only (hidden on mobile via CSS) -->
			<?php if ($this->countModules('loginload')): ?>
			<div class="ar-auth">
				<jdoc:include type="modules" name="<?php $this->_p('loginload'); ?>" style="raw" />
			</div>
			<?php endif; ?>

			<!-- Language switcher — desktop only (hidden on mobile via CSS)
			     Its <a href="..."> links are used by JS to populate the mobile globe dropdown -->
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

			<!-- ╔══════════════════════════════════════════════════════╗
			     ║  MOBILE ICON ROW — 4 icons, right-aligned in header  ║
			     ╚══════════════════════════════════════════════════════╝ -->
			<div class="ar-mobile-actions">

				<!-- Contact — URL from real menu item for current language -->
				<a href="<?php echo htmlspecialchars($_contactUrl); ?>"
				   class="ar-mobile-icon-btn"
				   aria-label="<?php echo htmlspecialchars(JText::_('TPL_CONTACT')); ?>">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" >
						<path d="M19 1.5H5a2 2 0 00-2 2v14a2 2 0 002 2h4l3 3 3-3h4c1.1 0 2-.9 2-2v-14c0-1.1-.9-2-2-2zm-6 16h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 11.4 13 12 13 13.5h-2V13c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"></path>
					</svg>
				</a>

				<!-- Cars — URL from real menu item for current language -->
				<a href="<?php echo htmlspecialchars($_carsUrl); ?>"
				   class="ar-mobile-icon-btn"
				   aria-label="<?php echo htmlspecialchars(JText::_('TPL_CARS')); ?>">
					<svg viewBox="0 2 22 22" xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px;fill: #fe5001; stroke: none;">
						<path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"></path>
					</svg>
				</a>

				<!-- Language globe
				     Dropdown items are built from PHP (flags + names).
				     Their HREF values are replaced by JS after page load
				     by reading them from the hidden (CSS) desktop switcher,
				     which already computes correct current-page associations. -->
				<?php if (!empty($_mobileLangs)): ?>
				<div class="ar-mobile-lang-wrap">
					<button type="button"
					        class="ar-mobile-icon-btn ar-mobile-lang-toggle"
					        aria-label="<?php echo htmlspecialchars(JText::_('TPL_LANGUAGE')); ?>"
					        aria-expanded="false"
					        aria-haspopup="listbox">
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<circle cx="12" cy="12" r="10"/>
							<line x1="2" y1="12" x2="22" y2="12"/>
							<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
						</svg>
					</button>
					<ul class="ar-mobile-lang-dropdown" role="listbox">
						<?php foreach ($_mobileLangs as $_ml): ?>
						<li class="<?php echo $_ml['active'] ? 'lang-active' : ''; ?>"
						    data-lang-sef="<?php echo htmlspecialchars($_ml['sef']); ?>">
							<a href="#"
							   hreflang="<?php echo htmlspecialchars($_ml['tag']); ?>"
							   data-lang-name="<?php echo htmlspecialchars($_ml['name']); ?>">
								<?php if (!empty($_ml['image'])): ?>
								<img src="<?php echo $_base; ?>/media/mod_languages/images/<?php echo htmlspecialchars($_ml['image']); ?>.gif"
								     alt="<?php echo htmlspecialchars($_ml['name']); ?>"
								     width="20" height="15" loading="lazy" />
								<?php endif; ?>
								<span><?php echo htmlspecialchars($_ml['name']); ?></span>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>

				<!-- Account:
				     guest      → openAuthModal('login')
				     logged-in  → direct link to orders/profile -->
				<?php if ($_jUser->guest): ?>
				<button type="button"
				        class="ar-mobile-icon-btn"
				        onclick="openAuthModal('login')"
				        aria-label="<?php echo htmlspecialchars(JText::_('TPL_ACCOUNT')); ?>">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
						<circle cx="12" cy="7" r="4"/>
					</svg>
				</button>
				<?php else: ?>
				<!-- Account → direct link -->
				<a href="<?php echo htmlspecialchars($_ordersUrl); ?>"
				   class="ar-mobile-icon-btn"
				   aria-label="<?php echo htmlspecialchars(JText::_('TPL_ACCOUNT')); ?>">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
						<circle cx="12" cy="7" r="4" style="fill: #fe5001;"/>
					</svg>
				</a>
				<!-- Logout — separate icon to the right of account -->
				<button type="button"
				        class="ar-mobile-icon-btn ar-mob-logout-icon"
				        onclick="arMobileLogout()"
				        aria-label="<?php echo htmlspecialchars(JText::_('JLOGOUT')); ?>"
				        title="<?php echo htmlspecialchars(JText::_('JLOGOUT')); ?>">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
						<polyline points="16 17 21 12 16 7"/>
						<line x1="21" y1="12" x2="9" y2="12"/>
					</svg>
				</button>
				<?php endif; ?>

			</div>
			<!-- /MOBILE ICON ROW -->

			<?php /* HAMBURGER TEMPORARILY DISABLED
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
			*/ ?>

		</div>
		<!-- /RIGHT GROUP -->

	</div>

	<?php /* MOBILE NAV PANEL TEMPORARILY DISABLED
	<div class="ar-mobile-menu" id="ar-mobile-menu" role="navigation">
		<jdoc:include type="... mainmenu ..." name="..." />
	</div>
	*/ ?>

</header>
<!-- //HEADER -->

<script>
(function () {
	// Sticky shadow on scroll
	var h = document.getElementById('ar-header');
	if (h) window.addEventListener('scroll', function () {
		h.classList.toggle('scrolled', window.scrollY > 10);
	}, { passive: true });
}());

/* ── Mobile nav toggle ───────────────────────────────────────────── */
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

/* ── Mobile logout — reuses CSRF token from desktop module form ── */
function arMobileLogout() {
	// The desktop .ar-account-dropdown form already has the Joomla CSRF token
	var form = document.querySelector('.ar-account-dropdown form');
	if (form) { form.submit(); return; }
	// Fallback: find any logout form on the page
	var allForms = document.querySelectorAll('form');
	for (var _i = 0; _i < allForms.length; _i++) {
		var _task = allForms[_i].querySelector('input[name="task"]');
		if (_task && _task.value === 'user.logout') { allForms[_i].submit(); return; }
	}
}

function arToggleMobile() {
	var menu   = document.getElementById('ar-mobile-menu');
	var btn    = document.getElementById('ar-mobile-toggle-btn');
	var iconM  = document.getElementById('ar-menu-icon');
	var iconC  = document.getElementById('ar-close-icon');
	if (!menu) return;
	var open = menu.classList.contains('open');
	menu.classList.toggle('open', !open);
	if (btn)   btn.setAttribute('aria-expanded', String(!open));
	if (iconM) iconM.style.display = open ? '' : 'none';
	if (iconC) iconC.style.display = open ? 'none' : '';
}

document.addEventListener('DOMContentLoaded', function () {

	/* Close nav on link click */
	document.querySelectorAll('.ar-mobile-menu a').forEach(function (l) {
		l.addEventListener('click', arCloseMobileMenu);
	});

	/* Move login modal out of sticky header (stacking-context trap) */
	var modal = document.getElementById('ja-login-form');
	if (modal) document.body.appendChild(modal);

	/* ── Desktop language dropdown ─────────────────────────────── */
	var lc = document.querySelector('.ar-lang .mod-languages');
	var lt = document.querySelector('.ar-lang .dropdown-toggle');
	if (lt && lc) {
		lt.addEventListener('click', function (e) {
			e.preventDefault(); e.stopPropagation();
			lc.classList.toggle('open');
		});
		document.addEventListener('click', function (e) {
			if (!lc.contains(e.target)) lc.classList.remove('open');
		});
	}

	/* ── Sync mobile language URLs from desktop switcher ─────────
	   The desktop .ar-lang module computes correct current-page
	   language association links via mod_languages + Joomla assoc API.
	   We read those links and copy them to the mobile globe dropdown.
	   Matching is done by language name (img alt or span text).       */
	(function syncLangLinks() {
		var desktopLinks = document.querySelectorAll('.ar-lang .dropdown-menu li a');
		var mobileLinks  = document.querySelectorAll('.ar-mobile-lang-dropdown li a');
		if (!desktopLinks.length || !mobileLinks.length) return;

		// Build map: normalised language name → href from desktop
		var map = {};
		desktopLinks.forEach(function (a) {
			var href = a.getAttribute('href');
			if (!href || href === '#') return;
			var img  = a.querySelector('img');
			var span = a.querySelector('span');
			var name = img
				? img.getAttribute('alt')
				: (span ? span.textContent : a.textContent);
			if (name) map[name.trim().toLowerCase()] = href;
		});

		// Apply matched URLs to mobile links
		mobileLinks.forEach(function (a) {
			var name = a.getAttribute('data-lang-name') || '';
			var key  = name.trim().toLowerCase();
			if (key && map[key]) {
				a.setAttribute('href', map[key]);
			}
		});
	}());


	/* ── Mobile account dropdown (logged-in) ───────────────────── */
	document.querySelectorAll('.ar-mob-acct-toggle').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault(); e.stopPropagation();
			var wrap   = btn.closest('.ar-mob-acct-wrap');
			if (!wrap) return;
			var isOpen = wrap.classList.contains('open');
			// Close all mobile dropdowns first
			document.querySelectorAll('.ar-mob-acct-wrap.open, .ar-mobile-lang-wrap.open').forEach(function (w) {
				w.classList.remove('open');
			});
			if (!isOpen) {
				wrap.classList.add('open');
				btn.setAttribute('aria-expanded', 'true');
			} else {
				btn.setAttribute('aria-expanded', 'false');
			}
		});
	});

	// Close account dropdown on outside click
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.ar-mob-acct-wrap')) {
			document.querySelectorAll('.ar-mob-acct-wrap.open').forEach(function (w) {
				w.classList.remove('open');
				var b = w.querySelector('.ar-mob-acct-toggle');
				if (b) b.setAttribute('aria-expanded', 'false');
			});
		}
	});

	/* ── Mobile globe toggle ───────────────────────────────────── */
	document.querySelectorAll('.ar-mobile-lang-toggle').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault(); e.stopPropagation();
			var wrap   = btn.closest('.ar-mobile-lang-wrap');
			if (!wrap) return;
			var isOpen = wrap.classList.contains('open');
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

	/* Close globe dropdown on outside click or Escape */
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.ar-mobile-lang-wrap')) {
			document.querySelectorAll('.ar-mobile-lang-wrap.open').forEach(function (w) {
				w.classList.remove('open');
				var b = w.querySelector('.ar-mobile-lang-toggle');
				if (b) b.setAttribute('aria-expanded', 'false');
			});
		}
	});
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') return;
		document.querySelectorAll('.ar-mobile-lang-wrap.open').forEach(function (w) {
			w.classList.remove('open');
			var b = w.querySelector('.ar-mobile-lang-toggle');
			if (b) { b.setAttribute('aria-expanded', 'false'); b.focus(); }
		});
	});

});
</script>