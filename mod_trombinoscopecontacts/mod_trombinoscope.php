<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Fonts as SYWFonts;
use SYW\Library\Libraries as SYWLibraries;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Library\Stylesheets as SYWStylesheets;
use SYW\Module\TrombinoscopeContacts\Site\Cache\CSSFileCache;
use SYW\Module\TrombinoscopeContacts\Site\Cache\JSAnimationFileCache;
use SYW\Module\TrombinoscopeContacts\Site\Cache\JSFileCache;
use SYW\Module\TrombinoscopeContacts\Site\Helper\Helper;

$isMobile = SYWUtilities::isMobile();

$show_on_mobile = $params->get('show_on_mobile', 1);
if (($isMobile && $show_on_mobile == 0) || (!$isMobile && $show_on_mobile == 2)) {
	return;
}

$list = Helper::getContacts($params, $module);

if (empty($list)) {
	return;
}

$class_suffix = $module->id;
$params->set('suffix', $class_suffix);

$urlPath = Uri::base().'modules/mod_trombinoscope/';
//$doc = Factory::getDocument();
$app = Factory::getApplication();
$wam = $app->getDocument()->getWebAssetManager();

$user = Factory::getUser();
$groups	= $user->getAuthorisedViewLevels();

$globalparams = Helper::getContactGlobalParams();

$bootstrap_version = Helper::getBootstrapVersion($params);
$load_bootstrap = Helper::isLoadBootstrap($params);

$params->set('bootstrap_version', $bootstrap_version); // for use in js and css cached files

$show_errors = Helper::isShowErrors($params);

$remove_whitespaces = Helper::isRemoveWhitespaces($params);

$module_link = $params->get('modulel', '');
$module_link_label = '';
$module_link_isExternal = false;
$module_link_class = trim($params->get('modulel_class', ''));

if ($module_link) {
	if ($module_link == 'extern') {
		$external_url = trim($params->get('modulel_external_url', ''));
		if ($external_url) {
			$module_link = $external_url;
			$module_link_isExternal = true;
			$module_link_label = trim($params->get('modulel_lbl', '')) == '' ? $external_url : trim($params->get('modulel_lbl'));
		} else {
			$module_link = '';
		}
	} else if (is_numeric($module_link)) {
		$menu = $app->getMenu();
		$menuitem = $menu->getItem($module_link);
		if ($menuitem->type == 'alias') { // if this is an alias use the item id stored in the parameters to make the link
			$menuitem = $menu->getItem($menuitem->params->get('aliasoptions')); // gets the targetted menu item
		}

		if (Multilanguage::isEnabled()) {
			$currentLanguage = Factory::getLanguage()->getTag();
			$langAssociations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $menuitem->id, 'id', '', '');
			foreach ($langAssociations as $langAssociation) {
				if ($langAssociation->language == $currentLanguage) {
					$menuitem = $menu->getItem($langAssociation->id);
					break;
				}
			}
		}

		switch ($menuitem->type)
		{
			case 'separator':
			case 'heading':
				$module_link = '';
				break;
			case 'url':
				$module_link = $menuitem->link;
				if ((strpos($menuitem->link, 'index.php?') === 0) && (strpos($menuitem->link, 'Itemid=') === false)) {
					// if this is an internal Joomla link, ensure the Itemid is set
					$module_link .= '&Itemid=' . $menuitem->id;
				}
				break;
			case 'alias': $module_link = 'index.php?Itemid=' . $menuitem->id; break;
			default: $module_link = $menuitem->link . '&Itemid=' . $menuitem->id;
		}

		$module_link_label = trim($params->get('modulel_lbl', '')) == '' ? $menuitem->title : trim($params->get('modulel_lbl'));
	} else { // backward compatibility, we get old value until the instance is saved again
		$module_link_label = trim($params->get('modulel_lbl', ''));
	}
}

$photo_align = $params->get('pic_a');
$link_picture = $params->get('link_pic', true);
$picture_tooltip = $params->get('pic_tooltip', true);
$default_picture = $params->get('d_pic', '');
$keep_space = $params->get('k_s', true);
$contact_link = $params->get('l_to_c', 'none');
$link_access = $params->get('link_access', '1');
$generic_link = $params->get('generic_l', '');

$popup_x = $params->get('popup_x', '600');
$popup_y = $params->get('popup_y', '500');

$link_label = $params->get('l_lbl', '');

$format_style = $params->get('name_fmt', 'none');
$uppercase = $params->get('name_upper', 0);
$pre_name = $params->get('s_name_lbl', 0);
$name_label = trim($params->get('name_lbl', '')) == '' ? Text::_('MOD_TROMBINOSCOPE_LABEL_NAME') : trim($params->get('name_lbl', ''));
$name_icon = $params->get('name_icon', '');

$link_name = $params->get('link_name', true);
if ($contact_link !== 'none' && empty($link_label) && !$link_picture) {
	$link_name = true;
}

$name_tooltip = $params->get('name_tooltip', true);

$show_allfields_label = $params->get('s_f_lbl', 0);

$label_separator = $params->get('lbl_separator', '');

$style_social_icons = $params->get('social_styling', false);

$card_width = $params->get('card_w', 100);
$min_card_width = trim($params->get('min_card_w', ''));
$card_width_unit = $params->get('card_w_u', '%');
$card_height = $params->get('card_h', '');

$min_card_flip_width = trim($params->get('min_card_flip_w', ''));

$border_width = Helper::getPictureBorderWidth($params);
$border_radius = $params->get('border_r', 0);

$picture_width = Helper::getPictureWidth($params);
$picture_height = Helper::getPictureHeight($params);

$picture_hover_type = $params->get('pic_hover_type', 'none');
if ($picture_hover_type != 'none') {
	$picture_hover_type = 'hvr-'.$picture_hover_type;
}

$show_picture = Helper::isShowPicture($params);
$keep_picture_space = $params->get('k_pic_s', true);
$overflow = $params->get('overflow', false);

$show_vcard = $params->get('s_v', false);
$vcard_type = $params->get('vcard_type', 'p');

$show_featured = $params->get('s_f', false);
$featured_icon = $params->get('f_icon', 'star');

$crop_picture = Helper::isCropPicture($params);

$quality = Helper::getPictureQuality($params);

$clear_cache = Helper::IsClearPictureCache($params);

$tmp_path = Helper::getPictureTemporaryPath($params);

$filter = Helper::getPictureFilters($params);

$category_showing = $params->get('s_cat', 'sl');
$cat_view_id = $params->get('cat_views', 'auto');
$show_category = false;
$link_to_category = false;
$link_to_view_category = false;
switch ($category_showing) {
	case 's' :
		$show_category = true;
		break;
	case 'sl' :
		$show_category = true;
		$link_to_category = true;
		break;
	case 'sv' :
		$show_category = true;
		$link_to_view_category = true;
		break;
	default :
		break;
}

$tags_showing = $params->get('s_tag', 'h');
$tags_view_id = $params->get('tag_views', '');
$show_tags = false;
$link_to_tags = false;
$link_to_view_tags = false;
switch ($tags_showing) {
	case 's' :
		$show_tags = true;
		break;
	case 'sl' :
		$show_tags = true;
		$link_to_tags = true;
		break;
	case 'sv' :
		$show_tags = true;
		$link_to_view_tags = true;
		break;
	default :
		break;
}

$header_showing = $params->get('s_h', 'h');
$header_html_tag = $params->get('h_tag', '4');
$header_view_id = $params->get('header_views', 'auto');
$show_category_header = false;
$link_to_category_header = false;
$link_to_view_category_header = false;

$show_heading = true;
switch ($header_showing) {
	case 'sc' :
		$show_category_header = true;
		break;
	case 'slc' :
		$show_category_header = true;
		$link_to_category_header = true;
		break;
	case 'svc' :
		$show_category_header = true;
		$link_to_view_category_header = true;
		break;
	default :
		$show_heading = false;
}

$cat_order = $params->get('c_order', '');

$requested_links = Helper::getRequestedLinks($params);
$requested_infos = Helper::getRequestedInfos($params);

$params->set('show_links', $requested_links ? true : false); // for use in css cached files

// add extra classes for fields

$extraclassesfields = '';
if ($params->get('ifont_bgc', '') && $params->get('ifont_bgc', '') != 'transparent') {
    $extraclassesfields .= 'iconbg';
} else {
    $extraclassesfields .= 'iconnobg';
}

if ($params->get('lbl_bgc', '') && $params->get('lbl_bgc', '') != 'transparent') {
    $extraclassesfields .= ' lblbg';
} else {
    $extraclassesfields .= ' lblnobg';
}

// add extra classes for link fields

$extraclasseslinkfields = '';
if ($params->get('ifont_bgc_links', '') && $params->get('ifont_bgc_links', '') != 'transparent') {
    $extraclasseslinkfields .= 'iconbg';
} else {
    $extraclasseslinkfields .= 'iconnobg';
}

$letter_count = $params->get('l_c', '');
$strip_tags = $params->get('s_t', 1);
$keep_tags = $params->get('keep_tags');

$link_email = $params->get('link_e', 1);
$cloak_email = $params->get('cloak_e', 1);
$email_substitut = $params->get('e_substitut', '');
$webpage_substitut = $params->get('w_substitut', '');
$protocol = $params->get('protocol', true);

$address_format = $params->get('a_fmt', 'ssz');
$address_link_with_map = $params->get('a_link_map', false);
$auto_map_params = trim($params->get('auto_map_params', ''));
$date_format = $params->get('d_format', 'd F Y');
$birthdate_format = $params->get('dob_format', 'F d');

$clear_header_files_cache = Helper::IsClearHeaderCache($params);

$generate_inline_scripts = $params->get('inline_scripts', 0);
$load_remotely = $params->get('remote_libraries', 0);

// carousel

$arrow_class = '';
$show_arrows = false;
$show_pages = false;

$arrow_prev_left = false;
$arrow_next_right = false;
$arrow_prev_top = false;
$arrow_next_bottom = false;
$arrow_prevnext_bottom = false;

$rtl_suffix = (Factory::getDocument()->getDirection() == 'rtl') ? '_rtl' : '';

$carousel_configuration = $params->get('carousel_config', 'none');
if ($carousel_configuration != 'none') {

    SYWLibraries::loadTinySlider($load_remotely);

    $show_arrows = true;
	switch ($params->get('arrows', 'none')) {
		case 'around':
			if ($carousel_configuration == 'h') {
				$arrow_class = ' side_navigation';
				$arrow_prev_left = true;
				$arrow_next_right = true;
			} else {
				$arrow_class = ' above_navigation';
				$arrow_prev_top = true;
				$arrow_next_bottom = true;
			}
			break;
		case 'under':
			$arrow_class = ' under_navigation';
			$arrow_prevnext_bottom = true;
			break;
		case 'title': break;
		default: $show_arrows = false;
	}

	$show_pages = $params->get('includepages', 0);

	$extra_pagination_classes = '';
	$extra_pagination_ul_class_attribute = '';
	$extra_pagination_li_class_attribute = '';
	$extra_pagination_a_classes = '';

	if ($params->get('arrowstyle', '') && $bootstrap_version > 0) { // Bootstrap is selected

	    $pagination_size = $params->get('arrowsize_bootstrap', '');
	    $pagination_align = SYWUtilities::getBootstrapProperty('pagination-center', $bootstrap_version);
	    if ($bootstrap_version == 2) {
	        $extra_pagination_classes = ' pagination';
	        if ($pagination_size) {
	            $extra_pagination_classes .= ' ' . SYWUtilities::getBootstrapProperty('pagination-'.$pagination_size, $bootstrap_version);
	        }
	    } else if ($bootstrap_version > 2) { // Bootstrap 3, 4 or 5
	        $extra_pagination_ul_class_attribute = ' class="pagination';
	        if ($pagination_size) {
	            $extra_pagination_ul_class_attribute .= ' ' . SYWUtilities::getBootstrapProperty('pagination-'.$pagination_size, $bootstrap_version);
	        }
	        if ($pagination_align) {
	            $extra_pagination_ul_class_attribute .= ' '.$pagination_align;
	        }
	        $extra_pagination_ul_class_attribute .= '"';
	        if ($bootstrap_version >= 4) {
	            $extra_pagination_li_class_attribute = ' class="page-item"';
	            $extra_pagination_a_classes = ' page-link';
	        }
	    }
	}

	$cache_anim_js = new JSAnimationFileCache('mod_trombinoscopecontacts', $params);

	if ($generate_inline_scripts) {

		$wam->addInlineScript($cache_anim_js->getBuffer());

	} else {

		$result = $cache_anim_js->cache('animation_' . $module->id . $rtl_suffix . '.js', $clear_header_files_cache);

		if ($result) {
			$wam->registerAndUseScript('tc.animation_' . $module->id . $rtl_suffix, $cache_anim_js->getCachePath() . '/animation_' . $module->id . $rtl_suffix . '.js', [], ['defer' => true]);
		}
	}

} else {
	// remove animation.js if it exists
	if (File::exists(JPATH_SITE . '/media/cache/mod_trombinoscopecontacts/animation_' . $module->id . $rtl_suffix . '.js')) {
		File::delete(JPATH_SITE . '/media/cache/mod_trombinoscopecontacts/animation_' . $module->id . $rtl_suffix . '.js');
	}
}

// adding responsiveness with card switch from landscape to portrait when there is no animation
if ($show_picture && $photo_align != 't' && $min_card_flip_width) {
	Helper::loadFlipCards();
	$cache_js = new JSFileCache('mod_trombinoscopecontacts', $params);
	$wam->addInlineScript($cache_js->getBuffer());
}

// styles

if (File::exists(JPATH_ROOT . '/media/mod_trombinoscopecontacts/css/substitute_styles.css') || File::exists(JPATH_ROOT . '/media/mod_trombinoscopecontacts/css/substitute_styles-min.css')) {
	Helper::loadUserStylesheet(true);

	// remove style.css if it exists
	if (File::exists(JPATH_SITE . '/media/cache/mod_trombinoscopecontacts/style_'.$module->id.'.css')) {
		File::delete(JPATH_SITE . '/media/cache/mod_trombinoscopecontacts/style_'.$module->id.'.css');
	}
} else {

	$user_styles = trim($params->get('style_overrides', ''));

	if (trim($params->get('font', '')) != '') {

	    $font = str_replace('\'', '"', trim($params->get('font', ''))); // " lost, replaced by '

	    $google_font = SYWUtilities::getGoogleFont($font); // get Google font, if any
	    if ($google_font) {
	        SYWFonts::loadGoogleFont($google_font);
	    }

	    $user_styles .= '#te_'.$module->id.' .person {';
	    $user_styles .= 'font-family: '.$font.' !important;';
	    $user_styles .= '} ';
	}

	if (!empty($user_styles)) {
		$user_styles = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $user_styles); // minify the CSS code
	}

	$cache_css = new CSSFileCache('mod_trombinoscopecontacts', $params);
	$cache_css->addDeclaration($user_styles);

	$result = $cache_css->cache('style_'.$module->id.'.css', $clear_header_files_cache);

	if ($result) {
		$wam->registerAndUseStyle('tc.style_' . $module->id, $cache_css->getCachePath() . '/style_' . $module->id . '.css');
	}

	Helper::loadCommonStylesheet();

	if (File::exists(JPATH_ROOT . '/media/mod_trombinoscopecontacts/css/common_user_styles.css') || File::exists(JPATH_ROOT . '/media/mod_trombinoscopecontacts/css/common_user_styles-min.css')) {
		Helper::loadUserStylesheet();
	}
}

if ($picture_hover_type != 'none') {
	$transition_method = SYWStylesheets::getTransitionMethod($picture_hover_type);
	SYWStylesheets::$transition_method();
}

// handle high resolution images
$create_highres_images = Helper::isCreateHighResolutionPicture($params);
// if ($show_picture && $create_highres_images) {
// 	SYWLibraries::loadLazysizes($load_remotely);
// }

// load icon font
$load_icon_font = $params->get('load_icon_font', 1);
if ($load_icon_font) {
	SYWFonts::loadIconFont();
}

// load modal script when no Bootstrap
// keep for B/C - remove in v5
$load_modal = false;
if ($contact_link == 'popup') {
	$load_modal = true;
	if ($bootstrap_version == 0) {
		SYWLibraries::loadPureModal($load_remotely);
	}
}

require(ModuleHelper::getLayoutPath('mod_trombinoscope', $params->get('layout', 'default')));
?>
