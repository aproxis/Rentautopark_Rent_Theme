<?php
/*
 * ------------------------------------------------------------------------
 * JA Rent template - Profile Edit Modal
 * AutoRent Figma Design — v3
 * Styles moved to: css/profile-edit.css
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$user = Factory::getUser();
$currentUser = $user->id == $this->data->id;

if (!$currentUser) {
    return;
}

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');

// Load shared profile-edit styles
$doc = Factory::getDocument();
$doc->addStyleSheet(Uri::root() . 'templates/rent/css/profile-edit.css');

// ── AJAX endpoint URLs ────────────────────────────────────────────────────
$getProfileUrl = Uri::root() . 'templates/rent/php/get-customer-profile.php';
$saveProfileUrl = Uri::root() . 'templates/rent/php/save-customer-profile.php';
?>
    <form id="member-profile-edit" action="<?php echo Uri::base(); ?>index.php" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

    <!-- ════════════════════════════════════════════════════════════════════
         VIKRENTCAR CUSTOMER PROFILE FIELDS
         Saved to #__vikrentcar_customers via AJAX
         ════════════════════════════════════════════════════════════════════ -->
    <div class="vrc-profile-section">
        <h4 class="vrc-profile-section-title"><?php echo Text::_('VRC_PERSONAL_DATA') ?: 'Date personale'; ?></h4>

        <div class="form-group">
            <label class="control-label" for="vrc-first-name"><?php echo Text::_('VRC_FIRST_NAME') ?: 'Prenume'; ?> <span class="star">&nbsp;*</span></label>
            <div class="controls">
                <input type="text" id="vrc-first-name" class="inputbox" value="" placeholder="Prenume" />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label" for="vrc-last-name"><?php echo Text::_('VRC_LAST_NAME') ?: 'Nume'; ?> <span class="star">&nbsp;*</span></label>
            <div class="controls">
                <input type="text" id="vrc-last-name" class="inputbox" value="" placeholder="Nume" />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label" for="vrc-phone"><?php echo Text::_('VRC_PHONE') ?: 'Telefon'; ?> <span class="star">&nbsp;*</span></label>
            <div class="controls">
                <input type="tel" id="vrc-phone" class="inputbox" value="" placeholder="+373XXXXXXXX" />
            </div>
        </div>

        <div id="vrc-profile-feedback" style="display:none;margin-bottom:12px;padding:8px 12px;border-radius:6px;font-size:13px;"></div>

        <button type="button" id="vrc-save-profile-btn" class="btn btn-primary" style="margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;">
                <path d="M20 6L9 17l-5-5"></path>
            </svg>
            <span><?php echo Text::_('VRC_SAVE_PROFILE') ?: 'Salvează datele'; ?></span>
        </button>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════
         PASSWORD CHANGE SECTION
         Saved to #__users via standard Joomla form submit
         ════════════════════════════════════════════════════════════════════ -->
    <div class="vrc-profile-section" style="margin-top:24px;">
        <h4 class="vrc-profile-section-title"><?php echo Text::_('VRC_CHANGE_PASSWORD') ?: 'Schimbare parolă'; ?></h4>

    <?php foreach ($this->form->getFieldsets() as $group => $fieldset): ?>
        <?php $fields = $this->form->getFieldset($group); ?>
        <?php if (count($fields)): ?>
            <?php foreach ($fields as $field): ?>
                <?php if ($field->hidden): ?>
                    <div class="form-group" style="display:none;">
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php elseif (strpos($field->fieldname, 'password') !== false): ?>
                    <div class="form-group">
                        <label class="control-label" for="<?php echo $field->id; ?>">
                            <?php echo $field->label; ?>
                        </label>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php
                    // Joomla stores email as ->email, but the form field is email1/email2
                    $fieldname = $field->fieldname;
                    if (in_array($fieldname, ['email1', 'email2'])) {
                        $value = $this->data->email ?? '';
                    } elseif ($fieldname === 'name' || $fieldname === 'username') {
                        $value = $this->data->{$fieldname} ?? '';
                    } else {
                        // For profile plugin fields, check params object
                        $value = $this->data->{$fieldname}
                            ?? ($this->data->params[$fieldname] ?? '');
                    }
                    ?>
                    <input type="hidden"
                           name="jform[<?php echo $fieldname; ?>]"
                           value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" />
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="form-actions">
        <button type="submit" class="btn-primary validate">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6L9 17l-5-5"></path>
            </svg>
            <span><?php echo Text::_('JAPPLY'); ?></span>
        </button>
        <button type="button" class="btn-danger" onclick="closeEditModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            <span><?php echo Text::_('JCANCEL'); ?></span>
        </button>

        <input type="hidden" name="option" value="com_users" />
        <input type="hidden" name="task" value="profile.save" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>

<script>
(function() {
    'use strict';

    var GET_URL  = '<?php echo $getProfileUrl; ?>';
    var SAVE_URL = '<?php echo $saveProfileUrl; ?>';

    var firstNameEl = document.getElementById('vrc-first-name');
    var lastNameEl  = document.getElementById('vrc-last-name');
    var phoneEl     = document.getElementById('vrc-phone');
    var feedbackEl  = document.getElementById('vrc-profile-feedback');
    var saveBtn     = document.getElementById('vrc-save-profile-btn');

    if (!firstNameEl || !lastNameEl || !phoneEl || !feedbackEl || !saveBtn) return;

    // ── Load existing profile data ──────────────────────────────────────
    function loadProfile() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', GET_URL, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onload = function() {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res && res.exists) {
                    firstNameEl.value = res.first_name || '';
                    lastNameEl.value  = res.last_name  || '';
                    phoneEl.value     = res.phone      || '';
                }
            } catch (e) {}
        };
        xhr.send();
    }

    // ── Show feedback ───────────────────────────────────────────────────
    function showFeedback(msg, isError) {
        feedbackEl.style.display = 'block';
        feedbackEl.textContent = msg;
        feedbackEl.style.background = isError ? '#fef2f2' : '#f0fdf4';
        feedbackEl.style.border     = isError ? '1px solid #fecaca' : '1px solid #bbf7d0';
        feedbackEl.style.color      = isError ? '#dc2626' : '#16a34a';
    }

    function clearFeedback() {
        feedbackEl.style.display = 'none';
    }

    // ── Save profile ────────────────────────────────────────────────────
    function saveProfile() {
        var firstName = firstNameEl.value.trim();
        var lastName  = lastNameEl.value.trim();
        var phone     = phoneEl.value.trim();

        if (!firstName) { showFeedback('Introduceți prenumele.', true); firstNameEl.focus(); return; }
        if (!lastName)  { showFeedback('Introduceți numele.', true); lastNameEl.focus(); return; }
        if (!phone)     { showFeedback('Introduceți numărul de telefon.', true); phoneEl.focus(); return; }

        clearFeedback();
        saveBtn.disabled = true;
        saveBtn.querySelector('span').textContent = 'Se salvează…';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', SAVE_URL, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onload = function() {
            saveBtn.disabled = false;
            saveBtn.querySelector('span').textContent = '<?php echo addslashes(Text::_('VRC_SAVE_PROFILE') ?: 'Salvează datele'); ?>';
            try {
                var res = JSON.parse(xhr.responseText);
                if (res && res.ok) {
                    showFeedback('Datele au fost salvate cu succes.', false);
                } else {
                    showFeedback(res && res.error ? res.error : 'Eroare la salvare.', true);
                }
            } catch (e) {
                showFeedback('Eroare de rețea.', true);
            }
        };
        xhr.onerror = function() {
            saveBtn.disabled = false;
            saveBtn.querySelector('span').textContent = '<?php echo addslashes(Text::_('VRC_SAVE_PROFILE') ?: 'Salvează datele'); ?>';
            showFeedback('Eroare de rețea.', true);
        };
        xhr.send(JSON.stringify({
            first_name: firstName,
            last_name:  lastName,
            phone:      phone
        }));
    }

    // ── Wire up ─────────────────────────────────────────────────────────
    saveBtn.addEventListener('click', saveProfile);

    // Load data when modal opens
    loadProfile();

    // Also reload when modal is opened (in case user closed and reopened)
    var modal = document.getElementById('editProfileModal');
    if (modal) {
        var observer = new MutationObserver(function() {
            if (modal.style.display !== 'none' && modal.style.display !== '') {
                loadProfile();
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    }
})();
</script>
