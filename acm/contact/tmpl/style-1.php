<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Contact Style 1: Contact Form & Map
 * ------------------------------------------------------------------------
 * ACM layout: Split layout with contact form (left) and Google Maps iframe (right).
 * Uses Joomla's built-in contact form functionality with proper validation.
 *
 * Extra Fields used:
 *   map-url   (Google Maps embed URL)
 *
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$uid     = 'ar-contact-' . $module->id;
$mapUrl  = $helper->get('map-url');
if (empty($mapUrl)) {
    $mapUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d87388.3241920343!2d28.784565399999997!3d47.010453!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40c97c3628b769a1%3A0xb106659da8f41093!2zQ2hpxZ9pbsSDbywgTW9sZG92YQ!5e0!3m2!1sen!2s!4v1234567890123!5m2!1sen!2s';
}

// Get contact ID from module parameters or use default
$contactId = $helper->get('contact_contact_id', 1);
?>

<style>
/* ═══ AutoRent Contact (contact/style-1) ════════════════════════════════ */
#<?php echo $uid; ?> {
	padding: 48px 20px;
	background: #f9fafb; /* bg-gray-50 */
}
@media (min-width: 640px)  { #<?php echo $uid; ?> { padding: 64px 30px; } }
@media (min-width: 768px)  { #<?php echo $uid; ?> { padding: 80px 40px; } }
@media (min-width: 1024px) { #<?php echo $uid; ?> { padding: 80px 50px; } }

.<?php echo $uid; ?>-inner {
	max-width: 1440px;
	margin: 0 auto;
}

/* Grid */
.<?php echo $uid; ?>-grid {
	display: grid;
	grid-template-columns: 1fr;
	gap: 32px;
}
@media (min-width: 1024px) {
	.<?php echo $uid; ?>-grid {
		grid-template-columns: 1fr 1fr;
		gap: 48px;
	}
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-grid {
		gap: 48px;
	}
}

/* ── Form card ── */
.<?php echo $uid; ?>-form-card {
	background: #fff;
	border-radius: 16px;
	padding: 24px;
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-form-card { padding: 32px; }
}

.<?php echo $uid; ?>-form-title {
	font-size: 1.5rem;
	font-weight: 700;
	color: #0a0a0a;
	margin: 0 0 24px;
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-form-title { font-size: 1.875rem; }
}

/* Two-column row for name + email */
.<?php echo $uid; ?>-row {
	display: grid;
	grid-template-columns: 1fr;
	gap: 24px;
	margin-bottom: 24px;
}
@media (min-width: 768px) {
	.<?php echo $uid; ?>-row { grid-template-columns: 1fr 1fr; }
}

.<?php echo $uid; ?>-field {
	margin-bottom: 0;
}
.<?php echo $uid; ?>-field-single {
	margin-bottom: 24px;
}

.<?php echo $uid; ?>-label {
	display: block;
	font-size: 0.875rem;
	font-weight: 500;
	color: #374151;
	margin-bottom: 8px;
}

.<?php echo $uid; ?>-input,
.<?php echo $uid; ?>-textarea {
	width: 100%;
	padding: 12px 16px;
	border: 1px solid #e5e7eb;
	border-radius: 8px;
	font-size: 1rem;
	color: #0a0a0a;
	background: #fff;
	transition: outline .15s, border-color .15s, box-shadow .15s;
	box-sizing: border-box;
}
.<?php echo $uid; ?>-input:focus,
.<?php echo $uid; ?>-textarea:focus {
	outline: none;
	border-color: transparent;
	box-shadow: 0 0 0 2px #FE5001;
}
.<?php echo $uid; ?>-textarea {
	resize: none;
	min-height: 112px; /* rows="4" */
}

.<?php echo $uid; ?>-submit {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	padding: 14px 16px;
	background: #FE5001;
	color: #fff;
	border: none;
	border-radius: 6px;
	font-size: 0.875rem;
	font-weight: 500;
	cursor: pointer;
	transition: background .2s;
	white-space: nowrap;
}
.<?php echo $uid; ?>-submit:hover {
	background: #E54801;
}

/* Form validation styles */
.<?php echo $uid; ?>-form-group {
	margin-bottom: 24px;
}
.<?php echo $uid; ?>-form-group.required .<?php echo $uid; ?>-label::after {
	content: ' *';
	color: #ef4444;
}

/* ── Map card ── */
.<?php echo $uid; ?>-map-card {
	background: #fff;
	border-radius: 16px;
	overflow: hidden;
	height: 100%;
	min-height: 320px;
}
.<?php echo $uid; ?>-map-card iframe {
	display: block;
	width: 100%;
	height: 100%;
	min-height: 320px;
	border: 0;
}
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<section id="<?php echo $uid; ?>" class="acm-contact style-1">
	<div class="<?php echo $uid; ?>-inner">
		<div class="<?php echo $uid; ?>-grid">

			<!-- Contact Form -->
			<div class="<?php echo $uid; ?>-form-card">
				<h2 class="<?php echo $uid; ?>-form-title">Trimite-ne un mesaj</h2>
				
				<?php
				// Load Joomla's contact form functionality
				HTMLHelper::_('behavior.keepalive');
				HTMLHelper::_('behavior.formvalidator');
				
				// Create form action URL
				$formAction = Route::_('index.php');
				
				// Get the contact form object
				JLoader::import('components.com_contact.models.contact', JPATH_SITE);
				$contactModel = JModelLegacy::getInstance('Contact', 'ContactModel', array('ignore_request' => true));
				$contactModel->setState('contact.id', $contactId);
				$contact = $contactModel->getItem();
				
				// Get the form
				JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
				JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
				$form = $contactModel->getForm();
				
				// Set default values
				$form->setValue('contact_subject', null, 'General Inquiry');
				?>
				
				<form id="contact-form-<?php echo $module->id; ?>" 
				      action="<?php echo $formAction; ?>" 
				      method="post" 
				      class="form-validate form-horizontal"
				      novalidate>
					
					<fieldset>
						<div class="<?php echo $uid; ?>-row">
							<div class="<?php echo $uid; ?>-form-group required">
								<?php echo $form->getLabel('contact_name'); ?>
								<?php echo $form->getInput('contact_name'); ?>
							</div>
							<div class="<?php echo $uid; ?>-form-group required">
								<?php echo $form->getLabel('contact_email'); ?>
								<?php echo $form->getInput('contact_email'); ?>
							</div>
						</div>
						
						<!-- Hidden subject field -->
						<input type="hidden" name="contact_subject" value="General Inquiry">
						
						<div class="<?php echo $uid; ?>-form-group required">
							<?php echo $form->getLabel('contact_message'); ?>
							<?php echo $form->getInput('contact_message'); ?>
						</div>
						
						<?php if ($params->get('show_email_copy')) : ?>
							<div class="<?php echo $uid; ?>-form-group">
								<div class="checkbox">
									<?php echo $form->getInput('contact_email_copy'); ?>
									<?php echo $form->getLabel('contact_email_copy'); ?>
								</div>
							</div>
						<?php endif; ?>
						
						<div class="<?php echo $uid; ?>-form-group">
							<button class="<?php echo $uid; ?>-submit validate" 
							        type="submit">
								<?php echo Text::_('COM_CONTACT_CONTACT_SEND'); ?>
							</button>
						</div>
						
						<!-- Hidden form fields -->
						<input type="hidden" name="option" value="com_contact" />
						<input type="hidden" name="task" value="contact.submit" />
						<input type="hidden" name="return" value="<?php echo base64_encode(JUri::getInstance()->toString()); ?>" />
						<input type="hidden" name="id" value="<?php echo $contactId; ?>" />
						<?php echo HTMLHelper::_('form.token'); ?>
					</fieldset>
				</form>
			</div>

			<!-- Google Maps -->
			<div class="<?php echo $uid; ?>-map-card">
				<iframe
					src="<?php echo htmlspecialchars($mapUrl); ?>"
					width="100%"
					height="100%"
					allowfullscreen=""
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					style="border:0;"></iframe>
			</div>

		</div>
	</div>
</section>
