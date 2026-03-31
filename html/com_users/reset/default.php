<?php
/**
 * Template override: templates/rent/html/com_users/reset/default.php
 * Step 1 — user enters email to receive a reset link.
 * J5 processResetRequest() searches #__users by email (jform[email]).
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
        <h1><?php echo Text::_('COM_USERS_RESET_REQUEST_TITLE'); ?></h1>
    </div>

    <form action="<?php echo Route::_('index.php?option=com_users'); ?>"
          method="post"
          id="user-reset-form"
          class="form-validate">

        <fieldset>
            <p><?php echo Text::_('COM_USERS_RESET_REQUEST_TEXT'); ?></p>

            <div class="form-group">
                <div class="control-label">
                    <label for="jform_email">
                        <?php echo Text::_('JGLOBAL_EMAIL'); ?>
                    </label>
                </div>
                <div class="controls">
                    <!-- J5 reset model reads jform[email] — do NOT use jform[username] -->
                    <input type="email"
                           name="jform[email]"
                           id="jform_email"
                           class="form-control validate-email required"
                           autocomplete="email"
                           placeholder="your@email.com"
                           required>
                </div>
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="16" x="2" y="4" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
                <?php echo Text::_('COM_USERS_RESET_SEND'); ?>
            </button>
            <a href="<?php echo Route::_('index.php'); ?>" class="btn btn-danger">
                <?php echo Text::_('JCANCEL'); ?>
            </a>
        </div>

        <input type="hidden" name="option" value="com_users">
        <input type="hidden" name="task"   value="reset.request">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

</div>
</div>