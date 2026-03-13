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

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

HTMLHelper::_('behavior.framework', true);
HTMLHelper::_('behavior.core');
HTMLHelper::_('formbehavior.chosen');
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('stylesheet', 'com_finder/finder.css', array('version' => 'auto', 'relative' => true));
?>

<div class="finder<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<h1>
	<?php if ($this->escape($this->params->get('page_heading'))) : ?>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	<?php else : ?>
		<?php echo $this->escape($this->params->get('page_title')); ?>
	<?php endif; ?>
</h1>
<?php endif; ?>

<?php if ($this->params->get('show_search_form', 1)) : ?>
	<div id="search-form">
		<?php echo $this->loadTemplate('form'); ?>
	</div>
<?php endif;

// Load the search results layout if we are performing a search.
if ($this->query->search === true):
?>
	<div id="search-results">
		<?php echo $this->loadTemplate('results'); ?>
	</div>
<?php endif; ?>
</div>

<script>
(function($) {
	$(document).ready(function() {
		$('#finder-search').on('keyup', function(e) {
			var btn = $('#smartsearch-btn', '#finder-search');
			if ($('#q', '#finder-search').val().length >= 3) {
				btn.removeClass('disabled');
			} else {
				btn.addClass('disabled');
			}
		})
	})
} (jQuery));
</script>