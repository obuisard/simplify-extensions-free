<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Filesystem\File;

/**
 * Script file for the SYW extensions library package
 */
class Pkg_SYWLibraryInstallerScript extends InstallerScript
{
	/**
	 * Available languages
	 */
	protected $availableLanguages = array('bg-BG', 'cs-CZ', 'da-DK', 'de-DE', 'en-GB', 'en-US', 'es-ES', 'fa-IR', 'fi-FI', 'fr-FR', 'hu-HU', 'it-IT', 'ja-JP', 'nl-NL', 'pl-PL', 'pt-BR', 'ru-RU', 'sl-SI', 'sv-SE', 'tr-TR');

	/**
	 * Link to the change logs
	 */
	protected $changelogLink = 'https://simplifyyourweb.com/downloads/syw-extension-library/file/383-simplify-your-web-extensions-library';

	/**
	 * Link to the translation page
	 */
	protected $translationLink = 'https://simplifyyourweb.com/translators';

	/**
	 * Extension script constructor
	 */
	public function __construct($installer)
	{
	    $this->extension = 'lib_syw';
	    $this->minimumJoomla = '4.1.0';
	    $this->minimumPhp = '7.4.0';
	}
	
	/**
	 * Called before any type of action
	 *
	 * @param string $action Which action is happening (install|uninstall|discover_install|update)
	 * @param InstallerAdapter $installer The class calling this method
	 *
	 * @return boolean True on success
	 */
	public function preflight($action, $installer)
	{
		if ($action === 'uninstall') {
			return true;
		}
		
		// checks minimum PHP and Joomla versions and that an upgrade is performed
		if (!parent::preflight($action, $installer)) {
		    return false;
		}

		return true;
	}

	/**
	 * method to install the component
	 *
	 * @return boolean True on success
	 */
	public function install($installer) {}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	public function uninstall($installer) {}

	/**
	 * method to update the component
	 *
	 * @return boolean True on success
	 */
	public function update($installer) {}

	/**
	 * Called after an install/update/uninstall method
	 *
	 * @return boolean True on success
	 */
	public function postflight($action, $installer)
	{
		if ($action === 'uninstall') {
			return true;
		}

		echo '<p style="margin: 10px 0 20px 0">';
		echo HTMLHelper::image('syw/logo.png', 'SimplifyYourWeb Extensions Library', null, true);
		echo '<br /><br /><span class="badge bg-dark">' . Text::sprintf('PKG_SYWLIBRARY_VERSION', $this->release) . '</span>';
		echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';

 		// language test

 		$current_language = Factory::getLanguage()->getTag();
 		if (!in_array($current_language, $this->availableLanguages)) {
 		    Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this component.<br /><a href="' . $this->translationLink . '" target="_blank">Please consider contributing to its translation</a> and get a license upgrade for your help!', 'info');
 		}

 		// enable the library plugin

 		$plugin_is_enable = $this->enableExtension('plugin', 'syw', 'system');
 		if (!$plugin_is_enable) {
 		    echo '<p><a class="btn btn-dark text-light" href="index.php?option=com_plugins&view=plugins&filter[folder]=system&filter[element]=syw&filter[enabled]=0"><i class="fa fa-stopwatch"></i> ' . Text::_('PKG_SYWLIBRARY_WARNING_ENABLEPLUGIN') . '</a></p>';
 		}

 		if ($action == 'update') {
 		
            // remove the old update site
 		
 			$this->removeUpdateSite('library', 'syw', '', 'http://www.barejoomlatemplates.com/autoupdates/sywlibrary/sywlibrary-update.xml');
 			
 			// files to remove
 			
 			$this->deleteFiles[] = '/media/syw/js/purepajinate/purePajinate.es6.js';
 			$this->deleteFiles[] = '/media/syw/js/purepajinate/purePajinate.es6.min.js';
 		}

 		$this->removeFiles();

		return true;
	}

	private function moveFile($file, $source, $destination, $minified_version = '.min')
	{
		if (is_file(JPATH_SITE . $source . '/' . $file) && !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_SYWLIBRARY_ERROR_CANNOTMOVEFILE', $file), 'warning');
		}

		$file_pieces = explode('.', $file); // assumes only one . in file name
		$file_pieces[0] .= $minified_version;
		$file = implode('.', $file_pieces);

		if (is_file(JPATH_SITE . $source . '/' . $file) && !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_SYWLIBRARY_ERROR_CANNOTMOVEFILE', $file), 'warning');
		}
	}

	private function enableExtension($type, $element, $folder = '', $enable = true)
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__extensions'));
		if ($enable) {
			$query->set($db->quoteName('enabled').' = 1');
		} else {
			$query->set($db->quoteName('enabled').' = 0');
		}
		$query->where($db->quoteName('type').' = '.$db->quote($type));
		$query->where($db->quoteName('element').' = '.$db->quote($element));
		if ($folder) {
			$query->where($db->quoteName('folder').' = '.$db->quote($folder));
		}

		$db->setQuery($query);

		try {
			$db->execute();
		} catch (ExecutionFailureException $e) {
			return false;
		}

		return true;
	}
	
	private function removeUpdateSite($type, $element, $folder = '', $location = '')
	{
	    $db = Factory::getDBO();
	    
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
	    } catch (ExecutionFailureException $e) {
	        Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
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
	        } catch (ExecutionFailureException $e) {
	            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
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
	            } catch (ExecutionFailureException $e) {
	                Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
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
	                } catch (ExecutionFailureException $e) {
	                    Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
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