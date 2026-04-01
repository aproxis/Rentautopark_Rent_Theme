<?php
/**
 * Template override: templates/rent/html/com_users/reset/complete.php
 * Step 3 — user sets new password after clicking reset link.
 * J5 processResetComplete() handles the form submission.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
   ->useScript('form.validate');

$doc = Factory::getDocument();
$doc->addStyleSheet(Uri::root() . 'templates/rent/css/reset-styles.css');
?>

<div class="reset-page">
<div class="reset-container">

    <div class="page-header">
        <h1><?php echo Text::_('COM_USERS_RESET_COMPLETE_TITLE'); ?></h1>
    </div>

    <form action="<?php echo Route::_('index.php?option=com_users&task=reset.complete'); ?>"
          method="post"
          id="user-reset-complete-form"
          class="form-validate">

        <fieldset>
            <p><?php echo Text::_('COM_USERS_RESET_COMPLETE_TEXT'); ?></p>

            <!-- Password field -->
            <div class="form-group">
                <div class="control-label">
                    <label for="jform_password1" class="required">
                        <?php echo Text::_('COM_USERS_PROFILE_PASSWORD1_LABEL'); ?>
                        <span class="star" aria-hidden="true">&nbsp;*</span>
                    </label>
                </div>
                <div class="controls">
                    <div class="password-group">
                        <div class="input-group">
                            <input type="password"
                                   name="jform[password1]"
                                   id="jform_password1"
                                   value=""
                                   autocomplete="new-password"
                                   class="form-control js-password-strength validate-password required"
                                   aria-describedby="jform_password1_rules"
                                   size="30"
                                   maxlength="99"
                                   required
                                   data-min-length="8">
                            <button type="button" class="btn btn-secondary input-password-toggle" aria-label="<?php echo Text::_('JSHOWPASSWORD'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-eye">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div id="jform_password1_rules" class="small text-muted">
                            <?php echo Text::_('COM_USERS_PROFILE_PASSWORD1_DESC'); ?>
                        </div>
                        <meter id="password-strength-meter" min="0" max="100" low="40" high="99" optimum="100" value="0"></meter>
                        <div class="text-center password-strength-text" id="password-strength-text" aria-live="polite"></div>
                    </div>
                </div>
            </div>

            <!-- Confirm password field -->
            <div class="form-group">
                <div class="control-label">
                    <label for="jform_password2" class="required">
                        <?php echo Text::_('COM_USERS_PROFILE_PASSWORD2_LABEL'); ?>
                        <span class="star" aria-hidden="true">&nbsp;*</span>
                    </label>
                </div>
                <div class="controls">
                    <div class="password-group">
                        <div class="input-group">
                            <input type="password"
                                   name="jform[password2]"
                                   id="jform_password2"
                                   value=""
                                   autocomplete="new-password"
                                   class="form-control validate-password required"
                                   size="30"
                                   maxlength="99"
                                   required
                                   data-min-length="8">
                            <button type="button" class="btn btn-secondary input-password-toggle" aria-label="<?php echo Text::_('JSHOWPASSWORD'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-eye">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?php echo Text::_('COM_USERS_RESET_COMPLETE_BUTTON'); ?>
            </button>
        </div>

        <input type="hidden" name="option" value="com_users">
        <input type="hidden" name="task" value="reset.complete">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

</div>
</div>

<script>
(function () {
    'use strict';

    var form    = document.getElementById('user-reset-complete-form');
    var btn     = form ? form.querySelector('[type="submit"]') : null;
    var SITEURL = <?php echo json_encode(Uri::root()); ?>;

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // ── 1. Basic client-side validation ──────────────────────────────
        var p1 = form.querySelector('[name="jform[password1]"]');
        var p2 = form.querySelector('[name="jform[password2]"]');
        if (!p1 || !p2) { form.submit(); return; }   // fallback

        if (p1.value.trim() === '') {
            p1.focus();
            return;
        }
        if (p1.value !== p2.value) {
            p2.focus();
            return;
        }

        var plainPassword = p1.value;

        if (btn) { btn.disabled = true; }

        var fd = new FormData(form);

        // ── 2. Submit reset.complete to Joomla ────────────────────────────
        fetch(SITEURL + 'index.php?option=com_users', {
            method:      'POST',
            body:        fd,
            credentials: 'same-origin',
            redirect:    'manual'   // stop fetch following the redirect
        })
        .then(function () {
            // ── 3. Auto-login with the new password ───────────────────────
            // Build a Joomla login POST using the email as username
            // (Email authentication plugin converts email → username internally)
            var email = <?php
                // Read email from session set by processResetConfirm()
                $resetUserId = (int) Factory::getApplication()
                    ->getUserState('com_users.reset.user', 0);
                $resetEmail  = '';
                if ($resetUserId > 0) {
                    try {
                        $u = Factory::getUser($resetUserId);
                        if ($u && $u->id > 0) $resetEmail = $u->email;
                    } catch (\Exception $e) {}
                }
                echo json_encode($resetEmail);
            ?>;

            if (!email) {
                // No session email — redirect to homepage
                window.location.href = SITEURL;
                return;
            }

            var loginData = new URLSearchParams();
            loginData.append('username',   email);
            loginData.append('password',   plainPassword);
            loginData.append('option',     'com_users');
            loginData.append('task',       'user.login');
            loginData.append('return',     btoa(SITEURL));   // base64-encoded return URL

            // Get J-token from a meta tag Joomla injects (or from the form itself)
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (tokenMeta) {
                loginData.append(tokenMeta.getAttribute('content'), '1');
            } else {
                // Fallback: find the hidden CSRF input in our form
                var csrfInput = form.querySelector('input[type="hidden"]:not([name^="jform"])');
                if (csrfInput) loginData.append(csrfInput.name, '1');
            }

            return fetch(SITEURL + 'index.php', {
                method:      'POST',
                body:        loginData,
                credentials: 'same-origin',
                redirect:    'manual'
            });
        })
        .then(function () {
            // Redirect to homepage after successful login
            window.location.href = SITEURL;
        })
        .catch(function () {
            // Network error — redirect to homepage
            window.location.href = SITEURL;
        });
    });

}());
</script>
