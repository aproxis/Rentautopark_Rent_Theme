<?php
/**
 * mod_vikrentcar_search - AutoRent Hero Layout
 * Alternative layout: "hero" — full hero section + search form below.
 * The T3 template wrapper applies bg_image and intro_text outside this file.
 * Place this file in: templates/rent/html/mod_vikrentcar_search/hero.php
 * Then select "AutoRent Hero" in Module Manager → Advanced → Alternative Layout.
 */
defined('_JEXEC') or die('Restricted Area');
use Joomla\CMS\Language\Text;

$dbo = JFactory::getDbo();
$session = JFactory::getSession();
$input = JFactory::getApplication()->input;
$vrc_tn = ModVikrentcarSearchHelper::getTranslator();
$restrictions = ModVikrentcarSearchHelper::loadRestrictions();
$def_min_los = ModVikrentcarSearchHelper::setDropDatePlus();

$randid = isset($module) && is_object($module) && property_exists($module, 'id') ? $module->id : rand(1, 999);

$svrcplace = $session->get('vrcplace', '');
$indvrcplace = 0;
$svrcreturnplace = $session->get('vrcreturnplace', '');
$indvrcreturnplace = 0;

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'modules/mod_vikrentcar_search/mod_vikrentcar_search.css');
if (intval($params->get('loadjqueryvrc')) == 1) {
	JHtml::_('jquery.framework', true);
}

// ── Language-aware hero copy ──────────────────────────────────────────────────
$langTag   = JFactory::getLanguage()->getTag();
$heroData  = array(
	'ro-RO' => array(
		'headline' => 'Închiriează Mașina<br><span class="ar-accent">Perfectă</span> în Chișinău',
		'subtitle' => 'Flotă modernă &middot; Prețuri transparente &middot; Asistență 24/7',
		'cta1'     => 'Rezervă Acum',
		'cta2'     => 'Sună: +373 68 001 155',
		'badge1'   => '21 mașini disponibile',
		'badge2'   => 'de la 28€/zi',
		'clients'  => '1000+ clienți mulțumiți',
	),
	'ru-RU' => array(
		'headline' => 'Арендуйте Лучший<br><span class="ar-accent">Автомобиль</span> в Кишиневе',
		'subtitle' => 'Современный парк &middot; Прозрачные цены &middot; Поддержка 24/7',
		'cta1'     => 'Забронировать',
		'cta2'     => 'Позвонить: +373 68 001 155',
		'badge1'   => '21 автомобиль',
		'badge2'   => 'от 28€/день',
		'clients'  => '1000+ довольных клиентов',
	),
	'en-GB' => array(
		'headline' => 'Rent the Perfect<br><span class="ar-accent">Car</span> in Chișinău',
		'subtitle' => 'Modern fleet &middot; Transparent prices &middot; 24/7 support',
		'cta1'     => 'Book Now',
		'cta2'     => 'Call: +373 68 001 155',
		'badge1'   => '21 cars available',
		'badge2'   => 'from 28€/day',
		'clients'  => '1000+ satisfied clients',
	),
);
$hero = isset($heroData[$langTag]) ? $heroData[$langTag] : $heroData['ro-RO'];

// ── One-time CSS injection ────────────────────────────────────────────────────
static $arHeroStylesLoaded = false;
if (!$arHeroStylesLoaded) {
	$arHeroStylesLoaded = true;
	$document->addStyleDeclaration('
/* ═══ AutoRent Hero Layout ═══════════════════════════════════════════════ */
.ar-hero-layout { position: relative; }

/* Hero text block */
.ar-hero-section { padding: 60px 0 40px; text-align: center; }
.ar-hero-section .ar-hero-inner { max-width: 820px; margin: 0 auto; padding: 0 20px; }
.ar-hero-title {
	font-size: 3rem; font-weight: 800; color: #fff; line-height: 1.15;
	margin-bottom: 18px; text-shadow: 0 2px 12px rgba(0,0,0,.45);
}
.ar-hero-title .ar-accent { color: #FE5001; }
.ar-hero-subtitle {
	font-size: 1.1rem; color: rgba(255,255,255,.85); margin-bottom: 32px;
	letter-spacing: .03em;
}

/* CTA buttons */
.ar-hero-ctas { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; margin-bottom: 36px; }
.ar-btn {
	display: inline-block; padding: 14px 32px; border-radius: 8px;
	font-size: 1rem; font-weight: 700; text-decoration: none;
	transition: transform .2s, box-shadow .2s;
}
.ar-btn:hover { transform: translateY(-2px); text-decoration: none; }
.ar-btn-primary {
	background: #FE5001; color: #fff !important;
	box-shadow: 0 4px 20px rgba(254,80,1,.4);
}
.ar-btn-primary:hover { background: #e04600; box-shadow: 0 6px 24px rgba(254,80,1,.5); color: #fff !important; }
.ar-btn-outline {
	background: rgba(255,255,255,.12); color: #fff !important;
	border: 2px solid rgba(255,255,255,.6); backdrop-filter: blur(6px);
}
.ar-btn-outline:hover { background: rgba(255,255,255,.22); color: #fff !important; }

/* Stat badges */
.ar-hero-badges { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; margin-bottom: 28px; }
.ar-badge {
	display: flex; align-items: center; gap: 10px;
	background: rgba(255,255,255,.14); backdrop-filter: blur(10px);
	border: 1px solid rgba(255,255,255,.25); border-radius: 12px;
	padding: 12px 22px; color: #fff; font-weight: 600; font-size: .95rem;
}
.ar-badge i { color: #FE5001; font-size: 1.2rem; }

/* Social proof */
.ar-hero-clients { display: flex; align-items: center; justify-content: center; gap: 10px; color: rgba(255,255,255,.8); font-size: .9rem; }
.ar-avatars { display: flex; }
.ar-avatar {
	width: 32px; height: 32px; border-radius: 50%; border: 2px solid #fff;
	background: #555; overflow: hidden; margin-left: -8px; display: flex; align-items: center; justify-content: center;
}
.ar-avatar:first-child { margin-left: 0; }
.ar-avatar i { color: #ccc; font-size: .9rem; }
.ar-clients-label { color: rgba(255,255,255,.85); font-weight: 600; }

/* Search card */
.ar-search-card {
	background: rgba(255,255,255,.97); border-radius: 16px;
	padding: 28px 24px 20px; box-shadow: 0 8px 40px rgba(0,0,0,.22);
	max-width: 900px; margin: 0 auto 40px;
}
.ar-search-card .vrcdivsearch { background: transparent !important; box-shadow: none !important; border: none !important; }
.ar-search-card .vrc-searchmod-heading { display: none; }
.ar-search-card .btn.vrcsearch {
	background: #FE5001 !important; border-color: #FE5001 !important; color: #fff !important;
	font-weight: 700; padding: 10px 36px; border-radius: 8px; font-size: 1rem;
}
.ar-search-card .btn.vrcsearch:hover { background: #e04600 !important; }

@media (max-width: 767px) {
	.ar-hero-title { font-size: 2rem; }
	.ar-hero-section { padding: 40px 0 24px; }
	.ar-search-card { margin: 0 12px 24px; padding: 20px 16px 14px; }
}
/* ════════════════════════════════════════════════════════════════════════ */
');
}

// ── Carslist URL for "Book Now" CTA ──────────────────────────────────────────
$carslistUrl = JRoute::_('index.php?option=com_vikrentcar&Itemid=' . (int)$params->get('itemid'));

// ── VRC: build $vrloc (pickup / return place selects) ────────────────────────
$diffopentime = false;
$closingdays = array();
$declclosingdays = '';
$vrloc = "";
if (intval($params->get('showloc')) == 0) {
	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='placesfront';";
	$dbo->setQuery($q);
	$dbo->execute();
	if ($dbo->getNumRows() == 1) {
		$sl = $dbo->loadAssocList();
		if (intval($sl[0]['setting']) == 1) {
			$q = "SELECT * FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`ordering` ASC, `#__vikrentcar_places`.`name` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$places = $dbo->loadAssocList();
				$vrc_tn->translateContents($places, '#__vikrentcar_places');
				foreach ($places as $kpla => $pla) {
					if (!empty($pla['opentime'])) { $diffopentime = true; }
					if (!empty($pla['closingdays'])) { $closingdays[$pla['id']] = $pla['closingdays']; }
					if (!empty($svrcplace) && !empty($svrcreturnplace)) {
						if ($pla['id'] == $svrcplace) { $indvrcplace = $kpla; }
						if ($pla['id'] == $svrcreturnplace) { $indvrcreturnplace = $kpla; }
					}
				}
				$wopening_pick = array();
				if (isset($places[$indvrcplace]) && !empty($places[$indvrcplace]['wopening'])) {
					$wopening_pick = json_decode($places[$indvrcplace]['wopening'], true);
					$wopening_pick = !is_array($wopening_pick) ? array() : $wopening_pick;
				}
				$wopening_drop = array();
				if (isset($places[$indvrcreturnplace]) && !empty($places[$indvrcreturnplace]['wopening'])) {
					$wopening_drop = json_decode($places[$indvrcreturnplace]['wopening'], true);
					$wopening_drop = !is_array($wopening_drop) ? array() : $wopening_drop;
				}
				if (count($closingdays) > 0) {
					foreach ($closingdays as $idpla => $clostr) {
						$jsclosingdstr = ModVikrentcarSearchHelper::formatLocationClosingDays($clostr);
						if (count($jsclosingdstr) > 0) {
							$declclosingdays .= 'var modloc'.$idpla.'closingdays = ['.implode(", ", $jsclosingdstr).'];'."\n";
						}
					}
				}
				$onchangeplaces = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTimeModule(this.value, 'pickup');\"" : "";
				$onchangeplacesdrop = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTimeModule(this.value, 'dropoff');\"" : "";
				if ($diffopentime == true) {
					$onchangedecl = '
var vrcmod_location_change = false;
var vrcmod_wopening_pick = '.json_encode($wopening_pick).';
var vrcmod_wopening_drop = '.json_encode($wopening_drop).';
var vrcmod_hopening_pick = null;
var vrcmod_hopening_drop = null;
var vrcmod_mopening_pick = null;
var vrcmod_mopening_drop = null;
function vrcSetLocOpenTimeModule(loc, where) {
	if (where == "dropoff") { vrcmod_location_change = true; }
	jQuery.ajax({ type: "POST", url: "'.JRoute::_('index.php?option=com_vikrentcar&task=ajaxlocopentime&tmpl=component').'", data: { idloc: loc, pickdrop: where } }).done(function(res) {
		var vrcobj = jQuery.parseJSON(res);
		if (where == "pickup") {
			jQuery("#vrcmodselph").html(vrcobj.hours);
			jQuery("#vrcmodselpm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) { vrcmod_wopening_pick = vrcobj.wopening; vrcmod_hopening_pick = vrcobj.hours; }
		} else {
			jQuery("#vrcmodseldh").html(vrcobj.hours);
			jQuery("#vrcmodseldm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) { vrcmod_wopening_drop = vrcobj.wopening; vrcmod_hopening_drop = vrcobj.hours; }
		}
		if (where == "pickup" && vrcmod_location_change === false) { jQuery("#modreturnplace").val(loc).trigger("change"); vrcmod_location_change = false; }
	});
}';
					$document->addScriptDeclaration($onchangedecl);
				}
				$vrloc .= "<div class=\"vrc-searchmod-section-pickup\">\n";
				$vrloc .= "<div class=\"vrcsfentrycont\"><label for=\"modplace\">".Text::_('VRMPPLACE')."</label><div class=\"vrcsfentryselect\"><select name=\"place\" id=\"modplace\"".$onchangeplaces.">";
				foreach ($places as $pla) {
					$vrloc .= "<option value=\"".$pla['id']."\"".(!empty($svrcplace) && $svrcplace == $pla['id'] ? " selected=\"selected\"" : "").">".$pla['name']."</option>\n";
				}
				$vrloc .= "</select></div></div>\n";
				$vrloc .= "<div class=\"vrcsfentrycont\"><label for=\"modreturnplace\">".Text::_('VRMPLACERET')."</label><div class=\"vrcsfentryselect\"><select name=\"returnplace\" id=\"modreturnplace\"".(strlen($onchangeplacesdrop) > 0 ? $onchangeplacesdrop : "").">";
				foreach ($places as $pla) {
					$vrloc .= "<option value=\"".$pla['id']."\"".(!empty($svrcreturnplace) && $svrcreturnplace == $pla['id'] ? " selected=\"selected\"" : "").">".$pla['name']."</option>\n";
				}
				$vrloc .= "</select></div></div>\n";
				$vrloc .= "</div>\n";
			}
		}
	}
} elseif (intval($params->get('showloc')) == 1) {
	$q = "SELECT * FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`ordering` ASC, `#__vikrentcar_places`.`name` ASC;";
	$dbo->setQuery($q);
	$dbo->execute();
	if ($dbo->getNumRows() > 0) {
		$places = $dbo->loadAssocList();
		$vrc_tn->translateContents($places, '#__vikrentcar_places');
		foreach ($places as $kpla => $pla) {
			if (!empty($pla['opentime'])) { $diffopentime = true; }
			if (!empty($pla['closingdays'])) { $closingdays[$pla['id']] = $pla['closingdays']; }
			if (!empty($svrcplace) && !empty($svrcreturnplace)) {
				if ($pla['id'] == $svrcplace) { $indvrcplace = $kpla; }
				if ($pla['id'] == $svrcreturnplace) { $indvrcreturnplace = $kpla; }
			}
		}
		$wopening_pick = array();
		if (isset($places[$indvrcplace]) && !empty($places[$indvrcplace]['wopening'])) {
			$wopening_pick = json_decode($places[$indvrcplace]['wopening'], true);
			$wopening_pick = !is_array($wopening_pick) ? array() : $wopening_pick;
		}
		$wopening_drop = array();
		if (isset($places[$indvrcreturnplace]) && !empty($places[$indvrcreturnplace]['wopening'])) {
			$wopening_drop = json_decode($places[$indvrcreturnplace]['wopening'], true);
			$wopening_drop = !is_array($wopening_drop) ? array() : $wopening_drop;
		}
		if (count($closingdays) > 0) {
			foreach ($closingdays as $idpla => $clostr) {
				$jsclosingdstr = ModVikrentcarSearchHelper::formatLocationClosingDays($clostr);
				if (count($jsclosingdstr) > 0) {
					$declclosingdays .= 'var modloc'.$idpla.'closingdays = ['.implode(", ", $jsclosingdstr).'];'."\n";
				}
			}
		}
		$onchangeplaces = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTimeModule(this.value, 'pickup');\"" : "";
		$onchangeplacesdrop = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTimeModule(this.value, 'dropoff');\"" : "";
		if ($diffopentime == true) {
			$onchangedecl = '
var vrcmod_location_change = false;
var vrcmod_wopening_pick = '.json_encode($wopening_pick).';
var vrcmod_wopening_drop = '.json_encode($wopening_drop).';
var vrcmod_hopening_pick = null;
var vrcmod_hopening_drop = null;
var vrcmod_mopening_pick = null;
var vrcmod_mopening_drop = null;
function vrcSetLocOpenTimeModule(loc, where) {
	if (where == "dropoff") { vrcmod_location_change = true; }
	jQuery.ajax({ type: "POST", url: "'.JRoute::_('index.php?option=com_vikrentcar&task=ajaxlocopentime&tmpl=component').'", data: { idloc: loc, pickdrop: where } }).done(function(res) {
		var vrcobj = jQuery.parseJSON(res);
		if (where == "pickup") {
			jQuery("#vrcmodselph").html(vrcobj.hours); jQuery("#vrcmodselpm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) { vrcmod_wopening_pick = vrcobj.wopening; vrcmod_hopening_pick = vrcobj.hours; }
		} else {
			jQuery("#vrcmodseldh").html(vrcobj.hours); jQuery("#vrcmodseldm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) { vrcmod_wopening_drop = vrcobj.wopening; vrcmod_hopening_drop = vrcobj.hours; }
		}
		if (where == "pickup" && vrcmod_location_change === false) { jQuery("#modreturnplace").val(loc).trigger("change"); vrcmod_location_change = false; }
	});
}';
			$document->addScriptDeclaration($onchangedecl);
		}
		$vrloc .= "<div class=\"vrc-searchmod-section-pickup\">\n";
		$vrloc .= "<div class=\"vrcsfentrycont\"><label for=\"modplace\">".Text::_('VRMPPLACE')."</label><div class=\"vrcsfentryselect\"><select name=\"place\" id=\"modplace\"".$onchangeplaces.">";
		foreach ($places as $pla) {
			$vrloc .= "<option value=\"".$pla['id']."\"".(!empty($svrcplace) && $svrcplace == $pla['id'] ? " selected=\"selected\"" : "").">".$pla['name']."</option>\n";
		}
		$vrloc .= "</select></div></div>\n";
		$vrloc .= "<div class=\"vrcsfentrycont\"><label for=\"modreturnplace\">".Text::_('VRMPLACERET')."</label><div class=\"vrcsfentryselect\"><select name=\"returnplace\" id=\"modreturnplace\"".(strlen($onchangeplacesdrop) > 0 ? $onchangeplacesdrop : "").">";
		foreach ($places as $pla) {
			$vrloc .= "<option value=\"".$pla['id']."\"".(!empty($svrcreturnplace) && $svrcreturnplace == $pla['id'] ? " selected=\"selected\"" : "").">".$pla['name']."</option>\n";
		}
		$vrloc .= "</select></div></div>\n";
		$vrloc .= "</div>\n";
	}
}

// ── Opening hours & minutes ───────────────────────────────────────────────────
$i = 0; $imin = 0; $j = 23;
if ($diffopentime == true && is_array($places) && strlen($places[$indvrcplace]['opentime']) > 0) {
	$parts = explode("-", $places[$indvrcplace]['opentime']);
	if (is_array($parts) && $parts[0] != $parts[1]) {
		$opent = ModVikrentcarSearchHelper::mgetHoursMinutes($parts[0]);
		$closet = ModVikrentcarSearchHelper::mgetHoursMinutes($parts[1]);
		$i = $opent[0]; $imin = $opent[1]; $j = $closet[0];
	}
	$iret = $i; $iminret = $imin; $jret = $j;
	if ($indvrcplace != $indvrcreturnplace) {
		if (strlen($places[$indvrcreturnplace]['opentime']) > 0) {
			$parts = explode("-", $places[$indvrcreturnplace]['opentime']);
			if (is_array($parts) && $parts[0] != $parts[1]) {
				$opent = ModVikrentcarSearchHelper::mgetHoursMinutes($parts[0]);
				$closet = ModVikrentcarSearchHelper::mgetHoursMinutes($parts[1]);
				$iret = $opent[0]; $iminret = $opent[1]; $jret = $closet[0];
			} else { $iret = 0; $iminret = 0; $jret = 23; }
		} else {
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeopenstore';";
			$dbo->setQuery($q);
			$dbo->execute();
			$timeopst = $dbo->loadResult();
			$timeopst = explode("-", $timeopst);
			if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
				$opent = ModVikrentcarSearchHelper::mgetHoursMinutes($timeopst[0]);
				$closet = ModVikrentcarSearchHelper::mgetHoursMinutes($timeopst[1]);
				$iret = $opent[0]; $iminret = $opent[1]; $jret = $closet[0];
			} else { $iret = 0; $iminret = 0; $jret = 23; }
		}
	}
} else {
	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeopenstore';";
	$dbo->setQuery($q);
	$dbo->execute();
	if ($dbo->getNumRows() == 1) {
		$n = $dbo->loadAssocList();
		if (!empty($n[0]['setting'])) {
			$timeopst = explode("-", $n[0]['setting']);
			if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
				$i = ($timeopst[0] >= 3600) ? floor($timeopst[0] / 3600) : "0";
				$opent = ModVikrentcarSearchHelper::mgetHoursMinutes($timeopst[0]);
				$imin = $opent[1];
				$j = ($timeopst[1] >= 3600) ? floor($timeopst[1] / 3600) : "0";
			}
		}
	}
	$iret = $i; $iminret = $imin; $jret = $j;
}

$nowtf = 'H:i';
$sval = $session->get('getTimeFormat', '');
if (!empty($sval)) {
	$nowtf = $sval;
} else {
	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeformat';";
	$dbo->setQuery($q);
	$dbo->execute();
	if ($dbo->getNumRows() > 0) { $tfget = $dbo->loadAssocList(); $nowtf = $tfget[0]['setting']; }
}

$pickhdeftime = !empty($places[$indvrcplace]['defaulttime']) ? ((int)$places[$indvrcplace]['defaulttime'] / 3600) : '';
$hours = "";
if (!($i < $j)) {
	while (intval($i) != (int)$j) {
		$sayi = $i < 10 ? "0".$i : $i;
		if ($nowtf != 'H:i') { $ampm = $i < 12 ? ' am' : ' pm'; $ampmh = $i > 12 ? ($i - 12) : $i; $sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm; } else { $sayh = $sayi; }
		$hours .= "<option value=\"" . (int)$i . "\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">" . $sayh . "</option>\n";
		$i++; $i = $i > 23 ? 0 : $i;
	}
	$sayi = $i < 10 ? "0".$i : $i;
	if ($nowtf != 'H:i') { $ampm = $i < 12 ? ' am' : ' pm'; $ampmh = $i > 12 ? ($i - 12) : $i; $sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm; } else { $sayh = $sayi; }
	$hours .= "<option value=\"" . (int)$i . "\">" . $sayh . "</option>\n";
} else {
	while ($i <= $j) {
		$sayi = $i < 10 ? "0".$i : $i;
		if ($nowtf != 'H:i') { $ampm = $i < 12 ? ' am' : ' pm'; $ampmh = $i > 12 ? ($i - 12) : $i; $sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm; } else { $sayh = $sayi; }
		$hours .= "<option value=\"" . (int)$i . "\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">" . $sayh . "</option>\n";
		$i++;
	}
}

$drophdeftime = !empty($places[$indvrcreturnplace]['defaulttime']) ? ((int)$places[$indvrcreturnplace]['defaulttime'] / 3600) : '';
$hoursret = "";
if (!($iret < $jret)) {
	while (intval($iret) != (int)$jret) {
		$sayiret = $iret < 10 ? "0".$iret : $iret;
		if ($nowtf != 'H:i') { $ampm = $iret < 12 ? ' am' : ' pm'; $ampmh = $iret > 12 ? ($iret - 12) : $iret; $sayhret = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm; } else { $sayhret = $sayiret; }
		$hoursret .= "<option value=\"" . (int)$iret . "\"".($drophdeftime == (int)$iret ? ' selected="selected"' : '').">" . $sayhret . "</option>\n";
		$iret++; $iret = $iret > 23 ? 0 : $iret;
	}
	$sayiret = $iret < 10 ? "0".$iret : $iret;
	if ($nowtf != 'H:i') { $ampm = $iret < 12 ? ' am' : ' pm'; $ampmh = $iret > 12 ? ($iret - 12) : $iret; $sayhret = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm; } else { $sayhret = $sayiret; }
	$hoursret .= "<option value=\"" . (int)$iret . "\">" . $sayhret . "</option>\n";
} else {
	while ((int)$iret <= $jret) {
		$sayiret = $iret < 10 ? "0".$iret : $iret;
		if ($nowtf != 'H:i') { $ampm = $iret < 12 ? ' am' : ' pm'; $ampmh = $iret > 12 ? ($iret - 12) : $iret; $sayhret = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm; } else { $sayhret = $sayiret; }
		$hoursret .= "<option value=\"" . (int)$iret . "\"".($drophdeftime == (int)$iret ? ' selected="selected"' : '').">" . $sayhret . "</option>\n";
		$iret++;
	}
}

$minutes = "";
for ($i = 0; $i < 60; $i += 15) {
	if ($i < 10) { $i = "0" . $i; }
	$minutes .= "<option value=\"" . (int)$i . "\"".((int)$i == $imin ? " selected=\"selected\"" : "").">" . $i . "</option>\n";
}
$minutesret = "";
for ($iret = 0; $iret < 60; $iret += 15) {
	if ($iret < 10) { $iret = "0" . $iret; }
	$minutesret .= "<option value=\"" . (int)$iret . "\"".((int)$iret == $iminret ? " selected=\"selected\"" : "").">" . $iret . "</option>\n";
}

$sval = $session->get('getDateFormat', '');
if (!empty($sval)) {
	$dateformat = $sval;
} else {
	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
	$dbo->setQuery($q);
	$dbo->execute();
	if ($dbo->getNumRows() == 1) { $df = $dbo->loadAssocList(); $dateformat = $df[0]['setting']; }
	else { $dateformat = "%d/%m/%Y"; }
}

if ($dateformat == "%d/%m/%Y") { $juidf = 'dd/mm/yy'; }
elseif ($dateformat == "%m/%d/%Y") { $juidf = 'mm/dd/yy'; }
else { $juidf = 'yy/mm/dd'; }

$document->addStyleSheet(JURI::root().'components/com_vikrentcar/resources/jquery-ui.min.css');
JHtml::_('script', JURI::root().'components/com_vikrentcar/resources/jquery-ui.min.js');

$ldecl = '
jQuery(function($) {
	$.datepicker.regional["vikrentcarmod"] = {
		closeText: "'.Text::_('VRCJQCALDONE').'",
		prevText: "'.Text::_('VRCJQCALPREV').'",
		nextText: "'.Text::_('VRCJQCALNEXT').'",
		currentText: "'.Text::_('VRCJQCALTODAY').'",
		monthNames: ["'.Text::_('VRMONTHONE').'","'.Text::_('VRMONTHTWO').'","'.Text::_('VRMONTHTHREE').'","'.Text::_('VRMONTHFOUR').'","'.Text::_('VRMONTHFIVE').'","'.Text::_('VRMONTHSIX').'","'.Text::_('VRMONTHSEVEN').'","'.Text::_('VRMONTHEIGHT').'","'.Text::_('VRMONTHNINE').'","'.Text::_('VRMONTHTEN').'","'.Text::_('VRMONTHELEVEN').'","'.Text::_('VRMONTHTWELVE').'"],
		monthNamesShort: ["'.mb_substr(Text::_('VRMONTHONE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHTWO'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHTHREE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHFOUR'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHFIVE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHSIX'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHSEVEN'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHEIGHT'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHNINE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHTEN'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHELEVEN'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHTWELVE'),0,3,'UTF-8').'"],
		dayNames: ["'.Text::_('VRCJQCALSUN').'","'.Text::_('VRCJQCALMON').'","'.Text::_('VRCJQCALTUE').'","'.Text::_('VRCJQCALWED').'","'.Text::_('VRCJQCALTHU').'","'.Text::_('VRCJQCALFRI').'","'.Text::_('VRCJQCALSAT').'"],
		dayNamesShort: ["'.mb_substr(Text::_('VRCJQCALSUN'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALMON'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALTUE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALWED'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALTHU'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALFRI'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALSAT'),0,3,'UTF-8').'"],
		dayNamesMin: ["'.mb_substr(Text::_('VRCJQCALSUN'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALMON'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALTUE'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALWED'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALTHU'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALFRI'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALSAT'),0,2,'UTF-8').'"],
		weekHeader: "'.Text::_('VRCJQCALWKHEADER').'",
		dateFormat: "'.$juidf.'",
		firstDay: '.ModVikrentcarSearchHelper::getFirstWeekDay().',
		isRTL: false, showMonthAfterYear: false, yearSuffix: ""
	};
	$.datepicker.setDefaults($.datepicker.regional["vikrentcarmod"]);
});
function vrcGetDateObject'.$randid.'(dstring) {
	var dparts = dstring.split("-");
	return new Date(dparts[0], (parseInt(dparts[1]) - 1), parseInt(dparts[2]), 0, 0, 0, 0);
}
function vrcFullObject'.$randid.'(obj) {
	var jk; for(jk in obj) { return obj.hasOwnProperty(jk); }
}
var vrcrestrctarange, vrcrestrctdrange, vrcrestrcta, vrcrestrctd;';
$document->addScriptDeclaration($ldecl);

// Restrictions JS (preserved from home.php)
$totrestrictions = count($restrictions);
$wdaysrestrictions = array(); $wdaystworestrictions = array(); $wdaysrestrictionsrange = array();
$wdaysrestrictionsmonths = array(); $ctarestrictionsrange = array(); $ctarestrictionsmonths = array();
$ctdrestrictionsrange = array(); $ctdrestrictionsmonths = array(); $monthscomborestr = array();
$minlosrestrictions = array(); $minlosrestrictionsrange = array(); $maxlosrestrictions = array();
$maxlosrestrictionsrange = array(); $notmultiplyminlosrestrictions = array();
if ($totrestrictions > 0) {
	foreach ($restrictions as $rmonth => $restr) {
		if ($rmonth != 'range') {
			if (strlen($restr['wday']) > 0) {
				$wdaysrestrictions[] = "'".($rmonth - 1)."': '".$restr['wday']."'";
				$wdaysrestrictionsmonths[] = $rmonth;
				if (strlen($restr['wdaytwo']) > 0) {
					$wdaystworestrictions[] = "'".($rmonth - 1)."': '".$restr['wdaytwo']."'";
					$monthscomborestr[($rmonth - 1)] = ModVikrentcarSearchHelper::parseJsDrangeWdayCombo($restr);
				}
			} elseif (!empty($restr['ctad']) || !empty($restr['ctdd'])) {
				if (!empty($restr['ctad'])) { $ctarestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctad']); }
				if (!empty($restr['ctdd'])) { $ctdrestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctdd']); }
			}
			if ($restr['multiplyminlos'] == 0) { $notmultiplyminlosrestrictions[] = $rmonth; }
			$minlosrestrictions[] = "'".($rmonth - 1)."': '".$restr['minlos']."'";
			if (!empty($restr['maxlos']) && $restr['maxlos'] > 0 && $restr['maxlos'] > $restr['minlos']) {
				$maxlosrestrictions[] = "'".($rmonth - 1)."': '".$restr['maxlos']."'";
			}
		} else {
			foreach ($restr as $kr => $drestr) {
				if (strlen($drestr['wday']) > 0) {
					$wdaysrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']);
					$wdaysrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
					$wdaysrestrictionsrange[$kr][2] = $drestr['wday'];
					$wdaysrestrictionsrange[$kr][3] = $drestr['multiplyminlos'];
					$wdaysrestrictionsrange[$kr][4] = strlen($drestr['wdaytwo']) > 0 ? $drestr['wdaytwo'] : -1;
					$wdaysrestrictionsrange[$kr][5] = ModVikrentcarSearchHelper::parseJsDrangeWdayCombo($drestr);
				} elseif (!empty($drestr['ctad']) || !empty($drestr['ctdd'])) {
					$ctfrom = date('Y-m-d', $drestr['dfrom']); $ctto = date('Y-m-d', $drestr['dto']);
					if (!empty($drestr['ctad'])) { $ctarestrictionsrange[$kr][0]=$ctfrom; $ctarestrictionsrange[$kr][1]=$ctto; $ctarestrictionsrange[$kr][2]=explode(',',$drestr['ctad']); }
					if (!empty($drestr['ctdd'])) { $ctdrestrictionsrange[$kr][0]=$ctfrom; $ctdrestrictionsrange[$kr][1]=$ctto; $ctdrestrictionsrange[$kr][2]=explode(',',$drestr['ctdd']); }
				}
				$minlosrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']);
				$minlosrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
				$minlosrestrictionsrange[$kr][2] = $drestr['minlos'];
				if (!empty($drestr['maxlos']) && $drestr['maxlos'] > 0 && $drestr['maxlos'] > $drestr['minlos']) {
					$maxlosrestrictionsrange[$kr] = $drestr['maxlos'];
				}
			}
			unset($restrictions['range']);
		}
	}
	$resdecl = "
var vrcrestrmonthswdays = [".implode(", ", $wdaysrestrictionsmonths)."];
var vrcrestrmonths = [".implode(", ", array_keys($restrictions))."];
var vrcrestrmonthscombojn = jQuery.parseJSON('".json_encode($monthscomborestr)."');
var vrcrestrminlos = {".implode(", ", $minlosrestrictions)."};
var vrcrestrminlosrangejn = jQuery.parseJSON('".json_encode($minlosrestrictionsrange)."');
var vrcrestrmultiplyminlos = [".implode(", ", $notmultiplyminlosrestrictions)."];
var vrcrestrmaxlos = {".implode(", ", $maxlosrestrictions)."};
var vrcrestrmaxlosrangejn = jQuery.parseJSON('".json_encode($maxlosrestrictionsrange)."');
var vrcrestrwdaysrangejn = jQuery.parseJSON('".json_encode($wdaysrestrictionsrange)."');
var vrcrestrcta = jQuery.parseJSON('".json_encode($ctarestrictionsmonths)."');
var vrcrestrctarange = jQuery.parseJSON('".json_encode($ctarestrictionsrange)."');
var vrcrestrctd = jQuery.parseJSON('".json_encode($ctdrestrictionsmonths)."');
var vrcrestrctdrange = jQuery.parseJSON('".json_encode($ctdrestrictionsrange)."');
var vrccombowdays = {};
function vrcRefreshDropoff".$randid."(darrive) {
	if(vrcFullObject".$randid."(vrccombowdays)) {
		var vrctosort = new Array();
		for(var vrci in vrccombowdays) { if(vrccombowdays.hasOwnProperty(vrci)) { var vrcusedate = darrive; vrctosort[vrci] = vrcusedate.setDate(vrcusedate.getDate() + (vrccombowdays[vrci] - 1 - vrcusedate.getDay() + 7) % 7 + 1); } }
		vrctosort.sort(function(da, db) { return da > db ? 1 : -1; });
		for(var vrcnext in vrctosort) { if(vrctosort.hasOwnProperty(vrcnext)) { var vrcfirstnextd = new Date(vrctosort[vrcnext]); jQuery('#releasedatemod".$randid."').datepicker('option','minDate',vrcfirstnextd); jQuery('#releasedatemod".$randid."').datepicker('setDate',vrcfirstnextd); break; } }
	}
}
var vrcDropMaxDateSet".$randid." = false;
function vrcSetMinDropoffDate".$randid." () {
	var vrcDropMaxDateSetNow".$randid." = false;
	var minlos = ".(intval($def_min_los) > 0 ? $def_min_los : '0').";
	var maxlosrange = 0;
	var nowpickup = jQuery('#pickupdatemod".$randid."').datepicker('getDate');
	var nowd = nowpickup.getDay(); var nowpickupdate = new Date(nowpickup.getTime()); vrccombowdays = {};
	if(vrcFullObject".$randid."(vrcrestrminlosrangejn)) {
		for (var rk in vrcrestrminlosrangejn) { if(vrcrestrminlosrangejn.hasOwnProperty(rk)) { var minldrangeinit = vrcGetDateObject".$randid."(vrcrestrminlosrangejn[rk][0]); if(nowpickupdate >= minldrangeinit) { var minldrangeend = vrcGetDateObject".$randid."(vrcrestrminlosrangejn[rk][1]); if(nowpickupdate <= minldrangeend) { minlos = parseInt(vrcrestrminlosrangejn[rk][2]); if(vrcFullObject".$randid."(vrcrestrmaxlosrangejn)) { if(rk in vrcrestrmaxlosrangejn) { maxlosrange = parseInt(vrcrestrmaxlosrangejn[rk]); } } if(rk in vrcrestrwdaysrangejn && nowd in vrcrestrwdaysrangejn[rk][5]) { vrccombowdays = vrcrestrwdaysrangejn[rk][5][nowd]; } } } } }
	}
	var nowm = nowpickup.getMonth();
	if(vrcFullObject".$randid."(vrcrestrmonthscombojn) && vrcrestrmonthscombojn.hasOwnProperty(nowm)) { if(nowd in vrcrestrmonthscombojn[nowm]) { vrccombowdays = vrcrestrmonthscombojn[nowm][nowd]; } }
	if(jQuery.inArray((nowm + 1), vrcrestrmonths) != -1) { minlos = parseInt(vrcrestrminlos[nowm]); }
	nowpickupdate.setDate(nowpickupdate.getDate() + minlos);
	jQuery('#releasedatemod".$randid."').datepicker('option','minDate',nowpickupdate);
	if(maxlosrange > 0) { var diffmaxminlos = maxlosrange - minlos; var maxdropoffdate = new Date(nowpickupdate.getTime()); maxdropoffdate.setDate(maxdropoffdate.getDate() + diffmaxminlos); jQuery('#releasedatemod".$randid."').datepicker('option','maxDate',maxdropoffdate); vrcDropMaxDateSet".$randid." = true; vrcDropMaxDateSetNow".$randid." = true; }
	if(nowm in vrcrestrmaxlos) { var diffmaxminlos = parseInt(vrcrestrmaxlos[nowm]) - minlos; var maxdropoffdate = new Date(nowpickupdate.getTime()); maxdropoffdate.setDate(maxdropoffdate.getDate() + diffmaxminlos); jQuery('#releasedatemod".$randid."').datepicker('option','maxDate',maxdropoffdate); vrcDropMaxDateSet".$randid." = true; vrcDropMaxDateSetNow".$randid." = true; }
	if(!vrcFullObject".$randid."(vrccombowdays)) { jQuery('#releasedatemod".$randid."').datepicker('setDate',nowpickupdate); if (!vrcDropMaxDateSetNow".$randid." && vrcDropMaxDateSet".$randid." === true) { jQuery('#releasedatemod".$randid."').datepicker('option','maxDate',null); } } else { vrcRefreshDropoff".$randid."(nowpickup); }
}";
	if (count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0) {
		$resdecl .= "
var vrcrestrwdays = {".implode(", ", $wdaysrestrictions)."};
var vrcrestrwdaystwo = {".implode(", ", $wdaystworestrictions)."};
function vrcIsDayDisabled".$randid."(date) {
	if(!vrcValidateCta".$randid."(date)) { return [false]; }
	".(strlen($declclosingdays) > 0 ? "var loc_closing = modpickupClosingDays".$randid."(date); if (!loc_closing[0]) {return loc_closing;}" : "")."
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject".$randid."(vrcrestrwdaysrangejn)) { for (var rk in vrcrestrwdaysrangejn) { if(vrcrestrwdaysrangejn.hasOwnProperty(rk)) { var wdrangeinit = vrcGetDateObject".$randid."(vrcrestrwdaysrangejn[rk][0]); if(date >= wdrangeinit) { var wdrangeend = vrcGetDateObject".$randid."(vrcrestrwdaysrangejn[rk][1]); if(date <= wdrangeend) { if(wd != vrcrestrwdaysrangejn[rk][2]) { if(vrcrestrwdaysrangejn[rk][4] == -1 || wd != vrcrestrwdaysrangejn[rk][4]) { return [false]; } } } } } } }
	if(vrcFullObject".$randid."(vrcrestrwdays)) { if(jQuery.inArray((m+1), vrcrestrmonthswdays) == -1) { return [true]; } if(wd == vrcrestrwdays[m]) { return [true]; } if(vrcFullObject".$randid."(vrcrestrwdaystwo)) { if(wd == vrcrestrwdaystwo[m]) { return [true]; } } return [false]; }
	return [true];
}
function vrcIsDayDisabledDropoff".$randid."(date) {
	if(!vrcValidateCtd".$randid."(date)) { return [false]; }
	".(strlen($declclosingdays) > 0 ? "var loc_closing = moddropoffClosingDays".$randid."(date); if (!loc_closing[0]) {return loc_closing;}" : "")."
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject".$randid."(vrccombowdays)) { if(jQuery.inArray(wd, vrccombowdays) != -1) { return [true]; } else { return [false]; } }
	if(vrcFullObject".$randid."(vrcrestrwdaysrangejn)) { for (var rk in vrcrestrwdaysrangejn) { if(vrcrestrwdaysrangejn.hasOwnProperty(rk)) { var wdrangeinit = vrcGetDateObject".$randid."(vrcrestrwdaysrangejn[rk][0]); if(date >= wdrangeinit) { var wdrangeend = vrcGetDateObject".$randid."(vrcrestrwdaysrangejn[rk][1]); if(date <= wdrangeend) { if(wd != vrcrestrwdaysrangejn[rk][2] && vrcrestrwdaysrangejn[rk][3] == 1) { return [false]; } } } } } }
	if(vrcFullObject".$randid."(vrcrestrwdays)) { if(jQuery.inArray((m+1), vrcrestrmonthswdays) == -1 || jQuery.inArray((m+1), vrcrestrmultiplyminlos) != -1) { return [true]; } if(wd == vrcrestrwdays[m]) { return [true]; } return [false]; }
	return [true];
}";
	}
	$document->addScriptDeclaration($resdecl);
}

if (strlen($declclosingdays) > 0) {
	$declclosingdays .= '
function modpickupClosingDays'.$randid.'(date) {
	var dmy = date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate(); var wday = date.getDay().toString();
	var arrlocclosd = jQuery("#modplace").val(); var checklocarr = window["modloc"+arrlocclosd+"closingdays"];
	if (jQuery.inArray(dmy, checklocarr) == -1 && jQuery.inArray(wday, checklocarr) == -1) { return [true, ""]; } else { return [false, "", "'.addslashes(Text::_('VRCMLOCDAYCLOSED')).'"]; }
}
function moddropoffClosingDays'.$randid.'(date) {
	var dmy = date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate(); var wday = date.getDay().toString();
	var arrlocclosd = jQuery("#modreturnplace").val(); var checklocarr = window["modloc"+arrlocclosd+"closingdays"];
	if (jQuery.inArray(dmy, checklocarr) == -1 && jQuery.inArray(wday, checklocarr) == -1) { return [true, ""]; } else { return [false, "", "'.addslashes(Text::_('VRCMLOCDAYCLOSED')).'"]; }
}';
	$document->addScriptDeclaration($declclosingdays);
}

$dropdayplus = $def_min_los;
$forcedropday = "jQuery('#releasedatemod".$randid."').datepicker('option','minDate',selectedDate);";
if (strlen($dropdayplus) > 0 && intval($dropdayplus) > 0) {
	$forcedropday = "var nowpick = jQuery(this).datepicker('getDate'); if(nowpick){var nowpickdate=new Date(nowpick.getTime()); nowpickdate.setDate(nowpickdate.getDate()+".$dropdayplus."); jQuery('#releasedatemod".$randid."').datepicker('option','minDate',nowpickdate); jQuery('#releasedatemod".$randid."').datepicker('setDate',nowpickdate);}";
}

$sdecl = "
function vrcCheckClosingDatesIn".$randid."(date) { if(!vrcValidateCta".$randid."(date)){return [false];} ".(strlen($declclosingdays)>0?"var loc_closing=modpickupClosingDays".$randid."(date); if(!loc_closing[0]){return loc_closing;}":"")." return [true]; }
function vrcCheckClosingDatesOut".$randid."(date) { if(!vrcValidateCtd".$randid."(date)){return [false];} ".(strlen($declclosingdays)>0?"var loc_closing=moddropoffClosingDays".$randid."(date); if(!loc_closing[0]){return loc_closing;}":"")." return [true]; }
function vrcValidateCta".$randid."(date) {
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject".$randid."(vrcrestrctarange)){for(var rk in vrcrestrctarange){if(vrcrestrctarange.hasOwnProperty(rk)){var wdrangeinit=vrcGetDateObject".$randid."(vrcrestrctarange[rk][0]);if(date>=wdrangeinit){var wdrangeend=vrcGetDateObject".$randid."(vrcrestrctarange[rk][1]);if(date<=wdrangeend){if(jQuery.inArray('-'+wd+'-',vrcrestrctarange[rk][2])>=0){return false;}}}}}}
	if(vrcFullObject".$randid."(vrcrestrcta)){if(vrcrestrcta.hasOwnProperty(m)&&jQuery.inArray('-'+wd+'-',vrcrestrcta[m])>=0){return false;}}
	return true;
}
function vrcValidateCtd".$randid."(date) {
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject".$randid."(vrcrestrctdrange)){for(var rk in vrcrestrctdrange){if(vrcrestrctdrange.hasOwnProperty(rk)){var wdrangeinit=vrcGetDateObject".$randid."(vrcrestrctdrange[rk][0]);if(date>=wdrangeinit){var wdrangeend=vrcGetDateObject".$randid."(vrcrestrctdrange[rk][1]);if(date<=wdrangeend){if(jQuery.inArray('-'+wd+'-',vrcrestrctdrange[rk][2])>=0){return false;}}}}}}
	if(vrcFullObject".$randid."(vrcrestrctd)){if(vrcrestrctd.hasOwnProperty(m)&&jQuery.inArray('-'+wd+'-',vrcrestrctd[m])>=0){return false;}}
	return true;
}
function vrcLocationWopening".$randid."(mode) {
	if(typeof vrcmod_wopening_pick==='undefined'){return true;}
	if(mode=='pickup'){vrcmod_mopening_pick=null;}else{vrcmod_mopening_drop=null;}
	var loc_data=mode=='pickup'?vrcmod_wopening_pick:vrcmod_wopening_drop;
	var def_loc_hours=mode=='pickup'?vrcmod_hopening_pick:vrcmod_hopening_drop;
	var sel_d=jQuery((mode=='pickup'?'#pickupdatemod".$randid."':'#releasedatemod".$randid."')).datepicker('getDate');
	if(!sel_d){return true;}
	var sel_wday=sel_d.getDay();
	if(!vrcFullObject".$randid."(loc_data)||!loc_data.hasOwnProperty(sel_wday)||!loc_data[sel_wday].hasOwnProperty('fh')){if(def_loc_hours!==null){jQuery((mode=='pickup'?'#vrcmodselph':'#vrcmodseldh')).html(def_loc_hours);}return true;}
	if(mode=='pickup'){vrcmod_mopening_pick=new Array(loc_data[sel_wday]['fh'],loc_data[sel_wday]['fm']);}else{vrcmod_mopening_drop=new Array(loc_data[sel_wday]['th'],loc_data[sel_wday]['tm']);}
	var hlim=loc_data[sel_wday]['fh']<loc_data[sel_wday]['th']?loc_data[sel_wday]['th']:(24+loc_data[sel_wday]['th']);
	hlim=loc_data[sel_wday]['fh']==0&&loc_data[sel_wday]['th']==0?23:hlim;
	var hopts=''; var def_hour=jQuery((mode=='pickup'?'#vrcmodselph':'#vrcmodseldh')).find('select').val();
	def_hour=def_hour.length>1&&def_hour.substr(0,1)=='0'?def_hour.substr(1):def_hour; def_hour=parseInt(def_hour);
	for(var h=loc_data[sel_wday]['fh'];h<=hlim;h++){var viewh=h>23?(h-24):h;hopts+='<option value=\''+viewh+'\''+(viewh==def_hour?' selected':')+'>'+(viewh<10?'0'+viewh:viewh)+'</option>';}
	jQuery((mode=='pickup'?'#vrcmodselph':'#vrcmodseldh')).find('select').html(hopts);
	if(mode=='pickup'){setTimeout(function(){vrcLocationWopening".$randid."('dropoff');},750);}
}
function vrcInitElems".$randid."() {
	if(typeof vrcmod_wopening_pick==='undefined'){return true;}
	vrcmod_hopening_pick=jQuery('#vrcmodselph').find('select').clone();
	vrcmod_hopening_drop=jQuery('#vrcmodseldh').find('select').clone();
}
jQuery(function() {
	vrcInitElems".$randid."();
	jQuery.datepicker.setDefaults(jQuery.datepicker.regional['']);
	jQuery('#pickupdatemod".$randid."').datepicker({
		showOn:'focus',".(count($wdaysrestrictions)>0||count($wdaysrestrictionsrange)>0?"\nbeforeShowDay:vrcIsDayDisabled".$randid.",\n":"\nbeforeShowDay:vrcCheckClosingDatesIn".$randid.",\n")."
		onSelect:function(selectedDate){ ".($totrestrictions>0?"vrcSetMinDropoffDate".$randid."();":$forcedropday)." vrcLocationWopening".$randid."('pickup'); }
	});
	jQuery('#pickupdatemod".$randid."').datepicker('option','dateFormat','".$juidf."');
	jQuery('#pickupdatemod".$randid."').datepicker('option','minDate','".ModVikrentcarSearchHelper::getMinDaysAdvance()."d');
	jQuery('#pickupdatemod".$randid."').datepicker('option','maxDate','".ModVikrentcarSearchHelper::getMaxDateFuture()."');
	jQuery('#releasedatemod".$randid."').datepicker({
		showOn:'focus',".(count($wdaysrestrictions)>0||count($wdaysrestrictionsrange)>0?"\nbeforeShowDay:vrcIsDayDisabledDropoff".$randid.",\n":"\nbeforeShowDay:vrcCheckClosingDatesOut".$randid.",\n")."
		onSelect:function(selectedDate){ vrcLocationWopening".$randid."('dropoff'); }
	});
	jQuery('#releasedatemod".$randid."').datepicker('option','dateFormat','".$juidf."');
	jQuery('#releasedatemod".$randid."').datepicker('option','minDate','".ModVikrentcarSearchHelper::getMinDaysAdvance()."d');
	jQuery('#releasedatemod".$randid."').datepicker('option','maxDate','".ModVikrentcarSearchHelper::getMaxDateFuture()."');
	jQuery('#pickupdatemod".$randid."').datepicker('option',jQuery.datepicker.regional['vikrentcarmod']);
	jQuery('#releasedatemod".$randid."').datepicker('option',jQuery.datepicker.regional['vikrentcarmod']);
	jQuery('.vr-cal-img,.vrc-caltrigger').click(function(){var jdp=jQuery(this).prev('input.hasDatepicker');if(jdp.length){jdp.focus();}});
});";
$document->addScriptDeclaration($sdecl);

// Categories
$vrcats = "";
if (intval($params->get('showcat')) == 0) {
	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='showcategories';";
	$dbo->setQuery($q); $dbo->execute();
	if ($dbo->getNumRows() == 1) {
		$sc = $dbo->loadAssocList();
		if (intval($sc[0]['setting']) == 1) {
			$q = "SELECT * FROM `#__vikrentcar_categories` ORDER BY `ordering` ASC, `name` ASC;";
			$dbo->setQuery($q); $dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$categories = $dbo->loadAssocList();
				$vrc_tn->translateContents($categories, '#__vikrentcar_categories');
				$vrcats .= "<div class=\"vrc-searchmod-section-categories\"><div class=\"vrcsfentrycont\"><label for=\"vrc-categories".$randid."\">".Text::_('VRMCARCAT')."</label><div class=\"vrcsfentryselect\"><select id=\"vrc-categories".$randid."\" name=\"categories\"><option value=\"all\">".Text::_('VRMALLCAT')."</option>\n";
				foreach ($categories as $cat) { $vrcats .= "<option value=\"".$cat['id']."\">".$cat['name']."</option>\n"; }
				$vrcats .= "</select></div></div></div>";
			}
		} elseif (intval($params->get('category_id')) > 0) {
			$q = "SELECT * FROM `#__vikrentcar_categories` WHERE `id`=".(int)$params->get('category_id').";";
			$dbo->setQuery($q); $dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$categories = $dbo->loadAssocList();
				$vrc_tn->translateContents($categories, '#__vikrentcar_categories');
				$vrcats .= "<input type=\"hidden\" name=\"categories\" value=\"".$categories[0]['id']."\" />";
			}
		}
	}
} elseif (intval($params->get('showcat')) == 1) {
	$q = "SELECT * FROM `#__vikrentcar_categories` ORDER BY `ordering` ASC, `name` ASC;";
	$dbo->setQuery($q); $dbo->execute();
	if ($dbo->getNumRows() > 0) {
		$categories = $dbo->loadAssocList();
		$vrc_tn->translateContents($categories, '#__vikrentcar_categories');
		$vrcats .= "<div class=\"vrc-searchmod-section-categories\"><div class=\"vrcsfentrycont\"><label for=\"vrc-categories".$randid."\">".Text::_('VRMCARCAT')."</label><div class=\"vrcsfentryselect\"><select id=\"vrc-categories".$randid."\" name=\"categories\"><option value=\"all\">".Text::_('VRMALLCAT')."</option>\n";
		foreach ($categories as $cat) { $vrcats .= "<option value=\"".$cat['id']."\">".$cat['name']."</option>\n"; }
		$vrcats .= "</select></div></div></div>";
	}
} elseif (intval($params->get('category_id')) > 0) {
	$q = "SELECT * FROM `#__vikrentcar_categories` WHERE `id`=".(int)$params->get('category_id').";";
	$dbo->setQuery($q); $dbo->execute();
	if ($dbo->getNumRows() > 0) {
		$categories = $dbo->loadAssocList();
		$vrc_tn->translateContents($categories, '#__vikrentcar_categories');
		$vrcats .= "<input type=\"hidden\" name=\"categories\" value=\"".$categories[0]['id']."\" />";
	}
}

// ── HTML OUTPUT ───────────────────────────────────────────────────────────────
?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?> ar-hero-layout">

	<!-- ── Hero text ── -->
	<div class="ar-hero-section">
		<div class="ar-hero-inner">

			<h1 class="ar-hero-title"><?php echo $hero['headline']; ?></h1>
			<p class="ar-hero-subtitle"><?php echo $hero['subtitle']; ?></p>

			<div class="ar-hero-ctas">
				<a href="<?php echo $carslistUrl; ?>" class="ar-btn ar-btn-primary">
					<i class="fa fa-car" style="margin-right:8px;"></i><?php echo $hero['cta1']; ?>
				</a>
				<a href="tel:+37368001155" class="ar-btn ar-btn-outline">
					<i class="fa fa-phone" style="margin-right:8px;"></i><?php echo $hero['cta2']; ?>
				</a>
			</div>

			<div class="ar-hero-badges">
				<div class="ar-badge">
					<i class="fa fa-car"></i>
					<span><?php echo $hero['badge1']; ?></span>
				</div>
				<div class="ar-badge">
					<i class="fa fa-tag"></i>
					<span><?php echo $hero['badge2']; ?></span>
				</div>
			</div>

			<div class="ar-hero-clients">
				<div class="ar-avatars">
					<div class="ar-avatar"><i class="fa fa-user"></i></div>
					<div class="ar-avatar"><i class="fa fa-user"></i></div>
					<div class="ar-avatar"><i class="fa fa-user"></i></div>
					<div class="ar-avatar"><i class="fa fa-user"></i></div>
				</div>
				<span class="ar-clients-label"><?php echo $hero['clients']; ?></span>
			</div>

		</div>
	</div><!-- /ar-hero-section -->

	<!-- ── Search form card ── -->
	<div class="ar-search-card">
		<div class="vrcdivsearch vrcdivsearchmodule <?php echo $params->get('orientation') == 'horizontal' ? 'vrc-searchmod-wrap-horizontal' : 'vrc-searchmod-wrap-vertical'; ?>">
			<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'); ?>" method="get" onsubmit="return vrcValidateSearch<?php echo $randid; ?>();">
				<input type="hidden" name="option" value="com_vikrentcar"/>
				<input type="hidden" name="task" value="search"/>
				<input type="hidden" name="Itemid" value="<?php echo $params->get('itemid'); ?>"/>

				<?php echo $vrloc; ?>

				<div class="vrc-searchmod-section-datetimes">
					<div class="vrcsfentrycont">
						<div class="vrcsfentrylabsel">
							<label for="pickupdatemod<?php echo $randid; ?>"><?php echo Text::_('VRMPICKUPCAR'); ?></label>
							<div class="vrcsfentrydate">
								<input type="text" name="pickupdate" id="pickupdatemod<?php echo $randid; ?>" size="10" autocomplete="off" onfocus="this.blur();" readonly/>
								<i class="fa fa-calendar vrc-caltrigger"></i>
							</div>
						</div>
						<div class="vrcsfentrytime">
							<label><?php echo Text::_('VRMALLE'); ?></label>
							<div class="vrc-sm-time-container">
								<span id="vrcmodselph"><select name="pickuph"><?php echo $hours; ?></select></span>
								<span class="vrctimesep">:</span>
								<span id="vrcmodselpm"><select name="pickupm"><?php echo $minutes; ?></select></span>
							</div>
						</div>
					</div>
					<div class="vrcsfentrycont">
						<div class="vrcsfentrylabsel">
							<label for="releasedatemod<?php echo $randid; ?>"><?php echo Text::_('VRMRETURNCAR'); ?></label>
							<div class="vrcsfentrydate">
								<input type="text" name="releasedate" id="releasedatemod<?php echo $randid; ?>" size="10" autocomplete="off" onfocus="this.blur();" readonly/>
								<i class="fa fa-calendar vrc-caltrigger"></i>
							</div>
						</div>
						<div class="vrcsfentrytime">
							<label><?php echo Text::_('VRMALLEDROP'); ?></label>
							<div class="vrc-sm-time-container">
								<span id="vrcmodseldh"><select name="releaseh"><?php echo $hoursret; ?></select></span>
								<span class="vrctimesep">:</span>
								<span id="vrcmodseldm"><select name="releasem"><?php echo $minutesret; ?></select></span>
							</div>
						</div>
					</div>
				</div>

				<?php echo $vrcats; ?>

				<div class="vrc-searchmod-section-sbmt">
					<div class="vrcsfentrycont">
						<div class="vrcsfentrysubmit">
							<button type="submit" class="btn vrcsearch">
								<?php echo (strlen($params->get('srchbtntext')) > 0 ? $params->get('srchbtntext') : Text::_('SEARCHD')); ?>
							</button>
						</div>
					</div>
				</div>

			</form>
		</div>
	</div><!-- /ar-search-card -->

</div><!-- /ar-hero-layout -->

<?php
// Session restore
$sespickupts = $session->get('vrcpickupts', '');
$sesdropoffts = $session->get('vrcreturnts', '');
$ptask = $input->getString('task', '');
if ($ptask == 'search' && !empty($sespickupts) && !empty($sesdropoffts)) {
	if ($dateformat == "%d/%m/%Y") { $jsdf = 'd/m/Y'; }
	elseif ($dateformat == "%m/%d/%Y") { $jsdf = 'm/d/Y'; }
	else { $jsdf = 'Y/m/d'; }
	$sespickuph = date('H', $sespickupts); $sespickupm = date('i', $sespickupts);
	$sesdropoffh = date('H', $sesdropoffts); $sesdropoffm = date('i', $sesdropoffts);
	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		document.getElementById('pickupdatemod<?php echo $randid; ?>').value = '<?php echo date($jsdf, $sespickupts); ?>';
		document.getElementById('releasedatemod<?php echo $randid; ?>').value = '<?php echo date($jsdf, $sesdropoffts); ?>';
		var modf = jQuery("#pickupdatemod<?php echo $randid; ?>").closest("form");
		modf.find("select[name='pickuph']").val("<?php echo $sespickuph; ?>");
		modf.find("select[name='pickupm']").val("<?php echo $sespickupm; ?>");
		modf.find("select[name='releaseh']").val("<?php echo $sesdropoffh; ?>");
		modf.find("select[name='releasem']").val("<?php echo $sesdropoffm; ?>");
	});
	</script>
	<?php
}
?>
<script type="text/javascript">
function vrcCleanNumber<?php echo $randid; ?>(snum) {
	if (snum.length > 1 && snum.substr(0, 1) == '0') { return parseInt(snum.substr(1)); }
	return parseInt(snum);
}
function vrcValidateSearch<?php echo $randid; ?>() {
	if (typeof jQuery === 'undefined' || typeof vrcmod_wopening_pick === 'undefined') { return true; }
	if (vrcmod_mopening_pick !== null) {
		var pickh = jQuery('#vrcmodselph').find('select').val();
		var pickm = jQuery('#vrcmodselpm').find('select').val();
		if (!pickh || !pickh.length || !pickm) { return true; }
		pickh = vrcCleanNumber<?php echo $randid; ?>(pickh);
		pickm = vrcCleanNumber<?php echo $randid; ?>(pickm);
		if (pickh == vrcmod_mopening_pick[0]) {
			if (pickm < vrcmod_mopening_pick[1]) {
				jQuery('#vrcmodselpm').find('select').html('<option value="'+vrcmod_mopening_pick[1]+'">'+(vrcmod_mopening_pick[1] < 10 ? '0'+vrcmod_mopening_pick[1] : vrcmod_mopening_pick[1])+'</option>').val(vrcmod_mopening_pick[1]);
			}
		}
	}
	if (vrcmod_mopening_drop !== null) {
		var droph = jQuery('#vrcmodseldh').find('select').val();
		var dropm = jQuery('#vrcmodseldm').find('select').val();
		if (!droph || !droph.length || !dropm) { return true; }
		droph = vrcCleanNumber<?php echo $randid; ?>(droph);
		dropm = vrcCleanNumber<?php echo $randid; ?>(dropm);
		if (droph == vrcmod_mopening_drop[0]) {
			if (dropm > vrcmod_mopening_drop[1]) {
				jQuery('#vrcmodseldm').find('select').html('<option value="'+vrcmod_mopening_drop[1]+'">'+(vrcmod_mopening_drop[1] < 10 ? '0'+vrcmod_mopening_drop[1] : vrcmod_mopening_drop[1])+'</option>').val(vrcmod_mopening_drop[1]);
			}
		}
	}
	return true;
}
</script>
