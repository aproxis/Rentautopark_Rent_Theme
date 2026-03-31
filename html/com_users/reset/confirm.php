<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

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

        <form id="user-reset-confirm-form" action="<?php echo Route::_('index.php?option=com_users&task=reset.confirm'); ?>" method="post" class="form-validate form-horizontal">

            <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
                <fieldset>
                    <?php if (isset($fieldset->label)) : ?>
                        <legend><?php echo Text::_($fieldset->label); ?></legend>
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
                    <a class="btn-danger" href="<?php echo Route::_(''); ?>">
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