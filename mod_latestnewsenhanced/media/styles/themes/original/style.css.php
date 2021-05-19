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

<?php echo $suffix; ?> ul.latestnews-items li.latestnews-item.active {
	opacity: 0.5;
}

		<?php echo $suffix; ?> .head_left .newshead {
			float: left;
			margin: 0 8px 0 0;
		}

		<?php echo $suffix; ?> .head_right .newshead {
			float: right;
			margin: 0 0 0 8px;
		}

			<?php if ($head_align) : ?>	
				<?php echo $suffix; ?> .text_top .newshead > div,
				<?php echo $suffix; ?> .text_bottom .newshead > div {
					<?php if ($head_align == 'left') : ?>
						margin: 0 auto 0 0;
					<?php elseif ($head_align == 'right') : ?>
						margin: 0 0 0 auto;
					<?php elseif ($head_align == 'center') : ?>
						margin: 0 auto;
					<?php endif; ?>
				}
			<?php endif; ?>	

		<?php echo $suffix; ?> .head_left .newsinfooverhead,
		<?php echo $suffix; ?> .head_right .newsinfooverhead,
		<?php echo $suffix; ?> .text_top .newsinfooverhead {
			display: none;
		}

		<?php if (!$wrap) : ?>
			<?php echo $suffix; ?> .newsinfo {
				overflow: hidden;
			}

			<?php echo $suffix; ?> .head_left .newsinfo.noimagespace {
				margin-left: 0 !important;
			}

			<?php echo $suffix; ?> .head_right .newsinfo.noimagespace {
				margin-right: 0 !important;
			}
		<?php endif; ?>

			<?php echo $suffix; ?> .newstitle {
				font-weight: bold;
			}

<?php if ($image) : ?>

	<?php echo $suffix; ?> .newshead.picturetype {
		position: relative;
		max-width: 100%;
	}

	<?php if ($pic_border_width > 0 || $pic_border_radius > 0) : ?>
		<?php echo $suffix; ?> .newshead .picture,
		<?php echo $suffix; ?> .newshead .nopicture {
			<?php if ($pic_border_width > 0) : ?>
    			border: <?php echo $pic_border_width ?>px solid <?php echo $pic_border_color ?>;

    			-webkit-box-sizing: border-box;
    			-moz-box-sizing: border-box;
    			box-sizing: border-box;
			<?php endif; ?>

    		<?php if ($pic_border_radius > 0) : ?>
    			border-radius: <?php echo $pic_border_radius; ?>px;
    			-moz-border-radius: <?php echo $pic_border_radius; ?>px;
    			-webkit-border-radius: <?php echo $pic_border_radius; ?>px;
    		<?php endif; ?>
		}
	<?php endif; ?>

	<?php echo $suffix; ?> .newshead .picture img {
		display: inherit;
	}

	<?php if (intval($pic_shadow_width) > 0) : ?>
		<?php echo $suffix; ?> .shadow.simple .picturetype {
			padding: <?php echo (intval($pic_shadow_width) + 2) ?>px;

			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
		}

		<?php echo $suffix; ?> .shadow.simple .picture,
		<?php echo $suffix; ?> .shadow.simple .nopicture {
			-moz-box-shadow: 0 0 <?php echo $pic_shadow_width; ?>px rgba(0, 0, 0, 0.8);
			-webkit-box-shadow: 0 0 <?php echo $pic_shadow_width; ?>px rgba(0, 0, 0, 0.8);
			box-shadow: 0 0 <?php echo $pic_shadow_width; ?>px rgba(0, 0, 0, 0.8);
		}
	<?php endif; ?>
<?php endif; ?>
