<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Cache;

defined('_JEXEC') or die;

use SYW\Library\HeaderFilesCache;

class JSFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$suffix = '#lnee_'.$params->get('suffix');
		$variables[] = 'suffix';

		$item_width = trim($params->get('item_w', 100)); // % : it is always in percentages when the caching occurs
		if ($item_width <= 0 || $item_width > 100) {
			$item_width = 100;
		}
		$variables[] = 'item_width';

		$min_width = trim($params->get('min_item_w', 0)); // px : there is always a width when the caching occurs
		$variables[] = 'min_width';

		$margin_min_width = 3; // px
		$variables[] = 'margin_min_width';

		$margin_error = 1; // px
		$variables[] = 'margin_error';

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

		echo 'jQuery(document).ready(function ($) { ';

			echo 'var item = $("'.$suffix.' .latestnews-item"); ';
			echo 'var itemlist = $("'.$suffix.' .latestnews-items"); ';

			echo 'if (item != null) { ';
				echo 'resize_news(); ';
			echo '} ';

			echo '$(window).resize(function() { ';
				echo 'if (item != null) { ';
					echo 'resize_news(); ';
				echo '} ';
			echo '}); ';

			echo 'function resize_news() { ';

				echo 'var container_width = itemlist.width(); ';

				echo 'var news_per_row = 1; ';

				echo 'var news_width = Math.floor(container_width * '.$item_width.' / 100); ';

				echo 'if (news_width < '.$min_width.') { ';
				   echo 'if (container_width < '.$min_width.') { ';
				    	echo 'news_width = container_width; ';
				   echo '} else { ';
				    	echo 'news_width = '.$min_width.'; ';
				   echo '} ';
				echo '} ';

				echo 'if ('.$item_width.' <= 50) { ';
					echo 'news_per_row = Math.floor(container_width / news_width); ';

					echo 'if (news_per_row == 1) { ';
				    	echo 'news_width = container_width; ';
					echo '} else { ';
				    	echo 'news_width = Math.floor(container_width / news_per_row) - ('.$margin_min_width.' * news_per_row); ';
					echo '} ';

				echo '} else { '; // we can never have 2 items on the same row
			        echo 'news_width = container_width; ';
				echo '} ';

				echo 'var left_for_margins = container_width - (news_per_row * news_width); ';
				echo 'var margin_width = Math.floor(left_for_margins / (news_per_row * 2)) - '.$margin_error.'; ';

				echo 'item.each(function() { ';
		            echo '$(this).width(news_width + "px"); ';
				echo '$(this).css("margin-left", margin_width + "px"); ';
			        echo '$(this).css("margin-right", margin_width + "px"); ';
				echo '}); ';
			echo '} ';

		echo '}); ';

		return ob_get_clean();
	}

}