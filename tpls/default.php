<?php
defined('_JEXEC') or die;
JHtml::_('formbehavior.chosen', 'select');
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>"
	  class='<jdoc:include type="pageclass" />'>

<head>
	<jdoc:include type="head" />
	<?php $this->loadBlock('head') ?>
  <?php $this->addCss('layouts/docs') ?>
	    <!-- LOGO -->
        <div class="col-xs-12 col-sm-3 logo">
            <div class="logo-<?php echo $logotype, ($logoimgsm ? ' logo-control' : '') ?>">
                <a href="<?php echo JUri::base() ?>" title="<?php echo strip_tags($sitename) ?>">
                    <?php if($logotype == 'image'): ?>
                        <img class="logo-img" src="<?php echo JUri::base(true) . '/' . $logoimage ?>" alt="<?php echo strip_tags($sitename) ?>" />
                    <?php endif ?>
                    <?php if($logoimgsm) : ?>
                        <img class="logo-img-sm" src="<?php echo JUri::base(true) . '/' . $logoimgsm ?>" alt="<?php echo strip_tags($sitename) ?>" />
                    <?php endif ?>
                    <span><?php echo $sitename ?></span>
                </a>
                <small class="site-slogan"><?php echo $slogan ?></small>
            </div>
        </div>
        <!-- //LOGO -->
</head>

<body>

<div class="t3-wrapper"> <!-- Need this wrapper for off-canvas menu. Remove if you don't use of-canvas -->
  <?php $this->loadBlock('topbar') ?>

  <?php $this->loadBlock('header') ?>

  <?php $this->loadBlock('masthead') ?>

  <?php $this->loadBlock('spotlight-1') ?>

  <?php $this->loadBlock('mainbody') ?>

  <?php $this->loadBlock('spotlight-2') ?>

  <?php $this->loadBlock('navhelper') ?>

  <?php $this->loadBlock('footer') ?>

</div>

</body>

</html>