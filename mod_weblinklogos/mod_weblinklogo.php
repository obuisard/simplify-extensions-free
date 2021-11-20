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
use Joomla\CMS\Uri\Uri;
use SYW\Library\Cache as SYWCache;
use SYW\Library\Fonts as SYWFonts;
use SYW\Library\Libraries as SYWLibraries;
use SYW\Library\Stylesheets as SYWStylesheets;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Library\Version as SYWVersion;
use SYW\Module\WeblinkLogos\Site\Cache\CSSFileCache;
use SYW\Module\WeblinkLogos\Site\Cache\JSAnimationFileCache;
use SYW\Module\WeblinkLogos\Site\Helper\Helper;

$isMobile = SYWUtilities::isMobile();

$show_on_mobile = $params->get('show_on_mobile', 1);
if (($isMobile && $show_on_mobile == 0) || (!$isMobile && $show_on_mobile == 2)) {
	return;
}

$list = Helper::getList($params);

if (empty($list)) {
	return;
}

$class_suffix = $module->id;
$params->set('suffix', $class_suffix);

$urlPath = Uri::base().'modules/mod_weblinklogo/'; // use Uri::base(true) when caching
//$doc = Factory::getDocument();
$app = Factory::getApplication();
$wam = $app->getDocument()->getWebAssetManager();

$bootstrap_version = $params->get('bootstrap_version', 'joomla');
$load_bootstrap = false;
if ($bootstrap_version === 'joomla') {
    $bootstrap_version = 5;
    $load_bootstrap = true;
} else {
	$bootstrap_version = intval($bootstrap_version);
}

$params->set('bootstrap_version', $bootstrap_version); // for use in js and css cached files

$general_errors = array();
$show_errors = Helper::isShowErrors($params);

$remove_whitespaces = Helper::isRemoveWhitespaces($params);

// logos

$width = $params->get('width', 120);
$height = $params->get('height', 40);

$restrict_width = $params->get('restrict_width', 0);
$center_vertically = $params->get('center_vertically', 0);

$filter = $params->get('filter', 'none');
if (strpos($filter, '_css') !== false) {
	$filter = 'none';
}
$filter_hover = $params->get('filter_hover', 'none');
if (strpos($filter_hover, '_css') !== false) {
	$filter_hover = 'none';
}

$thumbnail_mime_type = $params->get('thumb_mime_type', '');

$allow_remote = true;

$quality_jpg = $params->get('quality_jpg', 75);
$quality_png = $params->get('quality_png', 3);
$quality_webp = $params->get('quality_webp', 80);
$quality_avif = $params->get('quality_avif', 80);

if ($quality_jpg > 100) {
	$quality_jpg = 100;
}
if ($quality_jpg < 0) {
	$quality_jpg = 0;
}

if ($quality_png > 9) {
	$quality_png = 9;
}
if ($quality_png < 0) {
	$quality_png = 0;
}

if ($quality_webp > 100) {
	$quality_webp = 100;
}
if ($quality_webp < 0) {
	$quality_webp = 0;
}

if ($quality_avif > 100) {
    $quality_avif = 100;
}
if ($quality_avif < 0) {
    $quality_avif = 0;
}

$image_qualities = array('jpg' => $quality_jpg, 'png' => $quality_png, 'webp' => $quality_webp, 'avif' => $quality_avif);

$hover_type = $params->get('hover_type', 'none'); // hover animation
if ($hover_type != 'none') {
	$hover_type = 'hvr-'.$hover_type;
	$transition_method = SYWStylesheets::getTransitionMethod($hover_type);
	SYWStylesheets::$transition_method();
} else {
	$hover_type = 'smooth';
}

$subdirectory = 'thumbnails/wl';

$thumb_path = $params->get('thumb_path', 'images');

if ($thumb_path == 'cache') {
	$subdirectory = 'mod_weblinklogos';
}
$tmp_path = SYWCache::getTmpPath($thumb_path, $subdirectory);

$unique_filename_extra = '';
$unique_files = $params->get('unique_files', 1);
if ($unique_files) {
	$unique_filename_extra = $module->id;
}

$clear_cache = Helper::IsClearPictureCache($params);

if ($clear_cache) {
	Helper::clearThumbnails($tmp_path, $unique_filename_extra);
	
	SYWVersion::refreshMediaVersion('mod_weblinklogos_' . $module->id);
}

// links

$popup_width = $params->get('popup_x', 600);
$popup_height = $params->get('popup_y', 500);

// configuration

$configuration = $params->get('logos_layout', 'grid');

// $overall_width = $params->get('overall_width', '');

// $margin_top = $params->get('margin_top', 5);
// $margin_right = $params->get('margin_right', 5);
// $margin_bottom = $params->get('margin_bottom', 5);
// $margin_left = $params->get('margin_left', 5);

$title_html_tag = $params->get('title_tag', '4');
$description_html_tag = $params->get('description_tag', 'none');
if ($description_html_tag == 'none') {
	$description_html_tag = '';
}

// keep to make sure overrides still work after update

$letter_count = trim($params->get('l_count', ''));
if (empty($letter_count)) {
	$letter_count = -1;
} else {
	$letter_count = (int)($letter_count);
}

$strip_tags = $params->get('strip_tags', 1);
$keep_tags = trim($params->get('keep_tags'));
$trigger_events = $params->get('trigger_events', false);

// END keep to make sure overrides still work after update

$caption_classes = trim($params->get('caption_classes', ''));
if ($caption_classes) {
	$caption_classes = ' '.$caption_classes;
}

$hits_classes = trim($params->get('hits_classes', ''));
if ($hits_classes) {
	$hits_classes = ' '.$hits_classes;
}

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

if ($params->get('hit_feedback', 0)) {
	//HtmlHelper::_('jquery.framework');
	Helper::loadClickedScript($class_suffix);
}

$rtl_suffix = (Factory::getDocument()->getDirection() == 'rtl') ? '_rtl' : '';

$carousel_configuration = $params->get('carousel_config', 'none');
if ($carousel_configuration != 'none') {

    SYWLibraries::loadTinySlider($load_remotely);

	switch ($params->get('arrows', 'none')) {
		case 'around':
			$show_arrows = true;
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
			$show_arrows = true;
			$arrow_prevnext_bottom = true;
			break;
		case 'title':
			$show_arrows = true;
			break;
	}

	if ($show_arrows) {
		SYWFonts::loadIconFont();
	}

	$show_pages = $params->get('includepages', 0);

	$extra_pagination_classes = '';
	$extra_pagination_ul_class_attribute = '';
	$extra_pagination_li_class_attribute = '';
	$extra_pagination_a_classes = '';

	$pagination_style = $params->get('arrowstyle', '');
	if ($pagination_style && $bootstrap_version > 0) { // Bootstrap is selected
	    $pagination_size = $params->get('arrowsize_bootstrap', '');
	    $pagination_align = SYWUtilities::getBootstrapProperty('pagination-center', $bootstrap_version);
	    if ($bootstrap_version == 2) {
	        $extra_pagination_classes = ' pagination';
	        if ($pagination_size) {
	            $extra_pagination_classes .= ' '.SYWUtilities::getBootstrapProperty('pagination-'.$pagination_size, $bootstrap_version);
	        }
	    }
	    if ($bootstrap_version >= 3) {
	        $extra_pagination_ul_class_attribute = ' class="pagination';
	        if ($pagination_size) {
	            $extra_pagination_ul_class_attribute .= ' '.SYWUtilities::getBootstrapProperty('pagination-'.$pagination_size, $bootstrap_version);
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

	$cache_anim_js = new JSAnimationFileCache('mod_weblinklogos', $params);

	if ($generate_inline_scripts) {

		$wam->addInlineScript($cache_anim_js->getBuffer());

	} else {

		$result = $cache_anim_js->cache('animation_' . $module->id . $rtl_suffix . '.js', $clear_header_files_cache);

		if ($result) {
			$wam->registerAndUseScript('wl.animation_' . $module->id . $rtl_suffix, $cache_anim_js->getCachePath() . '/animation_' . $module->id . $rtl_suffix . '.js', [], ['defer' => true]);
		}
	}
} else {
	// remove animation.js if it exists
	if (File::exists(JPATH_SITE . '/media/cache/mod_weblinklogos/animation_' . $module->id . $rtl_suffix . '.js')) {
		File::delete(JPATH_SITE . '/media/cache/mod_weblinklogos/animation_' . $module->id . $rtl_suffix . '.js');
	}
}

// style

if (File::exists(JPATH_ROOT.'/media/mod_weblinklogos/css/substitute_styles.css') || File::exists(JPATH_ROOT.'/media/mod_weblinklogos/css/substitute_styles-min.css')) {
	Helper::loadUserStylesheet(true);

	// remove style.css if it exists
	if (File::exists(JPATH_SITE . '/media/cache/mod_weblinklogos/style_'.$module->id.'.css')) {
		File::delete(JPATH_SITE . '/media/cache/mod_weblinklogos/style_'.$module->id.'.css');
	}
} else {

	// add specific styles
	$user_styles = trim($params->get('styles', ''));
	if (!empty($user_styles)) {
		$user_styles = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $user_styles); // minify the CSS code
	}

	$cache_css = new CSSFileCache('mod_weblinklogos', $params);
	$cache_css->addDeclaration($user_styles);

	$result = $cache_css->cache('style_'.$module->id.'.css', $clear_header_files_cache);

	if ($result) {
		$wam->registerAndUseStyle('wl.style_' . $module->id, $cache_css->getCachePath() . '/style_' . $module->id . '.css');
	}

	Helper::loadCommonStylesheet();

	if (File::exists(JPATH_ROOT.'/media/mod_weblinklogos/css/common_user_styles.css') || File::exists(JPATH_ROOT.'/media/mod_weblinklogos/css/common_user_styles-min.css')) {
		Helper::loadUserStylesheet();
	}
}

// handle high resolution images
$create_highres_images = $params->get('create_highres', false);

// load icon font
// $load_icon_font = $params->get('load_icon_font', 1);
// if ($load_icon_font) {
// 	SYWFonts::loadIconFont();
// }

require ModuleHelper::getLayoutPath('mod_weblinklogo', $params->get('layout', 'default'));
