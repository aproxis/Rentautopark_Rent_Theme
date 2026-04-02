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
$minPrice = 0;
$currencysymb = '';

if (class_exists('VikRentCar')) {
    $dbo = JFactory::getDbo();
    $currencysymb = VikRentCar::getCurrencySymb();

    // Count available cars
    $dbo->setQuery("SELECT COUNT(*) FROM `#__vikrentcar_cars` WHERE `avail`='1'");
    $availableCars = (int) $dbo->loadResult();

    // Get minimum startfrom price
    $dbo->setQuery("SELECT MIN(`startfrom`) FROM `#__vikrentcar_cars` WHERE `avail`='1' AND `startfrom` > 0");
    $minPrice = (float) $dbo->loadResult();
    if ($minPrice > 0) {
        $minPrice = VikRentCar::numberFormat($minPrice);
    }
}

/* ── Fallback values ────────────────────────────────────────────────── */
if (empty($bgImage))        $bgImage = 'images/backgrounds/hero.jpg';
if (empty($heading))        $heading = 'Fast booking, delivery and 24/7 support';
if (empty($subheading))     $subheading = 'Professional car rental services for personal or business needs. We choose comfort, safety and a stress-free driving experience.';
if (empty($chooseCarUrl))   $chooseCarUrl = 'cars';
if (empty($chooseCarLabel)) $chooseCarLabel = 'Choose a car';
if (empty($carsAvailLabel)) $carsAvailLabel = Text::_('HERO_CARS_AVAILABLE_LABEL') ?: 'Cars available';
if (empty($priceNote))      $priceNote = Text::_('HERO_PRICE_NOTE_LABEL') ?: 'More days, lower price';
if (empty($peopleCount))    $peopleCount = '1000+ people';
if (empty($promoText))      $promoText = 'chose to rent cars from Rent AutoPark';
if (empty($callBtnLabel))   $callBtnLabel = 'Sună acum';
?>

<style>
/* ═══ Hero ACM Style 1 ═══════════════════════════════════════════════ */
#<?php echo $uid; ?> {
    position: relative;
    overflow: hidden;
    background: #fff;
    min-height: 365px;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> { min-height: 385px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> { min-height: 425px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> { min-height: 465px; } }

/* Background */
.#<?php echo $uid; ?>-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}
.#<?php echo $uid; ?>-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: 70% 75%;
}
.#<?php echo $uid; ?>-bg-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,.5), rgba(0,0,0,.3), rgba(0,0,0,.5));
}

/* Container */
.#<?php echo $uid; ?>-inner {
    max-width: 1440px;
    position: relative;
    margin: 0 auto;
    z-index: 10;
    height: 100%;
}
.#<?php echo $uid; ?>-flex {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
}
.#<?php echo $uid; ?>-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 20px;
    position: relative;
    width: 100%;
}
@media (min-width: 640px)  { .#<?php echo $uid; ?>-content { padding: 25px 30px; } }
@media (min-width: 768px)  { .#<?php echo $uid; ?>-content { padding: 30px 40px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-content { padding: 35px 50px; } }

.#<?php echo $uid; ?>-row {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: space-between;
    position: relative;
    width: 100%;
    gap: 24px;
}
@media (min-width: 1024px) {
    .#<?php echo $uid; ?>-row {
        flex-direction: row;
        gap: 16px;
    }
}
@media (min-width: 1280px) {
    .#<?php echo $uid; ?>-row { gap: 0; }
}

/* Left column */
.#<?php echo $uid; ?>-left {
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
    width: 100%;
    max-width: 100%;
    order: 1;
}
@media (min-width: 1024px) {
    .#<?php echo $uid; ?>-left {
        max-width: 500px;
        gap: 20px;
    }
}
@media (min-width: 1280px) {
    .#<?php echo $uid; ?>-left {
        max-width: 600px;
        gap: 24px;
    }
}

/* Heading */
.#<?php echo $uid; ?>-heading {
    font-weight: 700;
    line-height: 1.15;
    color: #fff;
    font-size: 24px;
    margin: 0;
    text-shadow: 0 2px 8px rgba(0,0,0,.25);
}
@media (min-width: 640px)  { .#<?php echo $uid; ?>-heading { font-size: 32px; } }
@media (min-width: 768px)  { .#<?php echo $uid; ?>-heading { font-size: 42px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-heading { font-size: 54px; } }
@media (min-width: 1280px) { .#<?php echo $uid; ?>-heading { font-size: 62px; } }

/* Subheading */
.#<?php echo $uid; ?>-subheading {
    font-weight: 400;
    line-height: 1.5;
    color: rgba(255,255,255,.95);
    font-size: 14px;
    max-width: 500px;
    margin: 0;
    text-shadow: 0 1px 4px rgba(0,0,0,.2);
}
@media (min-width: 640px)  { .#<?php echo $uid; ?>-subheading { font-size: 15px; } }
@media (min-width: 768px)  { .#<?php echo $uid; ?>-subheading { font-size: 16px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-subheading { font-size: 17px; max-width: none; } }

/* Buttons row */
.#<?php echo $uid; ?>-btns {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
}
@media (min-width: 640px) {
    .#<?php echo $uid; ?>-btns {
        flex-direction: row;
        width: auto;
    }
}

/* Choose car button */
.#<?php echo $uid; ?>-choose-btn {
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
    padding: 12px 32px;
    text-decoration: none;
    transition: background .2s;
    cursor: pointer;
    height: 44px;
    width: 100%;
}
@media (min-width: 640px) {
    .#<?php echo $uid; ?>-choose-btn {
        width: auto;
        min-width: 150px;
        height: 46px;
    }
}
@media (min-width: 768px) {
    .#<?php echo $uid; ?>-choose-btn { height: 48px; font-size: 16px; }
}
.#<?php echo $uid; ?>-choose-btn:hover { background: #E54801; color: #fff; }

/* Call button container */
.#<?php echo $uid; ?>-call-wrap {
    position: relative;
}

/* Call button (desktop) */
.#<?php echo $uid; ?>-call-btn {
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
    padding: 12px 32px;
    text-decoration: none;
    transition: background .2s;
    cursor: pointer;
    height: 44px;
    white-space: nowrap;
}
@media (min-width: 640px) {
    .#<?php echo $uid; ?>-call-btn {
        display: flex;
        min-width: 150px;
        height: 46px;
    }
}
@media (min-width: 768px) {
    .#<?php echo $uid; ?>-call-btn { height: 48px; font-size: 16px; }
}
.#<?php echo $uid; ?>-call-btn:hover { background: #f3f4f6; color: #FE5001; }
.#<?php echo $uid; ?>-call-btn svg { width: 16px; height: 16px; }

/* Messenger bubbles (desktop - shown on hover) */
.#<?php echo $uid; ?>-messengers {
    position: absolute;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    z-index: 20;
}
.#<?php echo $uid; ?>-messenger-link {
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
.#<?php echo $uid; ?>-messenger-link svg { width: 24px; height: 24px; }
.#<?php echo $uid; ?>-messenger-phone    { background: #000; }
.#<?php echo $uid; ?>-messenger-whatsapp { background: #25D366; }
.#<?php echo $uid; ?>-messenger-telegram { background: #0088CC; }
.#<?php echo $uid; ?>-messenger-viber    { background: #7360F2; }

/* Messenger tooltip */
.#<?php echo $uid; ?>-messenger-tooltip {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: #fff;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    opacity: 0;
    transition: opacity .2s;
    pointer-events: none;
}
.#<?php echo $uid; ?>-messenger-tooltip::before {
    content: '';
    position: absolute;
    top: -4px;
    left: 50%;
    transform: translateX(-50%) rotate(45deg);
    width: 8px;
    height: 8px;
    background: #1f2937;
}
.#<?php echo $uid; ?>-messenger-link:hover .#<?php echo $uid; ?>-messenger-tooltip {
    opacity: 1;
}

/* Mobile messenger column */
.#<?php echo $uid; ?>-mobile-messengers {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
}
@media (min-width: 640px) {
    .#<?php echo $uid; ?>-mobile-messengers { display: none; }
}
.#<?php echo $uid; ?>-mobile-messenger-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    transition: opacity .2s;
}
.#<?php echo $uid; ?>-mobile-messenger-link:hover { opacity: .85; color: #fff; }
.#<?php echo $uid; ?>-mobile-messenger-link svg { width: 24px; height: 24px; flex-shrink: 0; }
.#<?php echo $uid; ?>-mobile-messenger-phone    { background: #000; }
.#<?php echo $uid; ?>-mobile-messenger-whatsapp { background: #25D366; }
.#<?php echo $uid; ?>-mobile-messenger-telegram { background: #0088CC; }
.#<?php echo $uid; ?>-mobile-messenger-viber    { background: #7360F2; }

/* Social proof */
.#<?php echo $uid; ?>-social-proof {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    width: 100%;
    gap: 8px;
    margin-top: 16px;
}
.#<?php echo $uid; ?>-avatars-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.#<?php echo $uid; ?>-avatars {
    display: flex;
    align-items: flex-start;
}
.#<?php echo $uid; ?>-avatar {
    position: relative;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    border: 2px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,.2);
    overflow: hidden;
    margin-left: -24px;
}
.#<?php echo $uid; ?>-avatar:first-child { margin-left: 0; }
@media (min-width: 640px) {
    .#<?php echo $uid; ?>-avatar { width: 40px; height: 40px; margin-left: -28px; }
}
@media (min-width: 768px) {
    .#<?php echo $uid; ?>-avatar { width: 44px; height: 44px; }
}
@media (min-width: 1024px) {
    .#<?php echo $uid; ?>-avatar { width: 48px; height: 48px; }
}
.#<?php echo $uid; ?>-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}
.#<?php echo $uid; ?>-people-count {
    font-weight: 600;
    color: #fff;
    font-size: 16px;
    text-shadow: 0 1px 4px rgba(0,0,0,.2);
}
@media (min-width: 640px)  { .#<?php echo $uid; ?>-people-count { font-size: 17px; } }
@media (min-width: 768px)  { .#<?php echo $uid; ?>-people-count { font-size: 18px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-people-count { font-size: 19px; } }
.#<?php echo $uid; ?>-promo-text {
    font-weight: 400;
    line-height: 1.4;
    color: rgba(255,255,255,.95);
    font-size: 12px;
    text-shadow: 0 1px 4px rgba(0,0,0,.2);
}
@media (min-width: 640px) { .#<?php echo $uid; ?>-promo-text { font-size: 13px; } }

/* Right column - widgets */
.#<?php echo $uid; ?>-right {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    order: 2;
    margin-top: 24px;
}
@media (min-width: 1024px) {
    .#<?php echo $uid; ?>-right {
        width: 500px;
        justify-content: flex-end;
        margin-top: 0;
    }
}
@media (min-width: 1280px) {
    .#<?php echo $uid; ?>-right { width: 670px; }
}
.#<?php echo $uid; ?>-widgets {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 24px;
    width: 100%;
    margin-top: 32px;
}
@media (min-width: 768px) { .#<?php echo $uid; ?>-widgets { margin-top: 48px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-widgets { margin-top: 80px; } }

.#<?php echo $uid; ?>-widgets-row {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 14px;
}
@media (min-width: 640px) { .#<?php echo $uid; ?>-widgets-row { gap: 18px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-widgets-row { gap: 24px; } }

/* Widget card */
.#<?php echo $uid; ?>-widget {
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
@media (min-width: 640px)  { .#<?php echo $uid; ?>-widget { width: 125px; height: 125px; gap: 5px; padding: 18px; border-radius: 18px; } }
@media (min-width: 768px)  { .#<?php echo $uid; ?>-widget { width: 135px; height: 135px; padding: 20px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-widget { width: 145px; height: 145px; } }

.#<?php echo $uid; ?>-widget-border {
    position: absolute;
    inset: 0;
    border: 3px solid rgba(255,255,255,.8);
    border-radius: 16px;
    pointer-events: none;
}
@media (min-width: 640px) { .#<?php echo $uid; ?>-widget-border { border-radius: 18px; } }

.#<?php echo $uid; ?>-widget-value {
    font-weight: 700;
    color: #111;
    font-size: 32px;
    text-align: center;
    line-height: 1.1;
    text-shadow: 0 1px 2px rgba(0,0,0,.1);
}
@media (min-width: 640px)  { .#<?php echo $uid; ?>-widget-value { font-size: 36px; } }
@media (min-width: 768px)  { .#<?php echo $uid; ?>-widget-value { font-size: 40px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-widget-value { font-size: 44px; } }

.#<?php echo $uid; ?>-widget-label {
    font-weight: 600;
    color: #555;
    font-size: 10px;
    text-align: center;
    line-height: 1.3;
    text-shadow: 0 1px 2px rgba(0,0,0,.1);
    padding: 0 4px;
}
@media (min-width: 640px)  { .#<?php echo $uid; ?>-widget-label { font-size: 11px; } }
@media (min-width: 768px)  { .#<?php echo $uid; ?>-widget-label { font-size: 12px; } }

/* Price widget specific */
.#<?php echo $uid; ?>-widget-price {
    width: 145px;
}
@media (min-width: 640px)  { .#<?php echo $uid; ?>-widget-price { width: 160px; } }
@media (min-width: 768px)  { .#<?php echo $uid; ?>-widget-price { width: 175px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-widget-price { width: 185px; } }

.#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-value {
    font-size: 28px;
}
@media (min-width: 640px)  { .#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-value { font-size: 32px; } }
@media (min-width: 768px)  { .#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-value { font-size: 36px; } }
@media (min-width: 1024px) { .#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-value { font-size: 40px; } }

.#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-from {
    font-weight: 600;
    color: #555;
    font-size: 9px;
    text-align: center;
    line-height: 1.2;
    text-shadow: 0 1px 2px rgba(0,0,0,.1);
}
@media (min-width: 640px) { .#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-from { font-size: 10px; } }
@media (min-width: 768px) { .#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-from { font-size: 11px; } }

.#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-note {
    font-weight: 600;
    color: #555;
    font-size: 9px;
    text-align: center;
    line-height: 1.3;
    text-shadow: 0 1px 2px rgba(0,0,0,.1);
    white-space: nowrap;
}
@media (min-width: 640px) { .#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-note { font-size: 10px; } }
@media (min-width: 768px) { .#<?php echo $uid; ?>-widget-price .#<?php echo $uid; ?>-widget-note { font-size: 11px; } }
</style>

<section id="<?php echo $uid; ?>" class="acm-hero style-1">
    <!-- Background -->
    <div class="#<?php echo $uid; ?>-bg">
        <img src="<?php echo htmlspecialchars($bgImage); ?>" alt="<?php echo htmlspecialchars($heading); ?>">
        <div class="#<?php echo $uid; ?>-bg-overlay"></div>
    </div>

    <!-- Content -->
    <div class="#<?php echo $uid; ?>-inner">
        <div class="#<?php echo $uid; ?>-flex">
            <div class="#<?php echo $uid; ?>-content">
                <div class="#<?php echo $uid; ?>-row">
                    <!-- Left column -->
                    <div class="#<?php echo $uid; ?>-left">
                        <div style="width:100%;space-y:12px;">
                            <h1 class="#<?php echo $uid; ?>-heading"><?php echo htmlspecialchars($heading); ?></h1>
                            <?php if (!empty($subheading)): ?>
                            <p class="#<?php echo $uid; ?>-subheading"><?php echo htmlspecialchars($subheading); ?></p>
                            <?php endif; ?>

                            <!-- Buttons -->
                            <div class="#<?php echo $uid; ?>-btns">
                                <a href="<?php echo htmlspecialchars($chooseCarUrl); ?>">
                                    <button class="#<?php echo $uid; ?>-choose-btn"><?php echo htmlspecialchars($chooseCarLabel); ?></button>
                                </a>

                                <!-- Call / Messengers -->
                                <div class="#<?php echo $uid; ?>-call-wrap">
                                    <!-- Desktop call button -->
                                    <button class="#<?php echo $uid; ?>-call-btn" id="<?php echo $uid; ?>-call-trigger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                        <?php echo htmlspecialchars($callBtnLabel); ?>
                                    </button>

                                    <!-- Desktop messenger bubbles (hidden, shown on hover) -->
                                    <div class="#<?php echo $uid; ?>-messengers" id="<?php echo $uid; ?>-messengers">
                                        <?php if (!empty($phoneLink)): ?>
                                        <a href="<?php echo htmlspecialchars($phoneLink); ?>" target="_blank" rel="noopener noreferrer" class="#<?php echo $uid; ?>-messenger-link #<?php echo $uid; ?>-messenger-phone">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                            <div class="#<?php echo $uid; ?>-messenger-tooltip">Phone</div>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($whatsappLink)): ?>
                                        <a href="<?php echo htmlspecialchars($whatsappLink); ?>" target="_blank" rel="noopener noreferrer" class="#<?php echo $uid; ?>-messenger-link #<?php echo $uid; ?>-messenger-whatsapp">
                                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path></svg>
                                            <div class="#<?php echo $uid; ?>-messenger-tooltip">WhatsApp</div>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($telegramLink)): ?>
                                        <a href="<?php echo htmlspecialchars($telegramLink); ?>" target="_blank" rel="noopener noreferrer" class="#<?php echo $uid; ?>-messenger-link #<?php echo $uid; ?>-messenger-telegram">
                                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"></path></svg>
                                            <div class="#<?php echo $uid; ?>-messenger-tooltip">Telegram</div>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($viberLink)): ?>
                                        <a href="<?php echo htmlspecialchars($viberLink); ?>" target="_blank" rel="noopener noreferrer" class="#<?php echo $uid; ?>-messenger-link #<?php echo $uid; ?>-messenger-viber">
                                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.398.002C9.473.028 5.331.344 3.014 2.467 1.294 4.177.693 6.698.623 9.82c-.06 3.11-.13 8.95 5.5 10.541v2.42s-.038.97.602 1.17c.79.25 1.24-.499 1.99-1.299l1.4-1.58c3.85.32 6.8-.419 7.14-.529.78-.25 5.181-.811 5.901-6.652.74-6.031-.36-9.831-2.34-11.551l-.01-.002c-.6-.55-3-2.3-8.37-2.32 0 0-.396-.025-1.038-.016zm.067 1.697c.545-.003.88.02.88.02 4.54.01 6.711 1.38 7.221 1.84 1.67 1.429 2.528 4.856 1.9 9.892-.6 4.88-4.17 5.19-4.83 5.4-.28.09-2.88.73-6.152.52 0 0-2.439 2.941-3.199 3.701-.12.13-.26.17-.35.15-.13-.03-.17-.19-.16-.41l.02-4.019c-4.771-1.32-4.491-6.302-4.441-8.902.06-2.6.55-4.732 2-6.172 1.957-1.77 5.475-2.01 7.11-2.02zm.36 2.6a.299.299 0 0 0-.3.299.3.3 0 0 0 .3.3 5.631 5.631 0 0 1 4.03 1.59 5.458 5.458 0 0 1 1.591 3.96.3.3 0 0 0 .3.3.3.3 0 0 0 .299-.3 6.014 6.014 0 0 0-1.769-4.34 6.234 6.234 0 0 0-4.451-1.769zm-3.954.69a.955.955 0 0 0-.615.12h-.012c-.41.24-.788.54-1.148.94-.27.32-.421.639-.461.949a1.24 1.24 0 0 0 .05.541l.02.01a13.722 13.722 0 0 0 1.2 2.6 15.383 15.383 0 0 0 2.32 3.171l.03.04.04.03.03.03.03.03a15.603 15.603 0 0 0 3.2 2.33c1.32.72 2.1 1.06 2.65 1.2v.01c.13.04.24.06.33.06a1.25 1.25 0 0 0 .84-.35 1.72 1.72 0 0 0 .16-.14c.39-.41.719-.849.97-1.289v-.01a.571.571 0 0 0-.12-.73l-2.26-1.699a.63.63 0 0 0-.75.06l-1 .75a.301.301 0 0 1-.32.03c-.319-.129-1.229-.58-2.3-1.648-1.068-1.07-1.518-1.98-1.648-2.299a.299.299 0 0 1 .03-.32l.747-.999a.632.632 0 0 0 .061-.751L8.323 5.612a.634.634 0 0 0-.533-.334zm3.613 1.249a.297.297 0 0 0-.299.3.3.3 0 0 0 .3.299 3.734 3.734 0 0 1 2.65 1.05 3.553 3.553 0 0 1 1.06 2.58.3.3 0 0 0 .3.3.3.3 0 0 0 .299-.3 4.105 4.105 0 0 0-1.219-2.98 4.333 4.333 0 0 0-3.061-1.219zm.33 1.989a.299.299 0 0 0-.3.3.3.3 0 0 0 .3.3 1.765 1.765 0 0 1 1.25.53c.33.33.5.73.51 1.2a.3.3 0 0 0 .3.299.3.3 0 0 0 .3-.3 2.264 2.264 0 0 0-.661-1.579 2.326 2.326 0 0 0-1.649-.69z"></path></svg>
                                            <div class="#<?php echo $uid; ?>-messenger-tooltip">Viber</div>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile messenger buttons -->
                            <div class="#<?php echo $uid; ?>-mobile-messengers">
                                <?php if (!empty($phoneLink)): ?>
                                <a href="<?php echo htmlspecialchars($phoneLink); ?>" class="#<?php echo $uid; ?>-mobile-messenger-link #<?php echo $uid; ?>-mobile-messenger-phone">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    Phone
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($whatsappLink)): ?>
                                <a href="<?php echo htmlspecialchars($whatsappLink); ?>" class="#<?php echo $uid; ?>-mobile-messenger-link #<?php echo $uid; ?>-mobile-messenger-whatsapp">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path></svg>
                                    WhatsApp
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($telegramLink)): ?>
                                <a href="<?php echo htmlspecialchars($telegramLink); ?>" class="#<?php echo $uid; ?>-mobile-messenger-link #<?php echo $uid; ?>-mobile-messenger-telegram">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"></path></svg>
                                    Telegram
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($viberLink)): ?>
                                <a href="<?php echo htmlspecialchars($viberLink); ?>" class="#<?php echo $uid; ?>-mobile-messenger-link #<?php echo $uid; ?>-mobile-messenger-viber">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.398.002C9.473.028 5.331.344 3.014 2.467 1.294 4.177.693 6.698.623 9.82c-.06 3.11-.13 8.95 5.5 10.541v2.42s-.038.97.602 1.17c.79.25 1.24-.499 1.99-1.299l1.4-1.58c3.85.32 6.8-.419 7.14-.529.78-.25 5.181-.811 5.901-6.652.74-6.031-.36-9.831-2.34-11.551l-.01-.002c-.6-.55-3-2.3-8.37-2.32 0 0-.396-.025-1.038-.016zm.067 1.697c.545-.003.88.02.88.02 4.54.01 6.711 1.38 7.221 1.84 1.67 1.429 2.528 4.856 1.9 9.892-.6 4.88-4.17 5.19-4.83 5.4-.28.09-2.88.73-6.152.52 0 0-2.439 2.941-3.199 3.701-.12.13-.26.17-.35.15-.13-.03-.17-.19-.16-.41l.02-4.019c-4.771-1.32-4.491-6.302-4.441-8.902.06-2.6.55-4.732 2-6.172 1.957-1.77 5.475-2.01 7.11-2.02zm.36 2.6a.299.299 0 0 0-.3.299.3.3 0 0 0 .3.3 5.631 5.631 0 0 1 4.03 1.59 5.458 5.458 0 0 1 1.591 3.96.3.3 0 0 0 .3.3.3.3 0 0 0 .299-.3 6.014 6.014 0 0 0-1.769-4.34 6.234 6.234 0 0 0-4.451-1.769zm-3.954.69a.955.955 0 0 0-.615.12h-.012c-.41.24-.788.54-1.148.94-.27.32-.421.639-.461.949a1.24 1.24 0 0 0 .05.541l.02.01a13.722 13.722 0 0 0 1.2 2.6 15.383 15.383 0 0 0 2.32 3.171l.03.04.04.03.03.03.03.03a15.603 15.603 0 0 0 3.2 2.33c1.32.72 2.1 1.06 2.65 1.2v.01c.13.04.24.06.33.06a1.25 1.25 0 0 0 .84-.35 1.72 1.72 0 0 0 .16-.14c.39-.41.719-.849.97-1.289v-.01a.571.571 0 0 0-.12-.73l-2.26-1.699a.63.63 0 0 0-.75.06l-1 .75a.301.301 0 0 1-.32.03c-.319-.129-1.229-.58-2.3-1.648-1.068-1.07-1.518-1.98-1.648-2.299a.299.299 0 0 1 .03-.32l.747-.999a.632.632 0 0 0 .061-.751L8.323 5.612a.634.634 0 0 0-.533-.334zm3.613 1.249a.297.297 0 0 0-.299.3.3.3 0 0 0 .3.299 3.734 3.734 0 0 1 2.65 1.05 3.553 3.553 0 0 1 1.06 2.58.3.3 0 0 0 .3.3.3.3 0 0 0 .299-.3 4.105 4.105 0 0 0-1.219-2.98 4.333 4.333 0 0 0-3.061-1.219zm.33 1.989a.299.299 0 0 0-.3.3.3.3 0 0 0 .3.3 1.765 1.765 0 0 1 1.25.53c.33.33.5.73.51 1.2a.3.3 0 0 0 .3.299.3.3 0 0 0 .3-.3 2.264 2.264 0 0 0-.661-1.579 2.326 2.326 0 0 0-1.649-.69z"></path></svg>
                                    Viber
                                </a>
                                <?php endif; ?>
                            </div>

                            <!-- Social proof -->
                            <?php if (!empty($clientPhotos)): ?>
                            <div class="#<?php echo $uid; ?>-social-proof">
                                <div class="#<?php echo $uid; ?>-avatars-row">
                                    <div class="#<?php echo $uid; ?>-avatars">
                                        <?php foreach ($clientPhotos as $photo): ?>
                                        <div class="#<?php echo $uid; ?>-avatar">
                                            <img src="<?php echo htmlspecialchars($photo); ?>" alt="Client">
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (!empty($peopleCount)): ?>
                                    <p class="#<?php echo $uid; ?>-people-count"><?php echo htmlspecialchars($peopleCount); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($promoText)): ?>
                                <p class="#<?php echo $uid; ?>-promo-text"><?php echo htmlspecialchars($promoText); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right column - Widgets -->
                    <div class="#<?php echo $uid; ?>-right">
                        <div class="#<?php echo $uid; ?>-widgets">
                            <div class="#<?php echo $uid; ?>-widgets-row">
                                <!-- Cars available widget -->
                                <div class="#<?php echo $uid; ?>-widget">
                                    <p class="#<?php echo $uid; ?>-widget-value"><?php echo $availableCars; ?></p>
                                    <p class="#<?php echo $uid; ?>-widget-label"><?php echo htmlspecialchars($carsAvailLabel); ?></p>
                                    <div class="#<?php echo $uid; ?>-widget-border"></div>
                                </div>

                                <!-- Price widget -->
                                <?php if ($minPrice > 0): ?>
                                <div class="#<?php echo $uid; ?>-widget #<?php echo $uid; ?>-widget-price">
                                    <p class="#<?php echo $uid; ?>-widget-from">from</p>
                                    <p class="#<?php echo $uid; ?>-widget-value"><?php echo $currencysymb . $minPrice; ?>€/day</p>
                                    <p class="#<?php echo $uid; ?>-widget-note"><?php echo htmlspecialchars($priceNote); ?></p>
                                    <div class="#<?php echo $uid; ?>-widget-border"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    var wrap = document.getElementById('<?php echo $uid; ?>-call-wrap') || document.querySelector('#<?php echo $uid; ?> .#<?php echo $uid; ?>-call-wrap');
    if (!wrap) return;
    var btn = document.getElementById('<?php echo $uid; ?>-call-trigger');
    var msgs = document.getElementById('<?php echo $uid; ?>-messengers');
    var links = msgs ? msgs.querySelectorAll('.#<?php echo $uid; ?>-messenger-link') : [];
    var timeout;

    function showMessengers() {
        clearTimeout(timeout);
        if (btn) btn.style.opacity = '0';
        if (msgs) msgs.style.opacity = '1';
        links.forEach(function(l, i) {
            setTimeout(function() {
                l.style.opacity = '1';
                l.style.transform = 'translateX(0) scale(1)';
            }, i * 60);
        });
    }

    function hideMessengers() {
        timeout = setTimeout(function() {
            links.forEach(function(l) {
                l.style.opacity = '0';
                l.style.transform = 'translateX(-10px) scale(0.8)';
            });
            if (btn) btn.style.opacity = '1';
            if (msgs) msgs.style.opacity = '0';
        }, 200);
    }

    if (wrap) {
        wrap.addEventListener('mouseenter', showMessengers);
        wrap.addEventListener('mouseleave', hideMessengers);
    }
})();
</script>