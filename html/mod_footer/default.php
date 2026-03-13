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

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

?>
<div class="module">
	<small class="footer1<?php echo $moduleclass_sfx; ?>"><?php echo $lineone; ?> Designed by <a href="http://www.joomlart.com/" title="Visit Joomlart.com!" <?php echo method_exists('T3', 'isHome') && T3::isHome() ? '' : 'rel="nofollow"' ?>>JoomlArt.com</a>.</small>
	<small class="footer2<?php echo $moduleclass_sfx; ?>"><?php echo Text::_( 'MOD_FOOTER_LINE2' ); ?></small>
</div>