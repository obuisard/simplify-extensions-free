<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\JQueryEasy\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Event\SubscriberInterface;
use SYW\Plugin\System\JQueryEasy\Helper\JQueryEasyHelper as Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class JQueryEasy extends CMSPlugin implements SubscriberInterface
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
     * Internal variables
     */
    protected $_enabled;
    
    protected $_cdn;
    
    protected $_showreport;
    protected $_verbose_array;
    
    protected $_supplement_scripts;
    protected $_supplement_stylesheets;
    
    protected $_usejQuery;
    protected $_usejQueryUI;
    protected $_useBootstrap;
    
    protected $_jqpath;
    protected $_jqmigratepath;
    protected $_jqnoconflictpath;
    
    protected $_jquipath;
    protected $_jquicsspath;
    
    protected $_bootstrapjspath;
    protected $_bootstrapcsspath;
    protected $_popperpath;
    
    protected $_timebeforecompilehead;
    protected $_timeafterrender;
    
    protected $_suffix;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     */
    public static function getSubscribedEvents(): array
    {
        try {
            $app = Factory::getApplication();
        } catch (\Exception $e) {
            return [];
        }
        
        if (!$app->isClient('site')) {
            return [];
        }

        return [
            'onBeforeCompileHead' => 'onBeforeCompileHead',
            'onAfterRender'       => 'onAfterRender',
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
        
        if ($this->app->isClient('site')) {
            
            $this->_cdn = 'google';
            
            $this->_showreport = false;
            $this->_verbose_array = null;
            
            $this->_supplement_scripts = array();
            $this->_supplement_stylesheets = array();
            
            $this->_usejQuery = false;
            $this->_usejQueryUI = false;
            $this->_useBootstrap = false;
            
            $this->_jqpath = '';
            $this->_jqmigratepath = '';
            $this->_jqnoconflictpath = '';
            
            $this->_jquipath = '';
            $this->_jquicsspath = '';
            
            $this->_bootstrapjspath = array();
            $this->_bootstrapcsspath = array();
            $this->_popperpath = '';
            
            $this->_timebeforecompilehead = 0;
            $this->_timeafterrender = 0;
            
            $this->_suffix = '';
        }
    }
    
    public function onBeforeCompileHead()
    {
        if (!$this->isEnabledOnPage()) {
            return;
        }
        
        $this->loadLanguage();
        
        // report
        
        $showreport = $this->params->get('showreport', 0);
        
        if ($showreport == 1 || $showreport == 3) {
            $this->_showreport = true;
        } else if ($showreport == 2 || $showreport == 4) { // only show report when Super User is logged in
            $this->_showreport = Factory::getUser()->authorise('core.admin') ? true : false;
        }
        
        if ($this->_showreport) {
            $this->_verbose_array = array();
        }
        
        // page scan
        
        $pagescan = (int)$this->params->get('pagescan', 0);
        
        if ($pagescan === 0) {
            Helper::report($this->_verbose_array, 'message', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MODIFICATIONSAPIONLY');
        } elseif ($pagescan === 1) {
            Helper::report($this->_verbose_array, 'message', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MODIFICATIONSHEADONLY');
        } else {
            Helper::report($this->_verbose_array, 'message', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_PROCESSINGTHEWHOLEDOCUMENT');
        }
        
        // protocole
        
        $protocole = $this->params->get('whichhttp' . $this->_suffix, 'https');
        $protocole = ($protocole == 'none') ? '' : $protocole.':';
        
        // compression
        
        $compressed = '';
        if ($this->params->get('compression' . $this->_suffix, 'compressed') == 'compressed' && !(defined('JDEBUG') && JDEBUG)) {
            $compressed = '.min';
        }
        
        // timing starts
        
        $time_start = microtime(true);
        
        // STEP 1 - GET PATHS SET IN THE PLUGIN
        // ====================================
        
        // get additional scripts
        
        $javascript = Helper::getAdditionalScripts($this->params->get('addjavascript' . $this->_suffix, ''));
        if (!empty($javascript)) {
            $this->_supplement_scripts = array_unique($javascript);
        }
        
        // get additional stylesheets
        
        $css = Helper::getAdditionalStylesheets($this->params->get('addcss' . $this->_suffix, ''));
        if (!empty($css)) {
            $this->_supplement_stylesheets = array_unique($css);
        }
        
        // jQuery
        
        switch ($this->params->get('jqueryinpage' . $this->_suffix, 0)) {
            case 1: $this->_usejQuery = true; break;
            case 2: $this->_usejQuery = true; $this->_usejQueryUI = true; break;
            default: break;
        }
        
        if ($this->_usejQuery) {
            
            $this->_jqpath = Helper::getJQueryPath($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
            
            // jQuery Migrate
            
            $this->_jqmigratepath = Helper::getMigratePath($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
            
            // no conflict path
            
            if ($this->params->get('addnoconflict' . $this->_suffix, 0)) {
                $this->_jqnoconflictpath = 'media/plg_system_jqueryeasy/js/jquerynoconflict.js';
            }
            
            // jQuery UI
            
            if ($this->_usejQueryUI) {
                
                $this->_jquipath = Helper::getjQueryUIPath($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
                $this->_jquicsspath = Helper::getjQueryUICSSPath($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
            }
        }
        
        // Bootstrap
        
        $this->_useBootstrap = $this->params->get('bootstrapinpage' . $this->_suffix, 0);
        
        if ($this->_useBootstrap) {
            
            $this->_popperpath = Helper::getPopperPath($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
            $this->_bootstrapjspath = Helper::getBootstrapPaths($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
            $this->_bootstrapcsspath = Helper::getBootstrapCSSPaths($protocole, $compressed, $this->params, $this->_verbose_array, $this->_cdn, $this->_suffix);
        }
        
        // STEP 2 - FIX AND REMOVE DUPLICATES IN CODE GOING THROUGH API
        // ============================================================
        
        if ($pagescan === 0 || $pagescan === 2) { // API or API + body
            
            $scripts = Factory::getDocument()->_scripts;
            
            //var_dump($scripts);
            
            $script_declarations = Factory::getDocument()->_script; // array of script declarations
            if (!isset($script_declarations['text/javascript'])) {
                $script_declarations['text/javascript'] = array(); // no longer a string!
            }
            
            //var_dump($script_declarations['text/javascript']);
            
            $styles = Factory::getDocument()->_styleSheets;
            
            //var_dump($styles);
            
            $style_declarations = Factory::getDocument()->_style; // array of style declarations
            if (!isset($style_declarations['text/css'])) {
                $style_declarations['text/css'] = array(); // no longer a string!
            }
            
            //var_dump($style_declarations['text/css']);
            
            $new_scripts = array();
            $new_styles = array();
            
            // jQuery
            
            if ($this->_usejQuery) {
                
                // no conflict
                
                if ($this->params->get('removenoconflict' . $this->_suffix, 0)) {
                    
                    // remove all '...jQuery.noConflict(...);' or '... $.noConflict(...);'
                    
                    Helper::search_and_replace_noconflict(Helper::getRegularExpression('declaration', 'noconflict'), $script_declarations['text/javascript'], true, $this->_verbose_array);
                    
                    // remove potential jquery-noconflict.js (different combinations)
                    
                    $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'noconflict'), $scripts, $this->_verbose_array);
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTS', $number_removed);
                    }
                }
                
                // remove all references of the jQuery library except scripts to ignore
                
                $ignoreScripts = trim((string)$this->params->get('ignorescripts' . $this->_suffix, ''));
                if ($ignoreScripts) {
                    $ignoreScripts = array_map('trim', (array) explode("\n", $ignoreScripts));
                }
                
                $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'jquery'), $scripts, $this->_verbose_array, $ignoreScripts);
                
                if ($number_removed > 0) {
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERY', $number_removed);
                }
                
                // remove all references of Migrate scripts
                
                $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'migrate'), $scripts, $this->_verbose_array);
                
                if ($number_removed > 0) {
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDMIGRATE', $number_removed);
                }
                
                // jQuery UI
                
                if ($this->_usejQueryUI) {
                    
                    // remove all references of the jQuery UI library
                    
                    $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'jqueryui'), $scripts, $this->_verbose_array);
                    
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUI', $number_removed);
                    }
                    
                    // remove all references of the jQuery UI stylesheets
                    
                    $number_removed = Helper::search_and_delete('css', Helper::getRegularExpression('css', 'jqueryui'), $styles, $this->_verbose_array);
                    
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSS', $number_removed);
                    }
                    
                }
                
                // replace '$(document).ready(function()' or '$(document).ready(function($)' with 'jQuery(document).ready(function($)'
                
                if ($this->params->get('replacedocumentready' . $this->_suffix, 1)) {
                    
                    $number_replaced = Helper::search_and_replace('\$\(document\).ready\(function\([$]?\)', $script_declarations['text/javascript'], 'jQuery(document).ready(function($)');
                    
                    if ($number_replaced > 0) {
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REPLACEDDOCUMENTREADY', $number_replaced);
                    }
                }
                
            } // END if usejQuery
            
            // Bootstrap
            
            if ($this->_useBootstrap) {
                
                $bootstrap_library_types = $this->params->get('bootstraplibrarytypes' . $this->_suffix, 'both');
                
                // Bootstrap js path(s)
                
                if ($bootstrap_library_types == 'both' || $bootstrap_library_types == 'js') {
                    
                    // Popper js path
                    
                    if (!in_array($this->params->get('bootstrapversion' . $this->_suffix, 'joomla'), ['2.3.2', '3.0', '3.1', '3.2', '3.3', '3.4'])
                        && $this->params->get('popperversion' . $this->_suffix, 'none') !== 'none') {
                            
                            // remove loaded Popper scripts, if any
                            
                            $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'popper'), $scripts, $this->_verbose_array);
                            
                            if ($number_removed > 0) {
                                Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDPOPPERJS', $number_removed);
                            }
                        }
                        
                        // ignore the removal of some Bootstrap scripts
                        
                        $ignoreScripts = trim( (string) $this->params->get('ignorebootstrapscripts' . $this->_suffix, ''));
                        if ($ignoreScripts) {
                            $ignoreScripts = array_map('trim', (array) explode("\n", $ignoreScripts));
                        }
                        
                        // remove loaded Bootstrap scripts, if any
                        
                        $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'bootstrap'), $scripts, $this->_verbose_array, $ignoreScripts);
                        
                        if ($number_removed > 0) {
                            Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDBOOTSTRAPJS', $number_removed);
                        }
                }
                
                // Bootstrap css path(s)
                
                if ($bootstrap_library_types == 'both' || $bootstrap_library_types == 'css') {
                    
                    // ignore the removal of some Bootstrap stylesheets
                    
                    $ignoreStylesheets = trim( (string) $this->params->get('ignorebootstrapstylesheets' . $this->_suffix, ''));
                    if ($ignoreStylesheets) {
                        $ignoreStylesheets = array_map('trim', (array) explode("\n", $ignoreStylesheets));
                    }
                    
                    // remove loaded Bootstrap styles, if any
                    
                    $number_removed = Helper::search_and_delete('css', Helper::getRegularExpression('css', 'bootstrap'), $styles, $this->_verbose_array, $ignoreStylesheets);
                    
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDBOOTSTRAPCSS', $number_removed);
                    }
                }
                
            } // END if useBootstrap
            
            $remainingScripts = array();
            
            // remaining scripts from the plugin
            
            $remainingScriptsParam = trim( (string) $this->params->get('stripremainingscripts' . $this->_suffix, ''));
            if ($remainingScriptsParam) {
                $remainingScripts = array_map('trim', (array) explode("\n", $remainingScriptsParam));
            }
            
            // remove remaining scripts
            
            if (!empty($remainingScripts)) {
                foreach ($remainingScripts as $remainingScript) {
                    
                    $number_removed = Helper::search_and_delete('js', preg_quote($remainingScript, '/'), $scripts, $this->_verbose_array);
                    
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGSCRIPT', $remainingScript, $number_removed);
                    }
                }
            }
            
            $remainingStylesheets = array();
            
            // remaining styles from the plugin
            
            $remainingStylesheetsParam = trim( (string) $this->params->get('stripremainingcss' . $this->_suffix, ''));
            if ($remainingStylesheetsParam) {
                $remainingStylesheets = array_map('trim', (array) explode("\n", $remainingStylesheetsParam));
            }
            
            // remove remaining stylesheets
            
            if (!empty($remainingStylesheets)) {
                foreach ($remainingStylesheets as $remainingStylesheet) {
                    
                    $number_removed = Helper::search_and_delete('css', preg_quote($remainingStylesheet, '/'), $styles, $this->_verbose_array);
                    
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGCSS', $remainingStylesheet, $number_removed);
                    }
                }
            }
            
            // put back the API generated scripts and styles
            
            Factory::getDocument()->_scripts = array_merge($new_scripts, $scripts);
            //var_dump(Factory::getDocument()->_scripts);
            
            if (!empty($script_declarations['text/javascript'])) {
                
                Factory::getDocument()->_script['text/javascript'] = $script_declarations['text/javascript'];
                
                //var_dump(Factory::getDocument()->_script['text/javascript']);
            } else {
                // after removal of scripts, we may end up with nothing
                if (isset(Factory::getDocument()->_script['text/javascript'])) {
                    unset(Factory::getDocument()->_script['text/javascript']);
                }
            }
            
            Factory::getDocument()->_styleSheets = array_merge($new_styles, $styles);
            //var_dump(Factory::getDocument()->_styleSheets);
            
            if (!empty($style_declarations['text/css'])) {
                Factory::getDocument()->_style['text/css'] = $style_declarations['text/css'];
                
                //var_dump(Factory::getDocument()->_style['text/css']);
            }
        }
        
        // STEP 3 - ADD OR REPLACE
        // =======================
        
        $version = new Version();
        
        $wam = Factory::getApplication()->getDocument()->getWebAssetManager();
        
        // add all scripts and styles
        
        if ($this->_usejQuery) {
            
            if ($this->_jqpath) {
                
                if ($this->_jqpath === 'joomla') {
                    if ($wam->assetExists('script', 'jquery')) {
                        $asset = $wam->getAsset('script', 'jquery');
                        $wam->useScript('jquery');
                        
                        $this->_jqpath = $asset->getUri(); // needed to prevent removal in whole page scan
                        
                        Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERY', $this->_jqpath);
                    }
                } else {
                    $wam->registerAndUseScript('jquery', $this->_jqpath, Helper::isInternal($this->_jqpath) ? ['version' => $version->getMediaVersion()] : ['version' => '']); // add jquery with the new path
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERY', '<a href="' . $this->_jqpath.'" target="_blank">' . $this->_jqpath.'</a>');
                }
                
            } else {
                Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERY');
            }
            
            if ($this->_jqmigratepath) {
                
                if ($this->_jqmigratepath === 'joomla') {
                    if ($wam->assetExists('script', 'jquery-migrate')) {
                        $asset = $wam->getAsset('script', 'jquery-migrate');
                        $wam->useScript('jquery-migrate');
                        
                        $this->_jqmigratepath = $asset->getUri(); // needed to prevent removal in whole page scan
                        
                        Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYMIGRATE', $this->_jqmigratepath);
                    }
                } else {
                    $wam->registerAndUseScript('jquery-migrate', $this->_jqmigratepath, Helper::isInternal($this->_jqmigratepath) ? ['version' => $version->getMediaVersion()] : ['version' => ''], [], ['jquery']);
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYMIGRATE', '<a href="' . $this->_jqmigratepath.'" target="_blank">' . $this->_jqmigratepath.'</a>');
                }
            }
            
            if ($this->_jqnoconflictpath) {
                
                // use legacy asset, will probably be removed later
                if ($wam->assetExists('script', 'jquery-noconflict')) {
                    $asset = $wam->getAsset('script', 'jquery-noconflict');
                    $wam->useScript('jquery-noconflict');
                    
                    $this->_jqnoconflictpath = $asset->getUri(); // needed to prevent removal in whole page scan
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTSCRIPT', $this->_jqnoconflictpath);
                } else {
                    $wam->registerAndUseScript('jquery-noconflict', $this->_jqnoconflictpath, ['version' => $version->getMediaVersion()], [], ['jquery']);
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTSCRIPT', $this->_jqnoconflictpath);
                }
            }
            
            if ($this->_usejQueryUI) {
                
                if ($this->_jquipath) {
                    
                    $wam->registerAndUseScript('jquery.ui', $this->_jquipath, Helper::isInternal($this->_jquipath) ? ['version' => $version->getMediaVersion()] : ['version' => ''], [], ['jquery']); // add jquery ui with the new path
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUI', '<a href="' . $this->_jquipath.'" target="_blank">' . $this->_jquipath.'</a>');
                } else {
                    Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUI');
                }
                
                if ($this->_jquicsspath) {
                    
                    $wam->registerAndUseStyle('jquery.ui', $this->_jquicsspath, Helper::isInternal($this->_jquicsspath) ? ['version' => $version->getMediaVersion()] : ['version' => '']);
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUICSS', '<a href="' . $this->_jquicsspath.'" target="_blank">' . $this->_jquicsspath.'</a>');
                } else {
                    if ($this->params->get('jqueryuitheme' . $this->_suffix, 'none') != 'none') {
                        Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUICSS');
                    }
                }
            }
        }
        
        if ($this->_useBootstrap) {
            
            $bs_js_packages = array('alert', 'button', 'carousel', 'collapse', 'dropdown', 'modal', 'offcanvas', 'popover', 'scrollspy', 'tab', 'toast');
            $bs_css_packages = array('grid', 'reboot', 'utilities');
            
            $bootstrap_library_types = $this->params->get('bootstraplibrarytypes' . $this->_suffix, 'both');
            if ($bootstrap_library_types == 'both' || $bootstrap_library_types == 'js') {
                
                if ($bootstrap_library_types == 'js') { // remove Bootstrap css assets
                    
                    foreach ($bs_css_packages as $package) { // to do first because of dependencies
                        if ($wam->assetExists('style', 'bootstrap.css.' . $package)) {
                            $wam->disableAsset('style', 'bootstrap.css.' . $package);
                        }
                    }
                    
                    if ($wam->assetExists('style', 'bootstrap.css')) {
                        $wam->disableAsset('style', 'bootstrap.css');
                    }
                }
                
                if ($this->_popperpath) {
                    
                    $wam->registerAndUseScript('popper', $this->_popperpath, Helper::isInternal($this->_popperpath) ? ['version' => $version->getMediaVersion()] : ['version' => '']); // add popper with the new path
                    
                    Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDPOPPERJS', '<a href="' . $this->_popperpath.'" target="_blank">' . $this->_popperpath.'</a>');
                }
                
                if (!empty($this->_bootstrapjspath)) {
                    
                    if (isset($this->_bootstrapjspath['joomla'])) {
                        if ($wam->assetExists('script', 'bootstrap.es5')) {
                            $asset = $wam->getAsset('script', 'bootstrap.es5');
                            $wam->useScript('bootstrap.es5');
                            
                            $this->_bootstrapjspath['joomla'] = $asset->getUri();
                            
                            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDBOOTSTRAPJS', $this->_bootstrapjspath['joomla']);
                        }
                        
                        // add the js packages if specifically requested (keeps the ones that are already on the page) // TODO improve
                        
                        $bs_js_packages_selected = $this->params->get('bootstrapjspackages' . $this->_suffix, '');
                        if (!empty($bs_js_packages_selected) && is_array($bs_js_packages_selected)) {
                            foreach ($bs_js_packages_selected as $package) {
                                if ($wam->assetExists('script', 'bootstrap.' . $package)) {
                                    $wam->useScript('bootstrap.' . $package);
                                }
                            }
                        }
                    } else {
                        
                        $dependencies = array('core');
                        
                        // make sure, if jQuery is present, that Bootstrap is loaded after jQuery (even if it does not require jQuery) and Popper
                        if ($wam->assetExists('script', 'jquery')) {
                            if ($wam->isAssetActive('script', 'jquery')) {
                                $dependencies[] = 'jquery';
                            }
                        }
                        if ($wam->assetExists('script', 'jquery-noconflict')) {
                            if ($wam->isAssetActive('script', 'jquery-noconflict')) {
                                $dependencies[] = 'jquery-noconflict';
                            }
                        }
                        if ($wam->assetExists('script', 'jquery-migrate')) {
                            if ($wam->isAssetActive('script', 'jquery-migrate')) {
                                $dependencies[] = 'jquery-migrate';
                            }
                        }
                        if ($wam->assetExists('script', 'popper')) {
                            if ($wam->isAssetActive('script', 'popper')) {
                                $dependencies[] = 'popper';
                            }
                        }
                        
                        if (isset($this->_bootstrapjspath['cdn'])) {
                            
                            $wam->registerAndUseScript('bootstrap.es5', $this->_bootstrapjspath['cdn'], ['version' => ''], [], $dependencies); // add bootstrap with the new cdn path
                            
                            // remove Bootstrap packages, included in the CDN version already
                            
                            foreach ($bs_js_packages as $package) {
                                if ($wam->assetExists('script', 'bootstrap.' . $package)) {
                                    $wam->disableAsset('script', 'bootstrap.' . $package);
                                }
                            }
                            
                            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDBOOTSTRAPJS', '<a href="' . $this->_bootstrapjspath['cdn'].'" target="_blank">' . $this->_bootstrapjspath['cdn'].'</a>');
                            
                        } else { // local files
                            
                            // remove Bootstrap packages, included in the CDN version already
                            
                            foreach ($bs_js_packages as $package) { // to do first because of dependencies
                                if ($wam->assetExists('script', 'bootstrap.' . $package)) {
                                    $wam->disableAsset('script', 'bootstrap.' . $package);
                                }
                            }
                            
                            if ($wam->assetExists('script', 'bootstrap.es5')) {
                                $wam->disableAsset('script', 'bootstrap.es5');
                            }
                            
                            foreach ($this->_bootstrapjspath as $key => $bootstrapjspath) {
                                
                                $wam->registerAndUseScript('bootstrap.' . $key, $bootstrapjspath, ['version' => $version->getMediaVersion()], [], $dependencies);
                                
                                Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDBOOTSTRAPJS', '<a href="'.$bootstrapjspath.'" target="_blank">'.$bootstrapjspath.'</a>');
                            }
                        }
                    }
                } else {
                    Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGBOOTSTRAPJS');
                }
            }
            
            if ($bootstrap_library_types == 'both' || $bootstrap_library_types == 'css') {
                
                if ($bootstrap_library_types == 'css') { // remove Bootstrap js assets
                    
                    foreach ($bs_js_packages as $package) { // to do first because of dependencies
                        if ($wam->assetExists('script', 'bootstrap.' . $package)) {
                            $wam->disableAsset('script', 'bootstrap.' . $package);
                        }
                    }
                    
                    if ($wam->assetExists('script', 'bootstrap.es5')) {
                        $wam->disableAsset('script', 'bootstrap.es5');
                    }
                }
                
                if (!empty($this->_bootstrapcsspath)) {
                    
                    if (isset($this->_bootstrapcsspath['joomla'])) {
                        if ($wam->assetExists('style', 'bootstrap.css')) {
                            $asset = $wam->getAsset('style', 'bootstrap.css');
                            $wam->useStyle('bootstrap.css');
                            
                            $this->_bootstrapcsspath['joomla'] = $asset->getUri();
                            
                            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDBOOTSTRAPCSS', $this->_bootstrapcsspath['joomla']);
                        }
                    } else if (isset($this->_bootstrapcsspath['cdn'])) {
                        
                        // if grid/reboot/utilities exist, disable bootstrap.css asset
                        
                        $disable_asset = false;
                        foreach ($bs_css_packages as $package) {
                            if (isset($this->_bootstrapcsspath[$package])) {
                                
                                $wam->registerAndUseStyle('bootstrap.css.' . $package, $this->_bootstrapcsspath[$package], ['version' => '']);
                                
                                Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDBOOTSTRAPCSS', '<a href="' . $this->_bootstrapcsspath[$package].'" target="_blank">' . $this->_bootstrapcsspath[$package].'</a>');
                                
                                $disable_asset = true;
                            } else {
                                // disable that asset
                                
                                if ($wam->assetExists('style', 'bootstrap.css.' . $package)) {
                                    $wam->disableAsset('style', 'bootstrap.css.' . $package);
                                }
                            }
                        }
                        
                        if ($disable_asset) {
                            if ($wam->assetExists('style', 'bootstrap.css')) {
                                $wam->disableAsset('style', 'bootstrap.css'); // TODO check: may not work because grid is dependent in joomla assets file (error ?)
                            }
                        } else {
                            $wam->registerAndUseStyle('bootstrap.css', $this->_bootstrapcsspath['cdn'], ['version' => '']); // add bootstrap with the new cdn path
                            
                            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDBOOTSTRAPCSS', '<a href="' . $this->_bootstrapcsspath['cdn'].'" target="_blank">' . $this->_bootstrapcsspath['cdn'].'</a>');
                            
                            if (isset($this->_bootstrapcsspath['cdn_extra'])) {
                                $wam->registerAndUseStyle('bootstrap.css.extra', $this->_bootstrapcsspath['cdn_extra'], ['version' => '']);
                                
                                Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDBOOTSTRAPCSS', '<a href="' . $this->_bootstrapcsspath['cdn_extra'].'" target="_blank">' . $this->_bootstrapcsspath['cdn_extra'].'</a>');
                            }
                        }
                        
                    } else {
                        foreach ($this->_bootstrapcsspath as $key => $bootstrapcsspath) {
                            
                            $wam->registerAndUseStyle('bootstrap.css.' . $key, $bootstrapcsspath, ['version' => $version->getMediaVersion()]);
                            
                            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDBOOTSTRAPCSS', '<a href="'.$bootstrapcsspath.'" target="_blank">'.$bootstrapcsspath.'</a>');
                        }
                    }
                } else {
                    Helper::report($this->_verbose_array, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGBOOTSTRAPCSS');
                }
            }
        }
        
        // add all scripts
        
        foreach($this->_supplement_scripts as $i => $path) {
            
            $wam->registerAndUseScript('jqe-script.js.' . $i, $path, Helper::isInternal($path) ? ['version' => $version->getMediaVersion()] : ['version' => '']);
            
            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPT', $path);
        }
        
        // add all styles
        
        foreach($this->_supplement_stylesheets as $i => $path) {
            
            $wam->registerAndUseStyle('jqe-style.css.' . $i, $path, Helper::isInternal($path) ? ['version' => $version->getMediaVersion()] : ['version' => '']);
            
            Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEET', $path);
        }
        
        // add all script declarations
        
        $javascript_declaration = trim( (string) $this->params->get('addjavascriptdeclaration' . $this->_suffix, ''));
        if (!empty($javascript_declaration)) {
            
            $wam->addInlineScript($javascript_declaration);
            
            if ($this->_showreport) {
                $lines = array_map('trim', (array) explode("\n", $javascript_declaration));
                Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPTDECLARATION', $lines[0]);
            }
        }
        
        // add all style declarations
        
        $css_declaration = trim( (string) $this->params->get('addcssdeclaration' . $this->_suffix, ''));
        if (!empty($css_declaration)) {
            
            $wam->addInlineStyle($css_declaration);
            
            if ($this->_showreport) {
                $lines = array_map('trim', (array) explode("\n", $css_declaration));
                Helper::report($this->_verbose_array, 'added', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEETDECLARATION', $lines[0]);
            }
        }
        
        $time_end = microtime(true);
        $this->_timebeforecompilehead = $time_end - $time_start;
    }
    
    public function onAfterRender()
    {
        if (!$this->isEnabledOnPage()) {
            return;
        }
        
        $this->loadLanguage();
        
        // timing starts
        
        $time_start = microtime(true);
        
        // scan the whole document page
        
        $pagescan = (int)$this->params->get('pagescan', 0);
        
        if ($pagescan > 0) {
            
            switch ($pagescan)
            {
                case 1: // head only
                    
                    preg_match('/<head>([\s\S]*)<\/head>/s', $this->app->getBody(), $match);
                    $body = $match[0]; // keep the tags
                    
                    break;
                    
                case 2: // API + body
                    
                    preg_match('/<body.*?>([\s\S]*)<\/body>/s', $this->app->getBody(), $match);
                    $body = $match[0]; // keep the tags
                    
                    break;
                    
                default: // whole page scan
                    
                    $body = $this->app->getBody();
            }
            
            $remove_empty_scripts = false;
            $remove_empty_links = false;
            
            // jQuery
            
            if ($this->_usejQuery) {
                
                // no conflict
                
                if ($this->params->get('removenoconflict' . $this->_suffix, 0)) {
                    
                    // remove all '...jQuery.noConflict(...);' or '... $.noConflict(...);'
                    
                    Helper::search_and_replace_noconflict(Helper::getRegularExpression('declaration', 'noconflict'), $body, true, $this->_verbose_array);
                    
                    // remove potential jquery-noconflict.js (different combinations)
                    // ignores the script added by the plugin
                    
                    $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'noconflict'), $body, $this->_verbose_array, [$this->_jqnoconflictpath]);
                    
                    if ($number_removed > 0) {
                        $remove_empty_scripts = true;
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTS', $number_removed);
                    }
                }
                
                // remove all references of the jQuery library except scripts to ignore
                
                $ignoreScripts = trim( (string) $this->params->get('ignorescripts' . $this->_suffix, ''));
                if ($ignoreScripts) {
                    $ignoreScripts = array_map('trim', (array) explode("\n", $ignoreScripts));
                    $ignoreScripts[] = $this->_jqpath;
                } else {
                    $ignoreScripts = array($this->_jqpath);
                }
                
                $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'jquery'), $body, $this->_verbose_array, $ignoreScripts);
                
                if ($number_removed > 0) {
                    $remove_empty_scripts = true;
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERY', $number_removed);
                }
                
                // remove all references of Migrate scripts
                
                $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'migrate'), $body, $this->_verbose_array, [$this->_jqmigratepath]);
                
                if ($number_removed > 0) {
                    $remove_empty_scripts = true;
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDMIGRATE', $number_removed);
                }
                
                // replace '$(document).ready(function()' or '$(document).ready(function($)' with 'jQuery(document).ready(function($)'
                
                if ($this->params->get('replacedocumentready' . $this->_suffix, 1)) {
                    
                    $number_replaced = Helper::search_and_replace('\$\(document\).ready\(function\([$]?\)', $body, 'jQuery(document).ready(function($)');
                    
                    if ($number_replaced > 0) {
                        Helper::report($this->_verbose_array, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REPLACEDDOCUMENTREADY', $number_replaced);
                    }
                }
                
                // jQuery UI
                
                if ($this->_usejQueryUI) {
                    
                    // remove all references of the jQuery UI library
                    
                    $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'jqueryui'), $body, $this->_verbose_array, [$this->_jquipath]);
                    
                    if ($number_removed > 0) {
                        $remove_empty_scripts = true;
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUI', $number_removed);
                    }
                    
                    // remove all references of the jQuery UI stylesheets
                    
                    $number_removed = Helper::search_and_delete('css', Helper::getRegularExpression('css', 'jqueryui'), $body, $this->_verbose_array, [$this->_jquicsspath]);
                    
                    if ($number_removed > 0) {
                        $remove_empty_links = true;
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSS', $number_removed);
                    }
                }
            }
            
            // Bootstrap
            
            if ($this->_useBootstrap) {
                
                $bootstrap_library_types = $this->params->get('bootstraplibrarytypes' . $this->_suffix, 'both');
                
                // Bootstrap js path(s)
                
                if ($bootstrap_library_types == 'both' || $bootstrap_library_types == 'js') {
                    
                    // Popper js path
                    
                    if (!in_array($this->params->get('bootstrapversion' . $this->_suffix, 'joomla'), ['2.3.2', '3.0', '3.1', '3.2', '3.3', '3.4'])
                        && $this->params->get('popperversion' . $this->_suffix, 'none') !== 'none') {
                            
                            // remove loaded Popper scripts, if any
                            
                            $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'popper'), $body, $this->_verbose_array, [$this->_popperpath]);
                            
                            if ($number_removed > 0) {
                                $remove_empty_scripts = true;
                                Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDPOPPERJS', $number_removed);
                            }
                        }
                        
                        // ignore the removal of some Bootstrap scripts
                        
                        $ignoreScripts = trim( (string) $this->params->get('ignorebootstrapscripts' . $this->_suffix, ''));
                        if ($ignoreScripts) {
                            $ignoreScripts = array_map('trim', (array) explode("\n", $ignoreScripts));
                            $ignoreScripts = array_merge($this->_bootstrapjspath, $ignoreScripts);
                        } else {
                            $ignoreScripts = $this->_bootstrapjspath;
                        }
                        
                        // remove loaded Bootstrap scripts, if any
                        
                        $number_removed = Helper::search_and_delete('js', Helper::getRegularExpression('js', 'bootstrap'), $body, $this->_verbose_array, $ignoreScripts);
                        
                        if ($number_removed > 0) {
                            $remove_empty_scripts = true;
                            Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDBOOTSTRAPJS', $number_removed);
                        }
                }
                
                // Bootstrap css path(s)
                
                if ($bootstrap_library_types == 'both' || $bootstrap_library_types == 'css') {
                    
                    // ignore the removal of some Bootstrap stylesheets
                    
                    $ignoreStylesheets = trim( (string) $this->params->get('ignorebootstrapstylesheets' . $this->_suffix, ''));
                    if ($ignoreStylesheets) {
                        $ignoreStylesheets = array_map('trim', (array) explode("\n", $ignoreStylesheets));
                        $ignoreStylesheets = array_merge($this->_bootstrapcsspath, $ignoreStylesheets);
                    } else {
                        $ignoreStylesheets = $this->_bootstrapcsspath;
                    }
                    
                    // remove loaded Bootstrap styles, if any
                    
                    $number_removed = Helper::search_and_delete('css', Helper::getRegularExpression('css', 'bootstrap'), $body, $this->_verbose_array, $ignoreStylesheets);
                    
                    if ($number_removed > 0) {
                        $remove_empty_links = true;
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDBOOTSTRAPCSS', $number_removed);
                    }
                }
            }
            
            // remaining scripts
            
            $remainingScripts = array();
            
            // remaining scripts from the plugin
            $remainingScriptsParam = trim( (string) $this->params->get('stripremainingscripts' . $this->_suffix, ''));
            if ($remainingScriptsParam) {
                $remainingScripts = array_map('trim', (array) explode("\n", $remainingScriptsParam));
            }
            
            // remove remaining scripts
            
            if (!empty($remainingScripts)) {
                foreach ($remainingScripts as $remainingScript) {
                    
                    $number_removed = Helper::search_and_delete('js', '(.*?)' . preg_quote($remainingScript, '/') . '(.*?)', $body, $this->_verbose_array);
                    
                    if ($number_removed > 0) {
                        $remove_empty_scripts = true;
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGSCRIPT', $remainingScript, $number_removed);
                    }
                }
            }
            
            // remaining styles
            
            $remainingStylesheets = array();
            
            // remaining styles from the plugin
            $remainingStylesheetsParam = trim( (string) $this->params->get('stripremainingcss' . $this->_suffix, ''));
            if ($remainingStylesheetsParam) {
                $remainingStylesheets = array_map('trim', (array) explode("\n", $remainingStylesheetsParam));
            }
            
            // remove remaining styles
            
            if (!empty($remainingStylesheets)) {
                foreach ($remainingStylesheets as $remainingStylesheet) {
                    
                    $number_removed = Helper::search_and_delete('css', '(.*?)' . preg_quote($remainingStylesheet, '/') . '(.*?)', $body, $this->_verbose_array);
                    
                    if ($number_removed > 0) {
                        $remove_empty_links = true;
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGCSS', $remainingStylesheet, $number_removed);
                    }
                }
            }
            
            // remove all obsolete script tags
            
            if ($remove_empty_scripts) {
                
                $number_removed = Helper::search_and_replace('<script[^>]*GARBAGE[^>]*><\/script>', $body, '');
                
                if ($number_removed > 0) {
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYSCRIPTTAGS', $number_removed);
                }
            }
            
            // remove all obsolete link tags
            
            if ($remove_empty_links) {
                
                $number_removed = Helper::search_and_replace('<link[^>]*GARBAGE[^>]*\/>', $body, '');
                
                if ($number_removed > 0) {
                    Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYLINKTAGS', $number_removed);
                }
            }
            
            switch ($pagescan)
            {
                case 1: // head only
                    
                    // Remove blank lines
                    // gets all of the empty lines in the source and replaces them with a simple carriage return to preserve the content structure
                    
                    $number_removed = Helper::search_and_replace('(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+', $body, "\n");
                    
                    if ($number_removed > 0) {
                        Helper::report($this->_verbose_array, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEBLANKLINES', $number_removed);
                    }
                    
                    $this->app->setBody(preg_replace('#<head>([\s\S]*)<\/head>#', $body, $this->app->getBody(), 1));
                    
                    break;
                    
                case 2: // API + body
                    
                    $this->app->setBody(preg_replace('#<body.*?>([\s\S]*)<\/body>#', $body, $this->app->getBody(), 1));
                    
                    break;
                    
                default: // whole page scan
                    
                    // TODO? get the head section and remove blank lines
                    
                    $this->app->setBody($body);
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
            
            $report = Helper::getReport($this->_verbose_array, $this->_timebeforecompilehead + $this->_timeafterrender, '', $this_show_in_modal);
            
            $this->app->setBody(preg_replace('#</body>#', $report.'</body>', $this->app->getBody(), 1));
        }
        
        return true;
    }
    
    protected function isEnabledOnPage()
    {
        if (!$this->app->isClient('site')) {
            return false;
        }

        // enable the plugin for HTML pages only
        if (Factory::getDocument()->getType() !== 'html') {
            return false;
        }
        
        if ($this->app->getTemplate() === 'system') {
            return false;
        }
        
        // template selection
        
        $templates_inex = $this->params->get('template_inex' . $this->_suffix, '');
        
        if ($templates_inex !== '') {
            
            $templates = self::getParamValues($this->params->get('templateid' . $this->_suffix, array()));
            
            if ($templates) {
                
                if ((int)$templates_inex === 1) { // include : use the plugin if in template
                    
                    if (!in_array($this->app->getTemplate(true)->id, $templates)) {
                        return false;
                    }
                } else { // exclude : plugin is excluded if in template
                    
                    if (in_array($this->app->getTemplate(true)->id, $templates)) {
                        return false;
                    }
                }
            }
        }
        
        // component selection
        
        $components_inex = $this->params->get('wherecomponent_inex' . $this->_suffix, '');
        
        if ($components_inex !== '') {
            
            $components = self::getParamValues($this->params->get('wherecomponent' . $this->_suffix, array()));
            
            if ($components) {
                
                if ((int)$components_inex === 1) { // include : use the plugin if on extension's page
                    
                    if (!in_array($this->app->input->get('option', ''), $components)) {
                        return false;
                    }
                } else { // exclude : plugin is excluded if on extension's page
                    
                    if (in_array($this->app->input->get('option', ''), $components)) {
                        return false;
                    }
                }
            }
        }
        
        // page selection
        
        $urls_inex = $this->params->get('url_inex' . $this->_suffix, '');
        
        if ($urls_inex !== '') {
            
            $url_paths = trim( (string) $this->params->get('url_inex_items' . $this->_suffix, ''));
            
            if ($url_paths) {
                
                if ((int)$urls_inex === 1) { // include : use the plugin if on a page
                    
                    $paths = array_map('trim', (array) explode("\n", $url_paths));
                    
                    foreach ($paths as $path) {
                        if (self::paths_are_identical(Uri::current(), $path)) {
                            
                            return true;
                        }
                    }
                    
                    return false;
                    
                } else { // exclude: plugin is excluded if on a page
                    
                    $paths = array_map('trim', (array) explode("\n", $url_paths));
                    
                    foreach ($paths as $path) {
                        if (self::paths_are_identical(Uri::current(), $path)) {
                            
                            return false;
                        }
                    }
                }
            }
        }
        
        return true;
    }
}
