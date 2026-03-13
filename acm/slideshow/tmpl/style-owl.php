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
  //Get parameter
  $navigation = $helper->get('navigation');
  $pagination = $helper->get('pagination');
  
	if($helper->count('title') >= $helper->count('description')) {
		$count = $helper->count('title');
	} else {
		$count = $helper->count('description');
	}
?>

<div class="section-inner ">
  <div class="acm-slideshow bg-slideshow">
  	<div id="acm-slideshow-<?php echo $module->id; ?>">
  		<div class="owl-carousel owl-theme">
  				<?php for ($i=0; $i<$count; $i++) : ?>
  				<div class="item">
            <?php if($helper->get('image', $i)): ?>
              <img src="<?php echo $helper->get('image', $i); ?>" class="slider-img" alt="<?php echo $helper->get('title', $i) ?>">
            <?php endif; ?>
            <div class="container slider-content">
              <?php if($helper->get('data.title', $i)): ?>
                <h1 class="item-title"><?php echo $helper->get('title', $i) ?></h1>
              <?php endif; ?>
              
    					<?php if($helper->get('description', $i)): ?>
    						<p class="item-desc"><?php echo $helper->get('description', $i) ?></p>
    					<?php endif; ?>
              <?php if($helper->get('button', $i)): ?>
                <a href="<?php echo $helper->get('button-link', $i) ?>" class="slider-btn btn btn-decor btn-lg btn-primary"><?php echo $helper->get('button', $i) ?></a>
              <?php endif; ?>
            </div>
  				</div>
  			 	<?php endfor ;?>
  		</div>
  	</div>
  </div>
</div>

<script>
(function($){
  jQuery(document).ready(function($) {
    $("#acm-slideshow-<?php echo $module->id; ?> .owl-carousel").owlCarousel({
      slideSpeed : 300,
      paginationSpeed : 400,
      singleItem:true,
      pagination: <?php echo ($pagination == 1 ? 'true':'false'); ?>,
      navigation: <?php echo ($navigation == 1 ? 'true':'false'); ?>,
      autoPlay: false,
    });
  });
})(jQuery);

</script>