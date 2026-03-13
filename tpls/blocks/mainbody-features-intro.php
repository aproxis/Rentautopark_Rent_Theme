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
jimport( 'joomla.application.module.helper' );
?>

<div id="t3-features-intro" class="features-intro">
	<?php if ($this->countModules('section-1')) : ?>
		<jdoc:include type="modules" name="<?php $this->_p('section-1') ?>" style="T3Section" />
	<?php endif ?>

  <?php $this->loadBlock('mainbody') ?>

  <?php if ($this->countModules('section-2')) : ?>
    <jdoc:include type="modules" name="<?php $this->_p('section-2') ?>" style="T3Section" />
  <?php endif ?>

  <?php if ($this->countModules('section-3')) : ?>
    <jdoc:include type="modules" name="<?php $this->_p('section-3') ?>" style="T3Section" />
  <?php endif ?>

  <?php if ($this->countModules('section-4')) : ?>
    <jdoc:include type="modules" name="<?php $this->_p('section-4') ?>" style="T3Section" />
  <?php endif ?>

  <?php if ($this->countModules('section-5')) : ?>
    <jdoc:include type="modules" name="<?php $this->_p('section-5') ?>" style="T3Section" />
  <?php endif ?>

  <?php if ($this->countModules('section-6')) : ?>
    <jdoc:include type="modules" name="<?php $this->_p('section-6') ?>" style="T3Section" />
  <?php endif ?>

  <div class="wrap">
    <div class="container">
      <?php if($this->hasMessage()) : ?>
      <jdoc:include type="message" />
      <?php endif ?>
    </div>
  </div>
</div>
