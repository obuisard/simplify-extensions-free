<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\JqueryEasy\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class Helper
{
    static public function isEnabledOnPage($params, $suffix = '')
    {
        // enable the plugin for HTML pages only
        if (Factory::getDocument()->getType() !== 'html') {
            return false;
        }
        
        // disable plugin in selected templates
        
//         $templates_array = $params->get('templateid', array('none'));
        
//         if (!is_array($templates_array)) { // before the plugin is saved, the value is the string 'none'
//             $templates_array = explode(' ', $templates_array);
//         }
        
//         $array_of_template_values = array_count_values($templates_array);
//         if (isset($array_of_template_values['none']) && $array_of_template_values['none'] > 0) { // 'none' was selected
//             // keep the plugin enabled
//         } else {
//             if (Factory::getApplication()->getTemplate() !== 'system') {
//                 if (in_array(Factory::getApplication()->getTemplate(true)->id, $templates_array)) {
//                     return false;
//                 }
//             }
//         }

        $urls = $params->get('url_inex'.$suffix, '');
        
        if ($urls === '') {
            return true;
        }
        
        $url_paths = trim( (string) $params->get('url_inex_items'.$suffix, ''));
        
        // enable plugin only on the allowed pages
        if ($url_paths && $urls === 1) {
            
            $paths = array_map('trim', (array) explode("\n", $url_paths));
            
            $found = false;
            foreach ($paths as $path) {
                $paths_compare = self::paths_are_identical(Uri::current(), $path);
                if ($paths_compare) {
                    $found = true;
                }
            }
            if (!$found) {
                return false;
            }
        }
        
        // disable plugin in the listed pages
        if ($url_paths && $urls === 0) {
            
            $paths = array_map('trim', (array) explode("\n", $url_paths));
            
            foreach ($paths as $path) {
                $paths_compare = self::paths_are_identical(Uri::current(), $path);
                if ($paths_compare) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    static public function getRegularExpression($type, $name)
    {
        switch ($name . '_' . $type) {
            case 'jquery_js': return '([\\/a-zA-Z0-9_:\.~-]*)jquery([0-9\.-]|latest|core|min|pack)*?.js(.*?)';
            case 'jqueryui_js': return '([\\/a-zA-Z0-9_:\.~-]*)jquery[.-]*ui([0-9\.-]|latest|core|custom|min|pack)*?.js(.*?)';
            case 'noconflict_js': return '([\\/a-zA-Z0-9_:\.~-]*)jquery[.-]*no[.-]*[cC]onflict([0-9\.-]|min)*?.js(.*?)';
            case 'migrate_js': return '([\\/a-zA-Z0-9_:\.~-]*)jquery([0-9\.-])*?migrate([0-9\.-]|latest|core|min|pack)*?.js(.*?)';
            case 'popper_js': return '([\\/a-zA-Z0-9_:\.~-]*)popper([0-9\.-]|min)*?js(.*?)';
            case 'bootstrap_js': return '([\\/a-zA-Z0-9_:\.~-]*)bootstrap([0-9\.-]|bundle|min)*?js(.*?)';
            
            case 'jqueryui_css': return '([\\/a-zA-Z0-9_:\.~-]*)jquery[.-]*ui([0-9\.-]|latest|core|custom|min|pack)*?.css(.*?)';
            case 'bootstrap_css': return '([\\/a-zA-Z0-9_:\.~-]*)bootstrap([a-zA-Z0-9\.-]|min)*?css(.*?)';
            
            case 'noconflict_declaration': return '[^};\n>]*(jQuery|\$)\.no[cC]onflict\(\s*(true|false|)\s*\);';
        }
        
        return $regexp;
    }
    
    static public function getURL($cdn, $name, $protocole, $version, $extra = '')
    {
        switch ($name) {
            case 'jquery_js':
                if ($cdn == 'google') {
                    return $protocole.'//ajax.googleapis.com/ajax/libs/jquery/'.$version.'/jquery'.$extra.'.js';
                } else if ($cdn == 'cloudflare') {
                    return $protocole.'//cdnjs.cloudflare.com/ajax/libs/jquery/'.$version.'/jquery'.$extra.'.js';
                } else if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/jquery/jquery-'.$version.$extra.'.js';
                }
                return $protocole.'//code.jquery.com/jquery-'.$version.$extra.'.js';
                
            case 'migrate':
                if ($cdn == 'cloudflare') {
                    return $protocole.'//cdnjs.cloudflare.com/ajax/libs/jquery-migrate/'.$version.'/jquery-migrate'.$extra.'.js';
                } else if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/jquery.migrate/jquery-migrate-'.$version.$extra.'.js';
                }
                return $protocole.'//code.jquery.com/jquery-migrate-'.$version.$extra.'.js';
                
            case 'mobile_js':
                if ($cdn == 'cloudflare') {
                    return $protocole.'//cdnjs.cloudflare.com/ajax/libs/jquery-mobile/'.$version.'/jquery.mobile'.$extra.'.js';
                } else if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/jquery.mobile/'.$version.'/jquery.mobile-'.$version.$extra.'.js';
                }
                return $protocole.'//code.jquery.com/mobile/'.$version.'/jquery.mobile-'.$version.$extra.'.js';
                
            case 'mobile_default_css':
                if ($cdn == 'cloudflare') {
                    return $protocole.'//cdnjs.cloudflare.com/ajax/libs/jquery-mobile/'.$version.'/jquery.mobile'.$extra.'.css';
                } else if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/jquery.mobile/'.$version.'/jquery.mobile-'.$version.$extra.'.css';
                }
                return $protocole.'//code.jquery.com/mobile/'.$version.'/jquery.mobile-'.$version.$extra.'.css';
                
            case 'mobile_css':
                if ($cdn == 'cloudflare') {
                    return $protocole.'//cdnjs.cloudflare.com/ajax/libs/jquery-mobile/'.$version.'/jquery.mobile.structure'.$extra.'.css';
                } else if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/jquery.mobile/'.$version.'/jquery.mobile.structure-'.$version.$extra.'.css';
                }
                return $protocole.'//code.jquery.com/mobile/'.$version.'/jquery.mobile.structure-'.$version.$extra.'.css';
                
            case 'jqueryui_js':
                if ($cdn == 'google') {
                    return $protocole.'//ajax.googleapis.com/ajax/libs/jqueryui/'.$version.'/jquery-ui'.$extra.'.js';
                } else if ($cdn == 'cloudflare') {
                    return $protocole.'//cdnjs.cloudflare.com/ajax/libs/jqueryui/'.$version.'/jquery-ui'.$extra.'.js';
                } else if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/jquery.ui/'.$version.'/jquery-ui'.$extra.'.js';
                }
                return $protocole.'//code.jquery.com/ui/'.$version.'/jquery-ui'.$extra.'.js';
                
            case 'jqueryui_css':
                if ($cdn == 'google') {
                    return $protocole.'//ajax.googleapis.com/ajax/libs/jqueryui/'.$version.'/themes/'.$extra.'/jquery-ui.css';
                } else if ($cdn == 'cloudflare') {
                    return $protocole.'//cdnjs.cloudflare.com/ajax/libs/jqueryui/'.$version.'/themes/'.$extra.'/jquery-ui.css';
                } else if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/jquery.ui/'.$version.'/themes/'.$extra.'/jquery-ui.css';
                }
                return $protocole.'//code.jquery.com/ui/'.$version.'/themes/'.$extra.'/jquery-ui.css';
                
            case 'bootstrap_js':
                if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/bootstrap/'.$version.'/bootstrap'.$extra.'.js';
                }
                return $protocole.'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/'.$version.'/js/bootstrap'.$extra.'.js';
                
            case 'bootstrap_css':
                if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/bootstrap/'.$version.'/css/bootstrap'.$extra.'.css';
                }
                return $protocole.'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/'.$version.'/css/bootstrap'.$extra.'.css';
                
            case 'bootstrap_responsive_css':
                if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/bootstrap/'.$version.'/css/bootstrap-responsive'.$extra.'.css';
                }
                return $protocole.'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/'.$version.'/css/bootstrap-responsive'.$extra.'.css';
                
            case 'bootstrap_theme_css':
                if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/bootstrap/'.$version.'/css/bootstrap-theme'.$extra.'.css';
                }
                return $protocole.'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/'.$version.'/css/bootstrap-theme'.$extra.'.css';
                
            case 'bootstrap_grid_css':
                if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/bootstrap/'.$version.'/css/bootstrap-grid'.$extra.'.css';
                }
                return $protocole.'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/'.$version.'/css/bootstrap-grid'.$extra.'.css';
                
            case 'bootstrap_reboot_css':
                if ($cdn == 'microsoft') {
                    return $protocole.'//ajax.aspnetcdn.com/ajax/bootstrap/'.$version.'/css/bootstrap-reboot'.$extra.'.css';
                }
                return $protocole.'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/'.$version.'/css/bootstrap-reboot'.$extra.'.css';

            case 'bootstrap_utilities_css':
            	if ($cdn == 'microsoft') {
            		return $protocole.'//ajax.aspnetcdn.com/ajax/bootstrap/'.$version.'/css/bootstrap-utilities'.$extra.'.css';
        		}
            	return $protocole.'//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/'.$version.'/css/bootstrap-utilities'.$extra.'.css';
        }
        
        return '';
    }
    
    static public function getAdditionalScripts($script_param)
    {
        $script_paths = array();
        
        $js = trim((string) $script_param);
        if ($js) {
            return array_map('trim', (array) explode("\n", $js));
        }
        
        return $script_paths;
    }
    
    static public function getAdditionalStylesheets($style_param)
    {
        $stylesheet_paths = array();
        
        $css = trim((string) $style_param);
        if ($css) {
            return array_map('trim', (array) explode("\n", $css));
        }
        
        return $stylesheet_paths;
    }
    
    static public function paths_are_identical($url, $path)
    {
        $first_pos = (strpos($path, '*') === 0) ? true: false;
        $last_pos = (strrpos($path, '*') === (strlen($path) - 1)) ? true: false;
        
        if (Factory::getConfig()->get('unicodeslugs') == 1) {
            $url = urldecode($url);
        }
        
        if ($first_pos && $last_pos) { // any URL containing $path
            $path = trim($path, '*');
            if (stripos($url, $path) !== false) {
                return true;
            }
        } else if ($first_pos && !$last_pos) { // any URL ending with $path
            $path = ltrim($path, '*');
            $path_length = strlen($path);
            $url_tip = substr($url, -$path_length);
            if (strcasecmp($url_tip, $path) == 0) { // compare end of URI with $path
                return true;
            }
        } else if (!$first_pos && $last_pos) { // any URL starting with $path
            $path = rtrim($path, '*');
            $url = str_replace('index.php/', '', $url);
            $path = str_replace('index.php/', '', $path);
            if (stripos($url, Uri::root().ltrim($path, '/')) !== false) {
                return true;
            }
        } else {
            $url = str_replace('index.php/', '', $url);
            $path = str_replace('index.php/', '', $path);
            if (strcasecmp($url, Uri::root().ltrim($path, '/')) == 0) { // case-insensitive string comparison
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * search through array of strings BUT remove the only part that matches, not the whole string
     *
     * @param string $regexp
     * @param array|string $container
     * @param string $replace
     */
    static public function search_and_replace($regexp, &$container, $replace = '')
    {
        $total_count = 0;
        
        if (is_array($container)) {
            foreach ($container as $key => $value) {
                $value = preg_replace('/' . $regexp . '/', $replace, $value, -1, $count);
                $total_count += $count;
                if (trim($value) == '') {
                    unset($container[$key]);
                    continue;
                }
                $container[$key] = $value;
            }
        } else {
            $container = preg_replace('/' . $regexp . '/', $replace, $container, -1, $count);
            $total_count += $count;
        }
        
        return $total_count;
    }
    
    /**
     * specific search for noconflict code
     * 
     * @param string $regexp
     * @param array|string $container
     * @param boolean $keep_var
     * @param array $verbose
     */
    static public function search_and_replace_noconflict($regexp, &$container, $keep_var, &$verbose)
    {
        if (is_array($container)) {
            foreach ($container as $key => $value) {
                
                $matches = array();
                if (preg_match_all('/' . $regexp . '/', $value, $matches, PREG_SET_ORDER) > 0) {
                    foreach ($matches as $match) {
                        $quoted_match = preg_quote($match[0]); // prepares for regexp
                        if (!$keep_var) { // variable declarations included
                            $value = preg_replace('/' . $quoted_match . '/', '', $value, 1);
                            self::report($verbose, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTDECLARATIONS', $match[0]);
                        } else { // ignore the removal of variable declaration (keep var|let|const j = $.noConflict(); BUT replace $)
                            if (preg_match('/(.*)=/i', $match[0])) {
                                if (strpos($match[0], '$') !== false) {
                                    $match[0] = str_replace('$.', 'jQuery.', $match[0]);
                                    $value = preg_replace('/' . $quoted_match . '/', $match[0], $value, 1);
                                    self::report($verbose, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEPTANDFIXEDNOCONFLICTSCRIPTDECLARATION', $match[0]);
                                } else {
                                    self::report($verbose, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEPTNOCONFLICTSCRIPTDECLARATION', $match[0]);
                                }
                            } else {
                                $value = preg_replace('/' . $quoted_match . '/', '', $value, 1);
                                self::report($verbose, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTDECLARATIONS', $match[0]);
                            }
                        }
                    }
                }
                
                if (trim($value) == '') {
                    unset($container[$key]);
                    continue;
                }
                $container[$key] = $value;
            }
        } else {
            $matches = array();
            if (preg_match_all('#'.$regexp.'#', $container, $matches, PREG_SET_ORDER) > 0) {
                
                $number_of_deletions = 0;
                
                foreach ($matches as $match) {                    
                    $quoted_match = preg_quote($match[0], '#'); // prepares for regexp
                    if (!$keep_var) { // variable declarations included
                        $container = preg_replace('#'.$quoted_match.'#', '', $container, 1);
                        self::report($verbose, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTDECLARATIONS', $match[0]);
                        $number_of_deletions++;
                    } else { // ignore the removal if variable declaration (keep var|let|const j = $.noConflict(); BUT replace $)
                        if (preg_match('/(.*)=/i', $match[0])) {
                            if (strpos($match[0], '$') !== false) {
                                $match[0] = str_replace('$.', 'jQuery.', $match[0]);
                                $container = preg_replace('#' . $quoted_match . '#', $match[0], $container, 1);
                                self::report($verbose, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEPTANDFIXEDNOCONFLICTSCRIPTDECLARATION', $match[0]);
                            } else {
                                self::report($verbose, 'info', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_KEPTNOCONFLICTSCRIPTDECLARATION', $match[0]);
                            }
                        } else {
                            $container = preg_replace('#' . $quoted_match . '#', '', $container, 1);
                            self::report($verbose, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTDECLARATIONS', $match[0]);
                            $number_of_deletions++;
                        }
                    }
                }
                
                // TODO make sure javascript does not need to be quoted
                if ($number_of_deletions > 0) {
                    $count = 0;
                    $container = preg_replace('#<script type="text/javascript">[\s]*?</script>#', '', $container, -1, $count); // remove newly empty scripts, if any
                    if ($count > 0) {
                        self::report($verbose, 'deleted', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYSCRIPTTAGS', $count);
                    }
                }
            }
        }
    }
    
    /**
     *  Remove all occurences of a script or a stylesheet
     *  returns
     *  - an array of removed items if requesting results
     *  - the removal count otherwise
     **/
    static public function search_and_delete($type, $regexp, &$container, &$verbose = null, $ignore_files = array(), $request_results = false)
    {
        $removed = array();
        $num_removed = 0;
        
        if (is_array($container)) {
            
            $results = preg_grep('/' . $regexp . '/', array_keys($container));
            
            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!empty($ignore_files)) {
                        $ignore = false;
                        foreach ($ignore_files as $ignore_file) {
                            if (stripos($result, $ignore_file) !== false) { // library needs to be ignored from removal
                                $ignore = true;
                                if (!is_null($verbose)) {
                                    $verbose[] = array('info', Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_IGNORE' . ($type == 'js' ? 'SCRIPT' : 'STYLESHEET'), $ignore_file));
                                }
                                break;
                            }
                        }
                        if (!$ignore) {
                            unset($container[$result]);
                            $num_removed++;
                            $removed[] = $result;
                        }
                    } else {
                        unset($container[$result]);
                        $num_removed++;
                        $removed[] = $result;
                    }
                }
            }
            
        } else {
            
            $regexp = ($type == 'js' ? 'src="' : 'href="') . $regexp . '"';
            
            if (empty($ignore_files) && !$request_results) {
                $container = preg_replace('#'.$regexp.'#', 'GARBAGE', $container, -1, $num_removed);
            } else {
                
                // use so that if a file to ignore is found multiple times, it will keep the first occurence and remove the other ones
                
                $ignore_file_count = array();
                foreach ($ignore_files as $ignore_file) {
                    $ignore_file_count[$ignore_file] = 0;
                }                
                
                $container = preg_replace_callback('#'.$regexp.'#', function ($matches) use ($ignore_files, &$ignore_file_count, &$verbose, $type, &$num_removed, &$removed) { 
                    
                    $ignore = false;
                    foreach ($ignore_files as $ignore_file) {
                        
                        if (stripos($matches[0], $ignore_file) !== false && $ignore_file_count[$ignore_file] < 1) { // library needs to be ignored for removal
                            $ignore = true;
                            
                            $ignore_file_count[$ignore_file]++;
                            
                            if (!is_null($verbose)) {
                                $verbose[] = array('info', Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_IGNORE' . ($type == 'js' ? 'SCRIPT' : 'STYLESHEET'), $ignore_file));
                            }
                            break;
                        }
                    }
                    
                    if ($ignore) {
                        return $matches[0];
                    }
                    
                    $num_removed++;
                    $removed[] = ($type == 'js') ? rtrim(substr($matches[0], 5), '"') : rtrim(substr($matches[0], 6), '"');
                    
                    return 'GARBAGE';
                    
                }, $container, -1);
                
//                 $matches = array();
//                 if (preg_match_all('#'.$regexp.'#', $container, $matches, PREG_SET_ORDER) >= 0) {
//                     foreach ($matches as $match) {
//                         $quoted_match = preg_quote($match[0], '/'); // prepares for regexp
//                         $ignore = false;
                        
                        
//                         $verbose[] = array('error', $match[0]);
                        
//                         foreach ($ignore_files as $ignore_file) {                       
                            
//                             if (stripos($match[0], $ignore_file) !== false && $ignore_file_count[$ignore_file] < 1) { // library needs to be ignored for removal
//                                 $ignore = true;
                                
//                                 $ignore_file_count[$ignore_file]++; 
                                
//                                 if (!is_null($verbose)) {
//                                     $verbose[] = array('info', Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_IGNORE' . ($type == 'js' ? 'SCRIPT' : 'STYLESHEET'), $ignore_file));
//                                 }
//                                 break;
//                             }
//                         }
//                         if (!$ignore) { // remove the library
//                             $container = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $container, 1);
//                             $num_removed++;
//                             $removed[] = ($type == 'js') ? rtrim(substr($match[0], 5), '"') : rtrim(substr($match[0], 6), '"');
//                         }
//                     }
//                 }
            }
        }
        
        if ($request_results) {
            return $removed;
        }
        
        return $num_removed;
    }
    
    static public function report(&$verbose, $type, $message, $parameter_1 = null, $parameter_2 = null)
    {
        if (!is_null($verbose)) {
            if (isset($parameter_1) && isset($parameter_2)) {
                $verbose[] = array($type, Text::sprintf($message, $parameter_1, $parameter_2));
            } else if (isset($parameter_1) || isset($parameter_2)) {
                $parameter = $parameter_1 ? $parameter_1 : $parameter_2;
                $verbose[] = array($type, Text::sprintf($message, $parameter));
            } else {
                $verbose[] = array($type, Text::_($message));
            }
        }
    }
    
    static public function getReport($comments = array(), $execution_time = 0, $title = '', $as_modal = true)
    {
        $replacement = array();
        
        $replacement[] = '<style type="text/css"> ';
        
        if ($as_modal) {
            $replacement[] = '#jqe_report_overlay { z-index: 9000; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(130, 130, 130, 0.6); } ';
            $replacement[] = '#jqe_report_min { z-index: 10000; display: none; overflow: hidden; position: fixed; top: 10px; right: 10px; padding: 10px; font-family: Arial, sans-serif; font-size: 12px; } ';
            $replacement[] = '#jqe_report { z-index: 10000; display: block; overflow: hidden; position: fixed; top: 10px; left: 0; right: 0; width: 90%; max-width: 1000px; margin: 0 auto; padding: 10px 20px 30px 20px; box-sizing: border-box; font-family: Arial, sans-serif; font-size: 12px; } ';
        } else {
            $replacement[] = '#jqe_report { clear: both; overflow: hidden; width: 100%; padding: 10px 20px 30px 20px; box-sizing: border-box; font-family: Arial, sans-serif; font-size: 12px; } ';
        }
        
        $replacement[] = '#jqe_report > div { position: relative; overflow: hidden; width: 100%; margin: 0 auto; border-radius: 4px; box-shadow: 0 12px 15px 0 rgba(0, 0, 0, 0.25); background: #fff; } ';
        $replacement[] = '#jqe_report code { white-space: normal; word-break: break-all; font-size: 1em; } ';
        $replacement[] = '#jqe_report .jqe_header, #jqe_report .jqe_footer { position: relative; overflow: hidden; width: 100%; padding: 10px 15px; box-sizing: border-box; background-color: #eee; } ';
        
        if ($title) {
            $replacement[] = '#jqe_report .jqe_header h3 > em { color: #d14; padding: 0 2px; } ';
        }
        
        $replacement[] = '#jqe_report .jqe_footer > span { line-height: 36px; } ';
        
        if ($as_modal) {
            $replacement[] = '#jqe_report .jqe_footer > button, #jqe_report_min button { width: auto; float: right; font-size: 12px; padding: 5px 10px; border: none; background-color: #4e4e4e; color: #fff; font-weight: bold; } ';
            $replacement[] = '#jqe_report .jqe_footer > button:hover, #jqe_report_min button:hover { background-color: #000; } ';
            $replacement[] = '#jqe_report .jqe_content { padding: 0; margin: 15px; overflow: auto; max-height: 200px; max-height: 60vh } ';
        } else {
            $replacement[] = '#jqe_report .jqe_content { padding: 0; margin: 15px; overflow: auto; } ';
        }
        
        $replacement[] = '</style>'.chr(13);
        
        if ($as_modal) {
            $replacement[] = '<div id="jqe_report_min">';
            $replacement[] = '<button onclick="document.getElementById(\'jqe_report_min\').style.display = \'none\'; document.getElementById(\'jqe_report\').style.display = \'block\'; document.getElementById(\'jqe_report_overlay\').style.display = \'block\'; return false;">'.Text::_('JSHOW').'</button>';
            $replacement[] = '</div>';
            
            $replacement[] = '<div id="jqe_report_overlay"></div>';
        }
        
        $replacement[] = '<div id="jqe_report">';
        $replacement[] = '<div>';
        
        // header
        
        $replacement[] = '<div class="jqe_header">';
        $replacement[] = '<h2>'.Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_JQUERYEASY').'</h2>';
        if ($title) {
            $replacement[] = '<h3>'.$title.'</h3>';
        }
        $replacement[] = '</div>';
        
        // content
        
        $replacement[] = '<dl class="jqe_content">';
        $replacement[] = '<dt style="position: absolute; top: -9999px; left: -9999px;">'.Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_JQUERYEASY').'</dt>';
        
        if (!empty($comments)) {
            foreach ($comments as $comment) {
                
                switch ($comment[0]) {
                    case 'info': $color = '#0c5460'; $bgcolor = '#d1ecf1'; $label = '<span class="label" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>'; break;
                    case 'deleted': $color = '#856404'; $bgcolor = '#fff3cd'; $label = '<span class="label" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>'; break;
                    case 'error': $color = '#721c24'; $bgcolor = '#f8d7da'; $label = '<span class="label" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>'; break;
                    case 'added': $color = '#155724'; $bgcolor = '#d4edda'; $label = '<span class="label" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>'; break;
                    default: $color = '#1b1e21'; $bgcolor = '#d6d8d9'; $label = '<span class="label" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>';
                }
                
                $replacement[] = '<dd style="color: '.$color.'; margin-bottom: 6px;">'.$label.$comment[1].'</dd>';
            }
        } else {
            $replacement[] = '<dd>'.Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_NOCHANGESMADE').'</dd>';
        }
        
        $replacement[] = '</dl>';
        
        // footer
        
        $replacement[] = '<div class="jqe_footer">';
        $replacement[] = '<span>'.Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_EXECUTIONTIME').': '.number_format($execution_time, 4).'</span>';
        
        if ($as_modal) {
            $replacement[] = '<button onclick="document.getElementById(\'jqe_report_min\').style.display = \'block\'; document.getElementById(\'jqe_report\').style.display = \'none\'; document.getElementById(\'jqe_report_overlay\').style.display = \'none\'; return false;">'.Text::_('JHIDE').'</button>';
        }
        
        $replacement[] = '</div>';
        
        // end
        
        $replacement[] = '</div>';
        
        $replacement[] = '</div>';
        
        return implode('', $replacement).chr(13);
    }
    
    //$root_path = (strpos($this->_jqpath, 'http') !== 0) ? $this->_root . Uri::root(true) . '/' . $this->_jqpath : $this->_jqpath;
    //$new_scripts[$this->_jqpath] = array('type' => 'text/javascript', 'options' => (Uri::isInternal($root_path) ? ['version' => $version->getMediaVersion()] : array()));
    
    //var_dump($this->_root); // http://localhost:7878
    //var_dump(Uri::root(true)); // /MyWork_4_x_free_test
    //var_dump(JPATH_ROOT); // E:\wamp64\www\MyWork_4_x_free_test
    
    //var_dump($root_path); // http://localhost:7878/MyWork_4_x_free_test/media/vendor/jquery/js/jquery.js
    //var_dump(Uri::isInternal($root_path)); // true  
    
    static public function isInternal($path)
    {
        $root = str_replace(Uri::root(true) . '/', '', Uri::root());
        
        $root_path = (strpos($path, 'http') !== 0) ? $root . Uri::root(true) . '/' . $path : $path;
    
        if (Uri::isInternal($root_path)) {
            return true;
        }
        
        return false;
    }
    
    static public function getJQueryPath($protocole, $compressed, $params, &$verbose, $cdn= 'google', $suffix = '')
    {
        $jQueryVersion = $params->get('jqueryversion'.$suffix, '1.8');
        
        if ($jQueryVersion == 'joomla') {
            return 'joomla'; //'media/vendor/jquery/js/jquery'.$compressed.'.js';
        } else {
            if ($jQueryVersion == 'local') {
                $localVersionPath = trim($params->get('localversion'.$suffix, ''));
                if ($localVersionPath) {
                    if (File::exists(JPATH_ROOT.$localVersionPath)) {
                        return ltrim($localVersionPath, "/");
                    } else {
                        self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
                    }
                } else {
                    self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALFILE', 'jQuery');
                }
            } else {
                
                $jQuerySubversion = trim($params->get('jquerysubversion'.$suffix, ''));
                
                $values_that_do_not_need_subversion = array('1.3', '1.4', '1.5', '1.6', '1.7', '1.8');
                if ($jQuerySubversion == '' && !in_array($jQueryVersion, $values_that_do_not_need_subversion)) {
                    $jQuerySubversion = '0';
                }
                
                if ($jQuerySubversion != '') {
                    $jQuerySubversion = '.'.$jQuerySubversion;
                }
                
                return self::getURL($cdn, 'jquery_js', $protocole, $jQueryVersion.$jQuerySubversion, $compressed);
            }
        }
        
        return '';
    }
    
    static public function getMigratePath($protocole, $compressed, $params, &$verbose, $cdn= 'google', $suffix = '')
    {
        $jQueryVersion = $params->get('jqueryversion'.$suffix, '1.8');
        $migrateVersion = $params->get('migrateversion'.$suffix, 'none');
        
        if ($migrateVersion != 'none') {
            
            $migrate_is_unnecessary = false;
            if ($jQueryVersion == '1.3' || $jQueryVersion == '1.4' || $jQueryVersion == '1.5' || $jQueryVersion == '1.6' || $jQueryVersion == '1.7' || $jQueryVersion == '1.8') {
                $migrate_is_unnecessary = true;
            }
            
            if (!$migrate_is_unnecessary) {
                if ($migrateVersion == 'joomla') {
                    return 'joomla'; //'media/vendor/jquery-migrate/js/jquery-migrate'.$compressed.'.js';
                } else {
                    if ($migrateVersion == 'local') {
                        $localPathMigrate = trim($params->get('localpathmigrate'.$suffix, ''));
                        if ($localPathMigrate) {
                            if (File::exists(JPATH_ROOT.$localPathMigrate)) {
                                return ltrim($localPathMigrate, "/");
                            } else {
                                self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localPathMigrate);
                            }
                        } else {
                            self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALFILE', 'Migrate');
                        }
                    } else {
                        
                        $migrateSubversion = trim($params->get('migratesubversion'.$suffix, ''));
                        
                        $values_that_do_not_need_subversion = array('1.2.1', '1.3.0', '1.4.1');
                        
                        if (in_array($migrateVersion, $values_that_do_not_need_subversion)) {
                            $migrateSubversion = '';
                        } else if ($migrateSubversion == '') { // missing sub-version
                            $migrateSubversion = '0';
                        }
                        
                        if ($migrateSubversion != '') {
                            $migrateSubversion = '.'.$migrateSubversion;
                        }
                        
                        return self::getURL($cdn, 'migrate', $protocole, $migrateVersion.$migrateSubversion, $compressed);
                    }
                }
            } else {
                self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_MIGRATEUNNECESSARY');
            }
        }
        
        return '';
    }
    
    static public function getjQueryUIPath($protocole, $compressed, $params, &$verbose, $cdn= 'google', $suffix = '')
    {
        $jQueryUIVersion = $params->get('jqueryuiversion'.$suffix, '1.9');
        
        // there is no more version packaged with Joomla
        
        if ($jQueryUIVersion == 'local') {
            $localVersionPath = trim($params->get('localuiversion'.$suffix, ''));
            if ($localVersionPath) {
                if (File::exists(JPATH_ROOT.$localVersionPath)) {
                    return ltrim($localVersionPath, "/");
                } else {
                    self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
                }
            } else {
                self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALFILE', 'jQuery UI');
            }
        } else {
            $jQueryUISubversion = trim($params->get('jqueryuisubversion'.$suffix, ''));
            
            $values_that_do_not_need_subversion = array('1.7', '1.8');
            if ($jQueryUISubversion == '' && !in_array($jQueryUIVersion, $values_that_do_not_need_subversion)) {
                $jQueryUISubversion = '0';
            }
            
            if ($jQueryUISubversion != '') {
                $jQueryUISubversion = '.'.$jQueryUISubversion;
            }
            
            return self::getURL($cdn, 'jqueryui_js', $protocole, $jQueryUIVersion.$jQueryUISubversion, $compressed);
        }
        
        return '';
    }
    
    static public function getjQueryUICSSPath($protocole, $compressed, $params, &$verbose, $cdn= 'google', $suffix = '')
    {
        $jQueryUITheme = $params->get('jqueryuitheme'.$suffix, 'none');
        
        if ($jQueryUITheme != 'none') {
            
            $jQueryUIVersion = $params->get('jqueryuiversion'.$suffix, '1.9');
            
            if ($jQueryUITheme == 'custom' || $jQueryUIVersion == 'local') {
                $localVersionPath = trim($params->get('jqueryuithemecustom'.$suffix, ''));
                if ($localVersionPath) {
                    if (File::exists(JPATH_ROOT.$localVersionPath)) {
                        return ltrim($localVersionPath, "/");
                    } else {
                        self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
                    }
                } else {
                    self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALCSSFILE');
                }
            } else {
                $jQueryUISubversion = trim($params->get('jqueryuisubversion'.$suffix, ''));
                
                $values_that_do_not_need_subversion = array('1.7', '1.8');
                if ($jQueryUISubversion == '' && !in_array($jQueryUIVersion, $values_that_do_not_need_subversion)) {
                    $jQueryUISubversion = '0';
                }
                
                if ($jQueryUISubversion != '') {
                    $jQueryUISubversion = '.'.$jQueryUISubversion;
                }
                
                return self::getURL($cdn, 'jqueryui_css', $protocole, $jQueryUIVersion.$jQueryUISubversion, $jQueryUITheme);
            }
        }
        
        return '';
    }
    
    static public function getPopperPath($protocole, $compressed, $params, &$verbose, $cdn= 'google', $suffix = '')
    {
        $BootstrapVersion = $params->get('bootstrapversion'.$suffix, 'joomla');
        
        $bootstrap_library_types = $params->get('bootstraplibrarytypes'.$suffix, 'both');
        
        if ($bootstrap_library_types == 'both' || $bootstrap_library_types == 'js') {
            
            if (substr($BootstrapVersion, 0, 1) === '4' || substr($BootstrapVersion, 0, 1) === '5') { // Bootstrap 4 & 5
                
                $PopperVersion = $params->get('popperversion'.$suffix, 'none');
                // no Joomla asset version, assume it's already included in package
                
                if ($PopperVersion != 'none' && !$params->get('loadbootstrapbundle'.$suffix, 0)) {
                    
                    $PopperSubversion = '.'.$params->get('poppersubversion'.$suffix, 0);
                    
                    return $protocole.'//cdnjs.cloudflare.com/ajax/libs/popper.js/'.$PopperVersion.$PopperSubversion.'/umd/popper'.$compressed.'.js';
                }
            }
        }
        
        return '';
    }
    
    static public function getBootstrapPaths($protocole, $compressed, $params, &$verbose, $cdn= 'google', $suffix = '')
    {
        $paths = array();
        
        $BootstrapVersion = $params->get('bootstrapversion'.$suffix, 'joomla');
        
        $bootstrap_library_types = $params->get('bootstraplibrarytypes'.$suffix, 'both');
        
        if ($bootstrap_library_types == 'both' || $bootstrap_library_types == 'js') {
            if ($BootstrapVersion == 'joomla') {
//                 $bundle = '';
//                 if ($params->get('loadbootstrapbundle', 0)) {
                    //$bundle = '.bundle'; // no bundle in framework
//                 }
                $paths['joomla'] = 'joomla'; //'/media/vendor/bootstrap/js/bootstrap-es5'.$bundle.$compressed.'.js';
            } else {
                if ($BootstrapVersion == 'local') {
                    $localVersionPaths = trim( (string) $params->get('localbootstrapversionjs'.$suffix, ''));
                    
                    if ($localVersionPaths) {
                        $localVersionPaths = array_map('trim', (array) explode("\n", $localVersionPaths));
                        
                        foreach ($localVersionPaths as $key => $localVersionPath) {
                            
                            if (File::exists(JPATH_ROOT.$localVersionPath)) {
                                $paths['local' . $key] = $localVersionPath;
                            } else {
                                self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASYPROFILES_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
                            }
                        }
                    } else {
                        self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASYPROFILES_VERBOSE_EMPTYLOCALFILE', 'Bootstrap');
                    }
                } else {
                    $BootstrapSubversion = trim($params->get('bootstrapsubversion'.$suffix, ''));
                    
                    $values_that_do_not_need_subversion = array('2.3.2');
                    
                    if (in_array($BootstrapVersion, $values_that_do_not_need_subversion)) {
                        $BootstrapSubversion = '';
                    } else if ($BootstrapSubversion == '') {
                        $BootstrapSubversion = '0';
                    }
                    
                    if ($BootstrapSubversion != '') {
                        $BootstrapSubversion = '.'.$BootstrapSubversion;
                    }
                    
                    $bundle = '';
                    if (substr($BootstrapVersion, 0, 1) === '4' || substr($BootstrapVersion, 0, 1) === '5') { // Bootstrap 4 & 5
                        if ($params->get('loadbootstrapbundle'.$suffix, 0)) {
                            $bundle = '.bundle';
                        }
                    }
                    
                    $paths['cdn'] = self::getURL($cdn, 'bootstrap_js', $protocole, $BootstrapVersion.$BootstrapSubversion, $bundle.$compressed);
                }
            }
        }
        
        return $paths;
    }
    
    static public function getBootstrapCSSPaths($protocole, $compressed, $params, &$verbose, $cdn= 'google', $suffix = '')
    {
        $paths = array();
        
        $BootstrapVersion = $params->get('bootstrapversion'.$suffix, 'joomla');
        
        $bootstrap_library_types = $params->get('bootstraplibrarytypes'.$suffix, 'both');
        
        if ($bootstrap_library_types == 'both' || $bootstrap_library_types == 'css') {
            if ($BootstrapVersion == 'joomla') {                
//                 $bs_css_packages = $params->get('bootstrapcsspackages', '');
//                 if (is_array($bs_css_packages) && !empty($bs_css_packages)) {
//                     if (in_array('grid', $bs_css_packages)) {
//                         $paths[] = '/media/vendor/bootstrap/css/bootstrap-grid'.$compressed.'.css';
//                     }
//                     if (in_array('reboot', $bs_css_packages)) {
//                         $paths[] = '/media/vendor/bootstrap/css/bootstrap-reboot'.$compressed.'.css';
//                     }
                    
                    // no utilities, even though Joomla uses B5 - included automatically in bootstrap.css
                    
//                 } else {
                $paths['joomla'] = 'joomla'; //'/media/vendor/bootstrap/css/bootstrap'.$compressed.'.css';
//                 }
            } else {
                if ($BootstrapVersion == 'local') {
                    $localVersionPaths = trim( (string) $params->get('localbootstrapversioncss'.$suffix, ''));
                    
                    if ($localVersionPaths) {
                        $localVersionPaths = array_map('trim', (array) explode("\n", $localVersionPaths));
                        
                        foreach ($localVersionPaths as $key => $localVersionPath) {
                            if (File::exists(JPATH_ROOT.$localVersionPath)) {
                                $paths['local' . $key] = $localVersionPath;
                            } else {
                                self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASYPROFILES_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
                            }
                        }
                    } else {
                        self::report($verbose, 'error', 'PLG_SYSTEM_JQUERYEASYPROFILES_VERBOSE_EMPTYLOCALFILE', 'Bootstrap');
                    }
                } else {
                    $BootstrapSubversion = trim($params->get('bootstrapsubversion'.$suffix, ''));
                    
                    $values_that_do_not_need_subversion = array('2.3.2');
                    
                    if (in_array($BootstrapVersion, $values_that_do_not_need_subversion)) {
                        $BootstrapSubversion = '';
                    } else if ($BootstrapSubversion == '') {
                        $BootstrapSubversion = '0';
                    }
                    
                    if ($BootstrapSubversion != '') {
                        $BootstrapSubversion = '.'.$BootstrapSubversion;
                    }
                    
                    if ($BootstrapVersion == '2.3.2') { // Bootstrap 2
                        $paths['cdn'] = self::getURL($cdn, 'bootstrap_css', $protocole, $BootstrapVersion.$BootstrapSubversion, $compressed);
                        $paths['cdn_extra'] = self::getURL($cdn, 'bootstrap_responsive_css', $protocole, $BootstrapVersion.$BootstrapSubversion, $compressed);
                        // TODO glyph image under ..img/
                    } else if (substr($BootstrapVersion, 0, 1) === '3') { // Bootstrap 3
                        $paths['cdn'] = self::getURL($cdn, 'bootstrap_css', $protocole, $BootstrapVersion.$BootstrapSubversion, $compressed);
                        if ($params->get('loadbootstraptheme'.$suffix, 0)) {
                            $paths['cdn_extra'] = self::getURL($cdn, 'bootstrap_theme_css', $protocole, $BootstrapVersion.$BootstrapSubversion, $compressed);
                        }
                        // TODO glyph fonts under ..fonts/
                    } else { // Bootstrap 4 & 5
                        $bs_css_packages = $params->get('bootstrapcsspackages'.$suffix, '');
                        
                        $rtl = '';
                        if ($params->get('bootstraprtl'.$suffix, 0) && substr($BootstrapVersion, 0, 1) === '5') {
                            $rtl = '.rtl';
                        }
                        
                        if (is_array($bs_css_packages) && !empty($bs_css_packages)) {
                            if (in_array('grid', $bs_css_packages)) {
                                $paths['grid'] = self::getURL($cdn, 'bootstrap_grid_css', $protocole, $BootstrapVersion.$BootstrapSubversion, $rtl.$compressed);
                            }
                            if (in_array('reboot', $bs_css_packages)) {
                                $paths['reboot'] = self::getURL($cdn, 'bootstrap_reboot_css', $protocole, $BootstrapVersion.$BootstrapSubversion, $rtl.$compressed);
                            }
                            if (in_array('utilities', $bs_css_packages)) {
                                if (substr($BootstrapVersion, 0, 1) === '5') {	// Bootstrap 5 only
                                    $paths['utilities'] = self::getURL($cdn, 'bootstrap_utilities_css', $protocole, $BootstrapVersion.$BootstrapSubversion, $rtl.$compressed);
                                }
                            }
                        } else {
                            $paths['cdn'] = self::getURL($cdn, 'bootstrap_css', $protocole, $BootstrapVersion.$BootstrapSubversion, $rtl.$compressed);
                        }
                    }
                }
            }
        }
        
        return $paths;
    }
    
}
