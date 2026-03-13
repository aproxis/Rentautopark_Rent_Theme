<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_cars
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

// no direct access
defined('_JEXEC') or die;

$currencysymb = $params->get('currency');
$get_cars_layout = $params->get('layoutlist');
$widthroom = $params->get('widthroom');

$numb_total = $params->get('numb');
$numb_xrow = $params->get('numb_carrow');
$autoplayparam = $params->get('autoplay');

if($numb_xrow != 0 ) {
  $calc_item_width = 100 / $numb_xrow;
}

if($autoplayparam == 1) {
	$autoplayparam_status = "true";
	
} else {
	$autoplayparam_status = "false";
}

$pagination = $params->get('pagination');

if($pagination == 1) {
	$pagination_status = "true";
} else {
	$pagination_status = "false";
}

$navigation = $params->get('navigation');

if($navigation == 1) {
	$navigation_status = "true";
} else {
	$navigation_status = "false";
}

if ($get_cars_layout == 1) {
	$document = JFactory::getDocument();
	$document->addStyleSheet(JURI::root().'modules/mod_vikrentcar_cars/src/owl.carousel.min.css');
	//$document->addStyleSheet(JURI::root().'modules/mod_vikrentcar_cars/src/owl.theme.css');

	if (intval($params->get('loadjq')) == 1 ) {
		JHtml::_('jquery.framework', true, true);
		JHtml::_('script', JURI::root().'modules/mod_vikrentcar_cars/src/jquery.min.js');
	}
	JHtml::_('script', JURI::root().'modules/mod_vikrentcar_cars/src/owl.carousel.min.js');
}

$randid = isset($module) && is_object($module) && property_exists($module, 'id') ? $module->id : rand(1, 999);

	
?>
<div class="vrcmodcarsgridcontainer column-container <?php echo ($get_cars_layout) ? 'wrap ' : 'container-fluid'; ?>">
	<div>
		<div id="vrc-modcars-<?php echo $randid; ?>" class="<?php echo ($get_cars_layout) ? 'owl-carousel ' : ''; ?>vrcmodcarsgridcont-items vrcmodcarsgridhorizontal row-fluid">
		<?php
		foreach ($cars as $c) {

			$carats = modvikrentcar_carsHelper::getCarCaratOriz($c['idcarat'], array(), modvikrentcar_carsHelper::getTranslator());
			?>
			<div class="vrc-modcars-item <?php echo ($get_cars_layout) ? '' : 'vrc-modcars-grid-item'; ?>" style="<?php echo ($get_cars_layout) ? '' : 'width: '.$calc_item_width.'%;' ; ?>" data-groups='["<?php echo $c['catname']; ?>"]'>

				<figure class="vrcmodcarsgridcont-item">
					<div class="vrcmodcarsgridboxdiv">
            <div class="vrc-car-thumb">	
						<?php
						if (!empty($c['img'])) {
						?>
						<img src="<?php echo JURI::root(); ?>administrator/components/com_vikrentcar/resources/<?php echo $c['img']; ?>" class="vrcmodcarsgridimg"/>
						<?php
						}
						?>
            <?php
						if ($c['cost'] > 0) {
						?>
						<div class="vrcmodcarsgrid-box-cost">
							<span class="vrcmodcarsgridstartfrom"><?php echo JText::_('VRCMODCARSTARTFROM'); ?></span>
							<span class="vrcmodcarsgridcarcost"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo modvikrentcar_carsHelper::numberFormat($c['cost']); ?></span></span>
						</div>
						<?php
						}
						?>
            <figcaption class="vrcmodcarsgrid-item_title">
              <a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$c['id'].'&Itemid='.$params->get('itemid')); ?>">
                <?php echo $c['name']; ?>
              </a>
            </figcaption>
            </div>
					</div>	
				</figure>
			</div>
			<?php
		}
		?>
		</div>
	</div>
</div>

<?php if ($get_cars_layout == 1) { ?>
	<script type="text/javascript">
	jQuery(document).ready(function(){ 
		jQuery("#vrc-modcars-<?php echo $randid; ?>").owlCarousel({
			items : <?php echo $numb_xrow; ?>,
			autoplay : <?php echo $autoplayparam_status; ?>,
			nav :  <?php echo $navigation_status; ?>,
			dots : <?php echo $pagination_status; ?>,
			lazyLoad : true,
			loop: true,
			rewind: false,
      rtl: true,
			responsiveClass:true,
			responsive:{
		        0:{
		            items:1,
		            nav:true
		        },
				600:{
		            items:1,
		            nav:true
		        },
				800:{
		            items:2,
		            nav:true
		        },
				1024: {
					items: <?php echo $numb_xrow; ?>,
					nav: true
				}
		    }
		});

		<?php if($navigation_status == "false") { ?>
			jQuery("#vrc-modcars-<?php echo $randid; ?> .owl-nav").addClass('owl-disabled');
		<?php } ?>
	});
	</script>
<?php } ?>
