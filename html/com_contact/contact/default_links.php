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
use Joomla\CMS\Language\Text;

if(version_compare(JVERSION, '4', 'ge')){
  $presentation_style = 'plain';
} else {
  $presentation_style = $this->$tparams->get('presentation_style');
}

if ($presentation_style=='sliders'):?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title">
			<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-links">
			<?php echo Text::_('COM_CONTACT_LINKS');?>
			</a>
		</h4>
	</div>
	<div id="display-links" class="panel-collapse collapse">
		<div class="panel-body">
<?php endif; ?>
<?php if ($presentation_style == 'tabs') : ?>
<div id="display-links" class="tab-pane">
<?php endif; ?>


			<div class="contact-links">
				<ul class="nav nav-stacked">
					<?php
					foreach (range('a', 'e') as $char) :// letters 'a' to 'e'
						$link = $this->contact->params->get('link'.$char);
						$label = $this->contact->params->get('link'.$char.'_name');

						if (!$link) :
							continue;
						endif;

						// Add 'http://' if not present
						$link = (0 === strpos($link, 'http')) ? $link : 'http://'.$link;

						// If no label is present, take the link
						$label = $label ?: $link;
						?>
						<li>
							<a href="<?php echo $link; ?>" class="<?php echo str_replace(" ","-",strtolower($label));?>">
								<?php echo $label; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

<?php if ($presentation_style=='sliders'):?>
		</div>
	</div>
</div>
<?php endif; ?>
<?php if ($presentation_style == 'tabs') : ?>
</div>
<?php endif; ?>
