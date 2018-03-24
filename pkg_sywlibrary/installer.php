<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Script file for the SYW extensions library package
 */
class pkg_sywlibraryInstallerScript
{		
	static $version = '2.0.0';	
	static $available_languages = array('en-GB');
	static $changelog_link = 'TODO';
	static $transifex_link = 'https://www.transifex.com/opentranslators/extensions-library';
	
	/**
	 * Called before an install/update/uninstall method
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent) 
	{		
		return true;
	}
	
	/**
	 * Called after an install/update/uninstall method
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent, $results) 
	{				
		echo '<p style="margin: 10px 0 20px 0">';
		echo '<img src="'.Uri::root().'libraries/syw/images/logo.png" />';
		echo '<br /><br /><span class="label">'.\JText::sprintf('PKG_SYWLIBRARY_VERSION', self::$version).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="http://www.simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';
		
 		// language test 			
 		
 		$current_language = Factory::getLanguage()->getTag();
 		if (!in_array($current_language, self::$available_languages)) {
 			Factory::getApplication()->enqueueMessage(\JText::sprintf('PKG_SYWLIBRARY_INFO_LANGUAGETRANSLATE', Factory::getLanguage()->getName()), 'notice');
 		}
		
		if ($type == 'update') {
			
			// update warning
		
			Factory::getApplication()->enqueueMessage(\JText::sprintf('PKG_SYWLIBRARY_WARNING_RELEASENOTES', self::$changelog_link), 'warning');
		}
		
		return true;
	}		
	
	/**
	 * Called on installation
	 *
	 * @return  boolean  True on success
	 */
	public function install($parent) { }
	
	/**
	 * Called on update
	 *
	 * @return  boolean  True on success
	 */
	public function update($parent) { }
	
	/**
	 * Called on uninstallation
	 */
	public function uninstall($parent) { }
	
}
?>