<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Environment\Browser;
use SYW\Library\Vendor\MobileDetect;

class Utilities 
{	
	static $isMobile = null;
	
	/*
	 * Determines if the device is mobile
	 */
	static function isMobile($use_joomla_library = false)
	{
		if (!isset(self::$isMobile)) {
			
			if ($use_joomla_library) {				
				$browser = Browser::getInstance();				
				self::$isMobile = $browser->isMobile();
			} else {				
				$detect = new MobileDetect;				
				self::$isMobile = $detect->isMobile();
			}
		}
		
		return self::$isMobile;
	}
	
	/*
	* Returns the google font found in a font family
	* The returned font is of format "Google Font"
	*/
	static function getGoogleFont($font_family)
	{
		$google_font = '';
	
		$standard_fonts = array();
		$standard_fonts[] = "Palatino Linotype";
		$standard_fonts[] = "Book Antiqua";
		$standard_fonts[] = "MS Serif";
		$standard_fonts[] = "New York";
		$standard_fonts[] = "Times New Roman";
		$standard_fonts[] = "Arial Black";
		$standard_fonts[] = "Comic Sans MS";
		$standard_fonts[] = "Lucida Sans Unicode";
		$standard_fonts[] = "Lucida Grande";
		$standard_fonts[] = "Trebuchet MS";
		$standard_fonts[] = "MS Sans Serif";
		$standard_fonts[] = "Courier New";
		$standard_fonts[] = "Lucida Console";
	
		$fonts = explode(',', $font_family);
		foreach ($fonts as $font) {
			if (substr_count($font, '"') == 2) { // found a font with 2 quotes
				$font = trim($font, '"');
				foreach ($standard_fonts as $standard_font) {
					if (strcasecmp($standard_font, $font) == 0) { // identical fonts
						return '';
					}
				}
				$google_font = $font;
			}
		}
	
		return $google_font;
	}
	
	/*
	 * Transform "Google Font" into Google+Font for use in <link> tag
	 */
	static function getSafeGoogleFont($google_font)
	{
		$font = str_replace(' ', '+', $google_font); // replace spaces by +
		return trim($font, '"');
	}
	
	/*
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * @param string $hexStr (hexadecimal color value)
	 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
	 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
	 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
	 */
	static function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') 
	{
	    $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
	    $rgbArray = array();
	    if (strlen($hexStr) == 6) { // if a proper hex code, convert using bitwise operation. No overhead... faster
	        $colorVal = hexdec($hexStr);
	        $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
	        $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
	        $rgbArray['blue'] = 0xFF & $colorVal;
	    } elseif (strlen($hexStr) == 3) { // if shorthand notation, need some string manipulations
	        $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
	        $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
	        $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
	    } else {
	        return false; //Invalid hex color code
	    }
	    
	    return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	} 
	
}
?>
