<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Plugin\CMSPlugin;

class plgSystemSYW extends CMSPlugin
{
    protected $app;
    
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        
        if (!$this->app) {
            $this->app = Factory::getApplication();
        }
    }
    
	public function onAfterInitialise()
	{
		if (Folder::exists(JPATH_ROOT.'/libraries/syw/src')) {
			JLoader::registerNamespace('SYW\\Library', JPATH_LIBRARIES.'/syw/src', false, false, 'psr4');
		}

		if (Folder::exists(JPATH_ROOT.'/libraries/syw/src/Field')) {
			JLoader::registerNamespace('SYW\\Library\\Field', JPATH_LIBRARIES.'/syw/src/Field', false, false, 'psr4');
		}

		if (Folder::exists(JPATH_ROOT.'/libraries/syw/src/Vendor')) {
			JLoader::registerNamespace('SYW\\Library\\Vendor', JPATH_LIBRARIES.'/syw/src/Vendor', false, false, 'psr4');
		}
	}
	
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

}