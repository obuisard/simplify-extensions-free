<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Script file for the Trombinoscope Contacts Pro Free module package
 */
class Pkg_TrombinoscopeInstallerScript extends InstallerScript
{
	/*
	 * Minimum extensions library version required
	 */
	protected $minimumLibrary = '2.3.2';

	/**
	 * Available languages
	 */
	protected $availableLanguages = array('cs-CZ', 'da-DK', 'de-DE', 'en-GB', 'es-ES', 'fa-IR', 'fi-FI', 'fr-FR', 'nl-NL', 'pt-BR', 'ru-RU', 'sl-SI', 'tr-TR');

	/**
	 * Extensions library link for download
	 */
	protected $libraryDownloadLink = 'https://simplifyyourweb.com/downloads/syw-extension-library';

	/**
	 * Link to the change logs
	 */
	protected $changelogLink = 'https://simplifyyourweb.com/documentation/trombinoscope-contacts/installation/updating-older-versions';

	/**
	 * Link to the translation page
	 */
	protected $translationLink = 'https://simplifyyourweb.com/translators';

	/**
	 * Link to the quick start page
	 */
	protected $quickstartLink = 'https://simplifyyourweb.com/documentation/trombinoscope-contacts/quickstart-guide';

	/**
	 * Extension script constructor
	 */
	public function __construct($installer)
	{
	    $this->extension = 'pkg_trombinoscope';
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
    	echo HTMLHelper::image('mod_trombinoscopecontacts/logo.png', 'Trombinoscope Contacts', null, true);
    	echo '<br /><br /><span class="badge bg-dark">' . Text::sprintf('PKG_TROMBINOSCOPE_VERSION', $this->release) . '</span>';
    	echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
    	echo '</p>';

     	// language test

     	$current_language = Factory::getLanguage()->getTag();
     	if (!in_array($current_language, $this->availableLanguages)) {
     	    Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this component.<br /><a href="' . $this->translationLink . '" target="_blank">Please consider contributing to its translation</a> and get a license upgrade for your help!', 'info');
     	}

		// move default silhouettes to /images

     	$imagefiles = array();
     	$imagefiles[] = 'no-image-available-100x120.jpg';
     	$imagefiles[] = 'no-photo-86x110.jpg';
     	$imagefiles[] = 'silhouette-100x120.jpg';
     	$imagefiles[] = 'silhouette-transparent-100x120.png';
     	//$imagefiles[] = 'silhouette-transparent-100x120.webp'; // test WebP is supported

     	$media_params = ComponentHelper::getParams('com_media');
     	$images_path = $media_params->get('image_path', 'images');

     	foreach ($imagefiles as $imagefile) {
     		$src = JPATH_ROOT.'/media/mod_trombinoscopecontacts/images/'.$imagefile;
     		$dest = JPATH_ROOT.'/'.$images_path.'/'.$imagefile;

     		if (!File::copy($src, $dest)) {
     			Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_WARNING_COULDNOTCOPYFILE', $imagefile), 'warning');
     		}
     	}

     	if ($action === 'install') {

     		// link to Quickstart

     		echo '<p><a class="btn btn-primary" href="' . $this->quickstartLink . '" target="_blank"><i class="fa fa-stopwatch"></i> ' . Text::_('PKG_TROMBINOSCOPE_BUTTON_QUICKSTART') . '</a></p>';
     	}

		if ($action === 'update') {

			// update warning

			echo '<p><a class="btn btn-primary" href="' . $this->changelogLink . '" target="_blank">' . Text::_('PKG_TROMBINOSCOPE_BUTTON_UPDATENOTES') . '</a></p>';

			// overrides warning

// 			$defaultemplate = $this->getDefaultTemplate();

// 			if ($defaultemplate) {
// 				$overrides_path = JPATH_ROOT.'/templates/'.$defaultemplate.'/html/';

// 				if (Folder::exists($overrides_path.'mod_trombinoscope')) {
// 					Factory::getApplication()->enqueueMessage(Text::_('PKG_TROMBINOSCOPE_WARNING_OVERRIDES'), 'warning');
// 				}
// 			}

			// remove old cached headers which may interfere with fixes, updates or new additions

			if (function_exists('glob')) {

				$filenames = glob(JPATH_SITE.'/media/cache/mod_trombinoscopecontacts/style_*.{css,js}', GLOB_BRACE);
				if ($filenames != false) {
					$this->deleteFiles = array_merge($this->deleteFiles, $filenames);
				}

				$filenames = glob(JPATH_SITE.'/media/cache/mod_trombinoscopecontacts/animation_*.js');
				if ($filenames != false) {
					$this->deleteFiles = array_merge($this->deleteFiles, $filenames);
				}
			}		
			
			// +++ Migration Joomla 3 to Joomla 4
			
			// the old folders have not been removed on update so safe to do it here
			if (Folder::exists(JPATH_SITE . '/modules/mod_trombinoscope/images')) {
			
    			// move user files (substitutes)
    			
    			$this->moveFile('common_user_styles.css', '/modules/mod_trombinoscope/themes', '/media/mod_trombinoscopecontacts/css', '-min');
    			$this->moveFile('substitute_styles.css', '/modules/mod_trombinoscope/themes', '/media/mod_trombinoscopecontacts/css', '-min');
    			
    			// move known additional files (themes)
    			// best to re-install (so it can be removed properly, if needed) but help the user here
    			
    			if (Folder::exists(JPATH_SITE . '/media/syw_trombinoscopecontacts/themes/canary')) {
    			    
    			    $this->copyFile('WebfontLicense.txt', '/media/syw_trombinoscopecontacts/themes/canary', '/media/mod_trombinoscopecontacts/css/fonts');
    			    $this->copyFile('VerbBlack-webfont.eot', '/media/syw_trombinoscopecontacts/themes/canary', '/media/mod_trombinoscopecontacts/css/fonts');
    			    $this->copyFile('VerbBlack-webfont.svg', '/media/syw_trombinoscopecontacts/themes/canary', '/media/mod_trombinoscopecontacts/css/fonts');
    			    $this->copyFile('VerbBlack-webfont.ttf', '/media/syw_trombinoscopecontacts/themes/canary', '/media/mod_trombinoscopecontacts/css/fonts');
    			    $this->copyFile('VerbBlack-webfont.woff', '/media/syw_trombinoscopecontacts/themes/canary', '/media/mod_trombinoscopecontacts/css/fonts');
    			    
    			    //$this->deleteFolders[] = '/media/syw_trombinoscopecontacts/themes/canary';
    			    
    			    if (Folder::exists(JPATH_SITE . '/modules/mod_trombinoscope/themes/canary')) {
    			        
    			        $this->copyFile('style.css.php', '/modules/mod_trombinoscope/themes/canary', '/media/mod_trombinoscopecontacts/styles/themes/canary');
    			        
    			        if (File::exists(JPATH_SITE . '/modules/mod_trombinoscope/themes/canary/images/canary_card_landscape.png')
    			            && $this->isFolderReady('/media/mod_trombinoscopecontacts/images/themes')) {
    			            rename(JPATH_SITE . '/modules/mod_trombinoscope/themes/canary/images/canary_card_landscape.png', JPATH_SITE . '/media/mod_trombinoscopecontacts/images/themes/canary.png');
    			        }
    			    }
    			}
    			
    			// delete old media folder
    			// DO NOT REMOVE to allow users to move their own theme files
    			
    			//$this->deleteFolders[] = '/media/syw_trombinoscopecontacts';
    			
    			// remove data from /cache if coming from Joomla 3.10
    			
    			$this->deleteFolders[] = '/cache/mod_trombinoscopecontacts';
    			
    			// delete files and folders for module
    			
    			$this->deleteFolders[] = '/modules/mod_trombinoscope/fields';
    			$this->deleteFolders[] = '/modules/mod_trombinoscope/images';
    			//$this->deleteFolders[] = '/modules/mod_trombinoscope/themes'; // could contain user made theme files or additional downloads
    			
    			$this->deleteFiles[] = '/modules/mod_trombinoscope/headerfilesmaster.php';
    			$this->deleteFiles[] = '/modules/mod_trombinoscope/helper.php';
			}
						
			// +++ End Migration
			
			// data migration for changes in the parameters
			
			$this->migrateData();

			// remove files

			$this->deleteFiles[] = '/media/mod_trombinoscopecontacts/css/common_styles-min.css';
		}

		$this->removeFiles();

		return true;
	}
	
	private function migrateData()
	{
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
	        //Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	        return false;
	    }
	    
	    foreach ($tc_instances as $tc_instance) {
	        
	        $instance_params = json_decode($tc_instance->params, true);
	        
	        $changes_made = false;
	        
	        // move card shadow to new value
	        
	        if (isset($instance_params['card_shadow'])) {
	            
	            if (is_numeric($instance_params['card_shadow']) && intval($instance_params['card_shadow']) == 0) {
	                $instance_params['card_shadow'] = 'none';	                
	                $changes_made = true;
	            } else if (is_numeric($instance_params['card_shadow']) && intval($instance_params['card_shadow']) == 1) {
	                $instance_params['card_shadow'] = 'sm';	                
	                $changes_made = true;
	            }
	        }
	        
	        if ($changes_made) {
	            
	            $query->clear();
	            
	            $query->update('#__modules');
	            $query->set($db->quoteName('params').'='.$db->quote(json_encode($instance_params)));
	            $query->where($db->quoteName('id').'='.$db->quote($tc_instance->id));
	            
	            $db->setQuery($query);
	            
	            try {
	                $db->execute();
	            } catch (ExecutionFailureException $e) {
	                //Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	                return false;
	            }
	        }
	    }
	    
	    return true;
	}
	
	private function isFolderReady($extra_path)
	{
	    $path = JPATH_SITE;
	    $folders = explode('/', trim($extra_path, '/'));
	    
	    foreach ($folders as $folder) {
	        $path .= '/' . $folder;
	        if (!Folder::exists($path)) {
	            if (Folder::create($path)) {
	            } else {
	                return false;
	            }
	        }
	    }
	    
	    return true;
	}
	
	private function moveFile($file, $source, $destination, $minified_version = '')
	{
	    if (File::exists(JPATH_SITE . $source . '/' . $file)) {
	        if (!$this->isFolderReady($destination) || !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
	            Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_ERROR_CANNOTMOVEFILE', $file), 'warning');
	        }
	    }
	    
	    if ($minified_version) {
    	    $file_name = File::stripExt($file);
    	    $file_extension = File::getExt($file);
    	    $file = $file_name . $minified_version . '.' . $file_extension;
    	    
    	    if (File::exists(JPATH_SITE . $source . '/' . $file)) {
    	        if (!$this->isFolderReady($destination) || !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
    	            Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_ERROR_CANNOTMOVEFILE', $file), 'warning');
    	        }
    	    }
	    }
	}
	
	private function copyFile($file, $source, $destination)
	{
	    if (File::exists(JPATH_SITE . $source . '/' . $file)) {
	        if (!$this->isFolderReady($destination) || !File::copy(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
	            Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_TROMBINOSCOPE_WARNING_COULDNOTCOPYFILE', $file), 'warning');
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
	        Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	        return false;
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