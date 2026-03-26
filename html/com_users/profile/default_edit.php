<?php
/*
 * ------------------------------------------------------------------------
 * JA Rent template - Profile Edit Modal
 * AutoRent Figma Design — v3
 * Styles moved to: css/profile-edit.css
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

// Load shared profile-edit styles
$doc = Factory::getDocument();
$doc->addStyleSheet(Uri::root() . 'templates/rent/css/profile-edit.css');
?>
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