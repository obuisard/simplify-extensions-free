<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Cache;

defined('_JEXEC') or die;

use SYW\Library\HeaderFilesCache;

class JSAnimationFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$suffix = $params->get('suffix');
		$variables[] = 'suffix';

		// set all necessary parameters
		$this->params = compact($variables);
	}

	public function getBuffer($include_declaration = false)
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

		if ($include_declaration) {
			echo $this->declaration;
		}

		return $this->compress(ob_get_clean(), false);
	}

}
