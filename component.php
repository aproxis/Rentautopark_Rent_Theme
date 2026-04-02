<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// ── VikRentCar: replace unavailability search form with a clean notification ──
$_app = Factory::getApplication();
if ($_app->getInput()->getCmd('option') === 'com_vikrentcar') {

    // Translated strings rendered server-side into JS
    $subtitle = addslashes(Text::_('VRC_MODAL_UNAVAIL_SUBTITLE'));
    $closeBtn  = addslashes(Text::_('VRC_MODAL_UNAVAIL_CLOSE'));

    Factory::getDocument()->addScriptDeclaration('
document.addEventListener("DOMContentLoaded", function () {

    var errPara = document.querySelector("p.err");
    if (!errPara) return;

    var errText = errPara.textContent.trim();

    var mainBody = document.getElementById("window-mainbody");
    if (mainBody) mainBody.style.cssText = "display:none !important;";

    var notice = document.createElement("div");
    notice.id = "vrc-unavail-notice";
    notice.style.cssText = [
        "display:flex",
        "flex-direction:column",
        "align-items:center",
        "justify-content:center",
        "min-height:240px",
        "padding:2rem 1.5rem",
        "text-align:center",
        "font-family:inherit",
        "box-sizing:border-box"
    ].join(";");

    notice.innerHTML = [
        "<div style=\"font-size:2.8rem;margin-bottom:1rem;line-height:1;\">&#128683;</div>",
        "<h3 style=\"margin:0 0 0.75rem;font-size:1.05rem;font-weight:600;",
            "color:#c0392b;line-height:1.4;max-width:360px;\">",
            errText,
        "</h3>",
        "<p style=\"margin:0 0 1.5rem;color:#666;font-size:0.9rem;\">",
            "' . $subtitle . '",
        "</p>",
        "<button ",
            "onclick=\"try{window.parent.vrcCloseBookingModal()}catch(e){window.history.back()}\" ",
            "style=\"padding:0.55rem 1.8rem;background:#FE5001;color:#fff;border:none;",
            "border-radius:6px;cursor:pointer;font-size:0.9rem;font-weight:500;",
            "transition:background 0.2s;\" ",
            "onmouseover=\"this.style.background=\'#d94500\'\" ",
            "onmouseout=\"this.style.background=\'#FE5001\'\">",
            "' . $closeBtn . '",
        "</button>"
    ].join("");

    document.body.appendChild(notice);
});
    ');
}
// ─────────────────────────────────────────────────────────────────────────────

include(dirname(__FILE__) . '/index.php');

T3::getApp()->addCss('windows');