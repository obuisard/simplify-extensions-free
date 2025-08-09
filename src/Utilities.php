<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Environment\Browser;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\File;
use Joomla\Utilities\ArrayHelper;

class Utilities
{
	static $mobile_detector = null;
	static $SVGSprites = array();

	/**
	 * Determines if the device is mobile
	 * 
	 * @return boolean
	 */
	static public function isMobile($use_joomla_library = false)
	{
		if ($use_joomla_library) {
			$browser = Browser::getInstance();
			return $browser->isMobile();
		} else {				
			$mobileDetector = self::getMobileDetector();
			if ($mobileDetector instanceof \SYW\Library\Vendor\Detection\v4\Exception\MobileDetectException) {
			    return false; //'unknown'
			}
			    
			return $mobileDetector->isMobile();
		}
	}

	/**
	 * Determines if the device is a tablet
	 * 
	 * @return boolean
	 */
	static public function isTablet()
	{
	    $mobileDetector = self::getMobileDetector();
	    if ($mobileDetector instanceof \SYW\Library\Vendor\Detection\v4\Exception\MobileDetectException) {
	        return false; //'unknown'
	    }		    
        
	    return $mobileDetector->isTablet();
	}
	
	/**
	 * Returns the version of the library used for mobile detection
	 * 
	 * @return string
	 */
	static public function getMobileDetectVersion()
	{
	    $mobileDetector = self::getMobileDetector();
	    if ($mobileDetector instanceof \SYW\Library\Vendor\Detection\v4\Exception\MobileDetectException) {
	        return 'unknown';
	    }
	    
	    if (version_compare(PHP_VERSION, '8.0', '>=')) {
	        // Use MobileDetect v4.8
	        return $mobileDetector->getVersion();
	    } else {
	        return $mobileDetector::VERSION;
	    }
	}

	/**
	 * Get the mobile detector object
	 * 
	 * v2.8  PHP >=5.0,<7.0
	 * v3.74 PHP >=7.4,<8.0
	 * v4.8  PHP >=8.0
	 *
	 * @return \SYW\Library\Vendor\MobileDetect
	 */
	static protected function getMobileDetector()
	{
		if (!isset(self::$mobile_detector)) {
		    
		    $phpVersion = PHP_VERSION;
		    
		    if (version_compare($phpVersion, '8.0', '>=')) {
		        // Use MobileDetect v4.8
		        try {
		            self::$mobile_detector = new \SYW\Library\Vendor\Detection\v4\MobileDetect();
		        } catch (\SYW\Library\Vendor\Detection\v4\Exception\MobileDetectException $e) {
		            self::$mobile_detector = $e;
		        }
		    } elseif (version_compare($phpVersion, '7.4', '>=')) {
		        // Use MobileDetect v3.74
		        self::$mobile_detector = new \SYW\Library\Vendor\Detection\v3\MobileDetect();
		    } else {
		        // Use MobileDetect v2.8
		        self::$mobile_detector = new \SYW\Library\Vendor\MobileDetect();
		    }
		}

		return self::$mobile_detector;
	}

	/*
	* Returns the google font found in a font family
	* The returned font is of format "Google Font"
	* 
	* @deprecated use Fonts::getWebfontFromFamily
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
	 * 
	 * @deprecated use Fonts::getSafeWebfont
	 */
	static function getSafeGoogleFont($google_font)
	{
		$font = str_replace(' ', '+', $google_font); // replace spaces with +
		return trim($font, '"');
	}

	/*
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * @param string $hexStr (hexadecimal color value)
	 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
	 * @param string $seperator separator of RGB values. Applicable only if second parameter is true.
	 * 
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
	
	/**
	 * Convert a HSL color into RGB
	 * 
	 * @param string $hslStr color value eg: hsl(216, 98%, 52%)
	 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
	 * @param string $seperator separator of RGB values. Applicable only if second parameter is true.
	 * 
	 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
	 */
	static function HSL2RGB($hslStr, $returnAsString = false, $seperator = ',')
	{
		$hsl_string = str_replace('hsl', '', $hslStr);
		$hsl_string = trim($hsl_string, '()');
		
		$hsl_array = explode(',', $hsl_string);
		foreach ($hsl_array as $key => $value) {
			$value = trim($value, ' %');
			if (empty($value)) {
				unset($key);
			}
		}
		
		$h = (float)$hsl_array[0] / 360;
		$s = (float)$hsl_array[1] / 100;
		$l = (float)$hsl_array[2] / 100;
		
		$r = $l;
		$g = $l;
		$b = $l;
		$v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);
		
		if ($v > 0) {
			
			$m = $l + $l - $v;
			$sv = ($v - $m ) / $v;
			$h *= 6.0;
			$sextant = floor($h);
			$fract = $h - $sextant;
			$vsf = $v * $sv * $fract;
			$mid1 = $m + $vsf;
			$mid2 = $v - $vsf;
			
			switch ($sextant)
			{
				case 0:
					$r = $v;
					$g = $mid1;
					$b = $m;
					break;
				case 1:
					$r = $mid2;
					$g = $v;
					$b = $m;
					break;
				case 2:
					$r = $m;
					$g = $v;
					$b = $mid1;
					break;
				case 3:
					$r = $m;
					$g = $mid2;
					$b = $v;
					break;
				case 4:
					$r = $mid1;
					$g = $m;
					$b = $v;
					break;
				case 5:
					$r = $v;
					$g = $m;
					$b = $mid2;
					break;
			}
		}
		
		$rgbArray = array('red' => round($r * 255.0), 'green' => round($g * 255.0), 'blue' => round($b * 255.0));
		
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	}

	/*
	 * Bootstrap conversion function (handles Bootstrap 2, 3, 4 and 5)
	 * returns default class if Bootstrap version is unknown (or 0)
	 */
	static function getBootstrapProperty($property_string, $bootstrap_version = 5)
	{
		$bootstrap_version = intval($bootstrap_version);

		$properties = explode(' ', $property_string); // may get properties like 'label label-info'

		$converted_properties = array();

		foreach ($properties as $property) {
			switch ($property) {

				// buttons

				case 'btn': $converted_properties[] = 'btn'; break; // exists for all versions

				case 'btn-default': // no default in B2, B4 nor B5
					if ($bootstrap_version == 0 || $bootstrap_version == 3) { $converted_properties[] = 'btn-default'; }
					break;
				case 'btn-primary': $converted_properties[] = 'btn-primary'; break;
				case 'btn-secondary': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version >= 4) { $converted_properties[] = 'btn-secondary'; }
					break;
				case 'btn-info': $converted_properties[] = 'btn-info'; break;
				case 'btn-warning': $converted_properties[] = 'btn-warning'; break;
				case 'btn-danger': $converted_properties[] = 'btn-danger'; break;
				case 'btn-success': $converted_properties[] = 'btn-success'; break;
				case 'btn-link': $converted_properties[] = 'btn-link'; break;
				case 'btn-inverse': // no inverse for B3, B4 and B5
					if ($bootstrap_version == 0 || $bootstrap_version == 2) { $converted_properties[] = 'btn-inverse'; }
					break;
				case 'btn-light': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version >= 4) { $converted_properties[] = 'btn-light'; }
					break;
				case 'btn-dark': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version >= 4) { $converted_properties[] = 'btn-dark'; }
					break;
		        case 'btn-block': // removed in B5
		        	if ($bootstrap_version <= 4) { $converted_properties[] = 'btn-block'; } else { $converted_properties[] = 'w-100'; }
					break;
				case 'btn-large':
		        	if ($bootstrap_version == 2) { $converted_properties[] = 'btn-large'; } else { $converted_properties[] = 'btn-lg'; }
					break;
				case 'btn-small':
		        	if ($bootstrap_version == 2) { $converted_properties[] = 'btn-small'; } else { $converted_properties[] = 'btn-sm'; }
					break;
		        case 'btn-mini': // no xs in B4 nor B5
		        	if ($bootstrap_version == 2) { $converted_properties[] = 'btn-mini'; }
		        	if ($bootstrap_version == 0 || $bootstrap_version == 3) { $converted_properties[] = 'btn-xs'; }
		        	if ($bootstrap_version >= 4) { $converted_properties[] = 'btn-sm'; }
					break;

				// labels

				case 'label':
		        	if ($bootstrap_version < 4) { $converted_properties[] = 'label'; } else { $converted_properties[] = 'badge'; }
					break;
				case 'label-default': // no default in B2, B4 nor B5
					if ($bootstrap_version == 0 || $bootstrap_version == 3) { $converted_properties[] = 'label-default'; }
					break;
				case 'label-primary': // no primary in B2
					if ($bootstrap_version == 0 || $bootstrap_version == 3) { $converted_properties[] = 'label-primary'; }
					if ($bootstrap_version == 4) { $converted_properties[] = 'badge-primary'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-primary'; }
					break;
				case 'label-secondary': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version == 4) { $converted_properties[] = 'badge-secondary'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-secondary'; }
					break;
				case 'label-info':
					if ($bootstrap_version < 4) { $converted_properties[] = 'label-info'; }
					if ($bootstrap_version == 4) { $converted_properties[] = 'badge-info'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-info text-dark'; }
					break;
				case 'label-warning':
					if ($bootstrap_version < 4) { $converted_properties[] = 'label-warning'; }
					if ($bootstrap_version == 4) { $converted_properties[] = 'badge-warning'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-warning text-dark'; }
					break;
				case 'label-important':
					if ($bootstrap_version == 0 || $bootstrap_version == 2) { $converted_properties[] = 'label-important'; }
					if ($bootstrap_version == 3) { $converted_properties[] = 'label-danger'; }
					if ($bootstrap_version == 4) { $converted_properties[] = 'badge-danger'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-danger'; }
					break;
				case 'label-success':
					if ($bootstrap_version < 4) { $converted_properties[] = 'label-success'; }
					if ($bootstrap_version == 4) { $converted_properties[] = 'badge-success'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-success'; }
					break;
				case 'label-inverse': // no inverse for B3, B4 and B5
					if ($bootstrap_version == 0 || $bootstrap_version == 2) { $converted_properties[] = 'label-inverse'; }
					break;
				case 'label-light': // not in B2 nor B3
					if ($bootstrap_version == 0) { $converted_properties[] = 'label-light'; }
					if ($bootstrap_version == 4) { $converted_properties[] = 'badge-light'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-light text-dark'; }
					break;
				case 'label-dark': // not in B2 nor B3
					if ($bootstrap_version == 0) { $converted_properties[] = 'label-dark'; }
					if ($bootstrap_version == 4) { $converted_properties[] = 'badge-dark'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-dark'; }
					break;

				// badges-pills

				case 'badge':
					if ($bootstrap_version < 4) { $converted_properties[] = 'badge'; }
					if ($bootstrap_version == 4) { $converted_properties[] = 'badge badge-pill'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'badge rounded-pill'; }
					break;
				case 'badge-default': // no default in B2, B3, B4 or B5
					if ($bootstrap_version == 0) { $converted_properties[] = 'badge-default'; }
					break;
				case 'badge-primary': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version == 4) { $converted_properties[] = 'badge-primary'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-primary'; }
					break;
				case 'badge-secondary': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version == 4) { $converted_properties[] = 'badge-secondary'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-secondary'; }
					break;
				case 'badge-info': // not in B3
					if ($bootstrap_version == 0 || $bootstrap_version == 2 || $bootstrap_version == 4) { $converted_properties[] = 'badge-info'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-info text-dark'; }
					break;
				case 'badge-warning': // not in B3
					if ($bootstrap_version == 0 || $bootstrap_version == 2 || $bootstrap_version == 4) { $converted_properties[] = 'badge-warning'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-warning text-dark'; }
					break;
				case 'badge-important': // not in B3
					if ($bootstrap_version == 0 || $bootstrap_version == 2) { $converted_properties[] = 'badge-important'; }
					if ($bootstrap_version == 4) { $converted_properties[] = 'badge-danger'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-danger'; }
					break;
				case 'badge-success': // not in B3
					if ($bootstrap_version == 0 || $bootstrap_version == 2 || $bootstrap_version == 4) { $converted_properties[] = 'badge-success'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-success'; }
					break;
				case 'badge-inverse': // no inverse for B3, B4 and B5
					if ($bootstrap_version == 0 || $bootstrap_version == 2) { $converted_properties[] = 'badge-inverse'; }
					break;
				case 'badge-light': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version == 4) { $converted_properties[] = 'badge-light'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-light text-dark'; }
					break;
				case 'badge-dark': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version == 4) { $converted_properties[] = 'badge-dark'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'bg-dark'; }
					break;

				// alerts

				case 'alert': $converted_properties[] = 'alert'; break; // exists for all versions

				case 'alert-primary': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version >= 4) { $converted_properties[] = 'alert-primary'; }
					break;
				case 'alert-secondary': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version >= 4) { $converted_properties[] = 'alert-secondary'; }
					break;
				case 'alert-info': $converted_properties[] = 'alert-info'; break;
				case 'alert-success': $converted_properties[] = 'alert-success'; break;
				case 'alert-warning': // no B2
					if ($bootstrap_version == 0 || $bootstrap_version >= 3) { $converted_properties[] = 'alert-warning'; }
					break;
				case 'alert-error':
		        	if ($bootstrap_version == 0 || $bootstrap_version == 2) { $converted_properties[] = 'alert-error'; } else { $converted_properties[] = 'alert-danger'; }
					break;
				case 'alert-light': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version >= 4) { $converted_properties[] = 'alert-light'; }
					break;
				case 'alert-dark': // not in B2 nor B3
					if ($bootstrap_version == 0 || $bootstrap_version >= 4) { $converted_properties[] = 'alert-dark'; }
					break;

				// pagination

				case 'pagination': $converted_properties[] = 'pagination'; break; // exists for all versions

				case 'pagination-large':
		        	if ($bootstrap_version == 2) { $converted_properties[] = 'pagination-large'; } else { $converted_properties[] = 'pagination-lg'; }
					break;
				case 'pagination-small':
		        	if ($bootstrap_version == 2) { $converted_properties[] = 'pagination-small'; } else { $converted_properties[] = 'pagination-sm'; }
					break;
				case 'pagination-mini':
		        	if ($bootstrap_version == 0) { $converted_properties[] = 'pagination-xs'; }
		        	if ($bootstrap_version == 2) { $converted_properties[] = 'pagination-mini'; }
		        	if ($bootstrap_version >= 3) { $converted_properties[] = 'pagination-sm'; }
					break;
				case 'pagination-left': // not in Bootstrap
					if ($bootstrap_version == 0) { $converted_properties[] = 'pagination-left'; }
					break;
				case 'pagination-center': // not in B2 nor B3
					if ($bootstrap_version == 0) { $converted_properties[] = 'pagination-center'; }
					if ($bootstrap_version >= 4) { $converted_properties[] = 'justify-content-center'; }
					break;
				case 'pagination-right': // not in B2 nor B3
					if ($bootstrap_version == 0) { $converted_properties[] = 'pagination-right'; }
					if ($bootstrap_version >= 4) { $converted_properties[] = 'justify-content-end'; }
					break;

				// align

				case 'float-right':
					if ($bootstrap_version == 2 || $bootstrap_version == 3) { $converted_properties[] = 'pull-right'; }
					if ($bootstrap_version == 0 || $bootstrap_version == 4) { $converted_properties[] = 'float-right'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'float-end'; }
					break;
				case 'float-left':
					if ($bootstrap_version == 2 || $bootstrap_version == 3) { $converted_properties[] = 'pull-left'; }
					if ($bootstrap_version == 0 || $bootstrap_version == 4) { $converted_properties[] = 'float-left'; }
					if ($bootstrap_version == 5) { $converted_properties[] = 'float-start'; }
					break;
				case 'float-none':
					if ($bootstrap_version == 0 || $bootstrap_version >= 4) { $converted_properties[] = 'float-none'; }
					break;

				// clearfix exists for all versions

				// visibility

				case 'visually-hidden':
					switch ($bootstrap_version) {
						case 0: case 2: $converted_properties[] = 'element-invisible'; break;
						case 3: case 4: $converted_properties[] = 'sr-only'; break;
						case 5: $converted_properties[] = 'visually-hidden'; break;
					}
					break;

				// hidden on the phone (for tables)

				case 'hidden-phone':
					switch ($bootstrap_version) {
						case 0: case 2: $converted_properties[] = 'hidden-phone'; break;
						case 3: $converted_properties[] = 'hidden-xs'; break;
						default: $converted_properties[] = 'd-none d-sm-table-cell'; break;
					}
					break;
			}
		}

		return implode(' ', $converted_properties);
	}

	/**
	 * output inline svg with reusable sprites and avoid duplicate code
	 *
	 * @param string $spriteId
	 * @param array $svg_attributes
	 * @param array $path_attributes
	 */
	//static function getInlineSVG($spriteName, $path = JURI::root(true).'/media/syw/svg')
	static function getInlineSVG($spriteId, $svg_attributes, $path_attributes)
	{
		$output = '';

		if (!isset(self::$SVGSprites['syw_' .$spriteId])) {

			$output .= '<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">';

			$attributes = '';
			if (isset($svg_attributes['viewbox'])) {
				$attributes .= ' viewBox="' . $svg_attributes['viewbox'] . '"';
			}

			$output .= '<symbol id="' . $spriteId . '"' . $attributes . '>';

			$attributes = '';
			foreach ($path_attributes as $attribute => $value) {
				$attributes .= ' ' . $attribute . '="' . $value . '"';
			}

			$output .= '<path' . $attributes . '></path>';

			$output .= '</symbol>';

			$output .= '</svg>';

			self::$SVGSprites['syw_' .$spriteId] = true;
		}

		$attributes = '';
		foreach ($svg_attributes as $attribute => $value) {
			if ($attribute != 'viewbox') {
				$attributes .= ' ' . $attribute . '="' . $value . '"';
			}
		}

		$output .= '<svg' . $attributes . '>';

		$output .= '<use xlink:href="#' . $spriteId . '" />';

		$output .= '</svg>';

		return $output;
	}

	/**
	 * Output the <picture> or <img> HTML element according to the image source
	 * Follows web standards and ensures proper fallbacks
	 * If the image extension is webp, it adds a png fallback
	 *
	 * @param string $src the image source
	 * @param string $alt the image alt attribute
	 * @param array $attributes attributes to be added to the <img> element (can contain width and height for the image)
	 * @param boolean $lazy_load lazy load the image
	 * @param boolean $high_resolution handle high resolution devices
	 * @param array breakpoints the possible breakpoints to use for media queries (ordered from lower to higher)
	 * @param boolean check the file existence, use when full control over the creation of images
	 * @return string the <picture> or <img> element
	 */
	public static function getImageElement($src, $alt, $attributes = array(), $lazy_load = false, $high_resolution = false, $breakpoints = null, $check_files = true, $version = '')
	{
		$html = '';

		$extensions_needing_fallbacks = array('webp', 'avif');
		$mime_types = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', 'avif' => 'image/avif');
		$possible_fallback_extensions = array('png', 'jpg');

		if ($version) {
		    if (strpos($src, '?') === false) {
		        $version = '?' . $version; // stay homogeneous with the way Joomla adds versions (or use ?version=)
		    } else {
		        $version = '';
		    }
		}

// 		$version = '';
// 		$hash = hash_file('md5', JPATH_ROOT . '/' . $src);
// 		if ($hash !== false) {
// 		    $version = '?version=' . $hash;
// 		}

		// clean the src path and grab useful info
		// src may be something like images/default.png#joomlaImage://local-images/default.png?width=500&height=500

		$image_object = HTMLHelper::cleanImageURL($src);
		$src = $image_object->url;

		if (!isset($attributes['width']) && $image_object->attributes['width'] > 0) {
		    $attributes['width'] = $image_object->attributes['width'];
		}

		if (!isset($attributes['height']) && $image_object->attributes['height'] > 0) {
		    $attributes['height'] = $image_object->attributes['height'];
		}

		// get the image extension and the image path from $src
		$source_path = File::stripExt($src);
		
		if (class_exists('\Joomla\Filesystem\File') && method_exists('\Joomla\Filesystem\File', 'getExt')) {
		    // Joomla 5 and 6
		    $source_extension = \Joomla\Filesystem\File::getExt($src);
		} else {
		    // Joomla 4 fallback
		    $source_extension = \Joomla\CMS\Filesystem\File::getExt($src);
		}

		if ($lazy_load && isset($attributes['width'])) {
		    $attributes['loading'] = 'lazy';
		} else {
		    $attributes['loading'] = 'eager';
		}

		if (!empty($breakpoints)) {

			$html .= '<picture>';

			foreach ($breakpoints as $breakpoint) {

				$source_highres_breakpoint = false;
				if ($high_resolution) {
					if ($check_files) {
						if (is_file(JPATH_SITE . '/' . $source_path . '_' . $breakpoint . '@2x.' . $source_extension)) {
							$source_highres_breakpoint = true;
						}
					} else {
						$source_highres_breakpoint = true;
					}
				}

				$fallback_breakpoint = false;
				$fallback_extension_breakpoint = 'png';
				$fallback_highres_breakpoint = false;

				if (in_array($source_extension, $extensions_needing_fallbacks)) {

					if ($check_files) {
						foreach ($possible_fallback_extensions as $possible_fallback_extension) {
							if (is_file(JPATH_SITE . '/' . $source_path . '_' . $breakpoint . '.' . $possible_fallback_extension)) {
								$fallback_breakpoint = true;
								$fallback_extension_breakpoint = $possible_fallback_extension;
								if ($high_resolution && is_file(JPATH_SITE . '/' . $source_path . '_' . $breakpoint . '@2x.' . $possible_fallback_extension)) {
									$fallback_highres_breakpoint = true;
								}
								break;
							}
						}
					} else {
						$fallback_breakpoint = true;
						if ($high_resolution) {
							$fallback_highres_breakpoint = true;
						}
					}
				}

				$html .= '<source type="' . $mime_types[$source_extension] . '" media="(max-width: ' . $breakpoint . 'px)" srcset="' . $source_path . '_' . $breakpoint . '.' . $source_extension . $version . ($source_highres_breakpoint ? ' 1x,' . $source_path . '_' . $breakpoint . '@2x.' . $source_extension . $version . ' 2x' : '') . '">';
				if ($fallback_breakpoint) {
				    $html .= '<source type="' . $mime_types[$fallback_extension_breakpoint] . '" media="(max-width: ' . $breakpoint . 'px)" srcset="' . $source_path . '_' . $breakpoint . '.' . $fallback_extension_breakpoint . $version . ($fallback_highres_breakpoint ? ' 1x,' . $source_path . '_' . $breakpoint . '@2x.' . $fallback_extension_breakpoint . $version . ' 2x' : '') . '">';
				}
			}

			$source_highres = false;
			if ($high_resolution) {
				if ($check_files) {
					if (is_file(JPATH_SITE . '/' . $source_path . '@2x.' . $source_extension)) {
						$source_highres = true;
					}
				} else {
					$source_highres = true;
				}
			}

			$fallback = false;
			$fallback_extension = 'png';
			$fallback_highres = false;

			if (in_array($source_extension, $extensions_needing_fallbacks)) {

				if ($check_files) {
					foreach ($possible_fallback_extensions as $possible_fallback_extension) {
						if (is_file(JPATH_SITE . '/' . $source_path . '.' . $possible_fallback_extension)) {
							$fallback = true;
							$fallback_extension = $possible_fallback_extension;
							if ($high_resolution && is_file(JPATH_SITE . '/' . $source_path . '@2x.' . $possible_fallback_extension)) {
								$fallback_highres = true;
							}
							break;
						}
					}
				} else {
					$fallback = true;
					if ($high_resolution) {
						$fallback_highres = true;
					}
				}
			}

			if ($fallback) {
			    $html .= '<source type="' . $mime_types[$source_extension] . '" srcset="' . $src . $version . ($source_highres ? ' 1x,' . $source_path . '@2x.' . $source_extension . $version . ' 2x' : '') . '">';
			}

			if ($fallback) {
				if ($fallback_highres) {
				    $attributes['srcset'] = $source_path . '@2x.' . $fallback_extension . $version . ' 2x';
				}
			} else {
				if ($source_highres) {
				    $attributes['srcset'] = $source_path . '@2x.' . $source_extension . $version . ' 2x';
				}
			}

            $html .= '<img src="' . ($fallback ? $source_path . '.' . $fallback_extension . $version : $src . $version) . '" alt="' . $alt . '" ' . trim(ArrayHelper::toString($attributes)) . '>';

			$html .= '</picture>';

		} else {

			$source_highres = false;
			if ($high_resolution) {
				if ($check_files) {
					if (is_file(JPATH_SITE . '/' . $source_path . '@2x.' . $source_extension)) {
						$source_highres = true;
					}
				} else {
					$source_highres = true;
				}
			}

			$fallback = false;
			$fallback_extension = 'png';
			$fallback_highres = false;

			if (in_array($source_extension, $extensions_needing_fallbacks)) {

				if ($check_files) {
					foreach ($possible_fallback_extensions as $possible_fallback_extension) {
						if (is_file(JPATH_SITE . '/' . $source_path . '.' . $possible_fallback_extension)) {
							$fallback = true;
							$fallback_extension = $possible_fallback_extension;
							if ($high_resolution && is_file(JPATH_SITE . '/' . $source_path . '@2x.' . $possible_fallback_extension)) {
								$fallback_highres = true;
							}
							break;
						}
					}
				} else {
					$fallback = true;
					if ($high_resolution) {
						$fallback_highres = true;
					}
				}
			}

			if ($fallback) {
				$html .= '<picture>';
			}

			if ($fallback) {
			    $html .= '<source type="' . $mime_types[$source_extension] . '" srcset="' . $src . $version . ($source_highres ? ' 1x,' . $source_path . '@2x.' . $source_extension . $version . ' 2x' : '') . '">';
			}

			if ($fallback) {
				if ($fallback_highres) {
				    $attributes['srcset'] = $source_path . '@2x.' . $fallback_extension . $version . ' 2x';
				}
			} else {
				if ($source_highres) {
				    $attributes['srcset'] = $source_path . '@2x.' . $source_extension . $version . ' 2x';
				}
			}

            $html .= '<img src="' . ($fallback ? $source_path . '.' . $fallback_extension . $version : $src . $version) . '" alt="' . $alt . '" ' . trim(ArrayHelper::toString($attributes)) . '>';

			if ($fallback) {
				$html .= '</picture>';
			}
		}

		return $html;
	}

	/**
	 * Replace old icon name (missing SYWicon- prefix) with the prefixed counterpart
	 * for B/C compatibility with old way of getting icons
	 *
	 * icomoon-tada returns icon-tada
	 * tada returns SYWicon-tada
	 * fas fa-tada remains unchanged because 'fas fa' is part of the prefixes that are ignored
	 *
	 * @param string $icon
	 * @param array $ignore_prefix
	 * @return string
	 */
	public static function getIconFullname($icon, $ignore_prefix = array())
	{
		if (empty($icon))
		{
			return $icon;
		}

		$icon_full_name = $icon;

		$temp_value = explode('-', $icon);

		$ignore = array_merge(array('SYWicon', 'icon', 'bi bi', 'fa fa', 'fas fa', 'fal fa', 'fab fa', 'far fa', 'fad fa'), $ignore_prefix);

		if (!in_array($temp_value[0], $ignore))
		{
			$count_replacements = 0;
			$icon_full_name = str_replace('icomoon-', 'icon-', $icon, $count_replacements);

			if ($count_replacements <= 0)
			{
				$icon_full_name = 'SYWicon-' . $icon;
			}
		}

		return $icon_full_name;
	}
	
	public static function loadPureTreePreset($classes_array = [])
	{
	    $preset = ['prefix' => '', 'retracted' => '', 'expanded' => ''];
	    
	    if (empty($classes_array)) {
	        return $preset;
	    }

	    $presets = [];
	    $presets['fa-caret'] = ['prefix' => 'fas', 'retracted' => 'fa-caret-down', 'expanded' => 'fa-caret-up'];
	    $presets['fa-square-caret'] = ['prefix' => 'fas', 'retracted' => 'fa-square-caret-down', 'expanded' => 'fa-square-caret-up'];
	    $presets['fa-angle'] = ['prefix' => 'fas', 'retracted' => 'fa-angle-down', 'expanded' => 'fa-angle-up'];
	    $presets['fa-angles'] = ['prefix' => 'fas', 'retracted' => 'fa-angles-down', 'expanded' => 'fa-angles-up'];
	    
	    if (in_array('fa-caret', $classes_array)) {
	        $preset = $presets['fa-caret'];
	    } else if (in_array('fa-square-caret', $classes_array)) {
	        $preset = $presets['fa-square-caret'];
	    } else if (in_array('fa-angle', $classes_array)) {
	        $preset = $presets['fa-angle'];
	    } else if (in_array('fa-angles', $classes_array)) {
	        $preset = $presets['fa-angles'];
	    }
	    
	    return $preset;
	}

}
