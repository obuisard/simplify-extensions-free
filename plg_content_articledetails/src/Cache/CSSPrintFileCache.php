<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\Content\ArticleDetails\Cache;

// no direct access
defined('_JEXEC') or die;

use SYW\Library\HeaderFilesCache;

class CSSPrintFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$view = $params->get('joomla_view');

		// body parameters

		$font_details = $params->get('d_fs', 80);
		$variables[] = 'font_details';

		// head width and height

		if ($view == 'article') {
			$head_type = $params->get('head_type', 'none');
			$head_width = $params->get('head_w', 64);
			$head_height = $params->get('head_h', 80);
		} else {
			$head_type = $params->get('lists_head_type', 'none');
			$head_width = $params->get('lists_head_w', 64);
			$head_height = $params->get('lists_head_h', 80);
		}

		// calendar

		$calendar = '';
		if ($head_type == 'calendar') {
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

		include JPATH_ROOT . '/media/plg_content_articledetails/styles/print.css.php';
		if ($calendar) {
			if (is_file(JPATH_ROOT . '/media/plg_content_articledetails/styles/calendars/' . $calendar . '/print.css.php')) {
				include JPATH_ROOT . '/media/plg_content_articledetails/styles/calendars/' . $calendar . '/print.css.php';
			}
		}

		return $this->compress(ob_get_clean());
	}

}