<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Installer\Installer;

/**
 * Script file of the Article Details package
 */
class pkg_articledetailsInstallerScript
{
	static $version = '5.4.0';
	static $minimum_needed_library_version = '2.0.0';
	static $available_languages = array('de-DE', 'en-GB', 'es-ES', 'fa-IR', 'fi-FI', 'fr-FR', 'it-IT', 'nl-NL', 'pt-BR', 'ru-RU', 'sl-SI', 'tr-TR');
	static $download_link = 'https://simplifyyourweb.com/downloads/syw-extension-library';
	static $changelog_link = 'https://simplifyyourweb.com/free-products/article-details/file/364-article-details';
	static $translation_link = 'https://simplifyyourweb.com/translators';

	/**
	 * Called before an install/update/uninstall method
	 *
	 * @return boolean True on success
	 */
	public function preflight($type, $parent)
	{
		if ($type == 'uninstall') {
			return true;
		}

		// make sure we are under Joomla 4.0 or over

		if (version_compare(JVERSION, '3.20.0', 'lt')) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('JOOMLA_REQUIRED_VERSION', '4.0'), 'error');
			return false;
		}

		// check if syw library is present

		if (!Folder::exists(JPATH_ROOT . '/libraries/syw') || !Folder::exists(JPATH_ROOT . '/plugins/system/syw') || !SYW\Library\Version::isCompatible(self::$minimum_needed_library_version)) {

			if (!$this->installOrUpdatePackage($parent, 'lib_syw')) {
				Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_INSTALLFAILED').'<br /><a href="'.self::$download_link.'" target="_blank">'.Text::_('SYWLIBRARY_DOWNLOAD').'</a>', 'error');
				return false;
			}

			if (!$this->installOrUpdatePackage($parent, 'plg_system_syw')) {
				Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_INSTALLFAILED').'<br /><a href="'.self::$download_link.'" target="_blank">'.Text::_('SYWLIBRARY_DOWNLOAD').'</a>', 'error');
				return false;
			}

			// enable the library plugin
			$this->enablePlugin('plugin', 'syw', 'system');

			Factory::getApplication()->enqueueMessage(Text::sprintf('SYWLIBRARY_INSTALLED', self::$minimum_needed_library_version), 'message');
		}

		return true;
	}
	
	/**
	 * Called on installation
	 *
	 * @return boolean True on success
	 */
	public function install($parent) {}
	
	/**
	 * Called on uninstallation
	 */
	public function uninstall($parent) {}
	
	/**
	 * Called on update
	 *
	 * @return boolean True on success
	 */
	public function update($parent) {}

	/**
	 * Called after an install/update/uninstall method
	 *
	 * @return boolean True on success
	 */
	public function postflight($type, $parent)
	{
		if ($type == 'uninstall') {
			return true;
		}

		echo '<p style="margin: 20px 0">';
		echo '<img src="../media/plg_content_articledetails/images/logo.png" />';
		echo '<br /><br /><span class="label">'.Text::sprintf('PKG_ARTICLEDETAILS_VERSION', self::$version).'</span>';
		echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
		echo '</p>';

		// language test

		$current_language = Factory::getLanguage()->getTag();
		if (!in_array($current_language, self::$available_languages)) {
			Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this extension.<br /><a href="' . self::$translation_link . '" target="_blank">Please consider contributing to its translation</a>', 'notice');
		}

		// remove the old plugin update sites

//   	$this->removeUpdateSite('plugin', 'articledetails', 'content', 'http://www.barejoomlatemplates.com/autoupdates/articledetails/articledetails-update.xml');
//     	$this->removeUpdateSite('package', 'pkg_articledetails', '', 'http://www.barejoomlatemplates.com/autoupdates/articledetails/articledetails-pkg-update.xml');

		if ($type == 'update') {

			// update warning

			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_ARTICLEDETAILS_WARNING_RELEASENOTES', self::$changelog_link), 'warning');

			// delete unnecessary files

			$files = array();

// 			$files[] = '/plugins/content/articledetails/fields/styleselect.php';
// 			$files[] = '/plugins/content/articledetails/images/glyphicons-halflings.png';
// 			$files[] = '/plugins/content/articledetails/images/preview.png';
// 			$files[] = '/plugins/content/articledetails/stylemaster.css.php';
// 			$files[] = '/plugins/content/articledetails/printmaster.css.php';
// 			$files[] = '/plugins/content/articledetails/style.css';
// 			$files[] = '/plugins/content/articledetails/print.css';

			foreach ($files as $file) {
				if (File::exists(JPATH_ROOT.$file) && !File::delete(JPATH_ROOT.$file)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_ARTICLEDETAILS_ERROR_DELETINGFILEFOLDER', $file), 'warning');
				}
			}

			// remove old cached headers which may interfere with fixes, updates or new additions

			$filenames_to_delete = array();

			if (function_exists('glob')) {

				// remove old cached headers which may interfere with fixes, updates or new additions

				$filenames = glob(JPATH_SITE.'/media/cache/plg_content_articledetails/style_*.css');
				if ($filenames != false) {
					$filenames_to_delete = array_merge($filenames_to_delete, $filenames);
				}

				$filenames = glob(JPATH_SITE.'/media/cache/plg_content_articledetails/print_*.css');
				if ($filenames != false) {
					$filenames_to_delete = array_merge($filenames_to_delete, $filenames);
				}
			}

			foreach ($filenames_to_delete as $filename) {
				if (File::exists($filename) && !File::delete($filename)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_ARTICLEDETAILS_ERROR_DELETINGFILEFOLDER', $filename), 'warning');
				}
			}

			// move old fields to new subforms

			$plugin = PluginHelper::getPlugin('content', 'articledetails');

			if (is_object($plugin) && !empty($plugin->params)) {

				$plugin_params = json_decode($plugin->params, true);

				$changes_made = false;

				if (isset($plugin_params['show_icons_b1'])) {

					$changes_made = true;

					$j = 0;
					$j_sub = 0;

					$info_blocs = array();

					while ($j < 3) {
						if (isset($plugin_params['info_b'.($j + 1)]) && $plugin_params['info_b'.($j + 1)] != 'none') {
							$info_bloc = array();
							$info_bloc['show_icons'] = isset($plugin_params['show_icons_b'.($j + 1)]) ? $plugin_params['show_icons_b'.($j + 1)] : 0;
							$info_bloc['icon'] = '';
							$info_bloc['prepend'] = isset($plugin_params['prepend_b'.($j + 1)]) ? $plugin_params['prepend_b'.($j + 1)] : '';
							$info_bloc['append'] = '';
							$info_bloc['info'] = $plugin_params['info_b'.($j + 1)];
							$info_bloc['extra_classes'] = isset($plugin_params['extra_classes_b'.($j + 1)]) ? $plugin_params['extra_classes_b'.($j + 1)] : '';
							$info_bloc['new_line'] = isset($plugin_params['new_line_b'.($j + 1)]) ? $plugin_params['new_line_b'.($j + 1)] : 0;
							$info_bloc['showing_in'] = isset($plugin_params['showing_in_b'.($j + 1)]) ? $plugin_params['showing_in_b'.($j + 1)] : '';
							$info_bloc['access'] = 1;

							$info_blocs['information_blocks'.$j_sub] = $info_bloc;
							$j_sub++;
						}
						$j++;
					}

					$j = 0;
					while ($j < 3) {
						unset($plugin_params['show_icons_b'.($j + 1)]);
						unset($plugin_params['prepend_b'.($j + 1)]);
						unset($plugin_params['info_b'.($j + 1)]);
						unset($plugin_params['extra_classes_b'.($j + 1)]);
						unset($plugin_params['new_line_b'.($j + 1)]);
						unset($plugin_params['showing_in_b'.($j + 1)]);
						$j++;
					}

					if (!empty($info_blocs)) {
						$plugin_params['before_title_information_blocks'] = $info_blocs;
					}
				}

				if (isset($plugin_params['show_icons_1'])) {

					$changes_made = true;

					$j = 0;
					$j_sub = 0;

					$info_blocs = array();

					while ($j < 9) {
						if (isset($plugin_params['info_'.($j + 1)]) && $plugin_params['info_'.($j + 1)] != 'none') {
							$info_bloc = array();
							$info_bloc['show_icons'] = isset($plugin_params['show_icons_'.($j + 1)]) ? $plugin_params['show_icons_'.($j + 1)] : 0;
							$info_bloc['icon'] = '';
							$info_bloc['prepend'] = isset($plugin_params['prepend_'.($j + 1)]) ? $plugin_params['prepend_'.($j + 1)] : '';
							$info_bloc['append'] = '';
							$info_bloc['info'] = $plugin_params['info_'.($j + 1)];
							$info_bloc['extra_classes'] = isset($plugin_params['extra_classes_'.($j + 1)]) ? $plugin_params['extra_classes_'.($j + 1)] : '';
							$info_bloc['new_line'] = isset($plugin_params['new_line_'.($j + 1)]) ? $plugin_params['new_line_'.($j + 1)] : 0;
							$info_bloc['showing_in'] = isset($plugin_params['showing_in_'.($j + 1)]) ? $plugin_params['showing_in_'.($j + 1)] : '';
							$info_bloc['access'] = 1;

							$info_blocs['information_blocks'.$j_sub] = $info_bloc;
							$j_sub++;
						}
						$j++;
					}

					$j = 0;
					while ($j < 9) {
						unset($plugin_params['show_icons_'.($j + 1)]);
						unset($plugin_params['prepend_'.($j + 1)]);
						unset($plugin_params['info_'.($j + 1)]);
						unset($plugin_params['extra_classes_'.($j + 1)]);
						unset($plugin_params['new_line_'.($j + 1)]);
						unset($plugin_params['showing_in_'.($j + 1)]);
						$j++;
					}

					if (!empty($info_blocs)) {
						$plugin_params['after_title_information_blocks'] = $info_blocs;
					}
				}

				if (isset($plugin_params['show_icons_foot1'])) {

					$changes_made = true;

					$j = 0;
					$j_sub = 0;

					$info_blocs = array();

					while ($j < 3) {
						if (isset($plugin_params['info_foot'.($j + 1)]) && $plugin_params['info_foot'.($j + 1)] != 'none') {
							$info_bloc = array();
							$info_bloc['show_icons'] = isset($plugin_params['show_icons_foot'.($j + 1)]) ? $plugin_params['show_icons_foot'.($j + 1)] : 0;
							$info_bloc['icon'] = '';
							$info_bloc['prepend'] = isset($plugin_params['prepend_foot'.($j + 1)]) ? $plugin_params['prepend_foot'.($j + 1)] : '';
							$info_bloc['append'] = '';
							$info_bloc['info'] = $plugin_params['info_foot'.($j + 1)];
							$info_bloc['extra_classes'] = isset($plugin_params['extra_classes_foot'.($j + 1)]) ? $plugin_params['extra_classes_foot'.($j + 1)] : '';
							$info_bloc['new_line'] = isset($plugin_params['new_line_foot'.($j + 1)]) ? $plugin_params['new_line_foot'.($j + 1)] : 0;
							$info_bloc['showing_in'] = 2;
							$info_bloc['access'] = 1;

							$info_blocs['information_blocks'.$j_sub] = $info_bloc;
							$j_sub++;
						}
						$j++;
					}

					$j = 0;
					while ($j < 3) {
						unset($plugin_params['show_icons_foot'.($j + 1)]);
						unset($plugin_params['prepend_foot'.($j + 1)]);
						unset($plugin_params['info_foot'.($j + 1)]);
						unset($plugin_params['extra_classes_foot'.($j + 1)]);
						unset($plugin_params['new_line_foot'.($j + 1)]);
						unset($plugin_params['showing_in_foot'.($j + 1)]);
						$j++;
					}

					if (!empty($info_blocs)) {
						$plugin_params['footer_information_blocks'] = $info_blocs;
					}
				}

				if ($changes_made) {

					$db = Factory::getDBO();

					$query = $db->getQuery(true);

					$query->update('#__extensions');
					$query->set($db->quoteName('params').'='.$db->quote(json_encode($plugin_params)));
					$query->where($db->quoteName('type').'='.$db->quote('plugin'));
					$query->where($db->quoteName('folder').'='.$db->quote('content'));
					$query->where($db->quoteName('element').'='.$db->quote('articledetails'));

					$db->setQuery($query);

					try {
						$db->execute();
					} catch (RuntimeException $e) {
						Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
						return false;
					}
				}
			}
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
		} catch (RuntimeException $e) {
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
			} catch (RuntimeException $e) {
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
				} catch (RuntimeException $e) {
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
					} catch (RuntimeException $e) {
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
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

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
		} catch (RuntimeException $e) {
			//JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			return false;
		}

		return true;
	}

}
?>