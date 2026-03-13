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

<?php 
	jimport( 'joomla.application.module.helper' );
	$featuresImg 					= $helper->get('block-bg');
	$fullWidth 						= $helper->get('full-width');
	$featuresBackground  	= 'background-image: url("'.$featuresImg.'"); background-repeat: no-repeat; background-size: cover; background-position: center center;';
	$moduleParams 				= new JRegistry();
  $moduleParams->loadString($module->params);
  $moduleIntro 					= $moduleParams->get('module-intro', ''); 
  $specialTitle         = $moduleParams->get('module-specical-title','0');
?>
<div class="row">
  <?php if($specialTitle): ?>
  	<?php if($module->showtitle || $helper->get('block-intro')): ?>
  	<div class="col-xs-12 col-sm-3 section-header">
  		<?php if($module->showtitle): ?>
  		<h3><span><?php echo $module->title ?></span></h3>
  		<?php endif; ?>

  		<?php if($moduleIntro): ?>
  			<p class="container-sm module-intro"><?php echo $moduleIntro; ?></p>
  		<?php endif; ?>	

  		<?php if($helper->get('block-intro')): ?>
  			<p class="container-sm block-intro"><?php echo $helper->get('block-intro'); ?></p>
  		<?php endif; ?>	

  		<?php if($helper->get('btn-value')): ?>
  			<a class="btn btn-lg btn-feature btn-decor <?php echo $helper->get('btn-class'); ?>" href="<?php echo $helper->get('btn-link'); ?>">
  				<?php if($helper->get('btn-icon')): ?>
  					<i class="fa <?php echo $helper->get('btn-icon'); ?>"></i>
  				<?php endif; ?>
  				<?php echo $helper->get('btn-value'); ?>
  			</a>
  		<?php endif; ?>
  	</div>
	 <?php endif; ?>
  <?php endif; ?>
	<div id="acm-features<?php echo $module->id;?>" class="col-xs-12 <?php if($specialTitle): ?> col-sm-9 <?php endif; ?> acm-features <?php echo $helper->get('features-style'); ?> style-1">
			<div class="row equal-height equal-height-child owl-carousel owl-theme">
			<?php $count = $helper->getRows('data.title'); ?>
			<?php for ($i=0; $i<$count; $i++) : ?>
			
				<div class="features-item">
					<div class="inner">
						<?php if($helper->get('data.font-icon', $i)) : ?>
							<div class="font-icon">
								<i class="<?php echo $helper->get('data.font-icon', $i) ; ?>"></i>
							</div>
						<?php endif ; ?>
		
						<?php if($helper->get('data.img-icon', $i)) : ?>
							<div class="img-icon">
								<img src="<?php echo $helper->get('data.img-icon', $i) ?>" alt="" />
							</div>
						<?php endif ; ?>
						
						<?php if($helper->get('data.title', $i)) : ?>
							<h3><?php echo $helper->get('data.title', $i) ?></h3>
						<?php endif ; ?>
						
						<?php if($helper->get('data.description', $i)) : ?>
							<p><?php echo $helper->get('data.description', $i) ?></p>
						<?php endif ; ?>
					</div>
				</div>
			<?php endfor ?>
			</div>
	</div>
</div>

<script>
function set_carousel_thememagic () {
    jQuery("#acm-features<?php echo $module->id;?> .owl-carousel").owlCarousel({
      navigation: true,
      pagination: false,
      items: <?php echo $helper->get('columns'); ?>,
      loop: false,
      scrollPerPage : true,
      responsiveClass:true,
      responsive:{
          0:{
              items:1,
              nav:true
          },
          600:{
              items:2,
              nav:false
          },
          1000:{
              items:3,
              nav:true,
              loop:false
          }
      }
      

    });
}
jQuery(window).on('load',function() {
	setTimeout("set_carousel_thememagic();", 1000);
});

</script>