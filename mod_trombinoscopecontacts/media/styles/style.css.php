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

<?php if (!$animated) : ?>
	<?php echo $prefix; ?> .personlist,
	<?php echo $prefix; ?> .contactgroup {
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;

		-ms-flex-wrap: wrap;
		flex-wrap: wrap;

		-webkit-box-pack: center;
		-ms-flex-pack: center;
		justify-content: center;
	}

	<?php echo $prefix; ?> .groupheader,
	<?php echo $prefix; ?> .heading-group {
	    font-size: <?php echo $font_size; ?>px;
	}
<?php endif; ?>

	<?php echo $prefix; ?> .person {
		font-size: <?php echo $font_size; ?>px<?php if ($animated) : ?> !important<?php endif; ?>;

		<?php if (!$animated) : ?>
			-webkit-box-flex: 1;
			-ms-flex: 1 1 auto;
			flex: 1 1 auto;

			width: <?php echo $card_width; ?><?php echo $card_width_unit; ?>;

			<?php if ($card_min_width) : ?>
				min-width: <?php echo $card_min_width; ?>px;
			<?php endif; ?>
			<?php if ($card_max_width) : ?>
				max-width: <?php echo $card_max_width; ?>px;
			<?php endif; ?>
			<?php if ($card_width_unit == '%') : ?>
				margin: <?php echo intval($space_between_cards / 2); ?>px <?php echo $margin_in_perc; ?>%;
			<?php else : ?>
				margin: <?php echo intval($space_between_cards / 2); ?>px;
			<?php endif; ?>
		<?php else : ?>
			margin: 0;
		<?php endif; ?>
	}

	<?php if ($animated && $card_max_width) : ?>
		<?php echo $prefix; ?> .shell_animate {
			max-width: <?php echo $card_max_width; ?>px;
			margin: 0 auto;
		}
	<?php endif; ?>

						<?php echo $prefix; ?> .iconlinks .icon,
						<?php echo $prefix; ?> .vcard .icon {
							font-size: <?php echo $iconfont_link_size; ?>em;
							<?php if ($iconfont_link_color && $iconfont_link_color != 'transparent') : ?>
								color: <?php echo $iconfont_link_color; ?>;
							<?php endif; ?>
						}

						<?php if ($iconfont_link_bgcolor) : ?>
							<?php echo $prefix; ?> .iconlinks .iconbg .icon,
							<?php echo $prefix; ?> .vcard .iconbg .icon {
								background-color: <?php echo $iconfont_link_bgcolor; ?>;
							}
						<?php endif; ?>

		<?php if ($card_height > 0) : ?>
			<?php echo $prefix; ?> .innerperson {
				height: <?php echo $card_height; ?>px;
			}
		<?php endif; ?>

				<?php echo $prefix; ?> .featured .feature .icon {
				    font-size: <?php echo $iconfont_size; ?>em;
					<?php if ($iconfont_color && $iconfont_color != 'transparent') : ?>
						color: <?php echo $iconfont_color; ?>;
					<?php endif; ?>
    			}

    			<?php if ($effect_extra_margin > 0) : ?>
    				<?php echo $prefix; ?> .picture_animate {
    					margin: <?php echo $effect_extra_margin; ?>px;
    				}
    			<?php endif; ?>

				<?php echo $prefix; ?> .picture {
					<?php if ($keep_height) : ?>
						width: <?php echo $picture_width; ?>px;
						height: <?php echo $picture_height; ?>px;
					<?php else : ?>
	    				max-width: <?php echo $picture_width; ?>px;
						max-height: <?php echo $picture_height; ?>px;
					<?php endif; ?>

					<?php if ($picture_rotation != 0 && $picture_scale == 1) : ?>
						-moz-transform: rotate(<?php echo $picture_rotation; ?>deg);
						-webkit-transform: rotate(<?php echo $picture_rotation; ?>deg);
						-o-transform: rotate(<?php echo $picture_rotation; ?>deg);
						-ms-transform: rotate(<?php echo $picture_rotation; ?>deg);
						transform: rotate(<?php echo $picture_rotation; ?>deg);
					<?php endif; ?>
					<?php if ($picture_rotation == 0 && $picture_scale != 1) : ?>
						-moz-transform: scale(<?php echo $picture_scale; ?>));
						-webkit-transform: scale(<?php echo $picture_scale; ?>));
						-o-transform: scale(<?php echo $picture_scale; ?>));
						-ms-transform: scale(<?php echo $picture_scale; ?>));
						transform: scale(<?php echo $picture_scale; ?>));
					<?php endif; ?>
					<?php if ($picture_rotation != 0 && $picture_scale != 1) : ?>
						-moz-transform: scale(<?php echo $picture_scale; ?>) rotate(<?php echo $picture_rotation; ?>deg);
						-webkit-transform: scale(<?php echo $picture_scale; ?>) rotate(<?php echo $picture_rotation; ?>deg);
						-o-transform: scale(<?php echo $picture_scale; ?>) rotate(<?php echo $picture_rotation; ?>deg);
						-ms-transform: scale(<?php echo $picture_scale; ?>) rotate(<?php echo $picture_rotation; ?>deg);
						transform: scale(<?php echo $picture_scale; ?>) rotate(<?php echo $picture_rotation; ?>deg);
					<?php endif; ?>

					<?php if ($border_radius > 0) : ?>
						border-radius: <?php echo $border_radius; ?>px;
						-moz-border-radius: <?php echo $border_radius; ?>px;
						-webkit-border-radius: <?php echo $border_radius; ?>px;
					<?php endif; ?>
				}

					<?php if ($border_radius_img > 0) : ?>
						<?php echo $prefix; ?> .picture img {
							border-radius: <?php echo $border_radius_img; ?>px;
							-moz-border-radius: <?php echo $border_radius_img; ?>px;
							-webkit-border-radius: <?php echo $border_radius_img; ?>px;
						}
					<?php endif; ?>

					<?php if (!$keep_height) : ?>
						<?php echo $prefix; ?> .picture .nopicture {
							width: <?php echo $picture_width; ?>px;
							height: <?php echo $picture_height; ?>px;
						}
					<?php endif; ?>

			<?php if (!$overflow) : ?>
				<?php echo $prefix; ?> .personinfo {
					overflow: hidden;
				}
			<?php endif; ?>

                <?php echo $prefix; ?> .personfield {
                	line-height: <?php echo $line_spacing; ?>em;
                }

					<?php echo $prefix; ?> .personinfo .icon {
						font-size: <?php echo $iconfont_size; ?>em;
						<?php if ($iconfont_color && $iconfont_color != 'transparent') : ?>
							color: <?php echo $iconfont_color; ?>;
						<?php endif; ?>
					}

					<?php echo $prefix; ?> .personinfo .noicon {
						font-size: <?php echo $iconfont_size; ?>em;
					}

					<?php if ($iconfont_bgcolor) : ?>
						<?php echo $prefix; ?> .personinfo .iconbg .icon {
							background-color: <?php echo $iconfont_bgcolor; ?>;
						}
					<?php endif; ?>

					<?php if ($label_width > 0 || $label_color) : ?>
    					<?php echo $prefix; ?> .fieldlabel {
    						<?php if ($label_width > 0) : ?>
    							min-width: <?php echo $label_width; ?>px;
    							width: <?php echo $label_width; ?>px;
    							overflow: hidden;
    							text-overflow: ellipsis;
    							white-space: nowrap;
    							text-align: left;
    						<?php endif; ?>
    						<?php if ($label_color && $label_color != 'transparent') : ?>
    							color: <?php echo $label_color; ?>;
    						<?php endif; ?>
    					}
    				<?php endif; ?>

					<?php if ($label_width > 0) : ?>
    					html[dir="rtl"] <?php echo $prefix; ?> .fieldlabel {
    						text-align: right;
    					}
    				<?php endif; ?>

					<?php if ($label_bgcolor) : ?>
						<?php echo $prefix; ?> .lblbg .fieldlabel {
							background-color: <?php echo $label_bgcolor; ?>;
						}
					<?php endif; ?>

					<?php if ($label_width > 0) : ?>
						<?php echo $prefix; ?> .nowrap .nolabel {
							display: inline-block;
							min-width: <?php echo $label_width; ?>px;
    						width: <?php echo $label_width; ?>px;
							margin: 1px 6px 1px 0;
						}

						<?php echo $prefix; ?> .wrap.alignself .nolabel {
							-webkit-box-flex: 0;
							-ms-flex: 0 0 auto;
							flex: 0 0 auto;

							display: inline-block;
							min-width: <?php echo $label_width; ?>px;
    						width: <?php echo $label_width; ?>px;
							margin: 1px 6px 1px 0;
						}
					<?php endif; ?>

/* carousel */

<?php if ($animated) : ?>
	<?php echo $prefix; ?> {
		height: 0;
		opacity: 0;
		z-index: 0;
	}

	<?php echo $prefix; ?>.show {
		height: auto;
  		opacity: 1;
  		transition: opacity 1000ms;
	}
<?php endif; ?>

<?php if ($show_arrows || $show_pages) : ?>

    <?php echo $prefix; ?> .items_pagination {
    	text-align: center;
    	font-size: <?php echo $arrow_size; ?>em;
		opacity: 0;
    }

    <?php echo $prefix; ?> .items_pagination ul {
    	margin: 0;
    	padding: 0;
    }

    <?php echo $prefix; ?> .items_pagination li {
    	display: inline-block;
    	list-style: none;
    	cursor: pointer;
    }

    <?php echo $prefix; ?>.side_navigation .items_pagination {
    	position: absolute;
    	top: <?php echo $arrow_offset; ?>px;
    	z-index: 100;
    }

    <?php echo $prefix; ?>.side_navigation .items_pagination.top {
    	left: 0;
    }

    <?php echo $prefix; ?>.side_navigation .items_pagination.bottom {
    	right: 0;
    }

    <?php echo $prefix; ?>.above_navigation .items_pagination {
    	display: block;
    }

    <?php echo $prefix; ?>.above_navigation .items_pagination.top {
    	margin-bottom: <?php echo $arrow_offset; ?>px;
    }

    <?php echo $prefix; ?>.above_navigation .items_pagination.bottom {
    	margin-top: <?php echo $arrow_offset; ?>px;
    }

    <?php echo $prefix; ?>.under_navigation .items_pagination {
    	margin-top: <?php echo $arrow_offset; ?>px;
    }

    <?php echo $prefix; ?> .items_pagination a:hover,
    <?php echo $prefix; ?> .items_pagination a:focus {
    	text-decoration: none;
    }

    /* backward compatibility */
	<?php echo $prefix; ?> .items_pagination.pagination .pagenumbers {
		display: none;
	}

	/* extra bootstrap 2 styles for 'around' positions */
	<?php if ($bootstrap_arrows && $bootstrap_version == 2) : ?>

    	<?php echo $prefix; ?> .items_pagination.pagination ul > li:first-child > a,
    	<?php echo $prefix; ?> .items_pagination.pagination ul > li:first-child > span {
    		border-left-width: 1px;
    		-webkit-border-top-left-radius: 4px;
    		-moz-border-radius-topleft: 4px;
    		border-top-left-radius: 4px;
    		-webkit-border-bottom-left-radius: 4px;
    		-moz-border-radius-bottomleft: 4px;
    		border-bottom-left-radius: 4px;
    	}

    	<?php echo $prefix; ?> .items_pagination.pagination-mini ul > li:first-child > a,
    	<?php echo $prefix; ?> .items_pagination.pagination-mini ul > li:first-child > span,
    	<?php echo $prefix; ?> .items_pagination.pagination-small ul > li:first-child > a,
    	<?php echo $prefix; ?> .items_pagination.pagination-small ul > li:first-child > span {
    		-webkit-border-top-left-radius: 3px;
    		-moz-border-radius-topleft: 3px;
    		border-top-left-radius: 3px;
    		-webkit-border-bottom-left-radius: 3px;
    		-moz-border-radius-bottomleft: 3px;
    		border-bottom-left-radius: 3px;
    	}

    	<?php echo $prefix; ?> .items_pagination.pagination ul > li:last-child > a,
    	<?php echo $prefix; ?> .items_pagination.pagination ul > li:last-child > span {
    		-webkit-border-top-right-radius: 4px;
    		-moz-border-radius-topright: 4px;
    		border-top-right-radius: 4px;
    		-webkit-border-bottom-right-radius: 4px;
    		-moz-border-radius-bottomright: 4px;
    		border-bottom-right-radius: 4px;
    	}

    	<?php echo $prefix; ?> .items_pagination.pagination-mini ul > li:last-child > a,
    	<?php echo $prefix; ?> .items_pagination.pagination-mini ul > li:last-child > span,
    	<?php echo $prefix; ?> .items_pagination.pagination-small ul > li:last-child > a,
    	<?php echo $prefix; ?> .items_pagination.pagination-small ul > li:last-child > span {
    		-webkit-border-top-right-radius: 3px;
    		-moz-border-radius-topright: 3px;
    		border-top-right-radius: 3px;
    		-webkit-border-bottom-right-radius: 3px;
    		-moz-border-radius-bottomright: 3px;
    		border-bottom-right-radius: 3px;
    	}
	<?php endif; ?>

    /* extra bootstrap 3 styles for 'around' positions */
    <?php if ($bootstrap_arrows && $bootstrap_version == 3) : ?>

    	<?php echo $prefix; ?> .items_pagination.left .pagination > li:first-child > a,
        <?php echo $prefix; ?> .items_pagination.left .pagination > li:first-child > span,
        <?php echo $prefix; ?> .items_pagination.up .pagination > li:first-child > a,
        <?php echo $prefix; ?> .items_pagination.up .pagination > li:first-child > span {
        	-webkit-border-top-right-radius: 4px;
        	-moz-border-radius-topright: 4px;
        	border-top-right-radius: 4px;
        	-webkit-border-bottom-right-radius: 4px;
        	-moz-border-radius-bottomright: 4px;
        	border-bottom-right-radius: 4px;
        }

        <?php echo $prefix; ?> .items_pagination.left .pagination-sm > li:first-child > a,
        <?php echo $prefix; ?> .items_pagination.left .pagination-sm > li:first-child > span,
        <?php echo $prefix; ?> .items_pagination.up .pagination-sm > li:first-child > a,
        <?php echo $prefix; ?> .items_pagination.up .pagination-sm > li:first-child > span {
        	-webkit-border-top-right-radius: 3px;
        	-moz-border-radius-topright: 3px;
        	border-top-right-radius: 3px;
        	-webkit-border-bottom-right-radius: 3px;
        	-moz-border-radius-bottomright: 3px;
        	border-bottom-right-radius: 3px;
        }

    	<?php echo $prefix; ?> .items_pagination.right .pagination > li:last-child > a,
    	<?php echo $prefix; ?> .items_pagination.right .pagination > li:last-child > span,
    	<?php echo $prefix; ?> .items_pagination.down .pagination > li:last-child > a,
    	<?php echo $prefix; ?> .items_pagination.down .pagination > li:last-child > span {
    		-webkit-border-top-left-radius: 4px;
        	-moz-border-radius-topleft: 4px;
        	border-top-left-radius: 4px;
        	-webkit-border-bottom-left-radius: 4px;
        	-moz-border-radius-bottomleft: 4px;
        	border-bottom-left-radius: 4px;
    	}

    	<?php echo $prefix; ?> .items_pagination.right .pagination-sm > li:last-child > a,
    	<?php echo $prefix; ?> .items_pagination.right .pagination-sm > li:last-child > span,
    	<?php echo $prefix; ?> .items_pagination.down .pagination-sm > li:last-child > a,
    	<?php echo $prefix; ?> .items_pagination.down .pagination-sm > li:last-child > span {
    		-webkit-border-top-left-radius: 3px;
        	-moz-border-radius-topleft: 3px;
        	border-top-left-radius: 3px;
        	-webkit-border-bottom-left-radius: 3px;
        	-moz-border-radius-bottomleft: 3px;
        	border-bottom-left-radius: 3px;
    	}
    <?php endif; ?>

    /* extra bootstrap 4 and 5 styles for 'around' positions */
    <?php if ($bootstrap_arrows && $bootstrap_version >= 4) : ?>

        <?php echo $prefix; ?> .items_pagination.left .page-item:first-child .page-link,
        <?php echo $prefix; ?> .items_pagination.up .page-item:first-child .page-link {
        	-webkit-border-top-right-radius: .25rem;
        	-moz-border-radius-topright: .25rem;
        	border-top-right-radius: .25rem;
        	-webkit-border-bottom-right-radius: .25rem;
        	-moz-border-radius-bottomright: .25rem;
        	border-bottom-right-radius: .25rem;
        }

        <?php echo $prefix; ?> .items_pagination.left .pagination-sm .page-item:first-child .page-link,
        <?php echo $prefix; ?> .items_pagination.up .pagination-sm .page-item:first-child .page-link {
        	-webkit-border-top-right-radius: .2rem;
        	-moz-border-radius-topright: .2rem;
        	border-top-right-radius: .2rem;
        	-webkit-border-bottom-right-radius: .2rem;
        	-moz-border-radius-bottomright: .2rem;
        	border-bottom-right-radius: .2rem;
        }

        <?php echo $prefix; ?> .items_pagination.right .page-item:last-child .page-link,
        <?php echo $prefix; ?> .items_pagination.down .page-item:last-child .page-link {
        	-webkit-border-top-left-radius: .25rem;
        	-moz-border-radius-topleft: .25rem;
        	border-top-left-radius: .25rem;
        	-webkit-border-bottom-left-radius: .25rem;
        	-moz-border-radius-bottomleft: .25rem;
        	border-bottom-left-radius: .25rem;
        }

        <?php echo $prefix; ?> .items_pagination.right .pagination-sm .page-item:last-child .page-link,
        <?php echo $prefix; ?> .items_pagination.down .pagination-sm .page-item:last-child .page-link {
        	-webkit-border-top-left-radius: .2rem;
        	-moz-border-radius-topleft: .2rem;
        	border-top-left-radius: .2rem;
        	-webkit-border-bottom-left-radius: .2rem;
        	-moz-border-radius-bottomleft: .2rem;
        	border-bottom-left-radius: .2rem;
        }
    <?php endif; ?>
<?php endif; ?>