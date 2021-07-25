<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetItem;
use SYW\Plugin\System\JqueryEasy\Helper\Helper;
use Joomla\CMS\Version;

/**
 * testing - put in <head> of the template
 * 
 * HTMLHelper::_('script', 'template.es5.js', array('version' => 'auto', 'relative' => true)); // -> goes to _scripts 
 * HTMLHelper::_('stylesheet', 'template-rtl.css', array('version' => 'auto', 'relative' => true)); // -> goes to _styles 
 * Factory::getDocument()->addScriptDeclaration('var tada= 2; jQuery.noConflict(); var j = $.noconflict();');
 * Factory::getDocument()->addScriptDeclaration('var tada= 3;');
 * Factory::getDocument()->addScriptDeclaration('jQuery.noConflict();');
 * Factory::getDocument()->addScriptDeclaration('$(document).ready(function() { });');
 * Factory::getDocument()->addStyleDeclaration('.tada { display: none }');
 * Factory::getDocument()->addStyleDeclaration('.tada2 { display: none }');
 * 
 * <script src="/templates/cassiopeia/js/template.es5.js"></script><!-- part of body, not in _scripts -->
 * <script src="https://code.jquery.com/jquery-migrate-3.3.2.js"></script><!-- part of body, not in _scripts -->
 * <script>var tada = 1; var tada= 2; jQuery.noConflict(); var j = $.noconflict(); $(document).ready(function() { });</script><!-- part of body, not in _scripts -->
 */

class plgSystemJQueryEasy extends CMSPlugin
{
    protected $app;
    
    protected $autoloadLanguage = true;
    
    static protected $loaded = [];
    
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        
        if (!$this->app) {
            $this->app = Factory::getApplication();
        }
        
        if ($this->app->isClient('site')) {
            
            $this->_versioning = $this->params->get('versioning', false);
            
            $this->_enabled = true;
            $this->_headonly = false;
            
            $this->_cdn = 'google';
            
            $this->_showreport = false;
            $this->_verbose_array = null;
            
            $this->_supplement_scripts = array();
            $this->_supplement_stylesheets = array();
            
            $this->_usejQuery = false;
            $this->_usejQueryUI = false;
            
            $this->_jqpath = '';
            $this->_jqmigratepath = '';
            $this->_jqnoconflictpath = '';
            
            $this->_jquipath = '';
            $this->_jquicsspath = '';
            
            $this->_timeafterroute = 0;
            $this->_timebeforerender = 0;
            $this->_timebeforecompilehead = 0;
            $this->_timeafterrender = 0;
            
            $this->_root = str_replace(Uri::root(true) . '/', '', Uri::root());
            $this->_suffix = 'frontend';
        }
    }
    
    function onAfterRoute()
    {
        if (!$this->app->isClient('site')) {
            return;
        }
        
        $this->_enabled = Helper::isEnabledOnPage($this->params, $this->_suffix);
        
        if (!$this->_enabled) {
            return;
        }
        
        $this->loadLanguage();
        
        $showreport = $this->params->get('showreport', 0);
        
        if ($showreport == 1 || $showreport == 3) {
            $this->_showreport = true;
        } else if ($showreport == 2 || $showreport == 4) { // only show report when Super User is logged in
            $this->_showreport = Factory::getUser()->authorise('core.admin') ? true : false;
        }
        
        if ($this->_showreport) {
            $this->_verbose_array = array();
        }
        
        $this->_headonly = $this->params->get('limittohead', 0);
        
        if ($this->_headonly) {
            Helper::report($this->_verbose_array, 'message', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MODIFICATIONSHEADONLY');
        }
        
        $time_start = microtime(true);
        
        // BEGIN prepare spaces to fill with script
        
        $javascript = Helper::getAdditionalScripts($this->params->get('addjavascript' . $this->_suffix, ''));
        if (!empty($javascript)) {
            $this->_supplement_scripts = array_unique($javascript); //Helper::prepare_supplement_scripts(array_unique($javascript), $this->_versioning, ($this->_headonly ? false : true));
        }
        
        // END prepare spaces to fill with scripts
        
        // BEGIN prepare spaces to fill with scripts declarations
        
        //         if (!$this->_headonly) {
        //             Helper::addScriptDeclaration(trim((string) $this->params->get('addjavascriptdeclaration' . $this->_suffix, '')), 'ADD_SCRIPT_DECLARATION_HERE');
        //         }
        
        // END prepare spaces to fill with scripts declarations
        
        // BEGIN prepare spaces to fill with stylesheets and stylesheets declarations
        
        $css = Helper::getAdditionalStylesheets($this->params->get('addcss' . $this->_suffix, ''));
        if (!empty($css)) {
            $this->_supplement_stylesheets = array_unique($css); //Helper::prepare_supplement_stylesheets(array_unique($css), $this->_versioning, ($this->_headonly ? false : true));
        }
        
        //         if (!$this->_headonly) {
        //             Helper::addStyleDeclaration(trim((string) $this->params->get('addcssdeclaration' . $this->_suffix, '')), 'ADD_STYLESHEET_DECLARATION_HERE');
        //         }
        
        // END prepare spaces to fill with stylesheets and stylesheets declarations
        
        // protocole
        
        $protocole = $this->params->get('whichhttp' . $this->_suffix, 'https');
        $protocole = ($protocole == 'none') ? '' : $protocole.':';
        
        // compression
        
        $compressed = '';
        if ($this->params->get('compression' . $this->_suffix, 'compressed') == 'compressed' && !(defined('JDEBUG') && JDEBUG)) {
            $compressed = '.min';
        }
        
        // set jQuery variables
        
        switch ($this->params->get('jqueryin' . $this->_suffix, 0)) {
            case 1: $this->_usejQuery = true; break;
            case 2: $this->_usejQuery = true; $this->_usejQueryUI = true; break;
            default: break;
        }
        
        // jQuery
        
        if ($this->_usejQuery)
        {
            $this->_jqpath = Helper::getJQueryPath($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
            
            //             if (!$this->_headonly && $this->_jqpath) {
            //                 $root_path = (strpos($this->_jqpath, 'http') !== 0) ? $this->_root.$this->_jqpath : $this->_jqpath;
            //                 Helper::addScript('JQEASY_JQLIB', (Uri::isInternal($root_path) ? $this->_versioning : false));
            //             }
            
            // jQuery Migrate
            
            $this->_jqmigratepath = Helper::getMigratePath($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
            
            //             if (!$this->_headonly && $this->_jqmigratepath) {
            //                 $root_path = (strpos($this->_jqmigratepath, 'http') !== 0) ? $this->_root.$this->_jqmigratepath : $this->_jqmigratepath;
            //                 Helper::addScript('JQEASY_JQMIGRATELIB', (Uri::isInternal($root_path) ? $this->_versioning : false));
            //             }
            
            // no conflict path
            
            $addjQueryNoConflict = $this->params->get('addnoconflict' . $this->_suffix, 2);
            if ($addjQueryNoConflict == 1) {
                //                 if (!$this->_headonly) {
                //                     Factory::getDocument()->addScriptDeclaration('JQEASY_JQNOCONFLICT');
                //                 }
            } else if ($addjQueryNoConflict == 2) {
                $this->_jqnoconflictpath = 'media/plg_system_jqueryeasy/js/jquerynoconflict.js';
                
                //                 if (!$this->_headonly) {
                //                     Helper::addScript('JQEASY_JQNOCONFLICT', $this->_versioning);
                //                 }
            }
            
            // jQuery UI
            
            if ($this->_usejQueryUI)
            {
                $this->_jquipath = Helper::getjQueryUIPath($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
                
                //                 if (!$this->_headonly && $this->_jquipath) {
                //                     $root_path = (strpos($this->_jquipath, 'http') !== 0) ? $this->_root.$this->_jquipath : $this->_jquipath;
                //                     Helper::addScript('JQEASY_JQUILIB', (Uri::isInternal($root_path) ? $this->_versioning : false));
                //                 }
                
                $this->_jquicsspath = Helper::getjQueryUICSSPath($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
                
                //                 if (!$this->_headonly && $this->_jquicsspath) {
                //                     $root_path = (strpos($this->_jquicsspath, 'http') !== 0) ? $this->_root.$this->_jquicsspath : $this->_jquicsspath;
                //                     Helper::addStyleSheet('JQEASY_JQUICSS', (Uri::isInternal($root_path) ? $this->_versioning : false));
                //                 }
            } // END jQuery UI
        } // END jQuery
        
        $time_end = microtime(true);
        $this->_timeafterroute = $time_end - $time_start;
    }
    
    function onBeforeCompileHead()
    {
        if (!$this->app->isClient('site')) {
            return;
        }
        
        if (!$this->_enabled) {
            return;
        }
        
        $this->loadLanguage();
        
        $time_start = microtime(true);
        
        $wam = Factory::getApplication()->getDocument()->getWebAssetManager();
        
        $scripts = Factory::getDocument()->_scripts;
        
        var_dump($scripts);
        
        $script_declarations = Factory::getDocument()->_script; // array of script declarations
        if (!isset($script_declarations['text/javascript'])) {
            $script_declarations['text/javascript'] = array(); // no longer a string!
        }
        
        var_dump($script_declarations['text/javascript']);
        
        $styles = Factory::getDocument()->_styleSheets;
        
        var_dump($styles);
        
        $style_declarations = Factory::getDocument()->_style; // array of style declarations
        if (!isset($style_declarations['text/css'])) {
            $style_declarations['text/css'] = array(); // no longer a string!
        }
        
        var_dump($style_declarations['text/css']);
        
        $new_scripts = array();
        $new_styles = array();
        
        //if ($this->_headonly) {
        
        // jQuery
        
        $do_not_add_libraries = false;
        $do_not_add_stylesheets = false;
        $move_unique_library = false;
        $move_unique_libraryui = false;
        $move_unique_cssui = false;
        
        if ($this->_usejQuery) {
            
            $removejQueryNoConflict = $this->params->get('removenoconflict' . $this->_suffix, 1);
            if ($removejQueryNoConflict == 1 || $removejQueryNoConflict == 2) {
                
                // remove all '...jQuery.noConflict(...);' or '... $.noConflict(...);'
                
                $regexp = Helper::getRegularExpression('declaration', 'noconflict');
                
                Helper::search_and_replace_noconflict($regexp, $script_declarations['text/javascript'], ($removejQueryNoConflict == 1 ? false : true), $this->_verbose_array);
                
                // remove potential jquery-noconflict.js (different combinations)
                
                $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'noconflict'), $scripts, $this->_verbose_array);
                if ($number_removed > 0) {
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTS', $number_removed);
                }
            }
            
            $replace_when_unique = $this->params->get('replacewhenunique' . $this->_suffix, 1);
            $add_when_missing = $this->params->get('addwhenmissing' . $this->_suffix, 1);
            
            // remove all references of the jQuery library except scripts to ignore
            
            $ignoreScripts = trim((string)$this->params->get('ignorescripts' . $this->_suffix, ''));
            if ($ignoreScripts) {
                $ignoreScripts = array_map('trim', (array) explode("\n", $ignoreScripts));
            }
            
            $request_search_and_delete_results = ($add_when_missing && $replace_when_unique) ? false : true;
            
            $removed_scripts = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'jquery'), $scripts, $this->_verbose_array, $ignoreScripts, $request_search_and_delete_results);
            
            $number_removed = $request_search_and_delete_results ? count($removed_scripts) : $removed_scripts;
            
            if ($request_search_and_delete_results) {
                if ($number_removed == 0 && !$add_when_missing) {
                    $do_not_add_libraries = true;
                    $do_not_add_stylesheets = true;
                } else if ($number_removed == 1 && !$replace_when_unique) {
                    $this->_jqpath = $removed_scripts[0];
                    $move_unique_library = true;
                    Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUELIBRARY', $this->_jqpath);
                } else {
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERY', $number_removed);
                    }
                }
            } else {
                if ($number_removed > 0) {
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERY', $number_removed);
                }
            }
            
            // remove all references of Migrate scripts
            
            $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'migrate'), $scripts, $this->_verbose_array);
            if ($number_removed > 0) {
                Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDMIGRATE', $number_removed);
            }
            
            // jQuery UI
            
            if ($this->_usejQueryUI) {
                
                // remove all references of the jQuery UI library
                
                $request_search_and_delete_results = $replace_when_unique ? false : true;
                
                $removed_scripts = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'jqueryui'), $scripts, $this->_verbose_array, array(), $request_search_and_delete_results);
                
                $number_removed = $request_search_and_delete_results ? count($removed_scripts) : $removed_scripts;
                
                if ($request_search_and_delete_results) {
                    // 				        if ($number_removed == 0 && !$add_when_missing) {
                    //                             $do_not_add_libraries = true;
                    //                         }
                    if ($number_removed == 1 && !$replace_when_unique) {
                        $this->_jquipath = $removed_scripts[0];
                        $move_unique_libraryui = true;
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUELIBRARYUI', $this->_jquipath);
                    } else {
                        if ($number_removed > 0) {
                            Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUI', $number_removed);
                        }
                    }
                } else {
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUI', $number_removed);
                    }
                }
                
                // remove all references of the jQuery UI stylesheets
                
                $removed_stylesheets = Helper::search_and_delete('css', Helper::getRegularExpression('css', 'jqueryui'), $styles, $this->_verbose_array, array(), $request_search_and_delete_results);
                
                $number_removed = $request_search_and_delete_results ? count($removed_stylesheets) : $removed_stylesheets;
                
                if ($request_search_and_delete_results) {
                    // 				    if ($number_removed == 0 && !$add_when_missing) {
                    // 				        $do_not_add_stylesheets = true;
                    // 				    }
                    if ($number_removed == 1 && !$replace_when_unique) {
                        $this->_jquicsspath = $removed_stylesheets[0];
                        $move_unique_cssui = true;
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUECSSUI', $this->_jquicsspath);
                    } else {
                        if ($number_removed > 0) {
                            Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSS', $number_removed);
                        }
                    }
                } else {
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSS', $number_removed);
                    }
                }
                
            } // END if usejQueryUI
            
            // replace '$(document).ready(function()' or '$(document).ready(function($)' with 'jQuery(document).ready(function($)'
            
            if ($this->params->get('replacedocumentready' . $this->_suffix, 1)) {
                //$script_declarations['text/javascript'] = preg_replace('#\$\(document\).ready\(function\([$]?\)#s', 'jQuery(document).ready(function($)', $script_declarations['text/javascript'], -1, $count);
                
                $count = Helper::search_and_replace('\$\(document\).ready\(function\([$]?\)', $script_declarations['text/javascript'], 'jQuery(document).ready(function($)');
                
                if ($count > 0) {
                    Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REPLACEDDOCUMENTREADY', $count);
                }
            }
            
        } // END if usejQuery
        
        $remainingScripts = array();
        
        // remaining scripts from plugin
        
        $remainingScriptsParam = trim( (string) $this->params->get('stripremainingscripts' . $this->_suffix, ''));
        if ($remainingScriptsParam) {
            $remainingScripts = array_map('trim', (array) explode("\n", $remainingScriptsParam));
        }
        
        // remove remaining scripts
        
        if (!empty($remainingScripts)) {
            foreach ($remainingScripts as $remainingScript) {
                $quoted_script = preg_quote($remainingScript, '/'); // prepares for regexp
                $results = preg_grep('/' . $quoted_script . '/', array_keys($scripts));
                foreach ($results as $result) {
                    unset($scripts[$result]);
                }
                Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGSCRIPT', $remainingScript, count($results));
            }
        }
        
        $remainingStylesheets = array();
        
        // remaining styles from plugin
        
        $remainingStylesheetsParam = trim( (string) $this->params->get('stripremainingcss' . $this->_suffix, ''));
        if ($remainingStylesheetsParam) {
            $remainingStylesheets = array_map('trim', (array) explode("\n", $remainingStylesheetsParam));
        }
        
        // remove remaining stylesheets
        
        if (!empty($remainingStylesheets)) {
            foreach ($remainingStylesheets as $remainingStylesheet) {
                $quoted_stylesheet = preg_quote($remainingStylesheet, '/'); // prepares for regexp
                $results = preg_grep('/' . $quoted_stylesheet . '/', array_keys($styles));
                foreach ($results as $result) {
                    unset($styles[$result]);
                }
                Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGCSS', $remainingStylesheet, count($results));
            }
        }
        
        // additions after cleanup
        
        $options = array();
        if ($this->_versioning) {
            $options['version'] = 'auto';
        }
        
        // add all scripts
        
        if ($this->_usejQuery) {
            
            // 	            $web_assets = $wam->getAssets('script');
            // 	            foreach ($web_assets as $web_asset) {
            
            // 	            	$matches = array();
            // 	            	if (preg_match_all('#' . Helper::getRegularExpression('js', 'jquery') . '#', $web_asset->getUri(false), $matches, PREG_SET_ORDER) > 0) {
            // 	            		if ($web_asset->getName() != 'jquery') {
            // 	            			$wam->disableScript($web_asset->getName()); // won't work if there are dependencies
            // 	            		} else {
            // 	            			$wam->registerScript('jquery', $this->_jqpath); // replace jquery with the new path
            // 	            		}
            // 	            	}
            // 	            }
            
            //$new_asset = new WebAssetItem('jquery2', $this->_jqpath, ['type' => 'script'], ['src' => $this->_jqpath]);
            //$wam->getRegistry()->add('script', $new_asset);
            
            //var_dump($wam->getAsset('script', 'jquery')->getOptions());
            //exit();
            
            if ($this->_jqpath) {
                if ($do_not_add_libraries) {
                    
                    
                    if ($wam->assetExists('script', 'jquery')) {
                        $wam->disableScript('jquery'); // won't work if there are dependencies
                    }
                    
                    
                    Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NOJQUERYLIBRARIESADDED');
                } else {
                    $root_path = (strpos($this->_jqpath, 'http') !== 0) ? $this->_root . Uri::root(true) . '/' . $this->_jqpath : $this->_jqpath;
                    //$new_scripts[$this->_jqpath] = array('type' => 'text/javascript', 'options' => (Uri::isInternal($root_path) ? $options : array()));
                    
                    //var_dump($this->_root); // http://localhost:7878
                    //var_dump(Uri::root(true)); // /MyWork_4_x_free_test
                    //var_dump(JPATH_ROOT); // E:\wamp64\www\MyWork_4_x_free_test
                    
                    //var_dump($root_path); // http://localhost:7878/MyWork_4_x_free_test/media/vendor/jquery/js/jquery.js
                    //var_dump(Uri::isInternal($root_path)); // true
                    
                    $wam->registerAndUseScript('jquery', $this->_jqpath, Uri::isInternal($root_path) ? $options : ['version' => '']); // add or replace jquery with the new path
                    
                    
                    if ($move_unique_library) {
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERY', $this->_jqpath);
                    } else {
                        Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERY', '<a href="'.$this->_jqpath.'" target="_blank">'.$this->_jqpath.'</a>');
                    }
                }
            } else {
                Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERY');
            }
            
            if ($this->_jqmigratepath) {
                if ($do_not_add_libraries) { // no need to add Migrate if jQuery is not even loaded
                    
                    
                    if ($wam->assetExists('script', 'jquery-migrate')) {
                        $wam->disableScript('jquery-migrate');
                    }
                    
                    
                    Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NOMIGRATEADDED');
                } else {
                    $root_path = (strpos($this->_jqmigratepath, 'http') !== 0) ? $this->_root . Uri::root(true) . '/' . $this->_jqmigratepath : $this->_jqmigratepath;
                    //$new_scripts[$this->_jqmigratepath] = array('type' => 'text/javascript', 'options' => (Uri::isInternal($root_path) ? $options : array()));
                    
                    
                    $wam->registerAndUseScript('jquery-migrate', $this->_jqmigratepath, Uri::isInternal($root_path) ? $options : ['version' => ''], [], ['jquery']);
                    
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYMIGRATE', '<a href="'.$this->_jqmigratepath.'" target="_blank">'.$this->_jqmigratepath.'</a>');
                }
            }
            
            if ($this->params->get('addnoconflict' . $this->_suffix, 2) == 1) {
                if ($do_not_add_libraries) {
                    Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NONOCONFLICTDECLARATIONADDED');
                } else {
                    //$script_declarations['text/javascript'] = 'jQuery.noConflict(); ' . $script_declarations['text/javascript'];
                    
                    
                    $wam->addInlineScript('jQuery.noConflict(); ', [], [], ['jquery']);
                    
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTDECLARATION');
                }
            } else if ($this->params->get('addnoconflict' . $this->_suffix, 2) == 2 && $this->_jqnoconflictpath) {
                if ($do_not_add_libraries) {
                    
                    
                    if ($wam->assetExists('script', 'jquery-noconflict')) {
                        $wam->disableScript('jquery-noconflict');
                    }
                    
                    
                    Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NONOCONFLICTSCRIPTADDED');
                } else {
                    //$new_scripts[$this->_jqnoconflictpath] = array('type' => 'text/javascript', 'options' => $options);
                    
                    
                    $wam->registerAndUseScript('jquery-noconflict', $this->_jqnoconflictpath, $options, [], ['jquery']);
                    
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTSCRIPT', $this->_jqnoconflictpath);
                }
            }
            
            if ($this->_usejQueryUI) {
                if ($this->_jquipath) {
                    if ($do_not_add_libraries) {
                        
                        
                        if ($wam->assetExists('script', 'jquery-ui')) {
                            $wam->disableScript('jquery-ui'); // won't work if there are dependencies
                        }
                        
                        
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NOJQUERYUILIBRARYADDED');
                    } else {
                        $root_path = (strpos($this->_jquipath, 'http') !== 0) ? $this->_root . Uri::root(true) . '/' . $this->_jquipath : $this->_jquipath;
                        //$new_scripts[$this->_jquipath] = array('type' => 'text/javascript', 'options' => (Uri::isInternal($root_path) ? $options : array()));
                        
                        
                        $wam->registerAndUseScript('jquery-ui', $this->_jquipath, Uri::isInternal($root_path) ? $options : ['version' => ''], [], ['jquery']); // add or replace jquery ui with the new path
                        
                        
                        if ($move_unique_libraryui) {
                            Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERYUI', $this->_jquipath);
                        } else {
                            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUI', '<a href="'.$this->_jquipath.'" target="_blank">'.$this->_jquipath.'</a>');
                        }
                    }
                } else {
                    Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUI');
                }
            }
        }
        
        if (!empty($new_scripts)) {
            Helper::report($this->_verbose_array, 'message', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REORDEREDLIBRARIES');
        }
        
        // add all styles
        
        if ($this->_usejQuery) {
            
            if ($this->_usejQueryUI) {
                if ($this->_jquicsspath) {
                    if ($do_not_add_stylesheets) {
                        
                        
                        if ($wam->assetExists('style', 'jquery-ui')) {
                            $wam->disableStyle('jquery-ui'); // won't work if there are dependencies
                        }
                        
                        
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NOJQUERYUISTYLESHEETADDED');
                    } else {
                        $root_path = (strpos($this->_jquicsspath, 'http') !== 0) ? $this->_root . Uri::root(true) . '/' . $this->_jquicsspath : $this->_jquicsspath;
                        //$new_styles[$this->_jquicsspath] = array('type' => 'text/css', 'options' => (Uri::isInternal($root_path) ? $options : array()));
                        
                        
                        $wam->registerAndUseStyle('jquery-ui', $this->_jquicsspath, Uri::isInternal($root_path) ? $options : ['version' => ''], [], ['jquery']);
                        
                        
                        if ($move_unique_cssui) {
                            Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERYUICSS', $this->_jquicsspath);
                        } else {
                            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUICSS', '<a href="'.$this->_jquicsspath.'" target="_blank">'.$this->_jquicsspath.'</a>');
                        }
                    }
                } else {
                    if ($this->params->get('jqueryuitheme' . $this->_suffix, 'none') != 'none') {
                        Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUICSS');
                    }
                }
            }
        }
        
        // add all scripts
        
        foreach($this->_supplement_scripts as $i => $path) {
            $root_path = (strpos($path, 'http') !== 0) ? Uri::root() . ltrim($path, '/') : $path;
            //$new_scripts[$path] = array('type' => 'text/javascript', 'options' => (Uri::isInternal($root_path) ? $options : array()));
            
            
            $wam->registerAndUseScript('jqe-supplemental-script-' . $i, $path, Uri::isInternal($root_path) ? $options : ['version' => '']);
            
            
            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPT', $path);
        }
        
        // add all styles
        
        foreach($this->_supplement_stylesheets as $i => $path) {
            $root_path = (strpos($path, 'http') !== 0) ? Uri::root() . ltrim($path, '/') : $path;
            //$new_styles[$path] = array('type' => 'text/css', 'options' => (Uri::isInternal($root_path) ? $options : array()));
            
            
            $wam->registerAndUseStyle('jqe-supplemental-style-' . $i, $path, Uri::isInternal($root_path) ? $options : ['version' => '']);
            
            
            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEET', $path);
        }
        
        // add all script declarations
        
        // script declaration from plugin
        $javascript_declaration = trim( (string) $this->params->get('addjavascriptdeclaration' . $this->_suffix, ''));
        if (!empty($javascript_declaration)) {
            //$script_declarations['text/javascript'] .= $javascript_declaration;
            
            
            $wam->addInlineScript($javascript_declaration);
            
            
            if ($this->_showreport) {
                $lines = array_map('trim', (array) explode("\n", $javascript_declaration));
                Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPTDECLARATION', $lines[0]);
            }
        }
        
        // add all style declarations
        
        $css_declaration = trim( (string) $this->params->get('addcssdeclaration' . $this->_suffix, ''));
        if (!empty($css_declaration)) {
            //$style_declarations['text/css'] .= $css_declaration;
            
            
            $wam->addInlineStyle($css_declaration);
            
            
            if ($this->_showreport) {
                $lines = array_map('trim', (array) explode("\n", $css_declaration));
                Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEETDECLARATION', $lines[0]);
            }
        }
        
        //         } else { // the whole document is scanned
        
        //             // make sure we start with all jQuery Easy scripts
        
        //             $scripts_jqeasy = array();
        
        //             foreach ($scripts as $url => $type) {
        //                 if (preg_match('#JQEASY_#s', $url)) {
        //                     $scripts_jqeasy[$url] = $type;
        //                 }
        //             }
        
        //             if (!empty($scripts_jqeasy)) {
        
        //                 foreach ($scripts_jqeasy as $url_jqeasy => $type_jqeasy) {
        //                     $new_scripts[$url_jqeasy] = $type_jqeasy;
        //                     unset($scripts[$url_jqeasy]);
        //                 }
        
        //                 // then with all system scripts
        
        //                 $quoted_path = preg_quote('media/system/js/', '/');
        //                 foreach ($scripts as $url => $type) {
        //                     if (preg_match('#'.$quoted_path.'#s', $url)) {
        //                         $new_scripts[$url] = $type;
        //                         unset($scripts[$url]);
        //                     }
        //                 }
        
        //                 Helper::report($this->_verbose_array, 'message', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REORDEREDLIBRARIES');
        //             }
        //         }
        
        
        $version = new Version();
        
        foreach ($scripts as $key => &$value) {
            if ($value['options']['version'] == 'auto') {
                $value['options']['version'] = $version->getMediaVersion() . '_jqetagged';
            } else {
                $value['options']['version'] .= '_jqetagged';
            }
        }
        
        Factory::getDocument()->_scripts = array_merge($new_scripts, $scripts);
        
        if (!empty($script_declarations['text/javascript'])) {
            
            Factory::getDocument()->_script['text/javascript'] = $script_declarations['text/javascript'];
            
            var_dump(Factory::getDocument()->_script['text/javascript']);
        } else {
            // after removal of scripts, we may end up with nothing
            if (isset(Factory::getDocument()->_script['text/javascript'])) {
                unset(Factory::getDocument()->_script['text/javascript']);
            }
        }
        
        foreach ($styles as $key => $value) {
            if ($value['options']['version'] == 'auto') {
                $value['options']['version'] = $version->getMediaVersion() . '_jqetagged';
            } else {
                $value['options']['version'] .= '_jqetagged';
            }
        }
        
        Factory::getDocument()->_styleSheets = array_merge($new_styles, $styles);
        
        if (!empty($style_declarations['text/css'])) {
            Factory::getDocument()->_style['text/css'] = $style_declarations['text/css'];
            
            var_dump(Factory::getDocument()->_style['text/css']);
        }
        
        // 	    var_dump(preg_replace('!\s+!', ' ', Factory::getDocument()->_script['text/javascript']));
        //  	    var_dump(Factory::getDocument()->_scripts);
        // 	    var_dump(preg_split('/;[\s]+/', Factory::getDocument()->_script['text/javascript']));
        //  	    var_dump(Factory::getDocument()->_styleSheets);
        //  	var_dump(Factory::getDocument()->_style['text/css']);
        
        $time_end = microtime(true);
        $this->_timebeforecompilehead = $time_end - $time_start;
    }
    
    function onAfterRender()
    {
        if (!$this->app->isClient('site')) {
            return;
        }
        
        if (!$this->_enabled) {
            return;
        }
        
        $this->loadLanguage();
        
        $time_start = microtime(true);
        
        $body = $this->app->getBody();
        
        if (!$this->_headonly) {
            
            // remove and store ALL script tags that have the data-asset-name attribute
            // remove and store ALL link tags that have the data-asset-name attribute
            
            // remove and store all style tags that have the nonce attribute
            // remove and store all script tags that have the nonce attribute
            
            // remove and store all script and link tags that have '_jqetagged' in the url
            
            
            
            // <script[^>]*(data-asset-name)[^>]*><\/script>
            // <script[^>]*(nonce)[^>]*>[\s\S]*?[^(<\/script>)]*<\/script>
            // <link[^>]*(data-asset-name)[^>]*>
            // <style[^>]*(nonce)[^>]*>[\s\S]*?[^(<\/style>)]*<\/style>
            
            // <script[^>]*(_jqetagged)[^>]*><\/script>
            // <link[^>]*(_jqetagged)[^>]*>
            
            //             $stored_scripts = array();
            
            //             if (preg_match_all('#<script[^>]*(data-asset-name)[^>]*><\/script>#', $body, $matches, PREG_SET_ORDER) > 0) {
            //                 foreach ($matches as $match) {
            //                     $stored_scripts[] = $match[0];
            //                     $quoted_match = preg_quote($match[0], '#');
            //                     $body = preg_replace('#'.$quoted_match.'#', '', $body, 1);
            //                 }
            //             }
            
            //             var_dump($stored_scripts);
            
            
            
            
            
            
            
            $remove_empty_scripts = false;
            $remove_empty_links = false;
            
            $remainingScriptsParam = trim( (string) $this->params->get('stripremainingscripts' . $this->_suffix, ''));
            if ($remainingScriptsParam) {
                $remainingScripts = array_map('trim', (array) explode("\n", $remainingScriptsParam));
                if (!empty($remainingScripts)) {
                    foreach ($remainingScripts as $script) {
                        $quoted_script = preg_quote($script, '/'); // prepares for regexp
                        $count = 0;
                        $body = preg_replace('#<script[^>]*'.$quoted_script.'(?!([^>]*?)data-asset-name)[^>]*></script>#', '', $body, -1, $count); // leave assets untouched
                        if ($count > 0) {
                            Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGSCRIPT', $script, $count);
                        }
                    }
                }
            }
            
            $remainingStylesheetsParam = trim( (string) $this->params->get('stripremainingcss' . $this->_suffix, ''));
            if ($remainingStylesheetsParam) {
                $remainingStylesheets = array_map('trim', (array) explode("\n", $remainingStylesheetsParam));
                if (!empty($remainingStylesheets)) {
                    foreach ($remainingStylesheets as $stylesheet) {
                        $quoted_stylesheet = preg_quote($stylesheet, '/'); // prepares for regexp
                        $count = 0;
                        $body = preg_replace('#<link[^>]*'.$quoted_stylesheet.'(?!([^>]*?)data-asset-name)[^>]*/>#', '', $body, -1, $count); // leave assets untouched
                        if ($count > 0) {
                            Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGCSS', $stylesheet, $count);
                        }
                    }
                }
            }
            
            if ($this->_usejQuery) {
                
                $removejQueryNoConflict = $this->params->get('removenoconflict' . $this->_suffix, 1);
                if ($removejQueryNoConflict == 1 || $removejQueryNoConflict == 2) {
                    
                    // remove all '...jQuery.noConflict(...);' or '... $.noConflict(...);'
                    
                    $regexp = Helper::getRegularExpression('declaration', 'noconflict');
                    
                    Helper::search_and_replace_noconflict($regexp, $body, ($removejQueryNoConflict == 1 ? false : true), $this->_verbose_array);
                    
                    // remove potential jquery-noconflict.js (different combinations)
                    
                    $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'noconflict'), $body, $this->_verbose_array);
                    
                    if ($number_removed > 0) {
                        $remove_empty_scripts = true;
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTS', $number_removed);
                    }
                }
                
                $do_not_add_libraries = false;
                $do_not_add_stylesheets = false;
                $move_unique_library = false;
                
                $replace_when_unique = $this->params->get('replacewhenunique' . $this->_suffix, 1);
                $add_when_missing = $this->params->get('addwhenmissing' . $this->_suffix, 1);
                
                // remove all other references to jQuery library except some
                $ignoreScripts = trim( (string) $this->params->get('ignorescripts' . $this->_suffix, ''));
                if ($ignoreScripts) {
                    $ignoreScripts = array_map('trim', (array) explode("\n", $ignoreScripts));
                }
                
                $request_search_and_delete_results = ($add_when_missing && $replace_when_unique) ? false : true;
                
                $removed_scripts = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'jquery'), $body, $this->_verbose_array, $ignoreScripts, $request_search_and_delete_results);
                
                $number_removed = $request_search_and_delete_results ? count($removed_scripts) : $removed_scripts;
                
                if ($request_search_and_delete_results) {
                    if ($number_removed == 0 && !$add_when_missing) {
                        $do_not_add_libraries = true;
                        $do_not_add_stylesheets = true;
                        //Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NOJQUERYLIBRARIESADDED');
                    } else if ($number_removed == 1 && !$replace_when_unique) {
                        $this->_jqpath = $removed_scripts[0];
                        $move_unique_library = true;
                        $remove_empty_scripts = true;
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUELIBRARY', $this->_jqpath);
                    } else {
                        if ($number_removed > 0) {
                            $remove_empty_scripts = true;
                            foreach ($removed_scripts as $removed_script) {
                                Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYLIBRARY', $removed_script);
                            }
                        }
                    }
                } else {
                    if ($number_removed > 0) {
                        $remove_empty_scripts = true;
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERY', $number_removed);
                    }
                }
                
                // use jQuery version set in the plugin
                if ($this->_jqpath) {
                    if ($do_not_add_libraries) {
                        $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQLIB#', 'GARBAGE', $body, 1);
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NOJQUERYLIBRARIESADDED');
                        $remove_empty_scripts = true;
                    } else {
                        $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQLIB#', $this->_jqpath, $body, 1);
                        if ($move_unique_library) {
                            Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERY', $this->_jqpath);
                        } else {
                            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERY', '<a href="'.$this->_jqpath.'" target="_blank">'.$this->_jqpath.'</a>');
                        }
                    }
                } else {
                    Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERY');
                }
                
                // remove all references to Migrate scripts
                
                $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'migrate'), $body, $this->_verbose_array);
                
                // TODO? replace when unique
                
                if ($number_removed > 0) {
                    $remove_empty_scripts = true;
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDMIGRATE', $number_removed);
                }
                
                // use jQuery Migrate
                if ($this->_jqmigratepath) {
                    if ($do_not_add_libraries) { // no need to add Migrate if jQuery is not even loaded
                        $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQMIGRATELIB#', 'GARBAGE', $body, 1);
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NOMIGRATEADDED');
                        $remove_empty_scripts = true;
                    } else {
                        $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQMIGRATELIB#', $this->_jqmigratepath, $body, 1);
                        Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYMIGRATE', '<a href="'.$this->_jqmigratepath.'" target="_blank">'.$this->_jqmigratepath.'</a>');
                    }
                }
                
                // replace deleted occurences
                $addjQueryNoConflict = $this->params->get('addnoconflict' . $this->_suffix, 2);
                if ($addjQueryNoConflict == 1) {
                    if ($do_not_add_libraries) {
                        $body = preg_replace('#JQEASY_JQNOCONFLICT#', '', $body, 1);
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NONOCONFLICTDECLARATIONADDED');
                    } else {
                        $body = preg_replace('#JQEASY_JQNOCONFLICT#', 'jQuery.noConflict();', $body, 1); // add unique jQuery.noConflict();
                        Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTDECLARATION');
                    }
                } elseif ($addjQueryNoConflict == 2) {
                    if ($do_not_add_libraries) {
                        $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQNOCONFLICT#', 'GARBAGE', $body, 1);
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NONOCONFLICTSCRIPTADDED');
                        $remove_empty_scripts = true;
                    } else {
                        $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQNOCONFLICT#', $this->_jqnoconflictpath, $body, 1); // add jquerynoconflict.js
                        Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTSCRIPT', $this->_jqnoconflictpath);
                    }
                }
                
                // replace '$(document).ready(function()' or '$(document).ready(function($)' with 'jQuery(document).ready(function($)'
                if ($this->params->get('replacedocumentready' . $this->_suffix, 1)) {
                    $count = 0;
                    $body = preg_replace('#\$\(document\).ready\(function\([$]?\)#s', 'jQuery(document).ready(function($)', $body, -1, $count);
                    if ($count > 0) {
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REPLACEDDOCUMENTREADY', $count);
                    }
                }
                
                if ($this->_usejQueryUI) {
                    
                    //$do_not_add_libraries = false;
                    $move_unique_libraryui = false;
                    
                    // remove all other references to jQuery UI library
                    
                    $request_search_and_delete_results = $replace_when_unique ? false : true;
                    
                    $removed_scripts = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'jqueryui'), $body, $this->_verbose_array, array(), $request_search_and_delete_results);
                    
                    $number_removed = $request_search_and_delete_results ? count($removed_scripts) : $removed_scripts;
                    
                    if ($request_search_and_delete_results) {
                        // 				    if ($number_removed == 0 && !$add_when_missing) {
                        // 				        $do_not_add_libraries = true;
                        // 				    }
                        if ($number_removed == 1 && !$replace_when_unique) {
                            $this->_jquipath = $removed_scripts[0];
                            $move_unique_libraryui = true;
                            $remove_empty_scripts = true;
                            Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUELIBRARYUI', $this->_jquipath);
                        } else {
                            if ($number_removed > 0) {
                                $remove_empty_scripts = true;
                                foreach ($removed_scripts as $removed_script) {
                                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUILIBRARY', $removed_script);
                                }
                            }
                        }
                    } else {
                        if ($number_removed > 0) {
                            $remove_empty_scripts = true;
                            Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUI', $number_removed);
                        }
                    }
                    
                    // use jQuery UI version set in the plugin
                    if ($this->_jquipath) {
                        if ($do_not_add_libraries) {
                            $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQUILIB#', 'GARBAGE', $body, 1);
                            Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NOJQUERYUILIBRARYADDED');
                            $remove_empty_scripts = true;
                        } else {
                            $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQUILIB#', $this->_jquipath, $body, 1);
                            if ($move_unique_libraryui) {
                                Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERYUI', $this->_jquipath);
                            } else {
                                Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUI', '<a href="'.$this->_jquipath.'" target="_blank">'.$this->_jquipath.'</a>');
                            }
                        }
                    } else {
                        Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUI');
                    }
                    
                    // remove all other references to jQuery UI stylesheets
                    
                    //$do_not_add_stylesheets = $do_not_add_libraries;
                    $move_unique_cssui = false;
                    
                    $removed_stylesheets = Helper::search_and_delete('css', Helper::getRegularExpression('css', 'jqueryui'), $body, $this->_verbose_array, array(), $request_search_and_delete_results);
                    
                    $number_removed = $request_search_and_delete_results ? count($removed_stylesheets) : $removed_stylesheets;
                    
                    if ($request_search_and_delete_results) {
                        // 				    if ($number_removed == 0 && !$add_when_missing) {
                        // 				        $do_not_add_stylesheets = true;
                        // 				    }
                        if ($number_removed == 1 && !$replace_when_unique) {
                            $this->_jquicsspath = $removed_stylesheets[0];
                            $move_unique_cssui = true;
                            $remove_empty_links = true;
                            Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUECSSUI', $this->_jquicsspath);
                        } else {
                            if ($number_removed > 0) {
                                $remove_empty_links = true;
                                foreach ($removed_stylesheets as $removed_stylesheet) {
                                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSSLINK', $removed_stylesheet);
                                }
                            }
                        }
                    } else {
                        if ($number_removed > 0) {
                            $remove_empty_links = true;
                            Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSS', $number_removed);
                        }
                    }
                    
                    // use jQuery UI CSS set in the plugin
                    if ($this->_jquicsspath) {
                        if ($do_not_add_stylesheets) {
                            $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQUICSS#', 'GARBAGE', $body, 1);
                            Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_NOJQUERYUISTYLESHEETADDED');
                            $remove_empty_links = true;
                        } else {
                            $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQUICSS#', $this->_jquicsspath, $body, 1);
                            if ($this->_showreport) {
                                if ($move_unique_cssui) {
                                    Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERYUICSS', $this->_jquicsspath);
                                } else {
                                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUICSS', '<a href="'.$this->_jquicsspath.'" target="_blank">'.$this->_jquicsspath.'</a>');
                                }
                            }
                        }
                    } else {
                        if ($this->params->get('jqueryuitheme' . $this->_suffix, 'none') != 'none') {
                            Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUICSS');
                        }
                    }
                }
            } // END if $this->jQuery
            
            // remove all obsolete script tags
            if ($remove_empty_scripts) {
                $count = 0;
                $body = preg_replace('#<script[^>]*GARBAGE[^>]*></script>#', '', $body, -1, $count); // remove newly empty scripts
                if ($count > 0) {
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYSCRIPTTAGS', $count);
                }
            }
            
            // remove all obsolete link tags
            if ($remove_empty_links) {
                $count = 0;
                $body = preg_replace('#<link[^>]*GARBAGE[^>]*/>#', '', $body, -1, $count); // remove newly empty stylesheets
                if ($count > 0) {
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYLINKTAGS', $count);
                }
            }
            
            // all scripts and stylesheets are added here instead of earlier so they don't get checked by the plugin
            
            if (!empty($this->_supplement_scripts)) {
                foreach($this->_supplement_scripts as $path) {
                    $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)ADD_SCRIPT_HERE#', $path, $body, 1);
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPT', $path);
                }
            }
            
            $javascript_declaration = trim( (string) $this->params->get('addjavascriptdeclaration' . $this->_suffix, ''));
            if (!empty($javascript_declaration)) {
                $body = preg_replace('#ADD_SCRIPT_DECLARATION_HERE#', $javascript_declaration, $body, 1);
                if ($this->_showreport) {
                    $lines = array_map('trim', (array) explode("\n", $javascript_declaration));
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPTDECLARATION', $lines[0]);
                }
            }
            
            if (!empty($this->_supplement_stylesheets)) {
                foreach($this->_supplement_stylesheets as $path) {
                    $body = preg_replace('#([\\/a-zA-Z0-9_:\.~-]*)ADD_STYLESHEET_HERE#', $path, $body, 1);
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEET', $path);
                }
            }
            
            $css_declaration = trim( (string) $this->params->get('addcssdeclaration' . $this->_suffix, ''));
            if (!empty($css_declaration)) {
                $body = preg_replace('#ADD_STYLESHEET_DECLARATION_HERE#', $css_declaration, $body, 1);
                if ($this->_showreport) {
                    $lines = array_map('trim', (array) explode("\n", $css_declaration));
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEETDECLARATION', $lines[0]);
                }
            }
            
            // Remove blank lines
            
            if ($this->params->get('removeblanklines' . $this->_suffix, 0)) {
                $count = 0;
                $body = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $body, -1, $count); // gets all of the empty lines in the source and replaces them with a simple carriage return to preserve the content structure.
                if ($count > 0) {
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEBLANKLINES', $count);
                }
            }
        } // END if changes to the whole page
        
        $time_end = microtime(true);
        $this->_timeafterrender = $time_end - $time_start;
        
        // show the report
        
        if ($this->_showreport) {
            $showreport = $this->params->get('showreport', 0);
            $this_show_in_modal = true;
            if ($showreport == 3 || $showreport == 4) {
                $this_show_in_modal = false;
            }
            $report = Helper::getReport($this->_verbose_array, $this->_timeafterroute + $this->_timebeforerender + $this->_timebeforecompilehead + $this->_timeafterrender, '', $this_show_in_modal);
            $body = preg_replace('#</body>#', $report.'</body>', $body, 1);
        }
        
        $this->app->setBody($body);
        
        return true;
    }
    
}