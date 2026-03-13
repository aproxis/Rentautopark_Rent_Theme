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

defined('_JEXEC') OR die('Restricted Area');
use Joomla\CMS\Language\Text;

$cars=$this->cars;
$category=$this->category;
$vrc_tn=$this->vrc_tn;
$navig=$this->navig;

$currencysymb = vikrentcar :: getCurrencySymb();

if(is_array($category)) {
	?>
	<h3 class="vrcclistheadt"><?php echo $category['name']; ?></h3>
	<?php
	if(strlen($category['descr']) > 0) {
		?>
		<div class="vrccatdescr">
			<?php echo $category['descr']; ?>
		</div>
		<?php
	}
}else {
	echo vikrentcar :: getFullFrontTitle($vrc_tn);
}

?>
<div class="vrc-search-results-block">
<div class="gridlist">
	<span>View</span>
	<div class="view">
		<a href="javascript:void(0)" id="list" class="active"><i class="fa fa-th-list"></i></a>
		<a href="javascript:void(0)" id="grid" ><i class="fa fa-th icon-white"></i></a>
	</div>
</div>
<?php
foreach($cars as $c) {
	$carats = vikrentcar::getCarCaratOriz($c['idcarat'], array(), $vrc_tn);
	$vcategory = vikrentcar::sayCategory($c['idcat'], $vrc_tn);
	?>
	<div class="car_result row">

		<!-- Begin: Car thumbnail -->
		<div class="vrc-car-thumb col-md-4 col-xs-12">
		<?php
		if(!empty($c['img'])) {
			$imgpath = file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_vikrentcar'.DS.'resources'.DS.'vthumb_'.$c['img']) ? 'administrator/components/com_vikrentcar/resources/vthumb_'.$c['img'] : 'administrator/components/com_vikrentcar/resources/'.$c['img'];
			?>
			<img class="imgresult" alt="<?php echo $c['name']; ?>" src="<?php echo JURI::root().$imgpath; ?>"/>
			<?php
		}
		?>
    
      <div class="vrc-car-price">
      <?php if($c['cost'] > 0) { ?>
        <span class="vrcstartfrom"><?php echo Text::_('VRCLISTSFROM'); ?></span>
        <span class="car_cost"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo strlen($c['startfrom']) > 0 ? vikrentcar::numberFormat($c['startfrom']) : vikrentcar::numberFormat($c['cost']); ?></span></span>
      <?php } ?>
      </div>
		</div>
		<!-- End: Car thumbnail -->
    
    <!-- Begin: Car info -->
		<div class="vrc-car-result-info col-md-8 col-xs-12">
			<div class="inner">
				<h3 class="vrc-car-name"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$c['id']); ?>"><?php echo $vcategory; ?><?php echo strlen($vcategory) > 0 ? ':' : ''; ?> <?php echo $c['name']; ?></a></h3>
				<div class="vrc-car-result-description">
				<?php
				if(!empty($c['short_info'])) {
					//BEGIN: Joomla Content Plugins Rendering
					JPluginHelper::importPlugin('content');
					$myItem =JTable::getInstance('content');
          if(version_compare(JVERSION, '4', 'ge')) {
            $dispatcher = JFactory::getApplication()->getDispatcher();
          }else {
            $dispatcher =JDispatcher::getInstance();
          }
					$myItem->text = $c['short_info'];
					// $dispatcher->trigger('onContentPrepare', array('com_vikrentcar.carslist', &$myItem, &$params, 0));
					JFactory::getApplication()->triggerEvent('onContentPrepare', array('com_vikrentcar.carslist', &$myItem, &$params, 0));
					$c['short_info'] = $myItem->text;
					//END: Joomla Content Plugins Rendering
					echo $c['short_info'];
				}else {
					echo (strlen(strip_tags($c['info'])) > 250 ? substr(strip_tags($c['info']), 0, 250).' ...' : $c['info']);
				}
				?>
				</div>
        
        <span class="readmore"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$c['id']); ?>"><i class="fa fa-angle-double-right"></i><?php echo Text::_('VRCLISTPICK'); ?></a></span>
			</div>

		</div>
		<!-- End: Car info -->

		<?php	if(!empty($carats)) {	?>
		<div class="vrc-car-characteristics">
			<?php echo $carats; ?>
		</div>
		<?php	} ?>
		
	</div>
	<?php } ?>
</div>

<?php
//pagination
if(strlen($navig) > 0) {
	?>
<div class="vrc-pagination"><?php echo $navig; ?></div>
	<?php
}

?>
<script type="text/javascript">
jQuery(document).ready(function() {
	if (jQuery('.car_result').length) {
		jQuery('.car_result').each(function() {
			var car_img = jQuery(this).find('.vrc-car-result-left').find('img');
			if(car_img.length) {
				jQuery(this).find('.vrc-car-result-right').find('.vrc-car-result-rightinner').find('.vrc-car-result-rightinner-deep').find('.vrc-car-result-inner').css('min-height', car_img.height()+'px');
			}
		});
	};
});
</script>

<script type="text/javascript">

jQuery(document).ready(function($){
	$('.gridlist a').click(function() {
		if ($(this).hasClass('active')) {
			return false;
		}
		//Make the button active or inactive
		$('#list,#grid').toggleClass('active');
		//Make sure the icon remains visible
		$('#list span,#grid span').toggleClass('icon-white');
		//Now add a class 'list-group-item' to all products containers when we want to display in a list mode.
		$('.car_result').toggleClass('car_result_grid');
		$('.vrc-search-results-block').toggleClass('vrc-search-results-grid');

		// add cookie
		if ($('#grid').hasClass('active')) {
			$.cookie('gridlist', 'grid', { path: '/' });
		} else {
			$.cookie('gridlist', 'list', { path: '/' });
		}

		return false;
	});

	// check cookie
	if ($.cookie('gridlist') && $.cookie('gridlist') == 'grid') {
		$('#grid').addClass('active');
		$('#list').removeClass('active');
		$('#grid span').addClass('icon-white');
		$('.car_result').addClass('car_result_grid');
		$('.vrc-search-results-block').addClass('vrc-search-results-grid');
	}
});
</script>