<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
if (!isset($this->error)) {
	$this->error = new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 404);
	$this->debug = false;
}
//get language and direction
$doc = Factory::getDocument();
$this->language = $doc->language;
$this->direction = $doc->direction;
$theme = Factory::getApplication()->getTemplate(true)->params->get('theme', '');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<title><?php echo $this->error->getCode(); ?> - <?php echo $this->title; ?></title>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/error.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/bootstrap.css" type="text/css" />
	<?php if($theme && is_file(T3_TEMPLATE_PATH . '/css/themes/' . $theme . '/error.css')):?>
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/themes/<?php echo $theme ?>/error.css" type="text/css" />
	<?php endif; ?>
	<?php 
	if ($this->direction == 'rtl') : ?>
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/error_rtl.css" type="text/css" />
	<?php endif; ?>
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='http://fonts.googleapis.com/css?family=Roboto:400,900,300,700' rel='stylesheet' type='text/css'>
	<script src="<?php echo $this->baseurl ?>/media/jui/js/jquery.min.js" type="text/javascript"></script>
	<script src="<?php echo $this->baseurl ?>/media/jui/js/jquery-noconflict.js" type="text/javascript"></script>
	<script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/owl-carousel/owl.carousel.js" type="text/javascript"></script>
	<link href='<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/owl-carousel/owl.carousel.css' rel='stylesheet' type='text/css'>
</head>
<body class="page-error">

	<!-- HEADER -->
	<header id="t3-header" class="wrap t3-header">
		<div class="container">
			<div class="row">

				<!-- LOGO -->
				<div class="col-xs-12 col-sm-2 logo">
		    		<a title="Welcome to JaRent" href="<?php echo $this->baseurl; ?>/index.php">
		    			<img class="logo-img" alt="JARent" src="<?php echo $this->baseurl; ?>/templates/ja_rent/images/logo.png">
		    		</a>
				</div>
				<!-- //LOGO -->

				<!-- MAIN NAVIGATION -->
				<?php if (count(JModuleHelper::getModules('error-menu'))) : ?>
					<div class="col-xs-12 col-sm-10 navbar navbar-default t3-mainnav">
					<?php
						$modules	= JModuleHelper::getModules('error-menu');
						$params		= array('style' => 'raw');
						
						foreach ($modules as $module) {
							echo JModuleHelper::renderModule($module, $params);
						}
					?>
					</div>
				<?php endif ?>
				<!-- //MAIN NAVIGATION -->

			</div>
		</div>
	</header>
	<!-- //HEADER -->

	<div class="main">
		<div class="error">
			<div id="outline">
				<div id="errorboxoutline">
					<div class="small-title">oops!</div>
					<div class="error-code">
						<?php 
							$errcode = str_split($this->error->getCode());
							$i = 0;
							$lastclass='';
							foreach($errcode as $c){
														$firstclass = ($i==0)?'first':'';
								if($i==(count($errcode)-1)){
									$lastclass='last';
								}
								echo '<span class="'.$lastclass.$firstclass.'">'.$c.'</span>';
								$i++;
							}
						?>
						<span>page error</span>
					</div>
					<img class="icon-404" alt="" src="images/404.jpg">
					
					<div class="error-message"><h2><?php echo $this->error->getMessage(); ?></h2></div>
					
					<div id="errorboxbody">
						<p><?php echo Text::_('JERROR_LAYOUT_PLEASE_TRY_ONE_OF_THE_FOLLOWING_PAGES'); ?></p>
					</div>
					
					<a class="button-home" href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo Text::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo Text::_('JERROR_LAYOUT_HOME_PAGE'); ?></a>

				</div>
			</div>
		</div>
	</div>

	<!-- FOOTER -->
	<footer id="t3-footer" class="wrap t3-footer">
		<div class="container">
			<div class="row">

				<!-- footer 1 -->
				<?php if (count(JModuleHelper::getModules('footer-1'))) : ?>
					<div class="col-xs-12 col-sm-3 footer-1">
					<?php
						$modules	= JModuleHelper::getModules('footer-1');
						$params		= array('style' => 'raw');
						
						foreach ($modules as $module) {
							echo JModuleHelper::renderModule($module, $params);
						}
					?>
					</div>
				<?php endif ?>
				<!-- // footer 1 -->

				<!-- footer 2 -->
				<?php if (count(JModuleHelper::getModules('footer-2'))) : ?>
					<div class="col-xs-12 col-sm-3 footer-2">
					<?php
						$modules	= JModuleHelper::getModules('footer-2');
						$params		= array('style' => 'raw');
						
						foreach ($modules as $module) {
							echo JModuleHelper::renderModule($module, $params);
						}
					?>
					</div>
				<?php endif ?>
				<!-- // footer 2 -->

				<!-- footer 3 -->
				<?php if (count(JModuleHelper::getModules('footer-3'))) : ?>
					<div class="col-xs-12 col-sm-3 footer-3">
					<?php
						$modules	= JModuleHelper::getModules('footer-3');
						$params		= array('style' => 'raw');
						
						foreach ($modules as $module) {
							echo JModuleHelper::renderModule($module, $params);
						}
					?>
					</div>
				<?php endif ?>
				<!-- // footer 3 -->

				<!-- footer 4 -->
				<?php if (count(JModuleHelper::getModules('footer-4'))) : ?>
					<div class="col-xs-12 col-sm-3 footer-4">
					<?php
						$modules	= JModuleHelper::getModules('footer-4');
						$params		= array('style' => 'raw');
						
						foreach ($modules as $module) {
							echo JModuleHelper::renderModule($module, $params);
						}
					?>
					</div>
				<?php endif ?>
				<!-- // footer 4 -->
			</div>
		</div>

	</footer>
	<!-- //FOOTER -->
		<section class="t3-copyright">
			<div class="container">
				<div class="row">
		            <a href="http://twitter.github.io/bootstrap/" target="_blank">Bootstrap</a> is a front-end framework of Twitter, Inc. Code licensed under <a href="http://www.apache.org/licenses/LICENSE-2.0" target="_blank">Apache License v2.0</a>.
				</div>
			</div>
		</section>
</body>
</html>
