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

<?php echo $prefix; ?> .shell {
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

<?php echo $prefix; ?> .outerperson {
	display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
	display: flex;

	<?php if ($links_position == 'top') : ?>
		-webkit-flex-wrap: wrap;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
	<?php elseif ($links_position == 'bottom') : ?>
		-webkit-flex-wrap: wrap-reverse;
        -ms-flex-wrap: wrap-reverse;
        flex-wrap: wrap-reverse;
	<?php else : ?>
		-webkit-flex-wrap: nowrap;
        -ms-flex-wrap: nowrap;
        flex-wrap: nowrap;
	<?php endif; ?>

	<?php if ($links_position == 'side') : ?>
		-webkit-box-orient: horizontal;
    	-webkit-box-direction: normal;
    	-webkit-flex-direction: row;
        -ms-flex-direction: row;
        flex-direction: row;
	<?php else : ?>
		-webkit-box-orient: horizontal;
    	-webkit-box-direction: reverse;
    	-webkit-flex-direction: row-reverse;
        -ms-flex-direction: row-reverse;
        flex-direction: row-reverse;
	<?php endif; ?>

	-webkit-box-pack: justify;
    -webkit-justify-content: space-between;
    -ms-flex-pack: justify;
	justify-content: space-between;

	padding: 5px;

	<?php if ($card_border_width > 0) : ?>
		margin: 2px;
		border: <?php echo $card_border_width; ?>px solid <?php echo ($card_border_color && $card_border_color != 'transparent') ? $card_border_color : '#fff' ?>;

		<?php if ($card_radius > 0) : ?>
    		-moz-border-radius: <?php echo $card_radius; ?>px;
    		-webkit-border-radius: <?php echo $card_radius; ?>px;
    		border-radius: <?php echo $card_radius; ?>px;
    	<?php endif; ?>
	<?php endif; ?>
}

<?php if ($links_position == 'side') : ?>
	html[dir="rtl"] <?php echo $prefix; ?> .outerperson {
		-webkit-box-orient: horizontal;
    	-webkit-box-direction: reverse;
    	-webkit-flex-direction: row-reverse;
        -ms-flex-direction: row-reverse;
        flex-direction: row-reverse;
	}
<?php else : ?>
	html[dir="rtl"] <?php echo $prefix; ?> .outerperson {
		-webkit-box-orient: horizontal;
    	-webkit-box-direction: normal;
    	-webkit-flex-direction: row;
        -ms-flex-direction: row;
        flex-direction: row;
	}
<?php endif; ?>

<?php echo $prefix; ?> .picture_right .outerperson,
<?php echo $prefix; ?> .ghost_picture_right .outerperson {
	<?php if ($links_position == 'side') : ?>
		-webkit-box-orient: horizontal;
    	-webkit-box-direction: reverse;
    	-webkit-flex-direction: row-reverse;
        -ms-flex-direction: row-reverse;
        flex-direction: row-reverse;
	<?php else : ?>
		-webkit-box-orient: horizontal;
    	-webkit-box-direction: normal;
    	-webkit-flex-direction: row;
        -ms-flex-direction: row;
        flex-direction: row;
	<?php endif; ?>
}

<?php if ($links_position == 'side') : ?>
	html[dir="rtl"] <?php echo $prefix; ?> .picture_right .outerperson,
	html[dir="rtl"] <?php echo $prefix; ?> .ghost_picture_right .outerperson {
		-webkit-box-orient: horizontal;
    	-webkit-box-direction: normal;
    	-webkit-flex-direction: row;
        -ms-flex-direction: row;
        flex-direction: row;
	}
<?php else : ?>
	html[dir="rtl"] <?php echo $prefix; ?> .picture_right .outerperson,
	html[dir="rtl"] <?php echo $prefix; ?> .ghost_picture_right .outerperson {
		-webkit-box-orient: horizontal;
    	-webkit-box-direction: reverse;
    	-webkit-flex-direction: row-reverse;
        -ms-flex-direction: row-reverse;
        flex-direction: row-reverse;
	}
<?php endif; ?>

<?php if ($links_position == 'top' || $links_position == 'bottom') : ?>
    <?php echo $prefix; ?> .picture_top .outerperson,
    <?php echo $prefix; ?> .ghost_picture_top .outerperson {
    	-webkit-box-pack: center;
    	-webkit-justify-content: center;
        -ms-flex-pack: center;
    	justify-content: center;
    }
<?php endif; ?>

<?php if ($links_position == 'side') : ?>
    <?php echo $prefix; ?> .iconlinks {
    	-webkit-box-flex: 0;
    	-webkit-flex: none;
        -ms-flex: none;
        flex: none;
    }
<?php endif; ?>

<?php if ($links_position == 'top' || $links_position == 'bottom') : ?>
	<?php echo $prefix; ?> .iconlinks ul li {
    	display: inline-block;
    }
<?php endif; ?>

<?php echo $prefix; ?> .vcard {
	/*flex: none;*/
	<?php if ($links_position == 'side') : ?>
		position: absolute;
		bottom: 5px;
		left: 5px;
	<?php endif; ?>
}

<?php if ($links_position == 'side') : ?>
    <?php echo $prefix; ?> .picture_right .vcard,
    <?php echo $prefix; ?> .ghost_picture_right .vcard {
    	right: 5px;
    	left: auto;
    }
<?php endif; ?>

<?php echo $prefix; ?> .innerperson {
	-webkit-box-flex: 1;
    -webkit-flex: 1 auto;
    -ms-flex: 1 auto;
    flex: 1 auto;

	<?php if ($links_position == 'top' || $links_position == 'bottom') : ?>
		width: 100%;
	<?php endif; ?>
}

<?php echo $prefix; ?> .picture_left .personpicture {
	margin-right: 6px;
}

<?php echo $prefix; ?> .picture_right .personpicture {
	margin-left: 6px;
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

<?php echo $prefix; ?> .picture_left .picture,
<?php echo $prefix; ?> .picture_right .picture {
	margin: <?php echo (10 + $hover_extra_margin) ?>px;
}

<?php echo $prefix; ?> .picture_top .picture {
	margin: <?php echo (10 + $hover_extra_margin) ?>px auto;
}

<?php echo $prefix; ?> .picture_left .personinfo {
	<?php if ($overflow) : ?>
		padding: 5px;
	<?php else : ?>
		padding: 5px 5px 5px 0;
	<?php endif; ?>
}

<?php echo $prefix; ?> .picture_right .personinfo {
	<?php if ($overflow) : ?>
		padding: 5px;
	<?php else : ?>
		padding: 5px 0 5px 5px;
	<?php endif; ?>
}

<?php echo $prefix; ?> .picture_top .personinfo,
<?php echo $prefix; ?> .text_only .personinfo {
	padding: 5px;
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
	font-size: 1.5em;
	font-weight: bold;
	line-height: 1.2em;
}

<?php echo $prefix; ?> .personfield.fieldname .noicon {
	font-size: 0.6em;
}

<?php echo $prefix; ?> .featured.picture_left .picture_veil .feature,
<?php echo $prefix; ?> .featured.picture_right .picture_veil .feature,
<?php echo $prefix; ?> .featured.picture_top .picture_veil .feature {
	display: block;
	top: 5px;
	left: 5px;
	position: absolute;
}

<?php echo $prefix; ?> .featured.text_only.ghost_picture_top .text_veil .feature {
	display: block;
	top: 2px;
	left: 2px;
	right: auto;
	position: absolute;
}

<?php echo $prefix; ?> .featured.text_only .text_veil .feature {
	display: block;
	top: 2px;
	right: 2px;
	position: absolute;
}

<?php echo $prefix; ?> .personinfo .category,
<?php echo $prefix; ?> .personinfo .tags {
	font-size: .8em;
}