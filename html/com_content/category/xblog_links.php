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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>


<section class="items-more">
	<h3><?php echo Text::_('COM_CONTENT_MORE_ARTICLES'); ?></h3>
	<ol class="nav">
		<?php
		foreach ($this->link_items as &$item) :
			?>
			<li>
				<a href="<?php echo Route::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)); ?>">
					<?php echo $item->title; ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
