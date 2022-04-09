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

<?php if ($cal_shadow_width > 0) : ?>
	.articledetails .info {
		margin-top: <?php echo $cal_shadow_width; ?>px;
	}
<?php endif; ?>

.articledetails .head {
	height: auto;
}

	.articledetails .head .calendar {
		text-align: center;
		font-size: <?php echo $font_ref_cal; ?>px;
	}

	.articledetails .head .calendar.noimage {

		background: <?php echo $bgcolor1; ?>; /* Old browsers */

		<?php if ($bgcolor1 != $bgcolor2) : ?>
			background: -moz-linear-gradient(top, <?php echo $bgcolor1; ?> 0%, <?php echo $bgcolor2; ?> 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,<?php echo $bgcolor1; ?>), color-stop(100%,<?php echo $bgcolor2; ?>)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top, <?php echo $bgcolor1; ?> 0%,<?php echo $bgcolor2; ?> 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top, <?php echo $bgcolor1; ?> 0%,<?php echo $bgcolor2; ?> 100%); /* Opera11.10+ */
			background: -ms-linear-gradient(top, <?php echo $bgcolor1; ?> 0%,<?php echo $bgcolor2; ?> 100%); /* IE10+ */

			<?php if ($bgcolor1 == 'transparent') : ?>
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor2; ?>', endColorstr='<?php echo $bgcolor2; ?>',GradientType=0 ); /* IE6-9 (IE9 cannot use SVG because the colors are dynamic) */
			<?php elseif ($bgcolor2 == 'transparent') : ?>
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor1; ?>', endColorstr='<?php echo $bgcolor1; ?>',GradientType=0 ); /* IE6-9 (IE9 cannot use SVG because the colors are dynamic) */
			<?php else : ?>
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor1; ?>', endColorstr='<?php echo $bgcolor2; ?>',GradientType=0 ); /* IE6-9 (IE9 cannot use SVG because the colors are dynamic) */
			<?php endif; ?>

			background: linear-gradient(top, <?php echo $bgcolor1; ?> 0%,<?php echo $bgcolor2; ?> 100%); /* W3C */
		<?php endif; ?>

		color: <?php echo $color; ?>;

		<?php if ($cal_border_width > 0) : ?>
			border: <?php echo $cal_border_width; ?>px solid <?php echo $cal_border_color; ?>;
		<?php endif; ?>

		<?php if ($cal_border_radius > 0) : ?>
			border-radius: <?php echo $cal_border_radius; ?>px;
			-moz-border-radius: <?php echo $cal_border_radius; ?>px;
			-webkit-border-radius: <?php echo $cal_border_radius; ?>px;
			/* IE 7 AND 8 DO NOT SUPPORT BORDER RADIUS */

			-moz-background-clip: padding-box;
			-webkit-background-clip: padding-box;
			background-clip: padding-box;
			/* Use "background-clip: padding-box" when using rounded corners to avoid the gradient bleeding through the corners */
		<?php endif; ?>

		<?php if ($cal_shadow_width > 0) : ?>
			box-shadow: 0 0 <?php echo $cal_shadow_width; ?>px rgba(0, 0, 0, 0.8);
			-moz-box-shadow: 0 0 <?php echo $cal_shadow_width; ?>px rgba(0, 0, 0, 0.8);
			-webkit-box-shadow: 0 0 <?php echo $cal_shadow_width; ?>px rgba(0, 0, 0, 0.8);
			/* IE 7 AND 8 DO NOT SUPPORT BLUR PROPERTY OF SHADOWS */

			margin: <?php echo $cal_shadow_width; ?>px;
		<?php endif; ?>
	}

		.articledetails .head .calendar .position1,
		.articledetails .head .calendar .position2,
		.articledetails .head .calendar .position3,
		.articledetails .head .calendar .position4,
		.articledetails .head .calendar .position5 {
			display: block;
		}

		.articledetails .head .calendar .empty {
		    line-height: 0.6em;
		}

		.articledetails .head .calendar.noimage .position1 {

			background: <?php echo $bgcolor1_top; ?>; /* Old browsers */

			<?php if ($bgcolor1_top != $bgcolor2_top) : ?>
				background: -moz-linear-gradient(top, <?php echo $bgcolor1_top; ?> 0%, <?php echo $bgcolor2_top; ?> 100%); /* FF3.6+ */
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,<?php echo $bgcolor1_top; ?>), color-stop(100%,<?php echo $bgcolor2_top; ?>)); /* Chrome,Safari4+ */
				background: -webkit-linear-gradient(top, <?php echo $bgcolor1_top; ?> 0%,<?php echo $bgcolor2_top; ?> 100%); /* Chrome10+,Safari5.1+ */
				background: -o-linear-gradient(top, <?php echo $bgcolor1_top; ?> 0%,<?php echo $bgcolor2_top; ?> 100%); /* Opera11.10+ */
				background: -ms-linear-gradient(top, <?php echo $bgcolor1_top; ?> 0%,<?php echo $bgcolor2_top; ?> 100%); /* IE10+ */

				<?php if ($bgcolor1_top == 'transparent') : ?>
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor2_top; ?>', endColorstr='<?php echo $bgcolor2_top; ?>',GradientType=0 ); /* IE6-9 */
				<?php elseif ($bgcolor2_top == 'transparent') : ?>
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor1_top; ?>', endColorstr='<?php echo $bgcolor1_top; ?>',GradientType=0 ); /* IE6-9 */
				<?php else : ?>
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor1_top; ?>', endColorstr='<?php echo $bgcolor2_top; ?>',GradientType=0 ); /* IE6-9 */
				<?php endif; ?>

				background: linear-gradient(top, <?php echo $bgcolor1_top; ?> 0%,<?php echo $bgcolor2_top; ?> 100%); /* W3C */
			<?php endif; ?>

			color: <?php echo $color_top; ?>;

			<?php if ($cal_border_width == 0 && $cal_border_radius > 0) : ?>
				border-top-right-radius: <?php echo $cal_border_radius; ?>px;
				border-top-left-radius: <?php echo $cal_border_radius; ?>px;
				-moz-border-top-right-radius: <?php echo $cal_border_radius; ?>px;
				-moz-border-top-left-radius: <?php echo $cal_border_radius; ?>px;
				-webkit-border-top-right-radius: <?php echo $cal_border_radius; ?>px;
				-webkit-border-top-left-radius: <?php echo $cal_border_radius; ?>px;

				-moz-background-clip: padding-box;
				-webkit-background-clip: padding-box;
				background-clip: padding-box;
				/* Use "background-clip: padding-box" when using rounded corners to avoid the gradient bleeding through the corners */
			<?php endif; ?>

			margin-bottom: 3px;
			height: 1.5em;
			text-transform: uppercase !important;
			font-size: 1em !important;
			line-height: 1.5em !important;
		}

		.articledetails .head .calendar.noimage .position5 {

			background: <?php echo $bgcolor1_bottom; ?>; /* Old browsers */

			<?php if ($bgcolor1_bottom != $bgcolor2_bottom) : ?>
				background: -moz-linear-gradient(top, <?php echo $bgcolor1_bottom; ?> 0%, <?php echo $bgcolor2_bottom; ?> 100%); /* FF3.6+ */
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,<?php echo $bgcolor1_bottom; ?>), color-stop(100%,<?php echo $bgcolor2_bottom; ?>)); /* Chrome,Safari4+ */
				background: -webkit-linear-gradient(top, <?php echo $bgcolor1_bottom; ?> 0%,<?php echo $bgcolor2_bottom; ?> 100%); /* Chrome10+,Safari5.1+ */
				background: -o-linear-gradient(top, <?php echo $bgcolor1_bottom; ?> 0%,<?php echo $bgcolor2_bottom; ?> 100%); /* Opera11.10+ */
				background: -ms-linear-gradient(top, <?php echo $bgcolor1_bottom; ?> 0%,<?php echo $bgcolor2_bottom; ?> 100%); /* IE10+ */

				<?php if ($bgcolor1_bottom == 'transparent') : ?>
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor2_bottom; ?>', endColorstr='<?php echo $bgcolor2_bottom; ?>',GradientType=0 ); /* IE6-9 */
				<?php elseif ($bgcolor2_bottom == 'transparent') : ?>
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor1_bottom; ?>', endColorstr='<?php echo $bgcolor1_bottom; ?>',GradientType=0 ); /* IE6-9 */
				<?php else : ?>
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor1_bottom; ?>', endColorstr='<?php echo $bgcolor2_bottom; ?>',GradientType=0 ); /* IE6-9 */
				<?php endif; ?>

				background: linear-gradient(top, <?php echo $bgcolor1_bottom; ?> 0%,<?php echo $bgcolor2_bottom; ?> 100%); /* W3C */
			<?php endif; ?>

			color: <?php echo $color_bottom; ?>;

			<?php if ($cal_border_width == 0 && $cal_border_radius > 0) : ?>
				border-bottom-right-radius: <?php echo $cal_border_radius; ?>px;
				border-bottom-left-radius: <?php echo $cal_border_radius; ?>px;
				-moz-border-bottom-right-radius: <?php echo $cal_border_radius; ?>px;
				-moz-border-bottom-left-radius: <?php echo $cal_border_radius; ?>px;
				-webkit-border-bottom-right-radius: <?php echo $cal_border_radius; ?>px;
				-webkit-border-bottom-left-radius: <?php echo $cal_border_radius; ?>px;

				-moz-background-clip: padding-box;
				-webkit-background-clip: padding-box;
				background-clip: padding-box;
				/* Use "background-clip: padding-box" when using rounded corners to avoid the gradient bleeding through the corners */
			<?php endif; ?>

			margin-top: 3px;

			height: 1.7em;
			line-height: 1.7em !important;
			font-size: 0.8em !important;
		}

		.articledetails .head .calendar .weekday {
			font-size: 0.8em;
			line-height: 1em;
			text-transform: uppercase;
			letter-spacing: 0.4em;
			text-indent: 0.4em;
		}

		html[dir="rtl"] .articledetails .head .calendar .weekday {
			text-indent: -0.4em;
		}

		.articledetails .head .calendar .month {
			font-size: 0.8em;
			line-height: 1em;
			font-weight: bold;
			text-transform: uppercase;
			letter-spacing: 0.45em;
			text-indent: 0.45em;
		}

		html[dir="rtl"] .articledetails .head .calendar .month {
			text-indent: -0.45em;
		}

		.articledetails .head .calendar .day {
			font-size: 1.8em;
			line-height: 1.1em;
			font-weight: bold;
			letter-spacing: 0.1em;
			text-indent: 0.1em;
		}

		html[dir="rtl"] .articledetails .head .calendar .day {
			text-indent: -0.1em;
		}

		.articledetails .head .calendar .year {
			font-size: 0.7em;
			line-height: 1.2em;
			letter-spacing: 0.35em;
			text-indent: 0.35em;
		}

		html[dir="rtl"] .articledetails .head .calendar .year {
			text-indent: -0.35em;
		}

		.articledetails .head .calendar .time {
			font-size: 0.8em;
			line-height: 1.2em;
			letter-spacing: 0.1em;
			text-indent: 0.1em;
		}

		html[dir="rtl"] .articledetails .head .calendar .time {
			text-indent: -0.1em;
		}
