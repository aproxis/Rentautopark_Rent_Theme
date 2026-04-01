<?php
/**
 * Template override: templates/rent/html/com_users/reset/complete.php
 *
 * Step 3 — set new password.
 * Validation: client-side mirrors J5 com_users rules → top slide-in notification.
 * Server: AJAX to reset-complete-ajax.php (J5 processResetComplete → JSON).
 * Success: autologin via Email plugin → redirect to homepage.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive');

Factory::getDocument()
    ->addStyleSheet(Uri::root() . 'templates/rent/css/reset-styles.css');

$app = Factory::getApplication();

// ── J5 com_users password complexity params ───────────────────────────────
$comParams    = $app->getParams('com_users');
$pwMinLen     = max(1, (int) $comParams->get('minimum_length',   12));
$pwMinInts    = (int) $comParams->get('minimum_integers',  0);
$pwMinSymbols = (int) $comParams->get('minimum_symbols',   0);
$pwMinUpper   = (int) $comParams->get('minimum_uppercase', 0);
$pwMinLower   = (int) $comParams->get('minimum_lowercase', 0);

// ── Capture email BEFORE ajax endpoint clears the session ─────────────────
$resetUserId = (int) $app->getUserState('com_users.reset.user', 0);
$resetEmail  = '';
if ($resetUserId > 0) {
    try {
        $u = Factory::getUser($resetUserId);
        if ($u && $u->id > 0) $resetEmail = $u->email;
    } catch (\Exception $e) {}
}

$ajaxUrl = Uri::root() . 'templates/rent/php/reset-complete-ajax.php';
$siteUrl = Uri::root();
?>

<div class="reset-page">
<div class="reset-container">

    <?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    </div>
    <?php endif; ?>

    <form id="user-reset-complete-form" novalidate>

        <!-- ══ TOP NOTIFICATION — slides in on any error ══════════════════ -->
        <div id="rc-notification" class="rc-notification" role="alert" aria-live="assertive" hidden>
            <span id="rc-notification-msg"></span>
            <button type="button" id="rc-notification-close"
                    class="rc-notification-close" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6"  y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <fieldset>

            <!-- New password ─────────────────────────────────────────── -->
            <div class="form-group">
                <div class="control-label">
                    <label for="jform_password1"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
                </div>
                <div class="controls">
                    <div class="password-group">

                        <div class="input-group">
                            <input type="password"
                                   name="jform[password1]"
                                   id="jform_password1"
                                   class="form-control"
                                   autocomplete="new-password">
                            <button type="button" class="input-password-toggle"
                                    id="toggle-p1"
                                    aria-label="<?php echo Text::_('JSHOW_PASSWORD'); ?>"
                                    aria-pressed="false">
                                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg"
                                     width="18" height="18" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     aria-hidden="true">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Strength meter -->
                        <div class="rc-meter-wrap">
                            <div id="rc-strength-bar" class="rc-strength-bar">
                                <div id="rc-strength-fill" class="rc-strength-fill" style="width:0"></div>
                            </div>
                            <span id="rc-strength-label" class="rc-strength-label" aria-live="polite"></span>
                        </div>

                        <!-- Rules checklist (J5 translated strings via Text::plural) -->
                        <ul class="password-rules" id="pw-rules" aria-label="<?php echo Text::_('COM_USERS_PASSWORD_REQUIREMENTS'); ?>">
                            <li id="rule-length" class="rule rule-neutral">
                                <?php echo Text::plural('JLIB_USER_ERROR_MINIMUM_LENGTH_N', $pwMinLen); ?>
                            </li>
                            <?php if ($pwMinInts > 0) : ?>
                            <li id="rule-integers" class="rule rule-neutral">
                                <?php echo Text::plural('JLIB_USER_ERROR_MINIMUM_INTEGERS_N', $pwMinInts); ?>
                            </li>
                            <?php endif; ?>
                            <?php if ($pwMinSymbols > 0) : ?>
                            <li id="rule-symbols" class="rule rule-neutral">
                                <?php echo Text::plural('JLIB_USER_ERROR_MINIMUM_SYMBOLS_N', $pwMinSymbols); ?>
                            </li>
                            <?php endif; ?>
                            <?php if ($pwMinUpper > 0) : ?>
                            <li id="rule-upper" class="rule rule-neutral">
                                <?php echo Text::plural('JLIB_USER_ERROR_MINIMUM_UPPERCASE_N', $pwMinUpper); ?>
                            </li>
                            <?php endif; ?>
                            <?php if ($pwMinLower > 0) : ?>
                            <li id="rule-lower" class="rule rule-neutral">
                                <?php echo Text::plural('JLIB_USER_ERROR_MINIMUM_LOWERCASE_N', $pwMinLower); ?>
                            </li>
                            <?php endif; ?>
                        </ul>

                    </div>
                </div>
            </div>

            <!-- Confirm password ─────────────────────────────────────── -->
            <div class="form-group">
                <div class="control-label">
                    <label for="jform_password2"><?php echo Text::_('JGLOBAL_CONFIRM_PASSWORD'); ?></label>
                </div>
                <div class="controls">
                    <div class="input-group">
                        <input type="password"
                               name="jform[password2]"
                               id="jform_password2"
                               class="form-control"
                               autocomplete="new-password">
                        <button type="button" class="input-password-toggle"
                                id="toggle-p2"
                                aria-label="<?php echo Text::_('JSHOW_PASSWORD'); ?>"
                                aria-pressed="false">
                            <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg"
                                 width="18" height="18" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 aria-hidden="true">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Inline match indicator below confirm field -->
                    <div id="rc-match-hint" class="rc-match-hint" aria-live="polite"></div>
                </div>
            </div>

        </fieldset>

        <div class="form-actions">
            <button type="submit" id="reset-complete-btn" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                <?php echo Text::_('COM_USERS_RESET_COMPLETE_BUTTON'); ?>
            </button>
        </div>

    </form>

</div>
</div>

<script>
(function () {
    'use strict';

    /* ── PHP → JS ──────────────────────────────────────────────────────── */
    var AJAX_URL = <?php echo json_encode($ajaxUrl); ?>;
    var SITE_URL = <?php echo json_encode($siteUrl); ?>;
    var EMAIL    = <?php echo json_encode($resetEmail); ?>;
    var RULES    = {
        minLen:   <?php echo (int) $pwMinLen; ?>,
        minInts:  <?php echo (int) $pwMinInts; ?>,
        minSyms:  <?php echo (int) $pwMinSymbols; ?>,
        minUpper: <?php echo (int) $pwMinUpper; ?>,
        minLower: <?php echo (int) $pwMinLower; ?>
    };
    /* J5 translated strings — same source as processResetComplete errors */
    var MSG = {
        empty:   <?php echo json_encode(Text::_('JGLOBAL_PASSWORD') . ' required.'); ?>,
        len:     <?php echo json_encode(Text::plural('JLIB_USER_ERROR_MINIMUM_LENGTH_N',    $pwMinLen)); ?>,
        ints:    <?php echo json_encode(Text::plural('JLIB_USER_ERROR_MINIMUM_INTEGERS_N',  $pwMinInts)); ?>,
        syms:    <?php echo json_encode(Text::plural('JLIB_USER_ERROR_MINIMUM_SYMBOLS_N',   $pwMinSymbols)); ?>,
        upper:   <?php echo json_encode(Text::plural('JLIB_USER_ERROR_MINIMUM_UPPERCASE_N', $pwMinUpper)); ?>,
        lower:   <?php echo json_encode(Text::plural('JLIB_USER_ERROR_MINIMUM_LOWERCASE_N', $pwMinLower)); ?>,
        match:   <?php echo json_encode(Text::_('COM_USERS_PROFILES_PASSMATCH_ERROR')); ?>,
        matchOk: <?php echo json_encode(Text::_('COM_USERS_PROFILES_PASSMATCH_LABEL')); ?>,
        loading: <?php echo json_encode(Text::_('JLOADING')); ?>,
        network: 'Network error. Please try again.'
    };
    var STRENGTH_LABELS = [
        <?php echo json_encode(Text::_('COM_USERS_PASSWORD_STRENGTH_0')); ?>,
        <?php echo json_encode(Text::_('COM_USERS_PASSWORD_STRENGTH_1')); ?>,
        <?php echo json_encode(Text::_('COM_USERS_PASSWORD_STRENGTH_2')); ?>,
        <?php echo json_encode(Text::_('COM_USERS_PASSWORD_STRENGTH_3')); ?>,
        <?php echo json_encode(Text::_('COM_USERS_PASSWORD_STRENGTH_4')); ?>
    ];

    /* ── DOM ────────────────────────────────────────────────────────────── */
    var form       = document.getElementById('user-reset-complete-form');
    var p1El       = document.getElementById('jform_password1');
    var p2El       = document.getElementById('jform_password2');
    var btn        = document.getElementById('reset-complete-btn');
    var notifEl    = document.getElementById('rc-notification');
    var notifMsg   = document.getElementById('rc-notification-msg');
    var notifClose = document.getElementById('rc-notification-close');
    var matchHint  = document.getElementById('rc-match-hint');
    var strengthFill  = document.getElementById('rc-strength-fill');
    var strengthLabel = document.getElementById('rc-strength-label');

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfName = csrfMeta ? csrfMeta.getAttribute('content') : null;

    /* ── Top notification ───────────────────────────────────────────────── */
    function showNotif(msg, type) {
        /* type: 'error' (default) | 'success' */
        notifMsg.textContent = msg;
        notifEl.className    = 'rc-notification rc-notification--' + (type || 'error');
        notifEl.hidden       = false;
        /* re-trigger animation by removing + adding the class */
        notifEl.classList.remove('rc-notification--animate');
        void notifEl.offsetWidth; /* reflow */
        notifEl.classList.add('rc-notification--animate');
        notifEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideNotif() {
        notifEl.hidden = true;
        notifEl.className = 'rc-notification';
    }

    notifClose.addEventListener('click', hideNotif);

    /* ── Password toggle ────────────────────────────────────────────────── */
    function addToggle(btnId, inp) {
        var tb = document.getElementById(btnId);
        if (!tb) return;
        tb.addEventListener('click', function () {
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            tb.setAttribute('aria-pressed', show ? 'true' : 'false');
        });
    }
    addToggle('toggle-p1', p1El);
    addToggle('toggle-p2', p2El);

    /* ── Rule checklist ─────────────────────────────────────────────────── */
    function setRule(id, passes) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('rule-pass', 'rule-fail', 'rule-neutral');
        el.classList.add(passes ? 'rule-pass' : 'rule-fail');
    }

    function evalRules(v) {
        setRule('rule-length',   v.length >= RULES.minLen);
        if (RULES.minInts  > 0) setRule('rule-integers', (v.match(/\d/g)           || []).length >= RULES.minInts);
        if (RULES.minSyms  > 0) setRule('rule-symbols',  (v.match(/[^a-zA-Z0-9]/g) || []).length >= RULES.minSyms);
        if (RULES.minUpper > 0) setRule('rule-upper',    (v.match(/[A-Z]/g)         || []).length >= RULES.minUpper);
        if (RULES.minLower > 0) setRule('rule-lower',    (v.match(/[a-z]/g)         || []).length >= RULES.minLower);
    }

    function resetRules() {
        document.querySelectorAll('#pw-rules li').forEach(function (li) {
            li.classList.remove('rule-pass', 'rule-fail');
            li.classList.add('rule-neutral');
        });
    }

    /* ── Strength meter (custom div bar — reliable cross-browser) ────────── */
    var STRENGTH_COLORS = ['#ef4444', '#f97316', '#ca8a04', '#65a30d', '#16a34a'];

    function calcStrength(v) {
        var s = 0;
        if (v.length >= RULES.minLen) s++;
        if (/[A-Z]/.test(v))          s++;
        if (/[0-9]/.test(v))          s++;
        if (/[^a-zA-Z0-9]/.test(v))   s++;
        return Math.min(s, 4);
    }

    function updateStrength(v) {
        if (!v.length) {
            strengthFill.style.width      = '0';
            strengthFill.style.background = '';
            strengthLabel.textContent     = '';
            return;
        }
        var s   = calcStrength(v);
        var pct = ((s + 1) / 5 * 100).toFixed(0) + '%';
        strengthFill.style.width      = pct;
        strengthFill.style.background = STRENGTH_COLORS[s];
        strengthLabel.textContent     = STRENGTH_LABELS[s] || '';
        strengthLabel.style.color     = STRENGTH_COLORS[s];
    }

    /* ── Match hint (below confirm field) ───────────────────────────────── */
    function updateMatchHint() {
        var v1 = p1El.value, v2 = p2El.value;
        if (!v2.length) {
            matchHint.textContent = '';
            matchHint.className   = 'rc-match-hint';
            return;
        }
        if (v1 === v2) {
            matchHint.textContent = '✓ ' + MSG.matchOk;
            matchHint.className   = 'rc-match-hint rc-match-hint--ok';
            /* Clear top notification if it was showing the mismatch warning */
            if (notifMsg.textContent === MSG.match) hideNotif();
        } else {
            matchHint.textContent = MSG.match;
            matchHint.className   = 'rc-match-hint rc-match-hint--error';
        }
    }

    /* ── Live events ────────────────────────────────────────────────────── */
    p1El.addEventListener('input', function () {
        var v = p1El.value;
        if (!v.length) { resetRules(); updateStrength(''); }
        else           { evalRules(v); updateStrength(v); }
        if (p2El.value.length) updateMatchHint();
    });

    p2El.addEventListener('input', updateMatchHint);

    /* Show top notification on blur if passwords already typed but don't match */
    p2El.addEventListener('blur', function () {
        if (p1El.value.length && p2El.value.length && p1El.value !== p2El.value) {
            showNotif(MSG.match, 'error');
        }
    });

    /* ── Client-side validation ─────────────────────────────────────────── */
    function clientValidate() {
        var v = p1El.value, v2 = p2El.value;
        if (!v)                                                                               return MSG.empty;
        if (v.length < RULES.minLen)                                                          return MSG.len;
        if (RULES.minInts  > 0 && (v.match(/\d/g)           || []).length < RULES.minInts)   return MSG.ints;
        if (RULES.minSyms  > 0 && (v.match(/[^a-zA-Z0-9]/g) || []).length < RULES.minSyms)   return MSG.syms;
        if (RULES.minUpper > 0 && (v.match(/[A-Z]/g)         || []).length < RULES.minUpper)  return MSG.upper;
        if (RULES.minLower > 0 && (v.match(/[a-z]/g)         || []).length < RULES.minLower)  return MSG.lower;
        if (v !== v2)                                                                          return MSG.match;
        return null;
    }

    /* ── Form submit ────────────────────────────────────────────────────── */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideNotif();

        var clientErr = clientValidate();
        if (clientErr) {
            showNotif(clientErr, 'error');
            evalRules(p1El.value);
            updateMatchHint();
            /* Focus the right field */
            (p1El.value === p2El.value ? p1El : p2El).focus();
            return;
        }

        var btnLabel    = btn.textContent.trim();
        btn.disabled    = true;
        btn.textContent = MSG.loading;

        fetch(AJAX_URL, {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ password1: p1El.value, password2: p2El.value })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.ok) {
                btn.disabled    = false;
                btn.textContent = btnLabel;
                showNotif(res.error || 'Unknown error. Please try again.', 'error');
                return;
            }

            /* ── Autologin ───────────────────────────────────────────── */
            var email = EMAIL || res.email || '';
            if (!email) {
                window.location.href = SITE_URL + '#open-login-modal';
                return;
            }

            var loginData = new URLSearchParams();
            loginData.append('username', email);
            loginData.append('password', p1El.value);
            loginData.append('option',   'com_users');
            loginData.append('task',     'user.login');
            loginData.append('return',   btoa(SITE_URL));
            if (csrfName) loginData.append(csrfName, '1');

            fetch(SITE_URL + 'index.php', {
                method: 'POST', credentials: 'same-origin',
                body: loginData, redirect: 'manual'
            })
            .then(function ()  { window.location.href = SITE_URL; })
            .catch(function () { window.location.href = SITE_URL + '#open-login-modal'; });
        })
        .catch(function () {
            btn.disabled    = false;
            btn.textContent = btnLabel;
            showNotif(MSG.network, 'error');
        });
    });

}());
</script>