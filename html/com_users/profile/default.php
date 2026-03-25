<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// ── Load VikRentCar helper unconditionally ─────────────────────────────────
$_vikHelper = Uri::root() . 'components/com_vikrentcar/helpers/vikrentcar.php';
if (file_exists($_vikHelper)) {
    require_once $_vikHelper;
}

$user        = Factory::getUser();
$currentUser = ($user->id == $this->data->id);
$document    = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'templates/rent/css/profile-styles.css');
$document->addStyleSheet(Uri::root() . 'templates/rent/css/orders-styles.css');

// ── Fetch VikRentCar orders directly from DB ──────────────────────────────
$orders = [];
if ($currentUser && $user->id > 0) {
    $vikPath = Uri::root() . 'components/com_vikrentcar';
    if (is_dir($vikPath)) {
        $helperFile = $vikPath . '/helpers/vikrentcar.php';
        if (file_exists($helperFile)) {
            require_once $helperFile;
        }
        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__vikrentcar_orders'))
                ->where(
                    '(' .
                    $db->quoteName('iduser') . ' = ' . (int)$user->id .
                    ' OR ' .
                    $db->quoteName('custmail') . ' = ' . $db->quote($user->email) .
                    ')'
                )
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

            <!-- Booking History Section -->
<?php if ($currentUser): ?>
<div class="profile-section orders-section">
    <div class="profile-section-header orders-header">
        <h3><?php echo Text::_('VRCYOURRESERVATIONS') ?: 'Rezervările dvs.'; ?></h3>
        <p><?php echo Text::_('VRCYOURRESERVATIONSSUBTITLE') ?: 'Gestionați și urmăriți toate rezervările dvs.'; ?></p>
    </div>

    <?php
    $vikOrdersTmplPath = JPATH_THEMES . '/rent/html/com_vikrentcar/userorders/default.php';
    if (file_exists($vikOrdersTmplPath)) {
        // Map $orders (from DB) into variables the VikRentCar template expects
        $rows        = $orders;   // template reads: $rows = $this->rows
        $searchorder = 0;
        $islogged    = 1;
        $pagelinks   = '';
        include $vikOrdersTmplPath;
    } else {
        // Fallback: inline render
        if (!empty($orders)): ?>
        <div class="orders-list">
            <div class="orders-grid">
                <?php foreach ($orders as $ord): ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-date">
                            <span><?php echo date($df . ' ' . $tf, $ord['ts']); ?></span>
                        </div>
                        <div class="order-status">
                            <?php
                            $badge = match($ord['status']) {
                                'confirmed' => '<span class="order-status-badge confirmed">' . Text::_('VRCCONFIRMED') . '</span>',
                                'standby'   => '<span class="order-status-badge standby">'   . Text::_('VRCSTANDBY')  . '</span>',
                                'cancelled' => '<span class="order-status-badge cancelled">' . Text::_('VRCCANCELLED') . '</span>',
                                default     => '<span class="order-status-badge">' . $ord['status'] . '</span>',
                            };
                            echo $badge;
                            ?>
                        </div>
                    </div>
                    <div class="order-card-body">
                        <div class="order-detail-item">
                            <span class="order-detail-label"><?php echo Text::_('VRPICKUP'); ?></span>
                            <span class="order-detail-value"><?php echo date($df . ' ' . $tf, $ord['ritiro']); ?></span>
                        </div>
                        <div class="order-detail-item">
                            <span class="order-detail-label"><?php echo Text::_('VRRETURN'); ?></span>
                            <span class="order-detail-value"><?php echo date($df . ' ' . $tf, $ord['consegna']); ?></span>
                        </div>
                    </div>
                    <div class="order-actions">
                        <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=orders&sid=' . $ord['sid'] . '&ts=' . $ord['ts']); ?>" class="order-view-btn">
                            <span><?php echo Text::_('VRCVIEWORDER') ?: 'Vezi detalii'; ?></span>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="orders-empty">
            <h3><?php echo Text::_('VRCNOUSERRESFOUND') ?: 'Nicio rezervare găsită'; ?></h3>
            <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=carslist'); ?>" class="orders-empty-btn">
                <?php echo Text::_('VRCBOOKACAR') ?: 'Rezervați o mașină'; ?>
            </a>
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