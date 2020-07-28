<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

/**
 * Script file for the SYW Auto Reset system plugin
 */
class plgsystemsywautoresetInstallerScript
{
	static $version = '1.4.0';

	/**
	 * Called before an install/update method
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent)
	{
		if (!Folder::exists(JPATH_ROOT.'/modules/mod_latestnewsenhanced')
		    && !Folder::exists(JPATH_ROOT.'/modules/mod_latestnewsenhancedextended')
			//&& !Folder::exists(JPATH_ROOT.'/modules/mod_trulyresponsiveslides')
			//&& !Folder::exists(JPATH_ROOT.'/modules/mod_trulyresponsiveslider')
		    && !Folder::exists(JPATH_ROOT.'/modules/mod_trombinoscope')
		    && !Folder::exists(JPATH_ROOT.'/modules/mod_weblinklogo')) {

			Factory::getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SYWAUTORESET_MISSINGEXTENSION'), 'warning');
			return false;
		}


		//test Joomla version


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
		echo '<span class="label">'.Text::sprintf('PLG_SYSTEM_SYWAUTORESET_VERSION', self::$version).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';// remove the old module update sites

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