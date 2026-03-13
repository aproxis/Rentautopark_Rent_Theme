<?php
/*
 * ------------------------------------------------------------------------
 * JA Rent template
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

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');

//load user_profile plugin language
$lang = Factory::getLanguage();
$lang->load('plg_user_profile', JPATH_ADMINISTRATOR);
?>
<div class="profile-edit<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
<?php endif; ?>

<form id="member-profile" action="<?php echo Route::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
<?php foreach ($this->form->getFieldsets() as $group => $fieldset):// Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($group);?>
	<?php if (count($fields)):?>
	<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
		<legend><?php echo Text::_($fieldset->label); ?></legend>
		<?php endif;?>
    <?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
			<?php echo '<p>' . $this->escape(Text::_($fieldset->description)) . '</p>'; ?>
		<?php endif; ?>
		<?php foreach ($fields as $field):// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<div class="form-group">
					<div class="col-sm-12 controls">
						<?php echo $field->input;?>
					</div>
				</div>
			<?php else:?>
				<div class="form-group">
					<div class="col-sm-4 control-label">
						<?php echo $field->label; ?>
						<?php if (!$field->required && $field->type != 'Spacer') : ?>
						<?php if(version_compare(JVERSION, '4', 'lt')) : ?>
								<span class="optional"><?php echo Text::_('COM_USERS_OPTIONAL'); ?></span>
								<?php endif; ?>
						<?php endif; ?>
					</div>
					<div class="col-sm-8 controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endif;?>
		<?php endforeach;?>
	</fieldset>
	<?php endif;?>
<?php endforeach;?>

	<div class="form-group form-actions">
		<div class="col-sm-offset-4 col-sm-8">
			<button type="submit" class="btn btn-primary validate"><span><?php echo Text::_('JSUBMIT'); ?></span></button>
			<a class="btn btn-danger" href="<?php echo Route::_('index.php?option=com_users&view=profile'); ?>" title="<?php echo Text::_('JCANCEL'); ?>"><?php echo Text::_('JCANCEL'); ?></a>

			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="profile.save" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</div>

	</form>
</div>
