<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
//header("Content-type: text/css; charset=UTF-8");

// Denim color scheme

// primary color: #2a313b
// secondary color: #90acba
// text color: #ebe8d8
// link color: #fd6409
?>

@supports ((--a: 0)) {

	<?php echo $suffix; ?> {
		--primaryColor: #2a313b;
		--secondaryColor: #90acba;
		--textColor: #ebe8d8;
		--textOverColor: #ebe8d8;
		--iconColor: #ebe8d8;
		--iconOverColor: #ebe8d8;
		--linkColor: #fd6409;
		--linkHoverColor: #fd6409;
		--buttonColor: #fd6409;
		--buttonHoverColor: #ca4f05;
		--labelColor: #fd6409;
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