<?php
/**
 * Template override: templates/rent/html/com_users/reset/confirm.php
 *
 * J5 processResetConfirm() needs jform[username] + jform[token] via POST.
 * Strategy: iterate users with non-empty activation, call
 * UserHelper::verifyPassword(urlToken, activation) to identify the user
 * server-side → auto-submit hidden form → user lands on layout=complete.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive');

Factory::getDocument()
    ->addStyleSheet(Uri::root() . 'templates/rent/css/reset-styles.css');

// ── Resolve user from URL token ────────────────────────────────────────────
// activation = bcrypt(plain_token) → cannot query directly.
// Only users with a pending reset have activation != ''.
// For a small rental site this list is at most a handful of rows.

$app      = Factory::getApplication();
$urlToken = $app->input->getString('token', '');

$foundUsername = '';
$foundEmail    = '';

// Only do the expensive bcrypt scan when a token is actually present
if ($urlToken !== '') {
    $db = Factory::getDbo();
    $q  = $db->getQuery(true)
        ->select([
            $db->quoteName('username'),
            $db->quoteName('email'),
            $db->quoteName('activation'),
        ])
        ->from($db->quoteName('#__users'))
        ->where($db->quoteName('activation') . ' != ' . $db->quote(''))
        ->where($db->quoteName('block')      . ' = 0');
    $db->setQuery($q);

    foreach ((array) $db->loadObjectList() as $candidate) {
        if (!empty($candidate->activation)
            && UserHelper::verifyPassword($urlToken, $candidate->activation)
        ) {
            $foundUsername = $candidate->username;
            $foundEmail    = $candidate->email;
            break;
        }
    }
}
?>

<div class="reset-page">
<div class="reset-container">

<?php if ($urlToken === '') : ?>
    <!-- ═══════════════════════════════════════════════════════
         STATE 1 — No token: user just submitted step 1.
         J5 redirects here after processResetRequest().
         The Joomla system message ("check your email") is
         already injected above by the template; we add a
         friendly visual to reinforce it.
    ════════════════════════════════════════════════════════ -->
    <div class="reset-email-sent">
        <div class="reset-email-sent-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="16" x="2" y="4" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                <polyline points="17 17 19 19 23 15" />
            </svg>
        </div>
        <h2><?php echo Text::_('COM_USERS_RESET_EMAIL_SENT_HEADING'); ?></h2>
        <p><?php echo Text::_('COM_USERS_RESET_EMAIL_SENT_DESC'); ?></p>
        <a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>"
           class="btn btn-danger">
            <?php echo Text::_('COM_USERS_RESET_REQUEST_AGAIN'); ?>
        </a>
    </div>

<?php elseif ($foundUsername !== '') : ?>
    <!-- ═══════════════════════════════════════════════════════
         STATE 2 — Valid token: auto-submit to processResetConfirm().
         User sees a spinner for ~250ms then lands on layout=complete.
    ════════════════════════════════════════════════════════ -->
    <form action="<?php echo Route::_('index.php?option=com_users'); ?>"
          method="post"
          id="vrc-reset-confirm-form">
        <input type="hidden" name="jform[username]"
               value="<?php echo htmlspecialchars($foundUsername, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="jform[token]"
               value="<?php echo htmlspecialchars($urlToken, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="option" value="com_users">
        <input type="hidden" name="task"   value="reset.confirm">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

    <div class="reset-autosubmit" role="status" aria-live="polite">
        <div class="reset-spinner" aria-hidden="true"></div>
        <p><?php echo Text::_('COM_USERS_RESET_VERIFYING'); ?></p>
        <p class="reset-autosubmit-email">
            <?php echo htmlspecialchars($foundEmail, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            document.getElementById('vrc-reset-confirm-form').submit();
        }, 250);
    });
    </script>

<?php else : ?>
    <!-- ═══════════════════════════════════════════════════════
         STATE 3 — Token present but no match: expired or already used.
    ════════════════════════════════════════════════════════ -->
    <div class="reset-token-error">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
             viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true" class="reset-token-error-icon">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8"  x2="12"    y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <h2><?php echo Text::_('COM_USERS_RESET_TOKEN_EXPIRED_HEADING'); ?></h2>
        <p><?php echo Text::_('COM_USERS_RESET_TOKEN_EXPIRED_DESC'); ?></p>
        <a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>"
           class="btn btn-primary">
            <?php echo Text::_('COM_USERS_RESET_REQUEST_NEW'); ?>
        </a>
    </div>

<?php endif; ?>

</div>
</div>