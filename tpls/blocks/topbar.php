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
*/

defined('_JEXEC') or die;
?>

<!-- TOPBAR -->
<div id="t3-topbar" class="wrap t3-topbar">
	<div class="container">
	<div class="row">
		<div class="topbar-1 col-xs-12 col-sm-5 <?php $this->_c('topbar-1') ?>">
			<?php if ($this->countModules('topbar-1')) : ?>
				<jdoc:include type="modules" name="<?php $this->_p('topbar-1') ?>" style="raw" />
			<?php endif; ?>
		</div>
		<div class="topbar-2 col-xs-12 col-sm-7">
			<?php if ($this->countModules('loginload')) : ?>
				<!-- LOGIN FORM -->
				<div class="loginload <?php $this->_c('loginload') ?>">
					<jdoc:include type="modules" name="<?php $this->_p('loginload') ?>" style="T3None" />
				</div>
				<!-- //LOGIN FORM -->
			<?php endif ?>

			<?php if ($this->countModules('languageswitcherload')) : ?>
				<!-- LANGUAGE SWITCHER -->
				<div class="languageswitcherload <?php $this->_c('languageswitcherload') ?>">
					<jdoc:include type="modules" name="<?php $this->_p('languageswitcherload') ?>" style="raw" />
				</div>
				<!-- //LANGUAGE SWITCHER -->
			<?php endif ?>
		</div>
	</div>
	</div>
</div>
<!-- //TOPBAR -->
