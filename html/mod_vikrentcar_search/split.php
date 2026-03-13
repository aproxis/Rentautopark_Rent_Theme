<?php
/**
 * mod_vikrentcar_search - AutoRent Split Layout
 * Alternative layout: "split" — form on the left, hero content on the right.
 * Activate in Module Manager → Advanced → Alternative Layout → "AutoRent Split".
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
$langTag  = JFactory::getLanguage()->getTag();
$splitData = array(
	'ro-RO' => array(
		'headline' => 'Închiriează Mașina<br><span class="ar-accent">Perfectă</span>',
		'subtitle' => 'Flotă modernă &middot; Prețuri transparente &middot; Asistență 24/7',
		'cta1'     => 'Rezervă Acum',
		'cta2'     => 'Sună: +373 68 001 155',
		'badge1'   => '21 mașini',
		'badge2'   => 'de la 28€/zi',
		'clients'  => '1000+ clienți',
	),
	'ru-RU' => array(
		'headline' => 'Арендуйте<br><span class="ar-accent">Автомобиль</span> Мечты',
		'subtitle' => 'Современный парк &middot; Прозрачные цены &middot; Поддержка 24/7',
		'cta1'     => 'Забронировать',
		'cta2'     => 'Позвонить: +373 68 001 155',
		'badge1'   => '21 авто',
		'badge2'   => 'от 28€/день',
		'clients'  => '1000+ клиентов',
	),
	'en-GB' => array(
		'headline' => 'Rent Your<br><span class="ar-accent">Perfect Car</span> Now',
		'subtitle' => 'Modern fleet &middot; Transparent prices &middot; 24/7 support',
		'cta1'     => 'Book Now',
		'cta2'     => 'Call: +373 68 001 155',
		'badge1'   => '21 cars',
		'badge2'   => 'from 28€/day',
		'clients'  => '1000+ clients',
	),
);
$split = isset($splitData[$langTag]) ? $splitData[$langTag] : $splitData['ro-RO'];

// ── One-time CSS ──────────────────────────────────────────────────────────────
static $arSplitStylesLoaded = false;
if (!$arSplitStylesLoaded) {
	$arSplitStylesLoaded = true;
	$document->addStyleDeclaration('
/* ═══ AutoRent Split Layout ══════════════════════════════════════════════ */
.ar-split-layout { display: flex; flex-wrap: wrap; align-items: stretch; min-height: 520px; }

/* Left: form column */
.ar-split-form {
	flex: 0 0 42%; max-width: 42%;
	background: rgba(255,255,255,.97); border-radius: 16px;
	padding: 36px 28px 28px; box-shadow: 0 8px 40px rgba(0,0,0,.18);
	display: flex; flex-direction: column; justify-content: center;
}
.ar-split-form .vrcdivsearch { background: transparent !important; box-shadow: none !important; border: none !important; }
.ar-split-form .vrc-searchmod-heading { display: none; }
.ar-split-form-title {
	font-size: 1.1rem; font-weight: 700; color: #0a0a0a;
	margin-bottom: 18px; padding-bottom: 12px;
	border-bottom: 2px solid #FE5001; display: inline-block;
}
.ar-split-form .btn.vrcsearch {
	background: #FE5001 !important; border-color: #FE5001 !important; color: #fff !important;
	font-weight: 700; padding: 12px 0; border-radius: 8px; font-size: 1rem;
	width: 100%; display: block;
}
.ar-split-form .btn.vrcsearch:hover { background: #e04600 !important; }
.ar-split-form .vrc-searchmod-section-sbmt .vrcsfentrycont,
.ar-split-form .vrc-searchmod-section-sbmt .vrcsfentrysubmit { width: 100%; }

/* Right: hero content column */
.ar-split-hero {
	flex: 0 0 58%; max-width: 58%;
	padding: 60px 48px 40px;
	display: flex; flex-direction: column; justify-content: center;
}
.ar-split-title {
	font-size: 2.8rem; font-weight: 800; color: #fff; line-height: 1.15;
	margin-bottom: 16px; text-shadow: 0 2px 12px rgba(0,0,0,.4);
}
.ar-split-title .ar-accent { color: #FE5001; }
.ar-split-subtitle {
	font-size: 1rem; color: rgba(255,255,255,.82); margin-bottom: 28px;
	letter-spacing: .02em;
}
.ar-split-ctas { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 30px; }
.ar-split-badges { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
.ar-split-badge {
	display: flex; align-items: center; gap: 8px;
	background: rgba(255,255,255,.14); backdrop-filter: blur(8px);
	border: 1px solid rgba(255,255,255,.25); border-radius: 10px;
	padding: 10px 18px; color: #fff; font-weight: 600; font-size: .9rem;
}
.ar-split-badge i { color: #FE5001; }
.ar-split-clients { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.75); font-size: .88rem; }
.ar-split-avatars { display: flex; }
.ar-split-avatar {
	width: 30px; height: 30px; border-radius: 50%; border: 2px solid #fff;
	background: #555; margin-left: -8px; display: flex; align-items: center; justify-content: center;
}
.ar-split-avatar:first-child { margin-left: 0; }
.ar-split-avatar i { color: #ccc; font-size: .85rem; }
.ar-split-clients-label { color: rgba(255,255,255,.85); font-weight: 600; }

@media (max-width: 991px) {
	.ar-split-form, .ar-split-hero { flex: 0 0 100%; max-width: 100%; }
	.ar-split-hero { padding: 40px 20px 30px; }
	.ar-split-form { border-radius: 0 0 16px 16px; }
	.ar-split-title { font-size: 2rem; }
}
/* ════════════════════════════════════════════════════════════════════════ */
');
}

$carslistUrl = JRoute::_('index.php?option=com_vikrentcar&Itemid=' . (int)$params->get('itemid'));

// ── VRC: build $vrloc ────────────────────────────────────────────────────────
$diffopentime = false;
$closingdays = array();
$declclosingdays = '';
$vrloc = "";
if (intval($params->get('showloc')) == 0) {
	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='placesfront';";
	$dbo->setQuery($q); $dbo->execute();
	if ($dbo->getNumRows() == 1) {
		$sl = $dbo->loadAssocList();
		if (intval($sl[0]['setting']) == 1) {
			$q = "SELECT * FROM `#__vikrentcar_places` ORDER BY `ordering` ASC, `name` ASC;";
			$dbo->setQuery($q); $dbo->execute();
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
						if (count($jsclosingdstr) > 0) { $declclosingdays .= 'var modloc'.$idpla.'closingdays = ['.implode(", ", $jsclosingdstr).'];'."\n"; }
					}
				}
				$onchangeplaces = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTimeModule(this.value, 'pickup');\"" : "";
				$onchangeplacesdrop = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTimeModule(this.value, 'dropoff');\"" : "";
				if ($diffopentime == true) {
					$document->addScriptDeclaration('var vrcmod_location_change=false;var vrcmod_wopening_pick='.json_encode($wopening_pick).';var vrcmod_wopening_drop='.json_encode($wopening_drop).';var vrcmod_hopening_pick=null;var vrcmod_hopening_drop=null;var vrcmod_mopening_pick=null;var vrcmod_mopening_drop=null;function vrcSetLocOpenTimeModule(loc,where){if(where=="dropoff"){vrcmod_location_change=true;}jQuery.ajax({type:"POST",url:"'.JRoute::_('index.php?option=com_vikrentcar&task=ajaxlocopentime&tmpl=component').'",data:{idloc:loc,pickdrop:where}}).done(function(res){var vrcobj=jQuery.parseJSON(res);if(where=="pickup"){jQuery("#vrcmodselph").html(vrcobj.hours);jQuery("#vrcmodselpm").html(vrcobj.minutes);if(vrcobj.hasOwnProperty("wopening")){vrcmod_wopening_pick=vrcobj.wopening;vrcmod_hopening_pick=vrcobj.hours;}}else{jQuery("#vrcmodseldh").html(vrcobj.hours);jQuery("#vrcmodseldm").html(vrcobj.minutes);if(vrcobj.hasOwnProperty("wopening")){vrcmod_wopening_drop=vrcobj.wopening;vrcmod_hopening_drop=vrcobj.hours;}}if(where=="pickup"&&vrcmod_location_change===false){jQuery("#modreturnplace").val(loc).trigger("change");vrcmod_location_change=false;}});}');
				}
				$vrloc .= "<div class=\"vrc-searchmod-section-pickup\">\n";
				$vrloc .= "<div class=\"vrcsfentrycont\"><label for=\"modplace\">".Text::_('VRMPPLACE')."</label><div class=\"vrcsfentryselect\"><select name=\"place\" id=\"modplace\"".$onchangeplaces.">";
				foreach ($places as $pla) { $vrloc .= "<option value=\"".$pla['id']."\"".(!empty($svrcplace) && $svrcplace == $pla['id'] ? " selected=\"selected\"" : "").">".$pla['name']."</option>\n"; }
				$vrloc .= "</select></div></div>\n";
				$vrloc .= "<div class=\"vrcsfentrycont\"><label for=\"modreturnplace\">".Text::_('VRMPLACERET')."</label><div class=\"vrcsfentryselect\"><select name=\"returnplace\" id=\"modreturnplace\"".(strlen($onchangeplacesdrop) > 0 ? $onchangeplacesdrop : "").">";
				foreach ($places as $pla) { $vrloc .= "<option value=\"".$pla['id']."\"".(!empty($svrcreturnplace) && $svrcreturnplace == $pla['id'] ? " selected=\"selected\"" : "").">".$pla['name']."</option>\n"; }
				$vrloc .= "</select></div></div>\n";
				$vrloc .= "</div>\n";
			}
		}
	}
} elseif (intval($params->get('showloc')) == 1) {
	$q = "SELECT * FROM `#__vikrentcar_places` ORDER BY `ordering` ASC, `name` ASC;";
	$dbo->setQuery($q); $dbo->execute();
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
				if (count($jsclosingdstr) > 0) { $declclosingdays .= 'var modloc'.$idpla.'closingdays = ['.implode(", ", $jsclosingdstr).'];'."\n"; }
			}
		}
		$onchangeplaces = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTimeModule(this.value, 'pickup');\"" : "";
		$onchangeplacesdrop = $diffopentime == true ? " onchange=\"javascript: vrcSetLocOpenTimeModule(this.value, 'dropoff');\"" : "";
		$vrloc .= "<div class=\"vrc-searchmod-section-pickup\">\n";
		$vrloc .= "<div class=\"vrcsfentrycont\"><label for=\"modplace\">".Text::_('VRMPPLACE')."</label><div class=\"vrcsfentryselect\"><select name=\"place\" id=\"modplace\"".$onchangeplaces.">";
		foreach ($places as $pla) { $vrloc .= "<option value=\"".$pla['id']."\"".(!empty($svrcplace) && $svrcplace == $pla['id'] ? " selected=\"selected\"" : "").">".$pla['name']."</option>\n"; }
		$vrloc .= "</select></div></div>\n";
		$vrloc .= "<div class=\"vrcsfentrycont\"><label for=\"modreturnplace\">".Text::_('VRMPLACERET')."</label><div class=\"vrcsfentryselect\"><select name=\"returnplace\" id=\"modreturnplace\"".(strlen($onchangeplacesdrop) > 0 ? $onchangeplacesdrop : "").">";
		foreach ($places as $pla) { $vrloc .= "<option value=\"".$pla['id']."\"".(!empty($svrcreturnplace) && $svrcreturnplace == $pla['id'] ? " selected=\"selected\"" : "").">".$pla['name']."</option>\n"; }
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
} else {
	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeopenstore';";
	$dbo->setQuery($q); $dbo->execute();
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
if (!empty($sval)) { $nowtf = $sval; } else {
	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeformat';";
	$dbo->setQuery($q); $dbo->execute();
	if ($dbo->getNumRows() > 0) { $tfget = $dbo->loadAssocList(); $nowtf = $tfget[0]['setting']; }
}
$pickhdeftime = !empty($places[$indvrcplace]['defaulttime']) ? ((int)$places[$indvrcplace]['defaulttime'] / 3600) : '';
$hours = "";
if (!($i < $j)) {
	while (intval($i) != (int)$j) {
		$sayi = $i < 10 ? "0".$i : $i;
		if ($nowtf != 'H:i') { $ampm=$i<12?' am':' pm'; $ampmh=$i>12?($i-12):$i; $sayh=$ampmh<10?"0".$ampmh.$ampm:$ampmh.$ampm; } else { $sayh=$sayi; }
		$hours .= "<option value=\"".(int)$i."\"".($pickhdeftime==(int)$i?' selected="selected"':'').">".$sayh."</option>\n";
		$i++; $i = $i > 23 ? 0 : $i;
	}
	$sayi=$i<10?"0".$i:$i;
	if($nowtf!='H:i'){$ampm=$i<12?' am':' pm';$ampmh=$i>12?($i-12):$i;$sayh=$ampmh<10?"0".$ampmh.$ampm:$ampmh.$ampm;}else{$sayh=$sayi;}
	$hours .= "<option value=\"".(int)$i."\">".$sayh."</option>\n";
} else {
	while ($i <= $j) {
		$sayi=$i<10?"0".$i:$i;
		if($nowtf!='H:i'){$ampm=$i<12?' am':' pm';$ampmh=$i>12?($i-12):$i;$sayh=$ampmh<10?"0".$ampmh.$ampm:$ampmh.$ampm;}else{$sayh=$sayi;}
		$hours .= "<option value=\"".(int)$i."\"".($pickhdeftime==(int)$i?' selected="selected"':'').">".$sayh."</option>\n";
		$i++;
	}
}
$drophdeftime = !empty($places[$indvrcreturnplace]['defaulttime']) ? ((int)$places[$indvrcreturnplace]['defaulttime'] / 3600) : '';
$hoursret = "";
if (!($iret < $jret)) {
	while (intval($iret) != (int)$jret) {
		$sayiret=$iret<10?"0".$iret:$iret;
		if($nowtf!='H:i'){$ampm=$iret<12?' am':' pm';$ampmh=$iret>12?($iret-12):$iret;$sayhret=$ampmh<10?"0".$ampmh.$ampm:$ampmh.$ampm;}else{$sayhret=$sayiret;}
		$hoursret .= "<option value=\"".(int)$iret."\"".($drophdeftime==(int)$iret?' selected="selected"':'').">".$sayhret."</option>\n";
		$iret++; $iret=$iret>23?0:$iret;
	}
	$sayiret=$iret<10?"0".$iret:$iret;
	if($nowtf!='H:i'){$ampm=$iret<12?' am':' pm';$ampmh=$iret>12?($iret-12):$iret;$sayhret=$ampmh<10?"0".$ampmh.$ampm:$ampmh.$ampm;}else{$sayhret=$sayiret;}
	$hoursret .= "<option value=\"".(int)$iret."\">".$sayhret."</option>\n";
} else {
	while ((int)$iret <= $jret) {
		$sayiret=$iret<10?"0".$iret:$iret;
		if($nowtf!='H:i'){$ampm=$iret<12?' am':' pm';$ampmh=$iret>12?($iret-12):$iret;$sayhret=$ampmh<10?"0".$ampmh.$ampm:$ampmh.$ampm;}else{$sayhret=$sayiret;}
		$hoursret .= "<option value=\"".(int)$iret."\"".($drophdeftime==(int)$iret?' selected="selected"':'').">".$sayhret."</option>\n";
		$iret++;
	}
}
$minutes = "";
for ($i = 0; $i < 60; $i += 15) {
	if ($i < 10) { $i = "0".$i; }
	$minutes .= "<option value=\"".(int)$i."\"".((int)$i==$imin?" selected=\"selected\"":"").">".$i."</option>\n";
}
$minutesret = "";
for ($iret = 0; $iret < 60; $iret += 15) {
	if ($iret < 10) { $iret = "0".$iret; }
	$minutesret .= "<option value=\"".(int)$iret."\"".((int)$iret==$iminret?" selected=\"selected\"":"").">".$iret."</option>\n";
}

$sval = $session->get('getDateFormat', '');
if (!empty($sval)) { $dateformat = $sval; } else {
	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
	$dbo->setQuery($q); $dbo->execute();
	if ($dbo->getNumRows() == 1) { $df=$dbo->loadAssocList(); $dateformat=$df[0]['setting']; } else { $dateformat="%d/%m/%Y"; }
}
if ($dateformat=="%d/%m/%Y") { $juidf='dd/mm/yy'; } elseif ($dateformat=="%m/%d/%Y") { $juidf='mm/dd/yy'; } else { $juidf='yy/mm/dd'; }

$document->addStyleSheet(JURI::root().'components/com_vikrentcar/resources/jquery-ui.min.css');
JHtml::_('script', JURI::root().'components/com_vikrentcar/resources/jquery-ui.min.js');

$ldecl = 'jQuery(function($){$.datepicker.regional["vikrentcarmod"]={closeText:"'.Text::_('VRCJQCALDONE').'",prevText:"'.Text::_('VRCJQCALPREV').'",nextText:"'.Text::_('VRCJQCALNEXT').'",currentText:"'.Text::_('VRCJQCALTODAY').'",monthNames:["'.Text::_('VRMONTHONE').'","'.Text::_('VRMONTHTWO').'","'.Text::_('VRMONTHTHREE').'","'.Text::_('VRMONTHFOUR').'","'.Text::_('VRMONTHFIVE').'","'.Text::_('VRMONTHSIX').'","'.Text::_('VRMONTHSEVEN').'","'.Text::_('VRMONTHEIGHT').'","'.Text::_('VRMONTHNINE').'","'.Text::_('VRMONTHTEN').'","'.Text::_('VRMONTHELEVEN').'","'.Text::_('VRMONTHTWELVE').'"],monthNamesShort:["'.mb_substr(Text::_('VRMONTHONE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHTWO'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHTHREE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHFOUR'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHFIVE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHSIX'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHSEVEN'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHEIGHT'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHNINE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHTEN'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHELEVEN'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRMONTHTWELVE'),0,3,'UTF-8').'"],dayNames:["'.Text::_('VRCJQCALSUN').'","'.Text::_('VRCJQCALMON').'","'.Text::_('VRCJQCALTUE').'","'.Text::_('VRCJQCALWED').'","'.Text::_('VRCJQCALTHU').'","'.Text::_('VRCJQCALFRI').'","'.Text::_('VRCJQCALSAT').'"],dayNamesShort:["'.mb_substr(Text::_('VRCJQCALSUN'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALMON'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALTUE'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALWED'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALTHU'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALFRI'),0,3,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALSAT'),0,3,'UTF-8').'"],dayNamesMin:["'.mb_substr(Text::_('VRCJQCALSUN'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALMON'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALTUE'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALWED'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALTHU'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALFRI'),0,2,'UTF-8').'","'.mb_substr(Text::_('VRCJQCALSAT'),0,2,'UTF-8').'"],weekHeader:"'.Text::_('VRCJQCALWKHEADER').'",dateFormat:"'.$juidf.'",firstDay:'.ModVikrentcarSearchHelper::getFirstWeekDay().',isRTL:false,showMonthAfterYear:false,yearSuffix:""};$.datepicker.setDefaults($.datepicker.regional["vikrentcarmod"]);});
function vrcGetDateObject'.$randid.'(s){var p=s.split("-");return new Date(p[0],(parseInt(p[1])-1),parseInt(p[2]),0,0,0,0);}
function vrcFullObject'.$randid.'(o){var k;for(k in o){return o.hasOwnProperty(k);}}
var vrcrestrctarange,vrcrestrctdrange,vrcrestrcta,vrcrestrctd;';
$document->addScriptDeclaration($ldecl);

$totrestrictions = count($restrictions);
$sdecl = "
function vrcCheckClosingDatesIn".$randid."(date){return [true];}
function vrcCheckClosingDatesOut".$randid."(date){return [true];}
function vrcValidateCta".$randid."(date){return true;}
function vrcValidateCtd".$randid."(date){return true;}
function vrcSetMinDropoffDate".$randid."(){
	var minlos=".(intval($def_min_los)>0?$def_min_los:'1').";
	var nowpickup=jQuery('#pickupdatemod".$randid."').datepicker('getDate');
	if(!nowpickup){return;}
	var nowpickupdate=new Date(nowpickup.getTime());
	nowpickupdate.setDate(nowpickupdate.getDate()+minlos);
	jQuery('#releasedatemod".$randid."').datepicker('option','minDate',nowpickupdate);
	jQuery('#releasedatemod".$randid."').datepicker('setDate',nowpickupdate);
}
function vrcLocationWopening".$randid."(mode){}
function vrcInitElems".$randid."(){}
jQuery(function(){
	vrcInitElems".$randid."();
	jQuery.datepicker.setDefaults(jQuery.datepicker.regional['']);
	jQuery('#pickupdatemod".$randid."').datepicker({showOn:'focus',beforeShowDay:vrcCheckClosingDatesIn".$randid.",onSelect:function(selectedDate){vrcSetMinDropoffDate".$randid."();}});
	jQuery('#pickupdatemod".$randid."').datepicker('option','dateFormat','".$juidf."');
	jQuery('#pickupdatemod".$randid."').datepicker('option','minDate','".ModVikrentcarSearchHelper::getMinDaysAdvance()."d');
	jQuery('#pickupdatemod".$randid."').datepicker('option','maxDate','".ModVikrentcarSearchHelper::getMaxDateFuture()."');
	jQuery('#releasedatemod".$randid."').datepicker({showOn:'focus',beforeShowDay:vrcCheckClosingDatesOut".$randid.",onSelect:function(){}});
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
if (intval($params->get('showcat')) == 1) {
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
?>

<div class="<?php echo $params->get('moduleclass_sfx'); ?> ar-split-layout">

	<!-- ── Left: search form ── -->
	<div class="ar-split-form">
		<p class="ar-split-form-title">
			<i class="fa fa-search" style="margin-right:6px;color:#FE5001;"></i>
			<?php
			$langSrch = array('ro-RO'=>'Caută Mașina Ta','ru-RU'=>'Найти Автомобиль','en-GB'=>'Find Your Car');
			echo isset($langSrch[$langTag]) ? $langSrch[$langTag] : $langSrch['ro-RO'];
			?>
		</p>
		<div class="vrcdivsearch vrcdivsearchmodule <?php echo $params->get('orientation')=='horizontal' ? 'vrc-searchmod-wrap-horizontal' : 'vrc-searchmod-wrap-vertical'; ?>">
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
	</div><!-- /ar-split-form -->

	<!-- ── Right: hero content ── -->
	<div class="ar-split-hero">
		<h1 class="ar-split-title"><?php echo $split['headline']; ?></h1>
		<p class="ar-split-subtitle"><?php echo $split['subtitle']; ?></p>

		<div class="ar-split-ctas">
			<a href="<?php echo $carslistUrl; ?>" class="ar-btn ar-btn-primary">
				<i class="fa fa-car" style="margin-right:8px;"></i><?php echo $split['cta1']; ?>
			</a>
			<a href="tel:+37368001155" class="ar-btn ar-btn-outline">
				<i class="fa fa-phone" style="margin-right:8px;"></i><?php echo $split['cta2']; ?>
			</a>
		</div>

		<div class="ar-split-badges">
			<div class="ar-split-badge">
				<i class="fa fa-car"></i>
				<span><?php echo $split['badge1']; ?></span>
			</div>
			<div class="ar-split-badge">
				<i class="fa fa-tag"></i>
				<span><?php echo $split['badge2']; ?></span>
			</div>
		</div>

		<div class="ar-split-clients">
			<div class="ar-split-avatars">
				<div class="ar-split-avatar"><i class="fa fa-user"></i></div>
				<div class="ar-split-avatar"><i class="fa fa-user"></i></div>
				<div class="ar-split-avatar"><i class="fa fa-user"></i></div>
			</div>
			<span class="ar-split-clients-label"><?php echo $split['clients']; ?></span>
		</div>
	</div><!-- /ar-split-hero -->

</div><!-- /ar-split-layout -->

<?php
$sespickupts = $session->get('vrcpickupts', '');
$sesdropoffts = $session->get('vrcreturnts', '');
$ptask = $input->getString('task', '');
if ($ptask == 'search' && !empty($sespickupts) && !empty($sesdropoffts)) {
	if ($dateformat=="%d/%m/%Y"){$jsdf='d/m/Y';}elseif($dateformat=="%m/%d/%Y"){$jsdf='m/d/Y';}else{$jsdf='Y/m/d';}
	$sespickuph=date('H',$sespickupts); $sespickupm=date('i',$sespickupts);
	$sesdropoffh=date('H',$sesdropoffts); $sesdropoffm=date('i',$sesdropoffts);
	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		document.getElementById('pickupdatemod<?php echo $randid; ?>').value = '<?php echo date($jsdf,$sespickupts); ?>';
		document.getElementById('releasedatemod<?php echo $randid; ?>').value = '<?php echo date($jsdf,$sesdropoffts); ?>';
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
	if (snum.length > 1 && snum.substr(0,1) == '0') { return parseInt(snum.substr(1)); }
	return parseInt(snum);
}
function vrcValidateSearch<?php echo $randid; ?>() { return true; }
</script>
