<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$user        = Factory::getUser();
$currentUser = ($user->id == $this->data->id);
$document    = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'templates/rent/css/profile-styles.css');
$document->addStyleSheet(Uri::root() . 'templates/rent/css/orders-styles.css');

// ── Load VikRentCar orders for the current user ──────────────────────────────
$orders = [];
if ($currentUser && $user->id > 0) {
    $vikrentcarPath = JPATH_SITE . '/components/com_vikrentcar';
    if (is_dir($vikrentcarPath)) {
        $helperFile = $vikrentcarPath . '/helpers/vikrentcar.php';
        if (file_exists($helperFile)) {
            require_once $helperFile;
        }
        try {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__vikrentcar_orders'))
                ->where($db->quoteName('custmail') . ' = ' . $db->quote($user->email))
                ->order($db->quoteName('ts') . ' DESC');
            $db->setQuery($query);
            $orders = $db->loadAssocList() ?: [];
        } catch (\Exception $e) {
            $orders = [];
        }
    }
}

// ── Date format helpers ───────────────────────────────────────────────────────
$df = 'd/m/Y';
$tf = 'H:i';
if (function_exists('VikRentCar')) { // only if helper loaded
    $nowdf = VikRentCar::getDateFormat();
    if ($nowdf == "%d/%m/%Y")      { $df = 'd/m/Y'; }
    elseif ($nowdf == "%m/%d/%Y")  { $df = 'm/d/Y'; }
    else                           { $df = 'Y/m/d'; }
    $nowtf = VikRentCar::getTimeFormat();
    $tf    = ($nowtf == 'H:i') ? 'H:i' : 'h:i A';
}
?>
<div class="profile-page">
    <div class="profile-container">

        <!-- ── Profile Header ──────────────────────────────────────────── -->
        <div class="profile-header">
            <div class="profile-header-content">
                <div class="profile-user-info">
                    <div class="profile-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                        </svg>
                    </div>
                    <div class="profile-basic-info">
                        <div class="profile-name-row">
                            <span class="profile-username"><?php echo $this->escape($this->data->username); ?></span>
                            <span class="profile-separator">•</span>
                            <span class="profile-display-name"><?php echo $this->escape($this->data->name); ?></span>
                        </div>
                        <div class="profile-dates-group">
                            <div class="profile-date-item">
                                <span class="profile-date-label"><?php echo Text::_('COM_USERS_PROFILE_REGISTERED_DATE_LABEL'); ?></span>
                                <span class="profile-date-value"><?php echo HTMLHelper::_('date', $this->data->registerDate, Text::_('DATE_FORMAT_LC1')); ?></span>
                            </div>
                            <div class="profile-date-item">
                                <span class="profile-date-label"><?php echo Text::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?></span>
                                <span class="profile-date-value">
                                    <?php if ($this->data->lastvisitDate): ?>
                                        <?php echo HTMLHelper::_('date', $this->data->lastvisitDate, Text::_('DATE_FORMAT_LC1')); ?>
                                    <?php else: ?>
                                        <?php echo Text::_('COM_USERS_PROFILE_NEVER_VISITED'); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($currentUser): ?>
                <div class="profile-actions">
                    <button class="profile-edit-btn" onclick="openEditModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        <span><?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?></span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Profile Content ─────────────────────────────────────────── -->
        <div class="profile-content">

            <!-- Account Info Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <h3><?php echo Text::_('COM_USERS_PROFILE_CORE_LEGEND'); ?></h3>
                </div>
                <div class="profile-card-body">
                    <div class="profile-info-grid">
                        <div class="profile-info-item">
                            <div class="info-content">
                                <span class="info-label"><?php echo Text::_('COM_USERS_PROFILE_NAME_LABEL'); ?></span>
                                <span class="info-value"><?php echo $this->escape($this->data->name); ?></span>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <div class="info-content">
                                <span class="info-label"><?php echo Text::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?></span>
                                <span class="info-value"><?php echo $this->escape($this->data->username); ?></span>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <div class="info-content">
                                <span class="info-label"><?php echo Text::_('COM_USERS_PROFILE_EMAIL_LABEL'); ?></span>
                                <span class="info-value"><?php echo $this->escape($this->data->email); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Booking History ───────────────────────────────────────── -->
            <?php if ($currentUser): ?>
            <div class="profile-section">
                <div class="profile-section-header">
                    <h3><?php echo Text::_('VRCYOURRESERVATIONS') ?: 'Your Reservations'; ?></h3>
                </div>

                <?php if (count($orders) > 0): ?>
                <div class="orders-grid">
                    <?php foreach ($orders as $ord): ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div class="order-date">
                                <span><?php echo date($df . ' ' . $tf, $ord['ts']); ?></span>
                            </div>
                            <div class="order-status">
                                <?php
                                $badges = [
                                    'confirmed' => 'confirmed',
                                    'standby'   => 'standby',
                                    'cancelled' => 'cancelled',
                                ];
                                $cls = $badges[$ord['status']] ?? 'standby';
                                $lbl = match($ord['status']) {
                                    'confirmed' => Text::_('VRCONFIRMED'),
                                    'standby'   => Text::_('VRSTANDBY'),
                                    'cancelled' => Text::_('VRCANCELLED'),
                                    default     => $ord['status'],
                                };
                                ?>
                                <span class="order-status-badge <?php echo $cls; ?>"><?php echo $lbl; ?></span>
                            </div>
                        </div>
                        <div class="order-card-body">
                            <div class="order-details">
                                <div class="order-detail-item">
                                    <div class="order-detail-content">
                                        <span class="order-detail-label"><?php echo Text::_('VRPICKUP') ?: 'Pick-up'; ?></span>
                                        <span class="order-detail-value"><?php echo date($df . ' ' . $tf, $ord['ritiro']); ?></span>
                                    </div>
                                </div>
                                <div class="order-detail-item">
                                    <div class="order-detail-content">
                                        <span class="order-detail-label"><?php echo Text::_('VRRETURN') ?: 'Return'; ?></span>
                                        <span class="order-detail-value"><?php echo date($df . ' ' . $tf, $ord['consegna']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="order-actions">
                                <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=order&sid=' . $ord['sid'] . '&ts=' . $ord['ts']); ?>"
                                   class="order-view-btn">
                                    <span><?php echo Text::_('VRCVIEWORDER') ?: 'View details'; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php else: ?>
                <div class="orders-empty-card">
                    <h3><?php echo Text::_('VRCNOUSERRESFOUND') ?: 'No reservations found'; ?></h3>
                    <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=carslist'); ?>" class="orders-empty-btn">
                        <?php echo Text::_('VRCBOOKACAR') ?: 'Book a car'; ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div><!-- /.profile-content -->
    </div><!-- /.profile-container -->

    <!-- ── Edit Profile Modal ──────────────────────────────────────────── -->
    <?php if ($currentUser): ?>
    <div id="editProfileModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?></h3>
                <button class="modal-close" onclick="closeEditModal()" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <?php echo $this->loadTemplate('edit'); ?>
            </div>
        </div>
    </div>
    <script>
    function openEditModal() {
        document.getElementById('editProfileModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeEditModal() {
        document.getElementById('editProfileModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    document.getElementById('editProfileModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeEditModal();
    });
    </script>
    <?php endif; ?>
</div>