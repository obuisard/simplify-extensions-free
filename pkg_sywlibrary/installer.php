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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Script file for the SYW extensions library package
 */
class Pkg_SYWLibraryInstallerScript
{
	/**
	 * The version number of the extension
	 */
	protected $release;

	/**
	 * The extension name
	 */
	protected $extension;

	/**
	 * Minimum Joomla! version required to install the extension
	 */
	protected $minimumJoomla = '4.0.0-rc4';

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
		if ($action == 'uninstall') {
			return true;
		}

		// make sure we are under Joomla 4.0 or over

		if (version_compare(JVERSION, $this->minimumJoomla, 'lt')) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('JOOMLA_REQUIRED_VERSION', $this->minimumJoomla), 'error');
			return false;
		}

		$this->extension = $installer->getName();
		$this->release = $installer->getManifest()->version;

		return true;
	}

	/**
	 * Called on installation
	 *
	 * @return  boolean  True on success
	 */
	public function install($installer) { }

	/**
	 * Called on update
	 *
	 * @return  boolean  True on success
	 */
	public function update($installer) { }

	/**
	 * Called on uninstallation
	 */
	public function uninstall($installer) { }

	/**
	 * Called after an install/update/uninstall method
	 *
	 * @return boolean True on success
	 */
	public function postflight($action, $installer)
	{
		if ($action == 'uninstall') {
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
 			//Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this library.<br /><a href="' . self::$translation_link . '" target="_blank">Please consider contributing to its translation</a>', 'notice');
 			echo '<div class="alert alert-info">The ' . Factory::getLanguage()->getName() . ' language is missing for this extension.<br /><a href="' . $this->translationLink . '" target="_blank">Please consider contributing to its translation</a>.</div>';
 		}

 		// enable the library plugin

 		$plugin_is_enable = $this->enableExtension('plugin', 'syw', 'system');
 		if (!$plugin_is_enable) {
 			echo '<div class="alert alert-warning">' . Text::sprintf('PKG_SYWLIBRARY_WARNING_ENABLEPLUGIN') . '</div>';
 		}

 		if ($action == 'update') {

			// update warning

			//Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_SYWLIBRARY_WARNING_RELEASENOTES', self::$changelog_link), 'warning');
 			echo '<div class="alert alert-warning">' . Text::sprintf('PKG_SYWLIBRARY_WARNING_RELEASENOTES', $this->changelogLink) . '</div>';
 		}

 		$this->removeFiles();

		return true;
	}

	private function moveFile($file, $source, $destination, $minified_version = '.min')
	{
		if (File::exists(JPATH_SITE . $source . '/' . $file) && !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_SYWLIBRARY_ERROR_CANNOTMOVEFILE', $file), 'warning');
		}

		$file_pieces = explode('.', $file); // assumes only one . in file name
		$file_pieces[0] .= $minified_version;
		$file = implode('.', $file_pieces);

		if (File::exists(JPATH_SITE . $source . '/' . $file) && !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_SYWLIBRARY_ERROR_CANNOTMOVEFILE', $file), 'warning');
		}
	}

	private function removeFiles()
	{
		if (!empty($this->deleteFiles)) {
			foreach ($this->deleteFiles as $filename) {
				if (File::exists(JPATH_ROOT . $filename) && !File::delete(JPATH_ROOT . $filename)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_SYWLIBRARY_ERROR_DELETINGFILEFOLDER', $filename), 'warning');
				}
			}
		}

		if (!empty($this->deleteFolders)) {
			foreach ($this->deleteFolders as $folder) {
				if (Folder::exists(JPATH_ROOT . $folder) && !Folder::delete(JPATH_ROOT . $folder)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_SYWLIBRARY_ERROR_DELETINGFILEFOLDER', $folder), 'warning');
				}
			}
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

}
?>