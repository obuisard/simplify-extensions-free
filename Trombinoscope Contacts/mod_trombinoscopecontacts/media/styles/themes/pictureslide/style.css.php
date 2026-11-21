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

/* Contains font styles, borders, colors and images */

<?php echo $prefix; ?> .outerperson {
	<?php if ($font_color && $font_color != 'transparent') : ?>
		color: <?php echo $font_color; ?>;
	<?php endif; ?>

	<?php if ($bgcolor1) : ?>
		background: <?php echo $bgcolor1; ?>;
	<?php elseif ($bgcolor2) : ?>
		background: <?php echo $bgcolor2; ?>;
	<?php endif; ?>

	<?php if ($bgcolor1 && $bgcolor2 && $bgcolor1 != $bgcolor2) : ?>
		background: -moz-linear-gradient(top, <?php echo $bgcolor1; ?> 0%, <?php echo $bgcolor2; ?> 100%); /* FF3.6+ */
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,<?php echo $bgcolor1; ?>), color-stop(100%,<?php echo $bgcolor2; ?>)); /* Chrome,Safari4+ */
		background: -webkit-linear-gradient(top, <?php echo $bgcolor1; ?> 0%,<?php echo $bgcolor2; ?> 100%); /* Chrome10+,Safari5.1+ */
		background: -o-linear-gradient(top, <?php echo $bgcolor1; ?> 0%,<?php echo $bgcolor2; ?> 100%); /* Opera11.10+ */
		background: -ms-linear-gradient(top, <?php echo $bgcolor1; ?> 0%,<?php echo $bgcolor2; ?> 100%); /* IE10+ */
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $bgcolor1; ?>', endColorstr='<?php echo $bgcolor2; ?>',GradientType=0 ); /* IE6-9 (IE9 cannot use SVG because the colors are dynamic) */
		background: linear-gradient(top, <?php echo $bgcolor1; ?> 0%,<?php echo $bgcolor2; ?> 100%); /* W3C */
	<?php endif; ?>

	<?php if ($bgimage != '') : ?>
		background-image: url(../../<?php echo $bgimage; ?>);
		background-position: top left;
		background-repeat: repeat;
	<?php endif; ?>

	<?php if ($shadow_body == 's') : ?>
		-webkit-box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14),0 3px 1px -2px rgba(0,0,0,0.12),0 1px 5px 0 rgba(0,0,0,0.2);
		box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14),0 3px 1px -2px rgba(0,0,0,0.12),0 1px 5px 0 rgba(0,0,0,0.2);
		margin: 6px;
	<?php endif; ?>
	<?php if ($shadow_body == 'm') : ?>
		-webkit-box-shadow: 0 4px 5px 0 rgba(0,0,0,0.14),0 1px 10px 0 rgba(0,0,0,0.12),0 2px 4px -1px rgba(0,0,0,0.3);
		box-shadow: 0 4px 5px 0 rgba(0,0,0,0.14),0 1px 10px 0 rgba(0,0,0,0.12),0 2px 4px -1px rgba(0,0,0,0.3);
		margin: 11px;
	<?php endif; ?>
	<?php if ($shadow_body == 'l') : ?>
		-webkit-box-shadow: 0 8px 17px 2px rgba(0,0,0,0.14),0 3px 14px 2px rgba(0,0,0,0.12),0 5px 5px -3px rgba(0,0,0,0.2);
		box-shadow: 0 8px 17px 2px rgba(0,0,0,0.14),0 3px 14px 2px rgba(0,0,0,0.12),0 5px 5px -3px rgba(0,0,0,0.2);
		margin: 27px;
	<?php endif; ?>
	<?php if ($shadow_body == 'ss') : ?>
		-webkit-box-shadow: 1px 1px 4px rgba(51, 51, 51, 0.2);
		box-shadow: 1px 1px 4px rgba(51, 51, 51, 0.2);
		margin: 5px;
	<?php endif; ?>
	<?php if ($shadow_body == 'sm') : ?>
		-webkit-box-shadow: 1px 1px 4px rgba(51, 51, 51, 0.2);
		box-shadow: 1px 1px 10px rgba(51, 51, 51, 0.2);
		margin: 11px;
	<?php endif; ?>
	<?php if ($shadow_body == 'sl') : ?>
		-webkit-box-shadow: 1px 1px 4px rgba(51, 51, 51, 0.2);
		box-shadow: 1px 1px 15px rgba(51, 51, 51, 0.2);
		margin: 16px;
	<?php endif; ?>

	<?php if ($card_radius > 0) : ?>
		-moz-border-radius: <?php echo $card_radius; ?>px;
		-webkit-border-radius: <?php echo $card_radius; ?>px;
		border-radius: <?php echo $card_radius; ?>px;
	<?php endif; ?>
}

<?php echo $prefix; ?> .odd {
	border-bottom: none; /* to override k2 style */
	padding: 0; /* to override k2 style */
	<?php if ($bgcolor1 == '' && $bgcolor2 == '') : ?>
		background-color: transparent; /* to override k2 style */
	<?php endif; ?>
}

<?php echo $prefix; ?> .even {
	border-bottom: none; /* to override k2 style */
	padding: 0; /* to override k2 style */
	<?php if ($bgcolor1 == '' && $bgcolor2 == '') : ?>
		background-color: transparent; /* to override k2 style */
	<?php endif; ?>
}

<?php echo $prefix; ?> .iconlinks,
<?php echo $prefix; ?> .vcard {
	position: absolute;
}

<?php echo $prefix; ?> .iconlinks {
	bottom: <?php echo ($border_width + 5); ?>px;
	<?php if ($show_vcard) : ?>
		left: <?php echo ($border_width + 5); ?>px;
	<?php else : ?>
		width: 100%;
	<?php endif; ?>
}

<?php if (!$show_vcard) : ?>
	<?php echo $prefix; ?> .iconlinks ul {
		margin: 0 auto;
	}
<?php endif; ?>

<?php echo $prefix; ?> .iconlinks ul li {
	display: inline-block;
}

<?php echo $prefix; ?> .vcard {
	bottom: <?php echo ($border_width + 5); ?>px;
	right: <?php echo ($border_width + 5); ?>px;
}

<?php if ($border_width > 0) : ?>
	<?php echo $prefix; ?> .innerperson {
		border: <?php echo $border_width; ?>px solid <?php echo $pic_bgcolor; ?>;
	}
<?php endif; ?>

<?php echo $prefix; ?> .personpicture {
	float: none !important;
	z-index: 75 !important;
}

<?php echo $prefix; ?> .picture {
	width: 100% !important;
	margin-left: -<?php echo intval($picture_width / 2); ?>px;
	left: 50%;
}

html[dir="rtl"] <?php echo $prefix; ?> .picture {
	margin-right: -<?php echo intval($picture_width / 2); ?>px;
	right: 50%;
}

<?php echo $prefix; ?> .personinfo {
	position: absolute;
	overflow: auto;
	top: 0;
	left: 0;
	right: 0;
	<?php if ($show_links || $show_vcard) : ?>
		height: <?php echo ($picture_height - 25); ?>px;
	<?php else : ?>
		height: <?php echo $picture_height; ?>px;
	<?php endif; ?>
	padding: 5px;

	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}

<?php echo $prefix; ?> .personfield.index1 .fieldvalue {
	font-style: italic;
}

<?php echo $prefix; ?> .personfield,
<?php echo $prefix; ?> .personlinks {
	font-size: 0.9em;
}

<?php echo $prefix; ?> .personfield.fieldname {
	font-size: 1.5em;
	font-weight: bold;
	line-height: 1.2em;
}

<?php echo $prefix; ?> .featured .picture_veil .feature {
	display: block;
	position: absolute;
	top: 5px;
	left: 15px;
}

<?php echo $prefix; ?> .personinfo .category,
<?php echo $prefix; ?> .personinfo .tags {
	font-size: .8em;
	text-align: left !important;
}

/* animations */

<?php echo $prefix; ?> .personpicture {
   -webkit-transition: all 0.3s ease-in-out;
   -moz-transition: all 0.3s ease-in-out;
   -o-transition: all 0.3s ease-in-out;
   -ms-transition: all 0.3s ease-in-out;
   transition: all 0.3s ease-in-out;
}

<?php echo $prefix; ?> .outerperson:hover .personpicture {
   -webkit-transform: translateX(<?php echo $picture_width; ?>px);
   -moz-transform: translateX(<?php echo $picture_width; ?>px);
   -o-transform: translateX(<?php echo $picture_width; ?>px);
   -ms-transform: translateX(<?php echo $picture_width; ?>px);
   transform: translateX(<?php echo $picture_width; ?>px);
}

<?php echo $prefix; ?> .personinfo {
   -webkit-transform: translateX(-<?php echo $picture_width; ?>px);
   -moz-transform: translateX(-<?php echo $picture_width; ?>px);
   -o-transform: translateX(-<?php echo $picture_width; ?>px);
   -ms-transform: translateX(-<?php echo $picture_width; ?>px);
   transform: translateX(-<?php echo $picture_width; ?>px);
   -webkit-transition: all 0.3s ease-in-out;
   -moz-transition: all 0.3s ease-in-out;
   -o-transition: all 0.3s ease-in-out;
   -ms-transition: all 0.3s ease-in-out;
   transition: all 0.3s ease-in-out;
}

<?php echo $prefix; ?> .outerperson:hover .personinfo {
   -webkit-transform: translateX(0px);
   -moz-transform: translateX(0px);
   -o-transform: translateX(0px);
   -ms-transform: translateX(0px);
   transform: translateX(0px);
}
