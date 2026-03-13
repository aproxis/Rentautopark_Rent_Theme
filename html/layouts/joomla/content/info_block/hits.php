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

defined('JPATH_BASE') or die;

?>
			<dd class="hits">
					<span class="fa fa-eye"></span>
					<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $displayData['item']->hits; ?>" />
					<?php echo JText::sprintf($displayData['item']->hits); ?>
			</dd>