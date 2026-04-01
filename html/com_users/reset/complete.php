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