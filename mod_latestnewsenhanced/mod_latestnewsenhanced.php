<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Fonts as SYWFonts;
use SYW\Library\K2 as SYWK2;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Library\Stylesheets as SYWStylesheets;
use SYW\Module\LatestNewsEnhanced\Site\Cache\CSSFileCache;
use SYW\Module\LatestNewsEnhanced\Site\Cache\JSAnimationFileCache;
use SYW\Module\LatestNewsEnhanced\Site\Cache\JSFileCache;
use SYW\Module\LatestNewsEnhanced\Site\Helper\CalendarHelper as LNECalendarHelper;
use SYW\Module\LatestNewsEnhanced\Site\Helper\Helper as LNEHelper;
use SYW\Module\LatestNewsEnhanced\Site\Helper\ContentHelper as LNEContentHelper;
use SYW\Module\LatestNewsEnhanced\Site\Helper\K2Helper as LNEK2Helper;

$isMobile = SYWUtilities::isMobile();

$show_on_mobile = $params->get('show_on_mobile', 1);
if (($isMobile && $show_on_mobile == 0) || (!$isMobile && $show_on_mobile == 2)) {
	return;
}

$list = null;

$class_suffix = $module->id;
$params->set('suffix', $class_suffix);

$datasource = $params->get('datasource', 'articles');
switch ($datasource) {
	case 'articles':
		$list = LNEContentHelper::getList($params, $module);
		break;
	case 'k2':
		if (SYWK2::exists()) {
			$list = LNEK2Helper::getList($params, $module);
		} else {
			return; // wrong selection since K2 is not installed
		}
}

// consider $list is null, in which case, just do a return
if ($list === null) {
	return;
}

$bootstrap_version = $params->get('bootstrap_version', 'joomla');
$load_bootstrap = false;
if ($bootstrap_version === 'joomla') {
    $bootstrap_version = version_compare(JVERSION, '4.0.0', 'lt') ? 2 : 4;
    $load_bootstrap = true;
} else {
	$bootstrap_version = intval($bootstrap_version);
}

if (empty($list)) { // $list can be an empty array
	$nodata_message = trim($params->get('nodatamessage', ''));
	if (!empty($nodata_message)) {
		require ModuleHelper::getLayoutPath('mod_latestnewsenhanced', $params->get('layout', 'default'));
	} else {
		return;
	}
} else {

	// parameters

	$urlPath = Uri::base().'modules/mod_latestnewsenhanced/';
	$doc = Factory::getDocument();
	$app = Factory::getApplication();

	$params->set('bootstrap_version', $bootstrap_version); // for use in header files

	$show_errors = $params->get('show_errors', 0);
	if ($params->get('site_mode', 'adv') == 'dev') {
		$show_errors = 1;
	} else if ($params->get('site_mode', 'adv') == 'prod') {
		$show_errors = 0;
	}

	$remove_whitespaces = $params->get('remove_whitespaces', 0);
	if ($params->get('site_mode', 'adv') == 'dev') {
		$remove_whitespaces = 0;
	} else if ($params->get('site_mode', 'adv') == 'prod') {
		$remove_whitespaces = 1;
	}

	$items_align = $params->get('align', 'v');

	$items_height = trim($params->get('items_h', ''));
	$items_width = trim($params->get('items_w', ''));
	$item_width = trim($params->get('item_w', 100));
	$item_width_unit = $params->get('item_w_u', 'percent');
	if ($item_width_unit == 'percent') {
		$item_width_unit = '%';
	}
	$min_item_width = $params->get('min_item_w', '');

	if ($item_width_unit == '%') {
		if ($item_width <= 0 || $item_width > 100) {
			$item_width = 100;
		}
	} else {
		if ($item_width < 0) {
			$item_width = 0;
		}
	}

	$text_align = $params->get('text_align', 'r');
	$title_before_head = $params->get('title_before_head', false);
	$title_html_tag = $params->get('title_tag', '4');

	$follow = $params->get('follow', true);

	$popup_width = $params->get('popup_x', 600);
	$popup_height = $params->get('popup_y', 500);

	$show_title = true;
	if (trim($params->get('letter_count_title', '')) == '0') {
		$show_title = false;
	}
	$force_title_one_line = $params->get('force_one_line', false);

	$info_block_placement = $params->get('ad_place', 1);
	$overall_style = $params->get('overall_style', 'original');
	$keep_space = $params->get('keep_image_space', 1);
	$alignment = ($items_align == 'v') ? 'vertical' : 'horizontal';

	$clear_header_files_cache = $params->get('clear_css_cache', 1);
	if ($params->get('site_mode', 'adv') == 'dev') {
		$clear_header_files_cache = 1;
	} else if ($params->get('site_mode', 'adv') == 'prod') {
		$clear_header_files_cache = 0;
	}

	$generate_inline_scripts = $params->get('inline_scripts', 0);
	$load_remotely = $params->get('remote_libraries', 0);

	// link

	$link_label = trim($params->get('link', ''));
	$unauthorized_link_label = '';
	$link_tooltip = $params->get('readmore_tooltip', 1);

	$show_link = false; // keep to avoid template override crashes on update
	$show_link_label = false; // keep to avoid template override crashes on update
	$append_link = $params->get('append_link', 0); // keep to avoid template override crashes on update and backward compatibility

	$link_title = false;
	$link_head = false;
	$add_readmore = false;
	$append_readmore = false;

	$what_to_link = $params->get('what_to_link', '');
	if (is_array($what_to_link)) {
		foreach ($what_to_link as $choice) {
			switch ($choice) {
				case 'title' : $link_title = true; $show_link = true; break;
				case 'head' : $link_head = true; $show_link = true; break;
				case 'label' : $add_readmore = true; $show_link_label = true; break;
				case 'append' : $append_readmore = true; $append_link = true; break;
			}
		}
	} else { // old values
		switch ($what_to_link) { // for backward compatibility on template overrides
			case 'title' : $show_link = true; $link_title = true; $link_head = true; break;
			case 'label' : $show_link_label = true; $add_readmore = true; break;
			case 'both' : $show_link = true; $show_link_label = true; $link_title = true; $link_head = true; $add_readmore = true; break;
		}

		if ($append_link) {
			$append_readmore = true;
		}
	}

	// categories

	$show_category = ($params->get('show_cat', 0) == 0) ? false : true; // keep for backward compatibility on template overrides
	$link_category = ($params->get('show_cat', 0) == 1) ? true : false; // keep for backward compatibility on template overrides

	$pos_category_first = false;
	$pos_category_over_picture = false;
	$pos_category_before_title = false;
	$pos_category_last = false;

	$pos_category = $params->get('pos_cat', '');
	if (is_array($pos_category)) {
		foreach ($pos_category as $choice) {
			switch ($choice) {
				case 'first' : $pos_category_first = true; $show_category = true; $pos_category = 'first'; break;
				case 'title' : $pos_category_before_title = true; $show_category = true; $pos_category = 'title'; break;
				case 'last' : $pos_category_last = true; $show_category = true; $pos_category = 'last'; break;
			}
		}

		if ($params->get('link_cat_to', 'none') != 'none') {
			$link_category = true;
		}
	} else { // old values
		if ($show_category) {
			switch ($pos_category) {
				case 'first' : $pos_category_first = true; break;
				case 'title' : $pos_category_before_title = true; break;
				case 'last' : $pos_category_last = true; break;
			}
		}
	}

	$cat_link_text = trim($params->get('cat_link', ''));
	$unauthorized_cat_link_text = '';
	$category_link_tooltip = $params->get('cat_tooltip', 1);
	$consolidate_category = $params->get('consol_cat', 1);
	$show_category_description = $params->get('show_cat_description', 0);
	$show_article_count = $params->get('show_article_count', 0);

	// read all

	$readall_link = $params->get('readall_link', '');
	$readall_link_label = '';
	$readall_isExternal = false;
	if (!empty($readall_link)) {
		$readall_link_tooltip = $params->get('readall_tooltip', 1);
		if ($readall_link == 'extern') {
			$external_url = trim($params->get('readall_external_url', ''));
			if ($external_url) {
				$readall_link = $external_url;
				$readall_isExternal = true;
				$readall_link_label = trim($params->get('readall_link_lbl', '')) == '' ? $external_url : trim($params->get('readall_link_lbl'));
			} else {
				$readall_link = '';
			}
		} else {
			$menu = $app->getMenu();
			$menuitem = $menu->getItem($readall_link);
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
					$readall_link = '';
					break;
				case 'url':
					$readall_link = $menuitem->link;
					if ((strpos($menuitem->link, 'index.php?') === 0) && (strpos($menuitem->link, 'Itemid=') === false)) {
						// if this is an internal Joomla link, ensure the Itemid is set
						$readall_link .= '&Itemid=' . $menuitem->id;
					}
					break;
				case 'alias': $readall_link = 'index.php?Itemid=' . $menuitem->id; break;
				default: $readall_link = $menuitem->link . '&Itemid=' . $menuitem->id;
			}

			$readall_link_label = trim($params->get('readall_link_lbl', '')) == '' ? $menuitem->title : trim($params->get('readall_link_lbl'));
		}
	}

	$pos_readall = $params->get('readall_pos', 'last');

	// read-more skinning

	$extrareadmorestyle = ''; // keep to avoid template overrides to crash
	$extrareadmoreclass = '';

    $extrareadmorelinkclass = trim($params->get('readmore_classes', ''));

	$read_more_style = $params->get('readmore_style', '');

	if ($read_more_style == 'bootstrap' && $bootstrap_version > 0) {
		$extrareadmorelinkclass .= ' btn';
		$extrareadmorelinkclass .= ' '.SYWUtilities::getBootstrapProperty($params->get('readmore_type', 'btn-default'), $bootstrap_version);
		$extrareadmorelinkclass = trim($extrareadmorelinkclass);
		if ($params->get('readmore_size', '')) {
            $extrareadmorelinkclass .= ' '.SYWUtilities::getBootstrapProperty('btn-'.$params->get('readmore_size', ''), $bootstrap_version);
		}
	}

	$read_more_align = $params->get('readmore_align', '');
	if ($read_more_align == 'btn-block') { // for backward compatibility
		$read_more_align = 'justify';
	}

	switch ($read_more_align) {
		case 'left': $extrareadmoreclass .= ' linkleft'; break;
		case 'right': $extrareadmoreclass .= ' linkright'; break;
		case 'center': $extrareadmoreclass .= ' linkcenter'; break;
		case 'justify': $extrareadmoreclass .= ' linkjustify';
			if ($read_more_style == 'bootstrap' && $bootstrap_version > 0) {
			    $extrareadmorelinkclass .= ' '.SYWUtilities::getBootstrapProperty('btn-block', $bootstrap_version);
			}
	}

	// end read-more skinning

	// category skinning

	if ($show_category) {

		$extracategorystyle = ''; // keep to avoid template overrides to crash
		$extracategoryclass = '';
		$extracategorylinkclass = '';
		$extracategorynolinkclass = '';

		$cat_read_more_classes = trim($params->get('cat_readmore_classes', ''));
		if ($cat_read_more_classes) {
			if ($link_category) {
				$extracategorylinkclass = $cat_read_more_classes;
			} else {
				$extracategorynolinkclass = $cat_read_more_classes;
			}
		}

		$cat_read_more_style = $params->get('cat_readmore_style', '');

		if ($cat_read_more_style == 'bootstrap' && $bootstrap_version > 0 && $link_category) {

			$extracategorylinkclass .= ' btn';
			$extracategorylinkclass .= ' '.SYWUtilities::getBootstrapProperty($params->get('cat_readmore_type', 'btn-default'), $bootstrap_version);

			$cat_read_more_size = SYWUtilities::getBootstrapProperty('btn-'.$params->get('cat_readmore_size', ''), $bootstrap_version);
			if ($cat_read_more_size) {
			    $extracategorylinkclass .= ' '.$cat_read_more_size;
			}
		} else {
		    if ($cat_read_more_classes == '') {
		        $extracategoryclass .= ' nostyle';
		    }
		}

		switch ($params->get('cat_readmore_align', '')) {
			case 'left': $extracategoryclass .= ' linkleft'; break;
			case 'right': $extracategoryclass .= ' linkright'; break;
			case 'center': $extracategoryclass .= ' linkcenter'; break;
			case 'justify': $extracategoryclass .= ' linkjustify';
				if ($cat_read_more_style == 'bootstrap' && $bootstrap_version > 0) {
				    $extracategorylinkclass .= ' '.SYWUtilities::getBootstrapProperty('btn-block', $bootstrap_version);
				}
		}

		$extracategorylinkclass = trim($extracategorylinkclass);

		if ($extracategorynolinkclass) {
			$extracategorynolinkclass = ' class="'.$extracategorynolinkclass.'"';
		}

// 		if ($link_category) {
// 		    $category_additional_attributes = '';
// 		    if ($category_link_tooltip) {
// 		        if ($extracategorylinkclass) {
// 		            $extracategorylinkclass = ' '.$extracategorylinkclass;
// 		        }
// 		        $category_additional_attributes = ' title="'.$cat_label.'" class="hasTooltip'.$extracategorylinkclass.'"';
// 		    } else {
// 		        if ($extracategorylinkclass) {
// 		            $category_additional_attributes = ' class="'.$extracategorylinkclass.'"';
// 		        }
// 		    }
// 		}

		$article_count_classes = SYWUtilities::getBootstrapProperty('label', $bootstrap_version);
		$article_count_classes .= ' '.SYWUtilities::getBootstrapProperty('label-info', $bootstrap_version);
	}

	// end category skinning

	// readall skinning

	if (!empty($readall_link)) {

		$extrareadallstyle = ''; // keep to avoid template overrides to crash
		$extrareadallclass = '';
		$extrareadalllinkclass = '';

		$readall_classes = trim($params->get('readall_classes', ''));
		if ($readall_classes) {
			$extrareadalllinkclass = $readall_classes;
		}

		$readall_style = $params->get('readall_style', '');

		if ($readall_style == 'bootstrap' && $bootstrap_version > 0) {

		    $extrareadalllinkclass .= ' btn';
		    $extrareadalllinkclass .= ' '.SYWUtilities::getBootstrapProperty($params->get('readall_type', 'btn-default'), $bootstrap_version);

		    $readall_size = SYWUtilities::getBootstrapProperty('btn-'.$params->get('readall_size', ''), $bootstrap_version);
		    if ($readall_size) {
		        $extracategorylinkclass .= ' '.$readall_size;
		    }
		}

		switch ($params->get('readall_align', '')) {
			case 'left': $extrareadallclass .= ' linkleft'; break;
			case 'right': $extrareadallclass .= ' linkright'; break;
			case 'center': $extrareadallclass .= ' linkcenter'; break;
			case 'justify':
				$extrareadallclass .= ' linkjustify';
				if ($readall_style == 'bootstrap' && $bootstrap_version > 0) {
				    $extrareadalllinkclass .= ' '.SYWUtilities::getBootstrapProperty('btn-block', $bootstrap_version);
				}
		}

		$readall_additional_attributes = '';
		if ($readall_link_tooltip) {
		    $readall_additional_attributes = ' title="'.$readall_link_label.'" class="hasTooltip '.$extrareadalllinkclass.'"';
		} else {
		    if ($extrareadalllinkclass) {
		        $readall_additional_attributes = ' class="'.$extrareadalllinkclass.'"';
		    }
		}
	}

	// end readall skinning

	// Bootstrap compatible alerts
	// not used anymore, keep for backward compatibility
	$alert_info_classes = 'alert '.SYWUtilities::getBootstrapProperty('alert-info', $bootstrap_version);
	$alert_error_classes = 'alert '.SYWUtilities::getBootstrapProperty('alert-error', $bootstrap_version);

	// downgrading styles // keep for backward compatibility with old template files

	$leading_items_count = 0;
	$remove_head = false;
	$remove_text = false;
	$remove_details = false;

	// head

	$head_type = $params->get('head_type', 'none');
	$head_width = $params->get('head_w', 64);
	$head_height = $params->get('head_h', 64);
	$maintain_height = $params->get('maintain_height', 0);

	$show_image = false;
	$show_calendar = false;

	$image_types = array('image', 'imageintro', 'imagefull', 'allimagesasc', 'allimagesdesc');
	if (in_array($head_type, $image_types)) {
		$show_image = true;
	} else if ($head_type == 'calendar') {
		$show_calendar = true;
	}

	// parameters image

	if ($show_image) {

		$border_width_pic = $params->get('border_w', 0);
		$shadow_width_pic = $params->get('sh_w_pic', 0);
		$shadow_type_pic = $params->get('sh_type', 's');

		$head_width = $head_width - $border_width_pic * 2;
		$head_height = $head_height - $border_width_pic * 2;

		$hover_effect = $params->get('hover_effect', 'none');
		if (strval($hover_effect) == '0') { // for backward compatibility
			$hover_effect = 'none';
		} else if (strval($hover_effect) == '1') {
			$hover_effect = 'shrink';
		}
		if ($hover_effect != 'none') {
			$hover_effect = 'hvr-'.$hover_effect;
			//SYWStylesheets::load2DTransitions();
			$transition_method = SYWStylesheets::getTransitionMethod($hover_effect);
			SYWStylesheets::$transition_method();
		}
	}

	// parameters calendar

	$extracalendarclass = 'noimage';
	if ($show_calendar) {
		if ($params->get('cal_bg', '')) {
			$extracalendarclass = 'image';
		}

		// for backward compatibility, in case there are overrides, avoids crashes
		$date_params_keys = array('', 'update');
		$date_params_values = array('', 'update' => 'Please update override');
		$weekday_format = $month_format = $day_format = $time_format = '';
	}

	// animation / pagination

	$animation = '';
	$pagination = $params->get('pagination', '');

	if (!empty($pagination)) { // pagination only
		$animation = 'justpagination';
	}

	if ($animation) {

		//HTMLHelper::_('jquery.framework');

		LNEHelper::loadAnimationLibrary($animation, $load_remotely);

		$pagination_position_type = $params->get('pagination_pos', 'below');

		$pagination_position = '';
		$pagination_position_top = 'top';
		$pagination_position_bottom = 'bottom';

		if (!empty($pagination)) {

			if ($pagination_position_type == 'around') {
				if ($items_align == 'v') {
					$pagination_position_top = 'up';
					$pagination_position_bottom = 'down';
				} else {
					$pagination_position_top = 'left';
					$pagination_position_bottom = 'right';
				}
			}
		}

		$prev_type = $params->get('prev_type', '');
		$label_prev = $prev_type == 'prev' ? Text::_('JPREV') : ($prev_type == 'label' ? trim($params->get('label_prev', '')) : '');

		$next_type = $params->get('next_type', '');
		$label_next = $next_type == 'next' ? Text::_('JNEXT') : ($next_type == 'label' ? trim($params->get('label_next', '')) : '');

		$prev_next = true;
		if ($pagination == 'p' || $pagination == 's') {
			$prev_next = false;
		}

		// begin - to only load icon font when necessary

		switch ($params->get('pagination')) {
			case 'pn': case 'ppn': case 'psn': $arrows = true; break;
			default: $arrows = false;
		}

		if ($arrows && (empty($label_prev) || empty($label_next))) {
			SYWFonts::loadIconFont();
		}

		// end - to only load icon font when necessary

		$extra_pagination_classes = ''; // keep to prevent overrides to crash

		$pagination_style = $params->get('pagination_style', '');

		$pagination_size = '';
		if ($pagination_style && $bootstrap_version > 0) { // Bootstrap is selected
		    $pagination_size = SYWUtilities::getBootstrapProperty('pagination-'.$params->get('pagination_size', ''), $bootstrap_version);
		}

		$pagination_align = '';
		if ($pagination_style && $bootstrap_version > 0) { // Bootstrap is selected
		    $pagination_align = SYWUtilities::getBootstrapProperty('pagination-'.$params->get('pagination_align', 'center'), $bootstrap_version);
		}

		$cache_anim_js = new JSAnimationFileCache('mod_latestnewsenhanced', $params);

		if ($generate_inline_scripts) {

			$doc->addScriptDeclaration($cache_anim_js->getBuffer());

		} else {

			$result = $cache_anim_js->cache('animation_'.$module->id.'.js', $clear_header_files_cache);

			if ($result) {
				$doc->addScript(Uri::base(true).'/media/cache/mod_latestnewsenhanced/animation_'.$module->id.'.js');
			}
		}
	} else {
		// remove animation.js if it exists
		if (File::exists(JPATH_SITE . '/media/cache/mod_latestnewsenhanced/animation_'.$module->id.'.js')) {
			File::delete(JPATH_SITE . '/media/cache/mod_latestnewsenhanced/animation_'.$module->id.'.js');
		}
	}

	if ((empty($animation) || $animation == 'justpagination') && $item_width_unit == '%' && !empty($min_item_width)) {

		// add items responsiveness	when not in an animation other than pagination

		HTMLHelper::_('jquery.framework');

		$cache_js = new JSFileCache('mod_latestnewsenhanced', $params);

		if ($generate_inline_scripts) {

			$doc->addScriptDeclaration($cache_js->getBuffer());

		} else {

			$result = $cache_js->cache('style_'.$module->id.'.js', $clear_header_files_cache);

			if ($result) {
				$doc->addScript(Uri::base(true).'/media/cache/mod_latestnewsenhanced/style_'.$module->id.'.js');
			}
		}
	} else {
		// remove style.js if it exists
		if (File::exists(JPATH_SITE . '/media/cache/mod_latestnewsenhanced/style_'.$module->id.'.js')) {
			File::delete(JPATH_SITE . '/media/cache/mod_latestnewsenhanced/style_'.$module->id.'.js');
		}
	}

	if (File::exists(JPATH_ROOT.'/media/mod_latestnewsenhanced/css/substitute_styles.css') || File::exists(JPATH_ROOT.'/media/mod_latestnewsenhanced/css/substitute_styles-min.css')) {
		LNEHelper::loadUserStylesheet(true);

		// remove style.css if it exists
		if (File::exists(JPATH_SITE . '/media/cache/mod_latestnewsenhanced/style_'.$module->id.'.css')) {
			File::delete(JPATH_SITE . '/media/cache/mod_latestnewsenhanced/style_'.$module->id.'.css');
		}
	} else {

		// extra styles

		$extra_styles = trim($params->get('style_overrides', ''));
		if (!empty($extra_styles)) {
			$extra_styles .= ' ';
		}

		if ($show_calendar) {
			$extra_styles .= LNECalendarHelper::getCalendarInlineStyles($params, $class_suffix);
		}

		// font details
		$font_details = $params->get('details_font', '');
		if (!empty($font_details)) {
			$font_details = str_replace('\'', '"', $font_details); // " lost, replaced by '

			$google_font = SYWUtilities::getGoogleFont($font_details); // get Google font, if any
			if ($google_font) {
				SYWFonts::loadGoogleFont($google_font);
			}

			$extra_styles .= '#lnee_' . $class_suffix . ' .newsextra { font-family: ' . $font_details . '} ';
		}

		if (!empty($extra_styles)) {
			$extra_styles = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $extra_styles); // minify the CSS code
		}

		$cache_css = new CSSFileCache('mod_latestnewsenhanced', $params);
		$cache_css->addDeclaration($extra_styles);
		$result = $cache_css->cache('style_'.$module->id.'.css', $clear_header_files_cache);

		if ($result) {
			$doc->addStyleSheet(Uri::base(true).'/media/cache/mod_latestnewsenhanced/style_'.$module->id.'.css');
		}

		LNEHelper::loadCommonStylesheet();

		if (File::exists(JPATH_ROOT.'/media/mod_latestnewsenhanced/css/common_user_styles.css') || File::exists(JPATH_ROOT.'/media/mod_latestnewsenhanced/css/common_user_styles-min.css')) {
			LNEHelper::loadUserStylesheet();
		}
	}

	if ($params->get('allow_edit', 0)) {
		SYWFonts::loadIconFont();
	}

	// call the layout
	require ModuleHelper::getLayoutPath('mod_latestnewsenhanced', $params->get('layout', 'default'));
}
?>