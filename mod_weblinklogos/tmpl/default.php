<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use SYW\Library\Libraries as SYWLibraries;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Library\Version as SYWVersion;
use SYW\Module\WeblinkLogos\Site\Helper\Helper;

$modal_needed = false;

if ($remove_whitespaces) {
	ob_start(function($buffer) { return preg_replace('/\s+/', ' ', $buffer); });
}
?>
<div id="weblinklogo_<?php echo $class_suffix; ?>" class="weblinklogos<?php echo $arrow_class; ?><?php echo $isMobile ? ' mobile' : ''; ?>">

	<?php if ($show_errors && !empty($general_errors)) : ?>
		<?php foreach ($general_errors as $error) : ?>
			<div class="<?php echo SYWUtilities::getBootstrapProperty('alert alert-'.$error[1], $bootstrap_version); ?>">
    			<?php echo $error[0]; ?>
			</div>
    	<?php endforeach; ?>
	<?php endif; ?>

	<?php if (trim($params->get('pretext', ''))) : ?>
		<div class="pretext">
			<?php
				if ($params->get('allow_plugins', 0)) {
					echo HTMLHelper::_('content.prepare', $params->get('pretext'));
				} else {
					echo $params->get('pretext');
				}
			?>
		</div>
	<?php endif; ?>

	<?php if ($show_arrows && ($arrow_prev_left || $arrow_prev_top)) : ?>
		<div class="items_pagination top<?php echo $extra_pagination_classes; ?>">
			<ul<?php echo $extra_pagination_ul_class_attribute; ?>>
			<?php if ($arrow_prev_left) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="prev_<?php echo $class_suffix; ?>" class="previous<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JPREV'); ?>" onclick="return false;"><span class="SYWicon-arrow-left2" aria-hidden="true"></span></a></li>
			<?php endif; ?>
			<?php if ($arrow_prev_top) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="prev_<?php echo $class_suffix; ?>" class="previous<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JPREV'); ?>" onclick="return false;"><span class="SYWicon-arrow-up2" aria-hidden="true"></span></a></li>
			<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>

	<ul class="weblink_items <?php echo $configuration; ?>">
		<?php foreach ($list as $item) : ?>
			<?php
				$imagetag = '';
				$identical = false;

				if (empty($item->image_first)) {
					$item->error[] = Text::sprintf('MOD_WEBLINKLOGO_ERROR_IMAGEFILEDOESNOTEXIST', '');
				} else {
				    $result_array = Helper::getThumbnailPath($unique_filename_extra, $item->id, $item->image_first, $tmp_path, $clear_cache, $width, $height, false, $image_qualities, $filter, $create_highres_images, $allow_remote, $thumbnail_mime_type);

					if (!empty($result_array[1])) {
						$item->error[] = $result_array[1];
					}

					if (!empty($result_array[0])) {

						$attributes = array('class' => 'original');

						$imagetag .= SYWUtilities::getImageElement($result_array[0], $item->alt_first, $attributes, ($carousel_configuration != 'none') ? false : true, $create_highres_images, null, true, SYWVersion::getMediaVersion('mod_weblinklogos_' . $module->id));
					}

					if (!empty($item->image_second)) {
					    $additional_suffix = '';
					    if ($filter === $filter_hover) {
					        $additional_suffix = '_hover';
					    }
					    $result_array_hover = Helper::getThumbnailPath($unique_filename_extra, $item->id.$additional_suffix, $item->image_second, $tmp_path, $clear_cache, $width, $height, false, $image_qualities, $filter_hover, $create_highres_images, $allow_remote, $thumbnail_mime_type);
					} else {
						if ($filter === $filter_hover) { // thumbs will be identical
							$result_array_hover = $result_array;
							$identical = true;
						} else {
						    $result_array_hover = Helper::getThumbnailPath($unique_filename_extra, $item->id, $item->image_first, $tmp_path, $clear_cache, $width, $height, false, $image_qualities, $filter_hover, $create_highres_images, $allow_remote, $thumbnail_mime_type);
						}
					}

					if (!empty($result_array_hover[1]) && !$identical) {
						$item->error[] = $result_array_hover[1];
					}

					if (!empty($result_array_hover[0])) {

						$attributes = array('class' => 'hover');

						if ($params->get('logo_tooltip', 1)) {
							if ($bootstrap_version > 0) {
							    HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
								$attributes['class'] .= ' hasTooltip';
							}
							$attributes['title'] = htmlspecialchars($item->title);
						}

						$imagetag .= SYWUtilities::getImageElement($result_array_hover[0], $item->alt_first, $attributes, ($carousel_configuration != 'none') ? false : true, $create_highres_images, null, true, SYWVersion::getMediaVersion('mod_weblinklogos_' . $module->id));
					}
				}
			?>
			<li class="weblink_item weblink_id_<?php echo $item->id; ?> weblink_catid_<?php echo $item->catid; ?>">

				<?php if ($show_errors && !empty($item->error)) : ?>
					<div class="<?php echo SYWUtilities::getBootstrapProperty('alert alert-error', $bootstrap_version); ?>">
						<span><?php echo 'id '.$item->id.':'; ?></span>
            			<ul>
						<?php foreach ($item->error as $error) : ?>
	  						<li><?php echo $error; ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
				<?php else : ?>
					<?php if ($carousel_configuration != 'none') : ?><div class="shell_animate"><?php endif; ?>
					<div class="weblink_item_wrapper">
						<div class="logo">
							<div class="logo_link<?php echo ($identical && $hover_type == 'smooth') ? '' : ' '.$hover_type; ?>">
								<?php
									switch ($item->target)
									{
										case 1:	// open in a new window
											echo '<a href="'. $item->link .'" target="_blank" rel="'.$params->get('follow', 'nofollow').'">'.$imagetag.'</a>';
											break;
										case 2: // open in a popup window
										    echo '<a href="#" onclick="window.open(\''. $item->link .'\', \'\', \'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width='.$popup_width.',height='.$popup_height.'\'); return false">'.$imagetag.'</a>';
											break;
										case 3: // open in a modal window
										    $modal_needed = true;
										    $link_attributes = ' onclick="return false;" data-modaltitle="'.htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8').'"';
										    if ($bootstrap_version > 0) {
										    	$link_attributes .= ' data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'toggle="modal" data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'target="#wlpmodal_'.$module->id.'"';
										    }
										    echo '<a href="'.$item->link.'" class="wlpmodal_'.$module->id.'"' . $link_attributes . '>'.$imagetag.'</a>';
											break;
										default: // open in parent window
											echo '<a href="'. $item->link .'" rel="'.$params->get('follow', 'nofollow').'">'.$imagetag.'</a>';
									}
								?>
							</div>
							<?php if ($params->get('caption', 0)) : ?>
								<div class="logo_caption<?php echo $caption_classes; ?>"><?php echo htmlspecialchars($item->caption_first, ENT_COMPAT, 'UTF-8'); ?></div>
							<?php endif; ?>
						</div>
						<?php if (($params->get('description', 0) && $item->description) || $params->get('title', 0) || $params->get('hits', 0)) : ?>
							<div class="description">
								<?php if ($params->get('title', 0)) : ?>
									<h<?php echo $title_html_tag; ?> class="title">
									<?php if ($params->get('link_title', 0)) : ?>
										<?php
        									switch ($item->target)
        									{
        										case 1:	// open in a new window
        										    echo '<a href="'. $item->link .'" target="_blank" rel="'.$params->get('follow', 'nofollow').'">'.$item->title.'</a>';
        											break;
        										case 2: // open in a popup window
        										    echo '<a href="#" onclick="window.open(\''. $item->link .'\', \'\', \'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width='.$popup_width.',height='.$popup_height.'\'); return false">'.$item->title.'</a>';
        											break;
        										case 3: // open in a modal window
        										    $modal_needed = true;
        										    $link_attributes = ' onclick="return false;" data-modaltitle="'.htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8').'"';
        										    if ($bootstrap_version > 0) {
        										    	$link_attributes .= ' data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'toggle="modal" data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'target="#wlpmodal_'.$module->id.'"';
        										    }
        										    echo '<a href="'.$item->link.'" class="wlpmodal_'.$module->id.'"' . $link_attributes . '>'.$item->title.'</a>';
        											break;
        										default: // open in parent window
        										    echo '<a href="'. $item->link .'" rel="'.$params->get('follow', 'nofollow').'">'.$item->title.'</a>';
        									}
        								?>
									<?php else : ?>
										<?php echo $item->title; ?>
									<?php endif; ?>
									</h<?php echo $title_html_tag; ?>>
								<?php endif; ?>
								<?php if ($params->get('hits', 0)) : ?>
									<span class="hits<?php echo $hits_classes; ?>"><?php echo Text::sprintf('MOD_WEBLINKLOGO_HITS', $item->hits); ?></span>
								<?php endif; ?>
								<?php if (strlen($item->description) > 0) : ?>
									<?php if ($params->get('separator', '')) : ?><span><?php echo $params->get('separator', ''); ?></span><?php endif; ?>
									<?php if ($description_html_tag) : ?>
										<<?php echo $description_html_tag; ?> class="text"><?php echo $item->description; ?></<?php echo $description_html_tag; ?>>
									<?php else : ?>
										<?php echo $item->description; ?>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php if ($carousel_configuration != 'none') : ?></div><?php endif; ?>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ($show_arrows && ($arrow_prevnext_bottom || $arrow_next_right || $arrow_next_bottom)) : ?>
		<div class="items_pagination bottom<?php echo $extra_pagination_classes; ?>">
			<ul<?php echo $extra_pagination_ul_class_attribute; ?>>
			<?php if ($arrow_prevnext_bottom) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="prev_<?php echo $class_suffix; ?>" class="previous<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JPREV'); ?>" onclick="return false;"><span class="<?php echo ($carousel_configuration == 'h' ? 'SYWicon-arrow-left2' : 'SYWicon-arrow-up2') ?>" aria-hidden="true"></span></a></li><!--
				 --><li<?php echo $extra_pagination_li_class_attribute; ?>><a id="next_<?php echo $class_suffix; ?>" class="next<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JNEXT'); ?>" onclick="return false;"><span class="<?php echo ($carousel_configuration == 'h' ? 'SYWicon-arrow-right2' : 'SYWicon-arrow-down2') ?>" aria-hidden="true"></span></a></li>
			<?php endif; ?>
			<?php if ($arrow_next_right) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="next_<?php echo $class_suffix; ?>" class="next<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JNEXT'); ?>" onclick="return false;"><span class="SYWicon-arrow-right2" aria-hidden="true"></span></a></li>
			<?php endif; ?>
			<?php if ($arrow_next_bottom) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="next_<?php echo $class_suffix; ?>" class="next<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JNEXT'); ?>" onclick="return false;"><span class="SYWicon-arrow-down2" aria-hidden="true"></span></a></li>
			<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if (trim($params->get('footnote', ''))) : ?>
		<span class="weblinks_footnote"><?php echo $params->get('footnote', ''); ?></span>
	<?php endif; ?>

	<?php if (trim($params->get('posttext', ''))) : ?>
		<div class="posttext">
			<?php
				if ($params->get('allow_plugins', 0)) {
					echo HTMLHelper::_('content.prepare', $params->get('posttext'));
				} else {
					echo $params->get('posttext');
				}
			?>
		</div>
	<?php endif; ?>
</div>
<?php
    if ($modal_needed) {
    	if ($bootstrap_version == 0) {
    		SYWLibraries::loadPureModal($load_remotely);
    	}

    	$layout = new FileLayout('wlpmodal', JPATH_ROOT.'/modules/mod_weblinklogo/layouts'); // no overrides possible

        $data = array('selector' => 'wlpmodal_'.$module->id, 'width' => $popup_width, 'height' => $popup_height);
    	$data['bootstrap_version'] = $bootstrap_version;
    	$data['load_bootstrap'] = $load_bootstrap;

    	echo $layout->render($data);
    }
?>
<?php if ($remove_whitespaces) : ?>
	<?php ob_get_flush(); ?>
<?php endif; ?>