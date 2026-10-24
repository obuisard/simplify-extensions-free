<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
//header("Content-type: text/css; charset=UTF-8");
?>

.articledetails {
	overflow: hidden;
}

	.articledetails .info .article_title {
		line-height: 1em;
	}

	.articledetails .info .details {
		font-size: <?php echo ($font_details / 100); ?>em;
	}

		.articledetails .info .details .detail_rating .detail_data [class*=" SYWicon-"],
		.articledetails .info .details .detail_rating .detail_data [class^="SYWicon-"] {
			font-size: 1.2em;
			color: #000;
		}

		.articledetails .info .details .detail_social .detail_data [class*=" SYWicon-"],
		.articledetails .info .details .detail_social .detail_data [class^="SYWicon-"] {
			font-size: 1.2em;
			color: #000;
		}

		.articledetails .info .details .detail_social a {
			background-color: transparent;
		}