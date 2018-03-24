<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class Libraries 
{	
	static $jqlsLoaded = false;
	
	static $jqpLoaded = false;
	
	static $jqcLoaded = false;
	static $jqcMultipackLoaded = false;
	static $jqcthrottleLoaded = false;
	static $jqctouchLoaded = false;
	static $jqcmousewheelLoaded = false;
	static $jqctransitLoaded = false;
	
	static $jqhighres = array();
	
	static $jqcompareLoaded = false;
	
	/**
	 * Load Lazysizes jQuery plugin if needed
	 * https://github.com/aFarkas/lazysizes
	 */
	static function loadLazysizes($defer = false, $async = false, $debug = false)
	{
		if (self::$jqlsLoaded) {
			return;
		}
	
		$doc = Factory::getDocument();
			
		if ($debug) {
			$doc->addScript(URI::root(true).'/media/syw/js/lazysizes/jquery.lazysizes.js', "text/javascript", $defer, $async);
		} else {
			$doc->addScript(URI::root(true).'/media/syw/js/lazysizes/jquery.lazysizes.min.js', "text/javascript", $defer, $async);
		}
	
		self::$jqlsLoaded = true;
	}
	
	/**
	 * Load Pajinate jQuery plugin if needed
	 */
	static function loadPagination($defer = false, $async = false, $debug = false)
	{
		if (self::$jqpLoaded) {
			return;
		}
		
		$doc = Factory::getDocument();
			
		if ($debug) {
			$doc->addScript(URI::root(true).'/media/syw/js/pagination/jquery.pajinate.js', "text/javascript", $defer, $async);
		} else {
			$doc->addScript(URI::root(true).'/media/syw/js/pagination/jquery.pajinate.min.js', "text/javascript", $defer, $async);
		}
		
		self::$jqpLoaded = true;
	}
	
	/**
	 * Load the carousel library (and its plugins) if needed
	 * jQuery v1.7+
	 */
	static function loadCarousel($throttle = true, $touch = true, $mousewheel = false, $transit = false, $defer = false, $async = false, $debug = false)
	{
		if (self::$jqcMultipackLoaded && !$mousewheel && !$transit) {
			return;
		}
		
		$will_use_multipack = false;
		if (!self::$jqcLoaded && $throttle && $touch && !$mousewheel && !$transit && !$debug) {
			$will_use_multipack = true;
		}
		
		if ($throttle && !self::$jqcMultipackLoaded && !$will_use_multipack) {
			self::loadCarousel_throttle($defer, $async, $debug);
		}
		
		if ($touch && !self::$jqcMultipackLoaded && !$will_use_multipack) {
			self::loadCarousel_touch($defer, $async, $debug);
		}
		
		if ($mousewheel) {
			self::loadCarousel_mousewheel($defer, $async, $debug);
		}
		
		if ($transit) {
			self::loadCarousel_transit($defer, $async, $debug);
		}
		
		$doc = Factory::getDocument();
		
		if (!self::$jqcMultipackLoaded && $will_use_multipack) {
			
			$doc->addScript(URI::root(true).'/media/syw/js/carousel/jquery.carouFredSel.min.js', "text/javascript", $defer, $async);
			
			self::$jqcMultipackLoaded = true;
		} else {
	
			if (self::$jqcLoaded) {
				return;
			}		
			
			if ($debug) {
				$doc->addScript(URI::root(true).'/media/syw/js/carousel/jquery.carouFredSel-6.2.1.js', "text/javascript", $defer, $async);
			} else {
				$doc->addScript(URI::root(true).'/media/syw/js/carousel/jquery.carouFredSel-6.2.1-packed.js', "text/javascript", $defer, $async);
			}
		
			self::$jqcLoaded = true;
		}
	}
	
	static function loadCarousel_throttle($defer = false, $async = false, $debug = false)
	{	
		if (self::$jqcthrottleLoaded) {
			return;
		}
		
		Factory::getDocument()->addScript(URI::root(true).'/media/syw/js/carousel/jquery.ba-throttle-debounce.min.js', "text/javascript", $defer, $async);
	
		self::$jqcthrottleLoaded = true;
	}
	
	static function loadCarousel_touch($defer = false, $async = false, $debug = false)
	{	
		if (self::$jqctouchLoaded) {
			return;
		}
		
		Factory::getDocument()->addScript(URI::root(true).'/media/syw/js/carousel/jquery.touchSwipe.min.js', "text/javascript", $defer, $async);
	
		self::$jqctouchLoaded = true;
	}
	
	static function loadCarousel_mousewheel($defer = false, $async = false, $debug = false)
	{	
		if (self::$jqcmousewheelLoaded) {
			return;
		}
		
		Factory::getDocument()->addScript(URI::root(true).'/media/syw/js/carousel/jquery.mousewheel.min.js', "text/javascript", $defer, $async);
	
		self::$jqcmousewheelLoaded = true;
	}
	
	static function loadCarousel_transit($defer = false, $async = false, $debug = false)
	{	
		if (self::$jqctransitLoaded) {
			return;
		}
		
		Factory::getDocument()->addScript(URI::root(true).'/media/syw/js/carousel/jquery.transit.min.js', "text/javascript", $defer, $async);

		self::$jqctransitLoaded = true;
	}
	
	static function triggerLazysizes($jQuery_path = 'img') 
	{		
		if (in_array($jQuery_path, self::$jqhighres)) {
			return;
		}
		
		$javascript = 'jQuery(document).ready(function() { ';
			$javascript .= 'if (window.devicePixelRatio > 1) { '; // undefined > 1 results in false (IE < 11 do not support the property)
				$javascript .= 'jQuery("'.$jQuery_path.'[data-src]").each(function() { ';
					//$javascript .= 'var lowres = jQuery(this).attr("src"); ';
					//$javascript .= 'var highres = lowres.replace(".", "@2x."); ';
					//$javascript .= 'jQuery(this).attr("src", highres); ';
					$javascript .= 'jQuery(this).addClass("lazyload"); ';
				$javascript .= '});';				
						
				//$javascript .= 'jQuery("'.$jQuery_path.'").on("load", function() { alert("tada");';
					//$javascript .= 'var lowres = $(this).attr("src").replace(".", "@2x."); ';
					//$javascript .= 'var highres = lowres.replace(".", "@2x."); ';
					//$javascript .= 'alert(lowres);  ';
					//$javascript .= 'if (jQuery(this).attr("src").indexOf("@2x.") == -1) { ';
						//$javascript .= 'jQuery(this).attr("src", jQuery(this).attr("src").replace(".", "@2x.") + "?" + new Date().getTime()); ';						
						//$javascript .= 'jQuery(this).load(function() {}); ';
					//$javascript .= '}';
				//$javascript .= '});';	
			
			
			$javascript .= '}';
		$javascript .= '});';
				
		Factory::getDocument()->addScriptDeclaration($javascript);
		
		self::$jqhighres[] = $jQuery_path;
	}
	
	/**
	 * Load the comparison version function if needed
	 */
	static function loadCompareVersions()
	{
		if (self::$jqcompareLoaded) {
			return;
		}
				
		// returns false if version e > t (version is 1.3.2 for example)
		$compareScript = 'function SYWCompareVersions(e,t){var r=!1;if(e==t)return!0;"object"!=typeof e&&(e=e.toString().split(".")),"object"!=typeof t&&(t=t.toString().split("."));for(var o=0;o<Math.max(e.length,t.length);o++){if(void 0==e[o]&&(e[o]=0),void 0==t[o]&&(t[o]=0),Number(e[o])<Number(t[o])){r=!0;break}if(e[o]!=t[o])break}return r};';
			
		Factory::getDocument()->addScriptDeclaration($compareScript);
	
		self::$jqcompareLoaded = true;
	}
	
}
?>
