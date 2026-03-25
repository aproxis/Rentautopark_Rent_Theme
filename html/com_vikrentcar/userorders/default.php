<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/userorders/default.php
 * AutoRent Figma Design — v1
 */

defined('_JEXEC') OR die('Restricted Area');

// ── When included from the profile page, $rows/$islogged etc. are already
//    set as plain variables. Only fall back to $this->* when running as a
//    standalone VikRentCar view.
if (!isset($rows))        { $rows        = (isset($this) && isset($this->rows))        ? $this->rows        : []; }
if (!isset($searchorder)) { $searchorder = (isset($this) && isset($this->searchorder)) ? $this->searchorder : 0;  }
if (!isset($islogged))    { $islogged    = (isset($this) && isset($this->islogged))    ? $this->islogged    : 0;  }
if (!isset($pagelinks))   { $pagelinks   = (isset($this) && isset($this->pagelinks))   ? $this->pagelinks   : ''; }

// ── Date/time: use parent's $df/$nowtf if already set, else ask VikRentCar
if (!isset($df) || !isset($nowtf)) {
    $df    = 'd/m/Y';
    $nowtf = 'H:i';
    if (class_exists('VikRentCar')) {
        $nowdf = VikRentCar::getDateFormat();
        $nowtf = VikRentCar::getTimeFormat();
        if ($nowdf == '%d/%m/%Y') {
            $df = 'd/m/Y';
        } elseif ($nowdf == '%m/%d/%Y') {
            $df = 'm/d/Y';
        } else {
            $df = 'Y/m/d';
        }
    }
}

// ── VikRequest may not be loaded outside VikRentCar context
if (class_exists('VikRequest')) {
    $pitemid = VikRequest::getString('Itemid', '', 'request');
} else {
    $pitemid = Joomla\CMS\Factory::getApplication()->input->getString('Itemid', '');
}

// Add your custom CSS
$document = Joomla\CMS\Factory::getDocument();
$document->addStyleSheet(Joomla\CMS\Uri\Uri::root() . 'templates/rent/css/orders-styles.css');
?>

<div class="orders-page">
	<div class="orders-container">

		<!-- Search Section -->
		<?php if ($searchorder == 1): ?>
		<div class="orders-search-card">
			<div class="orders-search-header">
				<h3><?php echo JText::_('VRCCONFNUMBERLBL') ?: 'Căutare rezervare'; ?></h3>
				<p><?php echo JText::_('VRCCONFNUMBERLBL_SUBTITLE') ?: 'Introduceți numărul de confirmare sau codul PIN'; ?></p>
			</div>
			<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=userorders'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post" class="orders-search-form">
				<div class="orders-search-input-group">
					<div class="orders-input-wrapper">
						<svg class="orders-input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M21 21l-4.35-4.35"></path>
							<circle cx="10.5" cy="10.5" r="4.5"></circle>
						</svg>
						<input type="text" name="confirmnum" value="" size="25" id="vrcconfnum" class="orders-search-input" placeholder="<?php echo JText::_('VRCCONFNUMBERLBL') ?: 'Număr de confirmare sau cod PIN'; ?>">
					</div>
					<button type="submit" name="searchconfnum" class="orders-search-btn">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M21 21l-4.35-4.35"></path>
							<circle cx="10.5" cy="10.5" r="4.5"></circle>
						</svg>
						<span><?php echo JText::_('VRCCONFNUMBERSEARCHBTN') ?: 'Caută comandă'; ?></span>
					</button>
				</div>
				<input type="hidden" name="option" value="com_vikrentcar">
				<input type="hidden" name="view" value="userorders">
				<input type="hidden" name="searchorder" value="1">
			</form>
		</div>
		<?php endif; ?>

		<!-- Login Required Message -->
		<?php if ($islogged != 1): ?>
		<div class="orders-login-required">
			<div class="orders-login-card">
				<div class="orders-login-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
				</div>
				<div class="orders-login-content">
					<h3><?php echo JText::_('VRCRESERVATIONSLOGIN') ?: 'Autentificare necesară'; ?></h3>
					<p><?php echo JText::_('VRCRESERVATIONSLOGIN_SUBTITLE') ?: 'Pentru a vizualiza rezervările dvs., trebuie să vă autentificați.'; ?></p>
					<a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=loginregister'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" class="orders-login-btn">
						<?php echo JText::_('VRCRESERVATIONSLOGIN') ?: 'Autentificare'; ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Orders List -->
		<?php if ($islogged == 1): ?>
			<?php if (is_array($rows) && count($rows) > 0): ?>
			<div class="orders-list">
				<div class="orders-list-header">
					<h2><?php echo JText::_('VRCYOURRESERVATIONS') ?: 'Rezervările dvs.'; ?></h2>
					<span class="orders-count"><?php echo count($rows); ?> <?php echo JText::_('VRCRESERVATIONS') ?: 'rezervări'; ?></span>
				</div>

				<div class="orders-grid">
					<?php foreach ($rows as $ord): ?>
					<div class="order-card">
						<div class="order-card-header">
							<div class="order-date">
								<svg class="order-date-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
									<line x1="16" y1="2" x2="16" y2="6"></line>
									<line x1="8" y1="2" x2="8" y2="6"></line>
									<line x1="3" y1="10" x2="21" y2="10"></line>
								</svg>
								<span><?php echo date($df . ' ' . $nowtf, $ord['ts']); ?></span>
							</div>
							<div class="order-status">
								<?php
								if ($ord['status'] == 'confirmed') {
									echo '<span class="order-status-badge confirmed">' . JText::_('VRCONFIRMED') . '</span>';
								} elseif ($ord['status'] == 'standby') {
									echo '<span class="order-status-badge standby">' . JText::_('VRSTANDBY') . '</span>';
								} elseif ($ord['status'] == 'cancelled') {
									echo '<span class="order-status-badge cancelled">' . JText::_('VRCANCELLED') . '</span>';
								} else {
									echo '<span class="order-status-badge">' . htmlspecialchars($ord['status']) . '</span>';
								}
								?>
							</div>
						</div>

						<div class="order-card-body">
							<div class="order-details">
								<div class="order-detail-item">
									<div class="order-detail-content">
										<span class="order-detail-label"><?php echo JText::_('VRPICKUP') ?: 'Ridicare'; ?></span>
										<span class="order-detail-value"><?php echo date($df . ' ' . $nowtf, $ord['ritiro']); ?></span>
									</div>
								</div>
								<div class="order-detail-item">
									<div class="order-detail-content">
										<span class="order-detail-label"><?php echo JText::_('VRRETURN') ?: 'Predare'; ?></span>
										<span class="order-detail-value"><?php echo date($df . ' ' . $nowtf, $ord['consegna']); ?></span>
									</div>
								</div>
							</div>

							<div class="order-actions">
								<a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=order&sid='.$ord['sid'].'&ts='.$ord['ts'].(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" class="order-view-btn">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
										<circle cx="12" cy="12" r="3"></circle>
									</svg>
									<span><?php echo JText::_('VRCVIEWORDER') ?: 'Vezi detalii'; ?></span>
								</a>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>

				<!-- Pagination -->
				<?php if (!empty($pagelinks)): ?>
				<div class="orders-pagination">
					<?php echo $pagelinks; ?>
				</div>
				<?php endif; ?>
			</div>
			<?php else: ?>
			<div class="orders-empty">
				<div class="orders-empty-card">
					<div class="orders-empty-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
							<polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
							<line x1="12" y1="22.08" x2="12" y2="12"></line>
						</svg>
					</div>
					<div class="orders-empty-content">
						<h3><?php echo JText::_('VRCNOUSERRESFOUND') ?: 'Nicio rezervare găsită'; ?></h3>
						<p><?php echo JText::_('VRCNOUSERRESFOUND_SUBTITLE') ?: 'Momentan nu aveți nicio rezervare.'; ?></p>
						<a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=carslist'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" class="orders-empty-btn">
							<span><?php echo JText::_('VRCBOOKACAR') ?: 'Rezervați o mașină'; ?></span>
						</a>
					</div>
				</div>
			</div>
			<?php endif; ?>
		<?php endif; ?>

	</div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
	const orderCards = document.querySelectorAll('.order-card');
	orderCards.forEach(card => {
		card.addEventListener('mouseenter', () => {
			card.style.transform = 'translateY(-2px)';
			card.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
		});
		card.addEventListener('mouseleave', () => {
			card.style.transform = 'translateY(0)';
			card.style.boxShadow = '0 4px 15px rgba(0,0,0,0.08)';
		});
	});
	const searchInput = document.getElementById('vrcconfnum');
	if (searchInput) {
		searchInput.addEventListener('focus', () => searchInput.parentElement.classList.add('focused'));
		searchInput.addEventListener('blur',  () => searchInput.parentElement.classList.remove('focused'));
	}
});
</script>