<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - Hero ACM Style 1
 * ------------------------------------------------------------------------
 * Homepage hero section with:
 *   - Background image, heading, subheading
 *   - Dynamic car count + min price from VikRentCar
 *   - Client photos social proof
 *   - "Sună acum" button with messenger hover animation
 * ------------------------------------------------------------------------
 * CSS fix: all child selectors use  #uid .hero-xxx  (descendant scoping)
 *          instead of the invalid   .#uid-xxx        syntax.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$uid = 'ar-hero-' . $module->id;

/* ── ACM fields ─────────────────────────────────────────────────────── */
$bgImage         = $helper->get('bg-image');
$heading         = $helper->get('heading');
$subheading      = $helper->get('subheading');
$chooseCarUrl    = $helper->get('choose-car-url');
$chooseCarLabel  = $helper->get('choose-car-label');
$carsAvailLabel  = $helper->get('cars-available-label');
$priceNote       = $helper->get('price-note');
$peopleCount     = $helper->get('people-count');
$promoText       = $helper->get('promo-text');
$callBtnLabel    = $helper->get('call-btn-label');
$phoneLink       = $helper->get('phone-link');
$whatsappLink    = $helper->get('whatsapp-link');
$telegramLink    = $helper->get('telegram-link');
$viberLink       = $helper->get('viber-link');

/* ── Client photos from jalist ──────────────────────────────────────── */
$clientPhotos = array();
$photoCount = $helper->getRows('client-photo.photo');
for ($i = 0; $i < $photoCount; $i++) {
    $photo = $helper->get('client-photo.photo', $i);
    if (!empty($photo)) {
        $clientPhotos[] = $photo;
    }
}

/* ── Bootstrap VikRentCar library ───────────────────────────────────── */
$vrcLibPath = JPATH_SITE . '/components/com_vikrentcar/helpers/lib.vikrentcar.php';
if (!class_exists('VikRentCar') && file_exists($vrcLibPath)) {
    $definesPath = JPATH_SITE . '/components/com_vikrentcar/helpers/adapter/defines.php';
    if (file_exists($definesPath)) {
        include_once $definesPath;
    }
    require_once $vrcLibPath;
}

/* ── Fetch dynamic data from VikRentCar ─────────────────────────────── */
$availableCars = 0;
$minPrice      = 0;
$currencysymb  = '';

if (class_exists('VikRentCar')) {
    $dbo = JFactory::getDbo();
    $currencysymb = VikRentCar::getCurrencySymb();

    $dbo->setQuery("SELECT COUNT(*) FROM `#__vikrentcar_cars` WHERE `avail`='1'");
    $availableCars = (int) $dbo->loadResult();

    $dbo->setQuery("SELECT MIN(`startfrom`) FROM `#__vikrentcar_cars` WHERE `avail`='1' AND `startfrom` > 0");
    $minPrice = (float) $dbo->loadResult();
    if ($minPrice > 0) {
        $minPrice = VikRentCar::numberFormat($minPrice);
    }
}

/* ── Fallback values ────────────────────────────────────────────────── */
if (empty($bgImage))        $bgImage       = 'images/backgrounds/hero.jpg';
if (empty($heading))        $heading       = Text::_('HERO_HEADING_DEFAULT');
if (empty($subheading))     $subheading    = Text::_('HERO_SUBHEADING_DEFAULT');
if (empty($chooseCarUrl))   $chooseCarUrl  = 'cars';
if (empty($chooseCarLabel)) $chooseCarLabel = Text::_('HERO_CHOOSE_CAR_DEFAULT');
if (empty($carsAvailLabel)) $carsAvailLabel = Text::_('HERO_CARS_AVAILABLE_DEFAULT');
if (empty($priceNote))      $priceNote     = Text::_('HERO_PRICE_NOTE_DEFAULT');
if (empty($peopleCount))    $peopleCount   = Text::_('HERO_PEOPLE_COUNT_DEFAULT');
if (empty($promoText))      $promoText     = Text::_('HERO_PROMO_TEXT_DEFAULT');
if (empty($callBtnLabel))   $callBtnLabel  = Text::_('HERO_CALL_BTN_DEFAULT');

/* ── Messenger & widget labels ──────────────────────────────────────── */
$messengerPhone   = $helper->get('messenger-phone-label')   ?: Text::_('HERO_MESSENGER_PHONE');
$messengerWhatsapp = $helper->get('messenger-whatsapp-label') ?: Text::_('HERO_MESSENGER_WHATSAPP');
$messengerTelegram = $helper->get('messenger-telegram-label') ?: Text::_('HERO_MESSENGER_TELEGRAM');
$messengerViber   = $helper->get('messenger-viber-label')    ?: Text::_('HERO_MESSENGER_VIBER');
$priceFromLabel   = $helper->get('price-from-label')         ?: Text::_('HERO_PRICE_FROM');
$pricePerDayLabel = $helper->get('price-per-day-label')      ?: Text::_('HERO_PRICE_PER_DAY');
$clientAltText    = $helper->get('client-alt-text')          ?: Text::_('HERO_CLIENT_ALT');
?>

<style>
/* ═══ Hero ACM Style 1 ══════════════════════════════════════════════════
   Scoping: all child rules use  #uid .hero-xxx  (descendant selector).
   This is valid CSS and avoids the broken  .#uid-xxx  class pattern.
══════════════════════════════════════════════════════════════════════ */

/* ── Section wrapper ─────────────────────────────────────────────── */
#<?php echo $uid; ?> {
    position: relative;
    overflow: hidden;
    background: #fff;
    min-height: 365px;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> { min-height: 385px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> { min-height: 425px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> { min-height: 465px; } }

/* ── Background ──────────────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}
#<?php echo $uid; ?> .hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: 70% 75%;
}
#<?php echo $uid; ?> .hero-bg-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,.5), rgba(0,0,0,.3), rgba(0,0,0,.5));
}

/* ── Layout container ────────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-inner {
    max-width: 1440px;
    position: relative;
    margin: 0 auto;
    z-index: 10;
    height: 100%;
}
#<?php echo $uid; ?> .hero-flex {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
}
#<?php echo $uid; ?> .hero-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 20px;
    position: relative;
    width: 100%;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-content { padding: 25px 30px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-content { padding: 30px 40px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-content { padding: 35px 50px; } }

/* ── Two-column row ──────────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-row {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: space-between;
    position: relative;
    width: 100%;
    gap: 24px;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-row { gap: 32px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-row { flex-direction: row; gap: 16px; } }
@media (min-width: 1280px) { #<?php echo $uid; ?> .hero-row { gap: 0; } }

/* ── Left column ─────────────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-left {
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
    width: 100%;
    order: 1;
}
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-left { max-width: 500px; gap: 20px; } }
@media (min-width: 1280px) { #<?php echo $uid; ?> .hero-left { max-width: 600px; gap: 24px; } }

/* ── Text stack ──────────────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-text-stack {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-text-stack { gap: 16px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-text-stack { gap: 20px; } }

/* ── Heading ─────────────────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-heading {
    font-weight: 700;
    line-height: 1.15;
    color: #fff;
    font-size: 24px;
    margin: 0;
    text-shadow: 0 2px 8px rgba(0,0,0,.25);
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-heading { font-size: 32px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-heading { font-size: 42px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-heading { font-size: 54px; } }
@media (min-width: 1280px) { #<?php echo $uid; ?> .hero-heading { font-size: 62px; } }

/* ── Subheading ──────────────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-subheading {
    font-weight: 400;
    line-height: 1.5;
    color: rgba(255,255,255,.95);
    font-size: 14px;
    max-width: 500px;
    margin: 0;
    text-shadow: 0 1px 4px rgba(0,0,0,.2);
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-subheading { font-size: 15px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-subheading { font-size: 16px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-subheading { font-size: 17px; max-width: none; } }

/* ── Button row ──────────────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-btns {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
}
@media (min-width: 640px) { #<?php echo $uid; ?> .hero-btns { flex-direction: row; width: auto; } }

/* ── Choose car button ───────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-choose-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #FE5001;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    padding: 0 32px;
    text-decoration: none;
    transition: background .2s;
    cursor: pointer;
    height: 44px;
    width: 100%;
    white-space: nowrap;
}
@media (min-width: 640px) { #<?php echo $uid; ?> .hero-choose-btn { width: auto; min-width: 150px; height: 46px; } }
@media (min-width: 768px) { #<?php echo $uid; ?> .hero-choose-btn { height: 48px; font-size: 16px; } }
#<?php echo $uid; ?> .hero-choose-btn:hover { background: #E54801; color: #fff; text-decoration: none; }

/* ── Call button wrapper ─────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-call-wrap {
    position: relative;
}

/* Fade out call button when messengers are open */
#<?php echo $uid; ?> .hero-call-wrap:has(.hero-messengers.is-open) .hero-call-btn {
    opacity: 0;
    pointer-events: none;
    transition: opacity .3s ease;
}

/* ── Call button (desktop ≥640px) ────────────────────────────────── */
#<?php echo $uid; ?> .hero-call-btn {
    display: none;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #fff;
    color: #FE5001;
    border: 2px solid #fff;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    padding: 0 32px;
    cursor: pointer;
    height: 44px;
    white-space: nowrap;
    transition: background .2s;
}
@media (min-width: 640px) { #<?php echo $uid; ?> .hero-call-btn { display: flex; min-width: 150px; height: 46px; } }
@media (min-width: 768px) { #<?php echo $uid; ?> .hero-call-btn { height: 48px; font-size: 16px; } }
#<?php echo $uid; ?> .hero-call-btn:hover { background: #f3f4f6; }
#<?php echo $uid; ?> .hero-call-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

/* ── Mobile call button (<640px) ─────────────────────────────────── */
#<?php echo $uid; ?> .hero-call-btn-mobile {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #fff;
    color: #FE5001;
    border: 2px solid #fff;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    padding: 0 28px;
    cursor: pointer;
    height: 44px;
    width: 100%;
    transition: background .2s;
}
@media (min-width: 640px) { #<?php echo $uid; ?> .hero-call-btn-mobile { display: none; } }
#<?php echo $uid; ?> .hero-call-btn-mobile:hover { background: #f3f4f6; }
#<?php echo $uid; ?> .hero-call-btn-mobile svg { width: 16px; height: 16px; flex-shrink: 0; }

/* ── Desktop messenger bubbles (shown on .is-open) ───────────────── */
#<?php echo $uid; ?> .hero-messengers {
    position: absolute;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    z-index: 20;
    pointer-events: none;
}
#<?php echo $uid; ?> .hero-messengers.is-open { pointer-events: auto; }

#<?php echo $uid; ?> .hero-messenger-link {
    position: relative;
    display: flex;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    color: #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,.25);
    transition: opacity .3s, transform .3s;
    opacity: 0;
    transform: translateX(-10px) scale(.8);
    text-decoration: none;
}
#<?php echo $uid; ?> .hero-messengers.is-open .hero-messenger-link {
    opacity: 1;
    transform: translateX(0) scale(1);
}
#<?php echo $uid; ?> .hero-messenger-link svg { width: 24px; height: 24px; }
#<?php echo $uid; ?> .hero-messenger-link:hover { opacity: .85 !important; }

#<?php echo $uid; ?> .hero-messenger-phone    { background: #000; }
#<?php echo $uid; ?> .hero-messenger-whatsapp { background: #25D366; }
#<?php echo $uid; ?> .hero-messenger-telegram { background: #0088CC; }
#<?php echo $uid; ?> .hero-messenger-viber    { background: #7360F2; }

/* staggered entrance delay */
#<?php echo $uid; ?> .hero-messengers.is-open .hero-messenger-link:nth-child(1) { transition-delay: 0ms; }
#<?php echo $uid; ?> .hero-messengers.is-open .hero-messenger-link:nth-child(2) { transition-delay: 60ms; }
#<?php echo $uid; ?> .hero-messengers.is-open .hero-messenger-link:nth-child(3) { transition-delay: 120ms; }
#<?php echo $uid; ?> .hero-messengers.is-open .hero-messenger-link:nth-child(4) { transition-delay: 180ms; }

/* ── Messenger tooltip ───────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-messenger-tooltip {
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,.75);
    color: #fff;
    font-size: 11px;
    font-weight: 500;
    padding: 3px 8px;
    border-radius: 4px;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transition: opacity .2s;
}
#<?php echo $uid; ?> .hero-messenger-tooltip::before {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-top-color: rgba(0,0,0,.75);
}
#<?php echo $uid; ?> .hero-messenger-link:hover .hero-messenger-tooltip { opacity: 1; }

/* ── Mobile messenger list ───────────────────────────────────────── */
#<?php echo $uid; ?> .hero-mobile-messengers {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
    margin-top: 4px;
    opacity: 0;
    max-height: 0;
    overflow: hidden;
    transition: opacity .3s ease, max-height .3s ease;
}
#<?php echo $uid; ?> .hero-mobile-messengers.is-open {
    opacity: 1;
    max-height: 300px;
}
@media (min-width: 640px) { #<?php echo $uid; ?> .hero-mobile-messengers { display: none; } }

#<?php echo $uid; ?> .hero-mobile-messenger-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    padding: 8px 12px;
    text-decoration: none;
    transition: opacity .2s;
    white-space: nowrap;
    width: 100%;
}
#<?php echo $uid; ?> .hero-mobile-messenger-link:hover { opacity: .85; color: #fff; text-decoration: none; }
#<?php echo $uid; ?> .hero-mobile-messenger-link svg { width: 18px; height: 18px; flex-shrink: 0; }
#<?php echo $uid; ?> .hero-mobile-messenger-phone    { background: #000; border: 1px solid #ffffff66; }
#<?php echo $uid; ?> .hero-mobile-messenger-whatsapp { background: #25D366; }
#<?php echo $uid; ?> .hero-mobile-messenger-telegram { background: #0088CC; }
#<?php echo $uid; ?> .hero-mobile-messenger-viber    { background: #7360F2; }

/* ── Social proof strip ──────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-social-proof {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
    width: 100%;
    margin-top: 16px;
}
#<?php echo $uid; ?> .hero-avatars-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
#<?php echo $uid; ?> .hero-avatars {
    display: flex;
    align-items: flex-start;
}
#<?php echo $uid; ?> .hero-avatar {
    position: relative;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.2);
    overflow: hidden;
    margin-left: -14px;
    flex-shrink: 0;
}
#<?php echo $uid; ?> .hero-avatar:first-child { margin-left: 0; }
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-avatar { width: 40px; height: 40px; margin-left: -16px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-avatar { width: 44px; height: 44px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-avatar { width: 48px; height: 48px; } }
#<?php echo $uid; ?> .hero-avatar img { width: 100%; height: 100%; object-fit: cover; }

#<?php echo $uid; ?> .hero-people-count {
    font-weight: 600;
    color: #fff;
    font-size: 16px;
    text-shadow: 0 1px 4px rgba(0,0,0,.2);
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-people-count { font-size: 17px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-people-count { font-size: 18px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-people-count { font-size: 19px; } }

#<?php echo $uid; ?> .hero-promo-text {
    font-weight: 400;
    line-height: 1.4;
    color: rgba(255,255,255,.95);
    font-size: 12px;
    text-shadow: 0 1px 4px rgba(0,0,0,.15);
}
@media (min-width: 640px) { #<?php echo $uid; ?> .hero-promo-text { font-size: 13px; } }

/* ── Right column ────────────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-right {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    order: 2;
    margin-top: 24px;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-right { margin-top: 0; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-right { width: 500px; justify-content: flex-end; } }
@media (min-width: 1280px) { #<?php echo $uid; ?> .hero-right { width: 670px; } }

/* ── Widgets container ───────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-widgets {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 24px;
    width: 100%;
    margin-top: 32px;
}
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-widgets { margin-top: 48px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-widgets { margin-top: 80px; } }

#<?php echo $uid; ?> .hero-widgets-row {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 14px;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-widgets-row { gap: 18px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-widgets-row { gap: 24px; } }

/* ── Widget card (shared) ────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-widget {
    -webkit-backdrop-filter: blur(15px);
    backdrop-filter: blur(15px);
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: center;
    justify-content: center;
    padding: 16px;
    position: relative;
    border-radius: 16px;
    box-shadow: 0 12px 35px rgba(0,0,0,.3);
    background: linear-gradient(100.458deg, rgba(255,255,255,.75) 0%, rgba(255,255,255,.6) 100%);
    width: 115px;
    height: 115px;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-widget { width: 125px; height: 125px; gap: 5px; padding: 18px; border-radius: 18px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-widget { width: 135px; height: 135px; padding: 20px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-widget { width: 145px; height: 145px; } }

#<?php echo $uid; ?> .hero-widget-border {
    position: absolute;
    inset: 0;
    border: 3px solid rgba(255,255,255,.8);
    border-radius: 16px;
    pointer-events: none;
}
@media (min-width: 640px) { #<?php echo $uid; ?> .hero-widget-border { border-radius: 18px; } }

#<?php echo $uid; ?> .hero-widget-value {
    font-weight: 700;
    color: #111;
    font-size: 32px;
    text-align: center;
    line-height: 1.1;
    text-shadow: 0 1px 2px rgba(0,0,0,.1);
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-widget-value { font-size: 36px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-widget-value { font-size: 40px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-widget-value { font-size: 44px; } }

#<?php echo $uid; ?> .hero-widget-label {
    font-weight: 600;
    color: #555;
    font-size: 10px;
    text-align: center;
    line-height: 1.3;
    text-shadow: 0 1px 2px rgba(0,0,0,.1);
    padding: 0 4px;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-widget-label { font-size: 11px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-widget-label { font-size: 12px; } }

/* ── Price widget (wider) ────────────────────────────────────────── */
#<?php echo $uid; ?> .hero-widget-price {
    width: 145px;
    gap: 2px;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-widget-price { width: 160px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-widget-price { width: 175px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-widget-price { width: 185px; } }

#<?php echo $uid; ?> .hero-widget-price .hero-widget-value {
    font-size: 28px;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> .hero-widget-price .hero-widget-value { font-size: 32px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> .hero-widget-price .hero-widget-value { font-size: 36px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> .hero-widget-price .hero-widget-value { font-size: 40px; } }

#<?php echo $uid; ?> .hero-widget-from,
#<?php echo $uid; ?> .hero-widget-note {
    font-weight: 600;
    color: #555;
    font-size: 9px;
    text-align: center;
    line-height: 1.2;
    text-shadow: 0 1px 2px rgba(0,0,0,.1);
    white-space: nowrap;
}
@media (min-width: 640px) {
    #<?php echo $uid; ?> .hero-widget-from,
    #<?php echo $uid; ?> .hero-widget-note { font-size: 10px; }
}
@media (min-width: 768px) {
    #<?php echo $uid; ?> .hero-widget-from,
    #<?php echo $uid; ?> .hero-widget-note { font-size: 11px; }
}
</style>

<section id="<?php echo $uid; ?>" class="acm-hero style-1">

    <!-- Background -->
    <div class="hero-bg">
        <img src="<?php echo htmlspecialchars($bgImage); ?>" alt="<?php echo htmlspecialchars($heading); ?>" loading="eager">
        <div class="hero-bg-overlay"></div>
    </div>

    <!-- Content -->
    <div class="hero-inner">
        <div class="hero-flex">
            <div class="hero-content">
                <div class="hero-row">

                    <!-- ── Left column ──────────────────────────────── -->
                    <div class="hero-left">
                        <div class="hero-text-stack">

                            <h1 class="hero-heading"><?php echo htmlspecialchars($heading); ?></h1>

                            <?php if (!empty($subheading)): ?>
                            <p class="hero-subheading"><?php echo htmlspecialchars($subheading); ?></p>
                            <?php endif; ?>

                            <!-- Buttons -->
                            <div class="hero-btns">
                                <a href="<?php echo htmlspecialchars($chooseCarUrl); ?>" class="hero-choose-btn">
                                    <?php echo htmlspecialchars($chooseCarLabel); ?>
                                </a>

                                <!-- Call / Messengers – desktop -->
                                <div class="hero-call-wrap">
                                    <button class="hero-call-btn" id="<?php echo $uid; ?>-call-trigger" type="button"
                                            aria-label="<?php echo htmlspecialchars($callBtnLabel); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                        <?php echo htmlspecialchars($callBtnLabel); ?>
                                    </button>

                                    <div class="hero-messengers" id="<?php echo $uid; ?>-messengers">
                                        <?php if (!empty($phoneLink)): ?>
                                        <a href="<?php echo htmlspecialchars($phoneLink); ?>" target="_blank" rel="noopener noreferrer"
                                           class="hero-messenger-link hero-messenger-phone" aria-label="Phone">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                            <div class="hero-messenger-tooltip"><?php echo htmlspecialchars($messengerPhone); ?></div>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($whatsappLink)): ?>
                                        <a href="<?php echo htmlspecialchars($whatsappLink); ?>" target="_blank" rel="noopener noreferrer"
                                           class="hero-messenger-link hero-messenger-whatsapp" aria-label="WhatsApp">
                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path></svg>
                                            <div class="hero-messenger-tooltip"><?php echo htmlspecialchars($messengerWhatsapp); ?></div>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($telegramLink)): ?>
                                        <a href="<?php echo htmlspecialchars($telegramLink); ?>" target="_blank" rel="noopener noreferrer"
                                           class="hero-messenger-link hero-messenger-telegram" aria-label="Telegram">
                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"></path></svg>
                                            <div class="hero-messenger-tooltip"><?php echo htmlspecialchars($messengerTelegram); ?></div>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($viberLink)): ?>
                                        <a href="<?php echo htmlspecialchars($viberLink); ?>" target="_blank" rel="noopener noreferrer"
                                           class="hero-messenger-link hero-messenger-viber" aria-label="Viber"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.398.002C9.473.028 5.331.344 3.014 2.467 1.294 4.177.693 6.698.623 9.82c-.06 3.11-.13 8.95 5.5 10.541v2.42s-.038.97.602 1.17c.79.25 1.24-.499 1.99-1.299l1.4-1.58c3.85.32 6.8-.419 7.14-.529.78-.25 5.181-.811 5.901-6.652.74-6.031-.36-9.831-2.34-11.551l-.01-.002c-.6-.55-3-2.3-8.37-2.32 0 0-.396-.025-1.038-.016zm.067 1.697c.545-.003.88.02.88.02 4.54.01 6.711 1.38 7.221 1.84 1.67 1.429 2.528 4.856 1.9 9.892-.6 4.88-4.17 5.19-4.83 5.4-.28.09-2.88.73-6.152.52 0 0-2.439 2.941-3.199 3.701-.12.13-.26.17-.35.15-.13-.03-.17-.19-.16-.41l.02-4.019c-4.771-1.32-4.491-6.302-4.441-8.902.06-2.6.55-4.732 2-6.172 1.957-1.77 5.475-2.01 7.11-2.02zm.36 2.6a.299.299 0 0 0-.3.299.3.3 0 0 0 .3.3 5.631 5.631 0 0 1 4.03 1.59 5.458 5.458 0 0 1 1.591 3.96.3.3 0 0 0 .3.3.3.3 0 0 0 .299-.3 6.014 6.014 0 0 0-1.769-4.34 6.234 6.234 0 0 0-4.451-1.769zm-3.954.69a.955.955 0 0 0-.615.12h-.012c-.41.24-.788.54-1.148.94-.27.32-.421.639-.461.949a1.24 1.24 0 0 0 .05.541l.02.01a13.722 13.722 0 0 0 1.2 2.6 15.383 15.383 0 0 0 2.32 3.171l.03.04.04.03.03.03.03.03a15.603 15.603 0 0 0 3.2 2.33c1.32.72 2.1 1.06 2.65 1.2v.01c.13.04.24.06.33.06a1.25 1.25 0 0 0 .84-.35 1.72 1.72 0 0 0 .16-.14c.39-.41.719-.849.97-1.289v-.01a.571.571 0 0 0-.12-.73l-2.26-1.699a.63.63 0 0 0-.75.06l-1 .75a.301.301 0 0 1-.32.03c-.319-.129-1.229-.58-2.3-1.648-1.068-1.07-1.518-1.98-1.648-2.299a.299.299 0 0 1 .03-.32l.747-.999a.632.632 0 0 0 .061-.751L8.323 5.612a.634.634 0 0 0-.533-.334zm3.613 1.249a.297.297 0 0 0-.299.3.3.3 0 0 0 .3.299 3.734 3.734 0 0 1 2.65 1.05 3.553 3.553 0 0 1 1.06 2.58.3.3 0 0 0 .3.3.3.3 0 0 0 .299-.3 4.105 4.105 0 0 0-1.219-2.98 4.333 4.333 0 0 0-3.061-1.219zm.33 1.989a.299.299 0 0 0-.3.3.3.3 0 0 0 .3.3 1.765 1.765 0 0 1 1.25.53c.33.33.5.73.51 1.2a.3.3 0 0 0 .3.299.3.3 0 0 0 .3-.3 2.264 2.264 0 0 0-.661-1.579 2.326 2.326 0 0 0-1.649-.69z"></path></svg>
                                            <div class="hero-messenger-tooltip"><?php echo htmlspecialchars($messengerViber); ?></div>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Call / Messengers – mobile -->
                                <button class="hero-call-btn-mobile" id="<?php echo $uid; ?>-call-trigger-mobile" type="button"
                                        aria-label="<?php echo htmlspecialchars($callBtnLabel); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    <?php echo htmlspecialchars($callBtnLabel); ?>
                                </button>
                            </div>

                            <!-- Mobile messenger links (shown below buttons) -->
                            <div class="hero-mobile-messengers" id="<?php echo $uid; ?>-mobile-messengers">
                                <?php if (!empty($phoneLink)): ?>
                                <a href="<?php echo htmlspecialchars($phoneLink); ?>"
                                   class="hero-mobile-messenger-link hero-mobile-messenger-phone" aria-label="Phone">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    <?php echo htmlspecialchars($messengerPhone); ?>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($whatsappLink)): ?>
                                <a href="<?php echo htmlspecialchars($whatsappLink); ?>" target="_blank" rel="noopener noreferrer"
                                   class="hero-mobile-messenger-link hero-mobile-messenger-whatsapp" aria-label="WhatsApp">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path></svg>
                                    <?php echo htmlspecialchars($messengerWhatsapp); ?>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($telegramLink)): ?>
                                <a href="<?php echo htmlspecialchars($telegramLink); ?>" target="_blank" rel="noopener noreferrer"
                                   class="hero-mobile-messenger-link hero-mobile-messenger-telegram" aria-label="Telegram">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"></path></svg>
                                    <?php echo htmlspecialchars($messengerTelegram); ?>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($viberLink)): ?>
                                <a href="<?php echo htmlspecialchars($viberLink); ?>" target="_blank" rel="noopener noreferrer"
                                   class="hero-mobile-messenger-link hero-mobile-messenger-viber" aria-label="Viber">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.398.002C9.473.028 5.331.344 3.014 2.467 1.294 4.177.693 6.698.623 9.82c-.06 3.11-.13 8.95 5.5 10.541v2.42s-.038.97.602 1.17c.79.25 1.24-.499 1.99-1.299l1.4-1.58c3.85.32 6.8-.419 7.14-.529.78-.25 5.181-.811 5.901-6.652.74-6.031-.36-9.831-2.34-11.551l-.01-.002c-.6-.55-3-2.3-8.37-2.32 0 0-.396-.025-1.038-.016zm.067 1.697c.545-.003.88.02.88.02 4.54.01 6.711 1.38 7.221 1.84 1.67 1.429 2.528 4.856 1.9 9.892-.6 4.88-4.17 5.19-4.83 5.4-.28.09-2.88.73-6.152.52 0 0-2.439 2.941-3.199 3.701-.12.13-.26.17-.35.15-.13-.03-.17-.19-.16-.41l.02-4.019c-4.771-1.32-4.491-6.302-4.441-8.902.06-2.6.55-4.732 2-6.172 1.957-1.77 5.475-2.01 7.11-2.02zm.36 2.6a.299.299 0 0 0-.3.299.3.3 0 0 0 .3.3 5.631 5.631 0 0 1 4.03 1.59 5.458 5.458 0 0 1 1.591 3.96.3.3 0 0 0 .3.3.3.3 0 0 0 .299-.3 6.014 6.014 0 0 0-1.769-4.34 6.234 6.234 0 0 0-4.451-1.769zm-3.954.69a.955.955 0 0 0-.615.12h-.012c-.41.24-.788.54-1.148.94-.27.32-.421.639-.461.949a1.24 1.24 0 0 0 .05.541l.02.01a13.722 13.722 0 0 0 1.2 2.6 15.383 15.383 0 0 0 2.32 3.171l.03.04.04.03.03.03.03.03a15.603 15.603 0 0 0 3.2 2.33c1.32.72 2.1 1.06 2.65 1.2v.01c.13.04.24.06.33.06a1.25 1.25 0 0 0 .84-.35 1.72 1.72 0 0 0 .16-.14c.39-.41.719-.849.97-1.289v-.01a.571.571 0 0 0-.12-.73l-2.26-1.699a.63.63 0 0 0-.75.06l-1 .75a.301.301 0 0 1-.32.03c-.319-.129-1.229-.58-2.3-1.648-1.068-1.07-1.518-1.98-1.648-2.299a.299.299 0 0 1 .03-.32l.747-.999a.632.632 0 0 0 .061-.751L8.323 5.612a.634.634 0 0 0-.533-.334zm3.613 1.249a.297.297 0 0 0-.299.3.3.3 0 0 0 .3.299 3.734 3.734 0 0 1 2.65 1.05 3.553 3.553 0 0 1 1.06 2.58.3.3 0 0 0 .3.3.3.3 0 0 0 .299-.3 4.105 4.105 0 0 0-1.219-2.98 4.333 4.333 0 0 0-3.061-1.219zm.33 1.989a.299.299 0 0 0-.3.3.3.3 0 0 0 .3.3 1.765 1.765 0 0 1 1.25.53c.33.33.5.73.51 1.2a.3.3 0 0 0 .3.299.3.3 0 0 0 .3-.3 2.264 2.264 0 0 0-.661-1.579 2.326 2.326 0 0 0-1.649-.69z"></path></svg>
                                    <?php echo htmlspecialchars($messengerViber); ?>
                                </a>
                                <?php endif; ?>
                            </div>

                        </div><!-- .hero-text-stack -->

                        <!-- Social proof -->
                        <div class="hero-social-proof">
                            <div class="hero-avatars-row">
                                <?php if (!empty($clientPhotos)): ?>
                                <div class="hero-avatars">
                                    <?php foreach ($clientPhotos as $photo): ?>
                                    <div class="hero-avatar">
                                        <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($clientAltText); ?>" loading="lazy">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($peopleCount)): ?>
                                <span class="hero-people-count"><?php echo htmlspecialchars($peopleCount); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($promoText)): ?>
                            <p class="hero-promo-text"><?php echo htmlspecialchars($promoText); ?></p>
                            <?php endif; ?>
                        </div>

                    </div><!-- .hero-left -->

                    <!-- ── Right column (widgets) ───────────────────── -->
                    <div class="hero-right">
                        <div class="hero-widgets">
                            <div class="hero-widgets-row">

                                <!-- Cars available widget -->
                                <div class="hero-widget">
                                    <span class="hero-widget-value">
                                        <?php echo $availableCars > 0 ? (int)$availableCars : '—'; ?>
                                    </span>
                                    <span class="hero-widget-label">
                                        <?php echo htmlspecialchars($carsAvailLabel); ?>
                                    </span>
                                    <div class="hero-widget-border"></div>
                                </div>

                                <!-- Price widget -->
                                <?php if ($minPrice > 0): ?>
                                <div class="hero-widget hero-widget-price">
                                    <span class="hero-widget-from"><?php echo htmlspecialchars($priceFromLabel); ?></span>
                                    <span class="hero-widget-value">
                                        <?php echo $currencysymb . htmlspecialchars($minPrice); ?><?php echo htmlspecialchars($pricePerDayLabel); ?>
                                    </span>
                                    <span class="hero-widget-note">
                                        <?php echo htmlspecialchars($priceNote); ?>
                                    </span>
                                    <div class="hero-widget-border"></div>
                                </div>
                                <?php endif; ?>

                            </div><!-- .hero-widgets-row -->
                        </div><!-- .hero-widgets -->
                    </div><!-- .hero-right -->

                </div><!-- .hero-row -->
            </div><!-- .hero-content -->
        </div><!-- .hero-flex -->
    </div><!-- .hero-inner -->

</section>

<script>
(function () {
    var uid      = '<?php echo $uid; ?>';
    var trigger  = document.getElementById(uid + '-call-trigger');
    var bubbles  = document.getElementById(uid + '-messengers');
    var mTrigger = document.getElementById(uid + '-call-trigger-mobile');
    var mBubbles = document.getElementById(uid + '-mobile-messengers');

    /* Desktop: toggle messenger bubbles on button click */
    if (trigger && bubbles) {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            bubbles.classList.toggle('is-open');
        });
        document.addEventListener('click', function () {
            bubbles.classList.remove('is-open');
        });
        bubbles.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    /* Mobile: toggle messenger list below buttons with smooth animation */
    if (mTrigger && mBubbles) {
        mTrigger.addEventListener('click', function () {
            mBubbles.classList.toggle('is-open');
        });
    }
})();
</script>