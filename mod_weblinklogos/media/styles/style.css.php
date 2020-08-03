<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
header("Content-type: text/css; charset=UTF-8");
?>
<?php if ($animated) : ?>
	<?php if (!$horizontal) : ?>
		#weblinklogo_<?php echo $suffix; ?> ul.weblink_items li.weblink_item {
			width: 100%;
		}
	<?php else : ?>
		#weblinklogo_<?php echo $suffix; ?> ul.weblink_items.list li.weblink_item {
			display: inline-block;
		}
	<?php endif; ?>

		#weblinklogo_<?php echo $suffix; ?> ul.weblink_items.grid .shell_animate {
			<?php if ($overall_width) : ?>
				max-width: <?php echo $overall_width; ?>px;
			<?php else : ?>
				<?php if ($horizontal) : ?>
					max-width: <?php echo $width; ?>px;
				<?php endif; ?>
			<?php endif; ?>
			margin: 0 auto;
		}

		<?php if ($overall_width) : ?>
			#weblinklogo_<?php echo $suffix; ?> ul.weblink_items.list .shell_animate {
				max-width: <?php echo $overall_width; ?>px;
				margin: 0 auto;
			}
		<?php endif; ?>
<?php endif; ?>

#weblinklogo_<?php echo $suffix; ?> ul.weblink_items li .weblink_item_wrapper {
	<?php if ($animated) : ?>
		margin-top: <?php echo $margin_top; ?>px;
		margin-bottom: <?php echo $margin_bottom; ?>px;
		<?php if ($card_shadow) : ?>
			margin-left: <?php echo $shadow_width; ?>px;
			margin-right: <?php echo $shadow_width; ?>px;
		<?php endif; ?>
	<?php else : ?>
		margin: <?php echo $margin_top; ?>px <?php echo $margin_right; ?>px <?php echo $margin_bottom; ?>px <?php echo $margin_left; ?>px;
	<?php endif; ?>

	position: relative;

	<?php if ($overall_bgcolor != 'transparent') : ?>
		background-color: <?php echo $overall_bgcolor; ?>;
	<?php endif; ?>

	<?php if ($card_shadow) : ?>
		-moz-box-shadow: 0 0 8px rgba(0, 0, 0, 0.5);
		-webkit-box-shadow: 0 0 8px rgba(0, 0, 0, 0.5);
		box-shadow: 0 0 8px rgba(0, 0, 0, 0.5);
	<?php endif; ?>

	<?php if ($card_radius > 0) : ?>
		-moz-border-radius: <?php echo $card_radius; ?>px;
		-webkit-border-radius: <?php echo $card_radius; ?>px;
		border-radius: <?php echo $card_radius; ?>px;
	<?php endif; ?>

	<?php if ($card_border_width > 0) : ?>
		border: <?php echo $card_border_width; ?>px solid <?php echo $card_border_color; ?>;
	<?php endif; ?>

	<?php if ($overall_bgcolor != 'transparent' || $card_shadow || $card_border_width > 0) : ?>
		padding: 10px;
	<?php endif; ?>
}

<?php if (!$animated) : ?>
#weblinklogo_<?php echo $suffix; ?> ul.weblink_items.grid li.weblink_item .weblink_item_wrapper {
	<?php if ($overall_width) : ?>
		max-width: <?php echo $overall_width; ?>px;
		<?php if ($force_width) : ?>
			min-width: <?php echo $overall_width; ?>px;
		<?php endif; ?>
	<?php elseif ($width > 0 && !$restrict_width_to_image) : ?>
		width: <?php echo $width; ?>px;
	<?php endif; ?>
}
<?php endif; ?>

#weblinklogo_<?php echo $suffix; ?> ul.weblink_items.list li.weblink_item .weblink_item_wrapper {

	<?php if (!$animated) : ?>
		<?php if ($overall_width) : ?>
			max-width: <?php echo $overall_width; ?>px;
			<?php if ($force_width) : ?>
				min-width: <?php echo $overall_width; ?>px;
			<?php endif; ?>
			margin-left: auto;
			margin-right: auto;
		<?php endif; ?>
	<?php endif; ?>

	display: -webkit-box;
	display: -ms-flexbox;
	display: flex;

	<?php if ($content_valign == 'middle') : ?>
		-webkit-box-align: center;
		-ms-flex-align: center;
		align-items: center;
	<?php elseif ($content_valign == 'bottom') : ?>
		-webkit-box-align: end;
		-ms-flex-align: end;
		align-items: flex-end;
	<?php else : ?>
	 	-webkit-box-align: start;
		-ms-flex-align: start;
		align-items: flex-start;
	<?php endif; ?>

	-webkit-flex-wrap: wrap;
	-ms-flex-wrap: wrap;
	flex-wrap: wrap;
}

	<?php if ($logo_bgcolor != 'transparent') : ?>
		#weblinklogo_<?php echo $suffix; ?> ul.weblink_items li .logo {
			background-color: <?php echo $logo_bgcolor; ?>;
			padding: 10px;
		}
	<?php endif; ?>

	<?php if ($width > 0 && !$restrict_width_to_image) : ?>
		#weblinklogo_<?php echo $suffix; ?> ul.weblink_items.list li .logo {
			width: <?php echo $width; ?>px;
		}
	<?php endif; ?>

		#weblinklogo_<?php echo $suffix; ?> ul.weblink_items li .logo_caption {
			font-size: <?php echo ($font_size / 100); ?>em;
		}

		#weblinklogo_<?php echo $suffix; ?> ul.weblink_items li .logo_link {
		    filter: alpha(opacity=<?php echo ($opacity * 100); ?>);
		    opacity: <?php echo $opacity; ?>;
		}

	#weblinklogo_<?php echo $suffix; ?> ul.weblink_items li .logo_link a {
		<?php if ($width > 0) : ?>
			<?php if (!$restrict_width_to_image) : ?>
				width: <?php echo $width; ?>px;
			<?php else : ?>
				max-width: <?php echo $width; ?>px;
			<?php endif; ?>
		<?php endif; ?>
		<?php if ($height > 0 && !$restrict_width_to_image) : ?>
			height: <?php echo $height; ?>px;
		<?php endif; ?>
	}

	#weblinklogo_<?php echo $suffix; ?> ul.weblink_items li .logo_link a img {
		<?php if ($restrict_width_to_image) : ?>
			position: relative;
		<?php else : ?>
			position: absolute;
			left: 50%;
			transform: translateX(-50%);
		<?php endif; ?>
	}

	<?php if ($restrict_width_to_image) : ?>
		#weblinklogo_<?php echo $suffix; ?> ul.weblink_items li .logo_link a img.hover {
			position: absolute;
			left: 50%;
			transform: translateX(-50%);
		}
	<?php endif; ?>

	#weblinklogo_<?php echo $suffix; ?> ul.weblink_items li .description {
		font-size: <?php echo ($font_size / 100); ?>em;
	}

	#weblinklogo_<?php echo $suffix; ?> ul.weblink_items.list li .description {
		-webkit-box-flex: 1;
		-ms-flex: 1;
		flex: 1;
	}

	#weblinklogo_<?php echo $suffix; ?> ul.weblink_items.grid li .description {
		text-align: <?php echo $content_align; ?>;
	}

/* carousel */

<?php if ($animated) : ?>
	#weblinklogo_<?php echo $suffix; ?> {
		height: 0;
		opacity: 0;
	}

	#weblinklogo_<?php echo $suffix; ?>.show {
		height: auto;
  		opacity: 1;
  		transition: opacity 1000ms;
	}
<?php endif; ?>

<?php if ($show_arrows || $show_pages) : ?>

	#weblinklogo_<?php echo $suffix; ?> .items_pagination {
		text-align: center;
		font-size: <?php echo $arrow_size; ?>em;
		opacity: 0;
	}

	#weblinklogo_<?php echo $suffix; ?> .items_pagination ul {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	#weblinklogo_<?php echo $suffix; ?> .items_pagination li {
		display: inline-block;
		list-style: none;
		cursor: pointer;
	}

	#weblinklogo_<?php echo $suffix; ?>.side_navigation .items_pagination {
		position: absolute;
		top: <?php echo $arrow_offset; ?>px;
		z-index: 100;
	}

	#weblinklogo_<?php echo $suffix; ?>.side_navigation .items_pagination.top {
		left: 0;
	}

	#weblinklogo_<?php echo $suffix; ?>.side_navigation .items_pagination.bottom {
		right: 0;
	}

	#weblinklogo_<?php echo $suffix; ?>.above_navigation .items_pagination {
		display: block;
	}

	#weblinklogo_<?php echo $suffix; ?>.above_navigation .items_pagination.top {
		margin-bottom: <?php echo $arrow_offset; ?>px;
	}

	#weblinklogo_<?php echo $suffix; ?>.above_navigation .items_pagination.bottom {
		margin-top: <?php echo $arrow_offset; ?>px;
	}

	#weblinklogo_<?php echo $suffix; ?>.under_navigation .items_pagination {
		margin-top: <?php echo $arrow_offset; ?>px;
	}

	#weblinklogo_<?php echo $suffix; ?> .items_pagination a:hover,
	#weblinklogo_<?php echo $suffix; ?> .items_pagination a:focus {
		text-decoration: none;
	}

	/* backward compatibility */
	<?php echo $prefix; ?> .items_pagination.pagination .pagenumbers {
		display: none;
	}

	/* extra bootstrap 2 styles for 'around' positions */
	<?php if ($bootstrap && $bootstrap_version == 2) : ?>

    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination ul > li:first-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination ul > li:first-child > span {
    		border-left-width: 1px;
    		-webkit-border-top-left-radius: 4px;
    		-moz-border-radius-topleft: 4px;
    		border-top-left-radius: 4px;
    		-webkit-border-bottom-left-radius: 4px;
    		-moz-border-radius-bottomleft: 4px;
    		border-bottom-left-radius: 4px;
    	}

    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination-mini ul > li:first-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination-mini ul > li:first-child > span,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination-small ul > li:first-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination-small ul > li:first-child > span {
    		-webkit-border-top-left-radius: 3px;
    		-moz-border-radius-topleft: 3px;
    		border-top-left-radius: 3px;
    		-webkit-border-bottom-left-radius: 3px;
    		-moz-border-radius-bottomleft: 3px;
    		border-bottom-left-radius: 3px;
    	}

    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination ul > li:last-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination ul > li:last-child > span {
    		-webkit-border-top-right-radius: 4px;
    		-moz-border-radius-topright: 4px;
    		border-top-right-radius: 4px;
    		-webkit-border-bottom-right-radius: 4px;
    		-moz-border-radius-bottomright: 4px;
    		border-bottom-right-radius: 4px;
    	}

    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination-mini ul > li:last-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination-mini ul > li:last-child > span,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination-small ul > li:last-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.pagination-small ul > li:last-child > span {
    		-webkit-border-top-right-radius: 3px;
    		-moz-border-radius-topright: 3px;
    		border-top-right-radius: 3px;
    		-webkit-border-bottom-right-radius: 3px;
    		-moz-border-radius-bottomright: 3px;
    		border-bottom-right-radius: 3px;
    	}
	<?php endif; ?>

    /* extra bootstrap 3 styles for 'around' positions */
    <?php if ($bootstrap && $bootstrap_version == 3) : ?>

    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.left .pagination > li:first-child > a,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.left .pagination > li:first-child > span,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.up .pagination > li:first-child > a,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.up .pagination > li:first-child > span {
        	-webkit-border-top-right-radius: 4px;
        	-moz-border-radius-topright: 4px;
        	border-top-right-radius: 4px;
        	-webkit-border-bottom-right-radius: 4px;
        	-moz-border-radius-bottomright: 4px;
        	border-bottom-right-radius: 4px;
        }

        #weblinklogo_<?php echo $suffix; ?> .items_pagination.left .pagination-sm > li:first-child > a,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.left .pagination-sm > li:first-child > span,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.up .pagination-sm > li:first-child > a,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.up .pagination-sm > li:first-child > span {
        	-webkit-border-top-right-radius: 3px;
        	-moz-border-radius-topright: 3px;
        	border-top-right-radius: 3px;
        	-webkit-border-bottom-right-radius: 3px;
        	-moz-border-radius-bottomright: 3px;
        	border-bottom-right-radius: 3px;
        }

    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.right .pagination > li:last-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.right .pagination > li:last-child > span,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.down .pagination > li:last-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.down .pagination > li:last-child > span {
    		-webkit-border-top-left-radius: 4px;
        	-moz-border-radius-topleft: 4px;
        	border-top-left-radius: 4px;
        	-webkit-border-bottom-left-radius: 4px;
        	-moz-border-radius-bottomleft: 4px;
        	border-bottom-left-radius: 4px;
    	}

    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.right .pagination-sm > li:last-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.right .pagination-sm > li:last-child > span,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.down .pagination-sm > li:last-child > a,
    	#weblinklogo_<?php echo $suffix; ?> .items_pagination.down .pagination-sm > li:last-child > span {
    		-webkit-border-top-left-radius: 3px;
        	-moz-border-radius-topleft: 3px;
        	border-top-left-radius: 3px;
        	-webkit-border-bottom-left-radius: 3px;
        	-moz-border-radius-bottomleft: 3px;
        	border-bottom-left-radius: 3px;
    	}
    <?php endif; ?>

    /* extra bootstrap 4 styles for 'around' positions */
    <?php if ($bootstrap && $bootstrap_version == 4) : ?>

        #weblinklogo_<?php echo $suffix; ?> .items_pagination.left .page-item:first-child .page-link,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.up .page-item:first-child .page-link {
        	-webkit-border-top-right-radius: .25rem;
        	-moz-border-radius-topright: .25rem;
        	border-top-right-radius: .25rem;
        	-webkit-border-bottom-right-radius: .25rem;
        	-moz-border-radius-bottomright: .25rem;
        	border-bottom-right-radius: .25rem;
        }

        #weblinklogo_<?php echo $suffix; ?> .items_pagination.left .pagination-sm .page-item:first-child .page-link,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.up .pagination-sm .page-item:first-child .page-link {
        	-webkit-border-top-right-radius: .2rem;
        	-moz-border-radius-topright: .2rem;
        	border-top-right-radius: .2rem;
        	-webkit-border-bottom-right-radius: .2rem;
        	-moz-border-radius-bottomright: .2rem;
        	border-bottom-right-radius: .2rem;
        }

        #weblinklogo_<?php echo $suffix; ?> .items_pagination.right .page-item:last-child .page-link,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.down .page-item:last-child .page-link {
        	-webkit-border-top-left-radius: .25rem;
        	-moz-border-radius-topleft: .25rem;
        	border-top-left-radius: .25rem;
        	-webkit-border-bottom-left-radius: .25rem;
        	-moz-border-radius-bottomleft: .25rem;
        	border-bottom-left-radius: .25rem;
        }

        #weblinklogo_<?php echo $suffix; ?> .items_pagination.right .pagination-sm .page-item:last-child .page-link,
        #weblinklogo_<?php echo $suffix; ?> .items_pagination.down .pagination-sm .page-item:last-child .page-link {
        	-webkit-border-top-left-radius: .2rem;
        	-moz-border-radius-topleft: .2rem;
        	border-top-left-radius: .2rem;
        	-webkit-border-bottom-left-radius: .2rem;
        	-moz-border-radius-bottomleft: .2rem;
        	border-bottom-left-radius: .2rem;
        }
    <?php endif; ?>
<?php endif; ?>