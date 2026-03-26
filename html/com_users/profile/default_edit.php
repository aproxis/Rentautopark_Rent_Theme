<?php
/*
 * ------------------------------------------------------------------------
 * JA Rent template - Profile Edit Modal
 * AutoRent Figma Design — v1
 * Changes:
 *  - Modern modal dialog for profile editing
 *  - Styled form inputs matching design system
 *  - Orange theme integration
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

// Get current user to check permissions
$user = Factory::getUser();
$currentUser = $user->id == $this->data->id;

if (!$currentUser) {
    return;
}

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');
?>
<style>
/* Hide username field in profile edit modal */
#jform_username {
    display: none !important;
}
</style>

<form id="member-profile-edit" action="<?php echo Route::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
    <?php foreach ($this->form->getFieldsets() as $group => $fieldset):// Iterate through the form fieldsets and display each one.?>
        <?php $fields = $this->form->getFieldset($group);?>
        <?php if (count($fields)):?>
            <?php foreach ($fields as $field):// Iterate through the fields in the set and display them.?>
                <?php if ($field->hidden):// If the field is hidden, just display the input.?>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo $field->input;?>
                        </div>
                    </div>
                <?php else:?>
                    <div class="form-group">
                        <label class="control-label" for="<?php echo $field->id; ?>">
                            <?php echo $field->label; ?>
                            <?php if (!$field->required && $field->type != 'Spacer') : ?>
                                <!-- <span class="optional"><?php echo Text::_('COM_USERS_OPTIONAL'); ?></span> -->
                            <?php endif; ?>
                        </label>
                        <div class="controls">
                            <?php echo $field->input; ?>
                            <?php if ($field->type == 'Password') : ?>
                                <!-- <div class="password-strength-indicator" id="password-strength-<?php echo $field->id; ?>" style="display: none; margin-top: 8px;">
                                    <div class="strength-bar">
                                        <div class="strength-fill" style="width: 0%;"></div>
                                    </div>
                                    <div class="strength-text" style="margin-top: 4px; font-size: 0.85rem;"></div>
                                </div>
                                <div class="password-toggle" style="margin-top: 8px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="checkbox" id="toggle-<?php echo $field->id; ?>" style="width: 16px; height: 16px;">
                                        <span style="font-size: 0.9rem; color: #6c757d;"><?php echo Text::_('COM_USERS_SHOW_PASSWORD'); ?></span>
                                    </label>
                                </div> -->
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif;?>
            <?php endforeach;?>
        <?php endif;?>
    <?php endforeach;?>

    <div class="form-actions">
        <button type="submit" class="btn-primary validate">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                <path d="M20 6L9 17l-5-5"></path>
            </svg>
            <span><?php echo Text::_('JSUBMIT'); ?></span>
        </button>
        <button type="button" class="btn-danger" onclick="closeEditModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            <span><?php echo Text::_('JCANCEL'); ?></span>
        </button>

        <input type="hidden" name="option" value="com_users" />
        <input type="hidden" name="task" value="profile.save" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>

<script type="text/javascript">
// Password strength indicator
document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(function(field) {
        const strengthIndicator = document.getElementById('password-strength-' + field.id);
        const toggleCheckbox = document.getElementById('toggle-' + field.id);
        
        if (strengthIndicator) {
            field.addEventListener('input', function() {
                const strength = calculatePasswordStrength(this.value);
                updatePasswordStrength(strengthIndicator, strength);
            });
        }
        
        if (toggleCheckbox) {
            toggleCheckbox.addEventListener('change', function() {
                field.type = this.checked ? 'text' : 'password';
            });
        }
    });
    
    // Form validation
    document.getElementById('member-profile-edit').addEventListener('submit', function(e) {
        const passwordField = document.querySelector('input[type="password"]');
        const confirmPasswordField = document.querySelector('input[type="password"][name*="password2"]');
        
        if (passwordField && confirmPasswordField) {
            if (passwordField.value !== confirmPasswordField.value) {
                e.preventDefault();
                alert('<?php echo Text::_('COM_USERS_PASSWORDS_DO_NOT_MATCH'); ?>');
                return false;
            }
        }
    });
});

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    return strength;
}

function updatePasswordStrength(indicator, strength) {
    const fill = indicator.querySelector('.strength-fill');
    const text = indicator.querySelector('.strength-text');
    
    indicator.style.display = 'block';
    
    if (strength === 0) {
        fill.style.width = '0%';
        fill.style.backgroundColor = '#dc3545';
        text.textContent = '<?php echo Text::_('COM_USERS_PASSWORD_TOO_WEAK'); ?>';
        text.style.color = '#dc3545';
    } else if (strength <= 2) {
        fill.style.width = '40%';
        fill.style.backgroundColor = '#ffc107';
        text.textContent = '<?php echo Text::_('COM_USERS_PASSWORD_WEAK'); ?>';
        text.style.color = '#ffc107';
    } else if (strength <= 3) {
        fill.style.width = '70%';
        fill.style.backgroundColor = '#fd7e14';
        text.textContent = '<?php echo Text::_('COM_USERS_PASSWORD_MEDIUM'); ?>';
        text.style.color = '#fd7e14';
    } else {
        fill.style.width = '100%';
        fill.style.backgroundColor = '#28a745';
        text.textContent = '<?php echo Text::_('COM_USERS_PASSWORD_STRONG'); ?>';
        text.style.color = '#28a745';
    }
}
</script>