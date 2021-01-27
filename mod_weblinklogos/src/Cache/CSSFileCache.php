<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\WeblinkLogos\Site\Cache;

defined('_JEXEC') or die;

use SYW\Library\HeaderFilesCache;

class CSSFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$suffix = $params->get('suffix');
		$variables[] = 'suffix';

		$bootstrap_version = $params->get('bootstrap_version', 2);
		$variables[] = 'bootstrap_version';

		$items_align = $params->get('items_align', 'c');
		$variables[] = 'items_align';

		$items_valign = $params->get('items_valign', 'fs');
		$variables[] = 'items_valign';

		$items_valign_list = $params->get('items_valign_list', 's');
		$variables[] = 'items_valign_list';

		// card

		$overall_bgcolor = trim($params->get('overallbgcolor', '')) != '' ? trim($params->get('overallbgcolor')) : 'transparent';
		$variables[] = 'overall_bgcolor';

		$font_size = $params->get('fontsize', array('90', '%'));
		$unit = '%';
		if (is_array($font_size)) {
			$unit = $font_size[1];
			$font_size = $font_size[0];
		}
		if ($unit == '%') {
			$font_size = $font_size / 100;
			$unit = 'em';
		}
		$font_size = $font_size . $unit;
		$variables[] = 'font_size';

		$card_shadow = $params->get('card_shadow', false);
		$variables[] = 'card_shadow';

		$shadow_width = 8;
		$variables[] = 'shadow_width';

		$card_radius = $params->get('card_r', 0);
		$variables[] = 'card_radius';

		$card_border_width = $params->get('card_border_w', 0);
		$variables[] = 'card_border_width';

		$card_border_color = trim($params->get('card_border_c', ''));
		$variables[] = 'card_border_color';

		$overall_width = trim($params->get('overall_width', ''));
		$variables[] = 'overall_width';

		$force_width = $params->get('force_width', 1);
		$variables[] = 'force_width';

		$margin_top = $params->get('margin_top', 5);
		if ($card_shadow && $margin_top < $shadow_width) {
			$margin_top = $shadow_width;
		}
		$variables[] = 'margin_top';

		$margin_right = $params->get('margin_right', 5);
		if ($card_shadow && $margin_right < $shadow_width) {
			$margin_right = $shadow_width;
		}
		$variables[] = 'margin_right';

		$margin_bottom = $params->get('margin_bottom', 5);
		if ($card_shadow && $margin_bottom < $shadow_width) {
			$margin_bottom = $shadow_width;
		}
		$variables[] = 'margin_bottom';

		$margin_left = $params->get('margin_left', 5);
		if ($card_shadow && $margin_left < $shadow_width) {
			$margin_left = $shadow_width;
		}
		$variables[] = 'margin_left';

		$padding = $params->get('content_spacing', 10);
		$variables[] = 'padding';

		// logo

		$width = $params->get('width', 120);
		$variables[] = 'width';

		$height = $params->get('height', 40);
		$variables[] = 'height';

		$logo_bgcolor = trim($params->get('logobgcolor', '')) != '' ? trim($params->get('logobgcolor')) : 'transparent';
		$variables[] = 'logo_bgcolor';

		$opacity = $params->get('opacity', 1);
		if ($opacity > 1) {
			$opacity = 1;
		}
		if ($opacity < 0) {
			$opacity = 0;
		}
		$variables[] = 'opacity';

		$restrict_width_to_image = $params->get('restrict_width', 0);
		$variables[] = 'restrict_width_to_image';

		$center_vertically = $params->get('center_vertically', 0);
		$variables[] = 'center_vertically';

		$filter = $params->get('filter', 'none');
		if (strpos($filter, '_css') !== false) {
			$filter = str_replace('_css', '', $filter);
			$variables[] = 'filter';
		}

		$filter_hover = $params->get('filter_hover', 'none');
		if (strpos($filter_hover, '_css') !== false) {
			$filter_hover = str_replace('_css', '', $filter_hover);
			$variables[] = 'filter_hover';
		}

		// text

		$content_align = $params->get('content_align', 'center');
		$variables[] = 'content_align';

		$content_valign = $params->get('content_valign', 'top');
		$variables[] = 'content_valign';

		$text_wrap = $params->get('text_wrap', 1);
		$variables[] = 'text_wrap';

		// animation

		$animated = $params->get('carousel_config', 'none') != 'none' ? true : false;
		$variables[] = 'animated';

		$horizontal = $params->get('carousel_config', 'none') == 'h' ? true : false;
		$variables[] = 'horizontal';

		$bootstrap = $params->get('arrowstyle', '') === 'pagination' ? true : false;
		$variables[] = 'bootstrap';

		$arrow_size = $params->get('arrowsize', 1);
		$variables[] = 'arrow_size';

		$arrow_offset = $params->get('arrowoffset', 0);
		$variables[] = 'arrow_offset';

		$show_arrows = $params->get('arrows', 'none') !== 'none' ? true : false;
		$variables[] = 'show_arrows';

		$show_pages = $params->get('includepages', 0);
		$variables[] = 'show_pages';

		// computed values

		$logo_solo = true;
		if ($params->get('description', 0) || $params->get('title', 0) || $params->get('hits', 0)) {
			$logo_solo = false;
		}
		$variables[] = 'logo_solo';

		$computed_width = $width;

		if ($card_border_width > 0) {
			$computed_width += $card_border_width * 2;
		}

		if ($overall_bgcolor != 'transparent' || $card_shadow || $card_border_width > 0) {
			$computed_width += $padding * 2;
		}

		if ($logo_bgcolor != 'transparent') {
			$computed_width += $padding * 2;
		}

		$variables[] = 'computed_width';

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

		include JPATH_ROOT . '/media/mod_weblinklogos/styles/style.css.php';

		// image CSS filters

		if (isset($filter)) {
			switch($filter) {
				case 'sepia': echo '#weblinklogo_' . $suffix . ' .logo img.original { -webkit-filter: sepia(100%); filter: sepia(100%); }'; break;
				case 'grayscale': echo '#weblinklogo_' . $suffix . ' .logo img.original { -webkit-filter: grayscale(100%); filter: grayscale(100%); }'; break;
				case 'negate': echo '#weblinklogo_' . $suffix . ' .logo img.original { -webkit-filter: invert(100%); filter: invert(100%); }';
			}
		}

		if (isset($filter_hover)) {
			switch($filter_hover) {
				case 'sepia': echo '#weblinklogo_' . $suffix . ' .logo img.hover { -webkit-filter: sepia(100%); filter: sepia(100%); }'; break;
				case 'grayscale': echo '#weblinklogo_' . $suffix . ' .logo img.hover { -webkit-filter: grayscale(100%); filter: grayscale(100%); }'; break;
				case 'negate': echo '#weblinklogo_' . $suffix . ' .logo img.hover { -webkit-filter: invert(100%); filter: invert(100%); }';
			}
		}

		return $this->compress(ob_get_clean());
	}

}