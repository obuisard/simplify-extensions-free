<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;

/**
 * Script file for the SYW Auto Reset system plugin
 */
class PlgSystemSYWAutoResetInstallerScript extends InstallerScript
{
    /**
    * Link to the change logs
    */
    protected $changelogLink = 'https://simplifyyourweb.com/free-products/fields/syw-icon-field/file/282-syw-icon'; // TODO

    /**
     * Extension script constructor
     */
    public function __construct($parent)
    {
        $this->extension = 'plg_system_sywautoreset';
        $this->minimumJoomla = '4.0.0';
        //$this->minimumPhp = JOOMLA_MINIMUM_PHP; // not needed
    }

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
		
		// checks minimum PHP and Joomla versions and that an upgrade is performed
		if (!parent::preflight($action, $installer)) {
		    return false;
		}

		if (!is_dir(JPATH_ROOT.'/modules/mod_latestnewsenhanced')
			//&& !is_dir(JPATH_ROOT.'/modules/mod_trulyresponsiveslides')
			//&& !is_dir(JPATH_ROOT.'/modules/mod_trulyresponsiveslider')
		    && !is_dir(JPATH_ROOT.'/modules/mod_trombinoscope')
		    && !is_dir(JPATH_ROOT.'/modules/mod_weblinklogo')) {

			Factory::getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SYWAUTORESET_MISSINGEXTENSION'), 'warning');
			return false;
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

		echo '<p style="margin: 20px 0">';
		echo '<span class="badge bg-dark">' . Text::sprintf('PLG_SYSTEM_SYWAUTORESET_VERSION', $this->release) . '</span>';
		echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';
		
		echo '<p>';
		echo '<a class="btn btn-dark btn-sm text-light me-2" href="index.php?option=com_plugins&view=plugins&filter[folder]=system&filter[element]=sywautoreset">' . Text::_('PLG_SYSTEM_SYWAUTORESET_PLUGIN_SETUP') . '</a>';
		echo '</p>';
		
		if ($action === 'update') {
		    
		    // upgrade warning
		    
		    //echo '<div class="alert alert-warning">' . Text::sprintf('PLG_CONTENT_MENUICON_WARNING_RELEASENOTES', $this->changelogLink) . '</div>';
		}

		return true;
	}

}
