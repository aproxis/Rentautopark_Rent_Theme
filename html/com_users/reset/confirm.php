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

<?php if ($foundUsername !== '') : ?>

    <!--
        Auto-submitting form — completely invisible to the user.
        jform[username] is the real Joomla username (e.g. "user42"),
        jform[token]    is the plain token from the URL.
        processResetConfirm() verifies verifyPassword(token, activation)
        and sets session state for processResetComplete().
    -->
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

    <!-- Spinner shown during the ~200ms before form submits -->
    <div class="reset-autosubmit" role="status" aria-live="polite">
        <div class="reset-spinner" aria-hidden="true"></div>
        <p><?php echo Text::_('COM_USERS_RESET_VERIFYING'); ?></p>
        <p class="reset-autosubmit-email">
            <?php echo htmlspecialchars($foundEmail, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tiny delay so spinner paints before navigation starts
        setTimeout(function () {
            document.getElementById('vrc-reset-confirm-form').submit();
        }, 250);
    });
    </script>

<?php else : ?>

    <!-- Token not found, expired, or already used -->
    <div class="reset-token-error">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
             viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true" class="reset-token-error-icon">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8"  x2="12"   y2="12"/>
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