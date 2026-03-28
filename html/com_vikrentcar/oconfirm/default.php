<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/oconfirm/default.php
 *
 * Redesign — all 4 phases implemented:
 *   Phase 1 — 50/50 sticky-left / scrollable-right layout
 *   Phase 2 — Coupon removed from oconfirm; shown as read-only badge only
 *   Phase 3 — Zero-friction registration (checkbox only, auto-generated password emailed)
 *   Phase 4 — Joomla user name auto-split and pre-filled into first/last name cfields
 */

defined('_JEXEC') OR die('Restricted Area');

$car              = $this->car;
$price            = $this->price;
$selopt           = $this->selopt;
$days             = $this->days;
$calcdays         = $this->calcdays;
if ((int)$days != (int)$calcdays) {
    $origdays = $days;
    $days     = $calcdays;
}
$coupon           = $this->coupon;
$first            = $this->first;
$second           = $this->second;
$ftitle           = $this->ftitle;
$place            = $this->place;
$returnplace      = $this->returnplace;
$payments         = $this->payments;
$cfields          = $this->cfields;
$customer_details = $this->customer_details;
$countries        = $this->countries;
$vrc_tn           = $this->vrc_tn;

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

$pitemid = VikRequest::getInt('Itemid', '', 'request');
$ptmpl   = VikRequest::getString('tmpl', '', 'request');
$isModal = ($ptmpl === 'component');

// ── Phase 2: Coupon is applied upstream (cardetails) and passed via URL.
//    No coupon input shown in oconfirm — only a read-only badge if one is applied.
$preAppliedCoupon = VikRequest::getString('couponcode', '', 'request');

// ── Phase 4: Split Joomla logged-in user name → first / last
//    Build $nominatives: customer_details takes priority, then Joomla name split.
$currentUser  = JFactory::getUser();
$useremail    = !empty($currentUser->email) ? $currentUser->email : "";
$useremail    = array_key_exists('email', $customer_details) ? $customer_details['email'] : $useremail;
$previousdata = VikRentCar::loadPreviousUserData($currentUser->id);

$nominatives = [];
if (count($customer_details) > 0 && !empty($customer_details['first_name'])) {
    // Returning customer with stored data
    $nominatives[] = $customer_details['first_name'];
    $nominatives[] = $customer_details['last_name'];
} elseif (!$currentUser->guest && !empty($currentUser->name)) {
    // Logged-in Joomla user — split on first whitespace only
    $parts         = preg_split('/\s+/', trim($currentUser->name), 2);
    $nominatives[] = $parts[0];
    $nominatives[] = isset($parts[1]) ? $parts[1] : '';
}

// ── Price / cost calculations
$carats     = VikRentCar::getCarCaratOriz($car['idcarat'], array(), $vrc_tn);
$imp        = VikRentCar::sayCostMinusIva($price['cost'], $price['idprice']);
$totdue     = VikRentCar::sayCostPlusIva($price['cost'],  $price['idprice']);
$saywithout = $imp;
$saywith    = $totdue;

$wop = "";
if (is_array($selopt)) {
    foreach ($selopt as $selo) {
        $wop         .= $selo['id'] . ":" . $selo['quan'] . ";";
        $realcost     = intval($selo['perday']) == 1 ? ($selo['cost'] * $days * $selo['quan']) : ($selo['cost'] * $selo['quan']);
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

// ── Coupon discount
$session->set('vikrentcar_ordertotal', $totdue);
$origtotdue = $totdue;
$usedcoupon = false;
$couponsave = 0;
if (is_array($coupon)) {
    $coupontotok = true;
    if (strlen($coupon['mintotord']) > 0 && $totdue < $coupon['mintotord']) {
        $coupontotok = false;
    }
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

// ── Location fee
$locfee        = false;
$locfeewithout = 0;
$locfeewith    = 0;
$locfeetax     = 0;
$days_int      = intval($days);
if (!empty($place) && !empty($returnplace)) {
    $locfee = VikRentCar::getLocFee($place, $returnplace);
    if ($locfee) {
        if (strlen($locfee['losoverride']) > 0) {
            $arrvaloverrides = [];
            foreach (explode('_', $locfee['losoverride']) as $valovr) {
                if (!empty($valovr)) {
                    $ovrinfo = explode(':', $valovr);
                    $arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
                }
            }
            if (array_key_exists($days_int, $arrvaloverrides)) {
                $locfee['cost'] = $arrvaloverrides[$days_int];
            }
        }
        $locfeecost    = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $days_int) : $locfee['cost'];
        $locfeewithout = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva']);
        $locfeewith    = VikRentCar::sayLocFeePlusIva($locfeecost,  $locfee['idiva']);
        $locfeetax     = $locfeewith - $locfeewithout;
        $imp    += $locfeewithout;
        $totdue += $locfeewith;
    }
}

// ── Out-of-hours fee
$oohfee        = VikRentCar::getOutOfHoursFees($place, $returnplace, $first, $second, $car);
$oohfeewithout = 0;
$oohfeewith    = 0;
$oohfeetax     = 0;
$ooh_time      = '';
if (count($oohfee) > 0) {
    $oohfeecost    = $oohfee['cost'];
    $oohfeewithout = VikRentCar::sayOohFeeMinusIva($oohfeecost, $oohfee['idiva']);
    $oohfeewith    = VikRentCar::sayOohFeePlusIva($oohfeecost,  $oohfee['idiva']);
    $oohfeetax     = $oohfeewith - $oohfeewithout;
    $imp    += $oohfeewithout;
    $totdue += $oohfeewith;
    $ooh_time  = $oohfee['pickup']  == 1 ? $oohfee['pickup_ooh'] : '';
    $ooh_time .= $oohfee['dropoff'] == 1 && $oohfee['dropoff_ooh'] != $oohfee['pickup_ooh']
                 ? (!empty($ooh_time) ? ', ' : '') . $oohfee['dropoff_ooh'] : '';
}

// ── Place info for itinerary display
$pickloc = VikRentCar::getPlaceInfo($place,       $vrc_tn);
$droploc = VikRentCar::getPlaceInfo($returnplace,  $vrc_tn);

// ── Fetch lat/lng directly from DB for Google Maps tooltip links
$_placeDb  = JFactory::getDbo();
$_placeQ   = $_placeDb->getQuery(true)
    ->select(array($_placeDb->quoteName('id'), $_placeDb->quoteName('lat'), $_placeDb->quoteName('lng')))
    ->from($_placeDb->quoteName('#__vikrentcar_places'))
    ->where($_placeDb->quoteName('id') . ' IN (' . (int)$place . ',' . (int)$returnplace . ')');
$_placeDb->setQuery($_placeQ);
$_placeRows = $_placeDb->loadAssocList('id');
$pickloc_lat = isset($_placeRows[$place]['lat'])        ? $_placeRows[$place]['lat']        : '';
$pickloc_lng = isset($_placeRows[$place]['lng'])        ? $_placeRows[$place]['lng']        : '';
$droploc_lat = isset($_placeRows[$returnplace]['lat'])  ? $_placeRows[$returnplace]['lat']  : '';
$droploc_lng = isset($_placeRows[$returnplace]['lng'])  ? $_placeRows[$returnplace]['lng']  : '';

// ── Register-AJAX URL
$registerAjaxUrl = JURI::root() . 'templates/rent/php/register-ajax.php';
?>

<?php /* ── Modal CSS ── */ ?>
<?php if ($isModal): ?>
<link rel="stylesheet" href="<?php echo JURI::root(); ?>templates/rent/css/oconfirm-modal.css"/>
<?php endif; ?>

<?php /* ── Stepbar — hidden in modal ── */ ?>
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

<?php /* ── Page / modal heading ── */ ?>
<?php
// Build the rental title — same logic as vrcrentalforone
if (array_key_exists('hours', $price)) {
    $vrc_modal_title = JText::_('VRRENTAL') . ' ' . $car['name'] . ' ' . JText::_('VRFOR') . ' '
        . (intval($price['hours']) == 1 ? '1 ' . JText::_('VRCHOUR') : $price['hours'] . ' ' . JText::_('VRCHOURS'));
} else {
    $vrc_modal_title = JText::_('VRRENTAL') . ' ' . $car['name'] . ' ' . JText::_('VRFOR') . ' '
        . (intval($days) == 1 ? '1 ' . JText::_('VRDAY') : $days . ' ' . JText::_('VRDAYS'));
}
?>
<?php if ($isModal): ?>
<div class="vrc-modal-header">
    <h2 class="vrc-modal-title"><?php echo $vrc_modal_title; ?></h2>
</div>
<?php /* Mobile sticky total bar — shown only on scroll, below header */ ?>
<?php else: ?>
<h2 class="vrc-rental-summary-title"><?php echo JText::_('VRRIEPILOGOORD'); ?></h2>
<?php /* Mobile sticky total bar — shown only on scroll, below header (full-page view) */ ?>
<div class="vrc-sticky-total-bar" id="vrc-sticky-total-bar" aria-hidden="true">
    <span class="vrc-sticky-total-label"><?php echo JText::_('VRTOTAL'); ?></span>
    <span class="vrc-sticky-total-value">
        <span class="vrc_currency"><?php echo $currencysymb; ?></span><?php echo VikRentCar::numberFormat($totdue); ?>
    </span>
</div>
<?php endif; ?>

<?php /* ══════════════════════════════════════════════════════════════════
   PHASE 1 — Two-column layout
   .vrc-oconfirm-col-left  : sticky price summary (no coupon input)
   .vrc-oconfirm-col-right : scrollable form + payment + register CTA
   CSS lives in oconfirm-modal.css (modal) and the site stylesheet (full-page).
   ══════════════════════════════════════════════════════════════════ */ ?>
<div class="vrc-oconfirm-two-col">

    <!-- ═══════════════════ LEFT COLUMN ═══════════════════ -->
    <div class="vrc-oconfirm-col-left">

        <!-- Car + itinerary block -->
        <div class="vrcinfocarcontainer vrc-col-left-carinfo">
            <div class="vrcrentforlocs">
                <div class="vrcrentalfor">
                <?php if (array_key_exists('hours', $price)): ?>
                    <h3 class="vrcrentalforone"><?php echo JText::_('VRRENTAL'); ?> <?php echo $car['name']; ?> <?php echo JText::_('VRFOR'); ?> <?php echo (intval($price['hours'])==1?"1 ".JText::_('VRCHOUR'):$price['hours']." ".JText::_('VRCHOURS')); ?></h3>
                <?php else: ?>
                    <h3 class="vrcrentalforone"><?php echo JText::_('VRRENTAL'); ?> <?php echo $car['name']; ?> <?php echo JText::_('VRFOR'); ?> <?php echo (intval($days)==1?"1 ".JText::_('VRDAY'):$days." ".JText::_('VRDAYS')); ?></h3>
                <?php endif; ?>
                </div>

                <div class="vrc-itinerary-inline">

                    <?php /* ── Pickup line ── */ ?>
                    <div class="vrc-itin-line">
                        <span class="vrc-itin-dot vrc-itin-dot--pick"></span>
                        <span class="vrc-itin-text">
                            <span class="vrc-itin-label"><?php echo JText::_('VRPICKUPAT'); ?></span>
                            <?php if (count($pickloc)): ?>
                            <strong class="vrc-itin-loc"><?php echo htmlspecialchars($pickloc['name']); ?></strong>
                            <button type="button" class="vrc-itin-info-btn"
                                data-name="<?php echo htmlspecialchars($pickloc['name'] ?? ''); ?>"
                                data-addr="<?php echo htmlspecialchars($pickloc['address'] ?? ''); ?>"
                                data-lat="<?php echo htmlspecialchars($pickloc_lat); ?>"
                                data-lng="<?php echo htmlspecialchars($pickloc_lng); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="13" height="13"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253l-.317 2.539a.75.75 0 1 0 1.489.186l.359-2.871A.75.75 0 0 0 10 9H9Z" clip-rule="evenodd"/></svg>
                            </button>
                            <?php endif; ?>
                        </span>
                        <span class="vrc-itin-datetime"><?php echo date($df, $first); ?> &middot; <?php echo date($nowtf, $first); ?></span>
                    </div>

                    <?php /* ── Drop off line ── */ ?>
                    <div class="vrc-itin-line">
                        <span class="vrc-itin-dot vrc-itin-dot--drop"></span>
                        <span class="vrc-itin-text">
                            <span class="vrc-itin-label"><?php echo JText::_('VRRELEASEAT'); ?></span>
                            <?php if (count($droploc)): ?>
                            <strong class="vrc-itin-loc"><?php echo htmlspecialchars($droploc['name']); ?></strong>
                            <button type="button" class="vrc-itin-info-btn"
                                data-name="<?php echo htmlspecialchars($droploc['name'] ?? ''); ?>"
                                data-addr="<?php echo htmlspecialchars($droploc['address'] ?? ''); ?>"
                                data-lat="<?php echo htmlspecialchars($droploc_lat); ?>"
                                data-lng="<?php echo htmlspecialchars($droploc_lng); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="13" height="13"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253l-.317 2.539a.75.75 0 1 0 1.489.186l.359-2.871A.75.75 0 0 0 10 9H9Z" clip-rule="evenodd"/></svg>
                            </button>
                            <?php endif; ?>
                        </span>
                        <span class="vrc-itin-datetime"><?php echo !array_key_exists('hours', $price) ? date($df, $second) : ''; ?> &middot; <?php echo date($nowtf, $second); ?></span>
                    </div>

                </div><!-- /.vrc-itinerary-inline -->

            </div><!-- /.vrcrentforlocs -->

            <?php if (!empty($car['img']) && !$isModal): ?>
            <div class="vrc-summary-car-img">
                <img src="<?php echo VRC_ADMIN_URI; ?>resources/<?php echo $car['img']; ?>"/>
            </div>
            <?php endif; ?>
        </div><!-- /.vrcinfocarcontainer -->

        <?php /* ── Shared tooltip — lives at col-left root (position:relative) so JS offsetParent is correct ── */ ?>
        <div class="vrc-itin-tooltip" id="vrc-itin-tooltip" role="tooltip" aria-hidden="true">
            <p class="vrc-itin-tooltip-name"></p>
            <p class="vrc-itin-tooltip-addr"></p>
            <a class="vrc-itin-tooltip-map" href="#" target="_blank" rel="noopener noreferrer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C14.827 15.17 17 12.543 17 9A7 7 0 1 0 3 9c0 3.543 2.173 6.172 3.354 7.385a13.381 13.381 0 0 0 3.033 2.198l.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd"/></svg>
                <?php echo JText::_('VRC_VIEW_ON_MAPS') ?: 'View on Google Maps'; ?>
            </a>
        </div>

        <!-- Price breakdown — simplified clean list -->
        <div class="vrc-price-list">

            <!-- Car base row -->
            <div class="vrc-price-row">
                <span class="vrc-price-row-label"><?php echo $car['name']; ?></span>
                <span class="vrc-price-row-value"><?php echo $currencysymb; ?><?php echo VikRentCar::numberFormat($saywith); ?></span>
            </div>

            <!-- Optional rows -->
            <?php if (is_array($selopt)): foreach ($selopt as $aop):
                $thisoptcost  = intval($aop['perday']) == 1 ? ($aop['cost'] * $aop['quan']) * $days_int : $aop['cost'] * $aop['quan'];
                $basequancost = intval($aop['perday']) == 1 ? ($aop['cost'] * $days_int) : $aop['cost'];
                if (!empty($aop['maxprice']) && $aop['maxprice'] > 0 && $basequancost > $aop['maxprice']) {
                    $thisoptcost = $aop['maxprice'];
                    if (intval($aop['hmany']) == 1 && intval($aop['quan']) > 1) { $thisoptcost = $aop['maxprice'] * $aop['quan']; }
                }
                $optwith = VikRentCar::sayOptionalsPlusIva($thisoptcost, $aop['idiva']);
            ?>
            <div class="vrc-price-row">
                <span class="vrc-price-row-label"><?php echo $aop['name'].($aop['quan']>1?' &times;'.$aop['quan']:''); ?></span>
                <span class="vrc-price-row-value"><?php echo $currencysymb; ?><?php echo VikRentCar::numberFormat($optwith); ?></span>
            </div>
            <?php endforeach; endif; ?>

            <!-- Location fee -->
            <?php if ($locfee && $locfeewith > 0): ?>
            <div class="vrc-price-row">
                <span class="vrc-price-row-label"><?php echo JText::_('VRLOCFEETOPAY'); ?></span>
                <span class="vrc-price-row-value"><?php echo $currencysymb; ?><?php echo VikRentCar::numberFormat($locfeewith); ?></span>
            </div>
            <?php endif; ?>

            <!-- Out-of-hours fee -->
            <?php if (count($oohfee) > 0): ?>
            <div class="vrc-price-row">
                <span class="vrc-price-row-label"><?php echo JText::sprintf('VRCOOHFEETOPAY', $ooh_time); ?></span>
                <span class="vrc-price-row-value"><?php echo $currencysymb; ?><?php echo VikRentCar::numberFormat($oohfeewith); ?></span>
            </div>
            <?php endif; ?>

            <!-- Coupon saving -->
            <?php if ($usedcoupon && is_array($coupon)): ?>
            <div class="vrc-price-row vrc-price-row-coupon">
                <span class="vrc-price-row-label">
                    <span class="vrc-coupon-tag-icon">&#127991;</span>
                    <?php echo JText::_('VRCCOUPON'); ?> <strong><?php echo htmlspecialchars($coupon['code']); ?></strong>
                </span>
                <span class="vrc-price-row-value vrc-coupon-saving">&minus;<?php echo $currencysymb; ?><?php echo VikRentCar::numberFormat($couponsave); ?></span>
            </div>
            <?php endif; ?>

            <!-- Total -->
            <div class="vrc-price-row vrc-price-row-total">
                <span class="vrc-price-row-label"><?php echo $usedcoupon ? JText::_('VRCNEWTOTAL') : JText::_('VRTOTAL'); ?></span>
                <span class="vrc-price-row-value"><?php echo $currencysymb; ?><?php echo VikRentCar::numberFormat($totdue); ?></span>
            </div>
        </div><!-- /.vrc-price-list -->

        

        <?php /* ── OLD table kept for compatibility but hidden via CSS ── */ ?>
        <div class="vrc-oconfirm-summary-container" style="display:none!important;">
            <div class="vrc-oconfirm-summary-car-wrapper">
                <div class="vrc-oconfirm-summary-car-head">
                    <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-descr"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-days"><span><?php echo (array_key_exists('hours',$price)?JText::_('VRCHOURS'):JText::_('VRDAYS')); ?></span></div>
                    <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-net"><span><?php echo JText::_('ORDNOTAX'); ?></span></div>
                    <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-tax"><span><?php echo JText::_('ORDTAX'); ?></span></div>
                    <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-tot"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div>
                </div>

                <!-- Car base row -->
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

                <!-- Optional rows -->
                <?php
                if (is_array($selopt)) {
                    foreach ($selopt as $aop) {
                        $thisoptcost  = intval($aop['perday']) == 1
                            ? ($aop['cost'] * $aop['quan']) * $days_int
                            : $aop['cost'] * $aop['quan'];
                        $basequancost = intval($aop['perday']) == 1
                            ? ($aop['cost'] * $days_int)
                            : $aop['cost'];
                        if (!empty($aop['maxprice']) && $aop['maxprice'] > 0 && $basequancost > $aop['maxprice']) {
                            $thisoptcost = $aop['maxprice'];
                            if (intval($aop['hmany']) == 1 && intval($aop['quan']) > 1) {
                                $thisoptcost = $aop['maxprice'] * $aop['quan'];
                            }
                        }
                        $optwithout = VikRentCar::sayOptionalsMinusIva($thisoptcost, $aop['idiva']);
                        $optwith    = VikRentCar::sayOptionalsPlusIva($thisoptcost,  $aop['idiva']);
                        $opttax     = $optwith - $optwithout;
                        ?>
                        <div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-option-row">
                            <div class="vrc-oconfirm-summary-car-cell-descr">
                                <div class="vrc-oconfirm-optname"><?php echo $aop['name'].($aop['quan']>1?" <small>(x ".$aop['quan'].")</small>":""); ?></div>
                            </div>
                            <div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
                            <div class="vrc-oconfirm-summary-car-cell-net">
                                <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDNOTAX'); ?></span></div>
                                <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                                <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($optwithout); ?></span></span>
                            </div>
                            <div class="vrc-oconfirm-summary-car-cell-tax">
                                <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDTAX'); ?></span></div>
                                <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                                <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($opttax); ?></span></span>
                            </div>
                            <div class="vrc-oconfirm-summary-car-cell-tot">
                                <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div>
                                <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                                <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($optwith); ?></span></span>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>

                <!-- Location fee row -->
                <?php if ($locfee && $locfeewith > 0): ?>
                <div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-fee-row">
                    <div class="vrc-oconfirm-summary-car-cell-descr"><div class="vrc-oconfirm-feename"><?php echo JText::_('VRLOCFEETOPAY'); ?></div></div>
                    <div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-cell-net">
                        <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDNOTAX'); ?></span></div>
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeewithout); ?></span></span>
                    </div>
                    <div class="vrc-oconfirm-summary-car-cell-tax">
                        <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDTAX'); ?></span></div>
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeetax); ?></span></span>
                    </div>
                    <div class="vrc-oconfirm-summary-car-cell-tot">
                        <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div>
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeewith); ?></span></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Out-of-hours fee row -->
                <?php if (count($oohfee) > 0): ?>
                <div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-fee-row">
                    <div class="vrc-oconfirm-summary-car-cell-descr"><div class="vrc-oconfirm-feename"><?php echo JText::sprintf('VRCOOHFEETOPAY', $ooh_time); ?></div></div>
                    <div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-cell-net">
                        <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDNOTAX'); ?></span></div>
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeewithout); ?></span></span>
                    </div>
                    <div class="vrc-oconfirm-summary-car-cell-tax">
                        <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDTAX'); ?></span></div>
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeetax); ?></span></span>
                    </div>
                    <div class="vrc-oconfirm-summary-car-cell-tot">
                        <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-head-cell-responsive"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div>
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeewith); ?></span></span>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /.vrc-oconfirm-summary-car-wrapper -->

            <!-- Total + coupon badge -->
            <div class="vrc-oconfirm-summary-total-wrapper">

                <!-- Subtotal row -->
                <div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-total-row">
                    <div class="vrc-oconfirm-summary-car-cell-descr">
                        <div class="vrc-oconfirm-total-block"><?php echo JText::_('VRTOTAL'); ?></div>
                    </div>
                    <div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-cell-net">
                        <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-net"><span><?php echo JText::_('ORDNOTAX'); ?></span></div>
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($imp); ?></span></span>
                    </div>
                    <div class="vrc-oconfirm-summary-car-cell-tax">
                        <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-tax"><span><?php echo JText::_('ORDTAX'); ?></span></div>
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat(($origtotdue - $imp)); ?></span></span>
                    </div>
                    <div class="vrc-oconfirm-summary-car-cell-tot">
                        <div class="vrc-oconfirm-summary-car-head-cell vrc-oconfirm-summary-car-cell-tot"><span><?php echo JText::_('ORDWITHTAX'); ?></span></div>
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($origtotdue); ?></span></span>
                    </div>
                </div>

                <?php /* Phase 2: Applied coupon — READ-ONLY badge (no input field ever shown here).
                          The coupon was applied upstream on the cardetails page and passed via URL. */ ?>
                <?php if ($usedcoupon && is_array($coupon)): ?>
                <div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-total-row vrc-oconfirm-summary-coupon-row">
                    <div class="vrc-oconfirm-summary-car-cell-descr">
                        <span class="vrc-applied-coupon-badge">
                            <span class="vrc-coupon-tag-icon" aria-hidden="true">&#127991;</span>
                            <span><?php echo JText::_('VRCCOUPON'); ?></span>
                            <strong><?php echo htmlspecialchars($coupon['code']); ?></strong>
                        </span>
                    </div>
                    <div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-cell-net"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-cell-tax"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-cell-tot">
                        <span class="vrccurrency vrc-coupon-saving">
                            &minus; <span class="vrc_currency"><?php echo $currencysymb; ?></span>
                        </span>
                        <span class="vrcprice vrc-coupon-saving">
                            <span class="vrc_price"><?php echo VikRentCar::numberFormat($couponsave); ?></span>
                        </span>
                    </div>
                </div>
                <div class="vrc-oconfirm-summary-car-row vrc-oconfirm-summary-total-row vrc-oconfirm-summary-coupon-newtot-row">
                    <div class="vrc-oconfirm-summary-car-cell-descr">
                        <div class="vrc-oconfirm-total-block"><?php echo JText::_('VRCNEWTOTAL'); ?></div>
                    </div>
                    <div class="vrc-oconfirm-summary-car-cell-days"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-cell-net"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-cell-tax"><span></span></div>
                    <div class="vrc-oconfirm-summary-car-cell-tot">
                        <span class="vrccurrency"><span class="vrc_currency"><?php echo $currencysymb; ?></span></span>
                        <span class="vrcprice"><span class="vrc_price"><?php echo VikRentCar::numberFormat($totdue); ?></span></span>
                    </div>
                </div>
                <?php endif; ?>

        </div><!-- /.vrc-oconfirm-summary-total-wrapper (hidden compat) -->
        </div><!-- /.vrc-oconfirm-summary-container (hidden compat) -->

    </div><!-- /.vrc-oconfirm-col-left -->

    <!-- ═══════════════════ RIGHT COLUMN ═══════════════════ -->
    <!-- RIGHT COLUMN -->
<div class="vrc-oconfirm-col-right">
        <?php /* ── Customer PIN (guests without stored data only) ── */ ?>
        <?php if (VikRentCar::customersPinEnabled() && !VikRentCar::userIsLogged() && !(count($customer_details) > 0)): ?>
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
        <?php endif; ?>

        <?php /* ── Client-side validation function ── */ ?>
        <script type="text/javascript">
        function vrcValidateEmail(email) {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }
        
        function checkvrcFields() {
            var vrvar = document.vrc;
            var isValid = true;
            var errorMessage = '';
            
            // Reset all field colors
            <?php
            if (@is_array($cfields)) {
                foreach ($cfields as $cf) {
                    ?>
                    if (document.getElementById('vrcf<?php echo $cf['id']; ?>')) {
                        document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='';
                    }
                    <?php
                }
            }
            ?>
            
            // Validate required fields
            <?php
            if (@is_array($cfields)) {
                foreach ($cfields as $cf) {
                    if (intval($cf['required']) == 1) {
                        if ($cf['type'] == "text" || $cf['type'] == "textarea" || $cf['type'] == "date" || $cf['type'] == "country") {
                            ?>
                    var field<?php echo $cf['id']; ?> = vrvar.vrcf<?php echo $cf['id']; ?>;
                    if (!field<?php echo $cf['id']; ?> || !field<?php echo $cf['id']; ?>.value.match(/\S/)) {
                        if (document.getElementById('vrcf<?php echo $cf['id']; ?>')) {
                            document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
                        }
                        isValid = false;
                        errorMessage = 'Please fill in all required fields.';
                    } else { 
                        if (document.getElementById('vrcf<?php echo $cf['id']; ?>')) {
                            document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='';
                        }
                    }
                            <?php
                            if ($cf['isemail'] == 1) {
                                ?>
                    if (field<?php echo $cf['id']; ?> && field<?php echo $cf['id']; ?>.value.match(/\S/) && !vrcValidateEmail(field<?php echo $cf['id']; ?>.value)) {
                        if (document.getElementById('vrcf<?php echo $cf['id']; ?>')) {
                            document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
                        }
                        isValid = false;
                        errorMessage = 'Please enter a valid email address.';
                    }
                                <?php
                            }
                        } elseif ($cf['type'] == "select") {
                            ?>
                    var select<?php echo $cf['id']; ?> = vrvar.vrcf<?php echo $cf['id']; ?>;
                    if (!select<?php echo $cf['id']; ?> || !select<?php echo $cf['id']; ?>.value.match(/\S/)) {
                        if (document.getElementById('vrcf<?php echo $cf['id']; ?>')) {
                            document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
                        }
                        isValid = false;
                        errorMessage = 'Please select an option for all required fields.';
                    } else { 
                        if (document.getElementById('vrcf<?php echo $cf['id']; ?>')) {
                            document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='';
                        }
                    }
                            <?php
                        } elseif ($cf['type'] == "checkbox") {
                            ?>
                    var checkbox<?php echo $cf['id']; ?> = vrvar.vrcf<?php echo $cf['id']; ?>;
                    if (!checkbox<?php echo $cf['id']; ?> || !checkbox<?php echo $cf['id']; ?>.checked) {
                        if (document.getElementById('vrcf<?php echo $cf['id']; ?>')) {
                            document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
                        }
                        // Also highlight the checkbox itself
                        if (document.getElementById('vrcf-inp<?php echo $cf['id']; ?>')) {
                            document.getElementById('vrcf-inp<?php echo $cf['id']; ?>').style.borderColor = '#ff0000';
                            document.getElementById('vrcf-inp<?php echo $cf['id']; ?>').style.boxShadow = '0 0 5px #ff0000';
                        }
                        isValid = false;
                        errorMessage = 'Please agree to the terms and conditions.';
                    } else { 
                        if (document.getElementById('vrcf<?php echo $cf['id']; ?>')) {
                            document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='';
                        }
                        if (document.getElementById('vrcf-inp<?php echo $cf['id']; ?>')) {
                            document.getElementById('vrcf-inp<?php echo $cf['id']; ?>').style.borderColor = '';
                            document.getElementById('vrcf-inp<?php echo $cf['id']; ?>').style.boxShadow = '';
                        }
                    }
                            <?php
                        }
                    }
                }
            }
            ?>
            
            // If validation failed, show error message and prevent submission
            if (!isValid) {
                alert(errorMessage);
                return false;
            }
            
            return true;
        }
        </script>

        <?php /* ══════════════════════════════════════════════════════════
           MAIN ORDER FORM
           target="_top" → after saveorder the PARENT window navigates,
           not just the iframe.
           ══════════════════════════════════════════════════════════ */ ?>
        <form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid)?'&Itemid='.$pitemid:'')); ?>"
              name="vrc"
              method="post"
              target="_top"
              onsubmit="javascript: return checkvrcFields();">

            <?php /* ── Customer fields ── */ ?>
            <?php if (@is_array($cfields)): ?>
            <div class="vrccustomfields">
                <?php
                foreach ($cfields as $cf) {
                    $isreq = intval($cf['required']) == 1
                        ? "<span class=\"vrcrequired\"><sup>*</sup></span> "
                        : "";

                    $fname = !empty($cf['poplink'])
                        ? "<a href=\"" . $cf['poplink'] . "\" id=\"vrcf" . $cf['id'] . "\" rel=\"{handler:'iframe',size:{x:750,y:600}}\" target=\"_blank\" class=\"vrcmodal\">" . JText::_($cf['name']) . "</a>"
                        : "<label id=\"vrcf" . $cf['id'] . "\" for=\"vrcf-inp" . $cf['id'] . "\">" . JText::_($cf['name']) . "</label>";

                    // data-vrc-field-type attributes for registration JS detection
                    $extraDataAttr = '';
                    if (intval($cf['isemail'])        == 1) { $extraDataAttr = ' data-vrc-field-type="email"'; }
                    elseif (intval($cf['isnominative']) == 1) { $extraDataAttr = ' data-vrc-field-type="name"'; }
                    elseif (intval($cf['isphone'])    == 1) { $extraDataAttr = ' data-vrc-field-type="phone"'; }

                    if ($cf['type'] == "text") {
                        /* Phase 4: Determine default value.
                           $nominatives is already built at the top with split Joomla name. */
                        $def_textval = '';
                        if ($cf['isemail'] == 1) {
                            $def_textval = $useremail;
                        } elseif ($cf['isphone'] == 1) {
                            if (array_key_exists('phone', $customer_details)) {
                                $def_textval = $customer_details['phone'];
                            }
                        } elseif ($cf['isnominative'] == 1) {
                            // array_shift: first call = first name, second call = last name
                            if (count($nominatives) > 0) {
                                $def_textval = array_shift($nominatives);
                            }
                        } elseif (array_key_exists('cfields', $customer_details) && array_key_exists($cf['id'], $customer_details['cfields'])) {
                            $def_textval = $customer_details['cfields'][$cf['id']];
                        }
                        // Plain label text for floating label span
                        $plain_label = JText::_($cf['name']);
                        $req_star    = intval($cf['required']) == 1 ? ' *' : '';
                        ?>
                        <div class="vrcdivcustomfield">
                            <div class="vrc-customfield-input">
                            <?php if ($cf['isphone'] == 1 && method_exists($vrc_app, 'printPhoneInputField')): ?>
                                <div class="vrc-float-wrap vrc-float-wrap--phone<?php echo !empty($def_textval) ? ' vrc-float-has-val' : ''; ?>">
                                    <?php echo $vrc_app->printPhoneInputField(array('name'=>'vrcf'.$cf['id'],'id'=>'vrcf-inp'.$cf['id'],'value'=>$def_textval,'class'=>'vrcinput','size'=>'40',$extraDataAttr=>'')); ?>
                                    <label class="vrc-float-label" id="vrcf<?php echo $cf['id']; ?>" for="vrcf-inp<?php echo $cf['id']; ?>"><?php echo htmlspecialchars($plain_label . $req_star); ?></label>
                                </div>
                            <?php else: ?>
                                <div class="vrc-float-wrap<?php echo !empty($def_textval) ? ' vrc-float-has-val' : ''; ?>">
                                    <input type="text"
                                           name="vrcf<?php echo $cf['id']; ?>"
                                           id="vrcf-inp<?php echo $cf['id']; ?>"
                                           value="<?php echo htmlspecialchars($def_textval); ?>"
                                           placeholder=" "
                                           class="vrcinput"<?php echo $extraDataAttr; ?>/>
                                    <label class="vrc-float-label" id="vrcf<?php echo $cf['id']; ?>" for="vrcf-inp<?php echo $cf['id']; ?>"><?php echo htmlspecialchars($plain_label . $req_star); ?></label>
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    } elseif ($cf['type'] == "textarea") {
                        $defaultval = array_key_exists($cf['id'], $previousdata['customfields'])
                            ? $previousdata['customfields'][$cf['id']] : '';
                        if (isset($customer_details['cfields']) && array_key_exists($cf['id'], $customer_details['cfields'])) {
                            $defaultval = $customer_details['cfields'][$cf['id']];
                        }
                        ?>
                        <div class="vrcdivcustomfield">
                            <div class="vrc-customfield-label"><?php echo $isreq; ?><?php echo $fname; ?></div>
                            <div class="vrc-customfield-input">
                                <textarea name="vrcf<?php echo $cf['id']; ?>"
                                          id="vrcf-inp<?php echo $cf['id']; ?>"
                                          rows="5" cols="30"
                                          class="vrctextarea"<?php echo $extraDataAttr; ?>><?php echo htmlspecialchars($defaultval); ?></textarea>
                            </div>
                        </div>
                        <?php
                    } elseif ($cf['type'] == "date") {
                        $defaultval = array_key_exists($cf['id'], $previousdata['customfields'])
                            ? $previousdata['customfields'][$cf['id']] : '';
                        ?>
                        <div class="vrcdivcustomfield">
                            <div class="vrc-customfield-label"><?php echo $isreq; ?><?php echo $fname; ?></div>
                            <div class="vrc-customfield-input vrc-customfield-input-date">
                                <input type="text"
                                       name="vrcf<?php echo $cf['id']; ?>"
                                       id="vrcf-inp<?php echo $cf['id']; ?>"
                                       value="<?php echo htmlspecialchars($defaultval); ?>"
                                       size="40"
                                       class="vrcinput"<?php echo $extraDataAttr; ?>/>
                            </div>
                        </div>
                        <script>
                        jQuery(document).ready(function(){
                            jQuery("#vrcf-inp<?php echo $cf['id']; ?>").datepicker({
                                dateFormat: "<?php echo $juidf; ?>",
                                changeMonth: true,
                                changeYear: true,
                                yearRange: "<?php echo (date('Y')-100).':'.(date('Y')+20); ?>"
                            });
                        });
                        </script>
                        <?php
                    } elseif ($cf['type'] == "country" && is_array($countries)) {
                        $defaultval = array_key_exists($cf['id'], $previousdata['customfields'])
                            ? $previousdata['customfields'][$cf['id']] : '';
                        if (array_key_exists('country', $customer_details)) {
                            $defaultval = !empty($customer_details['country'])
                                ? substr($customer_details['country'], 0, 3) : '';
                        }
                        $countries_sel  = '<select name="vrcf'.$cf['id'].'" id="vrcf-inp'.$cf['id'].'" class="vrcf-countryinp"><option value=""></option>'."\n";
                        foreach ($countries as $country) {
                            $countries_sel .= '<option value="'.$country['country_3_code'].'::'.$country['country_name'].'"'
                                . ($defaultval == $country['country_3_code'] ? ' selected="selected"' : '')
                                . '>'.$country['country_name'].'</option>'."\n";
                        }
                        $countries_sel .= '</select>';
                        $plain_label = JText::_($cf['name']);
                        $req_star    = intval($cf['required']) == 1 ? ' *' : '';
                        ?>
                        <div class="vrcdivcustomfield">
                            <div class="vrc-customfield-input">
                                <div class="vrc-float-wrap<?php echo !empty($defaultval) ? ' vrc-float-has-val' : ''; ?>">
                                    <?php echo $countries_sel; ?>
                                    <label class="vrc-float-label" id="vrcf<?php echo $cf['id']; ?>" for="vrcf-inp<?php echo $cf['id']; ?>"><?php echo htmlspecialchars($plain_label . $req_star); ?></label>
                                </div>
                            </div>
                        </div>
                        <?php
                    } elseif ($cf['type'] == "select") {
                        $defaultval = array_key_exists($cf['id'], $previousdata['customfields'])
                            ? $previousdata['customfields'][$cf['id']] : '';
                        $answ   = explode(";;__;;", $cf['choose']);
                        $wcfsel = "<select name=\"vrcf".$cf['id']."\" id=\"vrcf-inp".$cf['id']."\">\n<option value=\"\"></option>\n";
                        foreach ($answ as $aw) {
                            if (!empty($aw)) {
                                $wcfsel .= "<option value=\"".JText::_($aw)."\""
                                    . ($defaultval == JText::_($aw) ? ' selected="selected"' : '')
                                    . ">".JText::_($aw)."</option>\n";
                            }
                        }
                        $wcfsel .= "</select>\n";
                        $plain_label = JText::_($cf['name']);
                        $req_star    = intval($cf['required']) == 1 ? ' *' : '';
                        ?>
                        <div class="vrcdivcustomfield">
                            <div class="vrc-customfield-input">
                                <div class="vrc-float-wrap<?php echo !empty($defaultval) ? ' vrc-float-has-val' : ''; ?>">
                                    <?php echo $wcfsel; ?>
                                    <label class="vrc-float-label" id="vrcf<?php echo $cf['id']; ?>" for="vrcf-inp<?php echo $cf['id']; ?>"><?php echo htmlspecialchars($plain_label . $req_star); ?></label>
                                </div>
                            </div>
                        </div>
                        <?php
                    } elseif ($cf['type'] == "separator") {
                        $cfsepclass = strlen(JText::_($cf['name'])) > 30
                            ? "vrcseparatorcflong" : "vrcseparatorcf";
                        ?>
                        <div class="vrcdivcustomfield vrccustomfldinfo">
                            <div class="<?php echo $cfsepclass; ?>"><?php echo JText::_($cf['name']); ?></div>
                        </div>
                        <?php
                    } else {
                        // Checkbox type — collect for rendering after payment methods
                        // Store in buffer; will be output below payment block
                        ob_start();
                        ?>
                        <div class="vrcdivcustomfield vrc-oconfirm-cfield-entry-checkbox vrc-terms-checkbox">
                            <div class="vrc-terms-label">
                                <input type="checkbox"
                                       name="vrcf<?php echo $cf['id']; ?>"
                                       id="vrcf-inp<?php echo $cf['id']; ?>"
                                       value="<?php echo JText::_('VRYES'); ?>"<?php echo $extraDataAttr; ?>/>
                                <span class="vrc-terms-text"><?php echo $isreq; ?><?php
                                    // Use $fname which already resolves poplink → <a href="..."> or plain <label>
                                    if (!empty($cf['poplink'])) {
                                        // Direct link text (not a <label> since the checkbox is above)
                                        echo '<a href="' . $cf['poplink'] . '" rel="noopener noreferrer" class="vrc-terms-link vrcmodal">' . JText::_($cf['name']) . '</a>';
                                    } else {
                                        echo '<label for="vrcf-inp' . $cf['id'] . '" class="vrc-terms-inline-label">' . JText::_($cf['name']) . '</label>';
                                    }
                                ?></span>
                            </div>
                        </div>
                        <?php
                        $vrc_checkbox_buffer = (isset($vrc_checkbox_buffer) ? $vrc_checkbox_buffer : '') . ob_get_clean();
                    }
                }
                ?>
            </div>
            <?php endif; ?>

            <?php /* ── Hidden booking fields ── */ ?>
            <input type="hidden" name="days"        value="<?php echo $days; ?>"/>
            <?php if (isset($origdays)): ?>
            <input type="hidden" name="origdays"    value="<?php echo $origdays; ?>"/>
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
            <input type="hidden" name="hourly"      value="<?php echo $price['hours']; ?>"/>
            <?php endif; ?>
            <?php if ($usedcoupon && is_array($coupon)): ?>
            <input type="hidden" name="couponcode"  value="<?php echo htmlspecialchars($coupon['code']); ?>"/>
            <?php endif; ?>
            <?php echo !empty($tok) ? $tok . JHtml::_('form.token') : ''; ?>
            <input type="hidden" name="task"        value="saveorder"/>
            <?php if (!empty($pitemid)): ?>
            <input type="hidden" name="Itemid"      value="<?php echo $pitemid; ?>"/>
            <?php endif; ?>
            <?php if ($isModal): ?>
            <input type="hidden" name="tmpl"        value="component"/>
            <?php endif; ?>

            <?php /* ── Payment methods ── */ ?>
            <?php if (is_array($payments)): ?>
            <div class="vrc-oconfirm-paym-block">
                <h4 class="vrc-medium-header"><?php echo JText::_('VRCHOOSEPAYMENT'); ?></h4>
                <ul class="vrc-noliststyletype">
                <?php foreach ($payments as $pk => $pay):
                    $rcheck = $pk == 0 ? " checked=\"checked\"" : "";
                    $saypcharge = "";
                    if ($pay['charge'] > 0.00) {
                        $dec = $pay['charge'] - (int)$pay['charge'];
                        $okch = $dec > 0 ? VikRentCar::numberFormat($pay['charge']) : number_format($pay['charge'], 0);
                        $saypcharge .= " (" . ($pay['ch_disc'] == 1 ? "+" : "-")
                            . "<span class=\"".($pay['val_pcent']==1?'vrc_price':'')."\">".$okch."</span>"
                            . " <span class=\"".($pay['val_pcent']==1?'vrc_currency':'')."\">".($pay['val_pcent']==1?$currencysymb:"%")."</span>)";
                    }
                    $pay_img_name = '';
                    if (strpos($pay['file'], '.') !== false) {
                        $fparts = explode('.', $pay['file']);
                        $pay_img_name = array_shift($fparts);
                    }
                ?>
                    <li class="vrc-gpay-licont<?php echo $pk==0?' vrc-gpay-licont-active':''; ?>">
                        <input type="radio" name="gpayid" value="<?php echo $pay['id']; ?>"
                               id="gpay<?php echo $pay['id']; ?>"<?php echo $rcheck; ?>
                               onclick="vrcToggleActiveGpay(this);"/>
                        <label for="gpay<?php echo $pay['id']; ?>">
                            <span class="vrc-paymeth-info"><?php echo $pay['name'].$saypcharge; ?></span>
                        </label>
                        <?php if (!empty($pay['logo'])):
                            $pay['logo'] = strpos($pay['logo'],'http')===false ? JUri::root().$pay['logo'] : $pay['logo']; ?>
                        <span class="vrc-payment-image">
                            <label for="gpay<?php echo $pay['id']; ?>">
                                <img src="<?php echo $pay['logo']; ?>" alt="<?php echo $pay['name']; ?>"/>
                            </label>
                        </span>
                        <?php elseif (!empty($pay_img_name) && file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'payments'.DIRECTORY_SEPARATOR.$pay_img_name.'.png')): ?>
                        <span class="vrc-payment-image">
                            <label for="gpay<?php echo $pay['id']; ?>">
                                <img src="<?php echo VRC_ADMIN_URI; ?>payments/<?php echo $pay_img_name; ?>.png" alt="<?php echo $pay['name']; ?>"/>
                            </label>
                        </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <script>
            function vrcToggleActiveGpay(elem) {
                jQuery('.vrc-gpay-licont').removeClass('vrc-gpay-licont-active');
                jQuery(elem).parent('li').addClass('vrc-gpay-licont-active');
            }
            </script>
            <?php endif; ?>

            <?php /* ── Terms / checkbox cfields — output after payment, one row ── */ ?>
            <?php if (!empty($vrc_checkbox_buffer)): ?>
            <div class="vrc-terms-block">
                <?php echo $vrc_checkbox_buffer; ?>
            </div>
            <?php endif; ?>

            <?php /* ══════════════════════════════════════════════════════════
               PHASE 3 — Zero-friction registration
               • Only shown to guests (logged-in users already have an account)
               • Single checkbox — no password fields
               • register-ajax.php auto-generates password + emails it
               ══════════════════════════════════════════════════════════ */ ?>
            <?php if ($currentUser->guest): ?>
            <div class="vrc-register-section" id="vrc-register-section">

                <div class="vrc-register-toggle-row">
                    <label class="vrc-register-label" for="vrc-reg-checkbox">
                        <input type="checkbox" id="vrc-reg-checkbox"/>
                        <span class="vrc-register-toggle-text">
                            <?php echo JText::_('VRC_CREATE_ACCOUNT_WITH_DATA') ?: 'Creează cont cu aceste date'; ?>
                        </span>
                    </label>
                </div>

                <!-- Expanded panel — visible only when checkbox is ticked -->
                <div class="vrc-register-fields" id="vrc-reg-fields" style="display:none;">

                    <p class="vrc-reg-info-note">
                        <?php echo JText::_('VRC_REG_AUTO_PASSWORD_NOTE') ?: 'Parola va fi generată automat și trimisă pe email-ul tău.'; ?>
                    </p>

                    <!-- Username row: input + register button -->
                    <div class="vrc-reg-username-row">
                        <div class="vrc-float-wrap" id="vrc-reg-username-wrap">
                            <input type="text"
                                   id="vrc-reg-username"
                                   name="vrc_reg_username"
                                   placeholder=" "
                                   autocomplete="username"
                                   class="vrcinput vrc-reg-username-inp"
                                   maxlength="150"/>
                            <label class="vrc-float-label" for="vrc-reg-username">
                                <?php echo JText::_('VRC_REG_USERNAME_LABEL') ?: 'Nume utilizator (opțional)'; ?>
                            </label>
                        </div>
                        <button type="button"
                                id="vrc-reg-btn"
                                class="btn vrc-pref-color-btn vrc-reg-create-btn">
                            <?php echo JText::_('VRC_REG_CREATE_BTN') ?: 'Creează Cont'; ?>
                        </button>
                    </div>

                    <!-- Status / error feedback -->
                    <div class="vrc-reg-error"   id="vrc-reg-error"   style="display:none;"></div>
                    <div class="vrc-reg-success" id="vrc-reg-success" style="display:none;"></div>

                </div>
            </div>
            <?php endif; ?>

            <?php /* ── Form footer: Back + Submit ── */ ?>
            <div class="vrc-oconfirm-footer">
                <div class="vrc-goback-block">
                    <?php if ($isModal): ?>
                    <button type="button"
                            class="btn vrc-pref-color-btn-secondary"
                            onclick="try{ window.parent.vrcCloseBookingModal(); }catch(e){ window.history.back(); }">
                        <?php echo JText::_('VRBACK'); ?>
                    </button>
                    <?php else: ?>
                    <?php $backto = 'index.php?option=com_vikrentcar&task=showprc&caropt='.$car['id'].'&days='.$days.'&pickup='.$first.'&release='.$second.'&place='.$place.'&returnplace='.$returnplace.(!empty($pitemid)?'&Itemid='.$pitemid:''); ?>
                    <a href="<?php echo JRoute::_($backto); ?>" class="btn vrc-pref-color-btn-secondary">
                        <?php echo JText::_('VRBACK'); ?>
                    </a>
                    <?php endif; ?>
                </div>
                <div class="vrc-save-order-block">
                    <input type="submit"
                           id="vrc-saveorder-btn"
                           name="saveorder"
                           value="<?php echo JText::_('VRORDCONFIRM'); ?>"
                           class="btn booknow vrc-pref-color-btn"/>
                </div>
            </div>

        </form>

    </div><!-- /.vrc-oconfirm-col-right -->

</div><!-- /.vrc-oconfirm-two-col -->

<?php VikRentCar::printTrackingCode(); ?>

<script type="text/javascript">
/* ── Info-icon tooltip for pickup/dropoff locations ── */
(function () {
    'use strict';

    function initTooltip() {
        var $tooltip = document.getElementById('vrc-itin-tooltip');
        if (!$tooltip) return;

        var $nameEl = $tooltip.querySelector('.vrc-itin-tooltip-name');
        var $addrEl = $tooltip.querySelector('.vrc-itin-tooltip-addr');
        var $mapEl  = $tooltip.querySelector('.vrc-itin-tooltip-map');
        var activeBtn = null;

        function buildMapsUrl(btn) {
            var lat  = btn.getAttribute('data-lat')  || '';
            var lng  = btn.getAttribute('data-lng')  || '';
            var name = btn.getAttribute('data-name') || '';
            var addr = btn.getAttribute('data-addr') || '';

            // Prefer precise DB coordinates (lat/lng from #__vikrentcar_places)
            // Use raw float values — no encodeURIComponent — Maps expects plain lat,lng
            if (lat && lng && parseFloat(lat) !== 0 && parseFloat(lng) !== 0) {
                return 'https://www.google.com/maps?q=' + parseFloat(lat) + ',' + parseFloat(lng);
            }
            // Fallback: text search
            var query = encodeURIComponent((name + ' ' + addr).trim());
            return 'https://www.google.com/maps/search/?api=1&query=' + query;
        }

        function showTooltip(btn) {
            var name = btn.getAttribute('data-name') || '';
            var addr = btn.getAttribute('data-addr') || '';

            if ($nameEl) $nameEl.textContent = addr || name;
            if ($addrEl) $addrEl.textContent = '';
            if ($mapEl)  $mapEl.href = buildMapsUrl(btn);

            // Show first so we can measure its width for clamping
            $tooltip.removeAttribute('aria-hidden');
            $tooltip.classList.add('is-visible');

            /* The tooltip is position:absolute inside .vrc-oconfirm-col-left.
               We must add the column's scrollTop to get correct position. */
            var colLeft    = document.querySelector('.vrc-oconfirm-col-left');
            var btnRect    = btn.getBoundingClientRect();
            var baseRect   = colLeft ? colLeft.getBoundingClientRect() : { top: 0, left: 0 };
            var scrollTop  = colLeft ? colLeft.scrollTop  : 0;
            var scrollLeft = colLeft ? colLeft.scrollLeft : 0;

            var top  = btnRect.bottom - baseRect.top  + scrollTop  + 6;
            var left = btnRect.left   - baseRect.left + scrollLeft;

            // Clamp horizontally so tooltip doesn't bleed outside the column
            var containerWidth = colLeft ? colLeft.offsetWidth : 300;
            var tooltipWidth   = $tooltip.offsetWidth || 220;
            if (left + tooltipWidth > containerWidth - 8) {
                left = containerWidth - tooltipWidth - 8;
            }
            if (left < 8) left = 8;

            $tooltip.style.top  = top  + 'px';
            $tooltip.style.left = left + 'px';

            activeBtn = btn;
        }

        function hideTooltip() {
            $tooltip.setAttribute('aria-hidden', 'true');
            $tooltip.classList.remove('is-visible');
            activeBtn = null;
        }

        document.querySelectorAll('.vrc-itin-info-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (activeBtn === btn) { hideTooltip(); return; }
                showTooltip(btn);
            });
        });

        // Clicking anywhere else closes the tooltip
        document.addEventListener('click', function (e) {
            if (activeBtn && !$tooltip.contains(e.target)) {
                hideTooltip();
            }
        });

        document.addEventListener('keyup', function (e) {
            if (e.key === 'Escape' && activeBtn) hideTooltip();
        });
    }

    // Safe init: works whether DOM is ready or not (important inside iframe)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTooltip);
    } else {
        initTooltip();
    }
})();
</script>

<style>
/* ── Phase 3 Registration UI ─────────────────────────────────────────── */
.vrc-register-section {
    margin: 16px 0 8px;
    padding: 14px 16px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: #fafafa;
}
.vrc-register-toggle-row {
    display: flex;
    align-items: center;
}
.vrc-register-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    margin: 0;
    user-select: none;
}
.vrc-register-label input[type="checkbox"] {
    width: 17px; height: 17px;
    accent-color: #FE5001;
    cursor: pointer;
    flex-shrink: 0;
}
.vrc-register-fields {
    margin-top: 14px;
    border-top: 1px solid #e5e7eb;
    padding-top: 14px;
}
.vrc-reg-info-note {
    font-size: 13px;
    color: #6b7280;
    margin: 0 0 12px;
}
/* Username row: input + button side by side */
.vrc-reg-username-row {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}
.vrc-reg-username-row .vrc-float-wrap {
    flex: 1;
}
.vrc-reg-create-btn {
    flex-shrink: 0;
    height: 42px;
    padding: 0 18px;
    font-size: 13px;
    white-space: nowrap;
}
.vrc-reg-error {
    margin-top: 10px;
    padding: 8px 12px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 6px;
    color: #dc2626;
    font-size: 13px;
}
.vrc-reg-success {
    margin-top: 10px;
    padding: 8px 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
    color: #16a34a;
    font-size: 13px;
}
/* Disable button spinner state */
.vrc-reg-create-btn[disabled] {
    opacity: .65;
    cursor: not-allowed;
}
</style>

<script type="text/javascript">
/* ══════════════════════════════════════════════════════════════════════════
   PHASE 3 — Registration section JS  (rewritten)
   ─────────────────────────────────────────────────────────────────────────
   Flow:
     1. Guest ticks checkbox → username input + "Creează Cont" button appear.
     2. Guest clicks "Creează Cont":
          • Reads email from [data-vrc-field-type="email"] input.
          • Reads name  from [data-vrc-field-type="name"]  input(s).
          • POSTs to register-ajax.php  (server generates password + emails it).
          • On success / EMAIL_EXISTS → marks section as "done", unlocks submit.
          • On hard error → shows message inline; does NOT block the booking.
     3. Booking form submit is INDEPENDENT — checkvrcFields() runs normally.
        No submit interception — registration is a pre-step, not a gate.
   ══════════════════════════════════════════════════════════════════════════ */
(function ($) {
    'use strict';

    /* ── Config / i18n ───────────────────────────────────────────────── */
    var REGISTER_AJAX  = '<?php echo $registerAjaxUrl; ?>';
    var CREATING_LABEL = '<?php echo addslashes(JText::_('VRC_CREATING_ACCOUNT') ?: 'Se creează contul…'); ?>';
    var ERR_EMAIL      = '<?php echo addslashes(JText::_('VRC_REG_ERR_EMAIL')   ?: 'Completați câmpul de email de mai sus înainte de a crea contul.'); ?>';
    var ERR_NAME       = '<?php echo addslashes(JText::_('VRC_REG_ERR_NAME')    ?: 'Completați câmpul cu numele de mai sus înainte de a crea contul.'); ?>';
    var ERR_GENERIC    = '<?php echo addslashes(JText::_('VRC_REG_ERR_GENERIC') ?: 'Eroare la crearea contului. Puteți continua rezervarea.'); ?>';
    var OK_LABEL       = '<?php echo addslashes(JText::_('VRC_REG_ACCOUNT_CREATED') ?: 'Cont creat! Parola a fost trimisă pe email.'); ?>';

    /* ── Element refs ────────────────────────────────────────────────── */
    var $checkbox   = $('#vrc-reg-checkbox');
    var $fields     = $('#vrc-reg-fields');
    var $regBtn     = $('#vrc-reg-btn');
    var $usernameWrap = $('#vrc-reg-username-wrap');
    var $usernameInp  = $('#vrc-reg-username');
    var $error      = $('#vrc-reg-error');
    var $success    = $('#vrc-reg-success');

    /* ── State ───────────────────────────────────────────────────────── */
    var registrationDone = false;  // true once account created / EMAIL_EXISTS

    /* ── Helpers ─────────────────────────────────────────────────────── */

    /**
     * Read the email value from whichever cfield is marked as email.
     * Handles both plain <input> and phone-widget containers.
     */
    function getEmail() {
        // Primary: input with data-vrc-field-type="email"
        var $el = $('input[data-vrc-field-type="email"], textarea[data-vrc-field-type="email"]');
        if ($el.length) return $.trim($el.first().val());
        // Fallback: any input whose name starts with "vrcf" and has type="email"
        var $fb = $('input[type="email"][name^="vrcf"]');
        if ($fb.length) return $.trim($fb.first().val());
        return '';
    }

    /**
     * Read the full name by combining all name-type cfields.
     * Supports single "full name" field or separate first + last fields.
     */
    function getName() {
        var $nameFields = $('input[data-vrc-field-type="name"], textarea[data-vrc-field-type="name"]');
        if (!$nameFields.length) return '';
        if ($nameFields.length === 1) {
            return $.trim($nameFields.first().val());
        }
        // Two fields: first + last
        return ($.trim($nameFields.eq(0).val()) + ' ' + $.trim($nameFields.eq(1).val())).trim();
    }

    /** Suggested username: sanitised email local-part, or what user typed. */
    function getSuggestedUsername() {
        var typed = $.trim($usernameInp.val());
        if (typed) return typed;
        var email = getEmail();
        if (!email) return '';
        return email.split('@')[0].replace(/[^a-zA-Z0-9._\-]/g, '').toLowerCase().substring(0, 60);
    }

    function showError(msg)   { $error.text(msg).show(); $success.hide(); }
    function showSuccess(msg) { $success.text(msg).show(); $error.hide(); }
    function clearFeedback()  { $error.hide().text(''); $success.hide().text(''); }

    /* ── Checkbox toggle ─────────────────────────────────────────────── */
    $checkbox.on('change', function () {
        if ($(this).is(':checked')) {
            // Pre-fill username suggestion from email field
            if (!$.trim($usernameInp.val())) {
                var suggested = getSuggestedUsername();
                if (suggested) {
                    $usernameInp.val(suggested);
                    $usernameWrap.addClass('vrc-float-has-val');
                }
            }
            $fields.slideDown(220);
        } else {
            $fields.slideUp(200);
            clearFeedback();
            registrationDone = false;
            $regBtn.prop('disabled', false)
                   .text('<?php echo addslashes(JText::_('VRC_REG_CREATE_BTN') ?: 'Creează Cont'); ?>');
        }
    });

    /* Float-label behaviour for username input */
    $usernameInp.on('input change', function () {
        if ($(this).val()) {
            $usernameWrap.addClass('vrc-float-has-val');
        } else {
            $usernameWrap.removeClass('vrc-float-has-val');
        }
    });

    /* ── "Creează Cont" button click ─────────────────────────────────── */
    $regBtn.on('click', function () {

        if (registrationDone) {
            // Already registered — just submit the booking form
            if (checkvrcFields()) { document.vrc.submit(); }
            return;
        }

        clearFeedback();

        /* Gather data from main booking cfields */
        var email = getEmail();
        var name  = getName();

        if (!email || !/\S+@\S+\.\S+/.test(email)) {
            showError(ERR_EMAIL);
            return;
        }
        if (!name) {
            showError(ERR_NAME);
            return;
        }

        var username = getSuggestedUsername() || email.split('@')[0];

        /* Disable button and show progress */
        $regBtn.prop('disabled', true).text(CREATING_LABEL);

        $.ajax({
            url:         REGISTER_AJAX,
            type:        'POST',
            contentType: 'application/json',
            data:        JSON.stringify({
                reg_name:     name,
                reg_email:    email,
                reg_username: username   // server falls back to auto-gen if collision
            }),
            dataType: 'json'
        })
        .done(function (res) {
            if (res && res.ok) {
                registrationDone = true;
                showSuccess(OK_LABEL);
                $regBtn.prop('disabled', false)
                       .text('<?php echo addslashes(JText::_('VRORDCONFIRM') ?: 'Confirmă Rezervarea'); ?>');
                return;
            }

            if (res && res.error_code === 'EMAIL_EXISTS') {
                // Soft: already has account — treat as success so booking can proceed
                registrationDone = true;
                showSuccess('<?php echo addslashes(JText::_('VRC_REG_EMAIL_EXISTS_INFO') ?: 'Există deja un cont cu acest email. Puteți continua rezervarea.'); ?>');
                $regBtn.prop('disabled', false)
                       .text('<?php echo addslashes(JText::_('VRORDCONFIRM') ?: 'Confirmă Rezervarea'); ?>');
                return;
            }

            // Hard error — show message but let user still submit booking
            var msg = (res && res.error) ? res.error : ERR_GENERIC;
            showError(msg);
            $regBtn.prop('disabled', false)
                   .text('<?php echo addslashes(JText::_('VRC_REG_CREATE_BTN') ?: 'Creează Cont'); ?>');
        })
        .fail(function () {
            showError('<?php echo addslashes(JText::_('VRC_REG_ERR_NETWORK') ?: 'Eroare de rețea. Puteți continua rezervarea fără cont.'); ?>');
            $regBtn.prop('disabled', false)
                   .text('<?php echo addslashes(JText::_('VRC_REG_CREATE_BTN') ?: 'Creează Cont'); ?>');
        });
    });

    /* ── Guard the booking form submit ───────────────────────────────── */
    /*
     * When checkbox is checked but user clicks the main "Confirmă Rezervarea"
     * submit button WITHOUT first hitting "Creează Cont", gently remind them.
     * We do NOT block the booking — registration is always optional.
     */
    $(document).on('submit', 'form[name="vrc"]', function () {
        if ($checkbox.is(':checked') && !registrationDone) {
            // Non-blocking nudge: show a note, but still let form go through
            showError('<?php echo addslashes(JText::_('VRC_REG_NUDGE') ?: 'Apăsați «Creează Cont» pentru a crea contul înainte de a rezerva, sau debifați caseta pentru a continua fără cont.'); ?>');
            // Scroll nudge into view
            var $section = $('#vrc-register-section');
            if ($section.length) {
                $('html,body').animate({ scrollTop: $section.offset().top - 80 }, 300);
            }
            return false;   // block submit so user sees the nudge
        }
        // No checkbox or already done — let checkvrcFields() + normal submit proceed
        return true;
    });

})(jQuery);
</script>