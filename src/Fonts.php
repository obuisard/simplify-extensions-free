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
        if (self::$wam == null)
        {
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
        if (self::$plugin_params == null)
        {
            if (PluginHelper::isEnabled('system', 'syw'))
            {
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
		$lazyload = false;

		if (Factory::getApplication()->isClient('site') && isset(self::getPluginParams()->lazy_stylesheets) && self::getPluginParams()->lazy_stylesheets > 0)
		{
			$lazyload = true;
		}

		$attributes = array();

		if ($lazyload)
		{
		    $attributes['rel'] = 'lazy-stylesheet';
		}

	    switch ($name)
	    {
	        case 'icomoon' :
	        	self::getWebAssetManager()->registerAndUseStyle('syw.font.icomoon', 'syw/fonts-icomoon.min.css', ['relative' => true, 'version' => 'auto'], $attributes);
	        	break;

	        case 'fontawesome' : // loads fontawesome and icomoon B/C from web asset, probably already loaded on the page

	        	if ($lazyload)
	        	{
	        		self::getWebAssetManager()->getAsset('style', 'fontawesome')->setAttribute('rel', 'lazy-stylesheet');
	        	}

	        	self::getWebAssetManager()->useStyle('fontawesome');
	        	break;

	        default:
	        	self::getWebAssetManager()->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto'], $attributes);
	    }
	}
	
	/**
	 * Load a Google font
	 * 
	 * @param string $font_name (can be "Google Font" or Google+Font)
	 * @param string $weight (can be 400 400;700 400..700)
	 * @param string $text get only the letters needed
	 */
	public static function loadGoogleFont($font_name, $weight = '', $text = '')
	{
		$font_name = trim($font_name, '"'); // removes quotes, if any
		
		$url = 'https://fonts.googleapis.com/css2?family=' . str_replace(' ', '+', $font_name);
		
		if ($weight) {
			$url .= ':wght@' . $weight;
		}
		
		if ($text) {
			$url .= '&text=' . urlencode($text);
		}
		
		$url .= '&display=swap';

		self::getWebAssetManager()->registerAndUseStyle('syw.googlefont.' . str_replace(' ', '_', $font_name), $url);
	}
	
	/**
	 * Load any local font
	 * 
	 * @param string $font_name
	 * @param string $font_file_path
	 * @param string $weight (could be 100 400 to specify weight range)
	 * @param string $style
	 * @param array $file_extensions possible: otf, eot, ttf, svg, woff, woff2
	 */
	public static function addFontFace($font_name, $font_file_path, $weight = '400', $style = 'normal', $file_extensions = ['ttf', 'woff', 'woff2'])
	{
		$fontface = '@font-face {';
		
		$fontface .= 'font-family: "' . $font_name . '";';
		
		foreach ($file_extensions as $file_extension) {
			switch ($file_extension) {
				case 'otf':
					$fontface .= 'src: url("' . $font_file_path . '.otf") format("opentype");';
					break;
				case 'eot':
					$fontface .= 'src: url("' . $font_file_path . '.eot");'; // IE9 compat modes
			}
		}
		
		$urls = array();
		
		foreach ($file_extensions as $file_extension) {
			
			switch ($file_extension) {
				case 'eot':
					$urls[] = 'url("' . $font_file_path . '.eot?#iefix") format("embedded-opentype")'; // IE6-IE8
					break;
				case 'ttf':
					$urls[] = 'url("' . $font_file_path . '.ttf") format("truetype")'; // Safari, Android, iOS
					break;
				case 'svg':
					$urls[] = 'url("' . $font_file_path . '.svg#' . str_replace(' ', '', strtolower($font_name)) . '") format("svg")'; // legacy iOS
					break;
				case 'woff':
					$urls[] = 'url("' . $font_file_path . '.wofff") format("woff")'; // modern browsers
					break;
				case 'woff2':
					$urls[] = 'url("' . $font_file_path . '.wofff2") format("woff2")'; // latest modern browsers
					break;
			}
		}
		
		if (!empty($urls)) {
			
			// add local(""), to specify the use of the local font, if available
// 			if (!Uri::isInternal($font_file_path)) {
// 				array_unshift($urls, 'local("")'); // may fail to load the font on Android
// 			}
			
			$fontface .= 'src: ' . implode(', ', $urls) . ';';
		}
		
		$fontface .= 'font-weight: ' . $weight . ';';
		$fontface .= 'font-style: ' . $style . ';';
		$fontface .= 'font-display: swap;';
		
		$fontface.= '}';
		
		self::getWebAssetManager()->addInlineStyle($fontface);
	}

}
