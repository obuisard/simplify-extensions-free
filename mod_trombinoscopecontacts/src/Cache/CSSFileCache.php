<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrombinoscopeContacts\Site\Cache;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use SYW\Library\HeaderFilesCache;

class CSSFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$prefix = '#te_' . $params->get('suffix');
		$variables[] = 'prefix';

		$theme = $params->get('theme', 'original');
		$variables[] = 'theme';

		$bootstrap_version = $params->get('bootstrap_version', 5);
		$variables[] = 'bootstrap_version';

		// show

		$show_links = $params->get('show_links', false);
		$variables[] = 'show_links';

		$links_position = $params->get('links_position', 'bottom');
		$variables[] = 'links_position';

		$show_vcard = $params->get('s_v', false);
		$variables[] = 'show_vcard';

		$show_picture = $params->get('s_pic', true);
		$variables[] = 'show_picture';

		$show_text = true;
		$variables[] = 'show_text';

		// card

		$card_width = $params->get('card_w', 100);
		$variables[] = 'card_width';

		$card_width_unit = $params->get('card_w_u', '%');
		$variables[] = 'card_width_unit';

		$card_height = $params->get('card_h', '');
		$variables[] = 'card_height';

		$card_min_width = trim($params->get('min_card_w', ''));
		$variables[] = 'card_min_width';

		$card_max_width = trim($params->get('max_card_w', ''));
		$variables[] = 'card_max_width';

		$space_between_cards = $params->get('card_spacebetween', '5');
		$variables[] = 'space_between_cards';

		$bgimage = $params->get('bgimage', '');		
		
		if ($bgimage) {
		    $image_object = HTMLHelper::cleanImageURL($bgimage);
		    $bgimage = $image_object->url;
		}		
		
		$variables[] = 'bgimage';

		$bgcolor1 = trim($params->get('bgcolor1', ''));
		$variables[] = 'bgcolor1';

		$bgcolor2 = trim($params->get('bgcolor2', ''));
		$variables[] = 'bgcolor2';

		$card_shadow = $params->get('card_shadow', false);
		$variables[] = 'card_shadow';

		$card_radius = $params->get('card_r', 0);
		$variables[] = 'card_radius';

		$card_border_width = $params->get('card_border_w', 0);
        $variables[] = 'card_border_width';

        $card_border_color = trim($params->get('card_border_c', ''));
        $variables[] = 'card_border_color';

		// picture

		$border_width = $params->get('border_w', 0);
		$variables[] = 'border_width';

		$border_radius = $params->get('border_r', 0);
		$variables[] = 'border_radius';

		$picture_width = $params->get('pic_w', 100);
		$picture_height = $params->get('pic_h', 120);

		$picture_width = $picture_width - $border_width * 2;
		$variables[] = 'picture_width';

		$picture_height = $picture_height - $border_width * 2;
		$variables[] = 'picture_height';

		$keep_height = $params->get('k_height', 1);
		if ($params->get('crop_pic', 1)) {
			$keep_height = 0; // all same height, no need to force height
		}
		$variables[] = 'keep_height';

		$pic_bgcolor = trim($params->get('pic_bgcolor', '')) != '' ? trim($params->get('pic_bgcolor', '')) : 'transparent';
		$variables[] = 'pic_bgcolor';

		$picture_shadow = $params->get('pic_shadow', false);
		$variables[] = 'picture_shadow';

		// TODO shadow color/width ?

		$picture_shadow_width = 0;
		if ($picture_shadow) {
			$picture_shadow_width = 8;
		}
		$variables[] = 'picture_shadow_width';

		$effect_extra_margin = $params->get('pic_hover_m', 0);
		$variables[] = 'effect_extra_margin';

		$hover_extra_margin = 0; // for backward compatibility with all themes
		$variables[] = 'hover_extra_margin';

		$overflow = $params->get('overflow', false);
		$variables[] = 'overflow';

		$picture_rotation = $params->get('rotate', 0);
		$variables[] = 'picture_rotation';

		$picture_scale = $params->get('scale', 1);
		$variables[] = 'picture_scale';

		// image filters

		$filters = array();

		if ($params->get('crop_pic', 1)) {

			$image_filters = $params->get('image_filters', null);
			if (!empty($image_filters) && is_object($image_filters)) {
				foreach ($image_filters as $image_filter) {
					if ($image_filter->filter && strpos($image_filter->filter, '_css') !== false) {
						$filters[] = str_replace('_css', '', $image_filter->filter);
					}
				}
			}
		} else {
			$image_filter = $params->get('filter_original', 'none');
			if (strpos($image_filter, '_css') !== false) {
				$filters[] = str_replace('_css', '', $image_filter);
			}
		}

		if (!empty($filters)) {

			$css_filters = '';
			foreach ($filters as $filter) {
				switch($filter) {
					case 'sepia': $css_filters .= 'sepia(100%) '; break;
					case 'grayscale': $css_filters .= 'grayscale(100%) '; break;
					case 'negate': $css_filters .= 'invert(100%) ';
				}
			}

			if ($css_filters) {
				$variables[] = 'css_filters';
			}
		}

		// text

		$font_override = false;
		if (trim($params->get('font', '')) != '') {
		    $font_override = true;
		}
		$variables[] = 'font_override';

		$font_size = $params->get('font_s', '14');
		$variables[] = 'font_size';

		$font_color = trim($params->get('fontcolor', ''));
		$variables[] = 'font_color';

		$line_spacing = $params->get('line_spacing', '1.6');
		$variables[] = 'line_spacing';

		$iconfont_size = $params->get('ifont_s', '1');
		$variables[] = 'iconfont_size';

		$iconfont_color = trim($params->get('ifont_c', ''));
		$variables[] = 'iconfont_color';

		$iconfont_bgcolor = $params->get('ifont_bgc', '');
		$variables[] = 'iconfont_bgcolor';

		$iconfont_link_size = $params->get('ifont_s_links', 1);
		$variables[] = 'iconfont_link_size';

		$iconfont_link_color = $params->get('ifont_c_links', '');
		$variables[] = 'iconfont_link_color';

		$iconfont_link_bgcolor = $params->get('ifont_bgc_links', '');
		$variables[] = 'iconfont_link_bgcolor';

		$label_width = $params->get('lbl_w', '0');
		$variables[] = 'label_width';

		$label_color = $params->get('lbl_c', '');
		$variables[] = 'label_color';

		$label_bgcolor = $params->get('lbl_bgc', '');
		$variables[] = 'label_bgcolor';

		// animation

		$animated = $params->get('carousel_config', 'none') !== 'none' ? true : false;
		$variables[] = 'animated';

		$bootstrap_arrows = $params->get('arrowstyle', '') === '' ? false : true;
		$variables[] = 'bootstrap_arrows';

		$arrow_size = $params->get('arrowsize', 1);
		$variables[] = 'arrow_size';

		$arrow_offset = $params->get('arrowoffset', 0);
		$variables[] = 'arrow_offset';

		$show_arrows = $params->get('arrows', 'none') !== 'none' ? true : false;
		$variables[] = 'show_arrows';

		$show_pages = $params->get('includepages', 0);
		$variables[] = 'show_pages';

		// calculated variables

		$border_radius_img = $border_radius;
		if ($border_width > 0 && $border_radius >= $border_width) {
			$border_radius_img -= $border_width;
		}
		$variables[] = 'border_radius_img';

		// calculate margins if card width is in %
		$margin_in_perc = 0;
		if ($card_width_unit == '%') {
			$cards_per_row = (int)(100 / $card_width);
			$left_for_margins = 100 - ($cards_per_row * $card_width);
			$margin_in_perc = $left_for_margins / ($cards_per_row * 2);
		}
		$variables[] = 'margin_in_perc';

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
		$this->sendHttpHeaders('css');

		include JPATH_ROOT . '/media/mod_trombinoscopecontacts/styles/style.css.php';
		include JPATH_ROOT . '/media/mod_trombinoscopecontacts/styles/themes/' . $theme . '/style.css.php';

		// image CSS filters

		if (isset($css_filters)) {
			echo $prefix . ' .picture img { -webkit-filter: ' . rtrim($css_filters) . '; filter: ' . rtrim($css_filters) . '; }';
		}

		return $this->compress(ob_get_clean());
	}

}