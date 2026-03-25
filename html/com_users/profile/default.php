<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// ── Load VikRentCar helper ────────────────────────────────────────────────
$_vikHelper = JPATH_ROOT . '/components/com_vikrentcar/helpers/vikrentcar.php';
if (file_exists($_vikHelper)) {
    require_once $_vikHelper;
}

$user        = Factory::getUser();
$currentUser = ($user->id == $this->data->id);
$document    = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'templates/rent/css/profile-styles.css');
$document->addStyleSheet(Uri::root() . 'templates/rent/css/orders-styles.css');

// ── Fetch VikRentCar orders directly from DB ──────────────────────────────
// FIX: VikRentCar uses 'custid' for the Joomla user ID and 'custmail' for email.
// We use a two-step approach: first detect which columns actually exist, then query.
$orders = [];
if ($currentUser && $user->id > 0) {
    $vikPath = JPATH_ROOT . '/components/com_vikrentcar';
    if (is_dir($vikPath)) {
        try {
            $db = Factory::getDbo();

            // ── Step 1: detect the correct user-ID column name ────────────
            // VRC < 1.14 uses 'iduser', VRC >= 1.14 uses 'custid'
            $userIdCol  = 'iduser';   // default (older versions)
            $emailCol   = 'custmail'; // default

            $columns = $db->getTableColumns('#__vikrentcar_orders', false);
            if (isset($columns['custid'])) {
                $userIdCol = 'custid';
            }
            if (isset($columns['customer_email'])) {
                $emailCol = 'customer_email';
            } elseif (isset($columns['email'])) {
                $emailCol = 'email';
            }

            // ── Step 2: query orders matching this user ───────────────────
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__vikrentcar_orders'))
                ->where(
                    '(' .
                    $db->quoteName($userIdCol) . ' = ' . (int)$user->id .
                    ' OR ' .
                    $db->quoteName($emailCol)  . ' = ' . $db->quote($user->email) .
                    ')'
                )
                ->order($db->quoteName('ts') . ' DESC');

            $db->setQuery($query);
            $orders = $db->loadAssocList() ?: [];

        } catch (\Exception $e) {
            // Capture error for debug display below
            $ordersError = $e->getMessage();
            $orders = [];
        }
    }
}

// ── Date/time format ──────────────────────────────────────────────────────
$df    = 'd/m/Y';
$nowtf = 'H:i';
if (class_exists('VikRentCar')) {
    $nowdf = VikRentCar::getDateFormat();
    $nowtf = VikRentCar::getTimeFormat();
    $df    = match($nowdf) {
        '%d/%m/%Y' => 'd/m/Y',
        '%m/%d/%Y' => 'm/d/Y',
        default    => 'Y/m/d',
    };
}
?>

<div class="profile-page">
    <div class="profile-container">

        <!-- ── Profile Header ────────────────────────────────────────────── -->
        <div class="profile-header">
            <div class="profile-header-inner">

                <div class="profile-avatar">
                    <?php
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

        <!-- ── Profile Content ───────────────────────────────────────────── -->
        <div class="profile-content">

            <?php if ($currentUser): ?>
            <div class="profile-section orders-section">
                <div class="profile-section-header orders-header">
                    <h3><?php echo Text::_('VRCYOURRESERVATIONS') ?: 'Rezervările dvs.'; ?></h3>
                    <p><?php echo Text::_('VRCYOURRESERVATIONSSUBTITLE') ?: 'Gestionați și urmăriți toate rezervările dvs.'; ?></p>
                </div>

                <?php
                // ── FIX: Try to include the userorders template override ──
                // Pass variables the template expects BEFORE including it.
                // The template guards with isset() so these take full priority.
                $vikOrdersTmplPath = JPATH_THEMES . '/rent/html/com_vikrentcar/userorders/default.php';

                if (file_exists($vikOrdersTmplPath)) {
                    // These must be set in THIS scope — include() shares scope.
                    $rows        = $orders;  // orders array from DB query above
                    $islogged    = 1;        // user is confirmed logged in
                    $searchorder = 0;        // hide search box in profile context
                    $pagelinks   = '';       // no pagination in profile view
                    // $df and $nowtf already set above — template reuses them

                    // FIX: The included template wraps output in .orders-page > .orders-container
                    // which adds extra nesting. We suppress that with a CSS override below,
                    // OR we render only the inner orders list directly here.
                    include $vikOrdersTmplPath;

                } else {
                    // ── Inline fallback render ────────────────────────────
                    if (!empty($orders)): ?>
                    <div class="orders-list">
                        <div class="orders-list-header">
                            <h2><?php echo Text::_('VRCYOURRESERVATIONS') ?: 'Rezervările dvs.'; ?></h2>
                            <span class="orders-count"><?php echo count($orders); ?> <?php echo Text::_('VRCRESERVATIONS') ?: 'rezervări'; ?></span>
                        </div>
                        <div class="orders-grid">
                            <?php foreach ($orders as $ord): ?>
                            <div class="order-card">
                                <div class="order-card-header">
                                    <div class="order-date">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <span><?php echo date($df . ' ' . $nowtf, $ord['ts']); ?></span>
                                    </div>
                                    <div class="order-status">
                                        <?php
                                        $statusMap = [
                                            'confirmed' => ['class' => 'confirmed', 'key' => 'VRCONFIRMED'],
                                            'standby'   => ['class' => 'standby',   'key' => 'VRSTANDBY'],
                                            'cancelled' => ['class' => 'cancelled', 'key' => 'VRCANCELLED'],
                                        ];
                                        $s = $statusMap[$ord['status']] ?? null;
                                        if ($s) {
                                            echo '<span class="order-status-badge ' . $s['class'] . '">' . Text::_($s['key']) . '</span>';
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
                                                <span class="order-detail-label"><?php echo Text::_('VRPICKUP') ?: 'Ridicare'; ?></span>
                                                <span class="order-detail-value"><?php echo date($df . ' ' . $nowtf, $ord['ritiro']); ?></span>
                                            </div>
                                        </div>
                                        <div class="order-detail-item">
                                            <div class="order-detail-content">
                                                <span class="order-detail-label"><?php echo Text::_('VRRETURN') ?: 'Predare'; ?></span>
                                                <span class="order-detail-value"><?php echo date($df . ' ' . $nowtf, $ord['consegna']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="order-actions">
                                        <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=order&sid=' . $ord['sid'] . '&ts=' . $ord['ts']); ?>" class="order-view-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                            <span><?php echo Text::_('VRCVIEWORDER') ?: 'Vezi detalii'; ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
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
                                <h3><?php echo Text::_('VRCNOUSERRESFOUND') ?: 'Nicio rezervare găsită'; ?></h3>
                                <p><?php echo Text::_('VRCNOUSERRESFOUND_SUBTITLE') ?: 'Momentan nu aveți nicio rezervare.'; ?></p>
                                <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=carslist'); ?>" class="orders-empty-btn">
                                    <span><?php echo Text::_('VRCBOOKACAR') ?: 'Rezervați o mașină'; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php
                    // ── Debug: show DB error or column detection (remove in production) ──
                    if (defined('JDEBUG') && JDEBUG && isset($ordersError)): ?>
                    <div style="background:#fff3cd;border:1px solid #ffc107;padding:12px;border-radius:8px;margin-top:16px;font-family:monospace;font-size:12px;">
                        <strong>VRC Orders Debug:</strong> <?php echo htmlspecialchars($ordersError); ?>
                    </div>
                    <?php endif;
                }
                ?>
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

<style>
/*
 * FIX: When userorders/default.php is included inside the profile,
 * its .orders-page wrapper adds unwanted padding/margin.
 * These rules neutralise that extra nesting.
 */
.orders-section .orders-page {
    padding: 0 !important;
    margin: 0 !important;
    background: none !important;
}
.orders-section .orders-container {
    padding: 0 !important;
    margin: 0 !important;
    max-width: none !important;
}
</style>