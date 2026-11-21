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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Contact\Site\Helper\RouteHelper as ContactRouteHelper;
use SYW\Library\Libraries as SYWLIbraries;
use SYW\Library\Utilities as SYWUtilities;
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

$modal_needed = ($contact_link == 'popup');

$card_layout = new FileLayout('card', JPATH_ROOT.'/modules/mod_trombinoscope/layouts');

// Ajax search ($ajax_enabled already computed by the Dispatcher)

if ($ajax_enabled) {
	Helper::initializeAjax($params, $module);

	$ajax_data = json_encode([
		'moduleId' => $module->id,
		'baseUrl' => Uri::base(),
		'token' => Session::getFormToken(),
		'container' => '#te_' . $class_suffix,
		'loadingClass' => 'loading',
	]);
}

if ($remove_whitespaces) {
	ob_start(function($buffer) { return preg_replace('/\s+/', ' ', $buffer); });
}
?>
<div id="te_<?php echo $class_suffix; ?>" class="te te_<?php echo $class_suffix; ?><?php echo $arrow_class; ?><?php echo $isMobile ? ' mobile' : ''; ?>"<?php echo $ajax_enabled ? ' data-trombinoscope-ajax=\'' . $ajax_data . '\'' : ''; ?>>

	<?php if ($ajax_enabled) : ?>
        <div class="trombinoscope-search-form">
            <input type="text" class="form-control contact-search" placeholder="<?php echo Text::_('MOD_TROMBINOSCOPE_SEARCH_PLACEHOLDER'); ?>" />
        </div>
	<?php endif; ?>

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

		<?php if (empty($list) && $ajax_enabled) : ?>
			<div class="no-contacts"><?php echo Text::_('MOD_TROMBINOSCOPE_TYPE_TO_SEARCH'); ?></div>
		<?php endif; ?>

		<?php foreach ($list as $i => $item) : ?>

			<?php
                // category header
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

                // alphabet header
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

				// category heading link
				$heading_link = '';

				if ($link_to_category_header) {
					$heading_link = Route::_(ContactRouteHelper::getCategoryRoute($item->catid, $item->language));
				}
			?>

			<?php if ($show_category_header) : ?>
    			<?php if ($header || $header_alpha) : ?>
    				<div class="heading-group">
    					<?php if ($header) : ?>
        					<?php echo '<h' . $header_html_tag . ' class="heading">'; ?>
        						<?php if ($heading_link) : ?>
        							<a href="<?php echo $heading_link; ?>">
        								<span><?php echo htmlspecialchars($header, ENT_QUOTES, 'UTF-8'); ?></span>
        							</a>
        						<?php else : ?>
        							<span><?php echo htmlspecialchars($header, ENT_QUOTES, 'UTF-8'); ?></span>
        						<?php endif; ?>
        					<?php echo '</h' . $header_html_tag . '>'; ?>
        				<?php endif; ?>
        				<?php if ($show_alphabet_header) : ?>
        					<?php if ($header_alpha) : ?>
        						<?php echo '<h' . $subheader_html_tag . ' class="sub-heading">'; ?>
        							<span><?php echo htmlspecialchars($header_alpha, ENT_QUOTES, 'UTF-8'); ?></span>
        						<?php echo '</h' . $subheader_html_tag . '>'; ?>
        					<?php endif; ?>
        				<?php endif; ?>
    				</div>
    			<?php endif; ?>
    		<?php elseif ($show_alphabet_header) : ?>
    			<?php if ($header_alpha) : ?>
    				<div class="heading-group">
    					<?php echo '<h' . $header_html_tag . ' class="heading">'; ?>
    						<span><?php echo htmlspecialchars($header_alpha, ENT_QUOTES, 'UTF-8'); ?></span>
    					<?php echo '</h' . $header_html_tag . '>'; ?>
    				</div>
    			<?php endif; ?>
    		<?php endif; ?>

			<?php
				echo $card_layout->render(array(
					'item' => $item,
					'params' => $params,
					'module' => $module,
					'groups' => $groups,
					'i_header' => $i_header,
					'carousel_configuration' => $carousel_configuration,
					'format_style' => $format_style,
					'uppercase' => $uppercase,
					'contact_link' => $contact_link,
					'link_access' => $link_access,
					'generic_link' => $generic_link,
					'link_label' => $link_label,
					'bootstrap_version' => $bootstrap_version,
					'show_errors' => $show_errors,
					'name_label' => $name_label,
					'name_tooltip' => $name_tooltip,
					'link_name' => $link_name,
					'show_picture' => $show_picture,
					'link_picture' => $link_picture,
					'picture_hover_type' => $picture_hover_type,
					'picture_tooltip' => $picture_tooltip,
					'keep_picture_space' => $keep_picture_space,
					'photo_align' => $photo_align,
					'crop_picture' => $crop_picture,
					'picture_width' => $picture_width,
					'picture_height' => $picture_height,
					'create_highres_images' => $create_highres_images,
					'show_vcard' => $show_vcard,
					'vcard_icon' => $vcard_icon,
					'show_featured' => $show_featured,
					'featured_icon' => $featured_icon,
					'style_social_icons' => $style_social_icons,
					'show_category' => $show_category,
					'link_to_category' => $link_to_category,
					'show_tags' => $show_tags,
					'link_to_tags' => $link_to_tags,
					'requested_links' => $requested_links,
					'requested_infos' => $requested_infos,
					'extraclassesfields' => $extraclassesfields,
					'extraclasseslinkfields' => $extraclasseslinkfields,
				));

				$i_header++;
			?>
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
