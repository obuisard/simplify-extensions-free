<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
header("Content-type: text/css; charset=UTF-8");

// conditions:
// bg image and contact picture must be square
// size bigger than min card width
// smallest card size can't be smaller than 1.5 times the contact picture
// compatible with browsers supporting css flex layout http://caniuse.com/#feat=flexbox
// rtl ready
// picture-only card - for no picture but have a bg, just set pictures to yes but no pictures in contact and no default
// explain how bg size is calculated
// limitation: on text only, the background picture is only on the left: use flex-direction:row-reverse on .innerperson on .odd or .even + move .vcard and .iconlinks + there is no landcsape/portrait switch
// limitation: bg images are not optimized/cropped by the component - make sure originals are

// give examples with b&w... tweet Daniel
// -webkit-filter: blur(3px);
// filter: blur(3px);
?>

/* Contains font styles, borders, colors and images */

<?php echo $prefix; ?> .shell {

	direction: ltr; /* keeps control of the direction because not everything needs to be reversed */

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

	<?php if ($card_shadow) : ?>
		-moz-box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
		-webkit-box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
		box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);

		margin: 8px;
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

<?php echo $prefix; ?> .outerperson {

	<?php if ($card_border_width > 0) : ?>
		border: <?php echo $card_border_width; ?>px solid <?php echo ($card_border_color && $card_border_color != 'transparent') ? $card_border_color : '#fff' ?>;
	<?php endif; ?>
}

<?php echo $prefix; ?> .iconlinks,
<?php echo $prefix; ?> .vcard {
	position: absolute;
}

<?php if ($links_position == 'top' || $links_position == 'bottom') : ?>
	<?php echo $prefix; ?> .iconlinks ul li {
    	display: inline-block;
    }
<?php endif; ?>

<?php echo $prefix; ?> .iconlinks {
	<?php if ($links_position == 'bottom') : ?>
		bottom: 5px;
	<?php else : ?>
		top: 5px;
	<?php endif; ?>
}

<?php echo $prefix; ?> .picture_top .iconlinks,
<?php echo $prefix; ?> .ghost_picture_top .iconlinks {
	top: 5px;
	bottom: auto;
}

<?php echo $prefix; ?> .picture_left .iconlinks,
<?php echo $prefix; ?> .picture_top .iconlinks,
<?php echo $prefix; ?> .text_only:not(.ghost_picture_right) .iconlinks {
	left: 5px;
}

<?php echo $prefix; ?> .picture_right .iconlinks,
<?php echo $prefix; ?> .text_only.ghost_picture_right .iconlinks {
	right: 5px;
	left: auto;
}

<?php echo $prefix; ?> .picture_top .iconlinks ul,
<?php echo $prefix; ?> .ghost_picture_top .iconlinks ul {
	margin: 0 auto;
}

<?php echo $prefix; ?> .iconlinks .iconbg .icon,
<?php echo $prefix; ?> .vcard .iconbg .icon {
    padding: 6px;
    border-radius: <?php echo ($iconfont_link_size + .3); ?>em;
}

<?php if ($iconfont_link_bgcolor == '' || $iconfont_link_bgcolor == 'transparent') : ?>
	<?php echo $prefix; ?> .iconlinks .icon,
	<?php echo $prefix; ?> .vcard .icon {
		background-color: #fff;
		padding: 6px;
		border-radius: <?php echo ($iconfont_link_size + .3); ?>em;
	}
<?php endif; ?>

<?php echo $prefix; ?> .iconlinks ul li a,
<?php echo $prefix; ?> .vcard a {
	line-height: <?php echo $iconfont_link_size; ?>em;
	display: block;
}

<?php echo $prefix; ?> .picture_left .vcard,
<?php echo $prefix; ?> .text_only .vcard {
	<?php if ($links_position == 'bottom') : ?>
		top: 5px;
	<?php else : ?>
		bottom: 5px;
	<?php endif; ?>
	left: 5px;
}

<?php echo $prefix; ?> .picture_right .vcard,
<?php echo $prefix; ?> .text_only.ghost_picture_right .vcard {
	<?php if ($links_position == 'bottom') : ?>
		top: 5px;
	<?php else : ?>
		bottom: 5px;
	<?php endif; ?>
	right: 5px;
	left: auto;
}

<?php echo $prefix; ?> .picture_top .vcard,
<?php echo $prefix; ?> .text_only.ghost_picture_top .vcard {
	top: 5px;
	right: 5px;
	bottom: auto;
	left: auto;
}

<?php echo $prefix; ?> .picture_left .innerperson,
<?php echo $prefix; ?> .picture_right .innerperson,
<?php echo $prefix; ?> .text_only:not(.ghost_picture_top) .innerperson {
	display: -webkit-box;
	display: -moz-box;
	display: -webkit-flex;
	display: -ms-flexbox;
	display: flex;

	-webkit-box-align: stretch;
	-ms-flex-align: stretch;
	align-items: stretch;
}

<?php echo $prefix; ?> .picture_right .innerperson,
<?php echo $prefix; ?> .ghost_picture_right .innerperson {
	-webkit-box-orient: horizontal;
	-webkit-box-direction: reverse;
	-webkit-flex-direction: row-reverse;
	-ms-flex-direction: row-reverse;
	flex-direction: row-reverse;

	-webkit-box-pack: justify;
	-ms-flex-pack: justify;
	justify-content: space-between;
}

html[dir="rtl"] <?php echo $prefix; ?> .picture_right .innerperson,
html[dir="rtl"] <?php echo $prefix; ?> .ghost_picture_right .innerperson {
	-webkit-box-pack: start;
	-ms-flex-pack: start;
	justify-content: flex-start;
}

html[dir="rtl"] <?php echo $prefix; ?> .picture_left .innerperson,
html[dir="rtl"] <?php echo $prefix; ?> .ghost_picture_left .innerperson {
	-webkit-box-pack: justify;
	-ms-flex-pack: justify;
	justify-content: space-between;
}

<?php echo $prefix; ?> .picture_left .personpicture,
<?php echo $prefix; ?> .picture_right .personpicture,
<?php echo $prefix; ?> .text_only:not(.ghost_picture_top) .personpicture {
	min-height: <?php echo ($picture_height * 2 + $border_width * 2) ?>px;
	min-width: <?php echo ($picture_width * 2 + $border_width * 2) ?>px;
	overflow: unset;
}

<?php echo $prefix; ?> .picture_left .personpicture {
	margin-right: <?php echo ($hover_extra_margin + $picture_shadow_width) ?>px;
}

<?php echo $prefix; ?> .picture_right .personpicture {
	margin-left: <?php echo ($hover_extra_margin + $picture_shadow_width) ?>px;
}

<?php echo $prefix; ?> .person.text_only .personpicture {
    display: block;
}

<?php echo $prefix; ?> .person.text_only:not(.ghost_picture_top) .personpicture {
    min-width: <?php echo ($picture_width * 1.5 + $border_width) ?>px;
}

<?php echo $prefix; ?> .personpicture {
	float: none;
	height: inherit;
}

<?php echo $prefix; ?> .picture_left .personpicture .individualbg,
<?php echo $prefix; ?> .picture_right .personpicture .individualbg,
<?php echo $prefix; ?> .text_only:not(.ghost_picture_top) .personpicture .individualbg {
	display: -webkit-box;
	display: -moz-box;
	display: -webkit-flex;
	display: -ms-flexbox;
	display: flex;

	-webkit-box-align: stretch;
	-ms-flex-align: stretch;
	align-items: stretch;

	height: 100%;
	width: <?php echo ($picture_width * 1.5 + $border_width) ?>px;
}

<?php echo $prefix; ?> .picture_right .personpicture .individualbg {
	margin-left: <?php echo ($picture_width / 2 + $border_width) ?>px;
}

<?php echo $prefix; ?> .picture_top .personpicture .individualbg,
<?php echo $prefix; ?> .ghost_picture_top .personpicture .individualbg {
	height: <?php echo ($picture_height * 1.5 + $border_width) ?>px;
	max-width: inherit;
}

<?php echo $prefix; ?> .ghost_picture_top .personpicture .individualbg {
	position: relative;
}

<?php echo $prefix; ?> .picture_left .personpicture .individualbg img,
<?php echo $prefix; ?> .picture_right .personpicture .individualbg img,
<?php echo $prefix; ?> .text_only:not(.ghost_picture_top) .personpicture .individualbg img {
	height: 100%;
    max-width: inherit;
    width: auto;
}

<?php echo $prefix; ?> .picture_right .personpicture .individualbg img,
<?php echo $prefix; ?> .ghost_picture_right .personpicture .individualbg img {
	position: absolute;
	right: 0;
}

<?php echo $prefix; ?> .picture {
	<?php if ($border_width > 0) : ?>
		padding: <?php echo $border_width; ?>px;
	<?php endif; ?>
	background-color: <?php echo $pic_bgcolor; ?>;
	<?php if ($picture_shadow_width > 0) : ?>
		-moz-box-shadow: 0px 0px <?php echo ($picture_shadow_width) ?>px rgba(0, 0, 0, 0.5);
		-webkit-box-shadow: 0px 0px <?php echo ($picture_shadow_width) ?>px rgba(0, 0, 0, 0.5);
		box-shadow: 0px 0px <?php echo ($picture_shadow_width) ?>px rgba(0, 0, 0, 0.5);
	<?php endif; ?>
}

<?php echo $prefix; ?> .picture_left .picture {
	margin: <?php echo ($picture_height / 2) ?>px 0 0 <?php echo ($picture_width) ?>px;
}

<?php echo $prefix; ?> .picture_right .picture {
	margin: <?php echo ($picture_height / 2) ?>px <?php echo ($picture_width) ?>px 0 0;
}

<?php echo $prefix; ?> .picture_top .picture {
	margin: <?php echo $picture_height ?>px auto <?php echo ($hover_extra_margin + $picture_shadow_width) ?>px auto;
}

<?php echo $prefix; ?> .text_only .picture {
    display: none;
}

<?php echo $prefix; ?> .personinfo {
	padding: 5px;
	-webkit-flex-grow: 2;
	-moz-flex-grow: 2;
	-ms-flex-grow: 2;
	flex-grow: 2;
}

html[dir="rtl"] <?php echo $prefix; ?> .personinfo {
    text-align: right;
    direction: rtl;
}

<?php echo $prefix; ?> .picture_top .personfield.index1,
<?php echo $prefix; ?> .ghost_picture_top .personfield.index1 {
    text-align: center;
}

<?php echo $prefix; ?> .picture_top .wrap.alignself.personfield.index1,
<?php echo $prefix; ?> .ghost_picture_top .wrap.alignself.personfield.index1 {
	-webkit-box-pack: center; -ms-flex-pack: center; justify-content: center;
}

<?php echo $prefix; ?> .picture_top .beneath.personfield.index1,
<?php echo $prefix; ?> .ghost_picture_top .beneath.personfield.index1 {
	-webkit-box-align: center; -ms-flex-align: center; align-items: center;
}

<?php echo $prefix; ?> .picture_top .beneath.personfield.index1 .fieldlabel,
<?php echo $prefix; ?> .ghost_picture_top .beneath.personfield.index1 .fieldlabel,
<?php echo $prefix; ?> .picture_top .beneath.personfield.index1 .icon,
<?php echo $prefix; ?> .ghost_picture_top .beneath.personfield.index1 .icon {
	margin: 1px 0;
}

<?php echo $prefix; ?> .picture_top .personfield.index1 .nolabel,
<?php echo $prefix; ?> .ghost_picture_top .personfield.index1 .nolabel,
<?php echo $prefix; ?> .picture_top .personfield.index1 .noicon,
<?php echo $prefix; ?> .ghost_picture_top .personfield.index1 .noicon {
	display: none;
}

<?php echo $prefix; ?> .personfield.index1 .fieldvalue {
	font-style: italic;
}

<?php echo $prefix; ?> .personfield,
<?php echo $prefix; ?> .personlinks {
	font-size: 0.9em;
}

<?php echo $prefix; ?> .personfield.fieldname {
	margin-bottom: 10px;
	font-size: 2em;
	line-height: 1.2em;
}

<?php echo $prefix; ?> .personfield.fieldname .noicon {
	font-size: 0.45em;
}

<?php echo $prefix; ?> .featured.picture_left .picture_veil .feature,
<?php echo $prefix; ?> .featured.picture_right .picture_veil .feature,
<?php echo $prefix; ?> .featured.picture_top .picture_veil .feature {
	display: none;
}

<?php echo $prefix; ?> .featured .text_veil .feature {
    display: block;
	top: 5px;
	right: 5px;
	position: absolute;
}

html[dir="rtl"] <?php echo $prefix; ?> .featured .text_veil .feature {
	left: 5px;
	right: auto;
}

<?php echo $prefix; ?> .personinfo .category,
<?php echo $prefix; ?> .personinfo .tags {
	font-size: .8em;
}

<?php echo $prefix; ?> .personlink.go i {
	display: none;
}
