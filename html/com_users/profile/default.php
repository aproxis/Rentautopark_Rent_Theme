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

if(version_compare(JVERSION, '3.0', 'ge')){
	HTMLHelper::_('bootstrap.tooltip');
} else {
	HTMLHelper::_('behavior.tooltip');
}
?>
<div class="profile <?php echo $this->pageclass_sfx; ?>">
<?php if (Factory::getUser()->id == $this->data->id) : ?>
<ul class="btn-toolbar pull-right">
	<li class="btn-group">
		<a class="btn btn-default" href="<?php echo Route::_('index.php?option=com_users&task=profile.edit&user_id='.(int) $this->data->id);?>">
			<span class="fa fa-user"></span> <?php echo Text::_('COM_USERS_EDIT_PROFILE'); ?>
		</a>
	</li>
</ul>
<?php endif; ?>
<?php if ($this->params->get('show_page_heading')) : ?>
<div class="page-header">
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
</div>
<?php endif; ?>

<?php echo $this->loadTemplate('core'); ?>

<?php echo $this->loadTemplate('params'); ?>

<?php echo $this->loadTemplate('custom'); ?>

</div>
