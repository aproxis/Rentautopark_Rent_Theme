<?php
/*
 * ------------------------------------------------------------------------
 * JA Rent template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 *
 * UPDATED: VikRentCar cardetails language switcher fix
 * Rewrites language links to preserve current car instead of falling back to carslist
*/

// no direct access

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');
HTMLHelper::_('stylesheet', 'mod_languages/template.css', array(), true);

$app       = Factory::getApplication();
$tplparams = $app->getTemplate(true)->params;
$menu      = $app->getMenu();
$active    = $menu->getActive();

if (!$active) {
    $active = $menu->getDefault();
}

// ── Original user-page association block ───────────────────────────────
if ($tplparams->get('tpl_userpage_mid') == $active->id) {

    $assoc = isset($app->item_associations) ? $app->item_associations : 0;
    if ($assoc) {
        $option = $app->input->get('option');
        $eName  = JString::ucfirst(JString::str_ireplace('com_', '', $option));
        $cName  = JString::ucfirst($eName . 'HelperAssociation');
        JLoader::register($cName, JPath::clean(JPATH_COMPONENT_SITE . '/helpers/association.php'));

        if (class_exists($cName) && is_callable(array($cName, 'getAssociations'))) {
            $cassociations = call_user_func(array($cName, 'getAssociations'));
        }

        if (!empty($cassociations)) {
            $associations = MenusHelper::getAssociations($active->id);

            foreach ($list as $language) {
                if (!$language->active && $cassociations[$language->lang_code] && $associations[$language->lang_code]) {
                    $language->link = JRoute::_(preg_replace('@Itemid=(\d+)@', 'Itemid=' . $associations[$language->lang_code], $cassociations[$language->lang_code] . '&lang=' . $language->sef));
                }
            }
        }
    }
}

// ── VikRentCar cardetails: rewrite links to keep the same car ──────────
$currentOption = $app->input->get('option', '', 'cmd');
$currentView   = $app->input->get('view',   '', 'cmd');
$currentCarId  = $app->input->getInt('carid', 0);

if ($currentOption === 'com_vikrentcar' && $currentView === 'cardetails' && $currentCarId > 0) {

    $db = JFactory::getDbo();

    foreach ($list as $language) {
        if ($language->active) {
            continue;
        }

        // Find the cardetails menu Itemid for this specific language
        $q = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link')      . ' LIKE ' . $db->quote('%option=com_vikrentcar%'))
            ->where($db->quoteName('link')      . ' LIKE ' . $db->quote('%view=cardetails%'))
            ->where($db->quoteName('language')  . ' = '    . $db->quote($language->lang_code))
            ->where($db->quoteName('published') . ' = 1')
            ->setLimit(1);

        $db->setQuery($q);
        $itemId = (int) $db->loadResult();

        $query = array(
            'option' => 'com_vikrentcar',
            'view'   => 'cardetails',
            'carid'  => $currentCarId,
            'lang'   => $language->sef,
        );

        if ($itemId > 0) {
            $query['Itemid'] = $itemId;
        }

        // JRoute converts this into the correct SEF URL e.g. /ru/cars/suzuki-splash
        $language->link = JRoute::_('index.php?' . http_build_query($query), false);
    }
}
// ───────────────────────────────────────────────────────────────────────
?>

<div class="dropdown mod-languages">
<?php if ($headerText) : ?>
    <div class="pretext"><p><?php echo $headerText; ?></p></div>
<?php endif; ?>
<?php if ($params->get('dropdown') == 0) : ?>
    <ul class="<?php echo $params->get('inline', 1) ? 'lang-inline' : 'lang-block';?>">
    <?php foreach ($list as $language) : ?>
        <?php if ($params->get('show_active', 0) || !$language->active):?>
            <li class="<?php echo $language->active ? 'lang-active' : '';?>" dir="<?php echo JLanguage::getInstance($language->lang_code)->isRTL() ? 'rtl' : 'ltr' ?>">
            <a href="<?php echo $language->link;?>">
            <?php if ($params->get('image', 1)):?>
                <?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, array('title' => $language->title_native), true);?>
            <?php else : ?>
                <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef);?>
            <?php endif; ?>
            </a>
            </li>
        <?php endif;?>
    <?php endforeach;?>
    </ul>
<?php else : ?>
    <a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo Uri::base(true) ?>">
        <?php if($params->get('image', 1)): ?>
            <?php 
                foreach($list as $language) {
                    if($language->active){
                        echo HTMLHelper::_('image', 'mod_languages/'.$language->image.'.gif', $language->title_native, array('title'=>$language->title_native), true);
                        break;
                    }
                }
            ?>
        <?php else : ?>
            <i class="fa fa-flag"></i>
        <?php endif ?>
        <i class="fa fa-caret-down"></i>
    </a>
    <ul class="dropdown-menu" role="menu">
    <?php foreach($list as $language):?>
        <?php if ($params->get('show_active', 0) || !$language->active):?>
            <li class="<?php echo $language->active ? 'lang-active' : '';?>">
            <a href="<?php echo $language->link;?>">
            <?php if ($params->get('image', 1)):?>
                <?php echo HTMLHelper::_('image', 'mod_languages/'.$language->image.'.gif', $language->title_native, array('title'=>$language->title_native), true);?>
                <span><?php echo $language->title_native; ?></span>
            <?php else : ?>
                <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef);?>
            <?php endif; ?>
            </a>
            </li>
        <?php endif;?>
    <?php endforeach;?>
    </ul>
<?php endif; ?>

<?php if ($footerText) : ?>
    <div class="posttext"><p><?php echo $footerText; ?></p></div>
<?php endif; ?>
</div>