<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class Fonts
{
	//protected static $iconfontLoaded = false;
	protected static $googlefontLoaded = array();

	/**
	 * Load the icon font if needed
	 */
	static function loadIconFont($syw_font = true, $icomoon_font = false, $debug = false)
	{
// 		if (self::$iconfontLoaded) {
// 			return;
// 		}

	    if ($syw_font) {
	    	$minified = (JDEBUG) ? '' : '-min';
    		//Factory::getDocument()->addStyleSheet(URI::base(true).'/media/syw/css/fonts.css');
	    	HTMLHelper::stylesheet('syw/fonts' . $minified . '.css', array('relative' => true, 'version' => 'auto'));
	    }

	    // TODO Beware! not used in Joomla 4 anymore (add font-awesome)
	    // offer old icomoon css for backward compatibility

	    if ($icomoon_font) {
	        //Factory::getDocument()->addStyleSheet(URI::base(true).'/media/jui/css/icomoon.css');
	    	HTMLHelper::stylesheet('jui/icomoon.css', array('relative' => true, 'version' => 'auto'));
		}

		//self::$iconfontLoaded = true;
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

		Factory::getDocument()->addStyleSheet('https://fonts.googleapis.com/css?family='.$safefont);

		self::$googlefontLoaded[$safefont] = true;
	}

}
?>
