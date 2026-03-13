<?php  
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_search
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2017 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
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
?>

<div class="<?php echo $params->get('moduleclass_sfx'); ?>">
<div class="vrcdivsearch vrcdivsearchmodule <?php echo $params->get('orientation') == 'horizontal' ? 'vrc-searchmod-wrap-horizontal' : 'vrc-searchmod-wrap-vertical'; ?>">
	<div class="vrc-searchmod-heading"><?php echo $vrtext; ?></div>
	<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'); ?>" method="get" onsubmit="return vrcValidateSearch<?php echo $randid; ?>();">
		<input type="hidden" name="option" value="com_vikrentcar"/>
		<input type="hidden" name="task" value="search"/>
		<input type="hidden" name="Itemid" value="<?php echo $params->get('itemid'); ?>"/>
    <?php
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
					//check if some place has a different opening time (1.6)
					foreach ($places as $kpla => $pla) {
						if (!empty($pla['opentime'])) {
							$diffopentime = true;
						}
						//check if some place has closing days
						if (!empty($pla['closingdays'])) {
							$closingdays[$pla['id']] = $pla['closingdays'];
						}
						if (!empty($svrcplace) && !empty($svrcreturnplace)) {
							if ($pla['id'] == $svrcplace) {
								$indvrcplace = $kpla;
							}
							if ($pla['id'] == $svrcreturnplace) {
								$indvrcreturnplace = $kpla;
							}
						}
					}
					// VRC 1.12 - location override opening time on some weekdays
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
					//
					//locations closing days (1.7)
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
	if (where == "dropoff") {
		vrcmod_location_change = true;
	}
	jQuery.ajax({
		type: "POST",
		url: "'.JRoute::_('index.php?option=com_vikrentcar&task=ajaxlocopentime&tmpl=component').'",
		data: { idloc: loc, pickdrop: where }
	}).done(function(res) {
		var vrcobj = jQuery.parseJSON(res);
		if (where == "pickup") {
			jQuery("#vrcmodselph").html(vrcobj.hours);
			jQuery("#vrcmodselpm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) {
				vrcmod_wopening_pick = vrcobj.wopening;
				vrcmod_hopening_pick = vrcobj.hours;
			}
		} else {
			jQuery("#vrcmodseldh").html(vrcobj.hours);
			jQuery("#vrcmodseldm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) {
				vrcmod_wopening_drop = vrcobj.wopening;
				vrcmod_hopening_drop = vrcobj.hours;
			}
		}
		if (where == "pickup" && vrcmod_location_change === false) {
			jQuery("#modreturnplace").val(loc).trigger("change");
			vrcmod_location_change = false;
		}
	});
}';
						$document->addScriptDeclaration($onchangedecl);
					}
					//end check if some place has a different openingtime (1.6)

					// start the DIV container for the locations part
					$vrloc .= "<div class=\"vrc-searchmod-section-pickup\">\n";
					//
					
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

					// close the DIV container for the locations part
					$vrloc .= "</div>\n";
					//
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
			//check if some place has a different opening time (1.6)
			foreach ($places as $kpla => $pla) {
				if (!empty($pla['opentime'])) {
					$diffopentime = true;
				}
				//check if some place has closing days
				if (!empty($pla['closingdays'])) {
					$closingdays[$pla['id']] = $pla['closingdays'];
				}
				if (!empty($svrcplace) && !empty($svrcreturnplace)) {
					if ($pla['id'] == $svrcplace) {
						$indvrcplace = $kpla;
					}
					if ($pla['id'] == $svrcreturnplace) {
						$indvrcreturnplace = $kpla;
					}
				}
			}
			// VRC 1.12 - location override opening time on some weekdays
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
			//
			//locations closing days (1.7)
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
	if (where == "dropoff") {
		vrcmod_location_change = true;
	}
	jQuery.ajax({
		type: "POST",
		url: "'.JRoute::_('index.php?option=com_vikrentcar&task=ajaxlocopentime&tmpl=component').'",
		data: { idloc: loc, pickdrop: where }
	}).done(function(res) {
		var vrcobj = jQuery.parseJSON(res);
		if (where == "pickup") {
			jQuery("#vrcmodselph").html(vrcobj.hours);
			jQuery("#vrcmodselpm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) {
				vrcmod_wopening_pick = vrcobj.wopening;
				vrcmod_hopening_pick = vrcobj.hours;
			}
		} else {
			jQuery("#vrcmodseldh").html(vrcobj.hours);
			jQuery("#vrcmodseldm").html(vrcobj.minutes);
			if (vrcobj.hasOwnProperty("wopening")) {
				vrcmod_wopening_drop = vrcobj.wopening;
				vrcmod_hopening_drop = vrcobj.hours;
			}
		}
		if (where == "pickup" && vrcmod_location_change === false) {
			jQuery("#modreturnplace").val(loc).trigger("change");
			vrcmod_location_change = false;
		}
	});
}';
				$document->addScriptDeclaration($onchangedecl);
			}
			//end check if some place has a different opningtime (1.6)

			// start the DIV container for the locations part
			$vrloc .= "<div class=\"vrc-searchmod-section-pickup\">\n";
			//
			
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

			// close the DIV container for the locations part
			$vrloc .= "</div>\n";
			//
		}
    }
    echo $vrloc;
    
	$i = 0;
	$imin = 0;
	$j = 23;
	
	if ($diffopentime == true && is_array($places) && strlen($places[$indvrcplace]['opentime']) > 0) {
		$parts = explode("-", $places[$indvrcplace]['opentime']);
		if (is_array($parts) && $parts[0] != $parts[1]) {
			$opent = ModVikrentcarSearchHelper::mgetHoursMinutes($parts[0]);
			$closet = ModVikrentcarSearchHelper::mgetHoursMinutes($parts[1]);
			$i = $opent[0];
			$imin = $opent[1];
			$j = $closet[0];
		} else {
			$i = 0;
			$imin = 0;
			$j = 23;
		}
		//change dates drop off location opening time (1.6)
		$iret = $i;
		$iminret = $imin;
		$jret = $j;
		if ($indvrcplace != $indvrcreturnplace) {
			if (strlen($places[$indvrcreturnplace]['opentime']) > 0) {
				//different opening time for drop off location
				$parts = explode("-", $places[$indvrcreturnplace]['opentime']);
				if (is_array($parts) && $parts[0] != $parts[1]) {
					$opent = ModVikrentcarSearchHelper::mgetHoursMinutes($parts[0]);
					$closet = ModVikrentcarSearchHelper::mgetHoursMinutes($parts[1]);
					$iret = $opent[0];
					$iminret = $opent[1];
					$jret = $closet[0];
				} else {
					$iret = 0;
					$iminret = 0;
					$jret = 23;
				}
			} else {
				//global opening time
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeopenstore';";
				$dbo->setQuery($q);
				$dbo->execute();
				$timeopst = $dbo->loadResult();
				$timeopst = explode("-", $timeopst);
				if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
					$opent = ModVikrentcarSearchHelper::mgetHoursMinutes($timeopst[0]);
					$closet = ModVikrentcarSearchHelper::mgetHoursMinutes($timeopst[1]);
					$iret = $opent[0];
					$iminret = $opent[1];
					$jret = $closet[0];
				} else {
					$iret = 0;
					$iminret = 0;
					$jret = 23;
				}
			}
		}
		//
	} else {
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeopenstore';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
    		$n=$dbo->loadAssocList();
    		if (!empty($n[0]['setting'])) {
    			$timeopst=explode("-", $n[0]['setting']);
    			if (is_array($timeopst) && $timeopst[0]!=$timeopst[1]) {
    				if ($timeopst[0] >= 3600) {
						$op = $timeopst[0] / 3600;
						$hoursop = floor($op);
					} else {
						$hoursop = "0";
					}
    				$i = $hoursop;
    				$opent = ModVikrentcarSearchHelper::mgetHoursMinutes($timeopst[0]);
    				$imin = $opent[1];
    				if ($timeopst[1] >= 3600) {
						$op = $timeopst[1] / 3600;
						$hourscl = floor($op);
					} else {
						$hourscl = "0";
					}
    				$j = $hourscl;
    			}
    		}
    	}
		$iret = $i;
		$iminret = $imin;
		$jret = $j;
	}
    
	$hours = "";
	//VRC 1.10
	$nowtf = 'H:i';
	$sval = $session->get('getTimeFormat', '');
	if (!empty($sval)) {
		$nowtf=$sval;
	} else {
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='timeformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$tfget = $dbo->loadAssocList();
			$nowtf = $tfget[0]['setting'];
		}
	}
	$pickhdeftime = !empty($places[$indvrcplace]['defaulttime']) ? ((int)$places[$indvrcplace]['defaulttime'] / 3600) : '';
	if (!($i < $j)) {
		while (intval($i) != (int)$j) {
			$sayi = $i < 10 ? "0".$i : $i;
			if ($nowtf != 'H:i') {
				$ampm = $i < 12 ? ' am' : ' pm';
				$ampmh = $i > 12 ? ($i - 12) : $i;
				$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
			} else {
				$sayh = $sayi;
			}
			$hours .= "<option value=\"" . (int)$i . "\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">" . $sayh . "</option>\n";
			$i++;
			$i = $i > 23 ? 0 : $i;
		}
		$sayi = $i < 10 ? "0".$i : $i;
		if ($nowtf != 'H:i') {
			$ampm = $i < 12 ? ' am' : ' pm';
			$ampmh = $i > 12 ? ($i - 12) : $i;
			$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
		} else {
			$sayh = $sayi;
		}
		$hours .= "<option value=\"" . (int)$i . "\">" . $sayh . "</option>\n";
	} else {
		while ($i <= $j) {
			$sayi = $i < 10 ? "0".$i : $i;
			if ($nowtf != 'H:i') {
				$ampm = $i < 12 ? ' am' : ' pm';
				$ampmh = $i > 12 ? ($i - 12) : $i;
				$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
			} else {
				$sayh = $sayi;
			}
			$hours .= "<option value=\"" . (int)$i . "\"".($pickhdeftime == (int)$i ? ' selected="selected"' : '').">" . $sayh . "</option>\n";
			$i++;
		}
	}
	//
	$hoursret = "";
	//VRC 1.9
	$drophdeftime = !empty($places[$indvrcreturnplace]['defaulttime']) ? ((int)$places[$indvrcreturnplace]['defaulttime'] / 3600) : '';
	if (!($iret < $jret)) {
		while (intval($iret) != (int)$jret) {
			$sayiret = $iret < 10 ? "0".$iret : $iret;
			if ($nowtf != 'H:i') {
				$ampm = $iret < 12 ? ' am' : ' pm';
				$ampmh = $iret > 12 ? ($iret - 12) : $iret;
				$sayhret = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
			} else {
				$sayhret = $sayiret;
			}
			$hoursret .= "<option value=\"" . (int)$iret . "\"".($drophdeftime == (int)$iret ? ' selected="selected"' : '').">" . $sayhret . "</option>\n";
			$iret++;
			$iret = $iret > 23 ? 0 : $iret;
		}
		$sayiret = $iret < 10 ? "0".$iret : $iret;
		if ($nowtf != 'H:i') {
			$ampm = $iret < 12 ? ' am' : ' pm';
			$ampmh = $iret > 12 ? ($iret - 12) : $iret;
			$sayhret = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
		} else {
			$sayhret = $sayiret;
		}
		$hoursret .= "<option value=\"" . (int)$iret . "\">" . $sayhret . "</option>\n";
	} else {
		while ((int)$iret <= $jret) {
			$sayiret = $iret < 10 ? "0".$iret : $iret;
			if ($nowtf != 'H:i') {
				$ampm = $iret < 12 ? ' am' : ' pm';
				$ampmh = $iret > 12 ? ($iret - 12) : $iret;
				$sayhret = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
			} else {
				$sayhret = $sayiret;
			}
			$hoursret .= "<option value=\"" . (int)$iret . "\"".($drophdeftime == (int)$iret ? ' selected="selected"' : '').">" . $sayhret . "</option>\n";
			$iret++;
		}
	}
	//
	$minutes = "";
	for ($i = 0; $i < 60; $i += 15) {
		if ($i < 10) {
			$i = "0" . $i;
		}
		$minutes .= "<option value=\"" . (int)$i . "\"".((int)$i == $imin ? " selected=\"selected\"" : "").">" . $i . "</option>\n";
	}
	$minutesret = "";
	for ($iret = 0; $iret < 60; $iret += 15) {
		if ($iret < 10) {
			$iret = "0" . $iret;
		}
		$minutesret .= "<option value=\"" . (int)$iret . "\"".((int)$iret == $iminret ? " selected=\"selected\"" : "").">" . $iret . "</option>\n";
	}
	
	$sval = $session->get('getDateFormat', '');
	if (!empty($sval)) {
		$dateformat = $sval;
	} else {
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$df = $dbo->loadAssocList();
			$dateformat = $df[0]['setting'];
		} else{
			$dateformat = "%d/%m/%Y";
		}
	}
	
	if (true) {
		if ($dateformat == "%d/%m/%Y") {
			$juidf = 'dd/mm/yy';
		} elseif ($dateformat == "%m/%d/%Y") {
			$juidf = 'mm/dd/yy';
		} else {
			$juidf = 'yy/mm/dd';
		}
		$document->addStyleSheet(JURI::root().'components/com_vikrentcar/resources/jquery-ui.min.css');
		//load jQuery UI
		JHtml::_('script', JURI::root().'components/com_vikrentcar/resources/jquery-ui.min.js');
		//
		//lang for jQuery UI Calendar
		$ldecl = '
jQuery(function($) {'."\n".'
	$.datepicker.regional["vikrentcarmod"] = {'."\n".'
		closeText: "'.Text::_('VRCJQCALDONE').'",'."\n".'
		prevText: "'.Text::_('VRCJQCALPREV').'",'."\n".'
		nextText: "'.Text::_('VRCJQCALNEXT').'",'."\n".'
		currentText: "'.Text::_('VRCJQCALTODAY').'",'."\n".'
		monthNames: ["'.Text::_('VRMONTHONE').'","'.Text::_('VRMONTHTWO').'","'.Text::_('VRMONTHTHREE').'","'.Text::_('VRMONTHFOUR').'","'.Text::_('VRMONTHFIVE').'","'.Text::_('VRMONTHSIX').'","'.Text::_('VRMONTHSEVEN').'","'.Text::_('VRMONTHEIGHT').'","'.Text::_('VRMONTHNINE').'","'.Text::_('VRMONTHTEN').'","'.Text::_('VRMONTHELEVEN').'","'.Text::_('VRMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(Text::_('VRMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(Text::_('VRMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.Text::_('VRCJQCALSUN').'", "'.Text::_('VRCJQCALMON').'", "'.Text::_('VRCJQCALTUE').'", "'.Text::_('VRCJQCALWED').'", "'.Text::_('VRCJQCALTHU').'", "'.Text::_('VRCJQCALFRI').'", "'.Text::_('VRCJQCALSAT').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(Text::_('VRCJQCALSUN'), 0, 3, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALMON'), 0, 3, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALTUE'), 0, 3, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALWED'), 0, 3, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALTHU'), 0, 3, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALFRI'), 0, 3, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALSAT'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(Text::_('VRCJQCALSUN'), 0, 2, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALMON'), 0, 2, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALTUE'), 0, 2, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALWED'), 0, 2, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALTHU'), 0, 2, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALFRI'), 0, 2, 'UTF-8').'", "'.mb_substr(Text::_('VRCJQCALSAT'), 0, 2, 'UTF-8').'"],'."\n".'
		weekHeader: "'.Text::_('VRCJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.ModVikrentcarSearchHelper::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikrentcarmod"]);'."\n".'
});
function vrcGetDateObject'.$randid.'(dstring) {
	var dparts = dstring.split("-");
	return new Date(dparts[0], (parseInt(dparts[1]) - 1), parseInt(dparts[2]), 0, 0, 0, 0);
}
function vrcFullObject'.$randid.'(obj) {
	var jk;
	for(jk in obj) {
		return obj.hasOwnProperty(jk);
	}
}
var vrcrestrctarange, vrcrestrctdrange, vrcrestrcta, vrcrestrctd;';
		$document->addScriptDeclaration($ldecl);
		//
		// VRC 1.12 - Restrictions Start
		$totrestrictions = count($restrictions);
		$wdaysrestrictions = array();
		$wdaystworestrictions = array();
		$wdaysrestrictionsrange = array();
		$wdaysrestrictionsmonths = array();
		$ctarestrictionsrange = array();
		$ctarestrictionsmonths = array();
		$ctdrestrictionsrange = array();
		$ctdrestrictionsmonths = array();
		$monthscomborestr = array();
		$minlosrestrictions = array();
		$minlosrestrictionsrange = array();
		$maxlosrestrictions = array();
		$maxlosrestrictionsrange = array();
		$notmultiplyminlosrestrictions = array();
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
						if (!empty($restr['ctad'])) {
							$ctarestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctad']);
						}
						if (!empty($restr['ctdd'])) {
							$ctdrestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctdd']);
						}
					}
					if ($restr['multiplyminlos'] == 0) {
						$notmultiplyminlosrestrictions[] = $rmonth;
					}
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
							$ctfrom = date('Y-m-d', $drestr['dfrom']);
							$ctto = date('Y-m-d', $drestr['dto']);
							if(!empty($drestr['ctad'])) {
								$ctarestrictionsrange[$kr][0] = $ctfrom;
								$ctarestrictionsrange[$kr][1] = $ctto;
								$ctarestrictionsrange[$kr][2] = explode(',', $drestr['ctad']);
							}
							if(!empty($drestr['ctdd'])) {
								$ctdrestrictionsrange[$kr][0] = $ctfrom;
								$ctdrestrictionsrange[$kr][1] = $ctto;
								$ctdrestrictionsrange[$kr][2] = explode(',', $drestr['ctdd']);
							}
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
		for(var vrci in vrccombowdays) {
			if(vrccombowdays.hasOwnProperty(vrci)) {
				var vrcusedate = darrive;
				vrctosort[vrci] = vrcusedate.setDate(vrcusedate.getDate() + (vrccombowdays[vrci] - 1 - vrcusedate.getDay() + 7) % 7 + 1);
			}
		}
		vrctosort.sort(function(da, db) {
			return da > db ? 1 : -1;
		});
		for(var vrcnext in vrctosort) {
			if(vrctosort.hasOwnProperty(vrcnext)) {
				var vrcfirstnextd = new Date(vrctosort[vrcnext]);
				jQuery('#releasedatemod".$randid."').datepicker( 'option', 'minDate', vrcfirstnextd );
				jQuery('#releasedatemod".$randid."').datepicker( 'setDate', vrcfirstnextd );
				break;
			}
		}
	}
}
var vrcDropMaxDateSet".$randid." = false;
function vrcSetMinDropoffDate".$randid." () {
	var vrcDropMaxDateSetNow".$randid." = false;
	var minlos = ".(intval($def_min_los) > 0 ? $def_min_los : '0').";
	var maxlosrange = 0;
	var nowpickup = jQuery('#pickupdatemod".$randid."').datepicker('getDate');
	var nowd = nowpickup.getDay();
	var nowpickupdate = new Date(nowpickup.getTime());
	vrccombowdays = {};
	if(vrcFullObject".$randid."(vrcrestrminlosrangejn)) {
		for (var rk in vrcrestrminlosrangejn) {
			if(vrcrestrminlosrangejn.hasOwnProperty(rk)) {
				var minldrangeinit = vrcGetDateObject".$randid."(vrcrestrminlosrangejn[rk][0]);
				if(nowpickupdate >= minldrangeinit) {
					var minldrangeend = vrcGetDateObject".$randid."(vrcrestrminlosrangejn[rk][1]);
					if(nowpickupdate <= minldrangeend) {
						minlos = parseInt(vrcrestrminlosrangejn[rk][2]);
						if(vrcFullObject".$randid."(vrcrestrmaxlosrangejn)) {
							if(rk in vrcrestrmaxlosrangejn) {
								maxlosrange = parseInt(vrcrestrmaxlosrangejn[rk]);
							}
						}
						if(rk in vrcrestrwdaysrangejn && nowd in vrcrestrwdaysrangejn[rk][5]) {
							vrccombowdays = vrcrestrwdaysrangejn[rk][5][nowd];
						}
					}
				}
			}
		}
	}
	var nowm = nowpickup.getMonth();
	if(vrcFullObject".$randid."(vrcrestrmonthscombojn) && vrcrestrmonthscombojn.hasOwnProperty(nowm)) {
		if(nowd in vrcrestrmonthscombojn[nowm]) {
			vrccombowdays = vrcrestrmonthscombojn[nowm][nowd];
		}
	}
	if(jQuery.inArray((nowm + 1), vrcrestrmonths) != -1) {
		minlos = parseInt(vrcrestrminlos[nowm]);
	}
	nowpickupdate.setDate(nowpickupdate.getDate() + minlos);
	jQuery('#releasedatemod".$randid."').datepicker( 'option', 'minDate', nowpickupdate );
	if(maxlosrange > 0) {
		var diffmaxminlos = maxlosrange - minlos;
		var maxdropoffdate = new Date(nowpickupdate.getTime());
		maxdropoffdate.setDate(maxdropoffdate.getDate() + diffmaxminlos);
		jQuery('#releasedatemod".$randid."').datepicker( 'option', 'maxDate', maxdropoffdate );
		vrcDropMaxDateSet".$randid." = true;
		vrcDropMaxDateSetNow".$randid." = true;
	}
	if(nowm in vrcrestrmaxlos) {
		var diffmaxminlos = parseInt(vrcrestrmaxlos[nowm]) - minlos;
		var maxdropoffdate = new Date(nowpickupdate.getTime());
		maxdropoffdate.setDate(maxdropoffdate.getDate() + diffmaxminlos);
		jQuery('#releasedatemod".$randid."').datepicker( 'option', 'maxDate', maxdropoffdate );
		vrcDropMaxDateSet".$randid." = true;
		vrcDropMaxDateSetNow".$randid." = true;
	}
	if(!vrcFullObject".$randid."(vrccombowdays)) {
		jQuery('#releasedatemod".$randid."').datepicker( 'setDate', nowpickupdate );
		if (!vrcDropMaxDateSetNow".$randid." && vrcDropMaxDateSet".$randid." === true) {
			// unset maxDate previously set
			jQuery('#releasedatemod".$randid."').datepicker( 'option', 'maxDate', null );
		}
	} else {
		vrcRefreshDropoff".$randid."(nowpickup);
	}
}";
			
			if (count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0) {
				$resdecl .= "
var vrcrestrwdays = {".implode(", ", $wdaysrestrictions)."};
var vrcrestrwdaystwo = {".implode(", ", $wdaystworestrictions)."};
function vrcIsDayDisabled".$randid."(date) {
	if(!vrcValidateCta".$randid."(date)) {
		return [false];
	}
	".(strlen($declclosingdays) > 0 ? "var loc_closing = modpickupClosingDays".$randid."(date); if (!loc_closing[0]) {return loc_closing;}" : "")."
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject".$randid."(vrcrestrwdaysrangejn)) {
		for (var rk in vrcrestrwdaysrangejn) {
			if(vrcrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vrcGetDateObject".$randid."(vrcrestrwdaysrangejn[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vrcGetDateObject".$randid."(vrcrestrwdaysrangejn[rk][1]);
					if(date <= wdrangeend) {
						if(wd != vrcrestrwdaysrangejn[rk][2]) {
							if(vrcrestrwdaysrangejn[rk][4] == -1 || wd != vrcrestrwdaysrangejn[rk][4]) {
								return [false];
							}
						}
					}
				}
			}
		}
	}
	if(vrcFullObject".$randid."(vrcrestrwdays)) {
		if(jQuery.inArray((m+1), vrcrestrmonthswdays) == -1) {
			return [true];
		}
		if(wd == vrcrestrwdays[m]) {
			return [true];
		}
		if(vrcFullObject".$randid."(vrcrestrwdaystwo)) {
			if(wd == vrcrestrwdaystwo[m]) {
				return [true];
			}
		}
		return [false];
	}
	return [true];
}
function vrcIsDayDisabledDropoff".$randid."(date) {
	if(!vrcValidateCtd".$randid."(date)) {
		return [false];
	}
	".(strlen($declclosingdays) > 0 ? "var loc_closing = moddropoffClosingDays".$randid."(date); if (!loc_closing[0]) {return loc_closing;}" : "")."
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject".$randid."(vrccombowdays)) {
		if(jQuery.inArray(wd, vrccombowdays) != -1) {
			return [true];
		} else {
			return [false];
		}
	}
	if(vrcFullObject".$randid."(vrcrestrwdaysrangejn)) {
		for (var rk in vrcrestrwdaysrangejn) {
			if(vrcrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vrcGetDateObject".$randid."(vrcrestrwdaysrangejn[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vrcGetDateObject".$randid."(vrcrestrwdaysrangejn[rk][1]);
					if(date <= wdrangeend) {
						if(wd != vrcrestrwdaysrangejn[rk][2] && vrcrestrwdaysrangejn[rk][3] == 1) {
							return [false];
						}
					}
				}
			}
		}
	}
	if(vrcFullObject".$randid."(vrcrestrwdays)) {
		if(jQuery.inArray((m+1), vrcrestrmonthswdays) == -1 || jQuery.inArray((m+1), vrcrestrmultiplyminlos) != -1) {
			return [true];
		}
		if(wd == vrcrestrwdays[m]) {
			return [true];
		}
		return [false];
	}
	return [true];
}";
			}
			$document->addScriptDeclaration($resdecl);
		}
		// VRC 1.12 - Restrictions End
		//locations closing days (1.7)
		if (strlen($declclosingdays) > 0) {
			$declclosingdays .= '
function modpickupClosingDays'.$randid.'(date) {
	var dmy = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
	var wday = date.getDay().toString();
	var arrlocclosd = jQuery("#modplace").val();
	var checklocarr = window["modloc"+arrlocclosd+"closingdays"];
	if (jQuery.inArray(dmy, checklocarr) == -1 && jQuery.inArray(wday, checklocarr) == -1) {
		return [true, ""];
	} else {
		return [false, "", "'.addslashes(Text::_('VRCMLOCDAYCLOSED')).'"];
	}
}
function moddropoffClosingDays'.$randid.'(date) {
	var dmy = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
	var wday = date.getDay().toString();
	var arrlocclosd = jQuery("#modreturnplace").val();
	var checklocarr = window["modloc"+arrlocclosd+"closingdays"];
	if (jQuery.inArray(dmy, checklocarr) == -1 && jQuery.inArray(wday, checklocarr) == -1) {
		return [true, ""];
	} else {
		return [false, "", "'.addslashes(Text::_('VRCMLOCDAYCLOSED')).'"];
	}
}';
			$document->addScriptDeclaration($declclosingdays);
		}
		//
		//Minimum Num of Days of Rental (VRC 1.8)
		$dropdayplus = $def_min_los;
		$forcedropday = "jQuery('#releasedatemod".$randid."').datepicker( 'option', 'minDate', selectedDate );";
		if (strlen($dropdayplus) > 0 && intval($dropdayplus) > 0) {
			$forcedropday = "
var nowpick = jQuery(this).datepicker('getDate');
if (nowpick) {
	var nowpickdate = new Date(nowpick.getTime());
	nowpickdate.setDate(nowpickdate.getDate() + ".$dropdayplus.");
	jQuery('#releasedatemod".$randid."').datepicker( 'option', 'minDate', nowpickdate );
	jQuery('#releasedatemod".$randid."').datepicker( 'setDate', nowpickdate );
}";
		}
		//
		$sdecl = "
function vrcCheckClosingDatesIn".$randid."(date) {
	if(!vrcValidateCta".$randid."(date)) {
		return [false];
	}
	".(strlen($declclosingdays) > 0 ? "var loc_closing = modpickupClosingDays".$randid."(date); if (!loc_closing[0]) {return loc_closing;}" : "")."
	return [true];
}
function vrcCheckClosingDatesOut".$randid."(date) {
	if(!vrcValidateCtd".$randid."(date)) {
		return [false];
	}
	".(strlen($declclosingdays) > 0 ? "var loc_closing = moddropoffClosingDays".$randid."(date); if (!loc_closing[0]) {return loc_closing;}" : "")."
	return [true];
}
function vrcValidateCta".$randid."(date) {
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject".$randid."(vrcrestrctarange)) {
		for (var rk in vrcrestrctarange) {
			if(vrcrestrctarange.hasOwnProperty(rk)) {
				var wdrangeinit = vrcGetDateObject".$randid."(vrcrestrctarange[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vrcGetDateObject".$randid."(vrcrestrctarange[rk][1]);
					if(date <= wdrangeend) {
						if(jQuery.inArray('-'+wd+'-', vrcrestrctarange[rk][2]) >= 0) {
							return false;
						}
					}
				}
			}
		}
	}
	if(vrcFullObject".$randid."(vrcrestrcta)) {
		if(vrcrestrcta.hasOwnProperty(m) && jQuery.inArray('-'+wd+'-', vrcrestrcta[m]) >= 0) {
			return false;
		}
	}
	return true;
}
function vrcValidateCtd".$randid."(date) {
	var m = date.getMonth(), wd = date.getDay();
	if(vrcFullObject".$randid."(vrcrestrctdrange)) {
		for (var rk in vrcrestrctdrange) {
			if(vrcrestrctdrange.hasOwnProperty(rk)) {
				var wdrangeinit = vrcGetDateObject".$randid."(vrcrestrctdrange[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vrcGetDateObject".$randid."(vrcrestrctdrange[rk][1]);
					if(date <= wdrangeend) {
						if(jQuery.inArray('-'+wd+'-', vrcrestrctdrange[rk][2]) >= 0) {
							return false;
						}
					}
				}
			}
		}
	}
	if(vrcFullObject".$randid."(vrcrestrctd)) {
		if(vrcrestrctd.hasOwnProperty(m) && jQuery.inArray('-'+wd+'-', vrcrestrctd[m]) >= 0) {
			return false;
		}
	}
	return true;
}
function vrcLocationWopening".$randid."(mode) {
	if (typeof vrcmod_wopening_pick === 'undefined') {
		return true;
	}
	if (mode == 'pickup') {
		vrcmod_mopening_pick = null;
	} else {
		vrcmod_mopening_drop = null;
	}
	var loc_data = mode == 'pickup' ? vrcmod_wopening_pick : vrcmod_wopening_drop;
	var def_loc_hours = mode == 'pickup' ? vrcmod_hopening_pick : vrcmod_hopening_drop;
	var sel_d = jQuery((mode == 'pickup' ? '#pickupdatemod".$randid."' : '#releasedatemod".$randid."')).datepicker('getDate');
	if (!sel_d) {
		return true;
	}
	var sel_wday = sel_d.getDay();
	if (!vrcFullObject".$randid."(loc_data) || !loc_data.hasOwnProperty(sel_wday) || !loc_data[sel_wday].hasOwnProperty('fh')) {
		if (def_loc_hours !== null) {
			// populate the default opening time dropdown
			jQuery((mode == 'pickup' ? '#vrcmodselph' : '#vrcmodseldh')).html(def_loc_hours);
		}
		return true;
	}
	if (mode == 'pickup') {
		vrcmod_mopening_pick = new Array(loc_data[sel_wday]['fh'], loc_data[sel_wday]['fm']);
	} else {
		vrcmod_mopening_drop = new Array(loc_data[sel_wday]['th'], loc_data[sel_wday]['tm']);
	}
	var hlim = loc_data[sel_wday]['fh'] < loc_data[sel_wday]['th'] ? loc_data[sel_wday]['th'] : (24 + loc_data[sel_wday]['th']);
	hlim = loc_data[sel_wday]['fh'] == 0 && loc_data[sel_wday]['th'] == 0 ? 23 : hlim;
	var hopts = '';
	var def_hour = jQuery((mode == 'pickup' ? '#vrcmodselph' : '#vrcmodseldh')).find('select').val();
	def_hour = def_hour.length > 1 && def_hour.substr(0, 1) == '0' ? def_hour.substr(1) : def_hour;
	def_hour = parseInt(def_hour);
	for (var h = loc_data[sel_wday]['fh']; h <= hlim; h++) {
		var viewh = h > 23 ? (h - 24) : h;
		hopts += '<option value=\''+viewh+'\''+(viewh == def_hour ? ' selected' : '')+'>'+(viewh < 10 ? '0'+viewh : viewh)+'</option>';
	}
	jQuery((mode == 'pickup' ? '#vrcmodselph' : '#vrcmodseldh')).find('select').html(hopts);
	if (mode == 'pickup') {
		setTimeout(function() {
			vrcLocationWopening".$randid."('dropoff');
		}, 750);
	}
}
function vrcInitElems".$randid."() {
	if (typeof vrcmod_wopening_pick === 'undefined') {
		return true;
	}
	vrcmod_hopening_pick = jQuery('#vrcmodselph').find('select').clone();
	vrcmod_hopening_drop = jQuery('#vrcmodseldh').find('select').clone();
}
jQuery(function() {
	vrcInitElems".$randid."();
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '' ] );
	jQuery('#pickupdatemod".$randid."').datepicker({
		showOn: 'focus',".(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "\nbeforeShowDay: vrcIsDayDisabled".$randid.",\n" : "\nbeforeShowDay: vrcCheckClosingDatesIn".$randid.",\n")."
		onSelect: function( selectedDate ) {
			".($totrestrictions > 0 ? "vrcSetMinDropoffDate".$randid."();" : $forcedropday)."
			vrcLocationWopening".$randid."('pickup');
		}
	});
	jQuery('#pickupdatemod".$randid."').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#pickupdatemod".$randid."').datepicker( 'option', 'minDate', '".ModVikrentcarSearchHelper::getMinDaysAdvance()."d');
	jQuery('#pickupdatemod".$randid."').datepicker( 'option', 'maxDate', '".ModVikrentcarSearchHelper::getMaxDateFuture()."');
	jQuery('#releasedatemod".$randid."').datepicker({
		showOn: 'focus',".(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "\nbeforeShowDay: vrcIsDayDisabledDropoff".$randid.",\n" : "\nbeforeShowDay: vrcCheckClosingDatesOut".$randid.",\n")."
		onSelect: function( selectedDate ) {
			vrcLocationWopening".$randid."('dropoff');
		}
	});
	jQuery('#releasedatemod".$randid."').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#releasedatemod".$randid."').datepicker( 'option', 'minDate', '".ModVikrentcarSearchHelper::getMinDaysAdvance()."d');
	jQuery('#releasedatemod".$randid."').datepicker( 'option', 'maxDate', '".ModVikrentcarSearchHelper::getMaxDateFuture()."');
	jQuery('#pickupdatemod".$randid."').datepicker( 'option', jQuery.datepicker.regional[ 'vikrentcarmod' ] );
	jQuery('#releasedatemod".$randid."').datepicker( 'option', jQuery.datepicker.regional[ 'vikrentcarmod' ] );
	jQuery('.vr-cal-img, .vrc-caltrigger').click(function() {
		var jdp = jQuery(this).prev('input.hasDatepicker');
		if(jdp.length) {
			jdp.focus();
		}
	});
});";
		$document->addScriptDeclaration($sdecl);

		// start the DIV container for the datetimes part
		echo "<div class=\"vrc-searchmod-section-datetimes\">\n";
		//

		echo "<div class=\"vrcsfentrycont\"><div class=\"vrcsfentrylabsel\"><label for=\"pickupdatemod".$randid."\">" . Text::_('VRMPICKUPCAR') . "</label><div class=\"vrcsfentrydate\"><input type=\"text\" name=\"pickupdate\" id=\"pickupdatemod".$randid."\" size=\"10\" autocomplete=\"off\" onfocus=\"this.blur();\" readonly/><i class=\"fa fa-calendar vrc-caltrigger\"></i></div></div><div class=\"vrcsfentrytime\"><label>" . Text::_('VRMALLE') . "</label><div class=\"vrc-sm-time-container\"><span id=\"vrcmodselph\"><select name=\"pickuph\">" . $hours . "</select></span><span class=\"vrctimesep\">:</span><span id=\"vrcmodselpm\"><select name=\"pickupm\">" . $minutes . "</select></span></div></div></div>\n";
		echo "<div class=\"vrcsfentrycont\"><div class=\"vrcsfentrylabsel\"><label for=\"releasedatemod".$randid."\">" . Text::_('VRMRETURNCAR') . "</label><div class=\"vrcsfentrydate\"><input type=\"text\" name=\"releasedate\" id=\"releasedatemod".$randid."\" size=\"10\" autocomplete=\"off\" onfocus=\"this.blur();\" readonly/><i class=\"fa fa-calendar vrc-caltrigger\"></i></div></div><div class=\"vrcsfentrytime\"><label>" . Text::_('VRMALLEDROP') . "</label><div class=\"vrc-sm-time-container\"><span id=\"vrcmodseldh\"><select name=\"releaseh\">" . $hoursret . "</select></span><span class=\"vrctimesep\">:</span><span id=\"vrcmodseldm\"><select name=\"releasem\">" . $minutesret . "</select></span></div></div></div>";

		// close datetimes part
		echo "</div>\n";
		//
	} else {
		/**
		 * @deprecated 	the Joomla calendar has been deprecated and removed.
		 * 
		 * @since 		1.13
		 */
	}
    
    $vrcats = "";
    
    if (intval($params->get('showcat')) == 0) {
    	$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='showcategories';";
		$dbo->setQuery($q);
		$dbo->execute();
    	if ($dbo->getNumRows() == 1) {
    		$sc = $dbo->loadAssocList();
    		if (intval($sc[0]['setting']) == 1) {
    			$q = "SELECT * FROM `#__vikrentcar_categories` ORDER BY `#__vikrentcar_categories`.`ordering` ASC, `#__vikrentcar_categories`.`name` ASC;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$categories = $dbo->loadAssocList();
					$vrc_tn->translateContents($categories, '#__vikrentcar_categories');
					// start categories part
					$vrcats .= "<div class=\"vrc-searchmod-section-categories\">";
					//
					$vrcats .= "<div class=\"vrcsfentrycont\"><label for=\"vrc-categories".$randid."\">".Text::_('VRMCARCAT')."</label><div class=\"vrcsfentryselect\"><select id=\"vrc-categories".$randid."\" name=\"categories\">";
					$vrcats .= "<option value=\"all\">".Text::_('VRMALLCAT')."</option>\n";
					foreach ($categories as $cat) {
						$vrcats .= "<option value=\"".$cat['id']."\">".$cat['name']."</option>\n";
					}
					$vrcats .= "</select></div></div>\n";
					// close categories part
					$vrcats .= "</div>";
					//
				}
    		} elseif (intval($params->get('category_id')) > 0) {
				$q = "SELECT * FROM `#__vikrentcar_categories` WHERE `id`=".(int)$params->get('category_id').";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$categories = $dbo->loadAssocList();
					$vrc_tn->translateContents($categories, '#__vikrentcar_categories');
					?>
					<input type="hidden" name="categories" value="<?php echo $categories[0]['id']; ?>" />
					<?php
				}
			}
    	}
    } elseif (intval($params->get('showcat')) == 1) {
    	$q = "SELECT * FROM `#__vikrentcar_categories` ORDER BY `#__vikrentcar_categories`.`ordering` ASC, `#__vikrentcar_categories`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$categories = $dbo->loadAssocList();
			$vrc_tn->translateContents($categories, '#__vikrentcar_categories');
			// start categories part
			$vrcats .= "<div class=\"vrc-searchmod-section-categories\">";
			//
			$vrcats .= "<div class=\"vrcsfentrycont\"><label for=\"vrc-categories".$randid."\">".Text::_('VRMCARCAT')."</label><div class=\"vrcsfentryselect\"><select id=\"vrc-categories".$randid."\" name=\"categories\">";
			$vrcats .= "<option value=\"all\">".Text::_('VRMALLCAT')."</option>\n";
			foreach ($categories as $cat) {
				$vrcats .= "<option value=\"".$cat['id']."\">".$cat['name']."</option>\n";
			}
			$vrcats .= "</select></div></div>\n";
			// close categories part
			$vrcats .= "</div>";
			//
		}
    } elseif (intval($params->get('category_id')) > 0) {
		$q = "SELECT * FROM `#__vikrentcar_categories` WHERE `id`=".(int)$params->get('category_id').";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$categories = $dbo->loadAssocList();
			$vrc_tn->translateContents($categories, '#__vikrentcar_categories');
			?>
			<input type="hidden" name="categories" value="<?php echo $categories[0]['id']; ?>" />
			<?php
		}
	}
    echo $vrcats;
    
    ?>
    	<div class="vrc-searchmod-section-sbmt">
			<div class="vrcsfentrycont">
				<div class="vrcsfentrysubmit">
					<button type="submit" class="btn vrcsearch"><?php echo (strlen($params->get('srchbtntext')) > 0 ? $params->get('srchbtntext') : Text::_('SEARCHD')); ?></button>
				</div>
			</div>
		</div>
    </form>
</div>
</div>

<?php
//VikRentCar 1.7
$sespickupts = $session->get('vrcpickupts', '');
$sesdropoffts = $session->get('vrcreturnts', '');
$ptask = $input->getString('task', '');
if ($ptask == 'search' && !empty($sespickupts) && !empty($sesdropoffts)) {
	if ($dateformat == "%d/%m/%Y") {
		$jsdf = 'd/m/Y';
	} elseif ($dateformat == "%m/%d/%Y") {
		$jsdf = 'm/d/Y';
	} else {
		$jsdf = 'Y/m/d';
	}
	$sespickuph = date('H', $sespickupts);
	$sespickupm = date('i', $sespickupts);
	$sesdropoffh = date('H', $sesdropoffts);
	$sesdropoffm = date('i', $sesdropoffts);
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

/**
 * Form submit JS validation (mostly used for the opening/closing minutes).
 * This piece of code should be always printed in the DOM as the main form
 * calls this function when going on submit.
 * 
 * @since 	1.12
 */
?>
<script type="text/javascript">
function vrcCleanNumber<?php echo $randid; ?>(snum) {
	if (snum.length > 1 && snum.substr(0, 1) == '0') {
		return parseInt(snum.substr(1));
	}
	return parseInt(snum);
}
function vrcValidateSearch<?php echo $randid; ?>() {
	if (typeof jQuery === 'undefined' || typeof vrcmod_wopening_pick === 'undefined') {
		return true;
	}
	if (vrcmod_mopening_pick !== null) {
		// pickup time
		var pickh = jQuery('#vrcmodselph').find('select').val();
		var pickm = jQuery('#vrcmodselpm').find('select').val();
		if (!pickh || !pickh.length || !pickm) {
			return true;
		}
		pickh = vrcCleanNumber<?php echo $randid; ?>(pickh);
		pickm = vrcCleanNumber<?php echo $randid; ?>(pickm);
		if (pickh == vrcmod_mopening_pick[0]) {
			if (pickm < vrcmod_mopening_pick[1]) {
				// location is still closed at this time
				jQuery('#vrcmodselpm').find('select').html('<option value="'+vrcmod_mopening_pick[1]+'">'+(vrcmod_mopening_pick[1] < 10 ? '0'+vrcmod_mopening_pick[1] : vrcmod_mopening_pick[1])+'</option>').val(vrcmod_mopening_pick[1]);
			}
		}
	}

	if (vrcmod_mopening_drop !== null) {
		// dropoff time
		var droph = jQuery('#vrcmodseldh').find('select').val();
		var dropm = jQuery('#vrcmodseldm').find('select').val();
		if (!droph || !droph.length || !dropm) {
			return true;
		}
		droph = vrcCleanNumber<?php echo $randid; ?>(droph);
		dropm = vrcCleanNumber<?php echo $randid; ?>(dropm);
		if (droph == vrcmod_mopening_drop[0]) {
			if (dropm > vrcmod_mopening_drop[1]) {
				// location is already closed at this time
				jQuery('#vrcmodseldm').find('select').html('<option value="'+vrcmod_mopening_drop[1]+'">'+(vrcmod_mopening_drop[1] < 10 ? '0'+vrcmod_mopening_drop[1] : vrcmod_mopening_drop[1])+'</option>').val(vrcmod_mopening_drop[1]);
			}
		}
	}

	return true;
}
</script>
<?php
//
