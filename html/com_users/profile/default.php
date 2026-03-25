<?php
/**
 * JA Rent template - Profile Page Override (FIXED FOR JOOMLA 5)
 * AutoRent Figma Design — v1
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user = Factory::getUser();
$currentUser = $user->id == $this->data->id;

$document = Factory::getDocument();
$document->addStyleSheet(JURI::root() . 'templates/rent/css/profile-styles.css');
?>
<div class="profile-page">
    <div class="profile-container">
        <!-- Profile Header [UNCHANGED] -->
        <div class="profile-header">
            <div class="profile-header-content">
                <div class="profile-user-info">
                    <div class="profile-avatar">
                        <svg class="profile-avatar-icon" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-3-3.87M4 21v-2a4 4 0 0 1 3-3.87M12 21v-2a4 4 0 0 0 1-7.95M12 3v1M12 20v1M20 12h1M4 12H3M12 4v1M12 19v1M4.93 4.93l.74.74M18.34 18.34l.74.74M18.34 5.66l-.74.74M5.66 18.34l-.74.74"></path>
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
                            <?php if ($this->data->lastvisitDate !== null): ?>
                            <div class="profile-date-item">
                                <span class="profile-date-label"><?php echo Text::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?></span>
                                <span class="profile-date-value"><?php echo HTMLHelper::_('date', $this->data->lastvisitDate, Text::_('DATE_FORMAT_LC1')); ?></span>
                            </div>
                            <?php else: ?>
                            <div class="profile-date-item">
                                <span class="profile-date-label"><?php echo Text::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?></span>
                                <span class="profile-date-value"><?php echo Text::_('COM_USERS_PROFILE_NEVER_VISITED'); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if ($currentUser): ?>
                <div class="profile-actions">
                    <button class="profile-edit-btn" onclick="openEditModal()">
                        <svg class="edit-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span><?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?></span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Account Info [UNCHANGED] -->
            <div class="profile-card">
                <div class="profile-card-header"><h3><?php echo Text::_('COM_USERS_PROFILE_CORE_LEGEND'); ?></h3></div>
                <div class="profile-card-body">
                    <div class="profile-info-grid">
                        <div class="profile-info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-3-3.87M4 21v-2a4 4 0 0 1 3-3.87M12 21v-2a4 4 0 0 0 1-7.95"></path>
                            </svg>
                            <div class="info-content">
                                <span class="info-label"><?php echo Text::_('COM_USERS_PROFILE_NAME_LABEL'); ?></span>
                                <span class="info-value"><?php echo $this->escape($this->data->name); ?></span>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                            </svg>
                            <div class="info-content">
                                <span class="info-label"><?php echo Text::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?></span>
                                <span class="info-value"><?php echo $this->escape($this->data->username); ?></span>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <div class="info-content">
                                <span class="info-label"><?php echo Text::_('COM_USERS_PROFILE_EMAIL_LABEL'); ?></span>
                                <span class="info-value"><?php echo $this->escape($this->data->email); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FIXED Booking History - NO $this reassignment -->
            <?php if ($currentUser): ?>
            <div class="profile-section orders-section">
                <div class="profile-section-header orders-header">
                    <h3><?php echo Text::_('VRCYOURRESERVATIONS') ?: 'Rezervările dvs.'; ?></h3>
                    <p><?php echo Text::_('VRCYOURRESERVATIONSSUBTITLE') ?: 'Gestionați și urmăriți toate rezervările dvs.'; ?></p>
                </div>
                
                <?php
                ob_start();
                try {
                    $vikModelPath = JPATH_SITE . '/components/com_vikrentcar/models/userorders.php';
                    $vikOrdersTmplPath = JPATH_THEMES . '/rent/html/com_vikrentcar/userorders/default.php';
                    
                    if (file_exists($vikModelPath) && file_exists($vikOrdersTmplPath)) {
                        require_once $vikModelPath;
                        
                        // Extract variables for template scope
                        $vik_rows = null;
                        $vik_searchorder = 0;
                        $vik_islogged = 1;
                        $vik_pagelinks = '';
                        
                        $ordersModel = new VikRentCarModelUserOrders();
                        $ordersModel->setState('id_user', (int)$this->data->id);
                        
                        $vik_rows = $ordersModel->getItems();
                        $vik_searchorder = $ordersModel->getState('searchorder') ?? 0;
                        $vik_pagelinks = $ordersModel->getPagination()->getPagesLinks() ?? '';
                        
                        // Include template with extracted variables (no $this change)
                        include $vikOrdersTmplPath;
                    } else {
                        throw new Exception('VikRentCar files missing');
                    }
                } catch (Exception $e) {
                    // Safe empty state
                    ?>
                    <div class="profile-empty-card orders-empty">
                        <div class="orders-empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                        </div>
                        <div class="orders-empty-content">
                            <h3><?php echo Text::_('VRCNOUSERRESFOUND') ?: 'Nicio rezervare'; ?></h3>
                            <p>Rezervați acum pentru a vedea istoricul.</p>
                            <a href="<?php echo Route::_('index.php?option=com_vikrentcar&view=carslist'); ?>" class="orders-empty-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                                <span><?php echo Text::_('VRCBOOKACAR') ?: 'Rezervați o mașină'; ?></span>
                            </a>
                        </div>
                    </div>
                    <?php
                }
                ob_end_flush();
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal [UNCHANGED] -->
    <?php if ($currentUser): ?>
    <div id="editProfileModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?></h3>
                <button class="modal-close" onclick="closeEditModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body"><?php echo $this->loadTemplate('edit'); ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function openEditModal() { document.getElementById('editProfileModal').style.display = 'flex'; document.body.style.overflow = 'hidden'; }
function closeEditModal() { document.getElementById('editProfileModal').style.display = 'none'; document.body.style.overflow = 'unset'; }
document.getElementById('editProfileModal').addEventListener('click', e => { if (e.target === this) closeEditModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEditModal(); });
</script>