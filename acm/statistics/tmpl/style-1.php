<?php
/**
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
?>

<?php if($module->showtitle || $helper->get('block-intro')): ?>
<h3 class="section-title ">
	<?php if($module->showtitle): ?>
		<span><?php echo $module->title ?></span>
	<?php endif; ?>
	<?php if($helper->get('block-intro')): ?>
		<p class="container-sm section-intro hidden-xs"><?php echo $helper->get('block-intro'); ?></p>
	<?php endif; ?>	
</h3>
<?php endif; ?>

<div class="uber-stats style-1 <?php echo $helper->get('acm-style'); ?>">
  <ul class="stats-list row">
    <?php $count=$helper->getRows('data-style-1.stats-count'); ?>
    <?php for ($i=0; $i<$count; $i++) : ?>
    <?php if ($helper->get ('data-style-1.stats-count', $i)) : ?>
    <?php 
    	$colNumber = 2;
			if($count<12 && (12%$count==0)) {
				$colNumber = $count;
			} elseif(12%$count!=0) {
				$colNumber = $count-1;
			}
		?>
    <li class="col-md-<?php echo (12/$colNumber) ?> col-sm-6 stats-asset <?php if($i==($count-1)): ?> last-child <?php endif; ?>" >
    <span class="stats-item-icon">
    	<i class="fa <?php echo $helper->get ('data-style-1.font-icon', $i) ?>"></i>
    </span>
    
    <span class="stats-item-counter" data-to="<?php echo $helper->get ('data-style-1.stats-count', $i) ?>" data-from="0" data-speed="2000" data-refresh-interval="20">
			<?php echo $helper->get ('data-style-1.stats-count', $i) ?>
		</span>
		
    <?php if ($helper->get ('data-style-1.stats-name', $i)) : ?>
      <h3 class="stats-subject"><?php echo $helper->get ('data-style-1.stats-name', $i) ?></h3>
    <?php endif; ?>
    <?php if ($helper->get ('data-style-1.stats-desc', $i)) : ?>
      <p class="stats-description"><?php echo $helper->get ('data-style-1.stats-desc', $i) ?></p>
    <?php endif; ?>
    </li>
    <?php endif; ?>
  <?php endfor;?>
  </ul>
</div>