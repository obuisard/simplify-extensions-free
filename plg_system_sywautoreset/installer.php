<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Script file for the SYW Auto Reset system plugin
 */
class plgsystemsywautoresetInstallerScript
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
	//protected $minimumLibrary = '2.0.0';

	/**
	 * Minimum Joomla! version required to install the extension
	 */
	protected $minimumJoomla = '4.0.0-beta3';

	/**
	 * Extensions library link for download
	 */
	//protected $libraryDownloadLink = 'https://simplifyyourweb.com/downloads/syw-extension-library';

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

		if (!Folder::exists(JPATH_ROOT.'/modules/mod_latestnewsenhanced')
		    && !Folder::exists(JPATH_ROOT.'/modules/mod_latestnewsenhancedextended')
			//&& !Folder::exists(JPATH_ROOT.'/modules/mod_trulyresponsiveslides')
			//&& !Folder::exists(JPATH_ROOT.'/modules/mod_trulyresponsiveslider')
		    && !Folder::exists(JPATH_ROOT.'/modules/mod_trombinoscope')
		    && !Folder::exists(JPATH_ROOT.'/modules/mod_weblinklogo')) {

			Factory::getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SYWAUTORESET_MISSINGEXTENSION'), 'warning');
			return false;
		}

		$this->extension = $installer->getName();
		$this->release = $installer->getManifest()->version;

		return true;
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
		echo HTMLHelper::image('plg_system_sywautoreset/logo.png', 'SYW Auto Reset', null, true);
		echo '<br /><br /><span class="badge badge-dark">'.Text::sprintf('PLG_SYSTEM_SYWAUTORESET_VERSION', $this->release).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';

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