<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Script file of the menu icons content plugin
 */
class plgfieldssywiconInstallerScript extends InstallerScript
{
	/*
	 * Minimum extensions library version required
	 */
	protected $minimumLibrary = '2.2.1';
	/**
	 * Extensions library link for download
	 */
	protected $libraryDownloadLink = 'https://simplifyyourweb.com/downloads/syw-extension-library';

	/**
	 * Link to the change logs
	 */
	protected $changelogLink = 'https://simplifyyourweb.com/free-products/fields/syw-icon-field/file/431-syw-icon';

	/**
	 * Extension script constructor
	 */
	public function __construct($installer)
	{
	    $this->extension = 'plg_fields_sywicon';
	    $this->minimumJoomla = '4.0.0';
	    //$this->minimumPhp = JOOMLA_MINIMUM_PHP; // not needed
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

		// make sure the library is installed and that it is compatible with the extension
		return $this->installOrUpdateLibrary($installer);
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
	 * Called after any type of action
	 *
	 * @param string $action Which action is happening (install|uninstall|discover_install|update)
	 * @param InstallerAdapter $installer The object responsible for running this script
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
		echo '<span class="badge bg-dark">'.Text::sprintf('PLG_FIELDS_SYWICON_VERSION', $this->release).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';

		$this->enableExtension('plugin', 'sywicon', 'fields');

		if ($action == 'update') {

			// upgrade warning

			echo '<div class="alert alert-warning">' . Text::sprintf('PLG_FIELDS_SYWICON_WARNING_RELEASENOTES', $this->changelogLink) . '</div>';
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
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			return false;
		}

		return true;
	}

	private function installOrUpdatePackage($installer, $package_name, $installation_type = 'install')
	{
	    // Get the path to the package

	    $sourcePath = $installer->getParent()->getPath('source');
	    $sourcePackage = $sourcePath . '/packages/'.$package_name.'.zip';

	    // Extract and install the package

        $package = InstallerHelper::unpack($sourcePackage);
        if ($package === false || (is_array($package) && $package['type'] === false)) {
            return false;
        }

        $tmpInstaller = new Installer();

        if ($installation_type === 'install') {
            return $tmpInstaller->install($package['dir']);
        } else {
            return $tmpInstaller->update($package['dir']);
        }
	}

	/**
	 * Install the library and its plugin if missing or outdated
	 */
	private function installOrUpdateLibrary($installer)
	{
	    if (!Folder::exists(JPATH_ROOT . '/libraries/syw') || !Folder::exists(JPATH_ROOT . '/plugins/system/syw')) {

	        if (!$this->installOrUpdatePackage($installer, 'pkg_sywlibrary')) {
	            Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_INSTALLFAILED').'<br /><a href="'.$this->libraryDownloadLink.'" target="_blank">'.Text::_('SYWLIBRARY_DOWNLOAD').'</a>', 'error');
	            return false;
	        }

	        Factory::getApplication()->enqueueMessage(Text::sprintf('SYWLIBRARY_INSTALLED', $this->minimumLibrary), 'message');
	    } else {

	        $library_version = strval(simplexml_load_file(JPATH_ADMINISTRATOR . '/manifests/libraries/syw.xml')->version);
	        if (!version_compare($library_version, $this->minimumLibrary, 'ge')) {

	            if (!$this->installOrUpdatePackage($installer, 'pkg_sywlibrary', 'update')) {
	                Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_UPDATEFAILED').'<br />'.Text::_('SYWLIBRARY_UPDATE'), 'error');
	                return false;
	            }

	            Factory::getApplication()->enqueueMessage(Text::sprintf('SYWLIBRARY_UPDATED', $this->minimumLibrary), 'message');
	        }
	    }

		return true;
	}

}
?>