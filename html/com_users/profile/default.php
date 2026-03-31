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

// ── Load VikRentCar language strings ─────────────────────────────────────
// The profile page is a com_users view, so Joomla never auto-loads
// VikRentCar's language file. We load it manually here so that
// Text::_('VRCYOURRESERVATIONS') etc. resolve to actual strings.
$_lang = Factory::getLanguage();
$_lang->load('com_vikrentcar', JPATH_ROOT . '/components/com_vikrentcar', null, true);
// Also try the site language override path (templates/rent/language/…)
$_lang->load('com_vikrentcar', JPATH_ROOT, null, false);

// Helper: returns the translation if the key was found, otherwise the fallback.
// Text::_() returns the key itself when untranslated, so ?: won't ever fire.
if (!function_exists('vrcText')) {
    function vrcText(string $key, string $fallback): string {
        $t = \Joomla\CMS\Language\Text::_($key);
        return ($t !== $key) ? $t : $fallback;
    }
}

$user        = Factory::getUser();
$currentUser = ($user->id == $this->data->id);
$document    = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'templates/rent/css/profile-styles.css');
$document->addStyleSheet(Uri::root() . 'templates/rent/css/orders-styles.css');

// ── Fetch VikRentCar orders ───────────────────────────────────────────────
//
// Confirmed DB structure from mb1ii_vikrentcar_orders:
//   ujid      INT   — Joomla user ID (0 = guest order, no user account linked)
//   custmail  VARCHAR — customer email (empty '' for many guest orders)
//   custdata  TEXT  — free-text blob, e.g. "e-Mail: user@example.com\nTelefon: ..."
//
// Match strategy — OR across all three so we catch every possible link:
//   1. ujid = $user->id                  registered orders linked to this account
//   2. custmail = $user->email           email captured at checkout
//   3. custdata LIKE '%user@email%'      guest order with email only in the text blob
//
$orders = [];
if ($currentUser && $user->id > 0) {
    $vikPath = JPATH_ROOT . '/components/com_vikrentcar';
    if (is_dir($vikPath)) {
        try {
            $db = Factory::getDbo();

            // Raw SQL — avoids Joomla query-builder wrapping each ->where() call
            // with AND, which silently breaks OR-only WHERE clauses (J3/J4/J5).
            $ujid      = (int)$user->id;
            $email     = $db->quote($user->email);
            $emailLike = $db->quote('%' . $db->escape($user->email, true) . '%');

            // 4th path: find the customer record(s) linked to this Joomla uid,
            //    then look up all orders for those customers via the bridge table.
            $custIdsSql = 'SELECT ' . $db->quoteName('id')
                        . ' FROM '  . $db->quoteName('#__vikrentcar_customers')
                        . ' WHERE ' . $db->quoteName('ujid') . ' = ' . $ujid;
            $db->setQuery($custIdsSql);
            $custIds = $db->loadColumn() ?: [];

            $custOrderIdsSql = '';
            if (!empty($custIds)) {
                $custIdsIn = implode(',', array_map('intval', $custIds));
                $custOrderIdsSql = 'SELECT ' . $db->quoteName('idorder')
                                 . ' FROM '  . $db->quoteName('#__vikrentcar_customers_orders')
                                 . ' WHERE ' . $db->quoteName('idcustomer') . ' IN (' . $custIdsIn . ')';
                $db->setQuery($custOrderIdsSql);
                $linkedOrderIds = $db->loadColumn() ?: [];
            } else {
                $linkedOrderIds = [];
            }

            $sql = 'SELECT * FROM ' . $db->quoteName('#__vikrentcar_orders')
                 . ' WHERE ('
                 // 1. Order placed while logged in — ujid stored at checkout
                 . '  (' . $db->quoteName('ujid') . ' > 0 AND ' . $db->quoteName('ujid') . ' = ' . $ujid . ')'
                 // 2. Guest order: email in custmail column
                 . '  OR (' . $db->quoteName('custmail') . " != '' AND " . $db->quoteName('custmail') . ' = ' . $email . ')'
                 // 3. Old guest order: email only in custdata text blob
                 . '  OR (' . $db->quoteName('custmail') . " = '' AND " . $db->quoteName('custdata') . ' LIKE ' . $emailLike . ')';

            // 4. Orders linked via customers_orders bridge table (catches orders where
            //    ujid=0 but admin manually linked the customer, or checkout email differs)
            if (!empty($linkedOrderIds)) {
                $orderIdsIn = implode(',', array_map('intval', $linkedOrderIds));
                $sql .= '  OR ' . $db->quoteName('id') . ' IN (' . $orderIdsIn . ')';
            }

            $sql .= ' ) ORDER BY ' . $db->quoteName('ts') . ' DESC';

            $db->setQuery($sql);
            $orders = $db->loadAssocList() ?: [];

        } catch (\Exception $e) {
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
                <button class="profile-edit-btn" onclick="openEditModal()" aria-label="<?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    <span class="profile-edit-text"><?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?></span>
                </button>
                <?php endif; ?>

            </div>
        </div>

        <!-- ── Profile Content ───────────────────────────────────────────── -->
        <div class="profile-content">

            <?php if ($currentUser): ?>
            <div class="profile-section orders-section">
                <!-- <div class="profile-section-header orders-header">
                    <h3><?php echo vrcText('VRCYOURRESERVATIONS', 'Rezervările dvs.'); ?></h3>
                    <p><?php echo vrcText('VRCYOURRESERVATIONSSUBTITLE', 'Gestionați și urmăriți toate rezervările dvs.'); ?></p>
                </div> -->

                <?php
                $vikOrdersTmplPath = JPATH_THEMES . '/rent/html/com_vikrentcar/userorders/default.php';

                if (file_exists($vikOrdersTmplPath)) {
                    // Variables set here take priority over $this->* guards in the template
                    $rows        = $orders;
                    $islogged    = 1;
                    $searchorder = 0;
                    $pagelinks   = '';
                    // $df / $nowtf already defined above
                    include $vikOrdersTmplPath;
                } else {
                    // ── Inline fallback ───────────────────────────────────
                    if (!empty($orders)): ?>
                    <div class="orders-list">
                        <div class="orders-list-header">
                            <h2><?php echo vrcText('VRCYOURRESERVATIONS', 'Rezervările dvs.'); ?></h2>
                            <span class="orders-count"><?php echo count($orders); ?> <?php echo vrcText('VRCRESERVATIONS', 'rezervări'); ?></span>
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
                                            'confirmed' => ['class' => 'confirmed', 'key' => 'VRCONFIRMED',  'fallback' => 'Confirmat'],
                                            'standby'   => ['class' => 'standby',   'key' => 'VRSTANDBY',   'fallback' => 'În așteptare'],
                                            'cancelled' => ['class' => 'cancelled', 'key' => 'VRCANCELLED', 'fallback' => 'Anulat'],
                                        ];
                                        $s = $statusMap[$ord['status']] ?? null;
                                        if ($s) {
                                            echo '<span class="order-status-badge ' . $s['class'] . '">' . vrcText($s['key'], $s['fallback']) . '</span>';
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
                                                <span class="order-detail-label"><?php echo vrcText('VRPICKUP', 'Ridicare'); ?></span>
                                                <span class="order-detail-value"><?php echo date($df . ' ' . $nowtf, $ord['ritiro']); ?></span>
                                            </div>
                                        </div>
                                        <div class="order-detail-item">
                                            <div class="order-detail-content">
                                                <span class="order-detail-label"><?php echo vrcText('VRRETURN', 'Predare'); ?></span>
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
                                            <span><?php echo vrcText('VRCVIEWORDER', 'Vezi detalii'); ?></span>
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
                                <h3><?php echo vrcText('VRCNOUSERRESFOUND', 'Nicio rezervare găsită'); ?></h3>
                                <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=carslist'); ?>" class="orders-empty-btn">
                                    <span><?php echo vrcText('VRCBOOKYOURFIRSTCAR', 'Rezervă prima ta mașină!'); ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif;
                }
                ?>

                <?php if (isset($ordersError)): ?>
                <div style="background:#fff3cd;border:1px solid #ffc107;padding:12px;border-radius:8px;margin-top:16px;font-family:monospace;font-size:12px;">
                    <strong>VRC Orders DB Error:</strong> <?php echo htmlspecialchars($ordersError); ?>
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

<style>
/*
 * Neutralise the extra .orders-page > .orders-container wrapper
 * that userorders/default.php adds when included inside profile.
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
