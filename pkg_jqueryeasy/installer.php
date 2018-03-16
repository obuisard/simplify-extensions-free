<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

\JLoader::import('joomla.filesystem.folder');
\JLoader::import('joomla.filesystem.file');

use Joomla\CMS\Factory;

/**
 * Script file for the jQuery Easy package
 */
class pkg_jqueryeasyInstallerScript
{		
	static $version = '3.0.1';
	static $available_languages = array('de-DE', 'en-GB', 'en-US', 'es-CO', 'es-ES', 'fr-FR', 'it-IT', 'nl-NL', 'pt-BR', 'ru-RU', 'sv-SE', 'tr-TR', 'uk-UA');
	static $changelog_link = 'https://simplifyyourweb.com/downloads/jquery-easy/file/314-jquery-easy';
	static $transifex_link = 'https://www.transifex.com/opentranslators/jquery-easy';
	
	/**
	 * Called before an install/update method
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent) 
	{
		return true;
	}
	
	/**
	 * Called after an install/update method
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent) 
	{				
		echo '<p style="margin: 10px 0 20px 0">';
		echo '<img src="../plugins/system/jqueryeasy/images/logo.png" />';
		echo '<br /><br /><span class="label">'.\JText::sprintf('PKG_JQUERYEASY_VERSION', self::$version).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="http://www.simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';
		
 		// language test 			
 		
 		$current_language = Factory::getLanguage()->getTag();
 		if (!in_array($current_language, self::$available_languages)) {
 			Factory::getApplication()->enqueueMessage(\JText::sprintf('PKG_JQUERYEASY_INFO_LANGUAGETRANSLATE', Factory::getLanguage()->getName()), 'notice');
 		}
		
		if ($type == 'update') {
			
			// delete unnecessary files
				
			$files = array();
			
			$folders = array();
			
			foreach ($files as $file) {
				if (\JFile::exists(JPATH_ROOT.$file) && !\JFile::delete(JPATH_ROOT.$file)) {
					Factory::getApplication()->enqueueMessage(\JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file), 'warning');
				}
			}
			
			foreach ($folders as $folder) {
				if (\JFolder::exists(JPATH_ROOT.$folder) && !\JFolder::delete(JPATH_ROOT.$folder)) {
					Factory::getApplication()->enqueueMessage(\JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder), 'warning');
				}
			}
			
			// update warning
			
			Factory::getApplication()->enqueueMessage(\JText::sprintf('PLG_SYSTEM_JQUERYEASY_WARNING_RELEASENOTES', self::$changelog_link), 'warning');
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