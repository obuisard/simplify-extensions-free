<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
//header("Content-type: text/css; charset=UTF-8");

// Pastels color scheme

// primary color: #bfd8d2
// secondary color: #fedcd2
// text color: #2e250b
// link color: #df744a
?>

@supports ((--a: 0)) {

	<?php echo $suffix; ?> {
		--primaryColor: #bfd8d2;
		--secondaryColor: #fedcd2;
		--textColor: #2e250b;
		--textOverColor: #fff;
		--iconColor: #2e250b;
		--iconOverColor: #fff;
		--linkColor: #df744a;
		--linkHoverColor: #df744a;
		--buttonColor: #df744a;
		--buttonHoverColor: #b75f3c;
		--labelColor: #df744a;
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

	<?php echo $suffix; ?> .over_head .detail_icon {
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