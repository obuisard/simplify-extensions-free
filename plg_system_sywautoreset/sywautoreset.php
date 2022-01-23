<?php

/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Plugin that resets the cached images, if any, for an item, before it is saved
 * Works for: LNE, LNEP, TC, TCP, WL, WLP
 *
 */
class plgSystemSYWAutoReset extends CMSPlugin
{
    protected $app;
    
    protected $autoloadLanguage = true;
    
    protected $contexts = array('com_content.article', 'com_content.form', 'com_k2.item', 'com_contact.contact', 'com_trombinoscopeextended.usercontact', 'com_weblinks.weblink', 'com_weblinklogospro.weblink');

    protected $filter_names = array('blur', 'duotone', 'edgedetect', 'emboss', 'grayscale', 'negate', 'pixelate', 'sepia', 'sharpen', 'sketch');
        
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        
        if (!$this->app) {
            $this->app = Factory::getApplication();
        }
    }
    
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
                } catch (RuntimeException $e) {
                    return true;
                }

                $categories_array = $this->params->get('contact_cat', array('none'));

                $array_of_category_values = array_count_values($categories_array);
                if (isset($array_of_category_values['none']) && $array_of_category_values['none'] > 0) { // 'none' was selected
                    return true;
                } else if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
                    // continue
                } else {
                    $get_sub_categories = $this->params->get('contact_subcat', 'no');
                    if ($get_sub_categories != 'no') {

                        $levels = $this->params->get('contact_levelsubcat', 1);

                        $categories_object = Categories::getInstance('Contact');

                        foreach ($categories_array as $category) {
                            $category_object = $categories_object->get($category);
                            if (isset($category_object) && $category_object->hasChildren()) {

                                $sub_categories_array = $category_object->getChildren(true);

                                foreach ($sub_categories_array as $subcategory_object) {
                                    if ($get_sub_categories == 'all' || ($subcategory_object->level - $category_object->level) <= $levels) {
                                        $categories_array[] = $subcategory_object->id;
                                    }
                                }
                            }
                        }

                        //$categories_array = array_unique($categories_array); // not useful
                    }

                    $include = $this->params->get('contact_cat_inex', 1);
                    if ((!in_array($catid, $categories_array) && $include) || (in_array($catid, $categories_array) && !$include)) {
                        return true;
                    }
                }

                $paths = $this->getPaths('com_contact.contact');

                $this->loadLanguage();

                $filenames_to_delete = array();

                foreach ($paths as $path) {
                    $filenames = Folder::files(JPATH_ROOT.$path, '.png|.jpg|.jpeg|.gif|.webp|.avif', false, true); // tests if the folder exists but returns warning
                    if ($filenames != false) {
                        $filenames_to_delete = array_merge($filenames_to_delete, $filenames);
                    }
                }

                if (empty($filenames_to_delete)) {
                    return true;
                }

                // try to find the specific item in the list of files

                $filenames_for_item = array();

                foreach ($filenames_to_delete as $filename) {

                    $stripped_filename = strrchr($filename, '_'); // look for the last chunk after _ in the file name with result: _id.jpg or _id@2x.jpg

                    $chunks = explode('.', $stripped_filename);
                    if ((string)$contact_id === ltrim($chunks[0], '_')) {
                        $filenames_for_item[] = $filename;
                        $filenames_for_item[] = str_replace(".", "@2x.", $filename); // add the possible @2x file
                    }
                }

                if (empty($filenames_for_item)) {
                    return true;
                }

                $filenames_to_delete = $filenames_for_item;

                $some_files_deleted = $this->deleteFiles($filenames_to_delete);

                if ($some_files_deleted && ($this->params->get('verbose', 0) == 1 || ($this->app->isClient('site') && $this->params->get('verbose', 0) == 3))) {
                    Factory::getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SYWAUTORESET_INFO_IMAGECACHECLEARED'), 'message');
                }
            }
        }

        return true;
    }

    public function onContentBeforeSave($context, $item, $isNew)
    {    	
    	if ($isNew) {
    		return true;
    	}
    	
    	if (!in_array($context, $this->contexts)) {
    	    return true;
        }

        // go through if article is in any of the categories selected

        if ($context == 'com_content.article' || $context == 'com_content.form') {

            $categories_array = $this->params->get('article_cat', array('none'));

            $array_of_category_values = array_count_values($categories_array);
            if (isset($array_of_category_values['none']) && $array_of_category_values['none'] > 0) { // 'none' was selected
                return true;
            } else if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
                // continue
            } else {
                $get_sub_categories = $this->params->get('article_subcat', 'no');
                if ($get_sub_categories != 'no') {

                    $levels = $this->params->get('article_levelsubcat', 1);

                    $categories_object = Categories::getInstance('Content');

                    foreach ($categories_array as $category) {
                        $category_object = $categories_object->get($category);
                        if (isset($category_object) && $category_object->hasChildren()) {

                            $sub_categories_array = $category_object->getChildren(true);

                            foreach ($sub_categories_array as $subcategory_object) {
                                if ($get_sub_categories == 'all' || ($subcategory_object->level - $category_object->level) <= $levels) {
                                    $categories_array[] = $subcategory_object->id;
                                }
                            }
                        }
                    }

                    //$categories_array = array_unique($categories_array); // not useful
                }

                $include = $this->params->get('article_cat_inex', 1);
                if ((!in_array($item->catid, $categories_array) && $include) || (in_array($item->catid, $categories_array) && !$include)) {
                    return true;
                }
            }
        } else if ($context == 'com_k2.item') {

            $categories_array = $this->params->get('k2_cat', array('none'));

            $array_of_category_values = array_count_values($categories_array);
            if (isset($array_of_category_values['none']) && $array_of_category_values['none'] > 0) { // 'none' was selected
                return true;
            } else if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
                // continue
            } else {

                $get_sub_categories = $this->params->get('k2_subcat', 'no');
                if ($get_sub_categories != 'no') {

                    require_once (JPATH_SITE.'/components/com_k2/models/itemlist.php');
                    $itemListModel = K2Model::getInstance('Itemlist', 'K2Model');

                    $sub_categories_array = array();
                    if ($get_sub_categories == 'all') {
                        $sub_categories_array = $itemListModel->getCategoryTree($categories_array);
                    } else {
                        foreach ($categories_array as $category) {
                            $sub_categories_rows = $itemListModel->getCategoryFirstChildren($category);
                            foreach ($sub_categories_rows as $sub_categories_row) {
                                $sub_categories_array[] = $sub_categories_row->id;
                            }
                        }
                    }
                    foreach ($sub_categories_array as $subcategory) {
                        $categories_array[] = $subcategory;
                    }

                    //$categories_array = array_unique($categories_array); // not useful
                }

                $include = $this->params->get('k2_cat_inex', 1);
                if ((!in_array($item->catid, $categories_array) && $include) || (in_array($item->catid, $categories_array) && !$include)) {
                    return true;
                }
            }
        } else if ($context == 'com_contact.contact' || $context == 'com_trombinoscopeextended.usercontact') {

            $categories_array = $this->params->get('contact_cat', array('none'));

            $array_of_category_values = array_count_values($categories_array);
            if (isset($array_of_category_values['none']) && $array_of_category_values['none'] > 0) { // 'none' was selected
                return true;
            } else if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
                // continue
            } else {
                $get_sub_categories = $this->params->get('contact_subcat', 'no');
                if ($get_sub_categories != 'no') {

                    $levels = $this->params->get('contact_levelsubcat', 1);

                    $categories_object = Categories::getInstance('Contact');

                    foreach ($categories_array as $category) {
                        $category_object = $categories_object->get($category);
                        if (isset($category_object) && $category_object->hasChildren()) {

                            $sub_categories_array = $category_object->getChildren(true);

                            foreach ($sub_categories_array as $subcategory_object) {
                                if ($get_sub_categories == 'all' || ($subcategory_object->level - $category_object->level) <= $levels) {
                                    $categories_array[] = $subcategory_object->id;
                                }
                            }
                        }
                    }

                    //$categories_array = array_unique($categories_array); // not useful
                }

                $include = $this->params->get('contact_cat_inex', 1);
                if ((!in_array($item->catid, $categories_array) && $include) || (in_array($item->catid, $categories_array) && !$include)) {
                    return true;
                }
            }
        } else if ($context == 'com_weblinks.weblink' || $context == 'com_weblinklogospro.weblink') {

            $categories_array = $this->params->get('weblink_cat', array('none'));

            $array_of_category_values = array_count_values($categories_array);
            if (isset($array_of_category_values['none']) && $array_of_category_values['none'] > 0) { // 'none' was selected
                return true;
            } else if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
                // continue
            } else {
                $get_sub_categories = $this->params->get('weblink_subcat', 'no');
                if ($get_sub_categories != 'no') {

                    $levels = $this->params->get('weblink_levelsubcat', 1);

                    $categories_object = Categories::getInstance('Weblinks');

                    foreach ($categories_array as $category) {
                        $category_object = $categories_object->get($category);
                        if (isset($category_object) && $category_object->hasChildren()) {

                            $sub_categories_array = $category_object->getChildren(true);

                            foreach ($sub_categories_array as $subcategory_object) {
                                if ($get_sub_categories == 'all' || ($subcategory_object->level - $category_object->level) <= $levels) {
                                    $categories_array[] = $subcategory_object->id;
                                }
                            }
                        }
                    }

                    //$categories_array = array_unique($categories_array); // not useful
                }

                $include = $this->params->get('weblink_cat_inex', 1);
                if ((!in_array($item->catid, $categories_array) && $include) || (in_array($item->catid, $categories_array) && !$include)) {
                    return true;
                }
            }
        } else {
            return true;
        }

        $paths = $this->getPaths($context);

        $this->loadLanguage();

        $filenames_to_delete = array();

        foreach ($paths as $path) {
            $filenames = Folder::files(JPATH_ROOT.$path, '.png|.jpg|.jpeg|.gif|.webp|.avif', false, true); // tests if the folder exists but returns warning
            if ($filenames != false) {
                $filenames_to_delete = array_merge($filenames_to_delete, $filenames);
            }
        }

        if (empty($filenames_to_delete)) {
            return true;
        }

        // try to find the specific item in the list of files

        $filenames_for_item = array();

        foreach ($filenames_to_delete as $filename) {

            if ($context == 'com_weblinks.weblink' || $context == 'com_weblinklogospro.weblink') {
                $stripped_filename = $filename;
                foreach ($this->filter_names as $filter_name) { // there are 1 or 2 filters in the file name AFTER the weblink id
                    $stripped_filename = str_replace('_'.$filter_name, '', $stripped_filename);
                }
                
                $stripped_filename = str_replace('_hover', '', $stripped_filename);
                
                $stripped_filename = strrchr($stripped_filename, '_'); // look for the last chunk after _ in the file name with result: _id.jpg or _id@2x.jpg
            } else {
                $stripped_filename = strrchr($filename, '_'); // look for the last chunk after _ in the file name with result: _id.jpg or _id@2x.jpg
            }

            $chunks = explode('.', $stripped_filename);
            if ((string)$item->id === ltrim($chunks[0], '_')) {
                $filenames_for_item[] = $filename;
                $filenames_for_item[] = str_replace(".", "@2x.", $filename); // add the possible @2x file
            }
        }

        if (empty($filenames_for_item)) {
            return true;
        }

        $filenames_to_delete = $filenames_for_item;

        $some_files_deleted = $this->deleteFiles($filenames_to_delete);
        
        if ($some_files_deleted && ($this->params->get('verbose', 0) == 1 || ($this->app->isClient('administrator') && $this->params->get('verbose', 0) == 2) || ($this->app->isClient('site') && $this->params->get('verbose', 0) == 3))) {
            Factory::getApplication()->enqueueMessage(Text::_('PLG_SYSTEM_SYWAUTORESET_INFO_IMAGECACHECLEARED'), 'message');
        }

        return true;
    }
    
    protected function deleteFiles($filenames_to_delete) 
    {
        $some_files_deleted = false;
        
        foreach ($filenames_to_delete as $filename) {
            if (File::exists($filename)) {
                if (File::delete($filename)) {
                    $some_files_deleted = true; // deleted the file
                    if ($this->params->get('verbose', 0) == 1 || ($this->app->isClient('administrator') && $this->params->get('verbose', 0) == 2) || ($this->app->isClient('site') && $this->params->get('verbose', 0) == 3)) {
                        Factory::getApplication()->enqueueMessage(Text::sprintf('PLG_SYSTEM_SYWAUTORESET_INFO_FILEDELETED', str_replace('\\', '/', str_replace(JPATH_ROOT, '', $filename))), 'message');
                    }
                } else {
                    if ($this->params->get('verbose', 0) == 1 || ($this->app->isClient('administrator') && $this->params->get('verbose', 0) == 2) || ($this->app->isClient('site') && $this->params->get('verbose', 0) == 3)) {
                        Factory::getApplication()->enqueueMessage(Text::sprintf('PLG_SYSTEM_SYWAUTORESET_ERROR_DELETINGFILE', str_replace('\\', '/', str_replace(JPATH_ROOT, '', $filename))), 'warning');
                    }
                }
            }
        }
        
        return $some_files_deleted;
    }

    protected function getPaths($context)
    {
        $paths = array();

        if ($context == 'com_contact.contact' || $context == 'com_trombinoscopeextended.usercontact') {

            if (Folder::exists(JPATH_ROOT.'/media/cache/com_trombinoscopecontactspro')) {
                $paths[] = '/media/cache/com_trombinoscopecontactspro';
            }

            if (Folder::exists(JPATH_ROOT.'/media/cache/mod_trombinoscopecontacts')) {
                $paths[] = '/media/cache/mod_trombinoscopecontacts';
            }

            if (Folder::exists(JPATH_ROOT.'/images/thumbnails/tc')) {
                $paths[] = '/images/thumbnails/tc';
            }

            if (Folder::exists(JPATH_ROOT.'/images/thumbnails/tcp')) {
                $paths[] = '/images/thumbnails/tcp';
            }

        } else if ($context == 'com_weblinks.weblink' || $context == 'com_weblinklogospro.weblink') {

            if (Folder::exists(JPATH_ROOT.'/media/cache/com_weblinklogospro')) {
                $paths[] = '/media/cache/com_weblinklogospro';
            }

            if (Folder::exists(JPATH_ROOT.'/media/cache/mod_weblinklogos')) {
                $paths[] = '/media/cache/mod_weblinklogos';
            }

            if (Folder::exists(JPATH_ROOT.'/images/thumbnails/wl')) {
                $paths[] = '/images/thumbnails/wl';
            }

            if (Folder::exists(JPATH_ROOT.'/images/thumbnails/wlp')) {
                $paths[] = '/images/thumbnails/wlp';
            }

        } else {

            if (Folder::exists(JPATH_ROOT.'/media/cache/com_latestnewsenhancedpro')) {
                $paths[] = '/media/cache/com_latestnewsenhancedpro';
            }

            if (Folder::exists(JPATH_ROOT.'/media/cache/mod_latestnewsenhancedpro')) {
                $paths[] = '/media/cache/mod_latestnewsenhancedpro';
            }

            if (Folder::exists(JPATH_ROOT.'/media/cache/mod_latestnewsenhanced')) {
                $paths[] = '/media/cache/mod_latestnewsenhanced';
            }

            if (Folder::exists(JPATH_ROOT.'/images/thumbnails/lne')) {
                $paths[] = '/images/thumbnails/lne';
            }

            if (Folder::exists(JPATH_ROOT.'/images/thumbnails/lnep')) {
                $paths[] = '/images/thumbnails/lnep';
            }

//             if (Folder::exists(JPATH_ROOT.'/cache/mod_trulyresponsiveslidespro')) {
//             	$paths[] = '/cache/mod_trulyresponsiveslidespro';
//             }

//             if (Folder::exists(JPATH_ROOT.'/cache/mod_trulyresponsiveslides')) {
//             	$paths[] = '/cache/mod_trulyresponsiveslides';
//             }

//             if (Folder::exists(JPATH_ROOT.'/images/thumbnails/trs')) {
//             	$paths[] = '/images/thumbnails/trs';
//             }
        }

        return $paths;
    }
}
?>