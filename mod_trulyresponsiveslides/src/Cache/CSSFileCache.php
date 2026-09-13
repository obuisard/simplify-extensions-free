<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Cache;

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

		$bootstrap_version = $params->get('bootstrap_version', 5);
		$variables[] = 'bootstrap_version';

		// sizes

		$max_width = trim($params->get('max_w', ''));
		$variables[] = 'max_width';

		$thumb_width = $params->get('thumb_w', 80);
		$variables[] = 'thumb_width';

// 		$thumb_width_percent = '';
// 		if (!empty($max_width)) {
// 			$thumb_width_percent = 100 / floor(floatval($max_width) / floatval($thumb_width));
// 		}
		$thumb_width_percent = $params->get('thumb_width_percent', '');
		$variables[] = 'thumb_width_percent';

		$thumb_height = $params->get('thumb_h', 60);
		$variables[] = 'thumb_height';

		// border

		$border_width = $params->get('border_w', 4);
		$variables[] = 'border_width';

		// caption

		$caption_top = $params->get('caption_top', 7);
		$variables[] = 'caption_top';

		$caption_right = $params->get('caption_right', 7);
		$variables[] = 'caption_right';

		$caption_bottom = $params->get('caption_bottom', 7);
		$variables[] = 'caption_bottom';

		$caption_left = $params->get('caption_left', 7);
		$variables[] = 'caption_left';

		// text

		$padding = $params->get('text_padding', 1);
		$variables[] = 'padding';

		// opacity

		$opacity = $params->get('bg_opacity', 60);
		if ($opacity < 0) {
			$opacity = 0;
		}
		if ($opacity > 100) {
			$opacity = 100;
		}
		$variables[] = 'opacity';

		$opacity_color = trim($params->get('opacity_color', '#000000'));
		$variables[] = 'opacity_color';

		$o_c_r = hexdec(substr($opacity_color, 1, 2));
		$variables[] = 'o_c_r';
		$o_c_g = hexdec(substr($opacity_color, 3, 2));
		$variables[] = 'o_c_g';
		$o_c_b = hexdec(substr($opacity_color, 5, 2));
		$variables[] = 'o_c_b';

		// arrows
		
		$font_family = (version_compare(JVERSION, '4.9.0', 'lt')) ? 'Font Awesome 5 Free' : 'Font Awesome 6 Free';
		$variables[] = 'font_family';

		$arrows_c = trim($params->get('arrow_c', '#000000'));
		$variables[] = 'arrows_c';

		$arrows_bgc = trim($params->get('arrow_bgc', '')) != '' ? trim($params->get('arrow_bgc', '')) : '';
		$variables[] = 'arrows_bgc';

		$arrows_bgr = $params->get('arrow_bgr', 20);
		if ($arrows_bgr > 20) {
			$arrows_bgr = 20;
		}
		if ($arrows_bgr < 0) {
			$arrows_bgr = 0;
		}
		$variables[] = 'arrows_bgr';

		$arrows_shadow = $params->get('arrow_shadow', 0);
		if ($arrows_shadow < 0) {
			$arrows_shadow = 0;
		}
		$variables[] = 'arrows_shadow';

		// thumbnail colors

		$current_color = trim($params->get('autohide_current_color', '#ff0000'));
		$variables[] = 'current_color';

		$other_color = trim($params->get('autohide_other_color', '#999999'));
		$variables[] = 'other_color';

		// dots

		$dot_navigation = $params->get('dot_navigation', 'under');
		$variables[] = 'dot_navigation';

		// transitions

		$transition = $params->get('type', 'fade');
		$variables[] = 'transition';

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
// 		}

		// set the header
		//$this->sendHttpHeaders('css');

		include JPATH_ROOT . '/media/mod_trulyresponsiveslides/styles/style.css.php';

		if ($transition == 'zoomout') {
			echo '#slider_' . $suffix . ' .slides { overflow: hidden }';
			echo '#slider_' . $suffix . ' .slides li img { -webkit-transition: -webkit-transform 1.5s ease-in-out; transition: -webkit-transform 1.5s ease-in-out; -o-transition: transform 1.5s ease-in-out; transition: transform 1.5s ease-in-out; }';
			echo '#slider_' . $suffix . ' .slides li.flex-active-slide img { -webkit-transform: scale(1.225); -ms-transform: scale(1.225); transform: scale(1.225); }';
		} else if ($transition == 'zoomin') {
			echo '#slider_' . $suffix . ' .slides { overflow: hidden }';
			echo '#slider_' . $suffix . ' .slides li img { -webkit-transition: -webkit-transform 1.5s ease-in-out; transition: -webkit-transform 1.5s ease-in-out; -o-transition: transform 1.5s ease-in-out; transition: transform 1.5s ease-in-out; -webkit-transform: scale(1.225); -ms-transform: scale(1.225); transform: scale(1.225); }';
			echo '#slider_' . $suffix . ' .slides li.flex-active-slide img { -webkit-transform: scale(1); -ms-transform: scale(1); transform: scale(1); }';
		}

		return $this->compress(ob_get_clean());
	}

}