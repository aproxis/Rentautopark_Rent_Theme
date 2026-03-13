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

/**
 * marker_class: Class based on the selection of text, none, or icons
 */
?>
<dl class="contact-address dl-horizontal row" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
	<?php if (($this->params->get('address_check') > 0) &&
		($this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode)) : ?>
		<?php if ($this->params->get('address_check') > 0) : ?>
			<dt>
				<span class="<?php echo $this->params->get('marker_class'); ?>" >
					<?php echo $this->params->get('marker_address'); ?>
				</span>
			</dt>
		<?php endif; ?>

		<?php if ($this->contact->address && $this->params->get('show_street_address')) : ?>
			<dd class="address-detail col-sm-4">
				<span class="contact-street" itemprop="streetAddress">
					<i class="fa fa-map-marker"> </i>
					<span><?php  echo Text::_('TPL_CONTACT_ADDRESS') ;?></span>
					<?php echo $this->contact->address; ?>
				</span>
			</dd>
		<?php endif; ?>

		<?php if ($this->contact->suburb && $this->params->get('show_suburb')) : ?>
			<dd class="address-detail col-sm-4">
				<span class="contact-suburb" itemprop="addressLocality">
					<i class="fa fa-location-arrow"></i>
					<span><?php  echo Text::_('TPL_CONTACT_ADDRESS') ;?></span>
					<?php echo $this->contact->suburb; ?>
				</span>
			</dd>
		<?php endif; ?>
		<?php if ($this->contact->state && $this->params->get('show_state')) : ?>
			<dd class="address-detail col-sm-4">
				<span class="contact-state" itemprop="addressRegion">
					<i class="fa fa-location-arrow"></i>
					<span><?php  echo Text::_('TPL_CONTACT_STATE') ;?></span>
					<?php echo $this->contact->state; ?>
				</span>
			</dd>
		<?php endif; ?>
		<?php if ($this->contact->postcode && $this->params->get('show_postcode')) : ?>
			<dd class="address-detail col-sm-4">
				<span class="contact-postcode" itemprop="postalCode">
					<i class="fa fa-magic"></i>
					<span><?php  echo Text::_('TPL_CONTACT_POSTCODE') ;?></span>
					<?php echo $this->contact->postcode; ?>
				</span>
			</dd>
		<?php endif; ?>
		<?php if ($this->contact->country && $this->params->get('show_country')) : ?>
		<dd class="address-detail col-sm-4">
			<span class="contact-country" itemprop="addressCountry">
				<i class="fa fa-building-o"></i>
				<span><?php  echo Text::_('TPL_CONTACT_COUNTRY') ;?></span>
				<?php echo $this->contact->country ; ?>
			</span>
		</dd>
		<?php endif; ?>
	<?php endif; ?>

<?php if ($this->contact->email_to && $this->params->get('show_email')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" itemprop="email">
			<?php echo nl2br($this->params->get('marker_email')); ?>
		</span>
	</dt>
	<dd class="address-detail col-sm-4">
		<span class="contact-emailto">
			<i class="fa fa-envelope-o"></i>
			<span><?php  echo Text::_('TPL_CONTACT_EMAIL') ;?></span>
			<div><?php echo $this->contact->email_to; ?></div>
		</span>
	</dd>
<?php endif; ?>

<?php if ($this->contact->telephone && $this->params->get('show_telephone')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<?php echo $this->params->get('marker_telephone'); ?>
		</span>
	</dt>
	<dd class="address-detail col-sm-4">
		<span class="contact-telephone" itemprop="telephone">
			<i class="fa fa-phone"></i>
			<span><?php  echo Text::_('TPL_CONTACT_PHONE') ;?></span>
			<?php echo nl2br($this->contact->telephone); ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->contact->fax && $this->params->get('show_fax')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<i class="fa fa-print"></i>
			<span><?php  echo Text::_('TPL_CONTACT_FAX') ;?></span>
			<?php echo $this->params->get('marker_fax'); ?>
		</span>
	</dt>
	<dd class="address-detail col-sm-4">
		<span class="contact-fax" itemprop="faxNumber">
			<i class="fa fa-print"></i>
			<span><?php  echo Text::_('TPL_CONTACT_FAX') ;?></span>
			<?php echo nl2br($this->contact->fax); ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->contact->mobile && $this->params->get('show_mobile')) :?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" >
			<?php echo $this->params->get('marker_mobile'); ?>
		</span>
	</dt>
	<dd class="address-detail col-sm-4">
		<span class="contact-mobile" itemprop="telephone">
			<i class="fa fa-phone-square"></i>
			<span><?php  echo Text::_('TPL_CONTACT_MOBILE') ;?></span>
			<?php echo nl2br($this->contact->mobile); ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->contact->webpage && $this->params->get('show_webpage')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" >
		</span>
	</dt>
	<dd class="address-detail col-sm-4">
		<span class="contact-webpage">
			<i class="fa fa-globe"></i><a href="<?php echo $this->contact->webpage; ?>" target="_blank" itemprop="url">
			<?php echo $this->contact->webpage; ?></a>
		</span>
	</dd>
<?php endif; ?>
</dl>
