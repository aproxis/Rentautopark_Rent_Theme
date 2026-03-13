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

HTMLHelper::_('bootstrap.tooltip');

$class = ' class="first"';
$lang  = Factory::getLanguage();

if ($this->maxLevel != 0 && count($this->children[$this->category->id]) > 0) : ?>

	<?php foreach ($this->children[$this->category->id] as $id => $child) : ?>
		<?php
		if ($this->params->get('show_empty_categories') || $child->numitems || count($child->getChildren())) :
			if (!isset($this->children[$this->category->id][$id + 1])) :
				$class = ' class="last"';
			endif;
		?>
		<div<?php echo $class; ?>>
			<?php $class = ''; ?>
			<?php if ($lang->isRTL()) : ?>
			<h3 class="page-header item-title">
				<?php if ( $this->params->get('show_cat_num_articles', 1)) : ?>
					<span class="badge badge-info tip hasTooltip" rel="tooltip" title="<?php echo Text::_('COM_CONTENT_NUM_ITEMS_TIP'); ?>">
						<?php echo $child->getNumItems(true); ?>
					</span>
				<?php endif; ?>
				<a href="<?php echo Route::_(ContentHelperRoute::getCategoryRoute($child->id)); ?>">
				<?php echo $this->escape($child->title); ?></a>

				<?php if ($this->maxLevel > 1 && count($child->getChildren()) > 0) : ?>
					<a href="#category-<?php echo $child->id;?>" data-toggle="collapse" data-toggle="button" class="btn btn-default btn-xs pull-right"><span class="fa fa-plus"></span></a>
				<?php endif;?>
			</h3>
			<?php else : ?>
			<h3 class="page-header item-title"><a href="<?php echo Route::_(ContentHelperRoute::getCategoryRoute($child->id));?>">
				<?php echo $this->escape($child->title); ?></a>
				<?php if ( $this->params->get('show_cat_num_articles', 1)) : ?>
				<span class="badge badge-info tip hasTooltip" rel="tooltip" title="<?php echo Text::_('COM_CONTENT_NUM_ITEMS_TIP'); ?>">
					<?php echo $child->getNumItems(true); ?>
				</span>
				<?php endif ; ?>
				
				<?php if ($this->maxLevel > 1 && count($child->getChildren()) > 0) : ?>
					<a href="#category-<?php echo $child->id;?>" data-toggle="collapse" data-toggle="button" class="btn btn-default btn-xs pull-right"><span class="fa fa-plus"></span></a>
				<?php endif;?>
			<?php endif;?>
			</h3>

			<?php if ($this->maxLevel > 1 && count($child->getChildren()) > 0) : ?>
				<?php if ($child->description) : ?>
					<div class="category-desc">
						<?php echo HTMLHelper::_('content.prepare', $child->description, '', 'com_content.category'); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if (count($child->getChildren()) > 0) : ?>
			<div class="collapse fade" id="category-<?php echo $child->id; ?>">
				<?php
				$this->children[$child->id] = $child->getChildren();
				$this->category = $child;
				$this->maxLevel--;
				if ($this->maxLevel != 0) :
					echo $this->loadTemplate('children');
				endif;
				$this->category = $child->getParent();
				$this->maxLevel++;
				?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	<?php endforeach; ?>

<?php endif;
