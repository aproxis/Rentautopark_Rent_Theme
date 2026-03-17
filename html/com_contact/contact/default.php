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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper;

jimport('joomla.html.html.bootstrap');

$cparams = ComponentHelper::getParams('com_media');
$tparams = $this->item->params;
$htag    = $tparams->get('show_page_heading') ? 'h2' : 'h1';

if(version_compare(JVERSION, '4', 'ge')) {
	$this->contact = $this->item;
	$canDo   = ContentHelper::getActions('com_contact', 'category', $this->item->catid);
	$canEdit = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by === Factory::getUser()->id);
} 
?>
<link rel="stylesheet" href="<?php echo JURI::base(true); ?>/templates/rent/css/contact-styles.css">
<div class="contact <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="https://schema.org/Person">
	<!-- Header Section with Dynamic Title -->
	<section class="relative py-20 bg-gradient-to-br from-[#0a0a0a] via-[#1a1a1a] to-[#0a0a0a] text-white overflow-hidden">
		<div class="absolute inset-0 opacity-10">
			<div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0px); background-size: 40px 40px;"></div>
		</div>
		<div class="relative container mx-auto px-4">
			<div class="max-w-4xl mx-auto text-center">
				<?php if ($tparams->get('show_page_heading')) : ?>
					<h1 class="text-5xl md:text-6xl font-bold mb-6">
						<?php echo $this->escape($tparams->get('page_heading')); ?>
					</h1>
				<?php elseif ($this->contact->name && $tparams->get('show_name')) : ?>
					<h1 class="text-5xl md:text-6xl font-bold mb-6">
						<?php if ($this->item->published == 0) : ?>
							<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
						<?php endif; ?>
						<?php echo $this->contact->name; ?>
					</h1>
				<?php else : ?>
					<h1 class="text-5xl md:text-6xl font-bold mb-6">
						<?php echo Text::_('COM_CONTACT_CONTACT_DETAILS'); ?>
					</h1>
				<?php endif; ?>
				<p class="text-xl text-gray-300"><?php echo Text::_('COM_CONTACT_CONTACT_US'); ?></p>
			</div>
		</div>
	</section>

	<!-- Contact Information Cards Section -->
	<section class="py-12 bg-white">
		<div class="container mx-auto px-4">
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
				<?php if (!empty($this->contact->address) || !empty($this->contact->suburb) || !empty($this->contact->state) || !empty($this->contact->country) || !empty($this->contact->postcode)) : ?>
				<div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
					<div class="w-12 h-12 bg-gradient-to-br from-[#FE5001] to-[#E54801] rounded-lg flex items-center justify-center mb-4">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
							<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
							<circle cx="12" cy="10" r="3"></circle>
						</svg>
					</div>
					<h3 class="text-lg font-bold text-foreground mb-2"><?php echo Text::_('COM_CONTACT_ADDRESS'); ?></h3>
					<p class="text-gray-600 text-sm whitespace-pre-line">
						<?php if (!empty($this->contact->address)) echo $this->contact->address . "\n"; ?>
						<?php if (!empty($this->contact->suburb)) echo $this->contact->suburb . "\n"; ?>
						<?php if (!empty($this->contact->state)) echo $this->contact->state . "\n"; ?>
						<?php if (!empty($this->contact->country)) echo $this->contact->country . "\n"; ?>
						<?php if (!empty($this->contact->postcode)) echo $this->contact->postcode; ?>
					</p>
				</div>
				<?php endif; ?>
				
				<?php if (!empty($this->contact->telephone) || !empty($this->contact->mobile) || !empty($this->contact->fax)) : ?>
				<div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
					<div class="w-12 h-12 bg-gradient-to-br from-[#FE5001] to-[#E54801] rounded-lg flex items-center justify-center mb-4">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
							<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
						</svg>
					</div>
					<h3 class="text-lg font-bold text-foreground mb-2"><?php echo Text::_('COM_CONTACT_TELEPHONE'); ?></h3>
					<p class="text-gray-600 text-sm whitespace-pre-line">
						<?php if (!empty($this->contact->telephone)) echo $this->contact->telephone . "\n"; ?>
						<?php if (!empty($this->contact->mobile)) echo $this->contact->mobile . "\n"; ?>
						<?php if (!empty($this->contact->fax)) echo $this->contact->fax; ?>
					</p>
				</div>
				<?php endif; ?>
				
				<?php if (!empty($this->contact->email_to)) : ?>
				<div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
					<div class="w-12 h-12 bg-gradient-to-br from-[#FE5001] to-[#E54801] rounded-lg flex items-center justify-center mb-4">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
							<rect width="20" height="16" x="2" y="4" rx="2"></rect>
							<path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
						</svg>
					</div>
					<h3 class="text-lg font-bold text-foreground mb-2"><?php echo Text::_('COM_CONTACT_EMAIL_LABEL'); ?></h3>
					<p class="text-gray-600 text-sm whitespace-pre-line"><?php echo $this->contact->email_to; ?></p>
				</div>
				<?php endif; ?>
				
				<?php if (!empty($this->contact->misc)) : ?>
				<div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
					<div class="w-12 h-12 bg-gradient-to-br from-[#FE5001] to-[#E54801] rounded-lg flex items-center justify-center mb-4">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
							<circle cx="12" cy="12" r="10"></circle>
							<polyline points="12 6 12 12 16 14"></polyline>
						</svg>
					</div>
					<h3 class="text-lg font-bold text-foreground mb-2"><?php echo Text::_('COM_CONTACT_OTHER_INFORMATION'); ?></h3>
					<p class="text-gray-600 text-sm whitespace-pre-line"><?php echo $this->contact->misc; ?></p>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- Content wrapper -->
	<div class="container mx-auto px-4 py-8">
	
	<?php if ($tparams->get('show_contact_category') == 'show_no_link') : ?>
		<h3>
			<span class="contact-category"><?php echo $this->contact->category_title; ?></span>
		</h3>
	<?php endif; ?>
	<?php if ($tparams->get('show_contact_category') == 'show_with_link') : ?>
		<?php $contactLink = RouteHelper::getCategoryRoute($this->item->catid, $this->item->language); ?>
		<h3>
			<span class="contact-category"><a href="<?php echo $contactLink; ?>">
				<?php echo $this->escape($this->contact->category_title); ?></a>
			</span>
		</h3>
	<?php endif; ?>
	
	<?php echo $this->item->event->afterDisplayTitle; ?>

	<?php if(version_compare(JVERSION, '4', 'ge')) {
		$presentation_style = 'plain';
		} else {
		$presentation_style = $tparams->get('presentation_style');
	}; ?>

	
	<!-- JA Override Contact From for case "plain" -->
	<?php if($presentation_style == 'plain') :?>
		
	<div class="<?php echo $presentation_style ?>-style">
		<!-- Show Contact name -->
		<?php if ($this->contact->name && $tparams->get('show_name')) : ?>
			<div class="page-header">
				<span><?php  echo Text::_('TPL_CONTACT_US') ;?></span>
				<h2>
					<?php if ($this->item->published == 0) : ?>
						<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
					<?php endif; ?>
					<?php echo $this->contact->name; ?>
				</h2>
			</div>
		<?php endif;  ?>
		<!-- End Show Contact name -->
	
		<div class="address-contact container">
			<div class="address-top">
				<?php echo $this->loadTemplate('address_top]'); ?>
			</div>
		</div>
		
		<div class="detail-contact">
			<div class="container">
				<div class="row">
					<!-- Show form contact -->
					<div class="col-sm-12 contact-bottom">
						

						<?php if ($tparams->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?>
							
							<div class="form-title">
								<span><?php  echo Text::_('COM_CONTACT_EMAIL_FORM');  ?></span>
								<h3><?php  echo Text::_('TPL_CONTACT_ASK') ;?></h3>
							</div>

							<?php echo $this->loadTemplate('form'); ?>
						<?php endif; ?> <!-- // Show email form -->
					</div>
					<!-- End Show form contact -->	

					<!-- Show Other information -->
					<div class="col-sm-12 contact-information">
						<div class="inner">

							<div class="box-contact">	
								<!-- Contact other information -->
								<div class="contact-miscinfo">
									<dl class="dl-horizontal">
										<dt>
											<span class="<?php echo $tparams->get('marker_class'); ?>">
												<?php echo $tparams->get('marker_misc'); ?>
											</span>
										</dt>
										<dd>
											<span class="contact-misc">
												<?php echo $this->contact->misc; ?>
											</span>
										</dd>
									</dl>
								</div>
								<!-- End other information -->
							</div>
				
							<!-- Contact images -->
							<?php if ($this->contact->image && $tparams->get('show_image')) : ?>
								<div class="contact-image">
									<?php echo HTMLHelper::_('image', $this->contact->image, Text::_('COM_CONTACT_IMAGE_DETAILS'), array('align' => 'middle')); ?>
								</div>
							<?php endif; ?>
							<!-- End Contact images -->
							
							<div class="box-contact">	
								<!-- Contact -->			
								<?php if ($this->contact->con_position && $tparams->get('show_position')) : ?>
									<?php  echo '<h3>'. Text::_('COM_CONTACT_DETAILS').'</h3>';  ?>
									
									<dl class="contact-position dl-horizontal">
										<dd>
											<?php echo $this->contact->con_position; ?>
										</dd>
									</dl>
								<?php endif; ?>									
						
								<?php if ($tparams->get('allow_vcard')) :	?>
									<?php echo Text::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS');?>
										<a href="<?php echo Route::_('index.php?option=com_contact&amp;view=contact&amp;id='.$this->contact->id . '&amp;format=vcf'); ?>">
										<?php echo Text::_('COM_CONTACT_VCARD');?></a>
								<?php endif; ?>
								<!-- End contact-->
							</div>

							<div class="box-contact">	
								<!-- Contact links -->
								<?php if ($tparams->get('show_links')) : ?>
									<?php echo $this->loadTemplate('links'); ?>
								<?php endif; ?>
								<!-- End contact Links -->
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- End Show -->
	</div>
	<?php endif;?>
	<!-- End Override -->
	
	<?php echo $this->item->event->beforeDisplayContent; ?>

	<?php if ($tparams->get('show_contact_list') && count($this->contacts) > 1) : ?>
		<form action="#" method="get" name="selectForm" id="selectForm">
			<?php echo Text::_('COM_CONTACT_SELECT_CONTACT'); ?>
			<?php echo HTMLHelper::_('select.genericlist', $this->contacts, 'id', 'class="input" onchange="document.location.href = this.value"', 'link', 'name', $this->contact->link);?>
		</form>
	<?php endif; ?>

	<?php if ($tparams->get('show_tags', 1) && !empty($this->item->tags)) : ?>
		<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
		<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
	<?php endif; ?>
	
	<?php if ($presentation_style == 'sliders') : ?>
		<div class="<?php echo $presentation_style ?>-style">

			<!-- Show Contact name -->
			<?php if ($this->contact->name && $tparams->get('show_name')) : ?>
				<div class="page-header">
					<span><?php  echo Text::_('TPL_CONTACT_US') ;?></span>
					<h2>
						<?php if ($this->item->published == 0) : ?>
							<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
						<?php endif; ?>
						<?php echo $this->contact->name; ?>
					</h2>
				</div>
			<?php endif;  ?>
			<!-- End Show Contact name -->
			<div class="container">
				<div class="panel-group" id="slide-contact">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#basic-details">
								<?php echo Text::_('COM_CONTACT_DETAILS');?>
								</a>
							</h4>
						</div>
						<div id="basic-details" class="panel-collapse collapse in">
							<div class="panel-body">
	<?php endif; ?>
	<?php if ($presentation_style == 'tabs'):?>
		<div class="<?php echo $presentation_style ?>-style">

			<!-- Show Contact name -->
			<?php if ($this->contact->name && $tparams->get('show_name')) : ?>
				<div class="page-header">
					<span><?php  echo Text::_('TPL_CONTACT_US') ;?></span>
					<h2>
						<?php if ($this->item->published == 0) : ?>
							<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
						<?php endif; ?>
						<?php echo $this->contact->name; ?>
					</h2>
				</div>
			<?php endif;  ?>
			<!-- End Show Contact name -->

			<div class="container">
				<ul class="nav nav-tabs" id="myTab">
						<li class="active"><a data-toggle="tab" href="#basic-details"><?php echo Text::_('COM_CONTACT_DETAILS'); ?></a></li>
						<?php if ($tparams->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?><li><a data-toggle="tab" href="#display-form"><?php echo Text::_('COM_CONTACT_EMAIL_FORM'); ?></a></li><?php endif; ?>
						<?php if ($tparams->get('show_links')) : ?><li><a data-toggle="tab" href="#display-links"><?php echo Text::_('COM_CONTACT_LINKS'); ?></a></li><?php endif; ?>
						<?php if ($tparams->get('show_articles') && $this->contact->user_id && $this->contact->articles) : ?><li><a data-toggle="tab" href="#display-articles"><?php echo Text::_('JGLOBAL_ARTICLES'); ?></a></li><?php endif; ?>
						<?php if ($tparams->get('show_profile') && $this->contact->user_id && PluginHelper::isEnabled('user', 'profile')) : ?><li><a data-toggle="tab" href="#display-profile"><?php echo Text::_('COM_CONTACT_PROFILE'); ?></a></li><?php endif; ?>
						<?php if ($this->contact->misc && $tparams->get('show_misc')) : ?><li><a data-toggle="tab" href="#display-misc"><?php echo Text::_('COM_CONTACT_OTHER_INFORMATION'); ?></a></li><?php endif; ?>
				</ul>
				<div class="tab-content" id="myTabContent">
					<div id="basic-details" class="tab-pane active">
	<?php endif; ?>
	
	<?php if ($this->contact->image && $tparams->get('show_image') && ($presentation_style != 'plain')) : ?>
		<div class="thumbnail pull-right">
			<?php echo HTMLHelper::_('image', $this->contact->image, Text::_('COM_CONTACT_IMAGE_DETAILS'), array('align' => 'middle', 'itemprop' => 'image')); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->contact->con_position && $tparams->get('show_position') && ($presentation_style != 'plain')) : ?>
		<dl class="contact-position dl-horizontal">
			<dd itemprop="jobTitle">
				<?php echo $this->contact->con_position; ?>
			</dd>
		</dl>
	<?php endif; ?>
	
	<?php if($presentation_style != 'plain') :?>
	
	<?php echo $this->loadTemplate('address'); ?>
	
	<?php endif;?>
	
	<?php if ($tparams->get('allow_vcard') && ($presentation_style != 'plain')) :	?>
		<?php echo Text::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS');?>
			<a href="<?php echo Route::_('index.php?option=com_contact&amp;view=contact&amp;id='.$this->contact->id . '&amp;format=vcf'); ?>">
			<?php echo Text::_('COM_CONTACT_VCARD');?></a>
	<?php endif; ?>

	<?php if ($presentation_style=='sliders'):?>
					</div>
				</div>
			</div>
	<?php endif; ?>
	
	<?php if ($presentation_style == 'tabs') : ?>
			</div>
	<?php endif; ?>
	<?php if ($tparams->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?>

		<?php if ($presentation_style=='sliders'):?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-form">
						<?php echo Text::_('COM_CONTACT_EMAIL_FORM');?>
						</a>
					</h4>
				</div>
				<div id="display-form" class="panel-collapse collapse">
					<div class="panel-body">
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			<div id="display-form" class="tab-pane">
		<?php endif; ?>
		
		<?php if($presentation_style != 'plain') :?>
		<?php  echo $this->loadTemplate('form');  ?>
		<?php endif;?>
		
		<?php if ($presentation_style=='sliders'):?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	
	<?php if ($tparams->get('show_links') && ($presentation_style != 'plain')) : ?>
		<?php echo $this->loadTemplate('links'); ?>
	<?php endif; ?>
		
	<?php if ($tparams->get('show_articles') && $this->contact->user_id && $this->contact->articles) : ?>
		<?php if ($presentation_style=='sliders'):?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-articles">
						<?php echo Text::_('JGLOBAL_ARTICLES');?>
						</a>
					</h4>
				</div>
				<div id="display-articles" class="panel-collapse collapse">
					<div class="panel-body">
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			<div id="display-articles" class="tab-pane">
		<?php endif; ?>
		<?php if  ($presentation_style=='plain'):?>
			<?php echo '<h3>'. Text::_('JGLOBAL_ARTICLES').'</h3>'; ?>
		<?php endif; ?>
			<?php echo $this->loadTemplate('articles'); ?>
		<?php if ($presentation_style=='sliders'):?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($tparams->get('show_profile') && $this->contact->user_id && PluginHelper::isEnabled('user', 'profile')) : ?>
		<?php if ($presentation_style=='sliders'):?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-profile">
						<?php echo Text::_('COM_CONTACT_PROFILE');?>
						</a>
					</h4>
				</div>
				<div id="display-profile" class="panel-collapse collapse">
					<div class="panel-body">
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			<div id="display-profile" class="tab-pane">
		<?php endif; ?>
		<div class="profile-user">
			<?php if ($presentation_style=='plain'):?>
				<?php echo '<h3>'. Text::_('COM_CONTACT_PROFILE').'</h3>'; ?>
			<?php endif; ?>
			<?php echo $this->loadTemplate('profile'); ?>
		</div>
		<?php if ($presentation_style=='sliders'):?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	
	<?php if ($tparams->get('show_user_custom_fields') && $this->contactUser) : ?>
		<?php echo $this->loadTemplate('user_custom_fields'); ?>
	<?php endif; ?>

	<?php if ($this->contact->misc && $tparams->get('show_misc')) : ?>
		<?php if ($presentation_style=='sliders'):?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-misc">
						<?php echo Text::_('COM_CONTACT_OTHER_INFORMATION');?>
						</a>
					</h4>
				</div>
				<div id="display-misc" class="panel-collapse collapse">
					<div class="panel-body">
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			<div id="display-misc" class="tab-pane">
		<?php endif; ?>
		
		<?php if($presentation_style != 'plain') :?>
				<div class="contact-miscinfo">
					<dl class="dl-horizontal">
						<dt>
							<span class="<?php echo $tparams->get('marker_class'); ?>">
								<?php echo $tparams->get('marker_misc'); ?>
							</span>
						</dt>
						<dd>
							<span class="contact-misc">
								<?php echo $this->contact->misc; ?>
							</span>
						</dd>
					</dl>
				</div>
		<?php endif;?>		
		<?php if ($presentation_style=='sliders'):?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($presentation_style == 'tabs') : ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($presentation_style=='sliders'):?>
					<script type="text/javascript">
						(function($){
							$('#slide-contact').collapse({ parent: false, toggle: true, active: 'basic-details'});
						})(jQuery);
					</script>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($presentation_style == 'tabs') : ?>
		</div>
		</div>
	<?php endif; ?>
	<?php echo $this->item->event->afterDisplayContent; ?>
	</div>
</div>
