<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Cache;

defined('_JEXEC') or die;

use SYW\Library\HeaderFilesCache;

class CSSFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$suffix = '#lnee_'.$params->get('suffix');
		$variables[] = 'suffix';

		$bootstrap_version = $params->get('bootstrap_version', 5);
		$variables[] = 'bootstrap_version';

		$overall = $params->get('overall_style', 'original');
		$variables[] = 'overall';

		$colortheme = $params->get('color_theme', '');
		$variables[] = 'colortheme';

		$horizontal = ($params->get('align', 'v') === 'h') ? true : false;
		$variables[] = 'horizontal';

		$items_align = $params->get('items_align', 'c');
		$variables[] = 'items_align';

		$items_valign_row = $params->get('items_valign_h', 'fs');
		$variables[] = 'items_valign_row';

		$items_valign_col = $params->get('items_valign_v', 'c');
		$variables[] = 'items_valign_col';

		// items width and height

		$items_height = trim($params->get('items_h', ''));
		$variables[] = 'items_height';
		$items_width = trim($params->get('items_w', ''));
		$variables[] = 'items_width';

		$item_width = trim($params->get('item_w', 100));
		$item_width_unit = $params->get('item_w_u', 'percent');

		if ($item_width_unit == 'percent') {
			$item_width_unit = '%';
		}

		$variables[] = 'item_width_unit';

		if ($item_width_unit == '%') {
			if ($item_width <= 0 || $item_width > 100) {
				$item_width = 100;
			}
		} else {
			if ($item_width < 0) {
				$item_width = 0;
			}
		}

		$variables[] = 'item_width';

		$item_min_width = trim($params->get('min_item_w', ''));
		$variables[] = 'item_min_width';

		$item_max_width = trim($params->get('max_item_w', ''));
		$variables[] = 'item_max_width';

		$space_between_items = $params->get('item_spacebetween', '0');
		$variables[] = 'space_between_items';

		$margin_in_perc = 0;
		if ($item_width_unit == '%') {
			$news_per_row = (int)(100 / $item_width);
			$left_for_margins = 100 - ($news_per_row * $item_width);
			$margin_in_perc = $left_for_margins / ($news_per_row * 2);
		}

		$variables[] = 'margin_in_perc';

		// body parameters

		$maintain_height = $params->get('maintain_height', 0);
		$variables[] = 'maintain_height';

		$bgcolor_body = trim($params->get('bgcolor', '')) != '' ? trim($params->get('bgcolor')) : 'transparent';
		$variables[] = 'bgcolor_body';

		$border_width_body = $params->get('item_border_w', 0);
		$variables[] = 'border_width_body';

		$border_color_body = trim($params->get('item_border_c', ''));
		$variables[] = 'border_color_body';

		$border_radius_body = $params->get('item_border_r', 0);
		$variables[] = 'border_radius_body';

		$shadow_body = $params->get('item_shadow', 'none');
		$variables[] = 'shadow_body';

		$padding_body = $params->get('d_to_b', 0);
		$variables[] = 'padding_body';

		$padding_head = trim($params->get('space_head', ''));
		if ($padding_head !== '' && is_numeric($padding_head)) {
		    $padding_head = intval($padding_head); // make sure we have values
		}
		$variables[] = 'padding_head';

		$padding_info = trim($params->get('space_body', ''));
		if ($padding_info !== '' && is_numeric($padding_info)) {
		    $padding_info = intval($padding_info); // make sure we have values
		}
		$variables[] = 'padding_info';

		$content_align = $params->get('content_align', '');
		$variables[] = 'content_align';

		$font_color_body = trim($params->get('item_color', ''));
		if ($font_color_body == 'transparent') {
			$font_color_body = '';
		}
		$variables[] = 'font_color_body';

		$link_color_body = trim($params->get('item_l_color', ''));
		if ($link_color_body == 'transparent') {
			$link_color_body = '';
		}
		$variables[] = 'link_color_body';

		$link_color_hover_body = trim($params->get('item_l_color_h', ''));
		if ($link_color_hover_body == 'transparent') {
			$link_color_hover_body = '';
		}
		$variables[] = 'link_color_hover_body';

		$force_title_one_line = $params->get('force_one_line', 0);
		$variables[] = 'force_title_one_line';

		$font_ref_body = $params->get('f_r_body', 14);
		$variables[] = 'font_ref_body';

		$wrap = $params->get('wrap', 0);
		$variables[] = 'wrap';

		// extra details

		$font_size_details = $params->get('details_fontsize', 80);
		$variables[] = 'font_size_details';

		$details_line_spacing = $params->get('details_line_spacing', array('', 'px'));
		$variables[] = 'details_line_spacing';

		$details_font_color = trim($params->get('details_color', ''));
		if ($details_font_color == 'transparent') {
			$details_font_color = '';
		}
		$variables[] = 'details_font_color';

		$iconfont_color = trim($params->get('iconscolor', ''));
		if ($iconfont_color == 'transparent') {
			$iconfont_color = '';
		}
		$variables[] = 'iconfont_color';

		// rating

		$star_color = trim($params->get('star_color', '#000000'));
		$variables[] = 'star_color';

		// head

		$head_align = $params->get('head_align', '');

		if (in_array($params->get('text_align', 'r'), array('l', 'r', 'lr', 'rl'))) {
			$head_align = '';
		}

		$variables[] = 'head_align';

		$head_width = $params->get('head_w', 64);
		$head_height = $params->get('head_h', 64);

		$head_type = $params->get('head_type', 'none');

		// image

		$image = false;
		
		$bgcolor = trim($params->get('imagebgcolor', '')) != '' ? trim($params->get('imagebgcolor')) : 'transparent';
		$variables[] = 'bgcolor';
		
		$pic_shadow_width = $params->get('sh_w_pic', 0);
		$variables[] = 'pic_shadow_width';
		
		$pic_border_width = $params->get('border_w', 0);
		$variables[] = 'pic_border_width';
		
		$pic_border_radius = $params->get('border_r_pic', 0);
		$variables[] = 'pic_border_radius';
		
		$pic_border_color = trim($params->get('border_c_pic', '#fff'));
		$variables[] = 'pic_border_color';

		$image_types = array('image', 'imageintro', 'imagefull', 'allimagesasc', 'allimagesdesc');

		if (in_array($head_type, $image_types)) {

			// make sure the variables are available in case no head is selected but the theme is image-only

			$head_width = $head_width - $pic_border_width * 2;
			$head_height = $head_height - $pic_border_width * 2;

			$image = true;
		}
		$variables[] = 'image';

		$filter = $params->get('filter', 'none');
		if (!$params->get('create_thumb', 1)) {
			$filter = $params->get('filter_original', 'none');
		}

		if (strpos($filter, '_css') !== false) {
			$filter = str_replace('_css', '', $filter);
			$variables[] = 'filter';
		}

		// calendar

		$calendar = '';
		if ($head_type == 'calendar') {

			$color = trim($params->get('c1', '#3D3D3D'));
			$variables[] = 'color';
			$bgcolor1 = trim($params->get('bgc11', '')) != '' ? trim($params->get('bgc11')) : 'transparent';
			$variables[] = 'bgcolor1';
			$bgcolor2 = trim($params->get('bgc12', '')) != '' ? trim($params->get('bgc12')) : 'transparent';
			$variables[] = 'bgcolor2';

			$color_top = trim($params->get('c2', '#494949'));
			$variables[] = 'color_top';
			$bgcolor1_top = trim($params->get('bgc21', '')) != '' ? trim($params->get('bgc21')) : 'transparent';
			$variables[] = 'bgcolor1_top';
			$bgcolor2_top = trim($params->get('bgc22', '')) != '' ? trim($params->get('bgc22')) : 'transparent';
			$variables[] = 'bgcolor2_top';

			$color_bottom = trim($params->get('c3', '#494949'));
			$variables[] = 'color_bottom';
			$bgcolor1_bottom = trim($params->get('bgc31', '')) != '' ? trim($params->get('bgc31')) : 'transparent';
			$variables[] = 'bgcolor1_bottom';
			$bgcolor2_bottom = trim($params->get('bgc32', '')) != '' ? trim($params->get('bgc32')) : 'transparent';
			$variables[] = 'bgcolor2_bottom';

			$cal_shadow_width = $params->get('sh_w', 0);
			$variables[] = 'cal_shadow_width';
			$cal_border_width = $params->get('border_w_cal', 0);
			$variables[] = 'cal_border_width';
			$cal_border_radius = $params->get('border_r', 0);
			$variables[] = 'cal_border_radius';
			$cal_border_color = trim($params->get('border_c_cal', '#000000'));
			$variables[] = 'cal_border_color';

			$font_ref_cal = $params->get('f_r', 14);
			$variables[] = 'font_ref_cal';
			$font_ratio = 1; // floatval($head_height) / 80; // 1em base for a height of 80px
			$variables[] = 'font_ratio';

			$calendar = $params->get('cal_style', 'original');
		}
		$variables[] = 'calendar';

		// icon

		$icon = false;
		$variables[] = 'icon';

		// head width and height

		$variables[] = 'head_width';
		$variables[] = 'head_height';

		// animation and pagination

		$animation = $params->get('anim', '');
		$pagination = $params->get('pagination', '');
		if (!empty($pagination) && empty($animation)) { // pagination only
			$animation = 'justpagination';
		}

		$variables[] = 'animation';

		if (!empty($animation)) {

			$symbols = false;
			$arrows = false;
			$pages = false;
			switch ($params->get('pagination')) {
				case 'p': $pages = true; break;
				case 's': $symbols = true; break;
				case 'pn': $arrows = true; break;
				case 'ppn': $arrows = true; $pages = true; break;
				case 'psn': $arrows = true; $symbols = true; break;
			}
			$variables[] = 'symbols';
			$variables[] = 'arrows';
			$variables[] = 'pages';

			$pagination_align = $params->get('pagination_align', 'center');
			$variables[] = 'pagination_align';
			$pagination_position = $params->get('pagination_pos', 'below');
			$variables[] = 'pagination_position';
			$pagination_specific_size = $params->get('pagination_specific_size', 1);
			$variables[] = 'pagination_specific_size';
			$pagination_offset = $params->get('pagination_offset', 0);
			$variables[] = 'pagination_offset';
			$pagination_style = $params->get('pagination_style', '');
			$variables[] = 'pagination_style';
		}

		// set all necessary parameters
		$this->params = compact($variables);
	}

	protected function getBuffer()
	{
		// get all necessary parameters
		extract($this->params);

// 		if (function_exists('ob_gzhandler')) { // TODO not tested
// 			ob_start('ob_gzhandler');
// 		} else {
			ob_start();
//		}

		// set the header
		//$this->sendHttpHeaders('css');

		if ($colortheme) {
			include JPATH_ROOT . '/media/mod_latestnewsenhanced/styles/colors/'.$colortheme.'/style.css.php';
		}
		include JPATH_ROOT . '/media/mod_latestnewsenhanced/styles/style.css.php';
		include JPATH_ROOT . '/media/mod_latestnewsenhanced/styles/themes/'.$overall.'/style.css.php';
		if ($calendar) {
			include JPATH_ROOT . '/media/mod_latestnewsenhanced/styles/calendars/'.$calendar.'/style.css.php';
		}
		if ($animation) {
			include JPATH_ROOT . '/media/mod_latestnewsenhanced/styles/animations/'.$animation.'/style.css.php';
		}

		// image CSS filters

		if (isset($filter)) {
			switch($filter) {
				case 'sepia': echo $suffix . ' .newshead .picture img { -webkit-filter: sepia(100%); filter: sepia(100%); }'; break;
				case 'grayscale': echo $suffix . ' .newshead .picture img { -webkit-filter: grayscale(100%); filter: grayscale(100%); }'; break;
				case 'negate': echo $suffix . ' .newshead .picture img { -webkit-filter: invert(100%); filter: invert(100%); }';
			}
		}

		return $this->compress(ob_get_clean());
	}

}