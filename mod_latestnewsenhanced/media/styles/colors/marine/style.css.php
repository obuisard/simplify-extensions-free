<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
//header("Content-type: text/css; charset=UTF-8");

// Marine color scheme

// primary color: #77c9d4
// secondary color: #a5a5af
// text color: #fff
// link color: #015249
?>

@supports ((--a: 0)) {

	<?php echo $suffix; ?> {
		--primaryColor: #77c9d4;
		--secondaryColor: #a5a5af;
		--textColor: #fff;
		--textOverColor: #fff;
		--iconColor: #fff;
		--iconOverColor: #fff;
		--linkColor: #015249;
		--linkHoverColor: #015249;
		--buttonColor: #015249;
		--buttonHoverColor: #3e8868;
		--labelColor: #015249;
	}

	<?php echo $suffix; ?> .innernews {
		background-color: var(--primaryColor);

		background: -webkit-linear-gradient(left, var(--primaryColor) 0%,#57bc90 100%);
		background: linear-gradient(to right, var(--primaryColor) 0%,#57bc90 100%);

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