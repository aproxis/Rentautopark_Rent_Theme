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

		var days = Math.round((relTs - pickTs) / 86400);

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

		// Coupon "Apply" button (optional): re-open modal with the coupon so
		// the server can validate it immediately on iframe load.
		$(document).on('click', '#vrc-coupon-apply', function (e) {
			e.preventDefault();
			var code = $('#vrc-coupon-code').val().trim();
			if (!code) { return; }
			// If modal is already open, reload iframe with coupon param
			var $iframe = $('#vrc-booking-modal-iframe');
			if ($iframe.attr('src') && $iframe.attr('src') !== 'about:blank') {
				var src = $iframe.attr('src');
				// Remove existing couponcode param and add new one
				src = src.replace(/[?&]couponcode=[^&]*/g, '');
				var sep2 = src.indexOf('?') >= 0 ? '&' : '?';
				$iframe.attr('src', src + sep2 + 'couponcode=' + encodeURIComponent(code));
			}
		});
	});

})(jQuery);
