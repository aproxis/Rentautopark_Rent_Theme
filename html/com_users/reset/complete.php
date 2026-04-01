<?php
/**
 * Template override: templates/rent/html/com_users/reset/complete.php
 *
 * Step 3 — set new password.
 * Validation: client mirrors J5 com_users rules → top slide-in notification.
 * Server: AJAX → reset-complete-ajax.php → processResetComplete() → JSON.
 * Success: autologin via Email plugin → redirect homepage.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive');

Factory::getDocument()
    ->addStyleSheet(Uri::root() . 'templates/rent/css/reset-styles.css');

$app = Factory::getApplication();

// ── J5 com_users password rules ───────────────────────────────────────────
$comParams    = $app->getParams('com_users');
$pwMinLen     = max(1, (int) $comParams->get('minimum_length',   12));
$pwMinInts    = (int) $comParams->get('minimum_integers',  0);
$pwMinSymbols = (int) $comParams->get('minimum_symbols',   0);
$pwMinUpper   = (int) $comParams->get('minimum_uppercase', 0);
$pwMinLower   = (int) $comParams->get('minimum_lowercase', 0);

// ── Grab email now (processResetComplete clears session on success) ────────
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

// Inline SVG eye icon (reused in both toggle buttons)
$iconEye = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
    viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
    aria-hidden="true">
    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
    <circle cx="12" cy="12" r="3"/>
</svg>';

$iconEyeOff = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
    viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
    aria-hidden="true">
    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
    <line x1="1" y1="1" x2="23" y2="23"/>
</svg>';
?>
<style>
/* ── Password field: toggle sits INSIDE the input border ─────────────────
   .pw-field is position:relative; the input gets padding-right for the icon;
   the toggle is position:absolute right inside.
   No flex siblings — avoids every border-join alignment quirk.          */

.pw-field {
    position: relative;
    width: 100%;
}

.pw-field .form-control {
    /* override the generic width:100% so padding-right is added cleanly */
    width: 100%;
    padding-right: 46px;   /* room for the 40px toggle + 6px gap */
    box-sizing: border-box;
}

.pw-toggle {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    padding: 0;
    color: #9ca3af;
    transition: color 0.15s, background 0.15s;
    /* Must be above the input — input has z-index:auto */
    z-index: 2;
}

.pw-toggle:hover {
    color: #374151;
    background: rgba(0,0,0,0.05);
}

.pw-toggle:focus-visible {
    outline: 2px solid #FE5001;
    outline-offset: 1px;
}

/* ── Strength bar ──────────────────────────────────────────────────────── */
.rc-meter-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}
.rc-strength-bar {
    flex: 1;
    height: 5px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
}
.rc-strength-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.28s ease, background-color 0.28s ease;
    width: 0;
}
.rc-strength-label {
    font-size: 11px;
    font-weight: 700;
    min-width: 58px;
    text-align: right;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #9ca3af;
    transition: color 0.28s ease;
}

/* ── Rules checklist ───────────────────────────────────────────────────── */
.password-rules {
    list-style: none;
    padding: 0;
    margin: 8px 0 0;
}
.password-rules li {
    position: relative;
    font-size: 12px;
    line-height: 1.6;
    padding: 1px 0 1px 20px;
    color: #9ca3af;
    transition: color 0.15s;
}
.password-rules li::before {
    position: absolute;
    left: 0;
    content: '○';
    font-size: 12px;
    line-height: 1.6;
}
.rule-pass         { color: #16a34a !important; }
.rule-pass::before { content: '✓' !important;  }
.rule-fail         { color: #dc2626 !important; }
.rule-fail::before { content: '✗' !important;  }

/* ── Match hint ────────────────────────────────────────────────────────── */
.rc-match-hint {
    font-size: 12px;
    margin-top: 5px;
    min-height: 18px;
    line-height: 1.4;
    transition: color 0.15s;
}
.rc-match-hint--ok    { color: #16a34a; }
.rc-match-hint--error { color: #dc2626; }

/* ── Top notification ──────────────────────────────────────────────────── */
.rc-notification {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.5;
    margin-bottom: 20px;
}
.rc-notification[hidden] { display: none; }
.rc-notification--error  {
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    color: #b91c1c;
}
.rc-notification--success {
    background: #f0fdf4;
    border: 1.5px solid #bbf7d0;
    color: #166534;
}
.rc-notification--animate {
    animation: rcSlideIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) both;
}
@keyframes rcSlideIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.rc-notification-close {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    color: inherit;
    opacity: 0.55;
    transition: opacity 0.15s;
}
.rc-notification-close:hover { opacity: 1; }

/* ── Submit button ─────────────────────────────────────────────────────── */
#reset-complete-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-width: 130px;
    justify-content: center;
}
#reset-complete-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

@media (prefers-reduced-motion: reduce) {
    .rc-notification--animate { animation: none; }
    .rc-strength-fill,
    .rc-strength-label,
    .password-rules li,
    .rc-match-hint,
    .pw-toggle { transition: none; }
}
</style>

<div class="reset-page">
<div class="reset-container">

    <?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    </div>
    <?php endif; ?>

    <form id="user-reset-complete-form" novalidate>

        <!-- TOP NOTIFICATION — slides in above fieldset on any error ──── -->
        <div id="rc-notification" class="rc-notification" role="alert" aria-live="assertive" hidden>
            <span id="rc-notification-msg"></span>
            <button type="button" id="rc-notification-close"
                    class="rc-notification-close"
                    aria-label="<?php echo Text::_('JCLOSE'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <line x1="18" y1="6"  x2="6"  y2="18"/>
                    <line x1="6"  y1="6"  x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <fieldset>

            <!-- ── New password ──────────────────────────────────────── -->
            <div class="form-group">
                <div class="control-label">
                    <label for="jform_password1"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
                </div>
                <div class="controls">

                    <!-- pw-field: relative wrapper, toggle is absolute inside -->
                    <div class="pw-field">
                        <input type="password"
                               name="jform[password1]"
                               id="jform_password1"
                               class="form-control"
                               autocomplete="new-password">
                        <button type="button"
                                class="pw-toggle"
                                id="toggle-p1"
                                data-target="jform_password1"
                                aria-label="<?php echo Text::_('JSHOW_PASSWORD'); ?>"
                                aria-pressed="false">
                            <?php echo $iconEye; ?>
                        </button>
                    </div>

                    <!-- Strength bar -->
                    <div class="rc-meter-wrap">
                        <div class="rc-strength-bar">
                            <div id="rc-strength-fill" class="rc-strength-fill"></div>
                        </div>
                        <span id="rc-strength-label" class="rc-strength-label" aria-live="polite"></span>
                    </div>

                    <!-- Rules checklist — labels from J5 Text::plural() — exact same
                         strings processResetComplete() uses in its validation errors   -->
                    <ul class="password-rules" id="pw-rules"
                        aria-label="<?php echo Text::_('COM_USERS_PASSWORD_REQUIREMENTS'); ?>">
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

            <!-- ── Confirm password ──────────────────────────────────── -->
            <div class="form-group">
                <div class="control-label">
                    <label for="jform_password2"><?php echo Text::_('JGLOBAL_CONFIRM_PASSWORD'); ?></label>
                </div>
                <div class="controls">

                    <div class="pw-field">
                        <input type="password"
                               name="jform[password2]"
                               id="jform_password2"
                               class="form-control"
                               autocomplete="new-password">
                        <button type="button"
                                class="pw-toggle"
                                id="toggle-p2"
                                data-target="jform_password2"
                                aria-label="<?php echo Text::_('JSHOW_PASSWORD'); ?>"
                                aria-pressed="false">
                            <?php echo $iconEye; ?>
                        </button>
                    </div>

                    <div id="rc-match-hint" class="rc-match-hint" aria-live="polite"></div>

                </div>
            </div>

        </fieldset>

        <div class="form-actions">
            <button type="submit" id="reset-complete-btn" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true" id="reset-complete-btn-icon">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                <span id="reset-complete-btn-label">
                    <?php echo Text::_('COM_USERS_RESET_COMPLETE_BUTTON'); ?>
                </span>
            </button>
        </div>

    </form>

</div>
</div>

<script>
(function () {
    'use strict';

    /* ── Config from PHP ───────────────────────────────────────────────── */
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
        empty:   <?php echo json_encode(Text::_('JGLOBAL_PASSWORD')); ?>,
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
    var STRENGTH_COLORS = ['#ef4444','#f97316','#ca8a04','#65a30d','#16a34a'];

    /* ── DOM ────────────────────────────────────────────────────────────── */
    var form         = document.getElementById('user-reset-complete-form');
    var p1El         = document.getElementById('jform_password1');
    var p2El         = document.getElementById('jform_password2');
    var btn          = document.getElementById('reset-complete-btn');
    var btnLabel     = document.getElementById('reset-complete-btn-label');
    var btnIcon      = document.getElementById('reset-complete-btn-icon');
    var notifEl      = document.getElementById('rc-notification');
    var notifMsg     = document.getElementById('rc-notification-msg');
    var notifClose   = document.getElementById('rc-notification-close');
    var matchHint    = document.getElementById('rc-match-hint');
    var strengthFill = document.getElementById('rc-strength-fill');
    var strengthLbl  = document.getElementById('rc-strength-label');

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfName = csrfMeta ? csrfMeta.getAttribute('content') : null;

    /* ── Password toggles ───────────────────────────────────────────────
       Each .pw-toggle has data-target="<input-id>".
       On click: flip type, swap icon, update aria-pressed.              */
    var SVG_EYE = document.getElementById('toggle-p1').innerHTML; /* eye icon */
    var SVG_EYE_OFF = <?php echo json_encode($iconEyeOff); ?>;

    document.querySelectorAll('.pw-toggle').forEach(function (tb) {
        tb.addEventListener('click', function () {
            var inp = document.getElementById(tb.dataset.target);
            if (!inp) return;
            var showing = inp.type === 'text';
            inp.type = showing ? 'password' : 'text';
            tb.setAttribute('aria-pressed', showing ? 'false' : 'true');
            tb.setAttribute('aria-label', showing
                ? <?php echo json_encode(Text::_('JSHOW_PASSWORD')); ?>
                : <?php echo json_encode(Text::_('JHIDE_PASSWORD')); ?>);
            tb.innerHTML = showing ? SVG_EYE : SVG_EYE_OFF;
        });
    });

    /* ── Top notification ───────────────────────────────────────────────── */
    function showNotif(msg, type) {
        notifMsg.textContent = msg;
        notifEl.className    = 'rc-notification rc-notification--' + (type || 'error');
        notifEl.hidden       = false;
        void notifEl.offsetWidth;
        notifEl.classList.add('rc-notification--animate');
        notifEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function hideNotif() {
        notifEl.hidden    = true;
        notifEl.className = 'rc-notification';
    }
    notifClose.addEventListener('click', hideNotif);

    /* ── Rule checklist ─────────────────────────────────────────────────── */
    function setRule(id, passes) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('rule-pass', 'rule-fail', 'rule-neutral');
        el.classList.add(passes ? 'rule-pass' : 'rule-fail');
    }
    function evalRules(v) {
        setRule('rule-length',   v.length >= RULES.minLen);
        if (RULES.minInts  > 0) setRule('rule-integers', (v.match(/\d/g)           ||[]).length >= RULES.minInts);
        if (RULES.minSyms  > 0) setRule('rule-symbols',  (v.match(/[^a-zA-Z0-9]/g)||[]).length >= RULES.minSyms);
        if (RULES.minUpper > 0) setRule('rule-upper',    (v.match(/[A-Z]/g)        ||[]).length >= RULES.minUpper);
        if (RULES.minLower > 0) setRule('rule-lower',    (v.match(/[a-z]/g)        ||[]).length >= RULES.minLower);
    }
    function resetRules() {
        document.querySelectorAll('#pw-rules .rule').forEach(function (li) {
            li.classList.remove('rule-pass','rule-fail');
            li.classList.add('rule-neutral');
        });
    }

    /* ── Strength bar ───────────────────────────────────────────────────── */
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
            strengthLbl.textContent       = '';
            strengthLbl.style.color       = '';
            return;
        }
        var s   = calcStrength(v);
        var pct = ((s + 1) / 5 * 100).toFixed(0) + '%';
        strengthFill.style.width           = pct;
        strengthFill.style.backgroundColor = STRENGTH_COLORS[s];
        strengthLbl.textContent            = STRENGTH_LABELS[s] || '';
        strengthLbl.style.color            = STRENGTH_COLORS[s];
    }

    /* ── Match hint ─────────────────────────────────────────────────────── */
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
            if (notifMsg.textContent === MSG.match) hideNotif();
        } else {
            matchHint.textContent = MSG.match;
            matchHint.className   = 'rc-match-hint rc-match-hint--error';
        }
    }

    /* ── Live events ────────────────────────────────────────────────────── */
    p1El.addEventListener('input', function () {
        var v = p1El.value;
        if (v.length) { evalRules(v); updateStrength(v); }
        else          { resetRules(); updateStrength(''); }
        if (p2El.value.length) updateMatchHint();
    });
    p2El.addEventListener('input', updateMatchHint);
    p2El.addEventListener('blur', function () {
        if (p1El.value.length && p2El.value.length && p1El.value !== p2El.value) {
            showNotif(MSG.match, 'error');
        }
    });

    /* ── Client-side validation ─────────────────────────────────────────── */
    function clientValidate() {
        var v = p1El.value, v2 = p2El.value;
        if (!v)                                                                              return MSG.empty;
        if (v.length < RULES.minLen)                                                         return MSG.len;
        if (RULES.minInts  > 0 && (v.match(/\d/g)           ||[]).length < RULES.minInts)   return MSG.ints;
        if (RULES.minSyms  > 0 && (v.match(/[^a-zA-Z0-9]/g)||[]).length < RULES.minSyms)    return MSG.syms;
        if (RULES.minUpper > 0 && (v.match(/[A-Z]/g)        ||[]).length < RULES.minUpper)  return MSG.upper;
        if (RULES.minLower > 0 && (v.match(/[a-z]/g)        ||[]).length < RULES.minLower)  return MSG.lower;
        if (v !== v2)                                                                         return MSG.match;
        return null;
    }

    /* ── Submit ─────────────────────────────────────────────────────────── */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideNotif();

        var clientErr = clientValidate();
        if (clientErr) {
            showNotif(clientErr, 'error');
            evalRules(p1El.value);
            updateMatchHint();
            (p1El.value === p2El.value ? p1El : p2El).focus();
            return;
        }

        /* Disable button, hide icon, show loading text */
        btn.disabled      = true;
        btnIcon.hidden    = true;
        btnLabel.textContent = MSG.loading;

        fetch(AJAX_URL, {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ password1: p1El.value, password2: p2El.value })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.ok) {
                /* Restore button */
                btn.disabled         = false;
                btnIcon.hidden       = false;
                btnLabel.textContent = <?php echo json_encode(Text::_('COM_USERS_RESET_COMPLETE_BUTTON')); ?>;
                showNotif(res.error || 'Unknown error. Please try again.', 'error');
                return;
            }

            /* ── Autologin ─────────────────────────────────────────── */
            var email = EMAIL || res.email || '';
            if (!email) {
                window.location.href = SITE_URL + '#open-login-modal';
                return;
            }
            var ld = new URLSearchParams();
            ld.append('username', email);
            ld.append('password', p1El.value);
            ld.append('option',   'com_users');
            ld.append('task',     'user.login');
            ld.append('return',   btoa(SITE_URL));
            if (csrfName) ld.append(csrfName, '1');

            fetch(SITE_URL + 'index.php', {
                method: 'POST', credentials: 'same-origin',
                body: ld, redirect: 'manual'
            })
            .then(function ()  { window.location.href = SITE_URL; })
            .catch(function () { window.location.href = SITE_URL + '#open-login-modal'; });
        })
        .catch(function () {
            btn.disabled         = false;
            btnIcon.hidden       = false;
            btnLabel.textContent = <?php echo json_encode(Text::_('COM_USERS_RESET_COMPLETE_BUTTON')); ?>;
            showNotif(MSG.network, 'error');
        });
    });

}());
</script>