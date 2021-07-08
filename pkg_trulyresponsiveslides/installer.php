<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Script file for the packaged Truly Responsive Slides module
 */
class Pkg_TrulyResponsiveSlidesInstallerScript
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
	protected $availableLanguages = array('de-DE', 'en-GB', 'fa-IR', 'fr-FR', 'ja-JP', 'nl-NL', 'pl-PL', 'ru-RU', 'sl-SI', 'tr-TR');

	/**
	 * Extensions library link for download
	 */
	protected $libraryDownloadLink = 'https://simplifyyourweb.com/downloads/syw-extension-library';

	/**
	 * Link to the change logs
	 */
	protected $changelogLink = 'https://simplifyyourweb.com/free-products/truly-responsive-slides/file/369-truly-responsive-slides';

	/**
	 * Link to the translation page
	 */
	protected $translationLink = 'https://simplifyyourweb.com/translators';

	/**
	 * Link to the quick start page
	 */
	protected $quickstartLink = 'https://simplifyyourweb.com/documentation/truly-responsive-slides/quickstart-guide';

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
	 * @return  boolean  True on success
	 */
	public function install($installer) {}

	/**
	 * Called on update
	 *
	 * @return  boolean  True on success
	 */
	public function update($installer) {}

	/**
	 * Called on uninstallation
	 */
	public function uninstall($installer) {}

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
        echo HTMLHelper::image('mod_trulyresponsiveslides/logo.png', 'Truly Responsive Slides', null, true);
        echo '<br /><br /><span class="badge bg-dark">'.Text::sprintf('PKG_TRULYRESPONSIVESLIDES_VERSION', $this->release).'</span>';
        echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
        echo '</p>';

   		// language test

   		$current_language = Factory::getLanguage()->getTag();
   		if (!in_array($current_language, $this->availableLanguages)) {
   			//Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this extension.<br /><a href="' . $this->translationLink . '" target="_blank">Please consider contributing to its translation</a>', 'notice');
   			echo '<div class="alert alert-info">The ' . Factory::getLanguage()->getName() . ' language is missing for this extension.<br /><a href="' . $this->translationLink . '" target="_blank">Please consider contributing to its translation</a>.</div>';
   		}

   		if ($action === 'install') {

   			// link to Quickstart

   			$message = Text::sprintf('PKG_TRULYRESPONSIVESLIDES_INFO_LEARN', $this->quickstartLink);
   			$message .= '<br /><br /><a href="' . $this->quickstartLink . '" target="_blank">' . HTMLHelper::image('mod_trulyresponsiveslides/quickstart.png', 'Quick Start', null, true) . '</a>';

   			//Factory::getApplication()->enqueueMessage($message, 'notice');
   			echo '<div class="alert alert-info">' . $message . '</div>';
   		}

		if ($action === 'update') {

			// update warning

			//Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TRULYRESPONSIVESLIDES_WARNING_RELEASENOTES', $this->changelogLink), 'warning');
			echo '<div class="alert alert-warning">' . Text::sprintf('PKG_TRULYRESPONSIVESLIDES_WARNING_RELEASENOTES', $this->changelogLink) . '</div>';

			// overrides warning

			$defaultemplate = $this->getDefaultTemplate();

			if ($defaultemplate) {
				$overrides_path = JPATH_ROOT.'/templates/'.$defaultemplate.'/html/';

				if (Folder::exists($overrides_path.'mod_trulyresponsiveslides')) {
					Factory::getApplication()->enqueueMessage(Text::_('PKG_TRULYRESPONSIVESLIDES_WARNING_OVERRIDES'), 'warning');
				}
			}

			// remove old cached headers which may interfere with fixes, updates or new additions

			if (function_exists('glob')) {

				$filenames = glob(JPATH_SITE.'/media/cache/mod_trulyresponsiveslides/style_*.css');
	 			if ($filenames != false) {
	 				$this->deleteFiles = array_merge($this->deleteFiles, $filenames);
	 			}

	 			$filenames = glob(JPATH_SITE.'/media/cache/mod_trulyresponsiveslides/animation_*.js');
	 			if ($filenames != false) {
	 				$this->deleteFiles = array_merge($this->deleteFiles, $filenames);
	 			}
			}
		}

		$this->removeFiles();

		return true;
	}

	private function moveFile($file, $source, $destination, $minified_version = '.min')
	{
		if (File::exists(JPATH_SITE . $source . '/' . $file) && !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TRULYRESPONSIVESLIDES_ERROR_CANNOTMOVEFILE', $file), 'warning');
		}

		$file_pieces = explode('.', $file); // assumes only one . in file name
		$file_pieces[0] .= $minified_version;
		$file = implode('.', $file_pieces);

		if (File::exists(JPATH_SITE . $source . '/' . $file) && !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TRULYRESPONSIVESLIDES_ERROR_CANNOTMOVEFILE', $file), 'warning');
		}
	}

	private function removeFiles()
	{
		if (!empty($this->deleteFiles)) {
			foreach ($this->deleteFiles as $filename) {
				if (File::exists(JPATH_SITE . $filename) && !File::delete(JPATH_SITE . $filename)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TRULYRESPONSIVESLIDES_ERROR_DELETINGFILEFOLDER', $filename), 'warning');
				}
			}
		}

		if (!empty($this->deleteFolders)) {
			foreach ($this->deleteFolders as $folder) {
				if (Folder::exists(JPATH_ROOT . $folder) && !Folder::delete(JPATH_ROOT . $folder)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TRULYRESPONSIVESLIDES_ERROR_DELETINGFILEFOLDER', $folder), 'warning');
				}
			}
		}
	}

	private function getDefaultTemplate()
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true);

		$query->select('template');
		$query->from('#__template_styles');
		$query->where($db->quoteName('client_id').'= 0');
		$query->where($db->quoteName('home').'= 1');

		$db->setQuery($query);

		$defaultemplate = '';

		try {
			$defaultemplate = $db->loadResult();
		} catch (ExecutionFailureException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $defaultemplate;
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

	private function installOrUpdatePackage($parent, $package_name, $installation_type = 'install')
	{
		// Get the path to the package

		$sourcePath = $parent->getParent()->getPath('source');
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