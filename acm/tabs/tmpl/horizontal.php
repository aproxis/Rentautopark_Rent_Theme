<?php
/**
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
?>

<?php
	jimport( 'joomla.application.module.helper' );
	$items_position = $helper->get('position');
	$mods = JModuleHelper::getModules($items_position);
?>
<div id="mod-<?php echo $module->id ?>" class="t3-tabs t3-tabs-horizontal">	
	<div class="row">
		<!-- BEGIN: TAB NAV -->
		<ul class="nav nav-tabs" role="tablist">
			<?php
			$i = 0;
			foreach ($mods as $mod):
				?>
				<li class="<?php if ($i < 1) echo "active"; ?>">
					<a href="#mod-<?php echo $module->id ?> .mod-<?php echo $mod->id ?>" role="tab"
						 data-toggle="tab"><?php echo $mod->title ?></a>
				</li>
				<?php
				$i++;
			endforeach
			?>

		</ul>
		<!-- BEGIN: TAB PANES -->
		<div class="tab-content">
			<?php
			echo $helper->renderModules($items_position,
				array(
					'style'=>'ACMContainerItems',
					'active'=>0,
					'tag'=>'div',
					'class'=>'tab-pane fade'
				))
			?>
		</div>
		<!-- END: TAB PANES -->
	</div>
</div>