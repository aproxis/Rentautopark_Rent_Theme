<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Contact Style 1: Contact Form & Map
 * ------------------------------------------------------------------------
 * ACM layout: Split layout with contact form (left) and Google Maps iframe (right).
 * Matches the AutoRent React design system exactly.
 *
 * Extra Fields used:
 *   map-url   (Google Maps embed URL)
 *
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$uid     = 'ar-contact-' . $module->id;
$mapUrl  = $helper->get('map-url');
if (empty($mapUrl)) {
    $mapUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d87388.3241920343!2d28.784565399999997!3d47.010453!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40c97c3628b769a1%3A0xb106659da8f41093!2zQ2hpxZ9pbsSDbywgTW9sZG92YQ!5e0!3m2!1sen!2s!4v1234567890123!5m2!1sen!2s';
}
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

/* Two-column row for name + phone */
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
				<form action="#" method="post">
					<div class="<?php echo $uid; ?>-row">
						<div class="<?php echo $uid; ?>-field">
							<label class="<?php echo $uid; ?>-label">Nume complet</label>
							<input type="text" name="name" required
							       class="<?php echo $uid; ?>-input"
							       placeholder="Ion Popescu">
						</div>
						<div class="<?php echo $uid; ?>-field">
							<label class="<?php echo $uid; ?>-label">Telefon</label>
							<input type="tel" name="phone" required
							       class="<?php echo $uid; ?>-input"
							       placeholder="+373 68001155">
						</div>
					</div>
					<div class="<?php echo $uid; ?>-field-single">
						<label class="<?php echo $uid; ?>-label">Mesaj</label>
						<textarea name="message" required rows="4"
						          class="<?php echo $uid; ?>-textarea"
						          placeholder="Cum te putem ajuta?"></textarea>
					</div>
					<button type="submit" class="<?php echo $uid; ?>-submit">Trimite mesaj</button>
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
