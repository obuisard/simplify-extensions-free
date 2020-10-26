<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Script file for the SYW extensions library package
 */
class pkg_sywlibraryInstallerScript
{
	static $version = '2.0.0';
	static $available_languages = array('bg-BG', 'cs-CZ', 'da-DK', 'de-DE', 'en-GB', 'en-US', 'es-ES', 'fa-IR', 'fi-FI', 'fr-FR', 'ja-JP', 'hu-HU', 'it-IT', 'nl-NL', 'pl-PL', 'pt-BR', 'ru-RU', 'sl-SI', 'sv-SE', 'tr-TR');
	static $changelog_link = 'https://simplifyyourweb.com/downloads/syw-extension-library/file/383-simplify-your-web-extensions-library';
	static $translation_link = 'https://simplifyyourweb.com/translators';

	/**
	 * Called before an install/update/uninstall method
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent)
	{
		if ($type == 'uninstall') {
			return true;
		}

		// make sure we are under Joomla 4.0 or over

		if (version_compare(JVERSION, '3.15.0', 'lt')) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('JOOMLA_REQUIRED_VERSION', '4.0'), 'error');
			return false;
		}

		return true;
	}

	/**
	 * Called after an install/update/uninstall method
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent, $results)
	{
		if ($type == 'uninstall') {
			return true;
		}

		echo '<p style="margin: 10px 0 20px 0">';
		echo HTMLHelper::image('syw/logo.png', 'SimplifyYourWeb Extensions Library', null, true);
		echo '<br /><br /><span class="badge badge-info">' . Text::sprintf('PKG_SYWLIBRARY_VERSION', self::$version) . '</span>';
		echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';

 		// language test

 		$current_language = Factory::getLanguage()->getTag();
 		if (!in_array($current_language, self::$available_languages)) {
 			Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this library.<br /><a href="' . self::$translation_link . '" target="_blank">Please consider contributing to its translation</a>', 'notice');
 		}

 		// enable the library plugin

 		$this->enablePlugin('plugin', 'syw', 'system');

		if ($type == 'update') {

			// update warning

			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_SYWLIBRARY_WARNING_RELEASENOTES', self::$changelog_link), 'warning');
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

	private function enablePlugin($type, $element, $folder = '')
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__extensions'));
		$query->set($db->quoteName('enabled').' = 1');
		$query->where($db->quoteName('type').' = '.$db->quote($type));
		$query->where($db->quoteName('element').' = '.$db->quote($element));
		$query->where($db->quoteName('folder').' = '.$db->quote($folder));

		$db->setQuery($query);

		try {
			$db->execute();
		} catch (\RuntimeException $e) {
			//JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			//return false;
			// ? TODO message to manually enable the plugin
		}

		return true;
	}

}
?>