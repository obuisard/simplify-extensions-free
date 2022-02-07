<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

class Fonts
{
// 	protected static $iconfontLoaded = array('syw' => false, 'icomoon' => false);
// 	protected static $googlefontLoaded = array();

    /**
     * The web asset manager
     */
    protected static $wam;
    
    /**
     * The plugin params
     */
    protected static $plugin_params;

    /**
     * Get the web asset manager
     * @return object
     */
    protected static function getWebAssetManager() 
    {
        if (self::$wam == null) {
            self::$wam = Factory::getApplication()->getDocument()->getWebAssetManager();
        }
        
        return self::$wam;
    }   
    
    /**
     * Get the plugin params
     * @return object
     */
    protected static function getPluginParams() 
    {
        if (self::$plugin_params == null) {
            if (PluginHelper::isEnabled('system', 'syw')) {
                $plugin = PluginHelper::getPlugin('system', 'syw');
                self::$plugin_params = json_decode($plugin->params);
            }
        }
        
        return self::$plugin_params;
    }

	/**
	 * Load the icon font if needed
	 */
	public static function loadIconFont($name = 'syw')
	{
// 		if (self::$iconfontLoaded[$name]) {
// 			return;
// 		}

		$minified = (defined('JDEBUG') && JDEBUG) ? '' : '-min';
		
		$attributes = array();
		if (Factory::getApplication()->isClient('site') && isset(self::getPluginParams()->lazy_stylesheets) && self::getPluginParams()->lazy_stylesheets > 0) {
		    $attributes['rel'] = 'lazy-stylesheet';
		}

	    switch ($name) {
	        case 'icomoon' : self::getWebAssetManager()->registerAndUseStyle('syw.font.icomoon', 'syw/fonts-icomoon' . $minified . '.css', ['relative' => true, 'version' => 'auto'], $attributes); break;
	        case 'fontawesome' : self::getWebAssetManager()->useStyle('fontawesome'); break; // loads fontawesome and icomoon B/C from web asset, probably already loaded on the page
	        default: self::getWebAssetManager()->registerAndUseStyle('syw.font', 'syw/fonts' . $minified . '.css', ['relative' => true, 'version' => 'auto'], $attributes);
	    }

// 		self::$iconfontLoaded[$name] = true;
	}

	/**
	 * Load the Google font if needed
	 * $font can be "Google Font" or Google+Font
	 *
	 */
	public static function loadGoogleFont($font)
	{
		$safefont = str_replace(' ', '+', trim($font, '"')); // replace spaces with + and removes quotes, if any

// 		if (isset(self::$googlefontLoaded[$safefont]) && self::$googlefontLoaded[$safefont]) {
// 			return;
// 		}

		self::getWebAssetManager()->registerAndUseStyle('syw.googlefont.' . $safefont, 'https://fonts.googleapis.com/css2?family=' . $safefont . '&display=swap');

// 		self::$googlefontLoaded[$safefont] = true;
	}

}
?>
