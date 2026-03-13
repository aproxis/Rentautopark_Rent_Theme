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

defined('JPATH_BASE') or die;

?>
<dd class="category-name">
  <i class="fa fa-folder-o"></i>
  <?php $title = $this->escape($displayData['item']->category_title); ?>
  <?php if ($displayData['params']->get('link_category') && $displayData['item']->catslug) : ?>
    <?php $url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($displayData['item']->catslug)) . '" itemprop="genre">' . $title . '</a>'; ?>
    <?php echo JText::_( $url); ?>
  <?php else : ?>
    <?php echo JText::_( '<span itemprop="genre">' . $title . '</span>'); ?>
  <?php endif; ?>
</dd>