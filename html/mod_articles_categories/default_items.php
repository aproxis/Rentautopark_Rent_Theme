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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$input  = Factory::getApplication()->input;
$option = $input->getCmd('option');
$view   = $input->getCmd('view');
$id     = $input->getInt('id');


foreach ($list as $item) : ?>
	<li<?php if ($id == $item->id && in_array($view, array('category', 'categories')) && $option == 'com_content') echo ' class="active"'; ?>> <?php $levelup = $item->level - $startLevel - 1; ?>
		<h<?php echo $params->get('item_heading') + $levelup; ?>>
		<a href="<?php echo Route::_(ContentHelperRoute::getCategoryRoute($item->id)); ?>">
		<?php echo $item->title;?>
			<?php if ($params->get('numitems')) : ?>
				<span>
				<?php echo $item->numitems; ?>
				</span>
			<?php endif; ?>
		</a>
   		</h<?php echo $params->get('item_heading') + $levelup; ?>>

		<?php if ($params->get('show_description', 0)) : ?>
			<?php echo HTMLHelper::_('content.prepare', $item->description, $item->getParams(), 'mod_articles_categories.content'); ?>
		<?php endif; ?>
		<?php if ($params->get('show_children', 0) && (($params->get('maxlevel', 0) == 0)
			|| ($params->get('maxlevel') >= ($item->level - $startLevel)))
			&& count($item->getChildren())) : ?>
			<?php echo '<ul>'; ?>
			<?php $temp = $list; ?>
			<?php $list = $item->getChildren(); ?>
			<?php require JModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default') . '_items'); ?>
			<?php $list = $temp; ?>
			<?php echo '</ul>'; ?>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
