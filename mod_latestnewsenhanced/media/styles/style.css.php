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

	<?php echo $suffix; ?> ul.latestnews-items {
	    <?php if ($items_height) : ?>
	    	height: <?php echo $items_height; ?>px;
	    	overflow-y: auto;
	    <?php endif; ?>
	    <?php if ($items_width) : ?>
	    	<?php if ($item_width_unit == 'px') : ?>
	    		min-width: <?php echo $item_width; ?>px;
	    	<?php endif; ?>
	    	max-width: <?php echo $items_width; ?>px;
	    	margin-left: auto;
	    	margin-right: auto;
	    <?php endif; ?>
	    
	    display: -webkit-box;
		display: -ms-flexbox;
		display: flex;

		-ms-flex-wrap: wrap;
		flex-wrap: wrap;

		<?php if ($items_align == 'fs') : ?>
	    	-webkit-box-pack: start;
			-webkit-justify-content: flex-start;
	    	-ms-flex-pack: start;
	        justify-content: flex-start;
	    <?php elseif ($items_align == 'fe') : ?>
	    	-webkit-box-pack: end;
			-webkit-justify-content: flex-end;
	    	-ms-flex-pack: end;
	        justify-content: flex-end;
	    <?php elseif ($items_align == 'c') : ?>
	    	-webkit-box-pack: center;
			-webkit-justify-content: center;
	    	-ms-flex-pack: center;
	        justify-content: center;
	    <?php elseif ($items_align == 'sb') : ?>
	    	-webkit-box-pack: justify;
	    	-webkit-justify-content: space-between;
	        -ms-flex-pack: justify;
	        justify-content: space-between;
	    <?php elseif ($items_align == 'se') : ?>
	    	-webkit-box-pack: space-evenly;
			-webkit-justify-content: space-evenly;
			-ms-flex-pack: space-evenly;
	        justify-content: space-evenly;
	    <?php else : ?>
	    	-webkit-justify-content: space-around;
			-ms-flex-pack: distribute;
	        justify-content: space-around;
	    <?php endif; ?>

		<?php if (!$horizontal) : ?>
			-webkit-flex-direction: column;
			-ms-flex-direction: column;
			flex-direction: column;

			-ms-flex-line-pack: center;
   			align-content: center;
		<?php endif; ?>
	}

	<?php echo $suffix; ?> ul.latestnews-items li.latestnews-item {
		<?php if ($font_ref_body > 0) : ?>
			font-size: <?php echo $font_ref_body; ?>px;
		<?php else : ?>
			font-size: medium;
		<?php endif; ?>
		
		-webkit-box-flex: 1;
		-ms-flex: 1 1 auto;
		flex: 1 1 auto;
			
    	width: <?php echo $item_width; ?><?php echo $item_width_unit; ?>;
    	
    	<?php if ($item_min_width) : ?>
			min-width: <?php echo $item_min_width; ?>px;
		<?php endif; ?>
		<?php if ($item_max_width) : ?>
			max-width: <?php echo $item_max_width; ?>px;
		<?php endif; ?>
		
    	<?php if ($item_width_unit == '%') : ?>
    		margin: <?php echo intval($space_between_items / 2); ?>px <?php echo $margin_in_perc; ?>%;
    	<?php else : ?>
    		margin: <?php echo intval($space_between_items / 2); ?>px;
    	<?php endif; ?>
	}

		<?php echo $suffix; ?> .news {
			<?php if ($item_width_unit == '%') : ?>
				width: 100%;
			<?php else : ?>
				width: <?php echo $item_width; ?>px;
			<?php endif; ?>
		}

			<?php if ($bgcolor_body && $bgcolor_body != 'transparent') : ?>
				<?php echo $suffix; ?> .innernews {
					background-color: <?php echo $bgcolor_body; ?>;
				}
			<?php endif; ?>

				<?php if ($image) : ?>

					<?php echo $suffix; ?> .newshead .picture,
					<?php echo $suffix; ?> .newshead .nopicture {
						<?php if ($head_width > 0) : ?>
							max-width: <?php echo $head_width; ?>px;
						<?php endif; ?>
						<?php if ($head_height > 0) : ?>
							max-height: <?php echo $head_height; ?>px;
						<?php endif; ?>
						<?php if ($maintain_height && $head_height > 0) : ?>
							height: <?php echo $head_height; ?>px;
							min-height: <?php echo $head_height; ?>px;
						<?php endif; ?>

						<?php if ($maintain_height) : ?>
							display: table-cell;
							vertical-align: middle;
						<?php endif; ?>

						background-color: <?php echo $bgcolor; ?>;
					}

					<?php echo $suffix; ?> .newshead .nopicture > span {
						<?php if ($head_width > 0) : ?>
							width: <?php echo $head_width; ?>px;
						<?php endif; ?>
						<?php if ($head_height > 0) : ?>
							height: <?php echo $head_height; ?>px;
						<?php endif; ?>
					}

				<?php endif; ?>

			<?php if ($calendar) : ?>

				<?php echo $suffix; ?> .newshead.calendartype {
					font-size: <?php echo $font_ref_cal; ?>px; /* the base size for the calendar */
				}

					<?php echo $suffix; ?> .newshead .nocalendar {
						width: <?php echo $head_width; ?>px;
						max-width: <?php echo $head_width; ?>px;
						height: <?php echo $head_height; ?>px;
						min-height: <?php echo $head_height; ?>px;
					}

					<?php echo $suffix; ?> .newshead .calendar {
						width: <?php echo $head_width; ?>px;
						max-width: <?php echo $head_width; ?>px;
					}

					<?php echo $suffix; ?> .newshead .calendar.image {
						height: <?php echo $head_height; ?>px;
					}

			<?php endif; ?>

					<?php if ($force_title_one_line) : ?>
						<?php echo $suffix; ?> .newstitle span {
			    			display: block;
			    			white-space: nowrap;
			    			text-overflow: ellipsis;
			    			overflow: hidden;
			    			line-height: initial;
						}
					<?php endif; ?>

						<?php echo $suffix; ?> .newsextra {
							font-size: <?php echo ($font_size_details / 100); ?>em;
							<?php if ($details_line_spacing[0]) : ?>
								line-height: <?php echo $details_line_spacing[0]; ?><?php echo $details_line_spacing[1]; ?>;
							<?php endif; ?>
							<?php if ($details_font_color) : ?>
								color: <?php echo $details_font_color; ?>;
							<?php endif; ?>
						}

						<?php if ($iconfont_color) : ?>
							<?php echo $suffix; ?> .newsextra [class^="SYWicon-"],
							<?php echo $suffix; ?> .newsextra [class*=" SYWicon-"] {
						    	color: <?php echo $iconfont_color; ?>;
							}
						<?php endif; ?>

						<?php echo $suffix; ?> .newsextra .detail_rating .detail_data [class*=" SYWicon-"],
						<?php echo $suffix; ?> .newsextra .detail_rating .detail_data [class^="SYWicon-"] {
							color: <?php echo $star_color; ?>;
						}
