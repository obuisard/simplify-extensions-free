<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class Fonts
{
	protected static $iconfontLoaded = array('syw' => false, 'icomoon' => false);
	protected static $googlefontLoaded = array();

	/**
	 * Load the icon font if needed
	 */
	static function loadIconFont($name = 'syw')
	{
		if (self::$iconfontLoaded[$name]) {
			return;
		}

		$minified = (JDEBUG) ? '' : '-min';

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

	    switch ($name) {
	    	case 'icomoon' : $wam->registerAndUseStyle('syw.font.icomoon', 'syw/fonts-icomoon' . $minified . '.css', ['relative' => true, 'version' => 'auto']); break;
	    	default: $wam->registerAndUseStyle('syw.font', 'syw/fonts' . $minified . '.css', ['relative' => true, 'version' => 'auto']);
	    }

		self::$iconfontLoaded[$name] = true;
	}

	/**
	 * Load the Google font if needed
	 * $font can be "Google Font" or Google+Font
	 *
	 */
	static function loadGoogleFont($font)
	{
		$safefont = str_replace(' ', '+', trim($font, '"')); // replace spaces by + and removes quotes

		if (isset(self::$googlefontLoaded[$safefont]) && self::$googlefontLoaded[$safefont]) {
			return;
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wam->registerAndUseStyle('syw.googlefont.' . $safefont, 'https://fonts.googleapis.com/css?family=' . $safefont);

		self::$googlefontLoaded[$safefont] = true;
	}

}
?>
