/**
 * Car Details Page - v3 Booking Functions
 * Extracted from: /templates/rent/html/com_vikrentcar/cardetails/default.php
 * 
 * Data variables required (defined in default.php):
 * - cdGraceHours, cdRateByDay, cdOptionals, cdCurrency, cdCarName, cdPlacesMap
 * - cdOohFees, cdDescPrefix, cdDescCarInfix, cdDescLocInfix, cdDescLocPickup, cdDescLocReturn
 * - cdDescDayWord, cdDescDaysWord, cdDateFormat, cdOohLabels, cdLabelBasePrice, cdTermsAlert
 */

/* Format integer (no decimals) */
function cdFmt(n) {
	return Math.round(parseFloat(n)).toString();
}

function cdGetDays() {
	var p = jQuery('#pickupdate').val(), r = jQuery('#releasedate').val();
	if (!p || !r) return null;
	try {
		var fmt = cdDateFormat; // e.g. 'dd/mm/yy' or 'mm/dd/yy' or 'yy/mm/dd'

		// Parse midnight dates first (no hours) for calendar-day calculation
		var d1m = jQuery.datepicker.parseDate(fmt, p);
		var d2m = jQuery.datepicker.parseDate(fmt, r);

		// Get hour values from selects
		var pickHour = parseInt(jQuery('#vrccomselph select').val()) || 0;
		var dropHour = parseInt(jQuery('#vrccomseldh select').val()) || 0;

		// Build hour-adjusted timestamps for overage calculation
		var d1 = new Date(d1m); d1.setHours(pickHour, 0, 0, 0);
		var d2 = new Date(d2m); d2.setHours(dropHour, 0, 0, 0);

		// Calendar days (midnight-to-midnight) = billing base, immune to hour diffs & DST.
		// This mirrors the logic in v3UpdateGraceBar() in default.php exactly.
		var calendarDays = Math.round((d2m - d1m) / 86400000);
		var diffDays = calendarDays;

		window.cdGraceState = 'none'; // 'none' | 'active' | 'exceeded'
		if (cdGraceHours > 0 && calendarDays >= 1) {
			// billingEnd = same clock-hour as pickup on the return calendar date
			var billingEndTs = d1.getTime() + (calendarDays * 86400000);
			// overageMs: negative = early return, 0 = exact, positive = overtime
			var overageMs = d2.getTime() - billingEndTs;

			if (overageMs > cdGraceHours * 3600000) {
				// Overtime exceeds grace window → extra day charged
				window.cdGraceState = 'exceeded';
				diffDays = calendarDays + 1;
			} else {
				// On time, early, or within grace window — no extra charge
				window.cdGraceState = 'active';
				diffDays = calendarDays;
			}
		}

		return diffDays > 0 ? diffDays : null;
	} catch(e) { return null; }
}

function cdGetRate(days) {
	if (!days || !cdRateByDay) return null;
	var best = null;
	var keys = Object.keys(cdRateByDay).map(Number).sort(function(a, b) { return a - b; });
	for (var i = 0; i < keys.length; i++) {
		if (keys[i] <= days) { best = cdRateByDay[keys[i]]; }
	}
	return best;
}

/* Read hour select value → seconds since midnight */
function cdGetHourSecs(selectOrWrapperId) {
	var $el = jQuery('#' + selectOrWrapperId);
	// For the new single select inside #vrccomselph / #vrccomseldh
	var $sel = $el.is('select') ? $el : $el.find('select');
	if (!$sel.length) return 0;
	return (parseInt($sel.val()) || 0) * 3600;
}

function cdIsOoh(secs, fee) {
	if (fee.from > fee.to) { return secs >= fee.from || secs < fee.to; }
	return secs >= fee.from && secs < fee.to;
}

function cdCheckOoh() {
	if (!cdOohFees || !cdOohFees.length) return;
	var pickSecs = cdGetHourSecs('vrccomselph');
	var dropSecs = cdGetHourSecs('vrccomseldh');
	var messages = [];
	for (var i = 0; i < cdOohFees.length; i++) {
		var f = cdOohFees[i];
		var pickOoh = (f.type === 1 || f.type === 3) && cdIsOoh(pickSecs, f);
		var dropOoh = (f.type === 2 || f.type === 3) && cdIsOoh(dropSecs, f);
		if (pickOoh || dropOoh) {
			var timeRange = ' (' + f.fromLabel + '–' + f.toLabel + ')';
			var label;
			if (pickOoh && dropOoh) {
				label = cdOohLabels.pickDrop + timeRange;
			} else if (pickOoh) {
				label = cdOohLabels.pick + timeRange;
			} else {
				label = cdOohLabels.drop + timeRange;
			}
			var parts = [];
			if (pickOoh) parts.push(cdCurrency + cdFmt(f.pickcharge));
			if (dropOoh) parts.push(cdCurrency + cdFmt(f.dropcharge));
			messages.push(label + ': ' + parts.join(' + '));
		}
	}
	var $w = jQuery('#cd-ooh-warning');
	if (messages.length) {
		$w.text(messages.join(' | ')).show();
	} else {
		$w.hide();
	}
}

function cdOohTotal() {
	var pickSecs = cdGetHourSecs('vrccomselph');
	var dropSecs = cdGetHourSecs('vrccomseldh');
	var total = 0;
	for (var i = 0; i < cdOohFees.length; i++) {
		var f = cdOohFees[i];
		var pickOoh = (f.type === 1 || f.type === 3) && cdIsOoh(pickSecs, f);
		var dropOoh = (f.type === 2 || f.type === 3) && cdIsOoh(dropSecs, f);
		var rowTotal = 0;
		if (pickOoh) rowTotal += parseFloat(f.pickcharge);
		if (dropOoh) rowTotal += parseFloat(f.dropcharge);
		if (f.maxcharge > 0 && rowTotal > f.maxcharge) rowTotal = parseFloat(f.maxcharge);
		total += rowTotal;
	}
	return total;
}

function cdToggleOptional(id, cost, perday, maxprice) {
	cdOptionals[id].checked = !cdOptionals[id].checked;
	var $row = jQuery('#cd-opt-row-' + id);
	if (cdOptionals[id].checked) {
		$row.addClass('is-checked');
		jQuery('#cd-opt-input-' + id).val('1');
	} else {
		$row.removeClass('is-checked');
		jQuery('#cd-opt-input-' + id).val('0');
	}
	cdUpdateSummary();
}

function cdSetOptionalQty(id, delta) {
	if (!cdOptionals[id]) return;
	var newQty = (cdOptionals[id].qty || 0) + delta;
	if (newQty < 0) newQty = 0;
	cdOptionals[id].qty = newQty;
	cdOptionals[id].checked = (newQty > 0);
	jQuery('#cd-opt-input-' + id).val(newQty);
	jQuery('#cd-opt-qty-' + id).text(newQty);
	var $row = jQuery('#cd-opt-row-' + id);
	if (newQty > 0) { $row.addClass('is-checked'); } else { $row.removeClass('is-checked'); }
	cdUpdateSummary();
}

function cdUpdateSummary() {
	var days = cdGetDays();
	var $sum = jQuery('#cd-summary');

	/* ── Update grace notice / exceeded warning ── */
	if (cdGraceHours > 0) {
		var $graceBy  = jQuery('#cd-grace-returnby');
		var $exceeded = jQuery('#cd-grace-exceeded');
		var graceState = (typeof window.cdGraceState !== 'undefined') ? window.cdGraceState : 'none';

		// Grace bar is visible and managed by v3UpdateGraceBar()
		// This section only manages the grace exceeded warning
		if (days && graceState === 'exceeded') {
			// graceState === 'exceeded': show warning
			var excLabel = cdOohLabels.graceExceeded;
			jQuery('#cd-grace-exceeded').text(excLabel);
			$exceeded.show();
		} else {
			$exceeded.hide();
		}
	}

	/* ── Update KM notice ── */
	var $kmValue = jQuery('#cd-km-value');
	var $kmNotice = jQuery('#cd-km-notice');
	var $kmOverLimit = jQuery('#cd-km-overlimit');
	var isUnlimitedActive = jQuery('#cd-opt-toggle-4').is(':checked');

	if (isUnlimitedActive) {
		$kmNotice.addClass('unlimited disabled');
		$kmValue.text('∞ Unlimited');
		
		// Set €0/km and disable over limit row when unlimited is active
		if ($kmOverLimit.length) {
			$kmOverLimit.text('€0/km');
			$kmOverLimit.closest('.v3-ni').addClass('disabled');
		}
	} else {
		$kmNotice.removeClass('unlimited disabled');
		if (days && $kmValue.length) {
			var kmPerDay = window.vrcKmLimit ? window.vrcKmLimit.kmPerDay : parseInt($kmValue.data('km-per-day')) || 200;
			var totalKm = days * kmPerDay;
			$kmValue.text(totalKm + ' km total');
		} else {
			if (window.vrcKmLimit && $kmValue.length) {
				$kmValue.text(window.vrcKmLimit.kmPerDay + ' km/day');
			}
		}
		
		// Restore over limit price and enable row
		if ($kmOverLimit.length && window.vrcKmLimit) {
			$kmOverLimit.text(cdCurrency + vrcKmLimit.overPrice + '/km');
			$kmOverLimit.closest('.v3-ni').removeClass('disabled');
		}
	}

	if (!days) { $sum.removeClass('is-visible'); return; }

	var rate = cdGetRate(days);
	if (!rate) { $sum.removeClass('is-visible'); return; }

	// Description sentence (i18n via cdDesc* vars)
	var _dw2 = days === 1 ? cdDescDayWord : cdDescDaysWord;
	var _locId = jQuery('#place').val() || '';
	var _locName = (_locId && typeof cdPlacesMap !== 'undefined' && cdPlacesMap[_locId]) ? cdPlacesMap[_locId] : '';
	var _diffReturn = jQuery('#cd-diff-return-chk').is(':checked');
	var _retLocName = '';
	if (_diffReturn) {
		var _retLocId = jQuery('#returnplace_visible').val();
		_retLocName = (_retLocId && typeof cdPlacesMap !== 'undefined' && cdPlacesMap[_retLocId]) ? cdPlacesMap[_retLocId] : '';
	}

	var _desc = cdDescPrefix + ' <strong>' + days + '\u00A0' + _dw2 + '</strong>';
	if (typeof cdCarName !== 'undefined' && cdCarName) { _desc += cdDescCarInfix + '<strong>' + cdCarName + '</strong>'; }

	if (_diffReturn && _locName && _retLocName) {
		_desc += ' ' + cdDescLocPickup + ' <strong>' + _locName + '</strong>'
			+ ' ' + cdDescLocReturn + ' <strong>' + _retLocName + '</strong>';
	} else if (_locName) {
		_desc += cdDescLocInfix + '<strong>' + _locName + '</strong>';
	}

	_desc += '.';
	jQuery('#cd-summary-desc').html(_desc);

	var baseTotal = rate * days;
	var dayWord = cdDescDaysWord;
	var rows = '<div class="cd-summary-row"><span>' + cdLabelBasePrice + '</span>'
		+ '<span class="cd-summary-row-val">' + cdCurrency + cdFmt(rate) + ' &times; ' + days + ' ' + dayWord + '</span></div>';

	var optTotal = 0;
	for (var id in cdOptionals) {
		var o = cdOptionals[id];
		var qty = (o.hmany === 1) ? (o.qty || 0) : (o.checked ? 1 : 0);
		if (!qty) continue;
		var oc = o.perday ? o.cost * days * qty : o.cost * qty;
		if (o.max > 0 && oc > o.max) oc = o.max;
		optTotal += oc;
		var name = jQuery('#cd-opt-row-' + id + ' .v3-opt-name').text();
		var qtyLabel = (o.hmany === 1 && qty > 1) ? ' \u00d7 ' + qty : '';
		rows += '<div class="cd-summary-row"><span>' + name + qtyLabel + '</span>'
			+ '<span class="cd-summary-row-val">' + cdCurrency + cdFmt(oc) + '</span></div>';
	}

	var oohTotal = cdOohTotal();
	if (oohTotal > 0) {
		var oohLabel;
		// Determine OOH label from active fees
		for (var i = 0; i < cdOohFees.length; i++) {
			var f = cdOohFees[i];
			var pickSecs = cdGetHourSecs('vrccomselph');
			var dropSecs = cdGetHourSecs('vrccomseldh');
			var pickOoh = (f.type === 1 || f.type === 3) && cdIsOoh(pickSecs, f);
			var dropOoh = (f.type === 2 || f.type === 3) && cdIsOoh(dropSecs, f);
			if (pickOoh || dropOoh) {
				var timeRange = ' (' + f.fromLabel + '–' + f.toLabel + ')';
				if (pickOoh && dropOoh) oohLabel = cdOohLabels.pickDrop + timeRange;
				else if (pickOoh) oohLabel = cdOohLabels.pick + timeRange;
				else oohLabel = cdOohLabels.drop + timeRange;
				break;
			}
		}
		rows += '<div class="cd-summary-row"><span>' + (oohLabel || 'OOH') + '</span>'
			+ '<span class="cd-summary-row-val">' + cdCurrency + cdFmt(oohTotal) + '</span></div>';
	}

	// Coupon discount row
	var couponDiscount = 0;
	if (window.vrcActiveCoupon) {
		var _ac = window.vrcActiveCoupon;
		var _subtotal = baseTotal + optTotal + oohTotal;
		if (_ac.type === 1) {
			couponDiscount = Math.round(_subtotal * parseFloat(_ac.value) / 100);
		} else {
			couponDiscount = Math.min(parseFloat(_ac.value), _subtotal);
		}
		if (couponDiscount > 0) {
			rows += '<div class="cd-summary-row cd-summary-row-discount"><span>' + (_ac.label || 'Reducere') + '</span>'
				+ '<span class="cd-summary-row-val cd-discount-val">\u2212' + cdCurrency + cdFmt(couponDiscount) + '</span></div>';
		}
	}

	var total = baseTotal + optTotal + oohTotal - couponDiscount;
	jQuery('#cd-summary-rows').html(rows);
	jQuery('#cd-summary-total').text(cdCurrency + cdFmt(total));
	
	// Mirror total to payment button amount
	jQuery('#v3-pay-full-amt').text(cdCurrency + cdFmt(total));
	
	// Compute and update Reserve payment option amount dynamically
	if (typeof cdPayAccPercent !== 'undefined' && jQuery('#v3-pay-reserve').length) {
		var reserveAmt;
		if (typeof cdTypeDeposit !== 'undefined' && cdTypeDeposit === 'fixed') {
			reserveAmt = parseFloat(cdPayAccPercent);
		} else {
			// percentage (default)
			reserveAmt = Math.round(total * parseFloat(cdPayAccPercent) / 100);
		}
		var restLabel = (typeof cdPayNowRestLabel !== 'undefined') ? cdPayNowRestLabel : 'now · rest on pickup';
		jQuery('#v3-pay-reserve-amt').text(cdCurrency + cdFmt(reserveAmt));
		jQuery('#v3-pay-res-desc').text(cdCurrency + cdFmt(reserveAmt) + ' ' + restLabel);
	}
	
	// Show/hide deposit notice based on whether we have a deposit
	var $depositNotice = jQuery('#v3-deposit-notice');
	if ($depositNotice.length && days) {
		$depositNotice.show();
	} else {
		$depositNotice.hide();
	}
	
	$sum.addClass('is-visible');

	cdHighlightTier(days);
}

/**
 * cdFilterHourSelect — removes unavailable hour options from the pickup or dropoff
 * time <select> based on existing bookings on the selected date.
 *
 * For pickup:  any booking returning on that day means pickup can only start
 *              at (returnHour + cdPrepHours) or later.
 * For dropoff: any booking starting on that day means dropoff must be at
 *              (startHour - cdPrepHours) or earlier.
 *
 * When the selected date has no conflicting bookings, the full store-hours list
 * (cdPickHoursHtml) is restored automatically.
 *
 * Requires (from default.php):
 *   cdBusyPeriods   — array of {fromDate:'Y-m-d', fromHour, toDate:'Y-m-d', toHour}
 *   cdPrepHours     — integer gap in hours between return and next pickup (default 2)
 *   cdPickHoursHtml — full baseline <option> HTML for all store hours
 *   cdDateFormat    — jQuery UI date format string (e.g. 'dd/mm/yy')
 */
function cdFilterHourSelect(mode) {
	if (typeof cdBusyPeriods === 'undefined' || typeof cdPickHoursHtml === 'undefined') return;

	var isPickup = (mode === 'pickup');
	var $sel = jQuery(isPickup ? '#vrccomselph select' : '#vrccomseldh select');
	if (!$sel.length) return;

	// ── Always restore full baseline hours first ─────────────────────────────
	$sel.html(cdPickHoursHtml);

	var dateStr = jQuery(isPickup ? '#pickupdate' : '#releasedate').val();
	if (!dateStr || !cdBusyPeriods.length) return; // no date or no bookings → done

	// ── Parse selected date → 'Y-m-d' string (matches PHP date('Y-m-d')) ────
	var fmt = (typeof cdDateFormat !== 'undefined') ? cdDateFormat : 'dd/mm/yy';
	var selDate;
	try { selDate = jQuery.datepicker.parseDate(fmt, dateStr); } catch (e) { return; }
	var ymd = selDate.getFullYear()
		+ '-' + ('0' + (selDate.getMonth() + 1)).slice(-2)
		+ '-' + ('0' + selDate.getDate()).slice(-2);

	var prep = parseInt(cdPrepHours, 10) || 2;

	// ── Compute constraint from busy periods ─────────────────────────────────
	var minPickupHour  = -1;  // pickup must be ≥ this (inclusive)
	var maxDropoffHour = 24;  // dropoff must be ≤ this (inclusive)
	var constrained    = false;

	for (var i = 0; i < cdBusyPeriods.length; i++) {
		var p = cdBusyPeriods[i];
		if (isPickup && p.toDate === ymd) {
			// A booking returns on this day → new pickup ≥ returnHour + prep
			var earliest = p.toHour + prep;
			if (earliest > minPickupHour) { minPickupHour = earliest; constrained = true; }
		} else if (!isPickup && p.fromDate === ymd) {
			// A booking starts on this day → new dropoff ≤ startHour - prep
			var latest = p.fromHour - prep;
			if (latest < maxDropoffHour) { maxDropoffHour = latest; constrained = true; }
		}
	}

	if (!constrained) return; // date is fully free — baseline already restored above

	// ── Remove unavailable <option> elements ─────────────────────────────────
	var prevVal = parseInt($sel.val(), 10);

	$sel.find('option').each(function () {
		var h = parseInt(jQuery(this).val(), 10);
		if (isPickup  && h < minPickupHour)  { jQuery(this).remove(); }
		if (!isPickup && h > maxDropoffHour) { jQuery(this).remove(); }
	});

	// ── Auto-select first remaining option if previous value was removed ─────
	if ($sel.find('option[value="' + prevVal + '"]').length === 0) {
		$sel.find('option:first').prop('selected', true);
		setTimeout(cdUpdateSummary, 50);
		setTimeout(cdCheckOoh, 50);
	}
}

jQuery(function($) {
    // Auto-hide scroll indicator when scrolled to bottom
    const scrollInner = document.querySelector('.cd-right-inner');
    const scrollOuter = document.querySelector('.cd-right');
    
    if (scrollInner && scrollOuter) {
        scrollInner.addEventListener('scroll', () => {
            const atBottom = scrollInner.scrollTop + scrollInner.clientHeight >= scrollInner.scrollHeight - 4;
            scrollOuter.classList.toggle('is-scrolled-end', atBottom);
        });
    }

	// Unlimited KM toggle handler
	function handleUnlimitedKmToggle() {
		cdUpdateSummary();
	}
	
	// Bind toggle change event
	$(document.body).on('change', '#cd-opt-toggle-4', function() {
		handleUnlimitedKmToggle();
	});
	
	// Check initial state on page load
	setTimeout(handleUnlimitedKmToggle, 100);

	var _lp='', _lr='', _lph='', _ldh='';
	function cdPoll() {
		var p  = $('#pickupdate').val(),  r  = $('#releasedate').val();
		var ph = $('#vrccomselph select').val() || '';
		var dh = $('#vrccomseldh select').val() || '';
		if (p!==_lp || r!==_lr || ph!==_lph || dh!==_ldh) {
			_lp=p; _lr=r; _lph=ph; _ldh=dh;
			cdUpdateSummary();
			cdCheckOoh();
		}
	}
	setInterval(cdPoll, 300);

	$(document.body).on('change', '#vrccomselph select, #vrccomseldh select', function() {
		setTimeout(cdCheckOoh, 50);
		setTimeout(cdUpdateSummary, 50);
	});

	$(document.body).on('click', '.vrc-cdetails-cal-pickday', function() {
		setTimeout(cdUpdateSummary, 400);
	});

	// Drag scroll for price tiers grid
	const priceTiersGrid = document.querySelector('.cd-price-tiers-grid');
	if (priceTiersGrid) {
		let isDown = false;
		let startX;
		let scrollLeft;

		priceTiersGrid.addEventListener('mousedown', (e) => {
			isDown = true;
			priceTiersGrid.classList.add('active');
			startX = e.pageX - priceTiersGrid.offsetLeft;
			scrollLeft = priceTiersGrid.parentElement.scrollLeft;
		});

		priceTiersGrid.addEventListener('mouseleave', () => {
			isDown = false;
			priceTiersGrid.classList.remove('active');
		});

		priceTiersGrid.addEventListener('mouseup', () => {
			isDown = false;
			priceTiersGrid.classList.remove('active');
		});

		priceTiersGrid.addEventListener('mousemove', (e) => {
			if (!isDown) return;
			e.preventDefault();
			const x = e.pageX - priceTiersGrid.offsetLeft;
			const walk = (x - startX) * 2; // scroll speed multiplier
			priceTiersGrid.parentElement.scrollLeft = scrollLeft - walk;
		});
	}
});

/* Convert date string + hour → unix timestamp (seconds) */
function vrcDateToUnixTs(dateStr, hour) {
	if (!dateStr) return 0;
	var p = dateStr.split('/');
	var y, m, d;
	var fmt = cdDateFormat;
	if (fmt === 'dd/mm/yy') {
		d = parseInt(p[0], 10); m = parseInt(p[1], 10) - 1; y = parseInt(p[2], 10);
	} else if (fmt === 'mm/dd/yy') {
		m = parseInt(p[0], 10) - 1; d = parseInt(p[1], 10); y = parseInt(p[2], 10);
	} else {
		y = parseInt(p[0], 10); m = parseInt(p[1], 10) - 1; d = parseInt(p[2], 10);
	}
	return Math.floor(Date.UTC(y, m, d, parseInt(hour, 10) || 0, 0, 0) / 1000);
}

function vrcCleanNumber(snum) { if (snum.length > 1 && snum.substr(0,1) == '0') { return parseInt(snum.substr(1)); } return parseInt(snum); }

function vrcValidateSearch() {
	var pickDate = jQuery('#pickupdate').val();
	var relDate  = jQuery('#releasedate').val();
	if (!pickDate || !relDate) {
		return false;
	}

	var pickH = parseInt(jQuery('#vrccomselph select').val(), 10) || 0;
	var relH  = parseInt(jQuery('#vrccomseldh select').val(), 10) || 0;

	var pickTs = vrcDateToUnixTs(pickDate, pickH);
	var relTs  = vrcDateToUnixTs(relDate, relH);

	if (!pickTs || !relTs || relTs <= pickTs) {
		return false;
	}

	var days = Math.round((relTs - pickTs) / 86400);
	jQuery('#vrc-pickup').val(pickTs);
	jQuery('#vrc-release').val(relTs);
	jQuery('#vrc-days').val(days);

	return true;
}

function vrcShowRequestInfo() { jQuery("#vrcdialog-overlay").fadeIn(); vrcdialog_on = true; }
function vrcHideRequestInfo() { jQuery("#vrcdialog-overlay").fadeOut(); vrcdialog_on = false; }

function vrcValidateReqInfo() { 
	if (document.getElementById('vrcf-inp').checked) return true; 
	alert(cdTermsAlert);
	return false; 
}

/* Gallery */
var cdAllImages = [];
function cdSetImage(idx) {
	if (idx >= cdAllImages.length) return;
	var mainEl = document.getElementById('cd-main-img-el');
	var mainLink = document.getElementById('cd-main-link');
	if (mainEl) mainEl.src = cdAllImages[idx];
	if (mainLink) mainLink.href = cdAllImages[idx];
	document.querySelectorAll('.cd-thumb').forEach(function(t) { t.classList.remove('active'); });
	var thumb = document.querySelector('.cd-thumb[data-idx="' + idx + '"]');
	if (thumb) thumb.classList.add('active');
}
