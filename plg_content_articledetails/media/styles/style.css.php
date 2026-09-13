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
	display: -webkit-box;
	display: -moz-box;
	display: -ms-flexbox;
	display: -webkit-flex;
	display: flex;

	-webkit-flex-direction: row;
	-ms-flex-direction: row;
	flex-direction: row;

	-webkit-flex-wrap: wrap;
	-ms-flex-wrap: wrap;
	flex-wrap: wrap;

	min-width: 100px; /* arbitrary - works better when working with surrounding image floats */
}

.articledetails-header {
	margin-bottom: 5px;

	<?php if ($align_details == 'right') : ?>
		-webkit-box-orient: horizontal;
		-webkit-box-direction: reverse;
		-webkit-flex-direction: row-reverse;
		-ms-flex-direction: row-reverse;
		flex-direction: row-reverse;
	<?php endif; ?>
}

.articledetails-footer {
	margin-top: 15px;
	padding-top: 15px;
	border-top: 1px solid #eee;
	clear: both;

	<?php if ($footer_align_details == 'right') : ?>
		-webkit-box-orient: horizontal;
		-webkit-box-direction: reverse;
		-webkit-flex-direction: row-reverse;
		-ms-flex-direction: row-reverse;
		flex-direction: row-reverse;
	<?php endif; ?>
}

	.articledetails .publishing_status {
		margin-bottom: 5px;
	}

	.articledetails .head {
		/* set height in the element stylesheet */
		width: <?php echo $head_width; ?>px;
		max-width: <?php echo $head_width; ?>px;

		-webkit-box-flex: none;
  		-moz-box-flex: none;
		-webkit-flex: none;
		-ms-flex: none;
		flex: none;
	}

	.articledetails-header .head {
		<?php if ($align_details == 'left') : ?>
		   	margin: 0 8px 0 0;
		<?php endif; ?>
		<?php if ($align_details == 'right') : ?>
		   	margin: 0 0 0 8px;
		<?php endif; ?>
	}

	html[dir="rtl"] .articledetails-header .head {
		<?php if ($align_details == 'left') : ?>
		   	margin: 0 0 0 8px;
		<?php endif; ?>
		<?php if ($align_details == 'right') : ?>
		   	margin: 0 8px 0 0;
		<?php endif; ?>
	}

	/* 0px for flexbox to work in IE11 */
	.articledetails .info {
		-webkit-box-flex: 1 1 0px;
		-moz-box-flex: 1 1 0px;
		-webkit-flex: 1 1 0px;
		-ms-flex: 1 1 0px;
		flex: 1 1 0px;
	}

		.articledetails .info .article_title {
		    display: block;
			margin: 0 0 3px 0;
		    padding: 0;
		    line-height: initial;
		    text-align: <?php echo $align_details; ?>;
		}

		html[dir="rtl"] .articledetails .info .article_title {
			<?php if ($align_details == 'left') : ?>
			   	text-align: right;
			<?php endif; ?>
			<?php if ($align_details == 'right') : ?>
			   	text-align: left;
			<?php endif; ?>
		}

		.articledetails .info .article_edit,
		.articledetails .info .article_checked_out {
			margin-left: 10px;
			font-size: 0.8em;
			display: inline-block;
		}

		html[dir="rtl"] .articledetails .info .article_edit,
		html[dir="rtl"] .articledetails .info .article_checked_out {
			margin-left: 0;
			margin-right: 10px;
		}

		.articledetails .info p {
			margin: 0;
		    padding: 0;
		}

		.articledetails .info p + p {
			text-indent: 0;
		}

		.articledetails .info .form-inline {
			margin: 0;
			padding: 0;
			display: block;
		}

		.articledetails .info .form-inline select {
			width: auto;
			display: inline-block;
		}

		.articledetails-header .info .form-inline,
		.articledetails-header .info .details {
			text-align: <?php echo $align_details; ?>;
		}

		html[dir="rtl"] .articledetails-header .info .form-inline,
		html[dir="rtl"] .articledetails-header .info .details {
			<?php if ($align_details == 'left') : ?>
			   	text-align: right;
			<?php endif; ?>
			<?php if ($align_details == 'right') : ?>
			   	text-align: left;
			<?php endif; ?>
		}

		.articledetails-footer .info .form-inline,
		.articledetails-footer .info .details {
			text-align: <?php echo $footer_align_details; ?>;
		}

		html[dir="rtl"] .articledetails-footer .info .form-inline,
		html[dir="rtl"] .articledetails-footer .info .details {
			<?php if ($footer_align_details == 'left') : ?>
			   	text-align: right;
			<?php endif; ?>
			<?php if ($footer_align_details == 'right') : ?>
			   	text-align: left;
			<?php endif; ?>
		}

		.articledetails .info dl.item_details,
		.articledetails .info dd.details {
			margin: 0;
			padding: 0;
		}

		.articledetails .info dl.item_details > dt {
			position: absolute;
			top: -9999px;
			left: -9999px;
		}

		.articledetails .info .item_details {
			font-size: <?php echo ($font_details / 100); ?>em;
			margin-bottom: 3px;
		}

			.articledetails .info .item_details .delimiter {
				white-space: pre-wrap;
			}

			.articledetails .info .details {
				<?php if ($details_line_spacing[0]) : ?>
					line-height: <?php echo $details_line_spacing[0]; ?><?php echo $details_line_spacing[1]; ?>;
				<?php endif; ?>
				color: <?php echo $details_font_color; ?>;
			}

			.articledetails .info .details [class^="SYWicon-"],
			.articledetails .info .details [class*=" SYWicon-"] {
				font-size: 1em;
			    color: <?php echo $iconfont_color; ?>;
			    padding-right: 3px;
			}

			html[dir="rtl"] .articledetails .info .details [class^="SYWicon-"],
			html[dir="rtl"] .articledetails .info .details [class*=" SYWicon-"] {
				padding-left: 3px;
			    padding-right: 0;
			}

			.articledetails .info .details a [class^="SYWicon-"],
			.articledetails .info .details a [class*=" SYWicon-"] {
				color: inherit;
			}

			.articledetails .info .details .detail {
				vertical-align: middle;
			}

			.articledetails .info .details .detail_email .detail_data i,
			.articledetails .info .details .detail_print .detail_data i {
				vertical-align: middle;
				font-size: 1.2em;
			}

			.articledetails .info .details .detail_rating .detail_data i {
				vertical-align: middle;
				font-size: 1.2em;
				color: <?php echo $star_color; ?>;
			}

			<?php if ($share_bgcolor) : ?>
				.articledetails .info .details .detail_social {
					line-height: 30px;
				}
			<?php endif; ?>

			.articledetails .info .details .detail_social a {
				text-align: center;
				margin: 0 3px;
				font-family: initial;
				line-height: 1em;
			}

			.articledetails .info .details .detail_social a:hover {
				text-decoration: none;
			}

			.articledetails .info .details .detail_social .detail_data a > i {
				vertical-align: middle;
				font-size: 1.2em;
    			display: inline-block;
			}
			
			.articledetails .info .details .detail_social .detail_data a svg {
				vertical-align: middle;
				width: 1.2em;
				height: 1.2em;
    			display: inline-block;
			}

			<?php if ($share_bgcolor) : ?>
    			.articledetails .info .details .detail_social .detail_data a > * {
    				display: inline-block;
    				color: #fff;
    				padding: 6px;
    				<?php if ($share_radius > 0) : ?>
    					-webkit-border-radius: <?php echo $share_radius; ?>px;
    					-moz-border-radius: <?php echo $share_radius; ?>px;
    					border-radius: <?php echo $share_radius; ?>px;
    				<?php endif; ?>
    			}
			<?php endif; ?>
