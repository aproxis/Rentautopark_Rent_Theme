<?php
/*
 * ------------------------------------------------------------------------
 * JA Rent template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die;
$moduleclass_sfx = $params->get('moduleclass_sfx','');
?>
<ul class="categories-module<?php echo $params->get('moduleclass_sfx'); ?> mod-list">
<?php require JModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default') . '_items'); ?>
</ul>
