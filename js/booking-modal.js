/**
 * booking-modal.js
 * Opens the VikRentCar oconfirm page inside a modal overlay with an iframe.
 *
 * Depends on:
 *   - jQuery (already loaded by Joomla / VikRentCar)
 *   - vrcDateToUnixTs() defined in cardetails/default.php
 *
 * Public API:
 *   vrcOpenBookingModal()   — validate dates, build URL, show modal
 *   vrcCloseBookingModal()  — hide modal, clear iframe src
 */

(function ($) {
	'use strict';

	/* ── Open ──────────────────────────────────────────────────────────────── */
	window.vrcOpenBookingModal = function () {

		// 1. Validate dates
		var pickDate = $('#pickupdate').val();
		var relDate  = $('#releasedate').val();

		if (!pickDate || !relDate) {
			alert('Vă rugăm să selectați datele de preluare și returnare.');
			return false;
		}

		var pickH = parseInt($('#vrccomselph select').val(), 10) || 0;
		var relH  = parseInt($('#vrccomseldh select').val(), 10) || 0;

		// vrcDateToUnixTs is defined inline in cardetails/default.php
		if (typeof vrcDateToUnixTs !== 'function') {
			console.error('booking-modal: vrcDateToUnixTs is not defined.');
			return false;
		}

		var pickTs = vrcDateToUnixTs(pickDate, pickH);
		var relTs  = vrcDateToUnixTs(relDate,  relH);

		if (!pickTs || !relTs || relTs <= pickTs) {
			alert('Intervalul de date selectat nu este valid.');
			return false;
		}

		var days = Math.ceil((relTs - pickTs) / 86400);

		// 2. Collect all booking parameters
		var $overlay   = $('#vrc-booking-modal-overlay');
		var baseUrl    = $overlay.data('oconfirm-url'); // set via data-attribute in PHP

		if (!baseUrl) {
			console.error('booking-modal: data-oconfirm-url not set on overlay element.');
			return false;
		}

		var params = {
			carid:       $('input[name="carid"]').val()    || '',
			priceid:     $('#vrc-priceid').val()            || '',
			pickup:      pickTs,
			release:     relTs,
			days:        days,
			place:       $('#place').val() || $('input[name="place"]').val() || '',
			returnplace: $('#returnplace').val() || '',
			tmpl:        'component'
		};

		// Optional extras (optid1, optid2 …)
		$('input[name^="optid"]').each(function () {
			params[$(this).attr('name')] = $(this).val();
		});

		// Coupon code (optional field added in cardetails)
		var coupon = $('#vrc-coupon-code').val();
		if (coupon && coupon.trim()) {
			params.couponcode = coupon.trim();
		}

		// 3. Build URL — baseUrl already has option + task + Itemid
		var sep = baseUrl.indexOf('?') >= 0 ? '&' : '?';
		var iframeUrl = baseUrl + sep + $.param(params);

		// 4. Show modal
		$('#vrc-booking-modal-iframe').attr('src', iframeUrl);
		$overlay.addClass('is-active');
		$(document.body).addClass('vrc-modal-open');

		return false; // prevent any default form submit
	};

	/* ── Close ─────────────────────────────────────────────────────────────── */
	window.vrcCloseBookingModal = function () {
		var $overlay = $('#vrc-booking-modal-overlay');
		$overlay.removeClass('is-active');
		$(document.body).removeClass('vrc-modal-open');

		// Clear iframe src after transition so it stops loading/running
		setTimeout(function () {
			$('#vrc-booking-modal-iframe').attr('src', 'about:blank');
		}, 350);
	};

	/* ── Event listeners ───────────────────────────────────────────────────── */
	$(function () {

		// Close button (×)
		$(document).on('click', '#vrc-booking-modal-close', function (e) {
			e.preventDefault();
			vrcCloseBookingModal();
		});

		// Click on the dark backdrop (outside the modal box) closes it
		$(document).on('click', '#vrc-booking-modal-overlay', function (e) {
			if ($(e.target).is('#vrc-booking-modal-overlay')) {
				vrcCloseBookingModal();
			}
		});

		// ESC key closes the modal
		$(document).on('keyup.vrcbookingmodal', function (e) {
			if ((e.key === 'Escape' || e.keyCode === 27)
					&& $('#vrc-booking-modal-overlay').hasClass('is-active')) {
				vrcCloseBookingModal();
			}
		});

		// Coupon "Apply" button — validate server-side, update price dynamically
		$(document).on('click', '#vrc-coupon-apply', function (e) {
			e.preventDefault();
			var code     = $('#vrc-coupon-code').val().trim();
			var $btn     = $(this);
			var $feedback = $('#vrc-coupon-feedback');

			// If empty: reset active coupon
			if (!code) {
				window.vrcActiveCoupon = null;
				$feedback.html('');
				if (typeof cdUpdateSummary === 'function') { cdUpdateSummary(); }
				return;
			}

			// AJAX URL is emitted by cardetails PHP as cdCouponAjaxUrl
			var ajaxUrl = (typeof cdCouponAjaxUrl !== 'undefined') ? cdCouponAjaxUrl : '';
			if (!ajaxUrl) {
				console.warn('booking-modal: cdCouponAjaxUrl not defined');
				return;
			}

			var days         = (typeof cdGetDays === 'function') ? (cdGetDays() || 0) : 0;
			var carid        = $('input[name="carid"]').val() || 0;
			var originalText = $btn.data('original-text') || 'Aplică';

			$btn.prop('disabled', true).text('…');
			$feedback.html('');

			$.ajax({
				type: 'POST',
				url: ajaxUrl,
				data: { couponcode: code, carid: carid, days: days },
				dataType: 'json',
				timeout: 8000
			}).done(function (res) {
				if (res && res.valid) {
					window.vrcActiveCoupon = { type: res.type, value: res.value, label: res.label };
					$feedback.html('<span class="cd-coupon-feedback-ok">\u2713 ' + (res.label || 'Reducere aplicată') + '</span>');
				} else {
					window.vrcActiveCoupon = null;
					$feedback.html('<span class="cd-coupon-feedback-error">\u2715 ' + (res && res.error ? res.error : 'Cod invalid') + '</span>');
				}
				if (typeof cdUpdateSummary === 'function') { cdUpdateSummary(); }
			}).fail(function () {
				window.vrcActiveCoupon = null;
				$feedback.html('<span class="cd-coupon-feedback-error">\u2715 Eroare de rețea. Încercați din nou.</span>');
			}).always(function () {
				$btn.prop('disabled', false).text(originalText);
			});
		});

		// Reset coupon discount when the code field is cleared manually
		$(document).on('input', '#vrc-coupon-code', function () {
			if (!$(this).val().trim() && window.vrcActiveCoupon) {
				window.vrcActiveCoupon = null;
				$('#vrc-coupon-feedback').html('');
				if (typeof cdUpdateSummary === 'function') { cdUpdateSummary(); }
			}
		});
	});

})(jQuery);
