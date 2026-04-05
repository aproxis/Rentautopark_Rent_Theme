<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Contact Style 1: Contact Form & Leaflet Map
 * ------------------------------------------------------------------------
 * Map: OpenStreetMap light tiles (identical to locationslist/default.php).
 * Popup shows address only. No bottom-left badge.
 *
 * Extra Fields used:
 *   map-url    (Google Maps embed URL — lat/lng extracted from it, or fallback)
 *   contact-id (Joomla contact ID)
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$uid       = 'ar-contact-' . $module->id;
$mapUrl    = $helper->get('map-url');
$contactId = (int) $helper->get('contact-id');

Factory::getApplication()->getLanguage()->load(
    'com_contact',
    JPATH_SITE,
    Factory::getApplication()->getLanguage()->getTag(),
    true
);

// Extract lat/lng from Google Maps URLs
$lat = 47.010453;
$lng = 28.7845654;

if (!empty($mapUrl)) {
    // Pattern 1: Standard embed URL - !2d = LONGITUDE, !3d = LATITUDE (correct order)
    if (preg_match('/!2d(-?[\d.]+)/', $mapUrl, $mLng)) { $lng = (float) $mLng[1]; }
    if (preg_match('/!3d(-?[\d.]+)/', $mapUrl, $mLat)) { $lat = (float) $mLat[1]; }
    
    // Pattern 2: Direct coordinates format /dir/?api=1&destination=LAT,LNG
    if (preg_match('/destination=([\d\.-]+),([\d\.-]+)/', $mapUrl, $matches)) {
        $lat = (float) $matches[1];
        $lng = (float) $matches[2];
    }
}

if ($contactId > 0) {
    $db = Factory::getDbo();
    $db->setQuery('SELECT * FROM #__contact_details WHERE id = ' . $contactId . ' AND published = 1');
    $contact = $db->loadObject();

    if (!$contact) {
        if (Factory::getApplication()->isClient('administrator')) {
            echo '<div class="alert alert-warning">Contact ID ' . $contactId . ' not found or unpublished.</div>';
        }
        return;
    }
} else {
    if (Factory::getApplication()->isClient('administrator')) {
        echo '<div class="alert alert-warning">No contact ID set in module parameters.</div>';
    }
    return;
}

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

$document = Factory::getDocument();
$document->addStyleSheet('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
$document->addScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');

$formAction  = Route::_('index.php');
$mapId       = 'ar-contact-map-' . $module->id;
// Joomla stores address in suburb field by default
$contactAddr = htmlspecialchars(trim($contact->suburb ?? $contact->address ?? ''), ENT_QUOTES, 'UTF-8');

// Fallback default
if (empty($contactAddr)) {
    $contactAddr = 'Chișinău, str. Nicolae Testemițanu 29/5';
}
?>

<style>
/* ═══ AutoRent Contact (contact/style-1) ════════════════════════════════ */
#<?php echo $uid; ?> {
    padding: 48px 20px;
    background: #f9fafb;
}
@media (min-width: 640px)  { #<?php echo $uid; ?> { padding: 64px 30px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> { padding: 80px 40px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> { padding: 80px 50px; } }

.<?php echo $uid; ?>-inner {
    max-width: 1440px;
    margin: 0 auto;
}

.<?php echo $uid; ?>-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
}
@media (min-width: 768px)  { .<?php echo $uid; ?>-grid { gap: 48px; } }
@media (min-width: 1024px) { .<?php echo $uid; ?>-grid { grid-template-columns: 1fr 1fr; gap: 48px; } }

/* ── Form card ── */
.<?php echo $uid; ?>-form-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
}
@media (min-width: 768px) { .<?php echo $uid; ?>-form-card { padding: 32px; } }

.<?php echo $uid; ?>-form-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0a0a0a;
    margin: 0 0 24px;
}
@media (min-width: 768px) { .<?php echo $uid; ?>-form-title { font-size: 1.875rem; } }

.<?php echo $uid; ?>-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    margin-bottom: 24px;
}
@media (min-width: 768px) { .<?php echo $uid; ?>-row { grid-template-columns: 1fr 1fr; } }

.<?php echo $uid; ?>-form-group { margin-bottom: 24px; }
.<?php echo $uid; ?>-form-group.required .<?php echo $uid; ?>-label::after {
    content: ' *';
    color: #ef4444;
}

.<?php echo $uid; ?>-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 8px;
}

.<?php echo $uid; ?>-input,
.<?php echo $uid; ?>-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    color: #0a0a0a;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
    box-sizing: border-box;
}
.<?php echo $uid; ?>-input:focus,
.<?php echo $uid; ?>-textarea:focus {
    outline: none;
    border-color: transparent;
    box-shadow: 0 0 0 2px #FE5001;
}
.<?php echo $uid; ?>-textarea {
    resize: none;
    min-height: 112px;
}

.<?php echo $uid; ?>-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 14px 16px;
    background: #FE5001;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s;
}
.<?php echo $uid; ?>-submit:hover { background: #E54801; }

/* ── Map card — identical styling to locationslist .ar-map-container ── */
.<?php echo $uid; ?>-map-card {
    position: relative;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    height: 100%;
    min-height: 420px;
    box-shadow:
        0 0 0 1px rgba(254, 80, 1, 0.35),
        0 0 32px rgba(254, 80, 1, 0.12),
        0 10px 40px rgba(0, 0, 0, 0.12);
}

/* Top orange accent bar — same as locationslist */
.<?php echo $uid; ?>-map-card::before {
    content: '';
    position: absolute;
    top: -1px; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent 0%, #FE5001 40%, #ff7a33 60%, transparent 100%);
    z-index: 1000;
    pointer-events: none;
}

/* Pulsing dot — top right, same as locationslist */
.<?php echo $uid; ?>-map-card::after {
    content: '';
    position: absolute;
    top: 12px; right: 12px;
    width: 8px; height: 8px;
    background: #FE5001;
    border-radius: 50%;
    z-index: 1000;
    pointer-events: none;
    box-shadow: 0 0 8px 2px rgba(254, 80, 1, 0.6);
    animation: <?php echo $uid; ?>-pulse 2s ease-in-out infinite;
}

@keyframes <?php echo $uid; ?>-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.5; transform: scale(1.5); }
}

/* Canvas — apply overflow clip only here, not on parent card */
#<?php echo $mapId; ?> {
    width: 100%;
    height: 100%;
    min-height: 420px;
    border-radius: 16px;
    overflow: hidden;
}

#<?php echo $mapId; ?> .leaflet-control-attribution {
    display: none !important;
}

/* Leaflet controls — light, same as locationslist */
.<?php echo $uid; ?>-map-card .leaflet-popup-content-wrapper {
    background: #ffffff !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 10px !important;
    box-shadow: 0 8px 32px rgba(0,0,0,.15), 0 0 16px rgba(254,80,1,.10) !important;
    color: #111827 !important;
}

.<?php echo $uid; ?>-map-card .leaflet-popup-tip {
    background: #ffffff !important;
    box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
}

.<?php echo $uid; ?>-map-card .ar-map-popup p {
    margin: 0;
    font-size: 12px;
    color: #6b7280;
    line-height: 1.4;
}
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<section id="<?php echo $uid; ?>" class="acm-contact style-1">
    <div class="<?php echo $uid; ?>-inner">
        <div class="<?php echo $uid; ?>-grid">

            <!-- Contact Form -->
            <div class="<?php echo $uid; ?>-form-card">
                <h2 class="<?php echo $uid; ?>-form-title"><?php echo Text::_('COM_CONTACT_CONTACT_DEFAULT_LABEL'); ?></h2>

                <form id="contact-form-<?php echo $module->id; ?>"
                      action="<?php echo $formAction; ?>"
                      method="post"
                      class="form-validate form-horizontal"
                      novalidate>
                    <fieldset>

                        <div class="<?php echo $uid; ?>-row">
                            <div class="<?php echo $uid; ?>-form-group required">
                                <label class="<?php echo $uid; ?>-label" for="jform_contact_name_<?php echo $module->id; ?>">
                                    <?php echo Text::_('COM_CONTACT_CONTACT_EMAIL_NAME_LABEL'); ?>
                                </label>
                                <input type="text"
                                       id="jform_contact_name_<?php echo $module->id; ?>"
                                       name="jform[contact_name]"
                                       class="<?php echo $uid; ?>-input required"
                                       autocomplete="name"
                                       required>
                            </div>
                            <div class="<?php echo $uid; ?>-form-group required">
                                <label class="<?php echo $uid; ?>-label" for="jform_contact_email_<?php echo $module->id; ?>">
                                    <?php echo Text::_('COM_CONTACT_EMAIL_LABEL'); ?>
                                </label>
                                <input type="email"
                                       id="jform_contact_email_<?php echo $module->id; ?>"
                                       name="jform[contact_email]"
                                       class="<?php echo $uid; ?>-input required"
                                       autocomplete="email"
                                       required>
                            </div>
                        </div>

                        <div class="<?php echo $uid; ?>-form-group required">
                            <label class="<?php echo $uid; ?>-label" for="jform_contact_message_<?php echo $module->id; ?>">
                                <?php echo Text::_('COM_CONTACT_CONTACT_ENTER_MESSAGE_LABEL'); ?>
                            </label>
                            <textarea id="jform_contact_message_<?php echo $module->id; ?>"
                                      name="jform[contact_message]"
                                      class="<?php echo $uid; ?>-textarea required"
                                      rows="4"
                                      required></textarea>
                        </div>

                        <div class="<?php echo $uid; ?>-form-group">
                            <button type="submit" class="<?php echo $uid; ?>-submit">
                                <?php echo Text::_('COM_CONTACT_CONTACT_SEND'); ?>
                            </button>
                        </div>

                        <input type="hidden" name="jform[contact_subject]" value="General Inquiry" />
                        <input type="hidden" name="option"  value="com_contact" />
                        <input type="hidden" name="task"    value="contact.submit" />
                        <input type="hidden" name="return"  value="<?php echo base64_encode(Uri::getInstance()->toString()); ?>" />
                        <input type="hidden" name="id"      value="<?php echo $contactId; ?>" />
                        <?php echo HTMLHelper::_('form.token'); ?>

                    </fieldset>
                </form>
            </div>

            <!-- Leaflet Map -->
            <div class="<?php echo $uid; ?>-map-card">
                <div id="<?php echo $mapId; ?>"></div>
            </div>

        </div>
    </div>
</section>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function initContactMap() {
    if (typeof L === 'undefined') {
        setTimeout(initContactMap, 200);
        return;
    }

    var map = L.map('<?php echo $mapId; ?>', {
        zoomControl: true,
        attributionControl: false,
        scrollWheelZoom: false
    });

    // Bright clean tile layer — CartoDB Voyager (identical to locations page)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        attribution: '',
        subdomains: 'abcd',
        r: window.devicePixelRatio >= 2 ? '@2x' : ''
    }).addTo(map);

    // Branded marker — same makeIcon as locationslist
    function makeIcon(active) {
        var color    = active ? '#ff7a33' : '#FE5001';
        var size     = active ? 44 : 36;
        var anchor   = active ? 22 : 18;
        var filterId = 'ds-<?php echo $module->id; ?>';
        return L.divIcon({
            className: '',
            html: '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 24 24">'
                + '<defs><filter id="' + filterId + '"><feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="rgba(254,80,1,0.5)"/></filter></defs>'
                + '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" fill="' + color + '" filter="url(#' + filterId + ')" stroke="rgba(255,255,255,0.3)" stroke-width="0.5"/>'
                + '<circle cx="12" cy="10" r="3.5" fill="#fff"/>'
                + '</svg>',
            iconSize:    [size, size],
            iconAnchor:  [anchor, size],
            popupAnchor: [0, -(size + 6)]
        });
    }

    map.setView([<?php echo $lat; ?>, <?php echo $lng; ?>], 15);

    var marker = L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>], { icon: makeIcon(false) }).addTo(map);

<?php if (!empty($contactAddr)): ?>
    
    // Popup shows address only — no contact name
    marker.bindPopup(
        '<div class="ar-map-popup" style="padding: 8px;"><p><a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($contactAddr); ?>&destination_place_id=ChIJfQ8N56HhJUcRbXqTfKx3rMU" target="_blank" style="color: #FE5001; text-decoration: none; font-weight: 500;"><?php echo addslashes($contactAddr); ?></a></p></div>',
        { 
            maxWidth: 300, 
            autoPan: true, 
            closeButton: false, 
            closeOnClick: false
        }
    );
    
    // Open popup immediately ALWAYS
    setTimeout(function() {
        marker.openPopup();
        
        // Force z-index
        document.querySelectorAll('.leaflet-popup').forEach(function(el) {
            el.style.zIndex = '999999';
        });
    }, 1000);
    
    marker.on('mouseover', function () { 
        this.setIcon(makeIcon(true)); 
        this.openPopup();
    });
    marker.on('mouseout', function () { 
        this.setIcon(makeIcon(false));
    });
    marker.on('click', function () {
        this.openPopup();
    });
    
    // Keep popup always open
    marker.on('popupclose', function() {
        setTimeout(function() { marker.openPopup(); }, 10);
    });
    
    <?php else: ?>
    marker.on('mouseover', function () { this.setIcon(makeIcon(true)); });
    marker.on('mouseout',  function () { this.setIcon(makeIcon(false)); });
    <?php endif; ?>
});
</script>
