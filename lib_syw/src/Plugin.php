<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;

class Plugin
{    
    /**
     * The plugin params
     */
    protected static $plugin_params;
    
    /**
     * Get the plugin params
     * 
     * @return object
     */
    protected static function getPluginParams()
    {
        if (self::$plugin_params == null) {
            if (PluginHelper::isEnabled('system', 'syw')) {
                $plugin = PluginHelper::getPlugin('system', 'syw');
                self::$plugin_params = json_decode($plugin->params);
            }
        }
        
        return self::$plugin_params;
    }
    
    public static function getImageLibrary()
    {
        $plugin_params = self::getPluginParams();
        
        if (isset($plugin_params) && isset($plugin_params->image_library) && $plugin_params->image_library != 'none') {
            return $plugin_params->image_library;
        }
        
        return 'gd';
    }

    public static function getLazyStylesheet()
    {
        $plugin_params = self::getPluginParams();
        
        if (isset($plugin_params) && isset($plugin_params->lazy_stylesheets)) {
            return $plugin_params->lazy_stylesheets;
        }
        
        return 0;
    }
    
    public static function getWebfontService()
    {
        $plugin_params = self::getPluginParams();
        
        if (isset($plugin_params) && isset($plugin_params->webfont_service)) {
            return $plugin_params->webfont_service;
        }
        
        return 'google';
    }
}
