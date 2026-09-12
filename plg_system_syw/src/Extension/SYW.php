<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\SYW\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class SYW extends CMSPlugin implements SubscriberInterface
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
        'com_plugins.plugin',
        'com_modules.module',
        'com_advancedmodules.module',
    ];

    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return  [
            'onAfterInitialise'    => 'onAfterInitialise',
            'onBeforeCompileHead'  => 'onBeforeCompileHead',
            'onContentAfterSave'   => 'onContentAfterSave',
            'onExtensionAfterSave' => 'onExtensionAfterSave',
        ];
    }
    
    /**
     * Constructor.
     *
     * @param   object  &$subject  The object to observe.
     * @param   array   $config    An optional associative array of configuration settings.
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        
        if (!$this->app) {
            $this->app = Factory::getApplication();
        }
    }

    /**
     * 
     */
	public function onAfterInitialise()
	{
		if (is_dir(JPATH_ROOT . '/libraries/syw/src')) {
			\JLoader::registerNamespace('SYW\\Library', JPATH_LIBRARIES . '/syw/src', false, false, 'psr4');
		}

		if (is_dir(JPATH_ROOT . '/libraries/syw/src/Field')) {
			\JLoader::registerNamespace('SYW\\Library\\Field', JPATH_LIBRARIES . '/syw/src/Field', false, false, 'psr4');
		}

		if (is_dir(JPATH_ROOT . '/libraries/syw/src/Image')) {
		    \JLoader::registerNamespace('SYW\\Library\\Image', JPATH_LIBRARIES . '/syw/src/Image', false, false, 'psr4');
		}

		if (is_dir(JPATH_ROOT . '/libraries/syw/src/Vendor')) {
			\JLoader::registerNamespace('SYW\\Library\\Vendor', JPATH_LIBRARIES . '/syw/src/Vendor', false, false, 'psr4');
		}
	}
	
	/**
	 * 
	 */
	public function onBeforeCompileHead()
	{
	    if (!$this->app->isClient('site')) {
	        return;
	    }
	    
	    if ($this->params->get('lazy_stylesheets', 0) == 2) {
	        
	        $wam = $this->app->getDocument()->getWebAssetManager();
	        
	        $inline_js = <<< JS
    			document.addEventListener("DOMContentLoaded", function(event) {
    				[].slice.call(document.head.querySelectorAll('link[rel="lazy-stylesheet"]')).forEach(function(el) { 
                        el.rel = "stylesheet"; 
                    });
    			});
JS;
	        
	        $wam->addInlineScript(str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $inline_js));
    	}
	}
	
	/**
	 * Empty the media cache for modules and plugins of Simplify Your Web extensions that use that cache after save
	 * 
	 * @param  EventInterface $event
	 * @return boolean
	 */
	public function onExtensionAfterSave(EventInterface $event)
	{
	    if (version_compare(JVERSION, '4.9.0', 'lt')) {
	        [$context, $table, $isNew] = $event->getArguments();
	    } else {
	        [$context, $table, $isNew] = array_values($event->getArguments());
	    }

	    if ($isNew) {
	        return true;
	    }

	    if (!$this->app->isClient('administrator')) {
	        return true;
	    }
	    
	    if (!in_array($context, $this->supportedContext)) {
	        return true;
	    }
	    
	    $extensions = [
	        'mod_latestnewsenhanced'             => 'com_latestnewsenhancedpro',
	        'mod_trombinoscope'                  => 'com_trombinoscopeextended',
	        'mod_trulyresponsiveslides'          => 'com_trulyresponsiveslidespro',
	        'mod_weblinklogo'                    => 'com_weblinklogospro',
	        'plg_content_articledetails'         => '',
	        'mod_articledetailsprofile'          => 'com_articledetailsprofiles',
	        'plg_content_articledetailsprofiles' => 'com_articledetailsprofiles',
	    ];	    
	    
	    if ($context !== 'com_plugins.plugin') {
	        
	        // modules
	        
	        $modules = [
	            'mod_latestnewsenhanced'    => 'mod_latestnewsenhanced',
	            'mod_trombinoscope'         => 'mod_trombinoscopecontacts',
	            'mod_trulyresponsiveslides' => 'mod_trulyresponsiveslides',
	            'mod_weblinklogo'           => 'mod_weblinklogos',
	            'mod_articledetailsprofile' => 'plg_content_articledetailsprofiles',
	        ];
	        
	        foreach ($modules as $key => $module) {
	            if ($table->module === $key && is_dir(JPATH_ROOT . '/media/cache/' . $module)) {	                
	                
	                $site_mode = $this->getSiteMode(new Registry($table->params ?? ''), $extensions[$key]);
	                if ($site_mode === 'prod') {	                
    	                $filenames = Folder::files(JPATH_ROOT . '/media/cache/' . $module, '.', false, true);	                
    	                if ($filenames !== false) {	                    
    	                    $file_types = $this->deleteFiles($filenames, $table->id);
    	                    if ($file_types !== false) {	                    
    	                        $this->app->enqueueMessage(Text::sprintf('PLG_SYSTEM_SYW_MESSAGE_MEDIACACHE', implode(', ', $file_types)), 'message');
    	                    }
    	                }
	                }

	                return true;
	            }
	        }
	    } else {
	        
	        // plugins
	        
	        $plugins = [
	            'plg_content_articledetails',
	            'plg_content_articledetailsprofiles',
	        ];
	        
	        $plugin_name = 'plg_' . $table->folder . '_' . $table->element;
	        
	        foreach ($plugins as $plugin) {
	            if ($plugin_name === $plugin && is_dir(JPATH_ROOT . '/media/cache/' . $plugin)) {
    	            
	                $site_mode = $this->getSiteMode(new Registry($table->params ?? ''), $extensions[$plugin]);
	                if ($site_mode === 'prod') {
    	                $filenames = Folder::files(JPATH_ROOT . '/media/cache/' . $plugin, '.', false, true);    	            
    	                if ($filenames !== false) {
    	                    $file_types = $this->deleteFiles($filenames);
    	                    if ($file_types !== false) {
    	                        $this->app->enqueueMessage(Text::sprintf('PLG_SYSTEM_SYW_MESSAGE_MEDIACACHE', implode(', ', $file_types)), 'message');
    	                    }
        	            }
	                }
    
    	            return true;
    	        }
	        }
	    }
	    
	    return true;
	}
	
	/**
	 * Empty the media cache for menu items of Simplify Your Web extensions that use that cache after save
	 * 
	 * @param  EventInterface $event
	 * @return boolean
	 */
	public function onContentAfterSave(EventInterface $event)
	{
	    if (version_compare(JVERSION, '4.9.0', 'lt')) {
	        [$context, $table, $isNew] = $event->getArguments();
	    } else {
	        [$context, $table, $isNew] = array_values($event->getArguments());
	    }
	    
	    if ($isNew) {
	        return true;
	    }
	    
	    if (!$this->app->isClient('administrator')) {
	        return true;
	    }
	    
	    if ($context !== 'com_menus.item') {
	        return true;
	    }

	    // menu items
	    
	    $options = [
	        'com_latestnewsenhancedpro' => 'com_latestnewsenhancedpro',
	        'com_trombinoscopeextended' => 'com_trombinoscopecontactspro',
	        'com_weblinklogospro'       => 'com_weblinklogospro',
	    ];
	    
	    $link_uri = Uri::getInstance($table->link);
	    $query_array = $link_uri->getQuery(true);

	    foreach ($options as $key => $option) {
	        if (isset($query_array['option']) && $query_array['option'] === $key && is_dir(JPATH_ROOT . '/media/cache/' . $option)) {
	            
	            $site_mode = $this->getSiteMode(new Registry($table->params ?? ''), $key);
	            if ($site_mode === 'prod') {
    	            $filenames = Folder::files(JPATH_ROOT . '/media/cache/' . $option, '.', false, true);	            
    	            if ($filenames !== false) {
    	                $file_types = $this->deleteFiles($filenames, $table->id);
    	                if ($file_types !== false) {
    	                    $this->app->enqueueMessage(Text::sprintf('PLG_SYSTEM_SYW_MESSAGE_MEDIACACHE', implode(', ', $file_types)), 'message');
    	                }
    	            }
	            }
	            
	            return true;
	        }
	    }
	    
	    return true;
	}
	
	protected function getSiteMode($params, $extension = '')
	{
	    $mode = $params->get('site_mode', '');

	    if ($mode === '' && $extension) {
	        if (is_file(JPATH_ADMINISTRATOR . '/components/' . $extension . '/config.xml')) {	            
	            $mode = ComponentHelper::getParams($extension)->get('site_mode', 'prod');
	        }
	    }
	    
	    return $mode;
	}
	
	/**
	 * 
	 * @param  array $files
	 * @param  int   $id
	 * @return boolean
	 */
	protected function deleteFiles($files, $id = null)
	{
	    $extensions = [];

	    foreach ($files as $filename) {
	        
	        $delete = false;

	        // Explode the filename (without extension) on _
	        $exploded_filename = explode('_', File::stripExt(basename($filename)));

	        if (empty($id)) {
	            
	            // Delete only the files with no id
	            $id_found = false;

	            foreach ($exploded_filename as $piece) {
	                if (preg_match('/^[0-9]+$/', $piece)) {
    	                $id_found = true;
    	                break;
    	            }
	            }
	            
	            if ($id_found) {
	                continue;
	            }

	            $delete = true;
	        } else {
	            // look for the $id on resulting array
	            if (in_array($id, $exploded_filename)) {
	                $delete = true;
	            }
	        }
	        
	        if ($delete) {
	            if (File::delete($filename)) {
	                if (class_exists('\Joomla\Filesystem\File') && method_exists('\Joomla\Filesystem\File', 'getExt')) {
	                    // Joomla 5 and 6
	                    $extension = \Joomla\Filesystem\File::getExt($filename);
	                } else {
	                    // Joomla 4 fallback
	                    $extension = \Joomla\CMS\Filesystem\File::getExt($filename);
	                }
	                
	                if (!isset($extensions[$extension])) {
	                    $extensions[] = $extension;
	                }
	            }
	        }
	    }

	    if (!empty($extensions)) {
	        
	        $removed = [];
	        
	        foreach ($extensions as $extension) {
	            switch ($extension) {
	                case 'css': $removed[] = Text::_('PLG_SYSTEM_SYW_STYLESHEETS'); break;
	                case 'js': $removed[] = Text::_('PLG_SYSTEM_SYW_SCRIPTS'); break;
	                default: $removed[] = Text::_('PLG_SYSTEM_SYW_IMAGES');
	            }
	        }
	        
	        return array_unique($removed);
	    }

	    return false;
	}

}