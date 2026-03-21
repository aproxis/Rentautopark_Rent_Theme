<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/oconfirm/default.php
 *
 * Changes vs. component default:
 *  - Loads oconfirm-modal.css when rendered inside the booking modal (tmpl=component)
 *  - Hides the stepbar in modal mode
 *  - Adds target="_top" to the main saveorder form & coupon form
 *    (so form submissions redirect the parent window, not just the iframe)
 *  - Hides the coupon input when a couponcode was pre-applied via URL
 *  - Adds data-vrc-field-type attributes on email/name cfields for JS detection
 *  - Adds optional "Creează cont" registration section at the bottom of the form
 *  - JS intercepts submit: if registration checkbox checked, first calls
 *    register-ajax.php, then submits the booking form
 *  - Back button in modal mode closes the parent modal overlay
 */

defined('_JEXEC') OR die('Restricted Area');

$car             = $this->car;
$price           = $this->price;
$selopt          = $this->selopt;
$days            = $this->days;
$calcdays        = $this->calcdays;
if ((int)$days != (int)$calcdays) {
	$origdays = $days;
	$days = $calcdays;
}
$coupon          = $this->coupon;
$first           = $this->first;
$second          = $this->second;
$ftitle          = $this->ftitle;
$place           = $this->place;
$returnplace     = $this->returnplace;
$payments        = $this->payments;
$cfields         = $this->cfields;
$customer_details = $this->customer_details;
$countries       = $this->countries;
$vrc_tn          = $this->vrc_tn;

$vrc_app  = VikRentCar::getVrcApplication();
$session  = JFactory::getSession();
$document = JFactory::getDocument();

if (VikRentCar::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
}
$document->addStyleSheet(VRC_SITE_URI . 'resources/jquery-ui.min.css');
JHtml::_('script', VRC_SITE_URI . 'resources/jquery-ui.min.js');

if (is_array($cfields)) {
	foreach ($cfields as $cf) {
		if (!empty($cf['poplink'])) {
			$mbox_opts = '{type:"iframe",iframe:{css:{width:"70%",height:"75%"}}}';
			$vrc_app->prepareModalBox('.vrcmodal', $mbox_opts);
			break;
		}
	}
}

$currencysymb = VikRentCar::getCurrencySymb();
$nowdf        = VikRentCar::getDateFormat();
$nowtf        = VikRentCar::getTimeFormat();
if ($nowdf == "%d/%m/%Y") {
	$df    = 'd/m/Y';
	$juidf = 'dd/mm/yy';
} elseif ($nowdf == "%m/%d/%Y") {
	$df    = 'm/d/Y';
	$juidf = 'mm/dd/yy';
} else {
	$df    = 'Y/m/d';
	$juidf = 'yy/mm/dd';
}

$tok = "";
if (VikRentCar::tokenForm()) {
	$vikt = uniqid(rand(17, 1717), true);
	$session->set('vikrtoken', $vikt);
	$tok = "<input type=\"hidden\" name=\"viktoken\" value=\"" . $vikt . "\"/>\n";
}

$pitemid    = VikRequest::getInt('Itemid', '', 'request');
$ptmpl      = VikRequest::getString('tmpl', '', 'request');
$isModal    = ($ptmpl === 'component');

// Was a coupon pre-applied via URL param?
$preAppliedCoupon = VikRequest::getString('couponcode', '', 'request');

$carats  = VikRentCar::getCarCaratOriz($car['idcarat'], array(), $vrc_tn);
$imp     = VikRentCar::sayCostMinusIva($price['cost'], $price['idprice']);
$totdue  = VikRentCar::sayCostPlusIva($price['cost'],  $price['idprice']);
$saywithout = $imp;
$saywith    = $totdue;

$wop = "";
if (is_array($selopt)) {
	foreach ($selopt as $selo) {
		$wop .= $selo['id'] . ":" . $selo['quan'] . ";";
		$realcost    = intval($selo['perday']) == 1 ? ($selo['cost'] * $days * $selo['quan']) : ($selo['cost'] * $selo['quan']);
		$basequancost = intval($selo['perday']) == 1 ? ($selo['cost'] * $days) : $selo['cost'];
		if (!empty($selo['maxprice']) && $selo['maxprice'] > 0 && $basequancost > $selo['maxprice']) {
			$realcost = $selo['maxprice'];
			if (intval($selo['hmany']) == 1 && intval($selo['quan']) > 1) {
				$realcost = $selo['maxprice'] * $selo['quan'];
			}
		}
		$imp    += VikRentCar::sayOptionalsMinusIva($realcost, $selo['idiva']);
		$totdue += VikRentCar::sayOptionalsPlusIva($realcost, $selo['idiva']);
	}
}

// Register-AJAX URL
$registerAjaxUrl = JURI::root() . 'templates/rent/php/register-ajax.php';
?>

<?php if ($isModal): ?>
<link rel="stylesheet" href="<?php echo JURI::root(); ?>templates/rent/css/oconfirm-modal.css"/>
<?php endif; ?>

<?php /* ── Stepbar — hidden in modal, shown in full-page context ── */ ?>
<?php if (!$isModal): ?>
<div class="vrcstepsbarcont">
	<ol class="vrc-stepbar" data-vrcsteps="4">
		<li class="vrc-step vrc-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=vikrentcar&pickup='.$first.'&return='.$second.(!empty($pitemid)?'&Itemid='.$pitemid:'')); ?>"><?php echo JText::_('VRSTEPDATES'); ?></a></li>
		<li class="vrc-step vrc-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=search&place='.$place.'&pickupdate='.urlencode(date($df,$first)).'&pickuph='.date('H',$first).'&pickupm='.date('i',$first).'&releasedate='.urlencode(date($df,$second)).'&releaseh='.date('H',$second).'&releasem='.date('i',$second).'&returnplace='.$returnplace.(!empty($pitemid)?'&Itemid='.$pitemid:''),false); ?>"><?php echo JText::_('VRSTEPCARSELECTION'); ?></a></li>
		<li class="vrc-step vrc-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=showprc&caropt='.$car['id'].'&pickup='.$first.'&release='.$second.'&place='.$place.'&returnplace='.$returnplace.'&days='.$days.(!empty($pitemid)?'&Itemid='.$pitemid:''),false); ?>"><?php echo JText::_('VRSTEPOPTIONS'); ?></a></li>
		<li class="vrc-step vrc-step-current"><span><?php echo JText::_('VRSTEPCONFIRM'); ?></span></li>
	</ol>
</div>
<?php endif; ?>

<?php if ($isModal): ?>
<div class="vrc-modal-header">
	<h2 class="vrc-modal-title"><?php echo JText::_('VRRIEPILOGOORD'); ?></h2>
</div>
<?php else: ?>
<h2 class="vrc-rental-summary-title"><?php echo JText::_('VRRIEPILOGOORD'); ?></h2>
<?php endif; ?>

<?php
$pickloc = VikRentCar::getPlaceInfo($place,       $vrc_tn);
$droploc = VikRentCar::getPlaceInfo($returnplace,  $vrc_tn);
?>

<div class="vrcinfocarcontainer">
	<div class="vrcrentforlocs">
		<div class="vrcrentalfor">
		<?php if (array_key_exists('hours', $price)): ?>
			<h3 class="vrcrentalforone"><?php echo JText::_('VRRENTAL'); ?> <?php echo $car['name']; ?> <?php echo JText::_('VRFOR'); ?> <?php echo (intval($price['hours'])==1?"1 ".JText::_('VRCHOUR'):$price['hours']." ".JText::_('VRCHOURS')); ?></h3>
		<?php else: ?>
			<h3 class="vrcrentalforone"><?php echo JText::_('VRRENTAL'); ?> <?php echo $car['name']; ?> <?php echo JText::_('VRFOR'); ?> <?php echo (intval($days)==1?"1 ".JText::_('VRDAY'):$days." ".JText::_('VRDAYS')); ?></h3>
		<?php endif; ?>
		</div>

		<div class="vrc-itinerary-confirmation">
			<div class="vrc-itinerary-pickup">
				<h4><?php echo JText::_('VRPICKUP'); ?></h4>
				<?php if (count($pickloc)): ?>
				<div class="vrc-itinerary-pickup-location">
					<?php VikRentCarIcons::e('location-arrow', 'vrc-pref-color-text'); ?>
					<div class="vrc-itinerary-pickup-locdet">
						<span class="vrc-itinerary-pickup-locname"><?php echo $pickloc['name']; ?></span>
						<span class="vrc-itinerary-pickup-locaddr"><?php echo $pickloc['address']; ?></span>
					</div>
				</div>
				<?php endif; ?>
				<div class="vrc-itinerary-pickup-date">
					<?php VikRentCarIcons::e('calendar', 'vrc-pref-color-text'); ?>
					<span class="vrc-itinerary-pickup-date-day"><?php echo date($df, $first); ?></span>
					<span class="vrc-itinerary-pickup-date-time"><?php echo date($nowtf, $first); ?></span>
				</div>
			</div>
			<div class="vrc-itinerary-dropoff">
				<h4><?php echo JText::_('VRRETURN'); ?></h4>
				<?php if (count($droploc)): ?>
				<div class="vrc-itinerary-dropoff-location">
					<?php VikRentCarIcons::e('location-arrow', 'vrc-pref-color-text'); ?>
					<div class="vrc-itinerary-dropfff-locdet">
						<span class="vrc-itinerary-dropoff-locname"><?php echo $droploc['name']; ?></span>
						<span class="vrc-itinerary-dropoff-locaddr"><?php echo $droploc['address']; ?></span>
					</div>
				</div>
				<?php endif; ?>
				<div class="vrc-itinerary-dropoff-date">
					<?php VikRentCarIcons::e('calendar', 'vrc-pref-color-text'); ?>
					<span class="vrc-itinerary-dropoff-date-day"><?php echo !array_key_exists('hours', $price) ? date($df, $second) : ''; ?></span>
					<span class="vrc-itinerary-dropoff-date-time"><?php echo date($nowtf, $second); ?></span>
				</div>
			</div>
		</div>
	</div>

	<?php if (!empty($car['img'])): ?>
	<div class="vrc-summary-car-img">
		<img src="<?php echo VRC_ADMIN_URI; ?>resources/<?php echo $car['img']; ?>"/>
	</div>
	<?php endif; ?>
</div>

<div class="vrc-oconfirm-summary-container">
	<div class="vrc-oconfirm-summary-car-wrapper">
		<div class="vrc-oconfirm-summary-car-head">
			<div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-descr"><span></span></div>
			<div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-days"><span><?php echo (array_key_exists('hours',$price)?JText::_('VRCHOURS'):JText::_('VRDAYS')); ?></span></div>
			<div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-net"><span><?php echo JText::_('ORDNOTAX'); ?></span></div>
			<div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-tax"><span><?php echo JText::_('ORDTAX'); ?></span></div>
			<div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-tot"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div>
		</div>

		<div class="vrc-oconfirm-summary-car-row">
			<div class="vrc-oconfirm-summary-car-cell-descr">
				<div class="vrc-oconfirm-carname vrc-pref-color-text"><?php echo $car['name']; ?></div>
				<div class="vrc-oconfirm-priceinfo"><?php echo VikRentCar::getPriceName($price['idprice'],$vrc_tn).(!empty($price['attrdata'])?"<br/>".VikRentCar::getPriceAttr($price['idprice'],$vrc_tn).": ".$price['attrdata']:""); ?></div>
			</div>
			<div class="vrc-oconfirm-summary-car-cell-days">
				<div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo (array_key_exists('hours',$price)?JText::_('VRCHOURS'):JText::_('VRDAYS')); ?></span></div>
				<span><?php echo (array_key_exists('hours',$price)?$price['hours']:$days); ?></span>
			</div>
			<div class="vrc-oconfirm-summary-car-cell-net">
				<div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDNOTAX'); ?></span></div>
				<span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
				<span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($saywithout); ?></span></span>
			</div>
			<div class="vrc-oconfirm-summary-car-cell-tax">
				<div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDTAX'); ?></span></div>
				<span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
				<span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($saywith - $saywithout); ?></span></span>
			</div>
			<div class="vrc-oconfirm-summary-car-cell-tot">
				<div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div>
				<span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
				<span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($saywith); ?></span></span>
			</div>
		</div>
<?php
if (is_array($selopt)) {
	foreach ($selopt as $aop) {
		$thisoptcost  = intval($aop['perday']) == 1 ? ($aop['cost'] * $aop['quan']) * $days : $aop['cost'] * $aop['quan'];
		$basequancost = intval($aop['perday']) == 1 ? ($aop['cost'] * $days) : $aop['cost'];
		if (!empty($aop['maxprice']) && $aop['maxprice'] > 0 && $basequancost > $aop['maxprice']) {
			$thisoptcost = $aop['maxprice'];
			if (intval($aop['hmany']) == 1 && intval($aop['quan']) > 1) { $thisoptcost = $aop['maxprice'] * $aop['quan']; }
		}
		$optwithout = VikRentCar::sayOptionalsMinusIva($thisoptcost, $aop['idiva']);
		$optwith    = VikRentCar::sayOptionalsPlusIva($thisoptcost,  $aop['idiva']);
		$opttax     = $optwith - $optwithout;
		?>
		<div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-option-row">
			<div class="vrc-oconfirm-summary-car-cell-descr"><div class="vrc-oconfirm-optname"><?php echo $aop['name'].($aop['quan']>1?" <small>(x ".$aop['quan'].")</small>":""); ?></div></div>
			<div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-net"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDNOTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($optwithout); ?></span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tax"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($opttax); ?></span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tot"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($optwith); ?></span></span></div>
		</div>
		<?php
	}
}
$days = intval($days);
if (!empty($place) && !empty($returnplace)) {
	$locfee = VikRentCar::getLocFee($place, $returnplace);
	if ($locfee) {
		if (strlen($locfee['losoverride']) > 0) {
			$arrvaloverrides = array();
			foreach (explode('_', $locfee['losoverride']) as $valovr) {
				if (!empty($valovr)) { $ovrinfo = explode(':', $valovr); $arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1]; }
			}
			if (array_key_exists($days, $arrvaloverrides)) { $locfee['cost'] = $arrvaloverrides[$days]; }
		}
		$locfeecost    = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $days) : $locfee['cost'];
		$locfeewithout = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva']);
		$locfeewith    = VikRentCar::sayLocFeePlusIva($locfeecost,  $locfee['idiva']);
		$locfeetax     = $locfeewith - $locfeewithout;
		$imp    += $locfeewithout;
		$totdue += $locfeewith;
		?>
		<div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-fee-row">
			<div class="vrc-oconfirm-summary-car-cell-descr"><div class="vrc-oconfirm-feename"><?php echo JText::_('VRLOCFEETOPAY'); ?></div></div>
			<div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-net"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDNOTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeewithout); ?></span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tax"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeetax); ?></span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tot"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeewith); ?></span></span></div>
		</div>
		<?php
	}
}
$oohfee = VikRentCar::getOutOfHoursFees($place, $returnplace, $first, $second, $car);
if (count($oohfee) > 0) {
	$oohfeecost    = $oohfee['cost'];
	$oohfeewithout = VikRentCar::sayOohFeeMinusIva($oohfeecost, $oohfee['idiva']);
	$oohfeewith    = VikRentCar::sayOohFeePlusIva($oohfeecost,  $oohfee['idiva']);
	$oohfeetax     = $oohfeewith - $oohfeewithout;
	$imp    += $oohfeewithout;
	$totdue += $oohfeewith;
	$ooh_time  = $oohfee['pickup'] == 1 ? $oohfee['pickup_ooh'] : '';
	$ooh_time .= $oohfee['dropoff'] == 1 && $oohfee['dropoff_ooh'] != $oohfee['pickup_ooh'] ? (!empty($ooh_time)?', ':'').$oohfee['dropoff_ooh'] : '';
	?>
	<div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-fee-row">
		<div class="vrc-oconfirm-summary-car-cell-descr"><div class="vrc-oconfirm-feename"><?php echo JText::sprintf('VRCOOHFEETOPAY', $ooh_time); ?></div></div>
		<div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
		<div class="vrc-oconfirm-summary-car-cell-net"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDNOTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeewithout); ?></span></span></div>
		<div class="vrc-oconfirm-summary-car-cell-tax"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeetax); ?></span></span></div>
		<div class="vrc-oconfirm-summary-car-cell-tot"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeewith); ?></span></span></div>
	</div>
	<?php
}
?>
	</div><!-- /.vrc-oconfirm-summary-car-wrapper -->

<?php
$session->set('vikrentcar_ordertotal', $totdue);
$origtotdue  = $totdue;
$usedcoupon  = false;
if (is_array($coupon)) {
	$coupontotok = true;
	if (strlen($coupon['mintotord']) > 0 && $totdue < $coupon['mintotord']) { $coupontotok = false; }
	if ($coupontotok) {
		$usedcoupon = true;
		if ($coupon['percentot'] == 1) {
			$couponsave = $totdue * $coupon['value'] / 100;
			$totdue     = $totdue * (100 - $coupon['value']) / 100;
		} else {
			$couponsave = $coupon['value'];
			$totdue     = $totdue - $coupon['value'];
		}
	} else {
		VikError::raiseWarning('', JText::_('VRCCOUPONINVMINTOTORD'));
	}
}
?>
	<div class="vrc-oconfirm-summary-total-wrapper">
		<div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-total-row">
			<div class="vrc-oconfirm-summary-car-cell-descr"><div class="vrc-oconfirm-total-block"><?php echo JText::_('VRTOTAL'); ?></div></div>
			<div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-net"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-net"><span><?php echo JText::_('ORDNOTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($imp); ?></span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tax"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-tax"><span><?php echo JText::_('ORDTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat(($origtotdue-$imp)); ?></span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tot"><div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-tot"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($origtotdue); ?></span></span></div>
		</div>
		<?php if ($usedcoupon): ?>
		<div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-total-row vrc-oconfirm-summary-coupon-row">
			<div class="vrc-oconfirm-summary-car-cell-descr"><span><?php echo JText::_('VRCCOUPON'); ?> <?php echo $coupon['code']; ?></span></div>
			<div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-net"><span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tax"><span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tot"><span class="vrccurrency">- <span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($couponsave); ?></span></span></div>
		</div>
		<div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-total-row vrc-oconfirm-summary-coupon-newtot-row">
			<div class="vrc-oconfirm-summary-car-cell-descr"><div class="vrc-oconfirm-total-block"><?php echo JText::_('VRCNEWTOTAL'); ?></div></div>
			<div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-net"><span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tax"><span></span></div>
			<div class="vrc-oconfirm-summary-car-cell-tot"><span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span> <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($totdue); ?></span></span></div>
		</div>
		<?php endif; ?>
	</div>
</div><!-- /.vrc-oconfirm-summary-container -->

<div class="vrc-oconfirm-middlep">
<?php
// ── Coupon input — only when coupons are enabled AND no coupon already applied
// In modal mode, hide if the coupon was pre-applied via URL param
if (VikRentCar::couponsEnabled() && !is_array($coupon)) {
	$hideCouponBlock = $isModal && !empty($preAppliedCoupon);
	?>
	<div class="vrc-coupon-outer"<?php echo $hideCouponBlock ? ' style="display:none;"' : ''; ?>>
		<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid)?'&Itemid='.$pitemid:'')); ?>"
		      method="post"
		      target="_top">
			<div class="vrcentercoupon">
				<span class="vrchaveacoupon"><?php echo JText::_('VRCHAVEACOUPON'); ?></span>
				<input type="text" name="couponcode" value="<?php echo htmlspecialchars($preAppliedCoupon); ?>" size="20" class="vrcinputcoupon"/>
				<input type="submit" class="btn vrcsubmitcoupon vrc-pref-color-btn" name="applyacoupon" value="<?php echo JText::_('VRCSUBMITCOUPON'); ?>"/>
			</div>
			<input type="hidden" name="priceid"      value="<?php echo $price['idprice']; ?>"/>
			<input type="hidden" name="place"        value="<?php echo $place; ?>"/>
			<input type="hidden" name="returnplace"  value="<?php echo $returnplace; ?>"/>
			<input type="hidden" name="carid"        value="<?php echo $car['id']; ?>"/>
			<input type="hidden" name="days"         value="<?php echo $days; ?>"/>
			<input type="hidden" name="pickup"       value="<?php echo $first; ?>"/>
			<input type="hidden" name="release"      value="<?php echo $second; ?>"/>
			<?php if (is_array($selopt)): foreach ($selopt as $aop): ?>
			<input type="hidden" name="optid<?php echo $aop['id']; ?>" value="<?php echo $aop['quan']; ?>"/>
			<?php endforeach; endif; ?>
			<?php if (!empty($pitemid)): ?>
			<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
			<?php endif; ?>
			<?php if ($isModal): ?>
			<input type="hidden" name="tmpl" value="component"/>
			<?php endif; ?>
			<input type="hidden" name="task" value="oconfirm"/>
		</form>
	</div>
	<?php
}

// ── Customer PIN
if (VikRentCar::customersPinEnabled() && !VikRentCar::userIsLogged() && !(count($customer_details) > 0)) {
	?>
	<div class="vrc-enterpin-block">
		<div class="vrc-enterpin-top">
			<span><span><?php echo JText::_('VRRETURNINGCUSTOMER'); ?></span><?php echo JText::_('VRENTERPINCODE'); ?></span>
			<input type="text" id="vrc-pincode-inp" value="" size="6"/>
			<button type="button" class="btn vrc-pincode-sbmt vrc-pref-color-btn"><?php echo JText::_('VRAPPLYPINCODE'); ?></button>
		</div>
		<div class="vrc-enterpin-response"></div>
	</div>
	<script>
	jQuery(document).ready(function() {
		jQuery(".vrc-pincode-sbmt").click(function() {
			var pin_code = jQuery("#vrc-pincode-inp").val();
			jQuery(this).prop('disabled', true);
			jQuery(".vrc-enterpin-response").hide();
			jQuery.ajax({
				type: "POST",
				url: "<?php echo JRoute::_('index.php?option=com_vikrentcar&task=validatepin&tmpl=component'.(!empty($pitemid)?'&Itemid='.$pitemid:''), false); ?>",
				data: { pin: pin_code }
			}).done(function(res) {
				var pinobj = JSON.parse(res);
				if (pinobj.hasOwnProperty('success')) {
					jQuery(".vrc-enterpin-top").hide();
					jQuery(".vrc-enterpin-response").removeClass("vrc-enterpin-error").addClass("vrc-enterpin-success").html("<span class=\"vrc-enterpin-welcome\"><?php echo addslashes(JText::_('VRWELCOMEBACK')); ?></span><span class=\"vrc-enterpin-customer\">"+pinobj.first_name+" "+pinobj.last_name+"</span>").fadeIn();
					jQuery.each(pinobj.cfields, function(k, v) { if (jQuery("#vrcf-inp"+k).length) { jQuery("#vrcf-inp"+k).val(v); } });
					var user_country = pinobj.country;
					if (jQuery(".vrcf-countryinp").length && user_country.length) {
						jQuery(".vrcf-countryinp option").each(function(i){ if(jQuery(this).val().substring(0,3)==user_country){jQuery(this).prop("selected",true);return false;} });
					}
				} else {
					jQuery(".vrc-enterpin-response").addClass("vrc-enterpin-error").html("<p><?php echo addslashes(JText::_('VRINVALIDPINCODE')); ?></p>").fadeIn();
					jQuery(".vrc-pincode-sbmt").prop('disabled', false);
				}
			}).fail(function(){ alert('Error validating the PIN. Request failed.'); jQuery(".vrc-pincode-sbmt").prop('disabled', false); });
		});
	});
	</script>
	<?php
}
?>
</div>

<script type="text/javascript">
function vrcValidateEmail(email) {
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}
function checkvrcFields() {
	var vrvar = document.vrc;
	<?php
	if (@is_array($cfields)) {
		foreach ($cfields as $cf) {
			if (intval($cf['required']) == 1) {
				if ($cf['type'] == "text" || $cf['type'] == "textarea" || $cf['type'] == "date" || $cf['type'] == "country") {
					?>
			if (!vrvar.vrcf<?php echo $cf['id']; ?>.value.match(/\S/)) {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			} else { document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color=''; }
					<?php
					if ($cf['isemail'] == 1) {
						?>
			if (!vrcValidateEmail(vrvar.vrcf<?php echo $cf['id']; ?>.value)) {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			} else { document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color=''; }
					<?php
					}
				} elseif ($cf['type'] == "select") {
					?>
			if (!vrvar.vrcf<?php echo $cf['id']; ?>.value.match(/\S/)) {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			} else { document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color=''; }
				<?php
				} elseif ($cf['type'] == "checkbox") {
					?>
			if (vrvar.vrcf<?php echo $cf['id']; ?>.checked) {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='';
			} else { document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000'; return false; }
				<?php
				}
			}
		}
	}
	?>
	return true;
}
</script>

<?php /* ═══════════════════════════════════════════════════════════════
   MAIN ORDER FORM — target="_top" so that after saveorder the parent
   window navigates to the confirmation / payment page, not just the iframe.
   ═══════════════════════════════════════════════════════════════ */ ?>
<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid)?'&Itemid='.$pitemid:'')); ?>"
      name="vrc"
      method="post"
      target="_top"
      onsubmit="javascript: return checkvrcFields();">

<?php if (@is_array($cfields)): ?>
	<div class="vrccustomfields">
	<?php
	$currentUser  = JFactory::getUser();
	$useremail    = !empty($currentUser->email) ? $currentUser->email : "";
	$useremail    = array_key_exists('email', $customer_details) ? $customer_details['email'] : $useremail;
	$previousdata = VikRentCar::loadPreviousUserData($currentUser->id);
	$nominatives  = array();
	if (count($customer_details) > 0) {
		$nominatives[] = $customer_details['first_name'];
		$nominatives[] = $customer_details['last_name'];
	}

	foreach ($cfields as $cf) {
		$isreq = intval($cf['required']) == 1 ? "<span class=\"vrcrequired\"><sup>*</sup></span> " : "";
		$fname = !empty($cf['poplink'])
			? "<a href=\"" . $cf['poplink'] . "\" id=\"vrcf" . $cf['id'] . "\" rel=\"{handler:'iframe',size:{x:750,y:600}}\" target=\"_blank\" class=\"vrcmodal\">" . JText::_($cf['name']) . "</a>"
			: "<label id=\"vrcf" . $cf['id'] . "\" for=\"vrcf-inp" . $cf['id'] . "\">" . JText::_($cf['name']) . "</label>";

		// Data attributes to help the registration JS find email & name fields
		$extraDataAttr = '';
		if (intval($cf['isemail'])      == 1) { $extraDataAttr = ' data-vrc-field-type="email"'; }
		elseif (intval($cf['isnominative']) == 1) { $extraDataAttr = ' data-vrc-field-type="name"'; }
		elseif (intval($cf['isphone'])  == 1) { $extraDataAttr = ' data-vrc-field-type="phone"'; }

		if ($cf['type'] == "text") {
			$def_textval = '';
			if ($cf['isemail'] == 1) { $def_textval = $useremail; }
			elseif ($cf['isphone'] == 1) { if (array_key_exists('phone', $customer_details)) { $def_textval = $customer_details['phone']; } }
			elseif ($cf['isnominative'] == 1) { if (count($nominatives) > 0) { $def_textval = array_shift($nominatives); } }
			elseif (array_key_exists('cfields', $customer_details) && array_key_exists($cf['id'], $customer_details['cfields'])) { $def_textval = $customer_details['cfields'][$cf['id']]; }
			?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label"><?php echo $isreq; ?><?php echo $fname; ?></div>
				<div class="vrc-customfield-input">
				<?php if ($cf['isphone'] == 1 && method_exists($vrc_app, 'printPhoneInputField')): ?>
					<?php echo $vrc_app->printPhoneInputField(array('name'=>'vrcf'.$cf['id'],'id'=>'vrcf-inp'.$cf['id'],'value'=>$def_textval,'class'=>'vrcinput','size'=>'40',$extraDataAttr=>'')); ?>
				<?php else: ?>
					<input type="text" name="vrcf<?php echo $cf['id']; ?>" id="vrcf-inp<?php echo $cf['id']; ?>" value="<?php echo $def_textval; ?>" size="40" class="vrcinput"<?php echo $extraDataAttr; ?>/>
				<?php endif; ?>
				</div>
			</div>
			<?php
		} elseif ($cf['type'] == "textarea") {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			if (isset($customer_details['cfields']) && array_key_exists($cf['id'], $customer_details['cfields'])) { $defaultval = $customer_details['cfields'][$cf['id']]; }
			?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label"><?php echo $isreq; ?><?php echo $fname; ?></div>
				<div class="vrc-customfield-input"><textarea name="vrcf<?php echo $cf['id']; ?>" id="vrcf-inp<?php echo $cf['id']; ?>" rows="5" cols="30" class="vrctextarea"<?php echo $extraDataAttr; ?>><?php echo $defaultval; ?></textarea></div>
			</div>
			<?php
		} elseif ($cf['type'] == "date") {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label"><?php echo $isreq; ?><?php echo $fname; ?></div>
				<div class="vrc-customfield-input vrc-customfield-input-date"><input type="text" name="vrcf<?php echo $cf['id']; ?>" id="vrcf-inp<?php echo $cf['id']; ?>" value="<?php echo $defaultval; ?>" size="40" class="vrcinput"<?php echo $extraDataAttr; ?>/></div>
			</div>
			<script>jQuery(document).ready(function(){jQuery("#vrcf-inp<?php echo $cf['id']; ?>").datepicker({dateFormat:"<?php echo $juidf; ?>",changeMonth:true,changeYear:true,yearRange:"<?php echo (date('Y')-100).':'.(date('Y')+20); ?>"});});</script>
			<?php
		} elseif ($cf['type'] == "country" && is_array($countries)) {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			if (array_key_exists('country', $customer_details)) { $defaultval = !empty($customer_details['country']) ? substr($customer_details['country'], 0, 3) : ''; }
			$countries_sel = '<select name="vrcf'.$cf['id'].'" class="vrcf-countryinp"><option value=""></option>'."\n";
			foreach ($countries as $country) { $countries_sel .= '<option value="'.$country['country_3_code'].'::'.$country['country_name'].'"'.($defaultval==$country['country_3_code']?' selected="selected"':'').'>'.$country['country_name'].'</option>'."\n"; }
			$countries_sel .= '</select>';
			?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label"><?php echo $isreq; ?><?php echo $fname; ?></div>
				<div class="vrc-customfield-input"><?php echo $countries_sel; ?></div>
			</div>
			<?php
		} elseif ($cf['type'] == "select") {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			$answ = explode(";;__;;", $cf['choose']);
			$wcfsel = "<select name=\"vrcf".$cf['id']."\">\n";
			foreach ($answ as $aw) { if (!empty($aw)) { $wcfsel .= "<option value=\"".JText::_($aw)."\"".($defaultval==JText::_($aw)?' selected="selected"':'').">".JText::_($aw)."</option>\n"; } }
			$wcfsel .= "</select>\n";
			?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label"><?php echo $isreq; ?><?php echo $fname; ?></div>
				<div class="vrc-customfield-input"><?php echo $wcfsel; ?></div>
			</div>
			<?php
		} elseif ($cf['type'] == "separator") {
			$cfsepclass = strlen(JText::_($cf['name'])) > 30 ? "vrcseparatorcflong" : "vrcseparatorcf";
			?>
			<div class="vrcdivcustomfield vrccustomfldinfo"><div class="<?php echo $cfsepclass; ?>"><?php echo JText::_($cf['name']); ?></div></div>
			<?php
		} else {
			?>
			<div class="vrcdivcustomfield vrc-oconfirm-cfield-entry-checkbox">
				<div class="vrc-customfield-label"><?php echo $isreq; ?><?php echo $fname; ?></div>
				<div class="vrc-customfield-input"><input type="checkbox" name="vrcf<?php echo $cf['id']; ?>" id="vrcf-inp<?php echo $cf['id']; ?>" value="<?php echo JText::_('VRYES'); ?>"<?php echo $extraDataAttr; ?>/></div>
			</div>
			<?php
		}
	}
	?>
	</div>
<?php endif; ?>

	<input type="hidden" name="days"   value="<?php echo $days; ?>"/>
	<?php if (isset($origdays)): ?>
	<input type="hidden" name="origdays" value="<?php echo $origdays; ?>"/>
	<?php endif; ?>
	<input type="hidden" name="pickup"      value="<?php echo $first; ?>"/>
	<input type="hidden" name="release"     value="<?php echo $second; ?>"/>
	<input type="hidden" name="car"         value="<?php echo $car['id']; ?>"/>
	<input type="hidden" name="prtar"       value="<?php echo $price['id']; ?>"/>
	<input type="hidden" name="priceid"     value="<?php echo $price['idprice']; ?>"/>
	<input type="hidden" name="optionals"   value="<?php echo $wop; ?>"/>
	<input type="hidden" name="totdue"      value="<?php echo $totdue; ?>"/>
	<input type="hidden" name="place"       value="<?php echo $place; ?>"/>
	<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>"/>
	<?php if (array_key_exists('hours', $price)): ?>
	<input type="hidden" name="hourly" value="<?php echo $price['hours']; ?>"/>
	<?php endif; ?>
	<?php if ($usedcoupon && is_array($coupon)): ?>
	<input type="hidden" name="couponcode" value="<?php echo $coupon['code']; ?>"/>
	<?php endif; ?>
	<?php echo !empty($tok) ? $tok . JHtml::_('form.token') : ''; ?>
	<input type="hidden" name="task" value="saveorder"/>
	<?php if (!empty($pitemid)): ?>
	<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
	<?php endif; ?>
	<?php if ($isModal): ?>
	<input type="hidden" name="tmpl" value="component"/>
	<?php endif; ?>

<?php /* ── Payment methods ──────────────────────────────────────────── */ ?>
<?php if (is_array($payments)): ?>
	<div class="vrc-oconfirm-paym-block">
		<h4 class="vrc-medium-header"><?php echo JText::_('VRCHOOSEPAYMENT'); ?></h4>
		<ul class="vrc-noliststyletype">
		<?php foreach ($payments as $pk => $pay):
			$rcheck = $pk == 0 ? " checked=\"checked\"" : "";
			$saypcharge = "";
			if ($pay['charge'] > 0.00) {
				$dec = $pay['charge'] - (int)$pay['charge'];
				$okch = $dec > 0 ? VikRentCar::numberFormat($pay['charge']) : number_format($pay['charge'],0);
				$saypcharge .= " (".($pay['ch_disc']==1?"+":"-")."<span class=\"".($pay['val_pcent']==1?'vrc_price':'')."\">".$okch."</span> <span class=\"".($pay['val_pcent']==1?'vrc_currency':'')."\">" . ($pay['val_pcent']==1?$currencysymb:"%") . "</span>)";
			}
			$pay_img_name = '';
			if (strpos($pay['file'], '.') !== false) { $fparts = explode('.', $pay['file']); $pay_img_name = array_shift($fparts); }
		?>
			<li class="vrc-gpay-licont<?php echo $pk==0?' vrc-gpay-licont-active':''; ?>">
				<input type="radio" name="gpayid" value="<?php echo $pay['id']; ?>" id="gpay<?php echo $pay['id']; ?>"<?php echo $rcheck; ?> onclick="vrcToggleActiveGpay(this);"/>
				<label for="gpay<?php echo $pay['id']; ?>"><span class="vrc-paymeth-info"><?php echo $pay['name'].$saypcharge; ?></span></label>
				<?php if (!empty($pay['logo'])): $pay['logo'] = strpos($pay['logo'],'http')===false?JUri::root().$pay['logo']:$pay['logo']; ?>
				<span class="vrc-payment-image"><label for="gpay<?php echo $pay['id']; ?>"><img src="<?php echo $pay['logo']; ?>" alt="<?php echo $pay['name']; ?>"/></label></span>
				<?php elseif (!empty($pay_img_name) && file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'payments'.DIRECTORY_SEPARATOR.$pay_img_name.'.png')): ?>
				<span class="vrc-payment-image"><label for="gpay<?php echo $pay['id']; ?>"><img src="<?php echo VRC_ADMIN_URI; ?>payments/<?php echo $pay_img_name; ?>.png" alt="<?php echo $pay['name']; ?>"/></label></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
	<script>function vrcToggleActiveGpay(elem){jQuery('.vrc-gpay-licont').removeClass('vrc-gpay-licont-active');jQuery(elem).parent('li').addClass('vrc-gpay-licont-active');}</script>
<?php endif; ?>

<?php /* ── Registration section (optional account creation) ───────── */ ?>
<div class="vrc-register-section" id="vrc-register-section">
	<div class="vrc-register-toggle-row">
		<label class="vrc-register-label" for="vrc-reg-checkbox">
			<input type="checkbox" id="vrc-reg-checkbox"/>
			<span class="vrc-register-toggle-text"><?php echo JText::_('VRC_CREATE_ACCOUNT_WITH_DATA') ?: 'Creează cont cu aceste date'; ?></span>
		</label>
	</div>
	<div class="vrc-register-fields" id="vrc-reg-fields">
		<div class="vrc-reg-benefits">
			<ul>
				<li>✓ <?php echo JText::_('VRC_REG_BENEFIT_1') ?: 'Vizualizează istoricul rezervărilor tale'; ?></li>
				<li>✓ <?php echo JText::_('VRC_REG_BENEFIT_2') ?: 'Rezervare mai rapidă data viitoare'; ?></li>
				<li>✓ <?php echo JText::_('VRC_REG_BENEFIT_3') ?: 'Oferte exclusive pentru membri'; ?></li>
			</ul>
		</div>
		<div class="vrc-reg-field-row">
			<label for="vrc-reg-password"><?php echo JText::_('VRC_PASSWORD') ?: 'Parolă'; ?> <sup class="vrcrequired">*</sup></label>
			<input type="password" id="vrc-reg-password" name="_reg_password" autocomplete="new-password"
			       placeholder="<?php echo JText::_('VRC_PASSWORD_MIN') ?: 'Minim 6 caractere'; ?>"/>
		</div>
		<div class="vrc-reg-field-row">
			<label for="vrc-reg-password2"><?php echo JText::_('VRC_PASSWORD_CONFIRM') ?: 'Confirmă parola'; ?> <sup class="vrcrequired">*</sup></label>
			<input type="password" id="vrc-reg-password2" name="_reg_password2" autocomplete="new-password"
			       placeholder="<?php echo JText::_('VRC_PASSWORD_CONFIRM') ?: 'Repetă parola'; ?>"/>
		</div>
		<div class="vrc-reg-error" id="vrc-reg-error"></div>
	</div>
</div>

<?php /* ── Form footer ─────────────────────────────────────────────── */ ?>
	<div class="vrc-oconfirm-footer">
		<div class="vrc-goback-block">
			<?php if ($isModal): ?>
			<button type="button" class="btn vrc-pref-color-btn-secondary"
			        onclick="try{ window.parent.vrcCloseBookingModal(); } catch(e){ window.history.back(); }">
				<?php echo JText::_('VRBACK'); ?>
			</button>
			<?php else: ?>
			<?php $backto = 'index.php?option=com_vikrentcar&task=showprc&caropt='.$car['id'].'&days='.$days.'&pickup='.$first.'&release='.$second.'&place='.$place.'&returnplace='.$returnplace.(!empty($pitemid)?'&Itemid='.$pitemid:''); ?>
			<a href="<?php echo JRoute::_($backto); ?>" class="btn vrc-pref-color-btn-secondary"><?php echo JText::_('VRBACK'); ?></a>
			<?php endif; ?>
		</div>
		<div class="vrc-save-order-block">
			<input type="submit" id="vrc-saveorder-btn" name="saveorder"
			       value="<?php echo JText::_('VRORDCONFIRM'); ?>"
			       class="btn booknow vrc-pref-color-btn"/>
		</div>
	</div>

</form>

<?php VikRentCar::printTrackingCode(); ?>

<script type="text/javascript">
/* ── Registration section toggle ─────────────────────────────────────────── */
(function ($) {
	'use strict';

	var $checkbox   = $('#vrc-reg-checkbox');
	var $fields     = $('#vrc-reg-fields');
	var $error      = $('#vrc-reg-error');
	var $submitBtn  = $('#vrc-saveorder-btn');

	// Initially collapsed
	$fields.hide();

	$checkbox.on('change', function () {
		if ($(this).is(':checked')) {
			$fields.slideDown(200);
			$submitBtn.val('<?php echo addslashes(JText::_('VRC_CREATE_AND_BOOK') ?: 'Creează Cont și Rezervă'); ?>');
		} else {
			$fields.slideUp(200);
			$submitBtn.val('<?php echo addslashes(JText::_('VRORDCONFIRM')); ?>');
		}
	});

	/* ── Intercept form submit for registration ──────────────────────────── */
	$(document).on('submit', 'form[name="vrc"]', function (e) {
		// Only intercept if "create account" is checked
		if (!$checkbox.is(':checked')) {
			return true; // proceed normally
		}

		e.preventDefault();
		e.stopPropagation();

		var pass1 = $('#vrc-reg-password').val().trim();
		var pass2 = $('#vrc-reg-password2').val().trim();

		// Gather name & email from cfields (marked with data-vrc-field-type)
		var email = $('[data-vrc-field-type="email"]').val() || '';
		var name  = $('[data-vrc-field-type="name"]').first().val() || '';

		// Validate
		$error.hide().text('');

		if (!email) {
			$error.text('<?php echo addslashes(JText::_('VRC_REG_ERR_EMAIL') ?: 'Introduceți adresa de email în câmpul de mai sus.'); ?>').show();
			return false;
		}
		if (!name) {
			$error.text('<?php echo addslashes(JText::_('VRC_REG_ERR_NAME') ?: 'Introduceți numele complet în câmpul de mai sus.'); ?>').show();
			return false;
		}
		if (pass1.length < 6) {
			$error.text('<?php echo addslashes(JText::_('VRC_REG_ERR_PASS_SHORT') ?: 'Parola trebuie să aibă cel puțin 6 caractere.'); ?>').show();
			return false;
		}
		if (pass1 !== pass2) {
			$error.text('<?php echo addslashes(JText::_('VRC_REG_ERR_PASS_MISMATCH') ?: 'Parolele nu coincid.'); ?>').show();
			return false;
		}

		// Disable button while processing
		$submitBtn.prop('disabled', true).val('<?php echo addslashes(JText::_('VRC_CREATING_ACCOUNT') ?: 'Se creează contul…'); ?>');

		// AJAX: create the user & log in
		$.ajax({
			url:         '<?php echo $registerAjaxUrl; ?>',
			type:        'POST',
			contentType: 'application/json',
			data:        JSON.stringify({ reg_name: name, reg_email: email, reg_password: pass1 }),
			dataType:    'json'
		})
		.done(function (res) {
			if (res && res.ok) {
				// User created & logged in — now submit the booking form
				$submitBtn.prop('disabled', false).val('<?php echo addslashes(JText::_('VRORDCONFIRM')); ?>');
				document.vrc.submit();
			} else {
				var msg = (res && res.error) ? res.error : '<?php echo addslashes(JText::_('VRC_REG_ERR_GENERIC') ?: 'Eroare la crearea contului.'); ?>';
				$error.text(msg).show();
				$submitBtn.prop('disabled', false).val('<?php echo addslashes(JText::_('VRC_CREATE_AND_BOOK') ?: 'Creează Cont și Rezervă'); ?>');
			}
		})
		.fail(function () {
			$error.text('<?php echo addslashes(JText::_('VRC_REG_ERR_NETWORK') ?: 'Eroare de rețea. Vă rugăm să încercați din nou.'); ?>').show();
			$submitBtn.prop('disabled', false).val('<?php echo addslashes(JText::_('VRC_CREATE_AND_BOOK') ?: 'Creează Cont și Rezervă'); ?>');
		});

		return false;
	});

})(jQuery);
</script>
