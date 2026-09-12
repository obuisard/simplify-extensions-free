<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use SYW\Library\Plugin as SYWPLugin;

class Fonts
{
    /**
     * The web asset manager
     */
    protected static $wam;

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
	 * Load the icon font if needed
	 */
	public static function loadIconFont($name = 'syw')
	{
		$lazyload = false;

		if (Factory::getApplication()->isClient('site') && SYWPLugin::getLazyStylesheet() > 0) {
			$lazyload = true;
		}

		$attributes = array();

		if ($lazyload) {
		    $attributes['rel'] = 'lazy-stylesheet';
		}

	    switch ($name)
	    {
	        case 'icomoon' :
	        	self::getWebAssetManager()->registerAndUseStyle('syw.font.icomoon', 'syw/fonts-icomoon.min.css', ['relative' => true, 'version' => 'auto'], $attributes);
	        	break;

	        case 'fontawesome' : // loads fontawesome and icomoon B/C from web asset, probably already loaded on the page

	        	if ($lazyload) {
	        		self::getWebAssetManager()->getAsset('style', 'fontawesome')->setAttribute('rel', 'lazy-stylesheet');
	        	}

	        	self::getWebAssetManager()->useStyle('fontawesome');
	        	break;

	        default:
	        	self::getWebAssetManager()->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto'], $attributes);
	    }
	}
	
	/**
	 * Returns the webfonts found in a font family
	 * The returned font is of format "Web Font"
	 * 
	 * @param string $font_family
	 * 
	 * @return array
	 */
	public static function getWebfontsFromFamily($font_family)
	{
	    $webfonts = [];

	    $standard_fonts = [];

	    $standard_fonts[] = 'Palatino Linotype';
	    $standard_fonts[] = 'Book Antiqua';
	    $standard_fonts[] = 'MS Serif';
	    $standard_fonts[] = 'New York';
	    $standard_fonts[] = 'Times New Roman';
	    $standard_fonts[] = 'Arial Black';
	    $standard_fonts[] = 'Comic Sans MS';
	    $standard_fonts[] = 'Lucida Sans Unicode';
	    $standard_fonts[] = 'Lucida Grande';
	    $standard_fonts[] = 'Trebuchet MS';
	    $standard_fonts[] = 'MS Sans Serif';
	    $standard_fonts[] = 'Courier New';
	    $standard_fonts[] = 'Lucida Console';

	    $fonts = explode(',', $font_family);

	    foreach ($fonts as $font) {
	        if (substr_count($font, '"') == 2 || substr_count($font, '\'') == 2) { // found a font with 2 quotes
	            $font = trim($font, ' \'"');
	            
	            if (in_array(ucwords($font), $standard_fonts)) {
	                continue; // Not a webfont
	            }

	            $webfonts[] = $font;
	        }
	    }
	    
	    return $webfonts;
	}
	
	/**
	 * Transform "Web Font" into Web<replacement>Font for use in <link> tag
	 *
	 * @param string $webfont with or without enclosed "
	 * 
	 * @return string
	 */
// 	public static function getSafeWebfont($webfont)
// 	{
// 	    $host_service = SYWPLugin::getWebfontService();
	    
// 	    $replacement = '+';
// 	    if ($host_service === 'bunny') {
// 	        $replacement = '-';
// 	    }

// 	    $font = str_replace(' ', $replacement, $webfont); // replace spaces with replacement
	    
// 	    return trim($font, '"');
// 	}
	
	/**
	 * Get a Bunny webfont family syntax
	 *
	 * @param string $font_name (can be "Web Font" or Web-Font or "Web-Font")
	 * @param array $weights
	 * 
	 * @return string
	 */
	protected static function getBunnyFamily($font_name, $weights = [])
	{
	    $family = $font_name;
	    
	    if (count($weights) > 0) {
	        $family .= ':' . implode(',', $weights);
	    }
        
        return $family;
	}
	
	/**
	 * Get a Google webfont family syntax
	 *
	 * @param string $font_name (can be "Web Font" or Web+Font or "Web+Font")
	 * @param array $weights
	 * 
	 * @return string
	 */
	protected static function getGoogleFamily($font_name, $weights = [])
	{
	    $family = $font_name;
	    
	    if (count($weights) > 0) {
	        
	        $weights_mdarray = [];
	        $italic = false;
	        
	        foreach ($weights as $weight) {
	            if (strpos($weight, 'i') !== false) {
	                $weights_mdarray[rtrim($weight, 'i')][] = '1';
	                $italic = true;
	            } else {
	                $weights_mdarray[$weight][] = '0';
	            }
	        }
	        
	        $family .= ':';
	        
	        if ($italic) {
	            $family .= 'ital,';
	        }
	        
	        $family .= 'wght@';
	        
	        // :wght@100;300 when no italic specimens
	        // :ital,wght@0,100;0,300;1,400 when italic specimens
	        
	        foreach ($weights_mdarray as $weight => $weight_array) {
	            foreach ($weight_array as $italic_value) {
	                if ($italic) {
	                    $family .= $italic_value . ',' . $weight . ';';
	                } else {
	                    $family .= $weight . ';';
	                }
	            }
	        }
	        
	        $family = rtrim($family, ';');
	    }
	    
	    return $family;
	}
	
	/**
	 * Load a set of web fonts
	 * 
	 * @param array 
	 *     $font_names [['name' => 'Web Font 1', 'weights' => ['400', '500', '500i']], ['name' => 'Web Font 2', 'weights' => []], ['name' => 'Web Font 2']]
	 *     $font_names can be ['Web Font 1', 'Web Font 2']
	 *     Web fonts can be Web Font or Web+Font or Web-Font
	 * 
	 * @return boolean if the webfont URL has been processed or not
	 */
	public static function loadWebFonts($font_names)
	{
	    if (count($font_names) <= 0) {
	        return false;
	    }
	    
	    // If the array is a mono list of font names, remove duplicates and create a usable array
	    if (!is_array(array_values($font_names)[0])) {
	        $font_names = array_unique($font_names);

	        $web_fonts_array = [];
	        foreach ($font_names as $font_name) {
	            $web_fonts_array[] = ['name' => $font_name];
	        }
	        
	        $font_names = $web_fonts_array;
	    } else {
	        // Look for duplicates only. Merge weights
	        
	        $unique_array = [];
	        $key_array = [];
	        
	        foreach ($font_names as $font_name_array) {
	            if (!in_array($font_name_array['name'], $key_array)) {
	                $key_array[] = $font_name_array['name'];
	                $unique_array[$font_name_array['name']] = isset($font_name_array['weights']) ? $font_name_array['weights'] : [];
	            } else {
	                $weights_recorded = $unique_array[$font_name_array['name']];
	                $weights_to_record = isset($font_name_array['weights']) ? $font_name_array['weights'] : [];

	                // Make sure 400 is part of the list or else the normal weight will be missing
	                if (count($weights_recorded) > 0 && empty($weights_to_record)) {
	                    $weights_to_record = ['400'];
	                } else if (count($weights_to_record) > 0 && empty($weights_recorded)) {
	                    $weights_recorded = ['400'];
	                }

	                $unique_array[$font_name_array['name']] = array_unique(array_merge($weights_recorded, $weights_to_record));
	            }
	        }
	        
	        // Duplicates have been found
	        if (count($font_names) > count($unique_array)) {
	            $font_names = [];
	            foreach ($unique_array as $name => $weights) {
	                $font_names[] = ['name' => $name, 'weights' => $weights];
	            }
	        }
	    }
	    
	    $host_service = SYWPLugin::getWebfontService();
	    
	    switch ($host_service)
	    {
	        case 'bunny':
	            
	            $url = 'https://fonts.bunny.net/css?family=';
	            
	            $families = [];
	            $asset_names = [];
	            
	            foreach ($font_names as $font_name_array) {
	                $safe_font_name = str_replace(' ', '-', $font_name_array['name']);
	                $safe_weights = isset($font_name_array['weights']) ? $font_name_array['weights'] : [];
	                $families[] = self::getBunnyFamily($safe_font_name, $safe_weights);
	                $asset_names[] = strtolower(str_replace('-', '_', $safe_font_name));
	            }

// 	            foreach ($font_names as $font_name => $weights) {
// 	                $safe_font_name = str_replace(' ', '-', $font_name);
// 	                $families[] = self::getBunnyFamily($safe_font_name, $weights);
// 	                $asset_names[] = strtolower(str_replace('-', '_', $safe_font_name));
// 	            }
	            
	            $url .= implode('|', $families);
	            
	            Factory::getApplication()->getDocument()->getPreloadManager()->preconnect('https://fonts.bunny.net');
	            self::getWebAssetManager()->registerAndUseStyle('syw.webfont.' . implode('__', $asset_names), $url);
	            
	            break;

	        default:
	            
	            $url = 'https://fonts.googleapis.com/css2?';
	            
	            $families = [];
	            $asset_names = [];
	            
	            foreach ($font_names as $font_name_array) {
	                $safe_font_name = str_replace(' ', '+', $font_name_array['name']);
	                $safe_weights = isset($font_name_array['weights']) ? $font_name_array['weights'] : [];
	                $families[] = 'family=' . self::getGoogleFamily($safe_font_name, $safe_weights);
	                $asset_names[] = strtolower(str_replace('+', '_', $safe_font_name));
	            }

// 	            foreach ($font_names as $font_name => $weights) {
// 	                $safe_font_name = str_replace(' ', '+', $font_name);	                
// 	                $families[] = 'family=' . self::getGoogleFamily($safe_font_name, $weights);
// 	                $asset_names[] = strtolower(str_replace('+', '_', $safe_font_name));
// 	            }
	            
	            $url .= implode('&', $families);
	            
	            $url .= '&display=swap';
	            
	            Factory::getApplication()->getDocument()->getPreloadManager()->preconnect('https://fonts.googleapis.com', ['crossorigin' => 'anonymous']);
	            Factory::getApplication()->getDocument()->getPreloadManager()->preconnect('https://fonts.gstatic.com', ['crossorigin' => 'anonymous']);
	            self::getWebAssetManager()->registerAndUseStyle('syw.webfont.' . implode('__', $asset_names), $url);
	    }
	    
	    return true;
	}
	
	/**
	 * Load a Google font
	 * 
	 * @param string $font_name (can be "Google Font" or Google+Font)
	 * @param string $weight (can be 400 400;700 400..700)
	 * @param string $text get only the letters needed
	 * 
	 * @deprecated use loadWebFont instead
	 */
	public static function loadGoogleFont($font_name, $weight = '', $text = '')
	{
		$font_name = trim($font_name, '"'); // removes quotes, if any
		$font_name = str_replace(' ', '+', $font_name);
		
		$url = 'https://fonts.googleapis.com/css2?family=' . $font_name;
		
		if ($weight) {
			$url .= ':wght@' . $weight;
		}
		
		if ($text) {
			$url .= '&text=' . urlencode($text);
		}
		
		$url .= '&display=swap';

		self::getWebAssetManager()->registerAndUseStyle('syw.googlefont.' . str_replace('+', '_', $font_name), $url);
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
