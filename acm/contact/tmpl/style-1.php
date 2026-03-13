<?php
/**
 * ------------------------------------------------------------------------
 * JA Rent template - AutoRent Contact Style 1: Contact Form & Map
 * ------------------------------------------------------------------------
 * ACM layout: Split layout with contact form and information display.
 * Clean design with icons, consistent with AutoRent design system.
 *
 * Extra Fields used:
 *   contact-item.icon     (icon class, e.g. "fa fa-map-marker")
 *   contact-item.label    (contact label)
 *   contact-item.value    (contact value)
 *
 * Background: Via JA Extra Fields background image support
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;

$count = $helper->getRows('contact-item.label');
$uid   = 'ar-contact-' . $module->id;
?>

<style>
/* ═══ AutoRent Contact (contact/style-1) ════════════════════════════════ */
#<?php echo $uid; ?> {
	padding: 40px 20px;
}
.<?php echo $uid; ?>-container {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 32px;
	align-items: start;
}
.<?php echo $uid; ?>-form-section {
	background: #fff;
	border-radius: 16px;
	padding: 32px;
	box-shadow: 0 2px 16px rgba(0,0,0,.06);
	border: 1px solid rgba(0,0,0,.05);
}
.<?php echo $uid; ?>-form-title {
	font-size: 1.4rem;
	font-weight: 700;
	color: #0a0a0a;
	margin: 0 0 20px;
}
.<?php echo $uid; ?>-form-group {
	margin-bottom: 16px;
}
.<?php echo $uid; ?>-form-group label {
	display: block;
	font-size: .9rem;
	color: #555;
	margin-bottom: 6px;
	font-weight: 600;
}
.<?php echo $uid; ?>-form-input,
.<?php echo $uid; ?>-form-textarea {
	width: 100%;
	padding: 12px 14px;
	border: 2px solid #e5e7eb;
	border-radius: 8px;
	font-size: 1rem;
	color: #0a0a0a;
	transition: border-color .2s, box-shadow .2s;
	background: #fff;
}
.<?php echo $uid; ?>-form-input:focus,
.<?php echo $uid; ?>-form-textarea:focus {
	outline: none;
	border-color: #FE5001;
	box-shadow: 0 0 0 3px rgba(254,80,1,.12);
}
.<?php echo $uid; ?>-form-textarea {
	min-height: 120px;
	resize: vertical;
}
.<?php echo $uid; ?>-form-submit {
	background: #FE5001;
	color: #fff;
	border: none;
	padding: 12px 24px;
	border-radius: 8px;
	font-size: 1rem;
	font-weight: 700;
	cursor: pointer;
	transition: background .2s, transform .1s;
	width: 100%;
}
.<?php echo $uid; ?>-form-submit:hover {
	background: #E54801;
	transform: translateY(-1px);
}
.<?php echo $uid; ?>-info-section {
	background: #fff;
	border-radius: 16px;
	padding: 32px;
	box-shadow: 0 2px 16px rgba(0,0,0,.06);
	border: 1px solid rgba(0,0,0,.05);
}
.<?php echo $uid; ?>-info-title {
	font-size: 1.4rem;
	font-weight: 700;
	color: #0a0a0a;
	margin: 0 0 24px;
}
.<?php echo $uid; ?>-contact-list {
	display: flex;
	flex-direction: column;
	gap: 16px;
}
.<?php echo $uid; ?>-contact-item {
	display: flex;
	align-items: flex-start;
	gap: 16px;
	padding: 16px;
	background: #f8f9fa;
	border-radius: 12px;
	transition: background .2s;
}
.<?php echo $uid; ?>-contact-item:hover {
	background: #e9ecef;
}
.<?php echo $uid; ?>-contact-icon {
	flex: 0 0 48px;
	width: 48px;
	height: 48px;
	border-radius: 12px;
	background: #fff;
	border: 2px solid #FE5001;
	color: #FE5001;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.2rem;
	box-shadow: 0 4px 12px rgba(254,80,1,.25);
	flex-shrink: 0;
}
.<?php echo $uid; ?>-contact-content { flex: 1 1 auto; }
.<?php echo $uid; ?>-contact-label {
	font-size: .85rem;
	color: #829eaf;
	font-weight: 600;
	margin-bottom: 4px;
	text-transform: uppercase;
	letter-spacing: .05em;
}
.<?php echo $uid; ?>-contact-value {
	font-size: 1rem;
	color: #0a0a0a;
	line-height: 1.5;
}
.<?php echo $uid; ?>-map-section {
	margin-top: 24px;
	background: #f8f9fa;
	border-radius: 12px;
	padding: 16px;
	min-height: 200px;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #666;
	border: 2px dashed #e5e7eb;
}
.<?php echo $uid; ?>-map-placeholder {
	text-align: center;
}
.<?php echo $uid; ?>-map-placeholder i {
	font-size: 2rem;
	color: #FE5001;
	margin-bottom: 8px;
	display: block;
}
.<?php echo $uid; ?>-map-placeholder p {
	margin: 0;
	font-size: .95rem;
}

/* Responsive */
@media (max-width: 768px) {
	.<?php echo $uid; ?>-container {
		grid-template-columns: 1fr;
		gap: 24px;
	}
	.<?php echo $uid; ?>-form-section,
	.<?php echo $uid; ?>-info-section {
		padding: 24px;
	}
	.<?php echo $uid; ?>-form-title,
	.<?php echo $uid; ?>-info-title {
		font-size: 1.2rem;
	}
	.<?php echo $uid; ?>-contact-item {
		padding: 12px;
	}
	.<?php echo $uid; ?>-contact-icon {
		flex: 0 0 40px;
		width: 40px;
		height: 40px;
		font-size: 1.1rem;
	}
}
/* ══════════════════════════════════════════════════════════════════════ */
</style>

<div id="<?php echo $uid; ?>" class="acm-contact style-1">
	<div class="<?php echo $uid; ?>-container">
		<div class="<?php echo $uid; ?>-form-section">
			<h2 class="<?php echo $uid; ?>-form-title">Contactează-ne</h2>
			<form action="#" method="post" class="<?php echo $uid; ?>-form">
				<div class="<?php echo $uid; ?>-form-group">
					<label for="<?php echo $uid; ?>-name">Nume</label>
					<input type="text" id="<?php echo $uid; ?>-name" name="name" class="<?php echo $uid; ?>-form-input" required>
				</div>
				<div class="<?php echo $uid; ?>-form-group">
					<label for="<?php echo $uid; ?>-email">Email</label>
					<input type="email" id="<?php echo $uid; ?>-email" name="email" class="<?php echo $uid; ?>-form-input" required>
				</div>
				<div class="<?php echo $uid; ?>-form-group">
					<label for="<?php echo $uid; ?>-phone">Telefon</label>
					<input type="tel" id="<?php echo $uid; ?>-phone" name="phone" class="<?php echo $uid; ?>-form-input">
				</div>
				<div class="<?php echo $uid; ?>-form-group">
					<label for="<?php echo $uid; ?>-message">Mesaj</label>
					<textarea id="<?php echo $uid; ?>-message" name="message" class="<?php echo $uid; ?>-form-textarea" required></textarea>
				</div>
				<button type="submit" class="<?php echo $uid; ?>-form-submit">Trimite Mesajul</button>
			</form>
		</div>
		
		<div class="<?php echo $uid; ?>-info-section">
			<h2 class="<?php echo $uid; ?>-info-title">Informații de Contact</h2>
			<div class="<?php echo $uid; ?>-contact-list">
				<?php for ($i = 0; $i < $count; $i++): 
					$icon = $helper->get('contact-item.icon', $i);
					$label = $helper->get('contact-item.label', $i);
					$value = $helper->get('contact-item.value', $i);
					if (empty($label) && empty($value)) continue;
				?>
				<div class="<?php echo $uid; ?>-contact-item">
					<div class="<?php echo $uid; ?>-contact-icon">
						<?php if (!empty($icon)): ?>
						<i class="<?php echo htmlspecialchars($icon); ?>"></i>
						<?php else: ?>
						<i class="fa fa-info-circle"></i>
						<?php endif; ?>
					</div>
					<div class="<?php echo $uid; ?>-contact-content">
						<?php if (!empty($label)): ?>
						<div class="<?php echo $uid; ?>-contact-label"><?php echo $label; ?></div>
						<?php endif; ?>
						<?php if (!empty($value)): ?>
						<div class="<?php echo $uid; ?>-contact-value"><?php echo $value; ?></div>
						<?php endif; ?>
					</div>
				</div>
				<?php endfor; ?>
			</div>
			
			<div class="<?php echo $uid; ?>-map-section">
				<div class="<?php echo $uid; ?>-map-placeholder">
					<i class="fa fa-map-marker"></i>
					<p>Harta de locație va fi integrată aici</p>
				</div>
			</div>
		</div>
	</div>
</div>