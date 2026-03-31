<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

// Load reset page styles
$doc = Factory::getDocument();
$doc->addStyleSheet(Uri::root() . 'templates/rent/css/reset-styles.css');

?>
<div class="reset-page">
    <div class="reset-container">

        <?php if ($this->params->get('show_page_heading')) : ?>
            <div class="page-header">
                <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
            </div>
        <?php endif; ?>

        <form id="user-reset-form" action="<?php echo Route::_('index.php?option=com_users&task=reset.request'); ?>" method="post" class="form-validate form-horizontal">

            <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
                <fieldset>
                    <?php if (isset($fieldset->label)) : ?>
                        <p><?php echo Text::_($fieldset->label); ?></p>
                    <?php endif; ?>
                    <?php echo $this->form->renderFieldset($fieldset->name); ?>
                </fieldset>
            <?php endforeach; ?>

            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn-primary validate">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                        <span><?php echo Text::_('JSUBMIT'); ?></span>
                    </button>
                    <a class="btn-danger" href="<?php echo Route::_('index.php?option=com_users&view=login'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span><?php echo Text::_('JCANCEL'); ?></span>
                    </a>
                    <?php echo HTMLHelper::_('form.token'); ?>
                </div>
            </div>

        </form>

    </div>
</div>