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
    		if ($debug) {
    		    //Factory::getDocument()->addStyleSheet(URI::base(true).'/media/syw/css/fonts.css');
    		    JHtml::_('stylesheet', 'syw/fonts.css', array('version' => 'auto', 'relative' => true));
    		} else {
    		    //Factory::getDocument()->addStyleSheet(URI::base(true).'/media/syw/css/fonts-min.css');
    		    JHtml::_('stylesheet', 'syw/fonts-min.css', array('version' => 'auto', 'relative' => true));
    		}
	    }
		
	    // TODO Beware! not used in Joomla 4 anymore (add font-awesome)
	    // offer old icomoon css for backward compatibility
	    
	    if ($icomoon_font) {
	        //Factory::getDocument()->addStyleSheet(URI::base(true).'/media/jui/css/icomoon.css');
	        JHtml::_('stylesheet', 'jui/icomoon.css', array('version' => 'auto', 'relative' => true));
		}
						
		//self::$iconfontLoaded = true;
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
