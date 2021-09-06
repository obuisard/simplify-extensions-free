<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Cache;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use SYW\Library\HeaderFilesCache;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Module\LatestNewsEnhanced\Site\Helper\Helper;

class JSAnimationFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$suffix = $params->get('suffix');
		$variables[] = 'suffix';

		$css_prefix = '#lnee_'.$suffix;
		$variables[] = 'css_prefix';

		$jQuery_var = 'jQuery';
		$variables[] = 'jQuery_var';

		$show_errors = Helper::isShowErrors($params);
		$variables[] = 'show_errors';

		$bootstrap_version = $params->get('bootstrap_version', 5);
		$variables[] = 'bootstrap_version';

		$animation = $params->get('anim', '');
		$pagination = $params->get('pagination', '');
		if (!empty($pagination) && empty($animation)) { // pagination only
			$animation = 'justpagination';
		}
		$variables[] = 'animation';

		// general parameters

		$horizontal = ($params->get('align', 'v') === 'h') ? true : false;
		$variables[] = 'horizontal';

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

		$margin_in_perc = 0;
		if ($item_width_unit == '%' && $item_width > 0 && $item_width < 100) {
			$news_per_row = (int)(100 / $item_width);
			$left_for_margins = 100 - ($news_per_row * $item_width);
			$margin_in_perc = $left_for_margins / ($news_per_row * 2);
		}
		$variables[] = 'margin_in_perc';

		$min_width = trim($params->get('min_item_w', '')); // px
		$variables[] = 'min_width';

		$max_width = trim($params->get('max_item_w', ''));
		$variables[] = 'max_width';

		$space_between_items = $params->get('item_spacebetween', '0');
		$variables[] = 'space_between_items';

		$items_height = trim($params->get('items_h', ''));
		$variables[] = 'items_height';
		$items_width = trim($params->get('items_w', ''));
		$variables[] = 'items_width';

		// animation parameters

		$direction = 'left';
		if (!$horizontal) {
			$direction = 'up';
		}
		switch ($params->get('dir', 't')) {
			case 'l' :
				$direction = 'left';
				if (!$horizontal) {
					$direction = 'up';
				}
				break;
			case 'r' :
				$direction = 'right';
				if (!$horizontal) {
					$direction = 'down';
				}
				break;
			case 't' :
				$direction = 'up';
				if ($horizontal) {
					$direction = 'left';
				}
				break;
			case 'b' :
				$direction = 'down';
				if ($horizontal) {
					$direction = 'right';
				}
				break;
		}
		$variables[] = 'direction';

		$auto = $params->get('auto', 1);
		$variables[] = 'auto';

		$speed = $params->get('speed', 1000);
		$variables[] = 'speed';

		$interval = $params->get('interval', 3000);
		$variables[] = 'interval';

		$visibleatonce = $params->get('visible_items', 1);
		$variables[] = 'visibleatonce';

		$moveatonce = ($params->get('move', 'all') === 'all') ? $visibleatonce : '1';
		$variables[] = 'moveatonce';

		$num_links = $params->get('num_links', 5);
		$variables[] = 'num_links';

		$prev_type = $params->get('prev_type', '');
		$prev_label = ($prev_type == 'prev') ? Text::_('JPREV') : ($prev_type == 'label' ? trim($params->get('label_prev', '')) : '');
		$variables[] = 'prev_label';
		
		$prev_aria_label = Text::_('JPREV');
		$variables[] = 'prev_aria_label';

		$next_type = $params->get('next_type', '');
		$next_label = ($next_type == 'next') ? Text::_('JNEXT') : ($next_type == 'label' ? trim($params->get('label_next', '')) : '');
		$variables[] = 'next_label';
		
		$next_aria_label = Text::_('JNEXT');
		$variables[] = 'next_aria_label';

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

		$pagination_position = $params->get('pagination_pos', 'below');
		$variables[] = 'pagination_position';

		$pagination_style = $params->get('pagination_style', '');
		$variables[] = 'pagination_style';

		$pagination_size = '';
		if ($pagination_style && $bootstrap_version > 0) { // Bootstrap is selected
		    $pagination_size = SYWUtilities::getBootstrapProperty('pagination-'.$params->get('pagination_size', ''), $bootstrap_version);
		}
		$variables[] = 'pagination_size';

		$pagination_align = '';
		if ($pagination_style && $bootstrap_version > 0) { // Bootstrap is selected
		    $pagination_align = SYWUtilities::getBootstrapProperty('pagination-'.$params->get('pagination_align', 'center'), $bootstrap_version);
		}
		$variables[] = 'pagination_align';

		// set all necessary parameters
		$this->params = compact($variables);
	}

	public function getBuffer()
	{
		// get all necessary parameters
		extract($this->params);

// 		if (function_exists('ob_gzhandler')) { // not tested
// 			ob_start('ob_gzhandler');
// 		} else {
 			ob_start();
// 		}

		// set the header
		$this->sendHttpHeaders('js');

		if (!empty($animation)) {
			include JPATH_ROOT . '/media/mod_latestnewsenhanced/scripts/animations/'.$animation.'/'.$animation.'.js.php';
		}

		return $this->compress(ob_get_clean(), false);
	}

}