<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
//header("Content-type: text/css; charset=UTF-8");

// Portfolio color scheme

// primary color: #e3e3e3
// secondary color: #caebf2
// text color: #4c5759
// link color: #ff3940
?>

@supports ((--a: 0)) {

	<?php echo $suffix; ?> {
		--primaryColor: #e3e3e3;
		--secondaryColor: #caebf2;
		--textColor: #4c5759;
		--textOverColor: #fff;
		--iconColor: #4c5759;
		--iconOverColor: #fff;
		--linkColor: #ff3940;
		--linkHoverColor: #ff3940;
		--buttonColor: #ff3940;
		--buttonHoverColor: #c62b30;
		--labelColor: #ff3940;
	}

	<?php echo $suffix; ?> .innernews {
		background-color: var(--primaryColor);
		color: var(--textColor);
		border-color: var(--primaryColor);
	}

	<?php echo $suffix; ?> .innernews a:not(.btn) {
		color: var(--linkColor);
	}

	<?php echo $suffix; ?> .innernews a:not(.btn):hover,
	<?php echo $suffix; ?> .innernews a:not(.btn):focus {
		color: var(--linkHoverColor);
	}

	<?php echo $suffix; ?> .innernews a.btn.btn-theme {
		background-color: var(--buttonColor);
		background-image: none;
		color: #fff;
		border-color: transparent;
		text-shadow: none;
	}

	<?php echo $suffix; ?> .innernews a.btn.btn-theme:hover,
	<?php echo $suffix; ?> .innernews a.btn.btn-theme:focus {
		background-color: var(--buttonHoverColor);
		color: #fff;
	}

	<?php echo $suffix; ?> .newsextra {
		color: var(--textColor);
	}

	<?php echo $suffix; ?> .over_head .newsextra {
		color: var(--textOverColor);
	}

	<?php echo $suffix; ?> .newsextra .detail_icon {
	    color: var(--iconColor);
	}

	<?php echo $suffix; ?> .over_head .newsextra .detail_icon {
	    color: var(--iconOverColor);
	}

	<?php echo $suffix; ?> .innernews .bg-theme {
		background-color: var(--labelColor);
		color: #fff!important;
	}

	<?php echo $suffix; ?> .newshead .picture,
	<?php echo $suffix; ?> .newshead .nopicture,
	<?php echo $suffix; ?> .newshead .calendar,
	<?php echo $suffix; ?> .newshead .nocalendar,
	<?php echo $suffix; ?> .newshead .icon,
	<?php echo $suffix; ?> .newshead .noicon,
	<?php echo $suffix; ?> .newshead .video,
	<?php echo $suffix; ?> .newshead .novideo {
		background-color: var(--secondaryColor);
	}

}