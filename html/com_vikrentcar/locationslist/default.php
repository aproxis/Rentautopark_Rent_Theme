<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/locationslist/default.php
 * AutoRent Figma Design System
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
        <div class="container mx-auto px-4 py-12">

            <?php if(count($locations) > 0): ?>
                <?php
                $lats = array();
                $lngs = array();
                foreach($locations as $l) {
                    $lats[] = $l['lat'];
                    $lngs[] = $l['lng'];
                }

                if(VikRentCar::loadJquery()) {
                    JHtml::_('jquery.framework', true, true);
                }
                $gmap_key = VikRentCar::getGoogleMapsKey();
                $document->addScript((strpos(JURI::root(), 'https') !== false ? 'https' : 'http').'://maps.google.com/maps/api/js'.(!empty($gmap_key) ? '?key='.$gmap_key : ''));
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

                    <!-- Right Column: Google Map -->
                    <div class="ar-locations-col-right">
                        <div class="ar-map-container sticky top-20 rounded-2xl overflow-hidden shadow-xl">
                            <div id="vrcmapcanvas" class="ar-map-canvas"></div>
                        </div>
                    </div>
                </div>

                <script type="text/javascript">
                jQuery.noConflict();
                jQuery(document).ready(function(){
                    var map = new google.maps.Map(document.getElementById("vrcmapcanvas"), {
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        styles: [
                            { featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] }
                        ]
                    });

                    <?php foreach($locations as $l): ?>
                        var marker<?php echo $l['id']; ?> = new google.maps.Marker({
                            position: new google.maps.LatLng(<?php echo $l['lat']; ?>, <?php echo $l['lng']; ?>),
                            map: map,
                            title: '<?php echo addslashes($l['name']); ?>'
                        });

                        <?php if(strlen(trim(strip_tags($l['descr']))) > 0): ?>
                            var tooltip<?php echo $l['id']; ?> = '<div class="vrcgmapinfow"><h3><?php echo addslashes($l['name']); ?></h3><div class="vrcgmapinfowdescr"><?php echo addslashes(preg_replace('/\s\s+/', ' ', $l['descr'])); ?></div></div>';
                            var infowindow<?php echo $l['id']; ?> = new google.maps.InfoWindow({
                                content: tooltip<?php echo $l['id']; ?>
                            });
                            google.maps.event.addListener(marker<?php echo $l['id']; ?>, 'click', function() {
                                infowindow<?php echo $l['id']; ?>.open(map, marker<?php echo $l['id']; ?>);
                            });
                        <?php endif; ?>
                    <?php endforeach; ?>

                    var lat_min = <?php echo min($lats); ?>;
                    var lat_max = <?php echo max($lats); ?>;
                    var lng_min = <?php echo min($lngs); ?>;
                    var lng_max = <?php echo max($lngs); ?>;

                    map.setCenter(new google.maps.LatLng( ((lat_max + lat_min) / 2.0), ((lng_max + lng_min) / 2.0) ));
                    map.fitBounds(new google.maps.LatLngBounds(new google.maps.LatLng(lat_min, lng_min), new google.maps.LatLng(lat_max, lng_max)));
                });
                </script>


            <?php endif; ?>

            <?php VikRentCar::printTrackingCode(); ?>

        </div>
    </div>
</div>