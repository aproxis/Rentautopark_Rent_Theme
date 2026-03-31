<?php
/**
 * Template override: templates/rent/html/com_users/reset/confirm.php
 * Step 2 of J5 password reset.
 *
 * J5 processResetConfirm() needs:
 *   jform[username] — looked up via AJAX from the email the user enters
 *   jform[token]    — the plain token from the URL ?token= param
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

// Plain token from URL — submitted as jform[token] for bcrypt verification in processResetConfirm()
$urlToken        = Factory::getApplication()->input->getString('token', '');
$lookupUrl       = Uri::root() . 'templates/rent/php/lookup-username.php';
?>

<div class="reset-page">
<div class="reset-container">

    <div class="page-header">
        <h1><?php echo Text::_('COM_USERS_RESET_CONFIRM_TITLE'); ?></h1>
    </div>

    <form action="<?php echo Route::_('index.php?option=com_users'); ?>"
          method="post"
          id="user-reset-confirm-form">

        <fieldset>
            <p><?php echo Text::_('COM_USERS_RESET_CONFIRM_LABEL'); ?></p>

            <!-- Visible: email field (used only for username lookup, not submitted) -->
            <div class="form-group">
                <div class="control-label">
                    <label for="reset-confirm-email"><?php echo Text::_('JGLOBAL_EMAIL'); ?></label>
                </div>
                <div class="controls">
                    <input type="email"
                           id="reset-confirm-email"
                           class="form-control"
                           placeholder="your@email.com"
                           autocomplete="email"
                           required>
                </div>
            </div>

            <!-- Error message (shown by JS) -->
            <div id="reset-confirm-error"
                 style="display:none;margin:8px 0 0;padding:8px 12px;background:rgba(220,38,38,.06);
                        border:1px solid rgba(220,38,38,.22);border-radius:8px;
                        color:#b91c1c;font-size:13px;line-height:1.5;"></div>
        </fieldset>

        <!-- Hidden: username resolved via AJAX before submit -->
        <input type="hidden" id="jform-username" name="jform[username]" value="">

        <!-- Hidden: plain token from URL — J5 verifies this with UserHelper::verifyPassword() -->
        <input type="hidden" name="jform[token]"
               value="<?php echo htmlspecialchars($urlToken, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="form-actions">
            <button type="submit" id="reset-confirm-btn" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 11 12 14 22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                <?php echo Text::_('COM_USERS_RESET_CONFIRM_BUTTON'); ?>
            </button>
            <a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>"
               class="btn btn-danger">
                <?php echo Text::_('JCANCEL'); ?>
            </a>
        </div>

        <input type="hidden" name="option" value="com_users">
        <input type="hidden" name="task"   value="reset.confirm">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

</div>
</div>

<script>
(function () {
    'use strict';

    var form     = document.getElementById('user-reset-confirm-form');
    var emailEl  = document.getElementById('reset-confirm-email');
    var usernameEl = document.getElementById('jform-username');
    var errEl    = document.getElementById('reset-confirm-error');
    var btn      = document.getElementById('reset-confirm-btn');
    var LOOKUP   = <?php echo json_encode($lookupUrl); ?>;

    function showError(msg) {
        errEl.textContent = msg;
        errEl.style.display = 'block';
    }

    function clearError() {
        errEl.style.display = 'none';
        errEl.textContent   = '';
    }

    form.addEventListener('submit', function onSubmit(e) {
        e.preventDefault();
        clearError();

        var email = emailEl.value.trim();
        if (!email || !/[^@\s]+@[^@\s]+\.[a-zA-Z]{2,}/.test(email)) {
            showError(<?php echo json_encode(Text::_('COM_USERS_RESET_INVALID_EMAIL')); ?>);
            emailEl.focus();
            return;
        }

        btn.disabled     = true;
        btn.textContent  = <?php echo json_encode(Text::_('JLOADING')); ?>;

        fetch(LOOKUP, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            btn.disabled    = false;
            btn.textContent = <?php echo json_encode(Text::_('COM_USERS_RESET_CONFIRM_BUTTON')); ?>;

            if (res.ok && res.username) {
                usernameEl.value = res.username;
                // Remove this listener so the next submit goes through natively
                form.removeEventListener('submit', onSubmit);
                form.submit();
            } else {
                showError(res.error || <?php echo json_encode(Text::_('COM_USERS_INVALID_EMAIL')); ?>);
                emailEl.focus();
            }
        })
        .catch(function () {
            btn.disabled    = false;
            btn.textContent = <?php echo json_encode(Text::_('COM_USERS_RESET_CONFIRM_BUTTON')); ?>;
            showError('Network error. Please try again.');
        });
    });
}());
</script>