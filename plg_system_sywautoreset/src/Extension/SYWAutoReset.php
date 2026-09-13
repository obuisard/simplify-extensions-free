<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\SYWAutoReset\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
//use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;
use SYW\Library\Version as SYWVersion;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin that resets the cached images, if any, for an item, before it is saved
 * Works for: LNE, LNEP, TC, TCP, WL, WLP
 *
 */
final class SYWAutoReset extends CMSPlugin //implements SubscriberInterface
{
    /**
     * Application object.
     * Needed for compatibility with Joomla 4 < 4.2
     * Ultimately, we should use $this->getApplication() in Joomla 6
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     */
    protected $app;
    
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     */
    protected $autoloadLanguage = true;

    /**
     * The supported form contexts
     *
     * @var    array
     */
    protected $supportedContext = [
        'com_content.article',
        'com_content.form',
        'com_contact.contact',
        'com_trombinoscopeextended.usercontact',
        'com_weblinks.weblink',
        'com_weblinklogospro.weblink',
    ];

    protected $filter_names = ['blur', 'duotone', 'edgedetect', 'emboss', 'grayscale', 'negate', 'pixelate', 'sepia', 'sharpen', 'sketch'];

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $subject  The object to observe
     * @param   array                $config   An optional associative array of configuration settings
     */
    public function __construct($subject, array $config = [])
    {
        parent::__construct($subject, $config);
        
        if (!$this->app) {
            $this->app = Factory::getApplication();
        }
    }
    
    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return  [
            'onContentBeforeSave' => 'onContentBeforeSave',
            'onUserAfterSave'     => 'onUserAfterSave',
        ];
    }

    /**
     * 
     * @return boolean
     */
    public function onUserAfterSave($data, $isNew, $result, $error)
    {        
    	if ($isNew) {
    		return true;
    	}

    	if (!$this->app->isClient('site')) {
            return true;
        }

        if (PluginHelper::isEnabled('user', 'editcontactinprofile')) {

            $userId	= ArrayHelper::getValue($data, 'id', 0, 'int');

            if ($userId && $result) {

                $db = Factory::getDbo();

                $query = $db->getQuery(true);

                $query->select($db->quoteName(array('id', 'catid')));
                $query->from($db->quoteName('#__contact_details'));
                $query->where($db->quoteName('user_id').' = '.(int) $userId);

                $db->setQuery($query);

                try {
                    $contact_data = $db->loadObjectList();

                    $contact_id = $contact_data[0]->id;
                    $catid = $contact_data[0]->catid;
                } catch (\RuntimeException $e) {
                    return true;
                }
                
                $result = $this->isInCategoryList($catid, $this->params->get('contact_cat', ['none']), $this->params->get('contact_subcat', 'no'), $this->params->get('contact_levelsubcat', 1), $this->params->get('contact_cat_inex', 1), 'Contact');
                if (!$result) {
                    return true;
                }

                $this->loadLanguage();
                
                $paths = $this->getPaths('com_contact.contact');                

                $possible_filenames_to_delete = $this->getFilenamesForPaths($paths);

                if (empty($possible_filenames_to_delete)) {
                    return true;
                }

                // try to find the specific item in the list of files
                $filenames_for_item = $this->getFilenamesForItem($contact_id, $possible_filenames_to_delete, 'com_contact.contact');

                if (empty($filenames_for_item)) {
                    return true;
                }

                $some_files_deleted = $this->deleteFiles($filenames_for_item);

                if ($some_files_deleted && $this->showVerbose()) {
                    Factory::getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SYWAUTORESET_INFO_IMAGECACHECLEARED'), 'message');
                }
            }
        }

        return true;
    }

    /**
     * 
     * @return boolean
     */
    public function onContentBeforeSave($context, $item, $isNew)
    {
    	if ($isNew) {
    		return true;
    	}
    	
    	if (!$this->app->isClient('site') && !$this->app->isClient('administrator')) {
    	    return true;
    	}

    	if (!in_array($context, $this->supportedContext)) {
    	    return true;
        }

        // go through if article is in any of the categories selected
        
        switch ($context) 
		{
		    case 'com_content.article':
		    case 'com_content.form':
		        
		        $result = $this->isInCategoryList($item->catid, $this->params->get('article_cat', ['none']), $this->params->get('article_subcat', 'no'), $this->params->get('article_levelsubcat', 1), $this->params->get('article_cat_inex', 1), 'Content');
		        if (!$result) {
		            return true;
		        }
		        break;
		        
		    case 'com_contact.contact':
		    case 'com_trombinoscopeextended.usercontact':
		        
		        $result = $this->isInCategoryList($item->catid, $this->params->get('contact_cat', ['none']), $this->params->get('contact_subcat', 'no'), $this->params->get('contact_levelsubcat', 1), $this->params->get('contact_cat_inex', 1), 'Contact');
		        if (!$result) {
		            return true;
		        }
		        break;
		        
		    case 'com_weblinks.weblink':
		    case 'com_weblinklogospro.weblink':
		        
		        $result = $this->isInCategoryList($item->catid, $this->params->get('weblink_cat', ['none']), $this->params->get('weblink_subcat', 'no'), $this->params->get('weblink_levelsubcat', 1), $this->params->get('weblink_cat_inex', 1), 'Weblinks');
		        if (!$result) {
		            return true;
		        }		       
		        break;
		        
		    default:
		        return true;
        }
        
        $this->loadLanguage();

        $paths = $this->getPaths($context);
        
        $possible_filenames_to_delete = $this->getFilenamesForPaths($paths);

        if (empty($possible_filenames_to_delete)) {
            return true;
        }

        // try to find the specific item in the list of files
        $filenames_for_item = $this->getFilenamesForItem($item->id, $possible_filenames_to_delete, $context);

        if (empty($filenames_for_item)) {
            return true;
        }

        $some_files_deleted = $this->deleteFiles($filenames_for_item);

        if ($some_files_deleted && $this->showVerbose()) {
            Factory::getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SYWAUTORESET_INFO_IMAGECACHECLEARED'), 'message');
        }

        return true;
    }
    
    /**
     * Checks if a category for an item is in the list of the selected categories of the plugin
     * 
     * @param integer       $category_id
     * @param string|array  $categories_array
     * @param string        $sub_categories
     * @param number        $levels
     * @param boolean       $include
     * @param string        $context
     * 
     * @return boolean
     */
    private function isInCategoryList($category_id, $categories_array, $sub_categories = 'no', $levels = 1, $include = true, $context = 'Content')
    {
        if (!is_array($categories_array)) { // before the plugin is saved, the value is the string 'none'
            $categories_array = explode(' ', $categories_array);
        }
        
        $array_of_category_values = array_count_values($categories_array);
        if (isset($array_of_category_values['none']) && $array_of_category_values['none'] > 0) { // 'none' was selected
            return false;
        } else if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
            return true;
        } else {
            if ($sub_categories != 'no') {
                $categories_object = Categories::getInstance($context);
                foreach ($categories_array as $category) {
                    $category_object = $categories_object->get($category);
                    if (isset($category_object) && $category_object->hasChildren()) {
                        $sub_categories_array = $category_object->getChildren(true);
                        foreach ($sub_categories_array as $subcategory_object) {
                            if ($sub_categories == 'all' || ($subcategory_object->level - $category_object->level) <= $levels) {
                                $categories_array[] = $subcategory_object->id;
                            }
                        }
                    }
                }
            }
            
            if ((!in_array($category_id, $categories_array) && !$include) || (in_array($category_id, $categories_array) && $include)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get all image filenames from an array of paths
     * 
     * @param string[] $paths
     * 
     * @return string[]
     */
    private function getFilenamesForPaths($paths)
    {
        $filenames_to_delete = [];
        
        foreach ($paths as $path) {
            $filenames = Folder::files(JPATH_ROOT . $path, '.png|.jpg|.jpeg|.gif|.webp|.avif', false, true); // tests if the folder exists but returns warning
            if ($filenames != false) {
                $filenames_to_delete = array_merge($filenames_to_delete, $filenames);
            }
        }
        
        return $filenames_to_delete;
    }
    
    /*
        thumb_[module_id]_default.ext
        thumb_[module_id]_global.ext
        thumb_[module_id]_[item_id].ext
        thumb_[module_id]_[item_id]@2x.ext
        thumb_[module_id]_[item_id]_hover.ext
        thumb_[module_id]_[item_id]_hover@2x.ext
        thumb_[module_id]_[item_id]_[filter].ext
        thumb_[module_id]_[item_id]_[filter]@2x.ext
        
        lnep
        thumb_articles_blog_[view_id]_[item_id].ext
        thumb_articles_blog_[view_id]_[item_id]@2x.ext
        thumb_articles_blog_[view_id]_lead_[item_id]@2x.ext
        
        tcp
        thumb_contact_default.ext -> no associated view
        thumb_[view_name]_[view_id]_default.ext
        thumb_[view_name]_[view_id]_global.ext
        thumb_[view_name]_[view_id]_[item_id].ext
        thumb_[view_name]_[view_id]_[item_id]@2x.ext
        
        wlp
        thumb_[view_name]_[view_id]_[item_id].ext
        thumb_[view_name]_[view_id]_[item_id]@2x.ext
        thumb_[view_name]_[view_id]_[item_id]_hover.ext
        thumb_[view_name]_[view_id]_[item_id]_hover@2x.ext
        thumb_[view_name]_[view_id]_[item_id]_[filter].ext
        thumb_[view_name]_[view_id]_[item_id]_[filter]@2x.ext
     */
    /**
     * Get the filenames for an item from a pool of filenames
     * During that process, it invalidates the media versions for the extensions that generated the images
     * 
     * @param integer   $id
     * @param string[]  $filenames
     * @param string    $context
     * 
     * @return string[]
     */
    private function getFilenamesForItem($id, $filenames, $context)
    {
        $filenames_for_item = [];
        $mediaversion_paths = [];
        
        foreach ($filenames as $filename) {
            
            if ($context == 'com_weblinks.weblink' || $context == 'com_weblinklogospro.weblink') {
                
                $stripped_filename = $filename;
                foreach ($this->filter_names as $filter_name) { // there are 1 or 2 filters in the file name AFTER the weblink id
                    $stripped_filename = str_replace('_' . $filter_name, '', $stripped_filename);
                }
                
                $stripped_filename = str_replace('_hover', '', $stripped_filename);
                $filename_chunks = explode('_', $stripped_filename);
            } else {              
                $filename_chunks = explode('_', $filename);
            }
            
            $lastElement = end($filename_chunks); // id.jpg or id@2x.jpg
            $secondLastElement = prev($filename_chunks); // the module or view id
            
            $lastElementChunks = explode('.', $lastElement);
            if ((string)$id === $lastElementChunks[0]) {
                $filenames_for_item[] = $filename;
                $filenames_for_item[] = str_replace(".", "@2x.", $filename); // add the possible @2x file
                
                // found file(s), refresh the media versions for the module or view instances                
                switch ($context)
                {
                    case 'com_content.article':
                    case 'com_content.form':
                        
                        if ($secondLastElement == 'lead') { // specific to LNEP views
                            $secondLastElement = prev($filename_chunks); // the view id
                        }
                        
                        $thirdLastElement = prev($filename_chunks);
                        
                        if (preg_match("/\/thumb$/", $thirdLastElement)) { // it's a module (thirdLastElement is 'latestnewsenhanced/thumb')
                            $mediaversion_paths[] = 'mod_latestnewsenhanced_' . $secondLastElement;
                        } else { // it's a view
                            $mediaversion_paths[] = 'com_latestnewsenhancedpro_articles_' . $thirdLastElement . '_' . $secondLastElement;
                        }
                        break;
                        
                    case 'com_contact.contact':
                    case 'com_trombinoscopeextended.usercontact':
                        
                        $thirdLastElement = prev($filename_chunks);
                        
                        if (preg_match("/\/thumb$/", $thirdLastElement)) { // it's a module
                            $mediaversion_paths[] = 'mod_trombinoscope_' . $secondLastElement;
                        } else { // it's a view
                            $mediaversion_paths[] = 'com_trombinoscopecontactspro_' . $thirdLastElement . '_' . $secondLastElement;
                        }

                        break;
                        
                    case 'com_weblinks.weblink':
                    case 'com_weblinklogospro.weblink':
                        
                        $thirdLastElement = prev($filename_chunks);
                        
                        if (preg_match("/\/thumb$/", $thirdLastElement)) { // it's a module
                            $mediaversion_paths[] = 'mod_weblinklogos_' . $secondLastElement;
                        } else { // it's a view
                            $mediaversion_paths[] = 'com_weblinklogospro_' . $thirdLastElement . '_' . $secondLastElement;
                        }

                        break;
                        
                    default:
                }
            }
        }
        
        $mediaversion_paths = array_unique($mediaversion_paths);
		
		foreach ($mediaversion_paths as $mediaversion_path) {
		    SYWVersion::refreshMediaVersion($mediaversion_path);
            if ($this->showVerbose() && $this->isSuperUser($this->app->getIdentity()->id)) {
                Factory::getApplication()->enqueueMessage(Text::sprintf('PLG_SYSTEM_SYWAUTORESET_INFO_MEDIAVERSIONREFRESHED', $mediaversion_path));
            }
		}
        
        return $filenames_for_item;
    }
    
    /* Example from the syw library
     {"mediaversions":"{
     \"mod_latestnewsenhanced_121\":\"0a26a889f9e03f6044e899c34aa5548c\",
     \"com_latestnewsenhancedpro_articles_list_528\":\"696f51a949b77fb99b6e8c626330e69c\",
     \"com_latestnewsenhancedpro_articles_blog_274\":\"b3121a5a2350ee32b7356de45610dfd3\",
     \"mod_weblinklogos_123\":\"b744d8d6abff04669c2b2c1ad49c0a89\",
     \"com_weblinklogospro_directory_386\":\"f471b687a1d8cbdd058e236c62881bb1\",
     \"mod_trombinoscope_114\":\"1138a9e07532a72898ae5ebe28504a33\",
     \"com_trombinoscopecontactspro_trombinoscope_355\":\"e27fc2134537c05ec5708c8737ba2b57\",
     \"com_trombinoscopecontactspro_contact_158\":\"e34e3d23ec802d7a33cf4757c5d96f48\",
     \"com_articledetailsprofiles\":\"0aed9525d9a8011afec0cfbf74b83cde\",
     \"mod_trulyresponsiveslides_119\":\"9f1df4808d0af400bdd96147808c92f8\"}"}
     */

    /**
     * Delete the files selected for deletion
     * 
     * @param   string[]  $filenames_to_delete  The file names to delete
     * @return  boolean   True if at least one file was deleted
     */
    private function deleteFiles($filenames_to_delete)
    {
        $some_files_deleted = false;

        foreach ($filenames_to_delete as $filename) {
            if (is_file($filename)) {
                if (File::delete($filename)) {
                    $some_files_deleted = true; // deleted the file
                    if ($this->showVerbose()) {
                        Factory::getApplication()->enqueueMessage(Text::sprintf('PLG_SYSTEM_SYWAUTORESET_INFO_FILEDELETED', str_replace('\\', '/', str_replace(JPATH_ROOT, '', $filename))), 'message');
                    }
                } else {
                    if ($this->showVerbose()) {
                        Factory::getApplication()->enqueueMessage(Text::sprintf('PLG_SYSTEM_SYWAUTORESET_ERROR_DELETINGFILE', str_replace('\\', '/', str_replace(JPATH_ROOT, '', $filename))), 'warning');
                    }
                }
            }
        }

        return $some_files_deleted;
    }
    
    /**
     * Checks if verbose information should be shown
     * 
     * @return boolean
     */
    private function showVerbose()
    {
        $show = false;
        
        if ($this->params->get('verbose', 0) == 1 || ($this->app->isClient('administrator') && $this->params->get('verbose', 0) == 2) || ($this->app->isClient('site') && $this->params->get('verbose', 0) == 3)) {
            return true;
        }
        
        return $show;
    }
    
    /**
     * Checks that the user is a Super User
     *
     * @param integer  $userId
     *
     * @return bool
     */
    private function isSuperUser($userId)
    {        
        // Hard coded group due to security reasons - Joomla! default: 8 = Super Users
        $filterGroups = array(8);
        $userGroups = Access::getGroupsByUser($userId);
        
        foreach ($userGroups as $userGroup) {
            if (in_array($userGroup, $filterGroups)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get all the paths where thumbnails can be located for the given context
     * 
     * @param string  $context
     * 
     * @return string[]
     */
    private function getPaths($context)
    {
        $paths = [];
        
        switch ($context)
        {
            case 'com_content.article':
            case 'com_content.form':
                
                if (is_dir(JPATH_ROOT.'/media/cache/com_latestnewsenhancedpro')) {
                    $paths[] = '/media/cache/com_latestnewsenhancedpro';
                }
                
                if (is_dir(JPATH_ROOT.'/media/cache/mod_latestnewsenhancedpro')) {
                    $paths[] = '/media/cache/mod_latestnewsenhancedpro';
                }
                
                if (is_dir(JPATH_ROOT.'/media/cache/mod_latestnewsenhanced')) {
                    $paths[] = '/media/cache/mod_latestnewsenhanced';
                }
                
                if (is_dir(JPATH_ROOT.'/images/thumbnails/lne')) {
                    $paths[] = '/images/thumbnails/lne';
                }
                
                if (is_dir(JPATH_ROOT.'/images/thumbnails/lnep')) {
                    $paths[] = '/images/thumbnails/lnep';
                }
                
//             if (is_dir(JPATH_ROOT.'/cache/mod_trulyresponsiveslidespro')) {
//             	$paths[] = '/cache/mod_trulyresponsiveslidespro';
//             }

//             if (is_dir(JPATH_ROOT.'/cache/mod_trulyresponsiveslides')) {
//             	$paths[] = '/cache/mod_trulyresponsiveslides';
//             }

//             if (is_dir(JPATH_ROOT.'/images/thumbnails/trs')) {
//             	$paths[] = '/images/thumbnails/trs';
//             }
                
                break;
                
            case 'com_contact.contact':
            case 'com_trombinoscopeextended.usercontact':
                
                if (is_dir(JPATH_ROOT.'/media/cache/com_trombinoscopecontactspro')) {
                    $paths[] = '/media/cache/com_trombinoscopecontactspro';
                }
                
                if (is_dir(JPATH_ROOT.'/media/cache/mod_trombinoscopecontacts')) {
                    $paths[] = '/media/cache/mod_trombinoscopecontacts';
                }
                
                if (is_dir(JPATH_ROOT.'/images/thumbnails/tc')) {
                    $paths[] = '/images/thumbnails/tc';
                }
                
                if (is_dir(JPATH_ROOT.'/images/thumbnails/tcp')) {
                    $paths[] = '/images/thumbnails/tcp';
                }
                
                break;
                
            case 'com_weblinks.weblink':
            case 'com_weblinklogospro.weblink':
                
                if (is_dir(JPATH_ROOT.'/media/cache/com_weblinklogospro')) {
                    $paths[] = '/media/cache/com_weblinklogospro';
                }
                
                if (is_dir(JPATH_ROOT.'/media/cache/mod_weblinklogos')) {
                    $paths[] = '/media/cache/mod_weblinklogos';
                }
                
                if (is_dir(JPATH_ROOT.'/images/thumbnails/wl')) {
                    $paths[] = '/images/thumbnails/wl';
                }
                
                if (is_dir(JPATH_ROOT.'/images/thumbnails/wlp')) {
                    $paths[] = '/images/thumbnails/wlp';
                }
                
                break;
        }

        return $paths;
    }
}
