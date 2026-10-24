<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\Content\ArticleDetails\Cache;

// no direct access
defined('_JEXEC') or die;

use SYW\Library\HeaderFilesCache;

class CSSFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$view = $params->get('view', 'article');

		// body parameters

		$font_details = $params->get('d_fs', 80);  // TODO make it $font_size_details
		$variables[] = 'font_details';

		$details_line_spacing = $params->get('details_line_spacing', array('', 'px'));
		$variables[] = 'details_line_spacing';

		$details_font_color = trim($params->get('details_color', '#000000'));
		$variables[] = 'details_font_color';

		$iconfont_color = trim($params->get('iconscolor', '#000000'));
		$variables[] = 'iconfont_color';

		// rating

		$star_color = trim($params->get('star_color', '#000000'));
		$variables[] = 'star_color';

		// share

		$share_color_type = $params->get('share_color', 'none');
		$share_color = false;
		$share_bgcolor = false;
		if ($share_color_type == 'bg') {
			$share_bgcolor = true;
		} else if ($share_color_type == 'icon') {
			$share_color = true;
		}
		$variables[] = 'share_color';
		$variables[] = 'share_bgcolor';

		$share_radius = $params->get('share_r', 0);
		if ($share_radius < 0) {
			$share_radius = 0;
		}
		if ($share_radius > 20) {
			$share_radius = 20;
		}
		$variables[] = 'share_radius';

		// social networks

		$social_networks = $params->get('social_networks', array());
		$variables[] = 'social_networks';

		// head type, width and height

		$head_width = 0;
		$head_height = 0;
		if ($view == 'article') {
			$head_type = $params->get('head_type', 'none');
		} else {
			$head_type = $params->get('lists_head_type', 'none');
		}

		// align details

		$align_details = 'left';
		$footer_align_details = 'left';
		if ($view == 'article') {
			$align_details = $params->get('align_details', 'left');
			$footer_align_details = $params->get('footer_align_details', 'left');
		} else {
			$align_details = $params->get('lists_align_details', 'left');
		}
		$variables[] = 'align_details';
		$variables[] = 'footer_align_details';

		// calendar

		$calendar = '';
		if ($head_type == 'calendar') {

			if ($view == 'article') {
				$head_width = $params->get('head_w', 64);
				$head_height = $params->get('head_h', 80); // uncommented
			} else {
				$head_width = $params->get('lists_head_w', 64);
				$head_height = $params->get('lists_head_h', 80); // uncommented
			}

			$color = trim($params->get('c1', '#3D3D3D'));
			$variables[] = 'color';
			$bgcolor1 = trim($params->get('bgc11', '')) != '' ? trim($params->get('bgc11', '')) : 'transparent';
			$variables[] = 'bgcolor1';
			$bgcolor2 = trim($params->get('bgc12', '')) != '' ? trim($params->get('bgc12', '')) : 'transparent';
			$variables[] = 'bgcolor2';

			$color_top = trim($params->get('c2', '#494949'));
			$variables[] = 'color_top';
			$bgcolor1_top = trim($params->get('bgc21', '')) != '' ? trim($params->get('bgc21', '')) : 'transparent';
			$variables[] = 'bgcolor1_top';
			$bgcolor2_top = trim($params->get('bgc22', '')) != '' ? trim($params->get('bgc22', '')) : 'transparent';
			$variables[] = 'bgcolor2_top';

			$color_bottom = trim($params->get('c3', '#494949'));
			$variables[] = 'color_bottom';
			$bgcolor1_bottom = trim($params->get('bgc31', '')) != '' ? trim($params->get('bgc31', '')) : 'transparent';
			$variables[] = 'bgcolor1_bottom';
			$bgcolor2_bottom = trim($params->get('bgc32', '')) != '' ? trim($params->get('bgc32', '')) : 'transparent';
			$variables[] = 'bgcolor2_bottom';

			$cal_shadow_width = $params->get('sh_w', 0);
			$variables[] = 'cal_shadow_width';
			$cal_border_width = $params->get('border_w', 0);
			$variables[] = 'cal_border_width';
			$cal_border_radius = $params->get('border_r', 0);
			$variables[] = 'cal_border_radius';
			$cal_border_color = trim($params->get('border_c', '#000000'));
			$variables[] = 'cal_border_color';

			$font_ref_cal = $params->get('f_r', 14);
			$variables[] = 'font_ref_cal';
			$font_ratio = 1; // floatval($head_height) / 80; // 1em base for a height of 80px
			$variables[] = 'font_ratio';

			$calendar = $params->get('cal_style', 'original');
		}
		$variables[] = 'calendar';

		// head width and height

		$variables[] = 'head_width';
		$variables[] = 'head_height';

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

		include JPATH_ROOT . '/media/plg_content_articledetails/styles/style.css.php';
		if ($calendar) {
			include JPATH_ROOT . '/media/plg_content_articledetails/styles/calendars/' . $calendar . '/style.css.php';
		}

		// social networks
		if (!empty($social_networks) && is_object($social_networks)) {

			$default_colors = array('facebook' => '#43609c', 'twitter' => '#02b0e8', 'linkedin' => '#0077b6', 'sendtofriend' => '#8d6e63');

			foreach ($social_networks as $social_network) {
				if ($social_network->social_network != 'none') {

					$social_network_class = $social_network->social_network;

					if ($social_network->social_network == 'email') {
						$social_network_class = 'sendtofriend';
					}

					$color = isset($default_colors[$social_network_class]) ? $default_colors[$social_network_class] : '';

					if ($share_bgcolor && $color) {
						echo '.articledetails .info .details .detail_social a.' . $social_network_class . ' > * {';
						echo 'background-color: ' . $color . ';';
						echo '}';
					}

					if ($share_color && $color) {
						echo '.articledetails .info .details .detail_social a.' . $social_network_class . ' > * {';
						echo 'color: ' . $color . ';';
						echo '}';
					}
				}
			}
		}

		return $this->compress(ob_get_clean());
	}

}
