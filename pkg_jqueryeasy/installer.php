<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

/**
 * Script file for the jQuery Easy package
 */
class pkg_jqueryeasyInstallerScript
{
	static $version = '3.2.3';
	static $available_languages = array('de-DE', 'en-GB', 'en-US', 'es-CO', 'es-ES', 'fr-FR', 'it-IT', 'nl-NL', 'pt-BR', 'ru-RU', 'sv-SE', 'tr-TR', 'uk-UA');
	static $changelog_link = 'https://simplifyyourweb.com/downloads/jquery-easy/file/314-jquery-easy';
	static $transifex_link = 'https://simplifyyourweb.com/translators';

	/**
	 * Called before an install/update method
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent)
	{
	    // make sure we are under Joomla 4.0 or over

	    if (version_compare(JVERSION, '3.15.0', 'lt')) {
	        JFactory::getApplication()->enqueueMessage(JText::sprintf('JOOMLA_REQUIRED_VERSION', '4'), 'error');
	        return false;
	    }

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
		echo '<br /><br /><span class="label">'.Text::sprintf('PKG_JQUERYEASY_VERSION', self::$version).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="http://www.simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';

 		// language test

 		$current_language = Factory::getLanguage()->getTag();
 		if (!in_array($current_language, self::$available_languages)) {
 			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_JQUERYEASY_INFO_LANGUAGETRANSLATE', Factory::getLanguage()->getName()), 'notice');
 		}

		if ($type == 'update') {

			// delete unnecessary files

		    $files = array(
		        '/plugins/system/jqueryeasy/jquerynoconflict.js',
		        '/plugins/system/jqueryeasy/images/SimplifyYourWeb_24.png'
		    );

			$folders = array();

			foreach ($files as $file) {
				if (File::exists(JPATH_ROOT.$file) && !File::delete(JPATH_ROOT.$file)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file), 'warning');
				}
			}

			foreach ($folders as $folder) {
				if (Folder::exists(JPATH_ROOT.$folder) && !Folder::delete(JPATH_ROOT.$folder)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder), 'warning');
				}
			}

			// remove the old update site

			$this->removeUpdateSite('package', 'pkg_jqueryeasy', '', 'http://www.barejoomlatemplates.com/autoupdates/jqueryeasy/jqueryeasy-v3-update.xml');

			// update warning

			Factory::getApplication()->enqueueMessage(Text::sprintf('PLG_SYSTEM_JQUERYEASY_WARNING_RELEASENOTES', self::$changelog_link), 'warning');
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

	private function removeUpdateSite($type, $element, $folder = '', $location = '')
	{
	    $db = JFactory::getDBO();

	    $query = $db->getQuery(true);

	    $query->select('extension_id');
	    $query->from('#__extensions');
	    $query->where($db->quoteName('type').'='.$db->quote($type));
	    $query->where($db->quoteName('element').'='.$db->quote($element));
	    if ($folder) {
	        $query->where($db->quoteName('folder').'='.$db->quote($folder));
	    }

	    $db->setQuery($query);

	    $extension_id = '';
	    try {
	        $extension_id = $db->loadResult();
	    } catch (RuntimeException $e) {
	        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	        return false;
	    }

	    if ($extension_id) {

	        $query->clear();

	        $query->select('update_site_id');
	        $query->from('#__update_sites_extensions');
	        $query->where($db->quoteName('extension_id').'='.$db->quote($extension_id));

	        $db->setQuery($query);

	        $updatesite_id = array(); // can have several results
	        try {
	            $updatesite_id = $db->loadColumn();
	        } catch (RuntimeException $e) {
	            JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	            return false;
	        }

	        if (empty($updatesite_id)) {
	            return false;
	        } else if (count($updatesite_id) == 1) {

	            $query->clear();

	            $query->delete($db->quoteName('#__update_sites'));
	            $query->where($db->quoteName('update_site_id').' = '.$db->quote($updatesite_id[0]));

	            $db->setQuery($query);

	            try {
	                $db->execute();
	            } catch (RuntimeException $e) {
	                JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	                return false;
	            }
	        } else { // several update sites exist for the same extension therefore we need to specify which to delete

	            if ($location) {
	                $query->clear();

	                $query->delete($db->quoteName('#__update_sites'));
	                $query->where($db->quoteName('update_site_id').' IN ('.implode(',', $updatesite_id).')');
	                $query->where($db->quoteName('location').' = '.$db->quote($location));

	                $db->setQuery($query);

	                try {
	                    $db->execute();
	                } catch (RuntimeException $e) {
	                    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	                    return false;
	                }
	            } else {
	                return false;
	            }
	        }
	    } else {
	        return false;
	    }

	    return true;
	}

}
?>