<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Script file of the menu icons content plugin
 */
class plgfieldssywiconInstallerScript
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
	 * Extensions library link for download
	 */
	protected $libraryDownloadLink = 'https://simplifyyourweb.com/downloads/syw-extension-library';

	/**
	 * Link to the change logs
	 */
	protected $changelogLink = 'https://simplifyyourweb.com/free-products/fields/syw-icon-field/file/282-syw-icon'; // TODO

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
		return $this->installOrUpdateLibrary($installer);
	}

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
		//echo HTMLHelper::image('plg_fields_sywicon/logo.png', 'SYW Icon', null, true);
		echo '<br /><br /><span class="badge bg-dark">'.Text::sprintf('PLG_FIELDS_SYWICON_VERSION', $this->release).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';

		$this->enableExtension('plugin', 'sywicon', 'fields');

		if ($action == 'update') {

			// upgrade warning

			//Factory::getApplication()->enqueueMessage(Text::sprintf('PLG_FIELDS_SYWICON_WARNING_RELEASENOTES', 'https://simplifyyourweb.com/free-products/fields/syw-icon-field/file/282-syw-icon'), 'warning');
			echo '<div class="alert alert-warning">' . Text::sprintf('PLG_FIELDS_SYWICON_WARNING_RELEASENOTES', $this->changelogLink) . '</div>';
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
		} catch (Exception $e) {
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

	private function installOrUpdateLibrary($installer)
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