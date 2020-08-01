<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Script file for the Trombinoscope Contacts Pro Free module package
 */
class pkg_trombinoscopeInstallerScript
{
	static $version = '4.2.0';
	static $minimum_needed_library_version = '2.0.0';
	static $available_languages = array('cs-CZ', 'da-DK', 'de-DE', 'en-GB', 'es-ES', 'fa-IR', 'fi-FI', 'fr-FR', 'nl-NL', 'pt-BR', 'ru-RU', 'sl-SI', 'tr-TR');
	static $download_link = 'http://www.simplifyyourweb.com/downloads/syw-extension-library';
	static $changelog_link = 'http://www.simplifyyourweb.com/free-products/trombinoscope/file/353-trombinoscope-contacts';
	static $translation_link = 'https://simplifyyourweb.com/translators';

	/**
	 * Called before an install/update method
	 *
	 * @return  boolean  True on success
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

	    if (!Folder::exists(JPATH_ROOT.'/libraries/syw') || !Folder::exists(JPATH_ROOT . '/plugins/system/syw') || !PluginHelper::isEnabled('system', 'syw') || !SYW\Library\Version::isCompatible(self::$minimum_needed_library_version)) {

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

	/**
	 * Called after an install/update method
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent)
	{
		if ($type == 'uninstall') {
			return true;
		}

        echo '<p style="margin: 10px 0 20px 0">';
    	echo '<img src="../media/mod_trombinoscope/images/logo.png" />';
    	echo '<br /><br /><span class="label">'.Text::sprintf('PKG_TROMBINOSCOPE_VERSION', self::$version).'</span>';
    	echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
    	echo '</p>';

     	// language test

     	$current_language = Factory::getLanguage()->getTag();
     	if (!in_array($current_language, self::$available_languages)) {
     		Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this extension.<br /><a href="' . self::$translation_link . '" target="_blank">Please consider contributing to its translation</a>', 'notice');
     	}

     	// link to Quickstart

     	$message = Text::sprintf('PKG_TROMBINOSCOPE_INFO_LEARN', 'https://simplifyyourweb.com/documentation/trombinoscope-contacts/quickstart-guide');
     	$message .= '<br /><br /><a href="https://simplifyyourweb.com/documentation/trombinoscope-contacts/quickstart-guide" target="_blank"><img src="../modules/mod_trombinoscope/images/quickstart.png" /></a>';

     	Factory::getApplication()->enqueueMessage($message, 'notice');

     	// remove the old module update sites

//      	$this->removeUpdateSite('package', 'pkg_trombinoscope', '', 'http://www.barejoomlatemplates.com/autoupdates/trombinoscope/trombinoscope-update.xml');
//      	$this->removeUpdateSite('package', 'pkg_trombinoscopecontacts', '', 'http://www.barejoomlatemplates.com/autoupdates/trombinoscope/trombinoscope-update.xml');
//      	$this->removeUpdateSite('package', 'pkg_trombinoscopecontacts', '', 'https://updates.simplifyyourweb.com/free/trombinoscope/trombinoscope-update.xml');

		// move default silhouettes to /images

     	$imagefiles = array();
     	$imagefiles[] = 'no-image-available-100x120.jpg';
     	$imagefiles[] = 'no-photo-86x110.jpg';
     	$imagefiles[] = 'silhouette-100x120.jpg';
     	$imagefiles[] = 'silhouette-transparent-100x120.png';

     	$media_params = ComponentHelper::getParams('com_media');
     	$images_path = $media_params->get('image_path', 'images');

     	foreach ($imagefiles as $imagefile) {
     		$src = JPATH_ROOT.'/media/mod_trombinoscope/images/'.$imagefile;
     		$dest = JPATH_ROOT.'/'.$images_path.'/'.$imagefile;

     		if (!File::copy($src, $dest)) {
     			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_WARNING_COULDNOTCOPYFILE', $imagefile), 'warning');
     		}
     	}

		if ($type == 'update') {

			// update warning

			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_WARNING_RELEASENOTES', self::$changelog_link), 'warning');

			// uninstall deprecated themes that were installed as a separate download

			$db = Factory::getDBO();

			$query = $db->getQuery(true);

			$old_themes = array('theme', 'theme_2', 'theme_3', 'theme_4'); // what about picturebackground ?

			foreach ($old_themes as $theme) {

			    $query->clear();

			    $query->select('extension_id');
			    $query->from('#__extensions');
			    $query->where($db->quoteName('type').'='.$db->quote('file'));
			    $query->where($db->quoteName('element').'='.$db->quote($theme));

			    $db->setQuery($query);

			    try {
			        $themeid = $db->loadResult();
			        if (!empty($themeid)) {
			            $installer = new Installer();
			            $success = $installer->uninstall('file', $themeid);
			            if ($success) {
			                Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_UNINSTALLED', ucfirst($theme)), 'message');
			            } else {
			                Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_ERRORUNINSTALLING', ucfirst($theme)), 'error');
			            }
			        }
			    } catch (\RuntimeException $e) {
			        Factory::getApplication()->enqueueMessage($themeid.Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			    }
			}

			// delete unnecessary files

			$files = array();

			$folders = array();

			foreach ($files as $file) {
				if (File::exists(JPATH_ROOT.$file) && !File::delete(JPATH_ROOT.$file)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_ERROR_DELETINGFILEFOLDER', $file), 'warning');
				}
			}

			foreach ($folders as $folder) {
				if (Folder::exists(JPATH_ROOT.$folder) && !Folder::delete(JPATH_ROOT.$folder)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_ERROR_DELETINGFILEFOLDER', $folder), 'warning');
				}
			}

			// remove old cached headers which may interfere with fixes, updates or new additions

			$filenames_to_delete = array();

			if (function_exists('glob')) {

				$filenames = glob(JPATH_SITE.'/media/cache/mod_trombinoscopecontacts/style_*.{css,js}', GLOB_BRACE);
				if ($filenames != false) {
					$filenames_to_delete = array_merge($filenames_to_delete, $filenames);
				}

				$filenames = glob(JPATH_SITE.'/media/cache/mod_trombinoscopecontacts/animation_*.js');
				if ($filenames != false) {
					$filenames_to_delete = array_merge($filenames_to_delete, $filenames);
				}

				// from previous versions

// 				$filenames = glob(JPATH_ROOT.'/modules/mod_trombinoscope/themes/stylemaster_*.{css,js}', GLOB_BRACE);
// 				if ($filenames != false) {
// 					$filenames_to_delete = array_merge($filenames_to_delete, $filenames);
// 				}

// 				$filenames = glob(JPATH_ROOT.'/modules/mod_trombinoscope/animationmaster_*.js');
// 				if ($filenames != false) {
// 					$filenames_to_delete = array_merge($filenames_to_delete, $filenames);
// 				}
			}

			foreach ($filenames_to_delete as $filename) {
				if (File::exists($filename) && !File::delete($filename)) {
					Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_ERROR_DELETINGFILEFOLDER', $filename), 'warning');
				}
			}

			// overrides warning

			$defaultemplate = $this->getDefaultTemplate();

			if ($defaultemplate) {
				$overrides_path = JPATH_ROOT.'/templates/'.$defaultemplate.'/html/';

				if (Folder::exists($overrides_path.'mod_trombinoscope')) {
					Factory::getApplication()->enqueueMessage(Text::_('PKG_TROMBINOSCOPE_WARNING_OVERRIDES'), 'warning');
				}
			}

			// update old instances to the new subforms

			$db = Factory::getDBO();

			$query = $db->getQuery(true);

			$query->select('id');
			$query->select('params');
			$query->from('#__modules');
			$query->where($db->quoteName('module').'='.$db->quote('mod_trombinoscope'));

			$db->setQuery($query);

			$tc_instances = array();
			try {
				$tc_instances = $db->loadObjectList();
			} catch (ExecutionFailureException $e) {
				Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				return false;
			}

			foreach ($tc_instances as $tc_instance) {

				// get info fields and transform them into new subform

				$instance_params = json_decode($tc_instance->params, true);

				if (isset($instance_params['f1'])) {

					// fields

					$j = 0;
					$j_sub = 0;

					$info_blocs = array();

					while ($j < 7) {
						if (isset($instance_params['f'.($j + 1)]) && $instance_params['f'.($j + 1)] != 'none') {
							$info_bloc = array();
							$info_bloc['f'] = $instance_params['f'.($j + 1)];
							$info_bloc['s_f_lbl'] = isset($instance_params['s_f'.($j + 1).'_lbl']) ? $instance_params['s_f'.($j + 1).'_lbl'] : 0;
							$info_bloc['f_lbl'] = isset($instance_params['f'.($j + 1).'_lbl']) ? $instance_params['f'.($j + 1).'_lbl'] : '';
							$info_bloc['f_icon'] = isset($instance_params['f'.($j + 1).'_icon']) ? $instance_params['f'.($j + 1).'_icon'] : '';
							$info_bloc['f_tooltip'] = isset($instance_params['f'.($j + 1).'_tooltip']) ? $instance_params['f'.($j + 1).'_tooltip'] : 1;
							$info_bloc['f_one_line'] = isset($instance_params['f'.($j + 1).'_one_line']) ? $instance_params['f'.($j + 1).'_one_line'] : 1;
							$info_bloc['f_access'] =  isset($instance_params['f'.($j + 1).'_access']) ? $instance_params['f'.($j + 1).'_access'] : 1;

							$info_blocs['detail_blocks'.$j_sub] = $info_bloc;
							$j_sub++;
						}
						$j++;
					}

					$j = 0;
					while ($j < 7) {
						unset($instance_params['f'.($j + 1)]);
						unset($instance_params['s_f'.($j + 1).'_lbl']);
						unset($instance_params['f'.($j + 1).'_lbl']);
						unset($instance_params['f'.($j + 1).'_icon']);
						unset($instance_params['f'.($j + 1).'_tooltip']);
						unset($instance_params['f'.($j + 1).'_one_line']);
						unset($instance_params['f'.($j + 1).'_access']);
						$j++;
					}

					if (!empty($info_blocs)) {
						$instance_params['detail_blocks'] = $info_blocs;
					}

					// field links

					$j = 0;
					$j_sub = 0;

					$info_blocs = array();

					while ($j < 5) {
						if (isset($instance_params['lf'.($j + 1)]) && $instance_params['lf'.($j + 1)] != 'none') {
							$info_bloc = array();
							$info_bloc['lf'] = $instance_params['lf'.($j + 1)];
							$info_bloc['lf_icon'] = isset($instance_params['lf'.($j + 1).'_icon']) ? $instance_params['lf'.($j + 1).'_icon'] : '';
							$info_bloc['lf_access'] =  isset($instance_params['lf'.($j + 1).'_access']) ? $instance_params['lf'.($j + 1).'_access'] : 1;

							$info_blocs['detaillink_blocks'.$j_sub] = $info_bloc;
							$j_sub++;
						}
						$j++;
					}

					$j = 0;
					while ($j < 5) {
						unset($instance_params['lf'.($j + 1)]);
						unset($instance_params['lf'.($j + 1).'_icon']);
						unset($instance_params['lf'.($j + 1).'_access']);
						$j++;
					}

					if (!empty($info_blocs)) {
						$instance_params['detaillink_blocks'] = $info_blocs;
					}

					$query->clear();

					$query->update('#__modules');
					$query->set($db->quoteName('params').'='.$db->quote(json_encode($instance_params)));
					$query->where($db->quoteName('id').'='.$db->quote($tc_instance->id));

					$db->setQuery($query);

					try {
						$db->execute();
					} catch (ExecutionFailureException $e) {
						Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
						return false;
					}
				}
			}
		}

		return true;
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
		$tmpInstaller = new Installer;

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
		} catch (ExecutionFailureException $e) {
			//JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			return false;
		}

		return true;
	}

}
?>