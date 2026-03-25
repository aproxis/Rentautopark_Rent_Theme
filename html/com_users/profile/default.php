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

// ── Fetch VikRentCar orders directly from DB ─────────────────────────────────
$orders = [];
if ($currentUser && $user->id > 0) {
    $vikPath = JPATH_SITE . '/components/com_vikrentcar';
    if (is_dir($vikPath)) {
        $helperFile = $vikPath . '/helpers/vikrentcar.php';
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

// ── Date/time format from VikRentCar settings ────────────────────────────────
$df = 'd/m/Y';
$tf = 'H:i';
if (class_exists('VikRentCar')) {
    $nowdf = VikRentCar::getDateFormat();
    $df    = match($nowdf) {
        '%d/%m/%Y' => 'd/m/Y',
        '%m/%d/%Y' => 'm/d/Y',
        default    => 'Y/m/d',
    };
    $nowtf = VikRentCar::getTimeFormat();
    $tf    = ($nowtf === 'H:i') ? 'H:i' : 'h:i A';
}
?>

<div class="profile-page">
    <div class="profile-container">

       <!-- ── Unified Profile Header (replaces both old blocks) ─────────────── -->
<div class="profile-header">
    <div class="profile-header-inner">

        <div class="profile-avatar">
            <?php
            // Initials from display name
            $nameParts = explode(' ', trim($this->data->name));
            $initials  = strtoupper(
                (isset($nameParts[0]) ? mb_substr($nameParts[0], 0, 1) : '') .
                (isset($nameParts[1]) ? mb_substr($nameParts[1], 0, 1) : '')
            );
            echo $initials ?: strtoupper(mb_substr($this->data->username, 0, 2));
            ?>
        </div>

        <div class="profile-identity">
            <div class="profile-identity-row">
                <span class="profile-name"><?php echo $this->escape($this->data->name); ?></span>
                <span class="profile-username">@<?php echo $this->escape($this->data->username); ?></span>
                <span class="profile-email"><?php echo $this->escape($this->data->email); ?></span>
            </div>
            <div class="profile-meta-row">
                <span class="profile-meta-item">
                    <?php echo Text::_('COM_USERS_PROFILE_REGISTERED_DATE_LABEL'); ?>
                    <strong><?php echo HTMLHelper::_('date', $this->data->registerDate, Text::_('DATE_FORMAT_LC1')); ?></strong>
                </span>
                <span class="profile-meta-sep">·</span>
                <span class="profile-meta-item">
                    <?php echo Text::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?>
                    <strong>
                        <?php if ($this->data->lastvisitDate): ?>
                            <?php echo HTMLHelper::_('date', $this->data->lastvisitDate, Text::_('DATE_FORMAT_LC1')); ?>
                        <?php else: ?>
                            <?php echo Text::_('COM_USERS_PROFILE_NEVER_VISITED'); ?>
                        <?php endif; ?>
                    </strong>
                </span>
            </div>
        </div>

        <?php if ($currentUser): ?>
        <button class="profile-edit-btn" onclick="openEditModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            <?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?>
        </button>
        <?php endif; ?>

    </div>
</div>

        <!-- ── Profile Content ────────────────────────────────────────────── -->
        <div class="profile-content">

            <!-- ── Booking History ─────────────────────────────────────────── -->
            <?php if ($currentUser): ?>
            <div class="profile-section">

                <div class="profile-section-header">
                    <h3><?php echo Text::_('VRCYOURRESERVATIONS') ?: 'Your Reservations'; ?></h3>
                    <p><?php echo Text::_('VRCYOURRESERVATIONS_SUBTITLE') ?: 'Manage and track all your bookings.'; ?></p>
                </div>

                <?php if (count($orders) > 0): ?>

                <div class="orders-list-header">
                    <span class="orders-count">
                        <?php echo count($orders); ?> <?php echo Text::_('VRCRESERVATIONS') ?: 'reservations'; ?>
                    </span>
                </div>

                <div class="orders-grid">
                    <?php foreach ($orders as $ord): ?>
                    <div class="order-card">

                        <div class="order-card-header">
                            <div class="order-date">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span><?php echo date($df . ' ' . $tf, $ord['ts']); ?></span>
                            </div>
                            <?php
                            $statusMap = [
                                'confirmed' => ['css' => 'confirmed', 'key' => 'VRCONFIRMED'],
                                'standby'   => ['css' => 'standby',   'key' => 'VRSTANDBY'],
                                'cancelled' => ['css' => 'cancelled', 'key' => 'VRCANCELLED'],
                            ];
                            $s   = $statusMap[$ord['status']] ?? ['css' => 'standby', 'key' => ''];
                            $lbl = $s['key'] ? Text::_($s['key']) : ucfirst($ord['status']);
                            ?>
                            <span class="order-status-badge <?php echo $s['css']; ?>">
                                <?php echo $lbl; ?>
                            </span>
                        </div>

                        <div class="order-card-body">
                            <div class="order-details">

                                <div class="order-detail-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    <div class="order-detail-content">
                                        <span class="order-detail-label">
                                            <?php echo Text::_('VRPICKUP') ?: 'Pick-up'; ?>
                                        </span>
                                        <span class="order-detail-value">
                                            <?php echo date($df . ' ' . $tf, $ord['ritiro']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="order-detail-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    <div class="order-detail-content">
                                        <span class="order-detail-label">
                                            <?php echo Text::_('VRRETURN') ?: 'Return'; ?>
                                        </span>
                                        <span class="order-detail-value">
                                            <?php echo date($df . ' ' . $tf, $ord['consegna']); ?>
                                        </span>
                                    </div>
                                </div>

                            </div>

                            <div class="order-actions">
                                <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=order&sid=' . $ord['sid'] . '&ts=' . $ord['ts']); ?>"
                                   class="order-view-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <span><?php echo Text::_('VRCVIEWORDER') ?: 'View details'; ?></span>
                                </a>
                            </div>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>

                <?php else: ?>

                <div class="orders-empty-card">
                    <div class="orders-empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <div class="orders-empty-content">
                        <h3><?php echo Text::_('VRCNOUSERRESFOUND') ?: 'No reservations found'; ?></h3>
                        <p><?php echo Text::_('VRCNOUSERRESFOUND_SUBTITLE') ?: 'You have no bookings yet.'; ?></p>
                        <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=carslist'); ?>"
                           class="orders-empty-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13" rx="2"/>
                                <path d="M16 8h4l3 5v3h-7V8z"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                            <span><?php echo Text::_('VRCBOOKACAR') ?: 'Book a car'; ?></span>
                        </a>
                    </div>
                </div>

                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div><!-- /.profile-content -->
    </div><!-- /.profile-container -->

    <!-- ── Edit Profile Modal ─────────────────────────────────────────────── -->
    <?php if ($currentUser): ?>
    <div id="editProfileModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?></h3>
                <button class="modal-close" onclick="closeEditModal()" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

</div><!-- /.profile-page -->