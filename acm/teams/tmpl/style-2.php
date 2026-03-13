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
  $count = $helper->getRows('member_info.member-name');
  $col = $helper->get('number_col');
?>

<div class="acm-teams">
	<div class="style-2 team-items">
		<?php
      for ($i=0; $i < $count; $i++) :
        if ($i%$col==0) echo '<div class="row">'; 
    ?>
		<div class="item col-sm-6 col-md-<?php echo (12/$col); ?>">
      <div class="item-inner">
    
        <div class="member-image">
          <img src="<?php echo $helper->get('member_info.member-image', $i); ?>" alt="<?php echo $helper->get('member_info.member-name', $i); ?>" />
        </div>
        <h4><?php echo $helper->get('member_info.member-name', $i); ?></h4>
        <p class="member-title"><?php echo $helper->get('member_info.member-position', $i); ?></p>
        <p class="member-phone"><i class="fa fa-phone"></i><?php echo $helper->get('member_info.member-phone', $i); ?></p>
        <p class="member-email"><i class="fa fa-envelope-o"></i><?php echo $helper->get('member_info.member-email', $i); ?></p>
        <a class="btn btn-lg btn-decor btn-primary member-link" href="<?php echo $helper->get('member_info.member-link', $i); ?>"><i class="fa fa-user"></i><?php echo JText::_( 'TPL_MEET' ); ?></a>
      </div>
		</div>
    
    <?php if ( ($i%$col==($col-1)) || $i==($count-1) )  echo '</div>'; ?>
		<?php endfor; ?>
	</div>
  
</div>
