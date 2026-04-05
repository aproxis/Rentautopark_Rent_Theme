<?php
/**
 * Header override — AutoRent Figma Design
 * Logo LEFT | Nav center (desktop) | Icons RIGHT (mobile)
 * v3 — mobile URLs from real menu items + JS language sync from desktop switcher
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

// Load header assets
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root() . 'templates/rent/css/header.css');
$document->addScript(JURI::root() . 'templates/rent/js/header.js');

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
					<svg viewBox="2 2 22 22" xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px;fill: #fe5001; stroke: none;">
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
