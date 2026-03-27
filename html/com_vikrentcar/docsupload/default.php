<?php
/**
 * Template override: /templates/rent/html/com_vikrentcar/docsupload/default.php
 * AutoRent Figma Design — v1
 * Changes:
 *  - Modern card-based layout matching existing design system
 *  - Two-column grid layout (2fr/1fr) like order details
 *  - Clean, professional styling with consistent visual hierarchy
 *  - Enhanced upload experience with better UX
 */

defined('_JEXEC') OR die('Restricted Area');

if (VikRentCar::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
}

$currencysymb = VikRentCar::getCurrencySymb();
$nowdf = VikRentCar::getDateFormat();
$nowtf = VikRentCar::getTimeFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}

// load langs for JS
JText::script('VRC_UPLOAD_FAILED');
JText::script('VRC_REMOVEF_CONFIRM');
JText::script('VRC_PRECHECKIN_TOAST_HELP');

$info_from = getdate($this->order['ritiro']);
$info_to   = getdate($this->order['consegna']);

$wdays_map = array(
	JText::_('VRWEEKDAYZERO'),
	JText::_('VRWEEKDAYONE'),
	JText::_('VRWEEKDAYTWO'),
	JText::_('VRWEEKDAYTHREE'),
	JText::_('VRWEEKDAYFOUR'),
	JText::_('VRWEEKDAYFIVE'),
	JText::_('VRWEEKDAYSIX'),
);

$docs_uploaded = !empty($this->customer['drivers_data']) ? json_decode($this->customer['drivers_data']) : (new stdClass);
$docs_uploaded = !is_object($docs_uploaded) ? (new stdClass) : $docs_uploaded;
$docs_uploaded = new JObject($docs_uploaded);

$current_files = explode('|', $docs_uploaded->get('files', ''));
$current_files = !is_array($current_files) ? array() : $current_files;

$pitemid = VikRequest::getInt('Itemid', '', 'request');
$backto = JRoute::_('index.php?option=com_vikrentcar&view=order&sid='.$this->order['sid'].'&ts='.$this->order['ts'].(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false);
?>

<!-- Add custom CSS -->
<?php
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root() . 'templates/rent/css/docsupload-styles.css');
?>

<div class="docsupload-page">
	<!-- Actions Bar -->
	<div class="docsupload-actions-bar">
		<div class="docsupload-actions-container">
			<div class="docsupload-actions-left">
				<a href="<?php echo JRoute::_($backto); ?>" class="docsupload-back-btn">
					<?php VikRentCarIcons::e('arrow-left'); ?>
					<span><?php echo JText::_('VRBACK'); ?></span>
				</a>
			</div>
			<div class="docsupload-actions-right">
				<button type="submit" name="docsuploadsubmit" class="docsupload-submit-btn" form="docsupload-form">
					<?php VikRentCarIcons::e('check'); ?>
					<span><?php echo JText::_('JAPPLY'); ?></span>
				</button>
			</div>
		</div>
	</div>

	<!-- Main Grid Layout -->
	<div class="docsupload-container">
		<div class="docsupload-grid">
			
			<!-- Left Column: Upload Interface -->
			<div class="docsupload-left">
				
				<!-- Upload Instructions -->
				<?php
				$upload_instructions = VikRentCar::docsUploadInstructions($this->vrc_tn);
				if (!empty($upload_instructions)) {
					?>
					<div class="docsupload-card">
						<div class="docsupload-card-header">
							<h3><?php echo JText::_('VRC_UPLOAD_DOCUMENTS'); ?></h3>
						</div>
						<div class="docsupload-instructions">
							<?php echo VikRentCar::prepareTextFromEditor($upload_instructions); ?>
						</div>
					</div>
					<?php
				}
				?>

				<!-- Upload Form -->
				<div class="docsupload-card">
					<div class="docsupload-card-header">
						<h3><?php echo JText::_('VRC_UPLOAD_DOCUMENTS'); ?></h3>
						<div class="docsupload-order-status">
							<?php
							$status_class = '';
							$status_icon = '';
							$status_text = '';
							
							switch ($this->order['status']) {
								case 'confirmed':
									$status_class = 'status-confirmed';
									$status_icon = 'check-circle';
									$status_text = JText::_('VRC_YOURCONF_ORDER_AT_SUBTITLE');
									break;
								case 'standby':
									$status_class = 'status-standby';
									$status_icon = 'clock';
									$status_text = JText::_('VRC_YOURORDER_PENDING_SUBTITLE');
									break;
								case 'cancelled':
									$status_class = 'status-cancelled';
									$status_icon = 'x-circle';
									$status_text = JText::_('VRC_YOURORDER_CANCELLED_SUBTITLE');
									break;
								default:
									$status_class = 'status-unknown';
									$status_icon = 'circle';
									$status_text = JText::_('VRCORDERSTATUS');
							}
							?>
							<span class="docsupload-status-badge <?php echo $status_class; ?>">
								<?php VikRentCarIcons::e($status_icon); ?>
								<span><?php echo $status_text; ?></span>
							</span>
						</div>
					</div>
					
					<div class="docsupload-content">
						<!-- Uploaded Files -->
						<div class="docsupload-field">
							<label class="docsupload-field-label"><?php echo JText::_('VRC_UPLOADED_FILES'); ?></label>
							<div class="docsupload-files" id="docsupload-files">
								<?php
								foreach ($current_files as $guest_file) {
									if (empty($guest_file) || strpos($guest_file, 'http') !== 0) {
										continue;
									}
									$furl_segments = explode('/', $guest_file);
									$guest_fname = $furl_segments[(count($furl_segments) - 1)];
									$read_fname = substr($guest_fname, (strpos($guest_fname, '_') + 1));
									
									// Determine file type for icon
									$file_extension = strtolower(pathinfo($read_fname, PATHINFO_EXTENSION));
									$file_icon = 'file';
									$is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
									$is_pdf = ($file_extension === 'pdf');
									
									if ($is_image) {
										$file_icon = 'image';
									} elseif ($is_pdf) {
										$file_icon = 'file-text';
									}
									?>
									<div class="docsupload-file-item" data-file-url="<?php echo $guest_file; ?>" data-file-name="<?php echo $read_fname; ?>" data-file-type="<?php echo $file_extension; ?>">
										<div class="docsupload-file-info">
											<div class="docsupload-file-icon">
												<?php VikRentCarIcons::e($file_icon); ?>
											</div>
											<div class="docsupload-file-details">
												<div class="docsupload-file-name"><?php echo $read_fname; ?></div>
												<div class="docsupload-file-size"><?php echo JText::_('VRC_FILE_UPLOADED'); ?></div>
											</div>
										</div>
										<button type="button" class="docsupload-file-remove" data-file="<?php echo $guest_file; ?>">
											<?php VikRentCarIcons::e('times'); ?>
										</button>
									</div>
									<?php
								}
								?>
							</div>
						</div>

						<!-- Upload Area -->
						<div class="docsupload-section">
							<div class="docsupload-field">
								<label class="docsupload-field-label"><?php echo JText::_('VRC_ADD_DOCUMENTS'); ?></label>
								<div class="docsupload-upload-area">
									<input type="hidden" id="docsupload-curfiles" name="docsupload[files]" value="<?php echo $this->escape($docs_uploaded->get('files', '')); ?>" />
									
									<div class="docsupload-drag-drop" id="docsupload-drag-drop">
										<div class="docsupload-drag-drop-content">
											<div class="docsupload-drag-drop-icon">
												<?php VikRentCarIcons::e('cloud-upload'); ?>
											</div>
											<div class="docsupload-drag-drop-text">
												<strong><?php echo JText::_('VRC_DRAG_DROP_UPLOAD'); ?></strong>
												<span><?php echo JText::_('VRC_DRAG_DROP_HINT'); ?></span>
											</div>
											<button type="button" class="docsupload-browse-btn" id="docsupload-browse-btn">
												<?php VikRentCarIcons::e('folder-open'); ?>
												<span><?php echo JText::_('VRC_BROWSE_FILES'); ?></span>
											</button>
										</div>
									</div>

									<!-- Progress Bar -->
									<div class="docsupload-progress" id="docsupload-progress" style="display: none;">
										<div class="docsupload-progress-bar">&nbsp;</div>
									</div>
								</div>
							</div>

							<!-- Disclaimer -->
							<div class="docsupload-disclaimer info">
								<?php echo JText::_('VRC_PRECHECKIN_DISCLAIMER'); ?>
							</div>
						</div>

						<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post" class="docsupload-form" id="docsupload-form">
							<input type="hidden" name="option" value="com_vikrentcar" />
							<input type="hidden" name="task" value="storedocsupload" />
							<input type="hidden" name="sid" value="<?php echo $this->order['sid']; ?>" />
							<input type="hidden" name="ts" value="<?php echo $this->order['ts']; ?>" />
							<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>" />
						</form>
					</div>
				</div>
			</div>

			<!-- Right Column: Order Information -->
			<div class="docsupload-right">
				
				<!-- Order Details Card -->
				<div class="docsupload-card">
					<div class="docsupload-card-header">
						<h3><?php echo JText::_('VRCORDERDETAILS'); ?></h3>
					</div>
					<div class="docsupload-order-details">
						<div class="docsupload-order-grid">
							<div class="docsupload-order-item">
								<div class="docsupload-order-label">
									<svg class="docsupload-order-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
									</svg>
									<span><?php echo JText::_('VRORDEREDON'); ?></span>
								</div>
								<div class="docsupload-order-value"><?php echo date($df . ' ' . $nowtf, $this->order['ts']); ?></div>
							</div>
							
							<div class="docsupload-order-item">
								<div class="docsupload-order-label">
									<svg class="docsupload-order-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
										<polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
										<line x1="12" y1="22.08" x2="12" y2="12"></line>
									</svg>
									<span><?php echo JText::_('VRCORDERNUMBER'); ?></span>
								</div>
								<div class="docsupload-order-value"><?php echo $this->order['id']; ?></div>
							</div>
							
							<div class="docsupload-order-item">
								<div class="docsupload-order-label">
									<svg class="docsupload-order-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
									</svg>
									<span><?php echo JText::_('VRCCONFIRMATIONNUMBER'); ?></span>
								</div>
								<div class="docsupload-order-value"><?php echo $this->order['sid'] . '-' . $this->order['ts']; ?></div>
							</div>
							
							<div class="docsupload-order-item">
								<div class="docsupload-order-label">
									<svg class="docsupload-order-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M21 10c0 6-9 13-9 13s-9-7-9-13a9 9 0 1 1 18 0z"></path>
										<polyline points="12 2 12 12 16 16"></polyline>
									</svg>
									<span><?php echo JText::_('VRPICKUP'); ?></span>
								</div>
								<div class="docsupload-order-value"><?php echo $wdays_map[$info_from['wday']] . ' ' . date($df . ' ' . $nowtf, $this->order['ritiro']); ?></div>
							</div>
							
							<div class="docsupload-order-item">
								<div class="docsupload-order-label">
									<svg class="docsupload-order-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M21 10c0 6-9 13-9 13s-9-7-9-13a9 9 0 1 1 18 0z"></path>
										<polyline points="12 2 12 12 16 16"></polyline>
									</svg>
									<span><?php echo JText::_('VRRETURN'); ?></span>
								</div>
								<div class="docsupload-order-value"><?php echo $wdays_map[$info_to['wday']] . ' ' . date($df . ' ' . $nowtf, $this->order['consegna']); ?></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Lightbox Modal -->
	<div id="docsupload-lightbox" class="docsupload-lightbox" style="display: none;">
		<div class="docsupload-lightbox-content">
			<span class="docsupload-lightbox-close">&times;</span>
			<div class="docsupload-lightbox-header">
				<h4 id="docsupload-lightbox-title"></h4>
			</div>
			<div class="docsupload-lightbox-body">
				<div id="docsupload-lightbox-image-container" style="display: none;">
					<img id="docsupload-lightbox-image" src="" alt="">
				</div>
				<div id="docsupload-lightbox-pdf-container" style="display: none;">
					<embed id="docsupload-lightbox-pdf" src="" type="application/pdf" width="100%" height="600px">
				</div>
				<div id="docsupload-lightbox-text-container" style="display: none;">
					<p>File preview not available. <a id="docsupload-lightbox-download" href="#" download>Download file</a></p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Hidden File Input -->
<input type="file" id="docsupload-file-input" accept="image/*,.pdf" multiple="multiple" style="display: none;" />

<script type="text/javascript">
	/**
	 * Displays a toast message
	 */
	function vrcPresentToast(content, duration, clickcallback) {
		// remove any other previous toast from the document
		jQuery('.vrc-toast-message').remove();
		// build toast
		var toast = jQuery('<div>').addClass('vrc-toast-message vrc-toast-message-presented');
		// onclick function
		var onclickfunc = function() {
			// hide toast when clicked
			jQuery(this).removeClass('vrc-toast-message-presented').addClass('vrc-toast-message-dimissed');
		};
		if (typeof clickcallback === 'function') {
			onclickfunc = function() {
				// launch callback
				clickcallback.call(this);
				// hide toast either way
				jQuery(this).removeClass('vrc-toast-message-presented').addClass('vrc-toast-message-dimissed');
			};
		}
		// register click event on toast
		toast.on('click', onclickfunc);
		// build toast content
		var inner = jQuery('<div>').addClass('vrc-toast-message-content');
		toast.append(inner.append(content));
		// present toast
		jQuery('body').append(toast);
		// set timeout for auto-dismiss
		if (typeof duration === 'undefined') {
			duration = 4000;
		}
		if (!isNaN(duration) && duration > 0) {
			// if duration NaN or <= 0, the toast won't be dismissed automatically
			setTimeout(function() {
				jQuery('.vrc-toast-message').removeClass('vrc-toast-message-presented').addClass('vrc-toast-message-dimissed');
			}, duration);
		}
	}

	/**
	 * Some older smartphones or tablets may not support files uploading
	 */
	function vrcIsUploadSupported() {
		if (!navigator || !navigator.userAgent) {
			return false;
		}
		if (navigator.userAgent.match(/(Android (1.0|1.1|1.5|1.6|2.0|2.1))|(Windows Phone (OS 7|8.0))|(XBLWP)|(ZuneWP)|(w(eb)?OSBrowser)|(webOS)|(Kindle\/(1.0|2.0|2.5|3.0))/)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Checks wether a jQuery XHR response object was due to a connection error.
	 * Property readyState 0 = Network Error (UNSENT), 4 = HTTP error (DONE).
	 * Property responseText may not be set in some browsers.
	 * This is what to check to determine if a connection error occurred.
	 */
	function vrcIsConnectionLostError(err) {
		if (!err || !err.hasOwnProperty('status')) {
			return false;
		}

		return (
			err.statusText == 'error'
			&& err.status == 0
			&& (err.readyState == 0 || err.readyState == 4)
			&& (!err.hasOwnProperty('responseText') || err.responseText == '')
		);
	}

	/**
	 * Ensures AJAX requests that fail due to connection errors are retried automatically.
	 * This function is made specifically to work with AJAX uploads.
	 */
	function vrcDoAjaxUpload(url, data, success, failure, progress, attempt) {
		var VRC_AJAX_MAX_ATTEMPTS = 3;

		if (attempt === undefined) {
			attempt = 1;
		}

		var settings = {
			type: 		 'post',
			contentType: false,
			processData: false,
			cache: 		 false,
		};

		// register event for upload progress
		settings.xhr = function() {
			var xhrobj = jQuery.ajaxSettings.xhr();
			if (xhrobj.upload) {
				// attach progress event
				xhrobj.upload.addEventListener('progress', function(event) {
					// calculate percentage
					var percent  = 0;
					var position = event.loaded || event.position;
					var total 	 = event.total;
					if (event.lengthComputable) {
						percent = Math.ceil(position / total * 100);
					}
					// trigger callback
					progress(percent);
				}, false);
			}
			return xhrobj;
		};

		if (typeof url === 'object') {
			// configuration object passed
			Object.assign(settings, url);
		} else {
			// use the default settings
			settings.url  = url;
		}

		// set request data
		settings.data = data;

		return jQuery.ajax(
			settings
		).done(function(resp) {
			if (success !== undefined) {
				// launch success callback function
				success(resp);
			}
		}).fail(function(err) {
			/**
			 * If the error is caused by a site connection lost, and if the number
			 * of retries is lower than max attempts, retry the same AJAX request.
			 */
			if (attempt < VRC_AJAX_MAX_ATTEMPTS && vrcIsConnectionLostError(err)) {
				// delay the retry by half second
				setTimeout(function() {
					// relaunch same request and increase number of attempts
					console.log('Retrying previous AJAX request');
					vrcDoAjaxUpload(url, data, success, failure, progress, (attempt + 1));
				}, 500);
			} else {
				// launch the failure callback otherwise
				if (failure !== undefined) {
					failure(err);
				}
			}

			// always log the error in console
			console.log('AJAX request failed' + (err.status == 500 ? ' (' + err.responseText + ')' : ''), err);
		});
	}

	/**
	 * Updates the progress bar for the current uploading process
	 */
	function vrcUploadSetProgress(progress) {
		progress = Math.max(0, progress);
		progress = Math.min(100, progress);

		var progress_wrap = jQuery('#docsupload-progress');
		if (!progress_wrap.length) {
			return;
		}
		progress_wrap.show();
		progress_wrap.find('.docsupload-progress-bar').width(progress + '%').html(progress + '%');
	}

	/**
	 * Uploads the selected document(s)
	 */
	function vrcUploadDocuments(files) {
		// create form data object
		var formData = new FormData();

		// set booking and pre-checkin information
		formData.append('sid', '<?php echo $this->order['sid']; ?>');
		formData.append('ts', '<?php echo $this->order['ts']; ?>');

		// iterate files selected and append them to the form data
		for (var i = 0; i < files.length; i++) {
			formData.append('docs[]', files[i]);
		}

		// AJAX request to upload the files selected
		vrcDoAjaxUpload(
			// url
			'<?php echo VikRentCar::ajaxUrl(JRoute::_('index.php?option=com_vikrentcar&task=order_upload_docs&tmpl=component'.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false)); ?>',
			// form data
			formData,
			// success callback
			function(response) {
				// hide progress wrap
				jQuery('#docsupload-progress').hide();
				// unset progress
				vrcUploadSetProgress(0);

				// parse response
				try {
					var obj_res = JSON.parse(response),
						uploaded_urls = [];

					for (var i in obj_res) {
						if (!obj_res.hasOwnProperty(i) || !obj_res[i].hasOwnProperty('url')) {
							continue;
						}
						uploaded_urls.push(obj_res[i]['url']);
					}
					if (!uploaded_urls.length) {
						console.log('no valid URLs returned', response);
						return false;
					}
					// update hidden field
					var hidden_inp = jQuery('#docsupload-curfiles');
					var current_guest_files = hidden_inp.val().split('|');
					if (!current_guest_files.length || !current_guest_files[0].length) {
						current_guest_files = [];
					}
					// merge current files with new ones uploaded
					var new_guest_files = current_guest_files.concat(uploaded_urls);
					// update hidden input field to contain all files
					hidden_inp.val(new_guest_files.join('|'));
					// display links for the newly uploaded files
					var uploaded_content = '';
					for (var i = 0; i < uploaded_urls.length; i++) {
						var furl_segments = uploaded_urls[i].split('/');
						var guest_fname = furl_segments[(furl_segments.length - 1)];
						var read_fname = guest_fname.substr((guest_fname.indexOf('_') + 1));

						uploaded_content += '<div class="docsupload-file-item">';
						uploaded_content += '	<div class="docsupload-file-info">';
						uploaded_content += '		<div class="docsupload-file-icon">';
						uploaded_content += '			<?php VikRentCarIcons::e("file"); ?>';
						uploaded_content += '		</div>';
						uploaded_content += '		<div class="docsupload-file-details">';
						uploaded_content += '			<div class="docsupload-file-name">' + read_fname + '</div>';
						uploaded_content += '			<div class="docsupload-file-size"><?php echo JText::_("VRC_FILE_UPLOADED"); ?></div>';
						uploaded_content += '		</div>';
						uploaded_content += '	</div>';
						uploaded_content += '	<button type="button" class="docsupload-file-remove" data-file="' + uploaded_urls[i] + '">';
						uploaded_content += '		<?php VikRentCarIcons::e("times"); ?>';
						uploaded_content += '	</button>';
						uploaded_content += '</div>';
					}
					// append the new content
					jQuery('#docsupload-files').append(uploaded_content);

					// update status
					jQuery('.docsupload-status-item').removeClass('pending').addClass('completed');
					jQuery('.docsupload-status-item .docsupload-status-icon').html('<?php VikRentCarIcons::e("check-circle"); ?>');
					jQuery('.docsupload-status-item .docsupload-status-subtitle').text('<?php echo JText::_("VRC_DOCUMENTS_UPLOADED_SUCCESS"); ?>');
					jQuery('.docsupload-status-actions').html('<button type="button" class="docsupload-status-btn docsupload-status-btn-success"><i class="fas fa-check"></i> <span><?php echo JText::_("VRC_ALL_DOCUMENTS_UPLOADED"); ?></span></button>');

					// display toast message
					vrcPresentToast(Joomla.JText._('VRC_PRECHECKIN_TOAST_HELP'), 4000, function() {
						jQuery('html,body').animate({scrollTop: jQuery('.docsupload-submit-btn').offset().top - 100}, {duration: 400});
					});
				} catch(err) {
					console.error('could not parse JSON response for uploading documents', err, response);
				}
			},
			// failure callback
			function(error) {
				alert(Joomla.JText._('VRC_UPLOAD_FAILED'));
				// hide progress wrap
				jQuery('#docsupload-progress').hide();
				// unset progress
				vrcUploadSetProgress(0);

				console.error(error);
			},
			// progress callback
			function(progress) {
				// update progress bar
				vrcUploadSetProgress(progress);
			}
		);
	}

	/**
	 * Declare functions for DOM ready
	 */
	jQuery(document).ready(function() {
		/**
		 * Click event on browse button
		 */
		jQuery('#docsupload-browse-btn').click(function() {
			// check if device supports file upload
			if (!vrcIsUploadSupported()) {
				alert('Your device may not support files uploading');
				return false;
			}

			// trigger the click event on the hidden input field for the files upload
			jQuery('#docsupload-file-input').trigger('click');
		});

		/**
		 * Click event on start upload button
		 */
		jQuery('#docsupload-start-upload').click(function() {
			jQuery('#docsupload-browse-btn').trigger('click');
		});

		/**
		 * Change event on global file-upload hidden field
		 */
		jQuery('#docsupload-file-input').on('change', function(e) {
			// get files selected
			var files = jQuery(this)[0].files;

			if (!files || !files.length) {
				console.error('no files selected for upload');
				return false;
			}

			// upload selected files
			vrcUploadDocuments(files);

			// make the input value empty
			jQuery(this).val(null);
		});

		/**
		 * Click event on the button to remove an uploaded file
		 */
		jQuery(document.body).on('click', '.docsupload-file-remove', function(e) {
			e.stopPropagation(); // Prevent lightbox from opening when clicking remove
			var file_container = jQuery(this).closest('.docsupload-file-item');
			if (!file_container.length) {
				return false;
			}
			var file_url = jQuery(this).data('file');
			if (confirm(Joomla.JText._('VRC_REMOVEF_CONFIRM'))) {
				var pax_elem = jQuery('#docsupload-curfiles');
				var pax_urls = pax_elem.val();
				if (pax_urls.indexOf(file_url + '|') >= 0) {
					pax_urls = pax_urls.replace(file_url + '|', '');
				} else if (pax_urls.indexOf('|' + file_url) >= 0) {
					pax_urls = pax_urls.replace('|' + file_url, '');
				} else {
					pax_urls = pax_urls.replace(file_url, '');
				}
				// update hidden input value
				pax_elem.val(pax_urls);
				// remove the file container
				file_container.remove();

				// update status if no files left
				if (jQuery('#docsupload-files .docsupload-file-item').length === 0) {
					jQuery('.docsupload-status-item').removeClass('completed').addClass('pending');
					jQuery('.docsupload-status-item .docsupload-status-icon').html('<?php VikRentCarIcons::e("circle"); ?>');
					jQuery('.docsupload-status-item .docsupload-status-subtitle').text('<?php echo JText::_("VRC_DOCUMENTS_PENDING"); ?>');
					jQuery('.docsupload-status-actions').html('<button type="button" class="docsupload-status-btn docsupload-status-btn-primary" id="docsupload-start-upload"><i class="fas fa-upload"></i> <span><?php echo JText::_("VRC_START_UPLOAD"); ?></span></button>');
				}

				// display toast message
				vrcPresentToast(Joomla.JText._('VRC_PRECHECKIN_TOAST_HELP'), 4000, function() {
					jQuery('html,body').animate({scrollTop: jQuery('.docsupload-submit-btn').offset().top - 100}, {duration: 400});
				});
			}
		});

		/**
		 * Click event on uploaded files to open lightbox
		 */
		jQuery(document.body).on('click', '.docsupload-file-item', function() {
			var file_url = jQuery(this).data('file-url');
			var file_name = jQuery(this).data('file-name');
			var file_type = jQuery(this).data('file-type');
			
			// Set lightbox content
			jQuery('#docsupload-lightbox-title').text(file_name);
			jQuery('#docsupload-lightbox-download').attr('href', file_url);
			
			// Show appropriate content based on file type
			if (file_type === 'pdf') {
				jQuery('#docsupload-lightbox-pdf').attr('src', file_url);
				jQuery('#docsupload-lightbox-pdf-container').show();
				jQuery('#docsupload-lightbox-image-container').hide();
				jQuery('#docsupload-lightbox-text-container').hide();
			} else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(file_type)) {
				jQuery('#docsupload-lightbox-image').attr('src', file_url);
				jQuery('#docsupload-lightbox-image-container').show();
				jQuery('#docsupload-lightbox-pdf-container').hide();
				jQuery('#docsupload-lightbox-text-container').hide();
			} else {
				jQuery('#docsupload-lightbox-text-container').show();
				jQuery('#docsupload-lightbox-image-container').hide();
				jQuery('#docsupload-lightbox-pdf-container').hide();
			}
			
			// Show lightbox
			jQuery('#docsupload-lightbox').show();
		});

		/**
		 * Close lightbox
		 */
		jQuery(document.body).on('click', '.docsupload-lightbox-close, .docsupload-lightbox', function(e) {
			if (e.target === this || e.target.classList.contains('docsupload-lightbox-close')) {
				jQuery('#docsupload-lightbox').hide();
			}
		});

		/**
		 * Close lightbox on escape key
		 */
		jQuery(document).on('keydown', function(e) {
			if (e.key === 'Escape') {
				jQuery('#docsupload-lightbox').hide();
			}
		});

		/**
		 * Drag and drop functionality
		 */
		var dragDropArea = jQuery('#docsupload-drag-drop');
		
		dragDropArea.on('dragover', function(e) {
			e.preventDefault();
			e.stopPropagation();
			jQuery(this).addClass('drag-over');
		});

		dragDropArea.on('dragleave', function(e) {
			e.preventDefault();
			e.stopPropagation();
			jQuery(this).removeClass('drag-over');
		});

		dragDropArea.on('drop', function(e) {
			e.preventDefault();
			e.stopPropagation();
			jQuery(this).removeClass('drag-over');
			
			var files = e.originalEvent.dataTransfer.files;
			if (files && files.length > 0) {
				vrcUploadDocuments(files);
			}
		});

		/**
		 * Form validation
		 */
		jQuery('.docsupload-form').on('submit', function() {
			var hasFiles = jQuery('#docsupload-curfiles').val().trim() !== '';
			var comments = jQuery('#docsupload-comments').val().trim();
			
			if (!hasFiles && comments === '') {
				alert('<?php echo JText::_("VRC_UPLOAD_OR_COMMENTS_REQUIRED"); ?>');
				return false;
			}
			
			return true;
		});
	});
</script>