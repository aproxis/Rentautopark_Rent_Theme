<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/order/default.php
 * AutoRent Figma Design — v1
 * Changes:
 *  - Modern card-based layout with your design system
 *  - Clean, spacious design with consistent styling
 *  - Enhanced status indicators and visual hierarchy
 *  - Responsive layout that matches your existing templates
 */

defined('_JEXEC') OR die('Restricted Area');

$ord = $this->ord;
$tar = $this->tar;
$payment = $this->payment;
$calcdays = $this->calcdays;
if (!empty($calcdays)) {
	$origdays = $ord['days'];
	$ord['days'] = $calcdays;
}
$vrc_tn = $this->vrc_tn;

$is_cust_cost = (!empty($ord['cust_cost']) && $ord['cust_cost'] > 0);

// make sure the number of days is never a float
$ord['days'] = (int)$ord['days'];

if (VikRentCar::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
}

$currencysymb = VikRentCar::getCurrencySymb();
$nowdf = VikRentCar::getDateFormat();
$nowtf = VikRentCar::getTimeFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$dbo = JFactory::getDbo();
$carinfo = VikRentCar::getCarInfo($ord['idcar'], $vrc_tn);

$wdays_map = array(
	JText::_('VRWEEKDAYZERO'),
	JText::_('VRWEEKDAYONE'),
	JText::_('VRWEEKDAYTWO'),
	JText::_('VRWEEKDAYTHREE'),
	JText::_('VRWEEKDAYFOUR'),
	JText::_('VRWEEKDAYFIVE'),
	JText::_('VRWEEKDAYSIX'),
);

$prname = "";
$isdue 	= 0;
$imp 	= 0;
$tax 	= 0;
if (is_array($tar)) {
	$prname = $is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar['idprice'], $vrc_tn);
	$isdue = $is_cust_cost ? $tar['cost'] : VikRentCar::sayCostPlusIva($tar['cost'], $tar['idprice'], $ord);
	$imp = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar['cost'], $ord['cust_idiva']) : VikRentCar::sayCostMinusIva($tar['cost'], $tar['idprice'], $ord);
}

$info_from = getdate($ord['ritiro']);
$info_to   = getdate($ord['consegna']);

// options
$optbought = array();
if (!empty($ord['optionals'])) {
	$stepo = explode(";", $ord['optionals']);
	foreach ($stepo as $one) {
		if (!empty($one)) {
			$stept = explode(":", $one);
			$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$actopt = $dbo->loadAssocList();
				$vrc_tn->translateContents($actopt, '#__vikrentcar_optionals');
				$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $ord['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
				$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $ord['days']) : $actopt[0]['cost'];
				if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
					$realcost = $actopt[0]['maxprice'];
					if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
						$realcost = $actopt[0]['maxprice'] * $stept[1];
					}
				}
				$imp += VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $ord);
				$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $ord);
				$isdue += $tmpopr;
				array_push($optbought, array(
					'id' 		=> $actopt[0]['id'],
					'quantity' 	=> $stept[1],
					'name' 		=> $actopt[0]['name'],
					'price' 	=> $tmpopr,
				));
			}
		}
	}
}

// custom extra costs
if (!empty($ord['extracosts'])) {
	$cur_extra_costs = json_decode($ord['extracosts'], true);
	foreach ($cur_extra_costs as $eck => $ecv) {
		$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $ord);
		$isdue += $efee_cost;
		$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $ord);
		$imp += $efee_cost_without;
		array_push($optbought, array(
			'name' 	=> $ecv['name'],
			'price' => $efee_cost,
		));
	}
}

// location fees
if (!empty($ord['idplace']) && !empty($ord['idreturnplace'])) {
	$locfee = VikRentCar::getLocFee($ord['idplace'], $ord['idreturnplace']);
	if ($locfee) {
		//VikRentCar 1.7 - Location fees overrides
		if (strlen($locfee['losoverride']) > 0) {
			$arrvaloverrides = array();
			$valovrparts = explode('_', $locfee['losoverride']);
			foreach($valovrparts as $valovr) {
				if (!empty($valovr)) {
					$ovrinfo = explode(':', $valovr);
					$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
				}
			}
			if (array_key_exists($ord['days'], $arrvaloverrides)) {
				$locfee['cost'] = $arrvaloverrides[$ord['days']];
			}
		}
		//end VikRentCar 1.7 - Location fees overrides
		$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $ord['days']) : $locfee['cost'];
		$locfeewithout = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $ord);
		$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $ord);
		$imp += $locfeewithout;
		$isdue += $locfeewith;
	}
}

// out of hours fees
$oohfee = VikRentCar::getOutOfHoursFees($ord['idplace'], $ord['idreturnplace'], $ord['ritiro'], $ord['consegna'], array('id' => (int)$ord['idcar']));
$ooh_time = '';
if (count($oohfee) > 0) {
	$oohfeewithout = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
	$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
	$ooh_time = $oohfee['pickup'] == 1 ? $oohfee['pickup_ooh'] : '';
	$ooh_time .= $oohfee['dropoff'] == 1 && $oohfee['dropoff_ooh'] != $oohfee['pickup_ooh'] ? (!empty($ooh_time) ? ', ' : '').$oohfee['dropoff_ooh'] : '';
	$imp += $oohfeewithout;
	$isdue += $oohfeewith;
}

// total tax
$tax = $isdue - $imp;

// coupon
$usedcoupon = false;
$origisdue = $isdue;
if (strlen($ord['coupon']) > 0) {
	$usedcoupon = true;
	$expcoupon = explode(";", $ord['coupon']);
	$isdue = $isdue - $expcoupon[1];
}

$pitemid 	= VikRequest::getInt('Itemid', '', 'request');
$printer 	= VikRequest::getInt('printer', '', 'request');
$bestitemid = VikRentCar::findProperItemIdType(array('order'));
if ($printer != 1) {
?>
<div class="order-details-print">
	<a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=order&sid='.$ord['sid'].'&ts='.$ord['ts'].'&printer=1&tmpl=component'.(!empty($bestitemid) ? '&Itemid='.$bestitemid : (!empty($pitemid) ? '&Itemid='.$pitemid : ''))); ?>" target="_blank" class="order-print-btn">
		<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<polyline points="6 9 6 2 18 2 18 9"></polyline>
			<path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
			<rect x="6" y="14" width="12" height="8"></rect>
		</svg>
		<span><?php echo JText::_('VRCDOWNLOADPDF') ?: 'Print Order'; ?></span>
	</a>
</div>
<?php
}

// Add your custom CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root() . 'templates/rent/css/order-details-styles.css');
?>

<div class="order-details-page">
	<div class="order-details-container">
		
		<!-- Order Status Header -->
		<div class="order-status-header">
			<?php if ($ord['status'] == 'confirmed') { ?>
			<div class="order-status-card confirmed">
				<div class="order-status-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
						<polyline points="22 4 12 14.01 9 11.01"></polyline>
					</svg>
				</div>
				<div class="order-status-content">
					<h2 class="order-status-title"><?php echo JText::_('VRC_YOURCONF_ORDER_AT') ?: 'Your order is confirmed'; ?></h2>
				</div>
			</div>
			<?php } elseif ($ord['status'] == 'standby') { ?>
			<div class="order-status-card standby">
				<div class="order-status-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 2v4"></path>
						<path d="m4.93 4.93 2.83 2.83"></path>
						<path d="M2 12h4"></path>
						<path d="m4.93 19.07 2.83-2.83"></path>
						<path d="M12 22v-4"></path>
						<path d="m19.07 19.07-2.83-2.83"></path>
						<path d="M22 12h-4"></path>
						<path d="m19.07 4.93-2.83 2.83"></path>
						<circle cx="12" cy="12" r="3"></circle>
					</svg>
				</div>
				<div class="order-status-content">
					<h2 class="order-status-title"><?php echo JText::_('VRC_YOURORDER_PENDING') ?: 'Order Pending'; ?></h2>
				</div>
			</div>
			<?php } else { ?>
			<div class="order-status-card cancelled">
				<div class="order-status-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="15" y1="9" x2="9" y2="15"></line>
						<line x1="9" y1="9" x2="15" y2="15"></line>
					</svg>
				</div>
				<div class="order-status-content">
					<h2 class="order-status-title"><?php echo JText::_('VRC_YOURORDER_CANCELLED') ?: 'Order Cancelled'; ?></h2>
				</div>
			</div>
			<?php } ?>
		</div>

		<!-- Main Content Grid -->
		<div class="order-details-grid">
			
			<!-- Left Column: Car & Details -->
			<div class="order-details-left">
				
				<!-- Car Information Card -->
				<div class="order-card">
					<div class="order-car-header">
						<h3><?php echo JText::_('VRCORDERCARINFO') ?: 'Vehicle Information'; ?></h3>
						<div class="order-car-actions">
							<a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=carslist'.(!empty($bestitemid) ? '&Itemid='.$bestitemid : (!empty($pitemid) ? '&Itemid='.$pitemid : ''))); ?>" class="order-car-action-btn">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
								</svg>
								<span><?php echo JText::_('VRCBOOKACAR') ?: 'Book Another Car'; ?></span>
							</a>
						</div>
					</div>
					
					<div class="order-car-content">
						<div class="order-car-image">
							<?php
							if (!empty($carinfo['img']) && $printer != 1) {
								$imgpath = is_file(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'vthumb_'.$carinfo['img']) ? VRC_ADMIN_URI . 'resources/vthumb_'.$carinfo['img'] : VRC_ADMIN_URI . 'resources/'.$carinfo['img'];
								?>
								<img alt="<?php echo $carinfo['name']; ?>" src="<?php echo $imgpath; ?>"/>
								<?php
							}
							?>
						</div>
						<div class="order-car-info">
							<h4 class="order-car-name"><?php echo $carinfo['name']; ?></h4>
							<div class="order-car-details">
								<div class="order-car-detail">
									<svg class="order-car-detail-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
									</svg>
									<span><?php echo $ord['days']; ?> <?php echo JText::_('VRCDAYS') ?: 'days'; ?></span>
								</div>
								<div class="order-car-detail">
									<svg class="order-car-detail-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
										<line x1="16" y1="2" x2="16" y2="6"></line>
										<line x1="8" y1="2" x2="8" y2="6"></line>
										<line x1="3" y1="10" x2="21" y2="10"></line>
									</svg>
									<span><?php echo JText::_('VRWEEKDAY' . $info_from['wday']) . ' ' . date($df . ' ' . $nowtf, $ord['ritiro']); ?></span>
								</div>
								<div class="order-car-detail">
									<svg class="order-car-detail-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M21 10c0 6-9 13-9 13s-9-7-9-13a9 9 0 1 1 18 0z"></path>
										<polyline points="12 2 12 12 16 16"></polyline>
									</svg>
									<span><?php echo VikRentCar::getPlaceName($ord['idplace'], $vrc_tn); ?></span>
								</div>
								<div class="order-car-detail">
									<svg class="order-car-detail-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
										<line x1="16" y1="2" x2="16" y2="6"></line>
										<line x1="8" y1="2" x2="8" y2="6"></line>
										<line x1="3" y1="10" x2="21" y2="10"></line>
									</svg>
									<span><?php echo JText::_('VRWEEKDAY' . $info_to['wday']) . ' ' . date($df . ' ' . $nowtf, $ord['consegna']); ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Order Details -->
				<div class="order-card order-details-compact">
					<div class="order-card-header">
						<h3><?php echo JText::_('VRCORDERDETAILS') ?: 'Order Details'; ?></h3>
					</div>
					<div class="order-details-content">
						<div class="order-details-line">
							<div class="order-details-info">
								<div class="order-detail-item">
									<svg class="order-detail-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
										<polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
										<line x1="12" y1="22.08" x2="12" y2="12"></line>
									</svg>
									<span class="order-detail-label"><?php echo JText::_('VRORDEREDON') ?: 'Order Date'; ?>:</span>
									<span class="order-detail-value"><?php echo date($df.' '.$nowtf, $ord['ts']); ?></span>
								</div>
								
								<?php if ($ord['status'] == 'confirmed') { ?>
								<div class="order-detail-item">
									<svg class="order-detail-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
									</svg>
									<span class="order-detail-label"><?php echo JText::_('VRCORDERNUMBER') ?: 'Order #'; ?>:</span>
									<span class="order-detail-value"><?php echo $ord['id']; ?></span>
								</div>
								
								<div class="order-detail-item">
									<svg class="order-detail-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
									</svg>
									<span class="order-detail-label"><?php echo JText::_('VRCCONFIRMATIONNUMBER') ?: 'Confirmation'; ?>:</span>
									<span class="order-detail-value"><?php echo $ord['sid'] . '-' . $ord['ts']; ?></span>
								</div>
								<?php } ?>
							</div>
							
							<?php if ($ord['status'] == 'confirmed' && is_file(VRC_SITE_PATH . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "pdfs" . DIRECTORY_SEPARATOR . $ord['id'] . '_' . $ord['ts'] . '.pdf')) { ?>
							<div class="order-details-actions">
								<a href="<?php echo VRC_SITE_URI; ?>resources/pdfs/<?php echo $ord['id'].'_'.$ord['ts']; ?>.pdf" target="_blank" class="order-pdf-btn">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
										<polyline points="14,2 14,8 20,8"></polyline>
										<line x1="16" y1="13" x2="8" y2="13"></line>
										<line x1="16" y1="17" x2="8" y2="17"></line>
										<polyline points="10,9 9,9 8,9"></polyline>
									</svg>
									<span><?php echo JText::_('VRCDOWNLOADPDF'); ?></span>
								</a>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>

				<!-- Customer Information -->
				<div class="order-card">
					<div class="order-card-header">
						<h3><?php echo JText::_('VRPERSDETS') ?: 'Customer Information'; ?></h3>
					</div>
					<div class="order-customer-content">
						<div class="order-customer-details">
							<?php echo nl2br($ord['custdata']); ?>
						</div>
						
						<?php
						$cpin = VikRentCar::getCPinIstance();
						$customer = $cpin->getCustomerFromBooking($ord['id']);
						if (VikRentCar::allowDocsUpload() && $ord['status'] == 'confirmed' && count($customer) && mktime(23, 59, 59, $info_from['mon'], $info_from['mday'], $info_from['year']) >= time()) {
							// manage customer uploaded documents
							$has_uploaded_docs = !empty($customer['drivers_data']);
							?>
							<div class="order-docs-upload">
								<a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=docsupload&sid='.$ord['sid'].'&ts='.$ord['ts'].(!empty($bestitemid) ? '&Itemid='.$bestitemid : (!empty($pitemid) ? '&Itemid='.$pitemid : ''))); ?>" class="order-docs-btn <?php echo $has_uploaded_docs ? 'uploaded' : ''; ?>">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<?php echo $has_uploaded_docs ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>' : '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line>'; ?>
									</svg>
									<span><?php echo $has_uploaded_docs ? JText::_('VRC_UPLOAD_DOCUMENTS_COMPLETED') : JText::_('VRC_UPLOAD_DOCUMENTS'); ?></span>
								</a>
							</div>
							<?php
						}
						?>
					</div>
				</div>

			</div>

			<!-- Right Column: Pricing & Actions -->
			<div class="order-details-right">
				
				<!-- Pricing Summary -->
				<div class="order-card">
					<div class="order-card-header">
						<h3><?php echo JText::_('VRCORDERPRICING') ?: 'Pricing Summary'; ?></h3>
					</div>
					<?php if ($ord['status'] == 'confirmed') { ?>
					<div class="order-status-notice confirmed">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
							<polyline points="22 4 12 14.01 9 11.01"></polyline>
						</svg>
						<span><?php echo JText::_('VRC_YOURCONF_ORDER_AT_SUBTITLE') ?: 'Your reservation is confirmed and ready for pickup'; ?></span>
					</div>
					<?php } elseif ($ord['status'] == 'standby') { ?>
					<div class="order-status-notice standby">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M12 2v4"></path>
							<path d="m4.93 4.93 2.83 2.83"></path>
							<path d="M2 12h4"></path>
							<path d="m4.93 19.07 2.83-2.83"></path>
							<path d="M12 22v-4"></path>
							<path d="m19.07 19.07-2.83-2.83"></path>
							<path d="M22 12h-4"></path>
							<path d="m19.07 4.93-2.83 2.83"></path>
							<circle cx="12" cy="12" r="3"></circle>
						</svg>
						<span><?php echo JText::_('VRC_YOURORDER_PENDING_SUBTITLE') ?: 'Your reservation is pending confirmation'; ?></span>
					</div>
					<?php } else { ?>
					<div class="order-status-notice cancelled">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="15" y1="9" x2="9" y2="15"></line>
							<line x1="9" y1="9" x2="15" y2="15"></line>
						</svg>
						<span><?php echo JText::_('VRC_YOURORDER_CANCELLED_SUBTITLE') ?: 'This reservation has been cancelled'; ?></span>
					</div>
					<?php } ?>
					<div class="order-pricing-content">
						<div class="order-pricing-list">
							<?php 
							if (is_array($tar)) {
								?>
								<div class="order-pricing-item">
									<div class="order-pricing-name"><?php echo $prname; ?></div>
									<div class="order-pricing-price">
										<span class="order-currency"><?php echo $currencysymb; ?></span>
										<span class="order-price"><?php echo VikRentCar::numberFormat(($is_cust_cost ? $tar['cost'] : VikRentCar::sayCostPlusIva($tar['cost'], $tar['idprice'], $ord))); ?></span>
									</div>
								</div>
								<?php
							}
							foreach ($optbought as $extra) {
								?>
								<div class="order-pricing-item">
									<div class="order-pricing-name"><?php echo (isset($extra['quantity']) && $extra['quantity'] > 1 ? ($extra['quantity'] . 'x ') : '') . $extra['name']; ?></div>
									<div class="order-pricing-price">
										<span class="order-currency"><?php echo $currencysymb; ?></span>
										<span class="order-price"><?php echo VikRentCar::numberFormat($extra['price']); ?></span>
									</div>
								</div>
								<?php
							}
							if (isset($locfeewith) && !empty($locfeewith)) {
								?>
								<div class="order-pricing-item">
									<div class="order-pricing-name"><?php echo JText::_('VRLOCFEETOPAY'); ?></div>
									<div class="order-pricing-price">
										<span class="order-currency"><?php echo $currencysymb; ?></span>
										<span class="order-price"><?php echo VikRentCar::numberFormat($locfeewith); ?></span>
									</div>
								</div>
								<?php
							}
							if (isset($oohfeewith) && !empty($oohfeewith)) {
								?>
								<div class="order-pricing-item">
									<div class="order-pricing-name"><?php echo JText::sprintf('VRCOOHFEETOPAY', $ooh_time); ?></div>
									<div class="order-pricing-price">
										<span class="order-currency"><?php echo $currencysymb; ?></span>
										<span class="order-price"><?php echo VikRentCar::numberFormat($oohfeewith); ?></span>
									</div>
								</div>
								<?php
							}
							if ($usedcoupon == true) {
								?>
								<div class="order-pricing-item discount">
									<div class="order-pricing-name"><?php echo JText::_('VRCCOUPON').' '.$expcoupon[2]; ?></div>
									<div class="order-pricing-price">
										<span class="order-currency"><?php echo $currencysymb; ?></span>
										<span class="order-price">-<?php echo VikRentCar::numberFormat($expcoupon[1]); ?></span>
									</div>
								</div>
								<?php
							}
							?>
						</div>
						
						<div class="order-pricing-total">
							<div class="order-pricing-item total">
								<div class="order-pricing-name"><?php echo JText::_('VRTOTAL'); ?></div>
								<div class="order-pricing-price">
									<span class="order-currency"><?php echo $currencysymb; ?></span>
									<span class="order-price"><?php echo VikRentCar::numberFormat($ord['order_total']); ?></span>
								</div>
							</div>
							
							<?php
							if ($ord['totpaid'] > 0 && !($ord['totpaid'] > $ord['order_total'])) {
								?>
								<div class="order-pricing-item paid">
									<div class="order-pricing-name"><?php echo JText::_('VRCAMOUNTPAID'); ?></div>
									<div class="order-pricing-price">
										<span class="order-currency"><?php echo $currencysymb; ?></span>
										<span class="order-price"><?php echo VikRentCar::numberFormat($ord['totpaid']); ?></span>
									</div>
								</div>
								<?php
							}

							/**
							 * We allow the payment for confirmed orders when a payment method is assigned, the configuration setting is enabled,
							 * the payment counter is greater than 0 (some tasks will force it to 1 when empty) and the amount paid is greater than
							 * zero but less than the total amount, or when the 'payable' property is greater than zero.
							 * We no longer need the payment counter to be greater than zero to allow a payment, as the payable amount can be defined by the admin.
							 * 
							 * @since 	1.14.5 (J) - 1.2.0 (WP)
							 */
							$allow_next_payment = false;
							$payable = (($ord['totpaid'] > 0.00 && $ord['totpaid'] < $ord['order_total'] && $ord['paymcount'] > 0) || $ord['payable'] > 0);
							if ($ord['status'] == 'confirmed' && is_array($payment) && VikRentCar::multiplePayments() && $ord['order_total'] > 0 && $payable) {
								$allow_next_payment = true;
								// build payment form
								$return_url = VikRentCar::externalroute("index.php?option=com_vikrentcar&view=order&sid=" . $ord['sid'] . "&ts=" . $ord['ts'], false, (!empty($bestitemid) ? $bestitemid : null));
								$error_url = VikRentCar::externalroute("index.php?option=com_vikrentcar&view=order&sid=" . $ord['sid'] . "&ts=" . $ord['ts'], false, (!empty($bestitemid) ? $bestitemid : null));
								$notify_url = VikRentCar::externalroute("index.php?option=com_vikrentcar&task=notifypayment&sid=" . $ord['sid'] . "&ts=" . $ord['ts'] . "&tmpl=component", false);

								// calculate amount to be paid
								$remainingamount = $ord['payable'] > 0 ? $ord['payable'] : ($ord['order_total'] - $ord['totpaid']);

								$transaction_name = VikRentCar::getPaymentName();
								$array_order = array();
								$array_order['order'] = $ord;
								$array_order['account_name'] = VikRentCar::getPaypalAcc();
								$array_order['transaction_currency'] = VikRentCar::getCurrencyCodePp();
								$array_order['vehicle_name'] = $carinfo['name'];
								$array_order['transaction_name'] = !empty($transaction_name) ? $transaction_name : $carinfo['name'];
								$array_order['order_total'] = $remainingamount;
								$array_order['currency_symb'] = $currencysymb;
								$array_order['net_price'] = $remainingamount;
								$array_order['tax'] = 0;
								$array_order['return_url'] = $return_url;
								$array_order['error_url'] = $error_url;
								$array_order['notify_url'] = $notify_url;
								$array_order['total_to_pay'] = $remainingamount;
								$array_order['total_net_price'] = $remainingamount;
								$array_order['total_tax'] = 0;
								$array_order['leave_deposit'] = 0;
								$array_order['percentdeposit'] = null;
								$array_order['payment_info'] = $payment;
								$array_order = array_merge($ord, $array_order);

								// display the information about the amount to be paid
								?>
								<div class="order-pricing-item remaining">
									<div class="order-pricing-name"><?php echo JText::_('VRCTOTALREMAINING'); ?></div>
									<div class="order-pricing-price">
										<span class="order-currency"><?php echo $currencysymb; ?></span>
										<span class="order-price"><?php echo VikRentCar::numberFormat($remainingamount); ?></span>
									</div>
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>

				<!-- Payment Methods -->
				<?php
				// render payment method
				if ($allow_next_payment === true) {
					/**
					 * @joomlaonly 	The Payment Factory library will invoke the gateway.
					 * 
					 * @since 	1.14.5
					 */
					require_once VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'payments' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'factory.php';
					$obj = VRCPaymentFactory::getPaymentInstance($payment['file'], $array_order, $payment['params']);
					?>
					<div class="order-card">
						<div class="order-card-header">
							<h3><?php echo JText::_('VRCORDERPAYMENT') ?: 'Payment Method'; ?></h3>
						</div>
						<div class="order-payment-content">
							<?php $obj->showPayment(); ?>
						</div>
					</div>
					<?php
				}

				if (is_array($payment) && $ord['status'] == 'standby') {
					$return_url = VikRentCar::externalroute("index.php?option=com_vikrentcar&view=order&sid=" . $ord['sid'] . "&ts=" . $ord['ts'], false, (!empty($bestitemid) ? $bestitemid : null));
					$error_url = VikRentCar::externalroute("index.php?option=com_vikrentcar&view=order&sid=" . $ord['sid'] . "&ts=" . $ord['ts'], false, (!empty($bestitemid) ? $bestitemid : null));
					$notify_url = VikRentCar::externalroute("index.php?option=com_vikrentcar&task=notifypayment&sid=" . $ord['sid'] . "&ts=" . $ord['ts'] . "&tmpl=component", false);

					$transaction_name = VikRentCar::getPaymentName();
					$leave_deposit = 0;
					$percentdeposit = "";
					$array_order = array();
					$array_order['order'] = $ord;
					$array_order['account_name'] = VikRentCar::getPaypalAcc();
					$array_order['transaction_currency'] = VikRentCar::getCurrencyCodePp();
					$array_order['vehicle_name'] = $carinfo['name'];
					$array_order['transaction_name'] = !empty($transaction_name) ? $transaction_name : $carinfo['name'];
					$array_order['order_total'] = $isdue;
					$array_order['currency_symb'] = $currencysymb;
					$array_order['net_price'] = $imp;
					$array_order['tax'] = $tax;
					$array_order['return_url'] = $return_url;
					$array_order['error_url'] = $error_url;
					$array_order['notify_url'] = $notify_url;
					$array_order['total_to_pay'] = $isdue;
					$array_order['total_net_price'] = $imp;
					$array_order['total_tax'] = $tax;
					$totalchanged = false;
					if ($payment['charge'] > 0.00) {
						$totalchanged = true;
						if ($payment['ch_disc'] == 1) {
							//charge
							if ($payment['val_pcent'] == 1) {
								//fixed value
								$array_order['total_net_price'] += $payment['charge'];
								$array_order['total_tax'] += $payment['charge'];
								$array_order['total_to_pay'] += $payment['charge'];
								$newtotaltopay = $array_order['total_to_pay'];
							} else {
								//percent value
								$percent_net = $array_order['total_net_price'] * $payment['charge'] / 100;
								$percent_tax = $array_order['total_tax'] * $payment['charge'] / 100;
								$percent_to_pay = $array_order['total_to_pay'] * $payment['charge'] / 100;
								$array_order['total_net_price'] += $percent_net;
								$array_order['total_tax'] += $percent_tax;
								$array_order['total_to_pay'] += $percent_to_pay;
								$newtotaltopay = $array_order['total_to_pay'];
							}
						} else {
							//discount
							if ($payment['val_pcent'] == 1) {
								//fixed value
								$array_order['total_net_price'] -= $payment['charge'];
								$array_order['total_tax'] -= $payment['charge'];
								$array_order['total_to_pay'] -= $payment['charge'];
								$newtotaltopay = $array_order['total_to_pay'];
							} else {
								//percent value
								$percent_net = $array_order['total_net_price'] * $payment['charge'] / 100;
								$percent_tax = $array_order['total_tax'] * $payment['charge'] / 100;
								$percent_to_pay = $array_order['total_to_pay'] * $payment['charge'] / 100;
								$array_order['total_net_price'] -= $percent_net;
								$array_order['total_tax'] -= $percent_tax;
								$array_order['total_to_pay'] -= $percent_to_pay;
								$newtotaltopay = $array_order['total_to_pay'];
							}
						}
					}
					if (!VikRentCar::payTotal()) {
						$percentdeposit = (float)VikRentCar::getAccPerCent();
						if ($percentdeposit > 0) {
							$leave_deposit = 1;
							if (VikRentCar::getTypeDeposit() == "fixed") {
								$array_order['total_to_pay'] = $percentdeposit;
								$array_order['total_net_price'] = $percentdeposit;
								$array_order['total_tax'] = ($array_order['total_to_pay'] - $array_order['total_net_price']);
							} else {
								$array_order['total_to_pay'] = $array_order['total_to_pay'] * $percentdeposit / 100;
								$array_order['total_net_price'] = $array_order['total_net_price'] * $percentdeposit / 100;
								$array_order['total_tax'] = ($array_order['total_to_pay'] - $array_order['total_net_price']);
							}
						}
					}
					$array_order['leave_deposit'] = $leave_deposit;
					$array_order['percentdeposit'] = $percentdeposit;
					$array_order['payment_info'] = $payment;
					$array_order = array_merge($ord, $array_order);
					
					?>
					<div class="order-card">
						<div class="order-card-header">
							<h3><?php echo JText::_('VRCORDERPAYMENT') ?: 'Payment Method'; ?></h3>
						</div>
						<div class="order-payment-content">
							<?php	
							if ($totalchanged) {
								$chdecimals = $payment['charge'] - (int)$payment['charge'];
								?>
								<div class="order-payment-changes">
									<span class="order-payment-change-label"><?php echo $payment['name']; ?> 
									(<?php echo ($payment['ch_disc'] == 1 ? "+" : "-").($chdecimals > 0.00 ? VikRentCar::numberFormat($payment['charge']) : number_format($payment['charge'], 0))." ".($payment['val_pcent'] == 1 ? $currencysymb : "%"); ?>)</span>
									<span class="order-payment-change-amount"><span class="order-currency"><?php echo $currencysymb; ?></span> <span class="order-price"><?php echo VikRentCar::numberFormat($newtotaltopay); ?></span></span>
								</div>
								<?php
							}
							/**
							 * @joomlaonly 	The Payment Factory library will invoke the gateway.
							 * 
							 * @since 	1.14.5
							 */
							require_once VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'payments' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'factory.php';
							$obj = VRCPaymentFactory::getPaymentInstance($payment['file'], $array_order, $payment['params']);

							$obj->showPayment();
							?>
						</div>
					</div>
					<?php
				}

				if ($ord['status'] == 'confirmed') {
					// hide prices in case the tariffs have changed for these dates (only for confirmed orders)
					if (number_format($isdue, 2) != number_format($ord['order_total'], 2)) {
						?>
						<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery(".order-currency, .order-price").not(".order_keepcost").text("");
							jQuery(".order-currency, .order-price").not(".order_keepcost").each(function(){
								var cur_txt = jQuery(this).prev("span").html();
								if (cur_txt) {
									jQuery(this).prev("span").html(cur_txt.replace(":", ""));
								}
							});
						});
						</script>
						<?php
					}

					if (is_array($payment) && intval($payment['shownotealw']) == 1 && !empty($payment['note'])) {
						?>
						<div class="order-card">
							<div class="order-card-header">
								<h3><?php echo JText::_('VRCORDERPAYMENTNOTE') ?: 'Payment Note'; ?></h3>
							</div>
							<div class="order-payment-note">
								<?php echo $payment['note']; ?>
							</div>
						</div>
						<?php
					}

					if ($printer == 1) {
						?>
						<script language="JavaScript" type="text/javascript">
						jQuery(document).ready(function() {
							window.print();
						});
						</script>
						<?php
					} else {
						// Cancellation Request
						?>
						<div class="order-card">
							<div class="order-card-header">
								<h3><?php echo JText::_('VRCREQUESTCANCMOD') ?: 'Cancellation Request'; ?></h3>
							</div>
							<div class="order-cancellation-content">
								<p class="order-cancellation-text"><?php echo JText::_('VRCREQUESTCANCMODTEXT') ?: 'If you need to cancel or modify your reservation, please fill out the form below.'; ?></p>
								
								<div class="order-cancellation-actions">
									<button type="button" id="vrcopencancform" onclick="vrcOpenCancOrdForm()" class="order-cancellation-btn">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M18 6L6 18"></path>
											<path d="M6 6l12 12"></path>
										</svg>
										<span><?php echo JText::_('VRCREQUESTCANCMODOPENTEXT'); ?></span>
									</button>
								</div>

								<div class="order-cancellation-form" id="vrcordcancformbox" style="display: none;">
									<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" name="vrccanc" method="post" onsubmit="return vrcValidateCancForm()">
										<div class="order-cancellation-form-group">
											<label for="vrccancemail"><?php echo JText::_('VRCREQUESTCANCMODEMAIL'); ?></label>
											<input type="email" name="email" id="vrccancemail" value="<?php echo $ord['custmail']; ?>" required>
										</div>
										
										<div class="order-cancellation-form-group">
											<label for="vrccancreason"><?php echo JText::_('VRCREQUESTCANCMODREASON'); ?></label>
											<textarea name="reason" id="vrccancreason" rows="5" required></textarea>
										</div>
										
										<div class="order-cancellation-form-actions">
											<button type="submit" name="sendrequest" class="order-cancellation-submit">
												<?php echo JText::_('VRCREQUESTCANCMODSUBMIT'); ?>
											</button>
										</div>
										
										<input type="hidden" name="sid" value="<?php echo $ord['sid']; ?>">
										<input type="hidden" name="idorder" value="<?php echo $ord['id']; ?>">
										<input type="hidden" name="option" value="com_vikrentcar">
										<input type="hidden" name="task" value="cancelrequest">
										<?php if (!empty($pitemid)) { ?>
										<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>">
										<?php } ?>
									</form>
								</div>
							</div>
						</div>
						<?php
					}

					// conversion code only for confirmed orders
					if ($ord['seen'] < 1) {
						VikRentCar::printConversionCode($ord['id']);
					}
				}

				if ($ord['status'] != 'confirmed') {
					// tracking code only for stand-by or cancelled orders
					VikRentCar::printTrackingCode();
				}

				/**
				 * If necessary, move the payment form onto the selected position.
				 * 
				 * @since 	1.14.5 (J) - 1.2.0 (WP)
				 */
				if (is_array($this->payment) && $this->payment['outposition'] != 'bottom') {
					// move the payment window, if available
					?>
					<script type="text/javascript">
						
						jQuery(document).ready(function() {

							var payment_output = jQuery('.order-payment-content'),
								payment_notes = jQuery('.order-payment-note'),
								payment_ctimer = jQuery('.vrc-timer-payment'),
								payment_wrappr = jQuery('.vrc-paycontainer-pos-<?php echo $this->payment['outposition']; ?>');

							if (payment_output.length && payment_wrappr.length) {
								// display final target
								payment_wrappr.show();

								if (payment_notes.length) {
									// prepend notes first
									payment_notes.prependTo(payment_wrappr);
								}

								if (payment_ctimer.length) {
									// prepend countdown timer first
									payment_ctimer.prependTo(payment_wrappr);
								}

								// append payment output
								payment_output.appendTo(payment_wrappr);
							}

						});

					</script>
					<?php
				}
				?>
			</div>

		</div>
	</div>
</div>

<script type="text/javascript">
// Add smooth animations and interactions
document.addEventListener('DOMContentLoaded', function() {
	// Add hover effects to order cards
	const orderCards = document.querySelectorAll('.order-card');
	orderCards.forEach(card => {
		card.addEventListener('mouseenter', () => {
			card.style.transform = 'translateY(-2px)';
			card.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
		});
		
		card.addEventListener('mouseleave', () => {
			card.style.transform = 'translateY(0)';
			card.style.boxShadow = '0 4px 15px rgba(0,0,0,0.08)';
		});
	});
	
	// Cancellation form validation
	function vrcOpenCancOrdForm() {
		document.getElementById('vrcopencancform').style.display = 'none';
		document.getElementById('vrcordcancformbox').style.display = 'block';
	}
	
	function vrcValidateCancForm() {
		var email = document.getElementById('vrccancemail').value;
		var reason = document.getElementById('vrccancreason').value;
		
		if (!email.trim()) {
			document.getElementById('vrccancemail').style.borderColor = '#ff0000';
			return false;
		} else {
			document.getElementById('vrccancemail').style.borderColor = '#ced4da';
		}
		
		if (!reason.trim()) {
			document.getElementById('vrccancreason').style.borderColor = '#ff0000';
			return false;
		} else {
			document.getElementById('vrccancreason').style.borderColor = '#ced4da';
		}
		
		return true;
	}
	
	// Expose functions globally
	window.vrcOpenCancOrdForm = vrcOpenCancOrdForm;
	window.vrcValidateCancForm = vrcValidateCancForm;
});
</script>