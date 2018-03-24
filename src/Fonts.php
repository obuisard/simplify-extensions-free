<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class Fonts 
{	
	static $iconfontLoaded = false;
	static $googlefontLoaded = array();
		
	/**
	 * Load the icon font if needed
	 */
	static function loadIconFont($include_icomoon = false, $debug = false)
	{	
		if (self::$iconfontLoaded) {
			return;
		}
		
		if ($debug) {
			Factory::getDocument()->addStyleSheet(URI::base(true).'/media/syw/css/fonts.css');
		} else {
			Factory::getDocument()->addStyleSheet(URI::base(true).'/media/syw/css/fonts-min.css');
		}	
		
		if ($include_icomoon) {
			Factory::getDocument()->addStyleSheet(URI::base(true).'/media/jui/css/icomoon.css');
		}
						
		self::$iconfontLoaded = true;
	}
	
	/**
	 * Load the Google font if needed
	 */
	static function loadGoogleFont($safefont)
	{
		if (isset(self::$googlefontLoaded[$safefont]) && self::$googlefontLoaded[$safefont]) {
			return;
		}
		
		Factory::getDocument()->addStyleSheet('https://fonts.googleapis.com/css?family='.$safefont);
		
		self::$googlefontLoaded[$safefont] = true;
	}
	
}
?>
