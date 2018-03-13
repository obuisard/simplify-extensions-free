<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Script file of the jQuery Easy plugin
 */
class plgsystemjqueryeasyInstallerScript
{	
	static $version = '2.1.0';
	static $available_languages = array('de-DE', 'en-GB', 'en-US', 'es-CO', 'es-ES', 'fr-FR', 'it-IT', 'nl-NL', 'pt-BR', 'ru-RU', 'sv-SE', 'tr-TR', 'uk-UA');
	static $changelog_link = 'http://www.simplifyyourweb.com/downloads/jquery-easy/file/58-jquery-easy';
	static $transifex_link = 'https://www.transifex.com/opentranslators/jquery-easy';
				
	/**
	 * Called before an install/update method
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent) {
		
		return true;
	}
	
	/**
	 * Called after an install/update method
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent) 
	{			
		echo '<p style="margin: 20px 0">';
		//echo '<img src="../plugins/system/jqueryeasy/images/logo.png" />';
		echo '<span class="label">'.JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERSIONNUMBER_LABEL', self::$version).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="http://www.simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';	
		
 		// language test 			
 		
 		$current_language = JFactory::getLanguage()->getTag();
 		if (!in_array($current_language, self::$available_languages)) {
 			JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_SYSTEM_JQUERYEASY_INFO_LANGUAGETRANSLATE', JFactory::getLanguage()->getName(), self::$transifex_link), 'notice');
 		}
		
		if ($type == 'update') {
			
			// delete unnecessary files
			
			$files = array(
				'/plugins/system/jqueryeasy/fields/help.php',
				'/plugins/system/jqueryeasy/fields/warningjqueryui.php',
				'/plugins/system/jqueryeasy/fields/warningnoconflict.php',
				'/plugins/system/jqueryeasy/images/jqueryeasyprofiles.png'
			);
			
			$folders = array(
				'/plugins/system/jqueryeasy/fields/help',
				'/plugins/system/jqueryeasy/fields/preview'
			);
			
			foreach ($files as $file) {
				if (JFile::exists(JPATH_ROOT.$file) && !JFile::delete(JPATH_ROOT.$file)) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file), 'warning');
				}
			}
			
			foreach ($folders as $folder) {
				if (JFolder::exists(JPATH_ROOT.$folder) && !JFolder::delete(JPATH_ROOT.$folder)) {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder), 'warning');
				}
			}			
			
			// update warning
			
			JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_SYSTEM_JQUERYEASY_WARNING_RELEASENOTES', self::$changelog_link), 'warning');
		}
		
		return true;
	}
	
	/**
	 * Called on installation
	 *
	 * @return  boolean  True on success
	 */
	public function install($parent) {}
	
	/**
	 * Called on update
	 *
	 * @return  boolean  True on success
	 */
	public function update($parent) {}
	
	/**
	 * Called on uninstallation
	 */
	public function uninstall($parent) {}
	
}
?>
