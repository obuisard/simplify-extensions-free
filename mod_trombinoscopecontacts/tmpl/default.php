<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper as ContactRouteHelper;
use Joomla\Component\Tags\Site\Helper\RouteHelper as TagsRouteHelper;
use Joomla\Registry\Registry;
use SYW\Library\Libraries as SYWLIbraries;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Library\Version as SYWVersion;
use SYW\Module\TrombinoscopeContacts\Site\Helper\Helper;

if ($load_bootstrap) {
    HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
}

$i_header = 0;
if ($show_heading) {
	$previous_header = '';
	$header = '';
	$previous_header_alpha = '';
	$header_alpha = '';
}

$modal_needed = false;

if ($remove_whitespaces) {
	ob_start(function($buffer) { return preg_replace('/\s+/', ' ', $buffer); });
}
?>
<div id="te_<?php echo $class_suffix; ?>" class="te te_<?php echo $class_suffix; ?><?php echo $arrow_class; ?><?php echo $isMobile ? ' mobile' : ''; ?>">

	<?php if (trim($params->get('pretext', ''))) : ?>
		<div class="pretext">
			<?php
				if ($params->get('allow_plugins_prepost', 0)) {
					echo HTMLHelper::_('content.prepare', $params->get('pretext'));
				} else {
					echo $params->get('pretext');
				}
			?>
		</div>
	<?php endif; ?>

	<?php if (empty($cat_order) && $show_category_header) : ?>
		<div class="alert <?php echo SYWUtilities::getBootstrapProperty('alert-warning', $bootstrap_version); ?>">
		  	<?php echo Text::_('MOD_TROMBINOSCOPE_MESSAGE_ORDERFORCATEGORYHEADER'); ?>
		</div>
	<?php endif; ?>
	
	<?php if (in_array($order, array('oa', 'od', 'random', 'manual', 'c_asc', 'c_dsc', 'mc_asc', 'mc_dsc', 'hit')) && $show_alphabet_header) : ?>
		<div class="alert <?php echo SYWUtilities::getBootstrapProperty('alert-warning', $bootstrap_version); ?>">
		  	<?php echo Text::_('MOD_TROMBINOSCOPE_MESSAGE_ORDERFORALPHAHEADER'); ?>
		</div>
	<?php endif; ?>

	<?php if ($show_arrows && ($arrow_prev_left || $arrow_prev_top)) : ?>
		<div class="items_pagination top<?php echo $extra_pagination_classes; ?>">
			<ul<?php echo $extra_pagination_ul_class_attribute; ?>>
			<?php if ($arrow_prev_left) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="prev_<?php echo $class_suffix; ?>" class="previous<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JPREV'); ?>" onclick="return false;"><i class="SYWicon-arrow-left2" aria-hidden="true"></i></a></li>
			<?php endif; ?>
			<?php if ($arrow_prev_top) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="prev_<?php echo $class_suffix; ?>" class="previous<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JPREV'); ?>" onclick="return false;"><i class="SYWicon-arrow-up2" aria-hidden="true"></i></a></li>
			<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>

	<div class="personlist">

		<?php foreach ($list as $i => $item) : ?>

			<?php
				// header
				if ($show_category_header) {
			    	$header = $item->category;
			    	if ($previous_header == $header) {
			        	$header = '';
			    	} else {
			        	$previous_header = $header;
			        	$previous_header_alpha = '';
			        	$header_alpha = '';
			        	$i_header = 0;
			    	}
				}
			
				// alpha header
				if ($show_alphabet_header) {
			    	switch ($order) {
			        	case 'na' : case 'nd' : $header_alpha = ucfirst($item->name[0]); break;
			        	case 'fnf_fa' : case 'fnf_fd' : $header_alpha = ucfirst($item->firstpart[0]); break;
			        	case 'fnf_la' : case 'fnf_ld' : $header_alpha = ucfirst($item->lastpart[0]); break;
			        	case 'sna' : case 'snd' :
			            	if (empty($item->sortname1)) { // all unsorted contacts appear first
			                	$item->error[] = $item->name . ': ' . Text::_('MOD_TROMBINOSCOPE_ERROR_MISSINGFIRSTSORTFIELD');
			            	} else {
			                	$header_alpha = ucfirst($item->sortname1[0]);
			            	}
			            	break;
			        	default : $header_alpha = ''; break;
			    	}
			    	if ($previous_header_alpha == $header_alpha) {
			        	$header_alpha = '';
			    	} else {
			        	$previous_header_alpha = $header_alpha;
			        	$i_header = 0;
			    	}
				}

				// Convert parameter fields to objects.
				$itemparams = new Registry();
				$itemparams->loadString($item->params);

				// name format
				$formatted_name = Helper::getFormattedName($item->name, $format_style, $uppercase);

				// link
				$link = '';
				$link_attributes = '';
				$link_classes = '';
				if (in_array($link_access, $groups)) {
				    switch ($contact_link) {
				        case 'standard' :
				        	$link = Route::_(ContactRouteHelper::getContactRoute($item->slug, $item->catid, $item->language));
				            break;
				        case 'popup' :
				        	$link = Route::_(ContactRouteHelper::getContactRoute($item->slug, $item->catid, $item->language) . '&tmpl=component');
				            $link_attributes = ' onclick="return false;" data-modaltitle="'.htmlspecialchars($formatted_name, ENT_COMPAT, 'UTF-8').'"';
				            if ($bootstrap_version > 0) {
				            	$link_attributes .= ' data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'toggle="modal" data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'target="#tcpmodal_'.$module->id.'"';
				            }
				            $link_classes = 'tcpmodal_'.$module->id;
				            $modal_needed = true;
				            break;
				        case 'linka' : $link_attributes = ' target="_blank"'; case 'linka_sw' : $link = $itemparams->get('linka'); break;
				        case 'linkb' : $link_attributes = ' target="_blank"'; case 'linkb_sw' : $link = $itemparams->get('linkb');	break;
				        case 'linkc' : $link_attributes = ' target="_blank"'; case 'linkc_sw' : $link = $itemparams->get('linkc');	break;
				        case 'linkd' : $link_attributes = ' target="_blank"'; case 'linkd_sw' : $link = $itemparams->get('linkd');	break;
				        case 'linke' : $link_attributes = ' target="_blank"'; case 'linke_sw' : $link = $itemparams->get('linke');	break;
				        case 'generic' : $link_attributes = ' target="_blank"'; case 'generic_sw' : $link = $generic_link; break;
				        default: $link = '';
				    }
				}

				// category links
				$link_category = '';
				$heading_link = '';

				if ($link_to_category) {
					$link_category = Route::_(ContactRouteHelper::getCategoryRoute($item->catid, $item->language));
				}

				if ($link_to_category_header) {
					$heading_link = Route::_(ContactRouteHelper::getCategoryRoute($item->catid, $item->language));
				}

				$extraclasses = " personid-".$item->id." catid-".$item->catid;

				if (isset($item->tags)) {
					foreach ($item->tags as $tag) {
						$extraclasses .= " tag-".$tag->id;
					}
				}

				$extraclasses .= ($i_header % 2) ? " even" : " odd";

				if ($item->featured && $show_featured) {
					$extraclasses .= " featured";
				}

				if ($style_social_icons) {
					$extraclasses .= " social";
				}

				if (!$show_picture) {
					$extraclasses .= " text_only";
				} else {
					$extraclasses .= " ";
					if (!$keep_picture_space && empty($item->image)) {
						$extraclasses .= "text_only ghost_";
					}
					switch ($photo_align) {
						case 'l': $extraclasses .= "picture_left"; break;
						case 'r': $extraclasses .= "picture_right"; break;
						case 't': $extraclasses .= "picture_top"; break;
						case 'lr': $extraclasses .= ($i_header % 2) ? "picture_right" : "picture_left"; break;
						case 'rl': $extraclasses .= ($i_header % 2) ? "picture_left" : "picture_right"; break;
						default : $extraclasses .= "picture_left";
					}
				}
				
				$i_header++;
			?>

			<?php if ($show_category_header) : ?>
    			<?php if ($header || $header_alpha) : ?>
    				<div class="heading-group">
    					<?php if ($header) : ?>
        					<?php echo '<h' . $header_html_tag . ' class="heading">'; ?>
        						<?php if ($heading_link) : ?>
        							<a href="<?php echo $heading_link; ?>">
        								<span><?php echo $header ?></span>
        							</a>
        						<?php else : ?>
        							<span><?php echo $header ?></span>
        						<?php endif; ?>
        					<?php echo '</h' . $header_html_tag . '>'; ?>
        				<?php endif; ?>
        				<?php if ($show_alphabet_header) : ?>
        					<?php if ($header_alpha) : ?>
        						<?php echo '<h' . $subheader_html_tag . ' class="sub-heading">'; ?>
        							<span><?php echo $header_alpha ?></span>
        						<?php echo '</h' . $subheader_html_tag . '>'; ?>
        					<?php endif; ?>
        				<?php endif; ?>
    				</div>
    			<?php endif; ?>
    		<?php elseif ($show_alphabet_header) : ?>
    			<?php if ($header_alpha) : ?>
    				<div class="heading-group">
    					<?php echo '<h' . $header_html_tag . ' class="heading">'; ?>
    						<span><?php echo $header_alpha ?></span>
    					<?php echo '</h' . $header_html_tag . '>'; ?>
    				</div>
    			<?php endif; ?>
    		<?php endif; ?>

			<div class="person<?php echo $extraclasses ?>">
				<?php if ($carousel_configuration != 'none') : ?><div class="shell_animate"><?php endif; ?>
				<div class="shell">
    				<div class="outerperson">

    					<?php if ($show_errors && !empty($item->error)) : ?>
    						<div class="alert <?php echo SYWUtilities::getBootstrapProperty('alert-error', $bootstrap_version); ?>">
                    			<ul>
                    			<?php foreach ($item->error as $error) : ?>
                    		  		<li><?php echo $error; ?></li>
    							<?php endforeach; ?>
    							</ul>
                    		</div>
    					<?php endif; ?>

    					<?php if ($requested_links) : ?>
    						<?php $links_output = ''; ?>
    						<?php foreach ($requested_links as $index => $requested_link) : ?>
    							<?php $links_output .= Helper::renderLink($params, $item, $index, $requested_link, $extraclasseslinkfields); ?>
    						<?php endforeach; ?>
    						<?php if ($links_output) : ?>
        						<div class="iconlinks">
        							<ul><?php echo $links_output; ?></ul>
        						</div>
    						<?php endif; ?>
    					<?php endif; ?>

    					<?php if ($show_vcard) : ?>
    						<div class="vcard">
    							<a href="<?php echo Route::_(ContactRouteHelper::getContactRoute($item->slug, $item->catid, $item->language) . '&format=vcf'); ?>" class="hasTooltip<?php echo $extraclasseslinkfields ? ' ' . $extraclasseslinkfields : ''; ?>" aria-label="<?php echo Text::_('MOD_TROMBINOSCOPE_VCARD');?>" title="<?php echo Text::_('MOD_TROMBINOSCOPE_DOWNLOAD_VCARD');?>">
    								<i class="icon <?php echo $vcard_icon; ?>" aria-hidden="true"></i><span><?php echo Text::_('MOD_TROMBINOSCOPE_VCARD'); ?></span>
    							</a>
    						</div>
    					<?php endif; ?>

    					<div class="innerperson">
    						<div class="personpicture">

    							<?php if (isset($item->individual_bg) && $item->individual_bg) : ?>
    								<div class="individualbg">
    									<div class="innerindividualbg">
    										<?php echo SYWUtilities::getImageElement($item->individual_bg, $item->individual_bg_alt, null, ($carousel_configuration != 'none') ? false : true, $create_highres_images, null, true, SYWVersion::getMediaVersion('mod_trombinoscope_' . $module->id)); ?>
    									</div>
    								</div>
    							<?php endif; ?>

    							<?php if ($picture_hover_type != 'none' && $link_picture && $link) : ?>
    								<div class="picture_animate <?php echo $picture_hover_type; ?>">
    							<?php endif; ?>

    							<div class="picture">

    								<div class="picture_veil">
    									<?php if ($item->featured && $show_featured) : ?>
    										<div class="feature">
    											<i class="icon <?php echo $featured_icon; ?>" aria-hidden="true"></i>
    										</div>
    									<?php endif; ?>
    								</div>
    								<?php if ($link_picture && $link) : ?>
    									<a href="<?php echo $link; ?>"<?php echo $link_attributes; ?><?php echo Helper::getTitleAttribute($formatted_name, $picture_tooltip) ?><?php echo Helper::getClassAttribute($picture_tooltip, $link_classes); ?>>
    								<?php endif; ?>
    								<?php if ($item->image) : ?>
    									<?php echo SYWUtilities::getImageElement($item->image, $formatted_name, $crop_picture ? array('width' => $picture_width, 'height' => $picture_height) : array(), ($carousel_configuration != 'none') ? false : true, $create_highres_images, null, true, SYWVersion::getMediaVersion('mod_trombinoscope_' . $module->id)); ?>
    								<?php else : ?>
    									<span class="nopicture">&nbsp;</span>
    								<?php endif; ?>
    								<?php if ($link_picture && $link) : ?>
    									</a>
    								<?php endif; ?>

    							</div>

    							<?php if ($picture_hover_type != 'none' && $link_picture && $link) : ?>
    								</div>
    							<?php endif; ?>
    						</div>

    						<div class="personinfo">
    							<div class="text_veil">
    								<?php if ($item->featured && $show_featured) : ?>
    									<div class="feature">
    										<i class="icon <?php echo $featured_icon; ?>" aria-hidden="true"></i>
    									</div>
    								<?php endif; ?>
    							</div>

    							<?php if ($show_category) : ?>
    								<div class="category">
    									<?php if ($link_category) : ?>
    										<a href="<?php echo $link_category; ?>" class="hasTooltip" title="<?php echo Text::_('JCATEGORY'); ?>">
    											<span><?php echo $item->category; ?></span>
    										</a>
    									<?php else : ?>
    										<span><?php echo $item->category; ?></span>
    									<?php endif; ?>
    								</div>
    							<?php endif; ?>

    							<?php if ($show_tags) : ?>
    								<?php if (isset($item->tags)) : ?>
    									<div class="tags">
    										<?php $layout = new FileLayout('tcptag', JPATH_ROOT.'/modules/mod_trombinoscope/layouts'); // no overrides possible ?>
    										<?php foreach ($item->tags as $tag) :  ?>
    											<?php
    												$data = array('tag' => $tag);
    												if ($link_to_tags) {
    													$data['link'] = Route::_(TagsRouteHelper::getTagRoute($tag->id . ':' . $tag->alias));
    												}
    												$data['bootstrap_version'] = $bootstrap_version;
    												$data['load_bootstrap'] = $load_bootstrap;

    												echo $layout->render($data);
    											?>
    										<?php endforeach; ?>
    									</div>
    								<?php endif; ?>
    							<?php endif; ?>

    							<?php
        							$title_attribute = Helper::getTitleAttribute($name_label, $name_tooltip);
        							if ($link_name && $link) {
        							    $name_value = '<a href="'.$link.'"'.$link_attributes.$title_attribute.Helper::getClassAttribute($name_tooltip, $link_classes.' fieldvalue').' aria-label="'.$name_label.'"><span>'.$formatted_name.'</span></a>';
        							} else {
        							    $name_value = '<span class="fieldvalue'.Helper::getTooltipClass($name_tooltip).'" aria-label="'.$name_label.'"'.$title_attribute.'>'.$formatted_name.'</span>';
        							}
    							?>
    							<?php echo Helper::renderName($params, $name_value, $extraclassesfields); ?>

    							<?php if ($requested_infos) : ?>
        							<?php foreach ($requested_infos as $index => $requested_info) : ?>
        								<?php echo Helper::renderInfo($params, $item, $index, $requested_info, $extraclassesfields); ?>
        							<?php endforeach; ?>
        						<?php endif; ?>

    							<?php if ($link_label && $link) : ?>
    								<div class="personlinks">
    									<div class="personlink go">
    										<a href="<?php echo $link; ?>"<?php echo $link_attributes; ?><?php echo Helper::getClassAttribute(false, $link_classes); ?>>
    											<?php if (Factory::getDocument()->getDirection() == 'rtl') : ?>
    												<i class="icon SYWicon-arrow-left" aria-hidden="true"></i><span><?php echo $link_label; ?></span>
    											<?php else : ?>
    												<i class="icon SYWicon-arrow-right" aria-hidden="true"></i><span><?php echo $link_label; ?></span>
    											<?php endif; ?>
    										</a>
    									</div>
        							</div>
    							<?php endif; ?>
    						</div>
						</div>
					</div>
				</div>
				<?php if ($carousel_configuration != 'none') : ?></div><?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ($show_arrows && ($arrow_prevnext_bottom || $arrow_next_right || $arrow_next_bottom)) : ?>
		<div class="items_pagination bottom<?php echo $extra_pagination_classes; ?>">
			<ul<?php echo $extra_pagination_ul_class_attribute; ?>>
			<?php if ($arrow_prevnext_bottom) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="prev_<?php echo $class_suffix; ?>" class="previous<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JPREV'); ?>" onclick="return false;"><i class="<?php echo ($carousel_configuration == 'h' ? 'SYWicon-arrow-left2' : 'SYWicon-arrow-up2') ?>" aria-hidden="true"></i></a></li><!--
				 --><li<?php echo $extra_pagination_li_class_attribute; ?>><a id="next_<?php echo $class_suffix; ?>" class="next<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JNEXT'); ?>" onclick="return false;"><i class="<?php echo ($carousel_configuration == 'h' ? 'SYWicon-arrow-right2' : 'SYWicon-arrow-down2') ?>" aria-hidden="true"></i></a></li>
			<?php endif; ?>
			<?php if ($arrow_next_right) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="next_<?php echo $class_suffix; ?>" class="next<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JNEXT'); ?>" onclick="return false;"><i class="SYWicon-arrow-right2" aria-hidden="true"></i></a></li>
			<?php endif; ?>
			<?php if ($arrow_next_bottom) : ?>
				<li<?php echo $extra_pagination_li_class_attribute; ?>><a id="next_<?php echo $class_suffix; ?>" class="next<?php echo $extra_pagination_a_classes; ?>" href="#" aria-label="<?php echo Text::_('JNEXT'); ?>" onclick="return false;"><i class="SYWicon-arrow-down2" aria-hidden="true"></i></a></li>
			<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ($module_link) : ?>
		<div class="module_link">
			<a href="<?php echo $module_link; ?>"<?php echo empty($module_link_class) ? '' : ' class="'.$module_link_class.'"'; ?><?php echo $module_link_isExternal ? ' target="_blank"' : ''; ?>><span><?php echo $module_link_label ?></span></a>
		</div>
	<?php endif; ?>

	<?php if (trim($params->get('posttext', ''))) : ?>
		<div class="posttext">
			<?php
				if ($params->get('allow_plugins_prepost', 0)) {
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

		$layout = new FileLayout('tcpmodal', JPATH_ROOT.'/modules/mod_trombinoscope/layouts'); // no overrides possible

        $data = array('selector' => 'tcpmodal_'.$module->id, 'width' => $popup_x, 'height' => $popup_y);
        $data['bootstrap_version'] = $bootstrap_version;
        $data['load_bootstrap'] = $load_bootstrap;

        echo $layout->render($data);
    }
?>

<?php if ($remove_whitespaces) : ?>
	<?php ob_get_flush(); ?>
<?php endif; ?>