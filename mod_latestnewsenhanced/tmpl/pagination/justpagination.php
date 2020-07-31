<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die;

$extra_container_class = '';
if ($pagination_style && $bootstrap_version == 2) {
	$extra_container_class = ' pagination';
	if ($pagination_size) {
		$extra_container_class .= ' '.$pagination_size;
	}
}
?>
<div class="items_pagination<?php echo $extra_container_class; ?><?php echo empty($pagination_position) ? '' : ' '.$pagination_position; ?>"></div>