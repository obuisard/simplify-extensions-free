<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
//header("Content-type: text/css; charset=UTF-8");

/**
* TODO
* change behavior of arrows when window size > 860 (from flexslider.css)
*/
?>

#trs_<?php echo $suffix; ?>_loader {
	text-align: center;
}

/* flexslider overrides */

#trs_<?php echo $suffix; ?> {

	<?php if (!empty($max_width)) : ?>
		max-width: <?php echo $max_width; ?>px;
	<?php else : ?>
		max-width: 100%;
	<?php endif; ?>

	margin: 0 auto;
	position: relative;
}

#trs_<?php echo $suffix; ?> .flexslider {
	background: #fff;

	<?php if ($border_width > 0) : ?>
		border: <?php echo $border_width; ?>px solid #fff;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		-o-border-radius: 4px;
		border-radius: 4px;
		box-shadow: 0 1px 4px rgba(0,0,0,.2);
		-webkit-box-shadow: 0 1px 4px rgba(0,0,0,.2);
		-moz-box-shadow: 0 1px 4px rgba(0,0,0,.2);
		-o-box-shadow: 0 1px 4px rgba(0,0,0,.2);
	<?php else : ?>
		border: none !important;
		-webkit-border-radius: 0 !important;
		-moz-border-radius: 0 !important;
		-o-border-radius: 0 !important;
		border-radius: 0 !important;
		box-shadow: none !important;
		-webkit-box-shadow: none !important;
		-moz-box-shadow: none !important;
		-o-box-shadow: none !important;
	<?php endif; ?>

	margin-bottom: 10px;
}

@media only screen and (max-width: 479px) {
	#trs_<?php echo $suffix; ?> .flexslider { /* to avoid borders on small sliders */
		border: none !important;
		-webkit-border-radius: 0 !important;
		-moz-border-radius: 0 !important;
		-o-border-radius: 0 !important;
		border-radius: 0 !important;
		box-shadow: none !important;
		-webkit-box-shadow: none !important;
		-moz-box-shadow: none !important;
		-o-box-shadow: none !important;
	}
}

#trs_<?php echo $suffix; ?> .flexslider a,
#trs_<?php echo $suffix; ?> .flexslider a:active,
#trs_<?php echo $suffix; ?> .flexslider a:focus,
#trs_<?php echo $suffix; ?> .flexslider a:hover {
	text-decoration: none;
}

#trs_<?php echo $suffix; ?> .flex-direction-nav li { /* To avoid possible template overrides */
	list-style: none;
	padding: 0 !important;
	background-image: none !important;
}

#trs_<?php echo $suffix; ?> .flex-direction-nav a::before {
    font-family: "<?php echo $font_family; ?>";
    font-weight: 900;
    font-size: 32px;
    line-height: 32px;
    color: <?php echo $arrows_c; ?>;

	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}

#trs_<?php echo $suffix; ?> .flex-direction-nav a.flex-next::before {
    content: "\f105";
}

#trs_<?php echo $suffix; ?> .flex-direction-nav a.flex-prev::before {
    content: "\f104";
}

#trs_<?php echo $suffix; ?> .flex-direction-nav a {
	display: block;
	height: 32px;
	width: 32px;
	vertical-align: middle;
	text-align: center;
	<?php if (!empty($arrows_bgc)) : ?>
		background-color: <?php echo $arrows_bgc; ?>;
	<?php endif; ?>

	<?php if ($arrows_shadow == 0 && empty($arrows_bgc)) : ?>
		-webkit-border-radius: 0;
		-moz-border-radius: 0;
		-o-border-radius: 0;
		border-radius: 0;
	<?php else : ?>
		-webkit-border-radius: <?php echo $arrows_bgr; ?>px;
		-moz-border-radius: <?php echo $arrows_bgr; ?>px;
		-o-border-radius: <?php echo $arrows_bgr; ?>px;
		border-radius: <?php echo $arrows_bgr; ?>px;
	<?php endif; ?>

	-webkit-box-shadow: 0 0 <?php echo $arrows_shadow; ?>px #000;
	-moz-box-shadow: 0 0 <?php echo $arrows_shadow; ?>px #000;
	-o-box-shadow: 0 0 <?php echo $arrows_shadow; ?>px #000;
	box-shadow: 0 0 <?php echo $arrows_shadow; ?>px #000;
}

#trs_<?php echo $suffix; ?> .flex-control-thumbs li {
	<?php if (!empty($thumb_width_percent)) : ?>
		width: <?php echo $thumb_width_percent; ?>% !important;
	<?php endif; ?>

	list-style: none; /* To avoid possible template overrides */
	padding: 0 !important; /* To avoid possible template overrides */
	background-image: none !important; /* To avoid possible template overrides */
}

#trs_<?php echo $suffix; ?> .flexslider .slides li {
	position: relative;

	list-style: none; /* To avoid possible template overrides */
	padding: 0 !important; /* To avoid possible template overrides */
	background-image: none !important; /* To avoid possible template overrides */
}

#trs_<?php echo $suffix; ?> .flexslider p {
	margin: 0;
}

#trs_<?php echo $suffix; ?> .flexslider p + p {
	margin: 0;
	text-indent: 0;
}

/* sliders */

#trs_<?php echo $suffix; ?> .flexslidercontainer {
	position: relative;
}

/* basic */

#trs_<?php echo $suffix; ?> .basic .flex-control-nav {
	<?php if ($dot_navigation == 'under') : ?>
		display: inline-block;
		bottom: 0 !important;
		margin: 15px 0 5px 0;
		position: relative !important;
	<?php elseif ($dot_navigation == 'none') : ?>
		display: none;
	<?php else : ?>
		bottom: 5px !important;
		z-index: 100;
	<?php endif; ?>
}

/* auto_thumbs */

#trs_<?php echo $suffix; ?> .auto_thumbs .flex-control-nav {
	bottom: 0 !important;
}

#trs_<?php echo $suffix; ?> .auto_thumbs .flex-control-thumbs li {
	width: 20%;
}

#trs_<?php echo $suffix; ?> .auto_thumbs .flex-control-thumbs .flex-active {
	opacity: 1;
	-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=100)"; /* IE8 */
}

#trs_<?php echo $suffix; ?> .auto_thumbs .flex-control-thumbs img {
	opacity: .5;
	-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";
}

#trs_<?php echo $suffix; ?> .auto_thumbs .flex-control-thumbs img:hover {
	opacity: 1;
	-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
}

/* caption */

#trs_<?php echo $suffix; ?> #out_captions_<?php echo $suffix; ?> .out_caption {
	display: none;
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption {
	display: none;
	position: absolute;
	padding: 0;
	margin: 0;

	bottom: <?php echo $caption_bottom; ?>%;
	left: <?php echo $caption_left; ?>%;
	width: <?php echo (100 - $caption_left - $caption_right); ?>%;
	height: <?php echo (100 - $caption_top - $caption_bottom); ?>%;

	color: #fff;
	font-size: 0.85em;
	line-height: 1.4em;
}

@media only screen and (min-width: 640px) {

	#trs_<?php echo $suffix; ?> #out_captions_<?php echo $suffix; ?> {
		display: none;
	}

	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption {
		display: block;
		font-size: 1em;
		line-height: inherit;
	}
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .innercaption {
	position: absolute;
	padding: <?php echo $padding; ?>%;
	text-align: left;
	max-height: 100%;
	overflow-y: auto;
	overflow-x: hidden;
	box-sizing: unset;

	background-color: <?php echo $opacity_color; ?>; /* fallback color */
	background-color: rgba(<?php echo $o_c_r; ?>, <?php echo $o_c_g; ?>, <?php echo $o_c_b; ?>, <?php echo (1 - $opacity / 100); ?>);
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .innercaption.simple_caption {
	text-align: center;
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-cc,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-cce,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ecc,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ccw,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-wcc {
	width: <?php echo (100 - $padding * 2); ?>%;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-c,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-wc.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-cw.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ec.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ce.two-column {
	width: <?php echo (70 - $padding * 2); ?>%;
	left: 15%;
	top: 15%;
	bottom: 15%;
}

@media only screen and (max-width: 479px) {
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-c,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-wc.two-column,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-cw.two-column,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ec.two-column,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ce.two-column {
		width: <?php echo (100 - $padding * 2); ?>%;
		left: 0;
		top: 0;
		bottom: 0;
	}
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-wn.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-nw.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-en.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ne.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ws.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-sw.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-es.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-se.two-column,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-n,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-s {
	width: <?php echo (100 - $padding * 2); ?>%;
	left: 0;
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-sw,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ws,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-nw,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-wn {
	width: <?php echo (50 - $padding * 2); ?>%;
	left: 0;
}

@media only screen and (max-width: 479px) {
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-sw,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ws,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-nw,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-wn {
		width: <?php echo (100 - $padding * 2); ?>%;
		left: 0;
	}
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-se,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-es,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-en,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ne {
	width: <?php echo (50 - $padding * 2); ?>%;
	right: 0;
}

@media only screen and (max-width: 479px) {
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-se,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-es,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-en,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ne {
		width: <?php echo (100 - $padding * 2); ?>%;
		right: 0;
	}
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-nw,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-wn,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ne,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-en,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-n {
	top: 0;
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-sw,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-ws,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-se,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-es,
#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .coordinate-s {
	bottom: 0;
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .caption_left {
	width: 48%;
	margin-right: 4%;
	display: inline-block;
	min-height: 1px;
	vertical-align: top;
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .caption_right {
	width: 48%;
	display: inline-block;
	min-height: 1px;
	vertical-align: top;
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption img {
	max-width: 100%;
	width: auto;
	display: inline;
}

#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .caption_links {
	margin-top: 10px;
}

@media only screen and (max-width: 479px) {

	#trs_<?php echo $suffix; ?> .flexslidercontainer .complex_caption { /* to prevent empty caption background */
		padding: 0 !important;
	}

    #trs_<?php echo $suffix; ?> .flexslidercontainer .caption .caption_left {
		width: 100%;
		margin-right: 0;
	}

	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .caption_right {
		width: 100%;
	}
}

@media only screen and (max-width: 639px) {

	#trs_<?php echo $suffix; ?>.mobile .flexslidercontainer .complex_caption {
		/* to prevent empty caption background */
		padding: 0 !important;
	}

    #trs_<?php echo $suffix; ?>.mobile .flexslidercontainer .caption .caption_left {
		width: 100%;
		margin-right: 0;
	}

	#trs_<?php echo $suffix; ?>.mobile .flexslidercontainer .caption .caption_right {
		width: 100%;
	}

	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .caption_category,
	#trs_<?php echo $suffix; ?> .flexslidercontainer .caption .caption_title {
		font-size: 0.85rem;
	}
}
