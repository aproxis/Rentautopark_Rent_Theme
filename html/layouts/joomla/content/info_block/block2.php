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

$blockPosition = $displayData['params']->get('info_block_position', 0);

?>
	<dl class="article-info ">

			<?php if ($displayData['params']->get('show_author') && !empty($displayData['item']->author )) : ?>	
				<?php echo JLayoutHelper::render('joomla.content.info_block.author', $displayData); ?>
			<?php endif; ?>


			<?php if ($displayData['params']->get('show_category')) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.category', $displayData); ?>
			<?php endif; ?>

			<?php if ($displayData['params']->get('show_publish_date')) : ?> 
				<?php echo JLayoutHelper::render('joomla.content.info_block.publish_date', $displayData); ?>
			<?php endif; ?>
			
			<?php if ($displayData['params']->get('show_hits')) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.hits', $displayData); ?>
			<?php endif; ?>

	</dl>
