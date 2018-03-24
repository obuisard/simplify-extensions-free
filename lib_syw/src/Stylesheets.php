<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class Stylesheets 
{	
	static $twodtransitionsLoaded = false;
	static $bgtransitionsLoaded = false;
		
	/**
	 * Load the 2d transitions stylesheet if needed
	 */
	static function load2DTransitions()
	{
		if (self::$twodtransitionsLoaded) {
			return;
		}
		
		Factory::getDocument()->addStyleSheet(URI::root(true).'/media/syw/css/2d-transitions-min.css');
			
		self::$twodtransitionsLoaded = true;
	}
	
	/**
	 * Load the background transitions stylesheet if needed
	 */
	static function loadBGTransitions()
	{
		if (self::$bgtransitionsLoaded) {
			return;
		}
	
		Factory::getDocument()->addStyleSheet(URI::root(true).'/media/syw/css/bg-transitions-min.css');
			
		self::$bgtransitionsLoaded = true;
	}
	
}
?>
