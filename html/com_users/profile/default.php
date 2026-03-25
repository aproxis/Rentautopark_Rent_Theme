<?php
/*
 * ------------------------------------------------------------------------
 * JA Rent template - Profile Page Override
 * AutoRent Figma Design — v1
 * Changes:
 *  - Modern horizontal profile header layout
 *  - Integrated booking history from VikRentCar
 *  - Orange theme (#FE5001) matching design system
 *  - Responsive design with mobile optimization
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Get current user
$user = Factory::getUser();
$currentUser = $user->id == $this->data->id;

// Add profile styles
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root() . 'templates/rent/css/profile-styles.css');

// Add edit modal template
require_once JPATH_SITE . '/templates/rent/html/com_users/profile/edit_modal.php';
?>
<div class="profile-page">
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-header-content">
                <!-- User Info Section -->
                <div class="profile-user-info">
                    <div class="profile-avatar">
                        <svg class="profile-avatar-icon" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M4 21v-2a4 4 0 0 1 3-3.87"></path>
                            <path d="M12 21v-2a4 4 0 0 0 1-7.95"></path>
                            <path d="M12 3v1"></path>
                            <path d="M12 20v1"></path>
                            <path d="M20 12h1"></path>
                            <path d="M4 12H3"></path>
                            <path d="M12 4v1"></path>
                            <path d="M12 19v1"></path>
                            <path d="M4.93 4.93l.74.74"></path>
                            <path d="M18.34 18.34l.74.74"></path>
                            <path d="M18.34 5.66l-.74.74"></path>
                            <path d="M5.66 18.34l-.74.74"></path>
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
                
                <!-- Edit Button -->
                <?php if ($currentUser): ?>
                <div class="profile-actions">
                    <button class="profile-edit-btn" onclick="openEditModal()">
                        <svg class="edit-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span><?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?></span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Account Information Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <h3><?php echo Text::_('COM_USERS_PROFILE_CORE_LEGEND'); ?></h3>
                </div>
                <div class="profile-card-body">
                    <div class="profile-info-grid">
                        <div class="profile-info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M4 21v-2a4 4 0 0 1 3-3.87"></path>
                                <path d="M12 21v-2a4 4 0 0 0 1-7.95"></path>
                            </svg>
                            <div class="info-content">
                                <span class="info-label"><?php echo Text::_('COM_USERS_PROFILE_NAME_LABEL'); ?></span>
                                <span class="info-value"><?php echo $this->escape($this->data->name); ?></span>
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                            </svg>
                            <div class="info-content">
                                <span class="info-label"><?php echo Text::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?></span>
                                <span class="info-value"><?php echo $this->escape($this->data->username); ?></span>
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <div class="info-content">
                                <span class="info-label"><?php echo Text::_('COM_USERS_PROFILE_EMAIL_LABEL'); ?></span>
                                <span class="info-value"><?php echo $this->escape($this->data->email); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking History Section -->
            <?php if ($currentUser): ?>
            <div class="profile-section">
                <div class="profile-section-header">
                    <h3><?php echo Text::_('VRCYOURRESERVATIONS') ?: 'Rezervările dvs.'; ?></h3>
                    <p><?php echo Text::_('VRCYOURRESERVATIONS_SUBTITLE') ?: 'Gestionați și urmăriți toate rezervările dvs.'; ?></p>
                </div>
                
                <!-- Include VikRentCar Orders -->
                <?php
                // Check if user is logged in and has orders
                if ($currentUser) {
                    // Load VikRentCar component files
                    $vikrentcarPath = JPATH_SITE . '/components/com_vikrentcar';
                    if (file_exists($vikrentcarPath)) {
                        // Include the VikRentCar orders view
                        $ordersViewPath = $vikrentcarPath . '/views/userorders/tmpl/default.php';
                        if (file_exists($ordersViewPath)) {
                            // We need to load the view properly through Joomla's MVC
                            // For now, we'll include the template directly with proper context
                            $vikrentcarHelper = $vikrentcarPath . '/helpers/vikrentcar.php';
                            if (file_exists($vikrentcarHelper)) {
                                require_once $vikrentcarHelper;
                            }
                            
                            // Set up the view context
                            $view = new stdClass();
                            $view->rows = $this->rows ?? [];
                            $view->searchorder = $this->searchorder ?? 0;
                            $view->islogged = $this->islogged ?? 1;
                            $view->pagelinks = $this->pagelinks ?? '';
                            
                            // Include the template with proper variables
                            include $ordersViewPath;
                        } else {
                            echo '<div class="profile-empty-card">';
                            echo '<div class="orders-empty-icon">';
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
                            echo '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>';
                            echo '<polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>';
                            echo '<line x1="12" y1="22.08" x2="12" y2="12"></line>';
                            echo '</svg>';
                            echo '</div>';
                            echo '<div class="orders-empty-content">';
                            echo '<h3>' . Text::_('VRCNOUSERRESFOUND') . '</h3>';
                            echo '<p>' . Text::_('VRCNOUSERRESFOUND_SUBTITLE') . '</p>';
                            echo '<a href="' . JRoute::_('index.php?option=com_vikrentcar&view=carslist') . '" class="orders-empty-btn">';
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
                            echo '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>';
                            echo '<polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>';
                            echo '<line x1="12" y1="22.08" x2="12" y2="12"></line>';
                            echo '</svg>';
                            echo '<span>' . Text::_('VRCBOOKACAR') . '</span>';
                            echo '</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>' . Text::_('VRCNOUSERRESFOUND') . '</p>';
                    }
                }
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <?php if ($currentUser): ?>
    <div id="editProfileModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?></h3>
                <button class="modal-close" onclick="closeEditModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <?php echo $this->loadTemplate('edit'); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
function openEditModal() {
    document.getElementById('editProfileModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editProfileModal').style.display = 'none';
    document.body.style.overflow = 'unset';
}

// Close modal when clicking outside
document.getElementById('editProfileModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});
</script>