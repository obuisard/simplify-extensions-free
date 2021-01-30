<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Script file for the jQuery Easy package
 */
class Pkg_JQueryEasyInstallerScript
{
	/**
	 * The version number of the extension
	 */
	protected $release;

	/**
	 * The extension name
	 */
	protected $extension;

	/*
	 * Minimum extensions library version required
	 */
	protected $minimumLibrary = '2.0.0';

	/**
	 * Minimum Joomla! version required to install the extension
	 */
	protected $minimumJoomla = '4.0.0-beta3';

	/**
	 * Available languages
	 */
	protected $availableLanguages = array('bg-BG', 'de-DE', 'en-GB', 'en-US', 'es-CO', 'es-ES', 'fr-FR', 'it-IT', 'nl-NL', 'pt-BR', 'ru-RU', 'sv-SE', 'tr-TR', 'uk-UA');

	/**
	 * Extensions library link for download
	 */
	protected $libraryDownloadLink = 'https://simplifyyourweb.com/downloads/syw-extension-library';

	/**
	 * Link to the change logs
	 */
	protected $changelogLink = 'https://simplifyyourweb.com/downloads/jquery-easy/file/314-jquery-easy';

	/**
	 * Link to the translation page
	 */
	protected $translationLink = 'https://simplifyyourweb.com/translators';

	/**
	 * A list of files to be deleted
	 */
	protected $deleteFiles = array();

	/**
	 * A list of folders to be deleted
	 */
	protected $deleteFolders = array();

	/**
	 * Called before an install/update/uninstall method
	 *
	 * @param string     $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param Installer  $installer  The class calling this method
	 *
	 * @return boolean True on success
	 */
	public function preflight($action, $installer)
	{
		if ($action === 'uninstall') {
			return true;
		}

		// make sure we are under Joomla 4.0 or over

		if (version_compare(JVERSION, $this->minimumJoomla, 'lt')) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('JOOMLA_REQUIRED_VERSION', $this->minimumJoomla), 'error');
			return false;
		}

		$this->extension = $installer->getName();
		$this->release = $installer->getManifest()->version;

		// make sure the library is installed and that it is compatible with the extension
		return $this->installOrUpdateLibrary();
	}

	/**
	 * Called on installation
	 *
	 * @return boolean True on success
	 */
	public function install($installer) { }

	/**
	 * Called on uninstallation
	 */
	public function uninstall($installer) { }

	/**
	 * Called on update
	 *
	 * @return boolean True on success
	 */
	public function update($installer) { }

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
		echo HTMLHelper::image('plg_system_jqueryeasy/logo.png', 'jQuery Easy', null, true);
		echo '<br /><br /><span class="badge badge-dark">'.Text::sprintf('PKG_JQUERYEASY_VERSION', $this->release).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';

 		// language test

 		$current_language = Factory::getLanguage()->getTag();
 		if (!in_array($current_language, $this->availableLanguages)) {
 			//Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this plugin.<br /><a href="' . $this->translationLink . '" target="_blank">Please consider contributing to its translation</a>', 'notice');
 			echo '<div class="alert alert-info">The ' . Factory::getLanguage()->getName() . ' language is missing for this extension.<br /><a href="' . $this->translationLink . '" target="_blank">Please consider contributing to its translation</a>.</div>';
 		}

		if ($action === 'update') {

			// update warning

			//Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_JQUERYEASY_WARNING_RELEASENOTES', $this->changelogLink), 'warning');
			echo '<div class="alert alert-warning">' . Text::sprintf('PKG_JQUERYEASY_WARNING_RELEASENOTES', $this->changelogLink) . '</div>';
		}

		$this->removeFiles();

		return true;
	}

	private function removeFiles()
	{
		if (!empty($this->deleteFiles)) {
			foreach ($this->deleteFiles as $filename) {
				if (File::exists($filename) && !File::delete($filename)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_JQUERYEASY_ERROR_DELETINGFILEFOLDER', $filename), 'warning');
				}
			}
		}

		if (!empty($this->deleteFolders)) {
			foreach ($this->deleteFolders as $folder) {
				if (Folder::exists(JPATH_ROOT.$folder) && !Folder::delete(JPATH_ROOT.$folder)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_JQUERYEASY_ERROR_DELETINGFILEFOLDER', $folder), 'warning');
				}
			}
		}
	}

	private function installOrUpdatePackage($installer, $package_name, $installation_type = 'install')
	{
		// Get the path to the package

		$sourcePath = $installer->getParent()->getPath('source');
		$sourcePackage = $sourcePath . '/packages/'.$package_name.'.zip';

		// Extract and install the package

		$package = InstallerHelper::unpack($sourcePackage);
		$tmpInstaller = new Installer();

		try {
			if ($installation_type == 'install') {
				$installResult = $tmpInstaller->install($package['dir']);
			} else {
				$installResult = $tmpInstaller->update($package['dir']);
			}
		} catch (\Exception $e) {
			return false;
		}

		return true;
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
			//Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
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

	private function installOrUpdateLibrary()
	{
		// install the library and its plugin if missing or outdated

		if (!Folder::exists(JPATH_ROOT . '/libraries/syw') || !Folder::exists(JPATH_ROOT . '/plugins/system/syw')) {
			if (!Folder::exists(JPATH_ROOT . '/libraries/syw')) {
				if (!$this->installOrUpdatePackage($installer, 'lib_syw')) {
					Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_INSTALLFAILED').'<br /><a href="'.$this->libraryDownloadLink.'" target="_blank">'.Text::_('SYWLIBRARY_DOWNLOAD').'</a>', 'error');
					return false;
				}
			}

			if (!Folder::exists(JPATH_ROOT . '/plugins/system/syw')) {
				if (!$this->installOrUpdatePackage($installer, 'plg_system_syw')) {
					Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_INSTALLFAILED').'<br /><a href="'.$this->libraryDownloadLink.'" target="_blank">'.Text::_('SYWLIBRARY_DOWNLOAD').'</a>', 'error');
					return false;
				}
			}

			Factory::getApplication()->enqueueMessage(Text::sprintf('SYWLIBRARY_INSTALLED', $this->minimumLibrary), 'message');
		} else {

			$library_version = strval(simplexml_load_file(JPATH_ADMINISTRATOR . '/manifests/libraries/syw.xml')->version);
			if (!version_compare($library_version, $this->minimumLibrary, 'ge')) {

				if (!$this->installOrUpdatePackage($installer, 'lib_syw', 'update')) {
					Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_UPDATEFAILED').'<br />'.Text::_('SYWLIBRARY_UPDATE'), 'error');
					return false;
				}

				if (!$this->installOrUpdatePackage($installer, 'plg_system_syw', 'update')) {
					Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_UPDATEFAILED').'<br />'.Text::_('SYWLIBRARY_UPDATE'), 'error');
					return false;
				}

				Factory::getApplication()->enqueueMessage(Text::sprintf('SYWLIBRARY_UPDATED', $this->minimumLibrary), 'message');
			}
		}

		if (!PluginHelper::isEnabled('system', 'syw')) {
			if (!$this->enableExtension('plugin', 'syw', 'system')) {
				Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_COULDNOTENABLEPLUGINFORLIBRARY'), 'error');
				return false;
			}
		}

		return true;
	}

}
?>