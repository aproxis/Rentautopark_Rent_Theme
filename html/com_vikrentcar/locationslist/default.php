<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/locationslist/default.php
 * AutoRent Figma Design System — Dark Map Edition
 */

defined('_JEXEC') OR die('Restricted Area');
use Joomla\CMS\Language\Text;
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root() . 'templates/rent/css/locationslist.css');

$locations = $this->locations;
$alllocations = $this->alllocations;
$vrc_tn = $this->vrc_tn;

$nowtf = VikRentCar::getTimeFormat();
?>

<!-- Hero Header Section -->
<section class="relative py-20 bg-gradient-to-br from-[#0a0a0a] via-[#1a1a1a] to-[#0a0a0a] text-white overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0px); background-size: 40px 40px;"></div>
    </div>
    <div class="relative container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6"><?php echo Text::_('VRC_LOCATIONS') ?: 'Locațiile Noastre'; ?></h1>
            <p class="text-xl text-gray-300"><?php echo Text::_('VRC_LOCATIONS_DESCR') ?: 'Găsește cel mai apropiat punct de ridicare sau returnare'; ?></p>
        </div>
    </div>
</section>

<div class="ar-page-wrap">
    <div class="ar-page-inner">
        <div class="container mx-auto px-10 py-12">

            <?php if(count($locations) > 0): ?>
                <?php
                $lats = array();
                $lngs = array();
                foreach($locations as $l) {
                    $lats[] = $l['lat'];
                    $lngs[] = $l['lng'];
                }

                // Leaflet OpenStreetMap
                $document->addStyleSheet('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
                $document->addScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
                ?>

                <!-- Two Column Layout -->
                <div class="ar-locations-two-col">
                    <!-- Left Column: Locations Cards -->
                    <div class="ar-locations-col-left">
                        <div class="ar-locations-grid">
                            <?php foreach($alllocations as $loc): ?>
                                <?php
                                if(strlen($loc['opentime']) > 0) {
                                    $parts = explode("-", $loc['opentime']);
                                    $opent=VikRentCar::getHoursMinutes($parts[0]);
                                    $closet=VikRentCar::getHoursMinutes($parts[1]);
                                    $tsopen = mktime($opent[0], $opent[1], 0, 1, 1, 2012);
                                    $tsclose = mktime($closet[0], $closet[1], 0, 1, 1, 2012);
                                    $stropeningtime = date($nowtf, $tsopen).' - '.date($nowtf, $tsclose);
                                } else {
                                    $stropeningtime = "";
                                }
                                ?>

                                <div class="ar-location-card">
                                    <div class="ar-location-card-header">
                                        <div class="ar-location-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                        </div>
                                        <h3 class="ar-location-name"><?php echo $loc['name']; ?></h3>
                                    </div>

                                    <div class="ar-location-info">
                                        <?php if (!empty($loc['address'])): ?>
                                            <div class="ar-location-row">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                                </svg>
                                                <span><?php echo $loc['address']; ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if(strlen($stropeningtime) > 0): ?>
                                            <div class="ar-location-row">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                <span>
                                                    <span class="ar-location-label"><?php echo JText::_('VRCLOCLISTLOCOPENTIME'); ?>:</span>
                                                    <span class="ar-location-time"><?php echo $stropeningtime; ?></span>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($loc['descr'])): ?>
                                            <div class="ar-location-description">
                                                <?php echo $loc['descr']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="ar-location-footer">
                                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $loc['lat']; ?>,<?php echo $loc['lng']; ?>" target="_blank" rel="noopener noreferrer" class="ar-location-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 18l6-6-6-6"></path>
                                            </svg>
                                            <?php echo Text::_('VRC_GET_DIRECTIONS') ?: 'Obține Direcții'; ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Right Column: Map -->
                    <div class="ar-locations-col-right">
                        <div class="ar-map-container sticky top-20">
                            <!-- Overlay badge top-left of map -->
                            <div class="ar-map-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#FE5001" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span><?php echo count($locations); ?></span> <?php echo Text::_('VRC_LOCATIONS_COUNT') ?: 'Locații'; ?>
                            </div>
                            <div id="vrcmapcanvas" class="ar-map-canvas"></div>
                        </div>
                    </div>
                </div>

                <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    var map = L.map('vrcmapcanvas', {
                        zoomControl: true,
                        attributionControl: true
                    });

                    // Bright clean tile layer — CartoDB Voyager
                    var lightTile = L.tileLayer(
                        'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                        {
                            maxZoom: 19,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>',
                            subdomains: 'abcd',
                            r: window.devicePixelRatio >= 2 ? '@2x' : ''
                        }
                    ).addTo(map);

                    // Custom brand marker — orange pin, white dot, no filter needed
                    function makeIcon(active) {
                        var color  = active ? '#ff7a33' : '#FE5001';
                        var size   = active ? 44 : 36;
                        var anchor = active ? 22 : 18;
                        return L.divIcon({
                            className: '',   /* no class — inline svg only, avoids CSS filter */
                            html: '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 24 24">'
                                + '<filter id="ds"><feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="rgba(254,80,1,0.5)"/></filter>'
                                + '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" fill="' + color + '" filter="url(#ds)" stroke="rgba(255,255,255,0.2)" stroke-width="0.5"/>'
                                + '<circle cx="12" cy="10" r="3.5" fill="#fff"/>'
                                + '</svg>',
                            iconSize:   [size,   size],
                            iconAnchor: [anchor, size],
                            popupAnchor:[0, -size]
                        });
                    }

                    var bounds = [];
                    var markers = [];

                    <?php foreach($locations as $l): ?>
                    (function() {
                        var lat = <?php echo $l['lat']; ?>;
                        var lng = <?php echo $l['lng']; ?>;
                        var marker = L.marker([lat, lng], { icon: makeIcon(false) }).addTo(map);
                        bounds.push([lat, lng]);

                        <?php if(strlen(trim(strip_tags($l['descr']))) > 0): ?>
                        marker.bindPopup(
                            '<div class="ar-map-popup">'
                            + '<h4><?php echo addslashes($l['name']); ?></h4>'
                            + '<p><?php echo addslashes(preg_replace('/\s\s+/', ' ', strip_tags($l['descr']))); ?></p>'
                            + '</div>',
                            { maxWidth: 220 }
                        );
                        <?php else: ?>
                        marker.bindPopup(
                            '<div class="ar-map-popup"><h4><?php echo addslashes($l['name']); ?></h4></div>',
                            { maxWidth: 220 }
                        );
                        <?php endif; ?>

                        marker.on('mouseover', function() { this.setIcon(makeIcon(true)); });
                        marker.on('mouseout',  function() { this.setIcon(makeIcon(false)); });

                        markers.push(marker);
                    })();
                    <?php endforeach; ?>

                    if (bounds.length > 0) {
                        map.fitBounds(bounds, { padding: [50, 50] });
                        // Don't zoom in too close for a single pin
                        if (bounds.length === 1) { map.setZoom(14); }
                    }
                });
                </script>

            <?php endif; ?>

            <?php VikRentCar::printTrackingCode(); ?>

        </div>
    </div>
</div>