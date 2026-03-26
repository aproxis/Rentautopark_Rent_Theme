<?php
/*
 * ------------------------------------------------------------------------
 * JA Rent template - Profile Edit Modal
 * AutoRent Figma Design — v2
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

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
/* ── Profile Edit Form ─────────────────────────────────────────── */

/* Hide username label */
#jform_username-lbl,
label[for="jform_username"] > label {
    display: none !important;
}

/* Row: label + input on one line */
#member-profile-edit .form-group {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 14px;
}

#member-profile-edit .form-group .control-label {
    flex: 0 0 140px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin: 0;
    line-height: 1.4;
}

/* Strip inner label margin */
#member-profile-edit .form-group .control-label label {
    margin: 0;
    font-weight: 600;
    color: #374151;
}

#member-profile-edit .form-group .controls {
    flex: 1;
    min-width: 0;
}

/* Inputs */
#member-profile-edit .form-control {
    width: 100%;
    padding: 9px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    color: #0a0a0a;
    background: #fff;
    box-sizing: border-box;
    transition: border-color .2s, box-shadow .2s;
}
#member-profile-edit .form-control:focus {
    outline: none;
    border-color: #FE5001;
    box-shadow: 0 0 0 3px rgba(254,80,1,.12);
}
#member-profile-edit .form-control[readonly] {
    background: #f9fafb;
    color: #9ca3af;
    cursor: not-allowed;
}

/* Password rules hint */
#member-profile-edit [id$="-rules"] {
    font-size: 11px !important;
    color: #9ca3af !important;
    margin-bottom: 4px;
    line-height: 1.3;
}

/* Password group — eye button overlaid */
#member-profile-edit .password-group {
    position: relative;
}
#member-profile-edit .password-group .input-group {
    position: relative;
    display: flex;
    align-items: center;
}
#member-profile-edit .password-group .form-control {
    padding-right: 42px;
}
#member-profile-edit .input-password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0;
    color: #9ca3af;
    cursor: pointer;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    transition: color .2s;
}
#member-profile-edit .input-password-toggle:hover {
    color: #374151;
}

/* Hide "Показать пароль" text — keep only icon */
#member-profile-edit .visually-hidden {
    display: none !important;
}

/* Meter (password strength) */
#member-profile-edit meter {
    width: 100%;
    height: 4px;
    margin-top: 6px;
    border-radius: 2px;
}

/* Form actions */
#member-profile-edit .form-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}

/* Buttons */
#member-profile-edit .btn-primary,
#member-profile-edit .btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background .2s, box-shadow .2s;
    line-height: 1;
}
#member-profile-edit .btn-primary svg,
#member-profile-edit .btn-danger svg {
    flex-shrink: 0;
    display: block;
    margin: 0 !important;
}
#member-profile-edit .btn-primary {
    background: #FE5001;
    color: #fff;
}
#member-profile-edit .btn-primary:hover {
    background: #E54801;
    box-shadow: 0 4px 12px rgba(254,80,1,.28);
}
#member-profile-edit .btn-danger {
    background: #f3f4f6;
    color: #374151;
    border: 1.5px solid #e5e7eb;
}
#member-profile-edit .btn-danger:hover {
    background: #fee2e2;
    color: #ef4444;
    border-color: #fca5a5;
}

/* Responsive */
@media (max-width: 540px) {
    #member-profile-edit .form-group {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    #member-profile-edit .form-group .control-label {
        flex: none;
    }
    #member-profile-edit .form-group .controls {
        width: 100%;
    }
}
</style>

<form id="member-profile-edit" action="<?php echo Route::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

    <?php foreach ($this->form->getFieldsets() as $group => $fieldset): ?>
        <?php $fields = $this->form->getFieldset($group); ?>
        <?php if (count($fields)): ?>
            <?php foreach ($fields as $field): ?>
                <?php if ($field->hidden): ?>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label class="control-label" for="<?php echo $field->id; ?>">
                            <?php echo $field->label; ?>
                        </label>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="form-actions">
        <button type="submit" class="btn-primary validate">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6L9 17l-5-5"></path>
            </svg>
            <span><?php echo Text::_('JAPPLY'); ?></span>
        </button>
        <button type="button" class="btn-danger" onclick="closeEditModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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