/*
 * ------------------------------------------------------------------------
 * JA Rent template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

 (function($){
  $(document).ready(function(){

    ////////////////////////////////
  // equalheight for col
  ////////////////////////////////
  var ehArray = ehArray2 = [],
    i = 0;

  $('.equal-height').each (function(){
    var $ehc = $(this);
    if ($ehc.has ('.equal-height')) {
      ehArray2[ehArray2.length] = $ehc;
    } else {
      ehArray[ehArray.length] = $ehc;
    }
  });
  for (i = ehArray2.length -1; i >= 0; i--) {
    ehArray[ehArray.length] = ehArray2[i];
  }

  var equalHeight = function() {
    for (i = 0; i < ehArray.length; i++) {
      var $cols = ehArray[i].children().filter('.col'),
        $2cols = ehArray[i].children().filter('.2col'),
        maxHeight = 0,
        equalChildHeight = ehArray[i].hasClass('equal-height-child');

    // reset min-height
      if (equalChildHeight) {
        $cols.each(function(){$(this).children().first().css('min-height', 0)});
      } else {
        $cols.css('min-height', 0);
      }
      $cols.each (function() {
        maxHeight = Math.max(maxHeight, equalChildHeight ? $(this).children().first().innerHeight() : $(this).innerHeight());
      });
      if (equalChildHeight) {
        $cols.each(function(){$(this).children().first().css('min-height', maxHeight)});
        $2cols.each(function(){$(this).children().first().css('min-height', maxHeight * 2)});
      } else {
        $cols.css('min-height', maxHeight);
        $2cols.css('min-height', maxHeight * 2);
      }
    }
    // store current size
    $('.equal-height > .col').each (function(){
      var $col = $(this);
      $col.data('old-width', $col.width()).data('old-height', $col.innerHeight());
    });
  };

  equalHeight();

  // monitor col width and fire equalHeight
  setInterval(function() {
    $('.equal-height > .col').each(function(){
      var $col = $(this);
      if (($col.data('old-width') && $col.data('old-width') != $col.width()) ||
          ($col.data('old-height') && $col.data('old-height') != $col.innerHeight())) {
        equalHeight();
        // break each loop
        return false;
      }
    });
  }, 500);
  $(document).on('hide.bs.modal',function(){
    setTimeout(function(){
      $(document).find('.modal-backdrop.fade.in') && $(document).find('.modal-backdrop.fade.in').remove();
    }, 100);
  });
  });
  
  jQuery(window).on('load',function() {
  	if (typeof vrcSetLocOpenTime != 'undefined' && typeof vrcSetLocOpenTime != undefined) {
		// override vikrentcar default function.
		_fc = vrcSetLocOpenTime.toString();
	
		// delete exists function. maybe this will drain cpu?
		delete vrcSetLocOpenTime;
		vrcSetLocOpenTime=undefined;
	
		var _m = _fc.match(/url\: \"(.*?)\"/);
		if (typeof(_m[1]) == 'string' && _m[1] != undefined && _m[1] != 'undefined') {
			vrcSetLocOpenTime = function (loc, where) {
				if(where == "dropoff") {
					vrc_location_change = true;
				}
				jQuery.ajax({
					type: "POST",
					url: _m[1],
					data: { idloc: loc, pickdrop: where }
				}).done(function(res) {
					var vrcobj = jQuery.parseJSON(res);
					if(where == "pickup") {
						jQuery("#vrccomselph").html(vrcobj.hours);
						jQuery("#vrccomselpm").html(vrcobj.minutes);
					}else {
						jQuery("#vrccomseldh").html(vrcobj.hours);
						jQuery("#vrccomseldm").html(vrcobj.minutes);
					}
					if(where == "pickup" && vrc_location_change === false) {
						jQuery("#returnplace").val(loc).trigger("change");
						vrc_location_change = false;
					}
					jQuery('select').chosen();
					jQuery('select').trigger("liszt","updated");
				});
			}
		}
	}
  });

})(jQuery);