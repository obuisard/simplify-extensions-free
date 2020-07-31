<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrombinoscopeContacts\Site\Cache;

defined('_JEXEC') or die;

use SYW\Library\HeaderFilesCache;

class JSFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$suffix = $params->get('suffix');
		$variables[] = 'suffix';

		$min_card_flip_width = trim($params->get('min_card_flip_w', '')); // px
		$variables[] = 'min_card_flip_width';

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

		//echo 'document.addEventListener("DOMContentLoaded", function() { ';

		echo 'document.addEventListener("readystatechange", function(event) { ';
		echo 'if (event.target.readyState !== "loading") { ';

				echo 'var flip_' . $suffix . ' = new flipCards({ ';
					echo 'selector: ".te_' . $suffix . ' .person", ';
					if ($min_card_flip_width) {
						echo 'min_flip_width: ' . $min_card_flip_width . ' ';
					}
				echo '}); ';

		echo '} ';
		echo '}); ';

		return ob_get_clean();
	}

}