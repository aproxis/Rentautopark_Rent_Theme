<?php
/**
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

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

if (version_compare(JVERSION, 4, 'ge')) {
	$app = Factory::getApplication();
	$wa = $app->getDocument()->getWebAssetManager();
	$wa->useScript('field.passwordview');
}
?>

<!-- Modern Login Modal -->
<style>
/* Auth Modal */
#ja-login-form {
	display: none;
	position: fixed;
	inset: 0;
	z-index: 99999;
	align-items: center;
	justify-content: center;
	padding: 16px;
}
#ja-login-form.modal-open {
	display: flex;
}
#ja-login-overlay {
	position: absolute;
	inset: 0;
	background: rgba(0,0,0,0.55);
	cursor: pointer;
}
#ja-login-box {
	position: relative;
	z-index: 1;
	width: 100%;
	max-width: 680px;
	background: #fff;
	border-radius: 16px;
	box-shadow: 0 20px 60px rgba(0,0,0,0.25);
	padding: 32px;
	max-height: 90vh;
	overflow-y: auto;
}
#ja-login-box .modal-close-btn {
	position: absolute;
	top: 16px;
	right: 16px;
	background: none;
	border: none;
	cursor: pointer;
	color: #9ca3af;
	padding: 4px;
	line-height: 1;
	border-radius: 6px;
	transition: color .2s, background .2s;
}
#ja-login-box .modal-close-btn:hover {
	color: #ef4444;
	background: #fee2e2;
}
/* Tab nav */
.auth-tab-nav {
	display: flex;
	border-bottom: 2px solid #f3f4f6;
	margin-bottom: 24px;
}
.auth-tab-btn {
	flex: 1;
	padding: 12px 16px;
	font-size: 14px;
	font-weight: 600;
	color: #6b7280;
	background: none;
	border: none;
	border-bottom: 2px solid transparent;
	margin-bottom: -2px;
	cursor: pointer;
	transition: color .2s, border-color .2s;
}
.auth-tab-btn:hover {
	color: #374151;
}
.auth-tab-btn.active-tab {
	color: #FE5001;
	border-bottom-color: #FE5001;
}
/* Tab content */
.auth-tab-content { display: none; }
.auth-tab-content.active { display: block; }
/* Form elements */
.auth-form-title {
	font-size: 1.75rem;
	font-weight: 700;
	color: #0a0a0a;
	text-align: center;
	margin-bottom: 6px;
}
.auth-form-subtitle {
	font-size: 14px;
	color: #6b7280;
	text-align: center;
	margin-bottom: 28px;
}
.auth-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 16px;
}
@media (max-width: 540px) {
	.auth-grid { grid-template-columns: 1fr; }
	#ja-login-box { padding: 20px 16px; }
}
.auth-field { margin-bottom: 0; }
.auth-field label {
	display: block;
	font-size: 13px;
	font-weight: 600;
	color: #374151;
	margin-bottom: 6px;
}
.auth-field .input-wrap {
	position: relative;
}
.auth-field .input-wrap svg.input-icon {
	position: absolute;
	left: 12px;
	top: 50%;
	transform: translateY(-50%);
	width: 18px;
	height: 18px;
	color: #9ca3af;
	pointer-events: none;
}
.auth-field input[type="text"],
.auth-field input[type="password"],
.auth-field input[type="email"] {
	width: 100%;
	padding: 10px 14px;
	border: 2px solid #e5e7eb;
	border-radius: 10px;
	font-size: 14px;
	color: #0a0a0a;
	background: #fff;
	transition: border-color .2s, box-shadow .2s;
	box-sizing: border-box;
}
.auth-field input.has-icon {
	padding-left: 40px;
}
.auth-field input[type="text"]:focus,
.auth-field input[type="password"]:focus,
.auth-field input[type="email"]:focus {
	outline: none;
	border-color: #FE5001;
	box-shadow: 0 0 0 3px rgba(254,80,1,.12);
}
.auth-field .pw-toggle {
	position: absolute;
	right: 12px;
	top: 50%;
	transform: translateY(-50%);
	background: none;
	border: none;
	cursor: pointer;
	color: #9ca3af;
	padding: 0;
	line-height: 1;
}
.auth-field .pw-toggle:hover { color: #374151; }
.auth-remember-row {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin: 16px 0;
}
.auth-remember-row label {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 13px;
	color: #6b7280;
	cursor: pointer;
	font-weight: 400;
}
.auth-remember-row a {
	font-size: 13px;
	color: #FE5001;
	text-decoration: none;
}
.auth-remember-row a:hover { color: #E54801; text-decoration: underline; }
.auth-submit-btn {
	width: 100%;
	background: #FE5001;
	color: #fff;
	border: none;
	border-radius: 10px;
	padding: 12px 24px;
	font-size: 14px;
	font-weight: 700;
	cursor: pointer;
	transition: background .2s;
	margin-top: 20px;
}
.auth-submit-btn:hover { background: #E54801; }
.auth-switch-row {
	text-align: center;
	margin-top: 16px;
	font-size: 13px;
	color: #6b7280;
}
.auth-switch-row button {
	background: none;
	border: none;
	color: #FE5001;
	font-weight: 600;
	cursor: pointer;
	font-size: 13px;
	padding: 0;
}
.auth-switch-row button:hover { color: #E54801; text-decoration: underline; }
.auth-req-note {
	font-size: 12px;
	color: #9ca3af;
	margin-top: 8px;
}
</style>

<div id="ja-login-form">
	<!-- Overlay — click to close -->
	<div id="ja-login-overlay" onclick="closeAuthModal()"></div>

	<!-- Modal box -->
	<div id="ja-login-box">
		<!-- Close button -->
		<button type="button" class="modal-close-btn" onclick="closeAuthModal()" aria-label="Close">
			<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<line x1="18" y1="6" x2="6" y2="18"></line>
				<line x1="6" y1="6" x2="18" y2="18"></line>
			</svg>
		</button>

		<!-- Tab Navigation -->
		<div class="auth-tab-nav">
			<button id="login-tab" class="auth-tab-btn active-tab" onclick="switchTab('login')">
				<?php echo Text::_('TXT_LOGIN'); ?>
			</button>
			<button id="register-tab" class="auth-tab-btn" onclick="switchTab('register')">
				<?php echo Text::_('REGISTER'); ?>
			</button>
		</div>

		<!-- LOGIN TAB -->
		<div id="login-content" class="auth-tab-content active">
			<h2 class="auth-form-title">Bun venit înapoi</h2>
			<p class="auth-form-subtitle">Autentifică-te pentru a continua</p>

			<?php if(PluginHelper::isEnabled('authentication', 'openid')) : ?>
				<?php HTMLHelper::_('script', 'openid.js'); ?>
			<?php endif; ?>

			<form action="<?php echo Route::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="form-login" id="modal-login-form">
				<div class="auth-grid">
					<div class="auth-field">
						<label for="modlgn-username"><?php echo Text::_('JAUSERNAME'); ?></label>
						<div class="input-wrap">
							<svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
								<circle cx="12" cy="7" r="4"></circle>
							</svg>
							<input id="modlgn-username" type="text" name="username" class="has-icon" placeholder="Nume utilizator" autocomplete="username" />
						</div>
					</div>

					<div class="auth-field">
						<label for="modlgn-passwd"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
						<div class="input-wrap">
							<svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
								<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
							</svg>
							<input id="modlgn-passwd" type="password" name="password" class="has-icon" placeholder="••••••••" autocomplete="current-password" />
							<button type="button" class="pw-toggle" onclick="arTogglePw(this)" aria-label="Show/hide password">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
									<circle cx="12" cy="12" r="3"></circle>
								</svg>
							</button>
						</div>
					</div>
				</div>

				<?php if (!is_null($tfa) && $tfa != array()): ?>
				<div class="auth-field" style="margin-top:16px;">
					<label for="secretkey"><?php echo Text::_('JASECRETKEY'); ?></label>
					<div class="input-wrap">
						<input type="text" size="25" value="" id="secretkey" name="secretkey" />
					</div>
				</div>
				<?php endif; ?>

				<?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
				<div class="auth-remember-row">
					<label>
						<input id="modlgn-remember" type="checkbox" name="remember">
						<?php echo Text::_('JAREMEMBER_ME'); ?>
					</label>
					<a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>"><?php echo Text::_('FORGOT_YOUR_PASSWORD'); ?></a>
				</div>
				<?php endif; ?>

				<button class="auth-submit-btn" type="submit"><?php echo Text::_('JABUTTON_LOGIN'); ?></button>

				<div class="auth-switch-row">
					Nu ai cont? <button type="button" onclick="switchTab('register')"><?php echo Text::_('REGISTER'); ?></button>
				</div>

				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="user.login" />
				<input type="hidden" name="return" value="<?php echo $returnLogin; ?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>

		<!-- REGISTER TAB -->
		<div id="register-content" class="auth-tab-content">
			<h2 class="auth-form-title">Creează un cont nou</h2>
			<p class="auth-form-subtitle">Completează datele — parola va fi trimisă pe email</p>

			<?php
			HTMLHelper::_('behavior.keepalive');
			HTMLHelper::_('behavior.formvalidation');
			?>

			<form id="member-registration" action="<?php echo Route::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-validate">
				<div class="auth-grid">
					<div class="auth-field">
						<label for="jform_name"><?php echo Text::_('JANAME'); ?> <span style="color:#ef4444">*</span></label>
						<div class="input-wrap">
							<input type="text" size="30" class="required" value="" id="jform_name" name="jform[name]" autocomplete="name" />
						</div>
					</div>

					<div class="auth-field">
						<label for="jform_username"><?php echo Text::_('JAUSERNAME'); ?> <span style="color:#ef4444">*</span></label>
						<div class="input-wrap">
							<input type="text" size="30" class="validate-username required" value="" id="jform_username" name="jform[username]" autocomplete="username" />
						</div>
					</div>

					<div class="auth-field">
						<label for="jform_email1"><?php echo Text::_('JAEMAIL'); ?> <span style="color:#ef4444">*</span></label>
						<div class="input-wrap">
							<input type="text" size="30" class="validate-email required" value="" id="jform_email1" name="jform[email1]" autocomplete="email" />
						</div>
					</div>

					<!-- <div class="auth-field">
						<label for="jform_email2"><?php echo Text::_('JACONFIRM_EMAIL_ADDRESS'); ?> <span style="color:#ef4444">*</span></label>
						<div class="input-wrap">
							<input type="text" size="30" class="validate-email required" value="" id="jform_email2" name="jform[email2]" autocomplete="email" />
						</div>
					</div> -->

				</div>
				
				<input type="hidden" name="jform[password1]" value="" />
				<input type="hidden" name="jform[password2]" value="" />


				<?php if(!empty($captchatext)): ?>
				<div class="auth-field" style="margin-top:16px;">
					<label for="jform_captcha"><?php echo Text::_('JACAPTCHA'); ?> <span style="color:#ef4444">*</span></label>
					<?php echo $captchatext; ?>
				</div>
				<?php endif; ?>

				<?php
				$privacy = PluginHelper::getPlugin('system', 'privacyconsent');
				if (!empty($privacy)) {
					FormHelper::addFieldPath(JPATH_SITE . '/plugins/system/privacyconsent/field');
					Form::addFormPath(JPATH_SITE . '/plugins/system/privacyconsent/privacyconsent');
					$form2 = new Form('jform');
					$form2->loadFile('privacyconsent');
					$fields = $form2->getFieldset('privacyconsent');
					$params = new Registry($privacy->params);
					$privacyArticleId = $params->get('privacy_article');
					$privacynote      = $params->get('privacy_note');
					$form2->setFieldAttribute('privacy', 'article', $privacyArticleId, 'privacyconsent');
					$form2->setFieldAttribute('privacy', 'note', $privacynote, 'privacyconsent');
					foreach ($fields as $kf => $field) {
						echo str_replace('privacyconsent[privacy]', 'jform[privacyconsent][privacy]', $field->renderField());
					}
				}
				?>

				<?php
				$term = PluginHelper::getPlugin('user', 'terms');
				if (!empty($term)) {
					FormHelper::addFieldPath(JPATH_SITE . '/plugins/user/terms/field');
					Form::addFormPath(JPATH_SITE . '/plugins/user/terms/terms');
					$form3 = new Form('jform');
					$form3->loadFile('terms');
					$fields = $form3->getFieldset('terms');
					$params = new Registry($term->params);
					$termsarticle = $params->get('terms_article');
					$termsnote    = $params->get('terms_note');
					$form3->setFieldAttribute('terms', 'article', $termsarticle, 'terms');
					$form3->setFieldAttribute('terms', 'note', $termsnote, 'terms');
					foreach ($fields as $kf => $field) {
						echo str_replace('terms[terms]', 'jform[terms][terms]', $field->renderField());
					}
				}
				?>

				<p class="auth-req-note"><?php echo Text::_('DESC_REQUIREMENT'); ?></p>

				<button type="submit" class="auth-submit-btn validate"><?php echo Text::_('JAREGISTER'); ?></button>

				<div class="auth-switch-row">
					Ai deja cont? <button type="button" onclick="switchTab('login')"><?php echo Text::_('TXT_LOGIN'); ?></button>
				</div>

				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="registration.register" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
	</div><!-- /#ja-login-box -->
</div><!-- /#ja-login-form -->

<script>
// ============================================================
// Auth Modal — open / close / tab switching
// ============================================================
function openAuthModal(tab) {
	var modal = document.getElementById('ja-login-form');
	if (!modal) return;
	modal.classList.add('modal-open');
	document.body.style.overflow = 'hidden';
	switchTab(tab || 'login');
}

function closeAuthModal() {
	var modal = document.getElementById('ja-login-form');
	if (!modal) return;
	modal.classList.remove('modal-open');
	document.body.style.overflow = '';
}

function switchTab(tab) {
	var loginContent  = document.getElementById('login-content');
	var registerContent = document.getElementById('register-content');
	var loginTab      = document.getElementById('login-tab');
	var registerTab   = document.getElementById('register-tab');
	if (!loginContent || !registerContent) return;

	if (tab === 'register') {
		loginContent.classList.remove('active');
		registerContent.classList.add('active');
		if (loginTab)    loginTab.classList.remove('active-tab');
		if (registerTab) registerTab.classList.add('active-tab');
	} else {
		registerContent.classList.remove('active');
		loginContent.classList.add('active');
		if (registerTab) registerTab.classList.remove('active-tab');
		if (loginTab)    loginTab.classList.add('active-tab');
	}
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
	if (e.key === 'Escape') closeAuthModal();
});

// Override login return URL with current page on submit (preserves language prefix)
// (function () {
// 	var form = document.getElementById('login-form');
// 	if (form) {
// 		form.addEventListener('submit', function () {
// 			var returnField = form.querySelector('input[name="return"]');
// 			if (returnField) {
// 				// Get current URL and preserve language context
// 				var currentUrl = window.location.href;
// 				var currentPath = window.location.pathname;
				
// 				// Extract language from URL path (e.g., /ru/, /en/, /ro/)
// 				var langMatch = currentPath.match(/^\/(ru|en|ro)(\/|$)/);
// 				var currentLang = langMatch ? langMatch[1] : '';
				
// 				// If we're on a language-specific page, ensure the return URL includes the language
// 				if (currentLang && !currentUrl.includes('/' + currentLang + '/')) {
// 					// Add language prefix to the return URL
// 					var baseUrl = window.location.origin;
// 					var pathWithoutLang = currentPath.replace(/^\/(ru|en|ro)\//, '/');
// 					var returnUrl = baseUrl + '/' + currentLang + pathWithoutLang + window.location.search + window.location.hash;
// 				} else {
// 					// Use current URL as is
// 					var returnUrl = currentUrl;
// 				}
				
// 				// Encode the return URL
// 				returnField.value = btoa(unescape(encodeURIComponent(returnUrl)));
// 			}
// 		});
// 	}
// })();

// Update login return URL to current page on submit
(function() {
	var form = document.getElementById('modal-login-form');
	if (form) {
		form.addEventListener('submit', function () {
			var returnField = form.querySelector('input[name="return"]');
			if (returnField) returnField.value = btoa(window.location.href);
		});
	}
})();

// Account dropdown toggle (logged-in state)
function arToggleAccountMenu(btn) {
	var wrap = btn.closest('.ar-account-wrap');
	if (!wrap) return;
	var isOpen = wrap.classList.contains('open');
	// Close all other open dropdowns first
	document.querySelectorAll('.ar-account-wrap.open').forEach(function(el) { el.classList.remove('open'); });
	if (!isOpen) {
		wrap.classList.add('open');
		btn.setAttribute('aria-expanded', 'true');
	}
}
// Close account dropdown on outside click
document.addEventListener('click', function(e) {
	if (!e.target.closest('.ar-account-wrap')) {
		document.querySelectorAll('.ar-account-wrap.open').forEach(function(el) { el.classList.remove('open'); });
	}
});

// Password toggle
function arTogglePw(btn) {
	var input = btn.parentElement.querySelector('input');
	if (!input) return;
	if (input.type === 'password') {
		input.type = 'text';
		btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
	} else {
		input.type = 'password';
		btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg>';
	}
}
</script>

<!-- Auth output — single "My Account" button -->
<?php
// Login:  redirect back to current page (preserves language prefix /ru, /ro, /en)
$returnLogin  = base64_encode(Uri::current());
// Logout: always go to homepage — avoids landing on ?view=login URLs
$returnLogout = base64_encode(Uri::root());
?>

<?php if($type == 'logout') : ?>
	<?php
	// Show first name only for brevity
	$displayName = ($params->get('name') == 0)
		? htmlspecialchars($user->get('username'))
		: htmlspecialchars(explode(' ', trim($user->get('name')))[0]);
	?>
	<div class="ar-account-wrap">
		<button class="ar-account-btn" onclick="arToggleAccountMenu(this)" type="button" aria-haspopup="true" aria-expanded="false">
			<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			<span class="ar-account-name"><?php echo $displayName; ?></span>
			<svg class="ar-account-caret" xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
		</button>
		<div class="ar-account-dropdown" role="menu">
			<form action="<?php echo Route::_('index.php', true, $params->get('usesecure')); ?>" method="post">
				<button type="submit" class="ar-account-logout-btn">
					<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
					<?php echo Text::_('JLOGOUT'); ?>
				</button>
				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="user.logout" />
				<input type="hidden" name="return" value="<?php echo $returnLogout; ?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
	</div>

<?php else : ?>

	<button class="ar-myaccount-btn" onclick="openAuthModal('login')" type="button">
		<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
		<span>My Account</span>
	</button>

	<!-- Hidden login form (used by JS to set the return URL on submit) -->
	<form action="<?php echo Route::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" style="display:none;">
		<input type="hidden" name="username" />
		<input type="hidden" name="password" />
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo $returnLogin; ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>

<?php endif; ?>

<style>
/* ── My Account button (logged-out) ──────────────────────────── */
.ar-myaccount-btn {
	display: inline-flex;
	align-items: center;
	gap: 7px;
	padding: 8px 16px;
	font-size: 13px;
	font-weight: 700;
	color: #fff;
	background: #FE5001;
	border: 2px solid #FE5001;
	border-radius: 8px;
	cursor: pointer;
	transition: background .2s, box-shadow .2s;
	white-space: nowrap;
	line-height: 1;
}
.ar-myaccount-btn:hover {
	background: #E54801;
	border-color: #E54801;
	box-shadow: 0 4px 12px rgba(254,80,1,.28);
}

/* ── Account dropdown (logged-in) ────────────────────────────── */
.ar-account-wrap {
	position: relative;
}
.ar-account-btn {
	display: inline-flex;
	align-items: center;
	gap: 7px;
	padding: 8px 14px;
	font-size: 13px;
	font-weight: 600;
	color: #374151;
	background: #f3f4f6;
	border: 1.5px solid #e5e7eb;
	border-radius: 8px;
	cursor: pointer;
	transition: border-color .2s, background .2s, color .2s;
	white-space: nowrap;
	line-height: 1;
}
.ar-account-btn:hover,
.ar-account-wrap.open .ar-account-btn {
	border-color: #FE5001;
	color: #FE5001;
	background: rgba(254,80,1,.04);
}
.ar-account-caret {
	transition: transform .2s;
	flex-shrink: 0;
}
.ar-account-wrap.open .ar-account-caret {
	transform: rotate(180deg);
}
.ar-account-dropdown {
	display: none;
	position: absolute;
	top: calc(100% + 6px);
	right: 0;
	min-width: 150px;
	background: #fff;
	border: 1.5px solid #e5e7eb;
	border-radius: 10px;
	box-shadow: 0 8px 24px rgba(0,0,0,.1);
	padding: 6px;
	z-index: 1050;
}
.ar-account-wrap.open .ar-account-dropdown {
	display: block;
	animation: arDropIn .15s ease;
}
@keyframes arDropIn {
	from { opacity: 0; transform: translateY(-4px); }
	to   { opacity: 1; transform: translateY(0); }
}
.ar-account-logout-btn {
	display: flex;
	align-items: center;
	gap: 8px;
	width: 100%;
	padding: 9px 12px;
	font-size: 13px;
	font-weight: 500;
	color: #ef4444;
	background: none;
	border: none;
	border-radius: 6px;
	cursor: pointer;
	text-align: left;
	transition: background .15s;
	white-space: nowrap;
}
.ar-account-logout-btn:hover {
	background: #fee2e2;
}
</style>