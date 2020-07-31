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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Script file for the packaged Latest News Enhanced module
 */
class pkg_latestnewsenhancedInstallerScript
{
	static $version = '4.15.0';
	static $minimum_needed_library_version = '2.0.0';
	static $available_languages = array('da-DK', 'de-DE', 'en-GB', 'es-ES', 'fi-FI', 'fr-FR', 'hu-HU', 'it-IT', 'ja-JP', 'nl-NL', 'pl-PL', 'pt-BR', 'ru-RU', 'sl-SI', 'tr-TR');
	static $download_link = 'http://www.simplifyyourweb.com/downloads/syw-extension-library';
	static $changelog_link = 'http://www.simplifyyourweb.com/free-products/latest-news-enhanced/file/162-latest-news-enhanced';
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
	public function install($parent) {}

	/**
	 * Called on uninstallation
	 */
	public function uninstall($parent) {}

	/**
	 * Called on update
	 *
	 * @return  boolean  True on success
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

   	    echo '<p style="margin: 10px 0 20px 0">';
   	    echo '<img src="../media/mod_latestnewsenhanced/images/logo.png" />';
   	    echo '<br /><br /><span class="label">'.Text::sprintf('PKG_LATESTNEWSENHANCED_VERSION', self::$version).'</span>';
   	    echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
   	    echo '</p>';

   	    // language test

   	    $current_language = Factory::getLanguage()->getTag();
   	    if (!in_array($current_language, self::$available_languages)) {
   	        Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this extension.<br /><a href="' . self::$translation_link . '" target="_blank">Please consider contributing to its translation</a>', 'notice');
   	    }

   	    // link to Quickstart

   	    $message = Text::sprintf('PKG_LATESTNEWSENHANCED_INFO_LEARN', 'https://simplifyyourweb.com/documentation/latest-news/quickstart-guide');
   	    $message .= '<br /><br /><a href="https://simplifyyourweb.com/documentation/latest-news/quickstart-guide" target="_blank"><img src="../media/mod_latestnewsenhanced/images/quickstart.png" /></a>';

    	Factory::getApplication()->enqueueMessage($message, 'notice');

    	// remove the old module update site

//     	$this->removeUpdateSite('module', 'mod_latestnewsenhanced');
//     	$this->removeUpdateSite('package', 'pkg_latestnewsenhanced', '', 'http://www.barejoomlatemplates.com/autoupdates/latestnewsenhanced/latestnewsenhanced-pkg-update.xml');
//     	$this->removeUpdateSite('package', 'pkg_latestnewsenhanced', '', 'http://www.barejoomlatemplates.com/autoupdates/latestnewsenhanced/latestnewsenhanced-pkg-v4-update.xml');

	    if ($type == 'update') {

	        // update warning

	        Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_LATESTNEWSENHANCED_WARNING_RELEASENOTES', self::$changelog_link), 'warning');

	        // delete unnecessary files

	        $files = array();

// 	        $files[] = '/modules/mod_latestnewsenhanced/animationmaster.js.php';
// 	        $files[] = '/modules/mod_latestnewsenhanced/stylemaster.css.php';
// 	        $files[] = '/modules/mod_latestnewsenhanced/stylemaster.js.php';

	        foreach ($files as $file) {
	            if (File::exists(JPATH_ROOT.$file) && !File::delete(JPATH_ROOT.$file)) {
	                Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_LATESTNEWSENHANCED_ERROR_DELETINGFILEFOLDER', $file), 'warning');
	            }
	        }

	        // remove old cached headers which may interfere with fixes, updates or new additions

	        $filenames_to_delete = array();

	        if (function_exists('glob')) {

	            $filenames = glob(JPATH_SITE.'/media/cache/mod_latestnewsenhanced/style_*.{css,js}', GLOB_BRACE);
	            if ($filenames != false) {
	                $filenames_to_delete = array_merge($filenames_to_delete, $filenames);
	            }

	            $filenames = glob(JPATH_SITE.'/media/cache/mod_latestnewsenhanced/animation_*.js');
	            if ($filenames != false) {
	                $filenames_to_delete = array_merge($filenames_to_delete, $filenames);
	            }

	            // from previous versions

// 	            $filenames = glob(JPATH_ROOT.'/modules/mod_latestnewsenhanced/stylemaster_*.{css,js}', GLOB_BRACE);
// 	            if ($filenames != false) {
// 	                $filenames_to_delete = array_merge($filenames_to_delete, $filenames);
// 	            }

// 	            $filenames = glob(JPATH_ROOT.'/modules/mod_latestnewsenhanced/animationmaster_*.js');
// 	            if ($filenames != false) {
// 	                $filenames_to_delete = array_merge($filenames_to_delete, $filenames);
// 	            }
	        }

	        foreach ($filenames_to_delete as $filename) {
	            if (File::exists($filename) && !File::delete($filename)) {
	                Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_LATESTNEWSENHANCED_ERROR_DELETINGFILEFOLDER', $filename), 'warning');
	            }
	        }

	        // overrides warning

	        $defaultemplate = $this->getDefaultTemplate();

	        if ($defaultemplate) {
	            $overrides_path = JPATH_ROOT.'/templates/'.$defaultemplate.'/html/';

	            if (Folder::exists($overrides_path.'mod_latestnewsenhanced')) {
	                Factory::getApplication()->enqueueMessage(Text::_('PKG_LATESTNEWSENHANCED_WARNING_OVERRIDES'), 'warning');
	            }
	        }

	        // update old instances to the new subforms

	        $db = Factory::getDBO();

	        $query = $db->getQuery(true);

	        $query->select('id');
	        $query->select('title');
	        $query->select('params');
	        $query->from('#__modules');
	        $query->where($db->quoteName('module').'='.$db->quote('mod_latestnewsenhanced'));

	        $db->setQuery($query);

	        $lne_instances = array();
	        try {
	        	$lne_instances = $db->loadObjectList();
	        } catch (ExecutionFailureException $e) {
	        	Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	        	return false;
	        }

	        foreach ($lne_instances as $lne_instance) {

	        	// get info fields and transform them into new subform

	        	$instance_params = json_decode($lne_instance->params, true);

	        	if (isset($instance_params['show_icons_1'])) {

		        	$j = 0;
		        	$j_sub = 0;

		        	$info_blocs = array();

		        	while ($j < 5) {
		        		if (isset($instance_params['info_'.($j + 1)]) && $instance_params['info_'.($j + 1)] != 'none') {
		        			$info_bloc = array();
		        			$info_bloc['show_icons'] = isset($instance_params['show_icons_'.($j + 1)]) ? $instance_params['show_icons_'.($j + 1)] : 0;
		        			$info_bloc['icon'] = '';
		        			$info_bloc['prepend'] = isset($instance_params['prepend_'.($j + 1)]) ? $instance_params['prepend_'.($j + 1)] : '';
		        			$info_bloc['extra_classes'] = isset($instance_params['extra_classes_'.($j + 1)]) ? $instance_params['extra_classes_'.($j + 1)] : '';
		        			$info_bloc['info'] = $instance_params['info_'.($j + 1)];
		        			$info_bloc['new_line'] = isset($instance_params['new_line_'.($j + 1)]) ? $instance_params['new_line_'.($j + 1)] : 0;
		        			$info_bloc['access'] =  1;

		        			$info_blocs['information_blocks'.$j_sub] = $info_bloc;
		        			$j_sub++;
		        		}
		        		$j++;
		        	}

		        	$instance_params['information_blocks'] = $info_blocs;

		        	$j = 0;
		        	while ($j < 5) {
		        		unset($instance_params['show_icons_'.($j + 1)]);
		        		unset($instance_params['prepend_'.($j + 1)]);
		        		unset($instance_params['info_'.($j + 1)]);
		        		if (isset($instance_params['extra_classes_'.($j + 1)])) {
		        			unset($instance_params['extra_classes_'.($j + 1)]);
		        		}
		        		unset($instance_params['new_line_'.($j + 1)]);
		        		$j++;
		        	}

		        	$query->clear();

		        	$query->update('#__modules');
		        	$query->set($db->quoteName('params').'='.$db->quote(json_encode($instance_params)));
		        	$query->where($db->quoteName('id').'='.$db->quote($lne_instance->id));

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
		} catch (ExecutionFailureException $e) {
			//JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			return false;
		}

		return true;
	}

}
?>