<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
header("Content-type: text/css; charset=UTF-8");

// Earth color scheme

// primary color: #34421e
// secondary color: #8d9739
// text color: #f1f1ef
// link color: #c19434
?>

@supports ((--a: 0)) {

	<?php echo $suffix; ?> {
		--primaryColor: #34421e;
		--secondaryColor: #8d9739;
		--textColor: #f1f1ef;
		--textOverColor: #f1f1ef;
		--iconColor: #f1f1ef;
		--iconOverColor: #f1f1ef;
		--linkColor: #c19434;
		--linkHoverColor: #c19434;
		--buttonColor: #c19434;
		--buttonHoverColor: #ffc444;
		--labelColor: #c19434;
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
	
	<?php echo $suffix; ?> .newsextra [class^="SYWicon-"],
	<?php echo $suffix; ?> .newsextra [class*=" SYWicon-"] {
	    color: var(--iconColor);
	}
	
	<?php echo $suffix; ?> .over_head .newsextra [class^="SYWicon-"],
	<?php echo $suffix; ?> .over_head .newsextra [class*=" SYWicon-"] {
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