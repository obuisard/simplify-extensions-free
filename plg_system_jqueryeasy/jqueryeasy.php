<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\CMSPlugin;

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

	    //\JLoader::registerNamespace('SYW\\Plugin\\System\\JqueryEasy\\Field', JPATH_ROOT.'/plugins/system/jqueryeasy/field', false, false, 'psr4');

		$this->_enabled = false;

		if ($this->app->isClient('site')) {
			$this->_enabled = $this->allowedOnPage();

			$this->_versioning = $this->params->get('versioning', false);

			$this->_supplement_scripts = array();
			$this->_supplement_stylesheets = array();

			//$this->_showreport = false;

			$this->_showreport = $this->params->get('showreport', 0);

			if ($this->_showreport == 2) { // only show report when Super User is logged in
				$user = Factory::getUser();
				$this->_showreport = $user->authorise('core.admin') ? true : false;
			}

			$this->_verbose_array = array();

			$this->_usejQuery = false;
			$this->_usejQueryUI = false;

			$this->_jqpath = '';
			$this->_jquipath = '';
			$this->_jquicsspath = '';
			$this->_jqnoconflictpath = '';

			$this->_jqmigratepath = '';

			$this->_timeafterroute = 0;
			$this->_timebeforerender = 0;
			$this->_timeafterrender = 0;
		}
	}

	protected function allowedOnPage()
	{
		$allowed = true;

		$suffix = 'frontend';

		if (Factory::getDocument()->getType() !== 'html') {
			return false;
		}

		// disable plugin in selected templates

		$templates_array = $this->params->get('templateid', array('none'));

		if (!is_array($templates_array)) { // before the plugin is saved, the value is the string 'none'
			$templates_array = explode(' ', $templates_array);
		}

		$array_of_template_values = array_count_values($templates_array);
		if (isset($array_of_template_values['none']) && $array_of_template_values['none'] > 0) { // 'none' was selected
			// keep the plugin enabled
		} else {
			if (!empty($this->app->getTemplate(true)->id)) {
				$current_template_id = $this->app->getTemplate(true)->id;
				foreach ($array_of_template_values as $key => $value) {
					if ($current_template_id == $key) {
						return false;
					}
				}
			}
		}

		// enable plugin only on the allowed pages
		$includedPaths = trim( (string) $this->params->get('enableonlyin'.$suffix, ''));
		if ($includedPaths) {
			$paths = array_map('trim', (array) explode("\n", $includedPaths));
			$current_uri_string = URI::getInstance()->toString();

			$found = false;
			foreach ($paths as $path) {
				$paths_compare = self::path_compare($current_uri_string, $path);
				if ($paths_compare) {
					$found = true;
				}
			}
			if (!$found) {
				return false;
			}
		} else {
			// disable plugin in the listed pages
			$excludedPaths = trim( (string) $this->params->get('disablein'.$suffix, ''));
			if ($excludedPaths) {
				$paths = array_map('trim', (array) explode("\n", $excludedPaths));
				$current_uri_string = URI::getInstance()->toString();

				foreach ($paths as $path) {
					$paths_compare = self::path_compare($current_uri_string, $path);
					if ($paths_compare) {
						return false;
					}
				}
			}
		}

		return $allowed;
	}

	function onAfterInitialise()
	{
		if (!$this->app->isClient('site')) {
			return;
		}

		if (!$this->_enabled) {
			return;
		}

		$this->loadLanguage();

		if ($this->params->get('disablecaptions', 0)) { // never add caption scripts to the site (libraries/cms/html/behavior)

			$caption = function()
			{
				if (isset(self::$loaded['behavior.caption'])) {
					return;
				}

				// do nothing

				self::$loaded['behavior.caption'] = true;
			};

			HTMLHelper::register('jhtml.behavior.caption', $caption);

			if ($this->_showreport) {
				$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDCAPTIONLIBRARY');
			}
		}
	}

	function onAfterRoute()
	{
	    if (!$this->app->isClient('site')) {
			return;
		}

		//$doc = Factory::getDocument();

		// 		if (Factory::getDocument()->getType() !== 'html') {
			// put here so Factory::getDocument() does not break feeds (will break if used in any function before onAfterRoute)
			// https://groups.google.com/forum/?fromgroups#!topic/joomla-dev-general/S0GYKhLm92A
// 			$this->_enabled = false;
// 			return;
// 		}

		if (!$this->_enabled) {
			return;
		}

		$this->loadLanguage();

		$time_start = microtime(true);

// 		$this->_showreport = $this->params->get('showreport', 0);

// 		if ($this->_showreport == 2) { // only show report when Super User is logged in
// 			$user = Factory::getUser();
// 			$this->_showreport = $user->authorise('core.admin') ? true : false;
// 		}

		$suffix = 'frontend';

		// disable plugin in selected templates

// 		$templates_array = $this->params->get('templateid', array('none'));

// 		if (!is_array($templates_array)) { // before the plugin is saved, the value is the string 'none'
// 			$templates_array = explode(' ', $templates_array);
// 		}

// 		$array_of_template_values = array_count_values($templates_array);
// 		if (isset($array_of_template_values['none']) && $array_of_template_values['none'] > 0) { // 'none' was selected
// 			// keep the plugin enabled
// 		} else {
// 			if (!empty($this->app->getTemplate(true)->id)) {
// 				$current_template_id = $this->app->getTemplate(true)->id;
// 				foreach ($array_of_template_values as $key => $value) {
// 					if ($current_template_id == $key) {
// 						$this->_enabled = false;
// 						return;
// 					}
// 				}
// 			}
// 		}

		// enable plugin only on the allowed pages
// 		$includedPaths = trim( (string) $this->params->get('enableonlyin'.$suffix, ''));
// 		if ($includedPaths) {
// 			$paths = array_map('trim', (array) explode("\n", $includedPaths));
// 			$current_uri_string = URI::getInstance()->toString();

			//if ($this->_showreport) {
			//	$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ENABLEPLUGININPAGES');
			//	$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_CURRENTURI', $current_uri_string);
			//}

// 			$found = false;
// 			foreach ($paths as $path) {
// 				$paths_compare = self::path_compare($current_uri_string, $path);
// 				if ($paths_compare) {
// 					$found = true;
// 				}
// 			}
// 			if (!$found) {
// 				$this->_enabled = false;
// 				return;
// 			}
// 		} else {
			// disable plugin in the listed pages
// 			$excludedPaths = trim( (string) $this->params->get('disablein'.$suffix, ''));
// 			if ($excludedPaths) {
// 				$paths = array_map('trim', (array) explode("\n", $excludedPaths));
// 				$current_uri_string = URI::getInstance()->toString();

				//if ($this->_showreport) {
				//	$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_DISABLEPLUGININPAGES');
				//	$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_CURRENTURI', $current_uri_string);
				//}

// 				foreach ($paths as $path) {
// 					$paths_compare = self::path_compare($current_uri_string, $path);
// 					if ($paths_compare) {
// 						$this->_enabled = false;
// 						return;
// 					}
// 				}
// 			}
// 		}

		// BEGIN prepare spaces to fill with script

		$javascript = trim( (string) $this->params->get('addjavascript'.$suffix, ''));
		if (!empty($javascript)) {
			$this->_supplement_scripts = array_map('trim', (array) explode("\n", $javascript));
			foreach($this->_supplement_scripts as $i => $path) {
				if (Uri::isInternal(Uri::base().$path) || Uri::isInternal($path)) {
					self::addScript($i.'ADD_SCRIPT_HERE', $this->_versioning);
				} else {
					self::addScript($i.'ADD_SCRIPT_HERE');
				}
			}
		}

		// END prepare spaces to fill with scripts

		// BEGIN prepare spaces to fill with scripts declarations

		$javascript_declaration = trim( (string) $this->params->get('addjavascriptdeclaration'.$suffix, ''));
		if (!empty($javascript_declaration)) {
			Factory::getDocument()->addScriptDeclaration('ADD_SCRIPT_DECLARATION_HERE');
		}

		// END prepare spaces to fill with scripts declarations

		// BEGIN prepare spaces to fill with stylesheets and stylesheets declarations

		$css = trim( (string) $this->params->get('addcss'.$suffix, ''));
		if (!empty($css)) {
			$this->_supplement_stylesheets = array_map('trim', (array) explode("\n", $css));
			foreach($this->_supplement_stylesheets as $i => $path) {
				if (Uri::isInternal(Uri::base().$path) || Uri::isInternal($path)) {
					self::addStyleSheet($i.'ADD_STYLESHEET_HERE', $this->_versioning);
				} else {
					self::addStyleSheet($i.'ADD_STYLESHEET_HERE');
				}
			}
		}

		$css_declaration = trim( (string) $this->params->get('addcssdeclaration'.$suffix, ''));
		if (!empty($css_declaration)) {
			Factory::getDocument()->addStyleDeclaration('ADD_STYLESHEET_DECLARATION_HERE');
		}

		// END prepare spaces to fill with stylesheets and stylesheets declarations

		// protocole

		$protocole = $this->params->get('whichhttp'.$suffix, 'https');
		$protocole = ($protocole == 'none') ? '' : $protocole.':';

		// compression

		$compressed = '';
		if ($this->params->get('compression'.$suffix, 'compressed') == 'compressed') {
			$compressed = '.min';
		}

		// set jQuery variables

		switch ($this->params->get('jqueryin'.$suffix, 0)) {
			case 1: $this->_usejQuery = true; break;
			case 2: $this->_usejQuery = true; $this->_usejQueryUI = true; break;
			default: break;
		}

		// jQuery

		if ($this->_usejQuery)
		{
			$jQueryVersion = $this->params->get('jqueryversion'.$suffix, '1.8');

			// jQuery path

			if ($jQueryVersion == 'joomla') {
				$this->_jqpath = URI::root(true).'/media/vendor/jquery/js/jquery'.$compressed.'.js';
			} else {
				if ($jQueryVersion == 'local') {
					$localVersionPath = trim($this->params->get('localversion'.$suffix, ''));
					if ($localVersionPath) {
						if (File::exists(JPATH_ROOT.$localVersionPath)) {
							$this->_jqpath = URI::root(true).$localVersionPath;
						} else {
							if ($this->_showreport) {
								$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
							}
						}
					} else {
						if ($this->_showreport) {
							$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALFILE', 'jQuery');
						}
					}
				} else {

					$jQuerySubversion = trim($this->params->get('jquerysubversion'.$suffix, ''));

					$values_that_do_not_need_subversion = array('1.3', '1.4', '1.5', '1.6', '1.7', '1.8');
					if ($jQuerySubversion == '' && !in_array($jQueryVersion, $values_that_do_not_need_subversion)) {
						$jQuerySubversion = '0';
					}

					if ($jQuerySubversion != '') {
						$jQuerySubversion = '.'.$jQuerySubversion;
					}

					$this->_jqpath = $protocole.'//ajax.googleapis.com/ajax/libs/jquery/'.$jQueryVersion.$jQuerySubversion.'/jquery'.$compressed.'.js';
				}
			}

			if (!empty($this->_jqpath)) {
				if ($jQueryVersion == 'joomla' || $jQueryVersion == 'local') {
					self::addScript('JQEASY_JQLIB', $this->_versioning);
				} else {
					self::addScript('JQEASY_JQLIB');
				}
			}

			// jQuery Migrate

			$migrateVersion = $this->params->get('migrateversion'.$suffix, 'none');
			if ($migrateVersion != 'none') {

				$migrate_is_unnecessary = false;
				if ($jQueryVersion == '1.3' || $jQueryVersion == '1.4' || $jQueryVersion == '1.5' || $jQueryVersion == '1.6' || $jQueryVersion == '1.7' || $jQueryVersion == '1.8') {
					$migrate_is_unnecessary = true;
				}

				if (!$migrate_is_unnecessary) {
					if ($migrateVersion == 'joomla') {
						$this->_jqmigratepath = URI::root(true).'/media/vendor/jquery/js/jquery-migrate'.$compressed.'.js';
					} else {
						if ($migrateVersion == 'local') {
							$localPathMigrate = trim($this->params->get('localpathmigrate'.$suffix, ''));
							if ($localPathMigrate) {
								if (File::exists(JPATH_ROOT.$localPathMigrate)) {
									$this->_jqmigratepath = URI::root(true).$localPathMigrate;
								} else {
									if ($this->_showreport) {
										$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localPathMigrate);
									}
								}
							} else {
								if ($this->_showreport) {
									$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALFILE', 'Migrate');
								}
							}
						} else {

							if ($migrateVersion == '3.0.0') { // for backward compatibility
								$migrateVersion = '3.0';
							}

							$migrateSubversion = trim($this->params->get('migratesubversion'.$suffix, ''));

							$values_that_do_not_need_subversion = array('1.2.1', '1.3.0', '1.4.1');

							if (in_array($migrateVersion, $values_that_do_not_need_subversion)) {
								$migrateSubversion = '';
							} else if ($migrateSubversion == '') { // missing sub-version
								$migrateSubversion = '0';
							}

							if ($migrateSubversion != '') {
								$migrateSubversion = '.'.$migrateSubversion;
							}

							$this->_jqmigratepath = $protocole.'//code.jquery.com/jquery-migrate-'.$migrateVersion.$migrateSubversion.$compressed.'.js';
						}
					}
				} else {
					if ($this->_showreport) {
						$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_MIGRATEUNNECESSARY');
					}
				}

				if (!empty($this->_jqmigratepath)) {
					if ($migrateVersion == 'joomla' || $migrateVersion == 'local') {
						self::addScript('JQEASY_JQMIGRATELIB', $this->_versioning);
					} else {
						self::addScript('JQEASY_JQMIGRATELIB');
					}
				}
			}

			// no conflict path

			$addjQueryNoConflict = $this->params->get('addnoconflict'.$suffix, 2);
			if ($addjQueryNoConflict == 1) {
				Factory::getDocument()->addScriptDeclaration('JQEASY_JQNOCONFLICT');
			} else if ($addjQueryNoConflict == 2) {
				self::addScript('JQEASY_JQNOCONFLICT', $this->_versioning);

				// media/system/js/jquery-noconflict.js contains var $=jQuery.noConflict();

				//if ($jQueryVersion == 'joomla') {
					//$this->_jqnoconflictpath = URI::root(true).'/media/system/js/jquery-noconflict.js';
				//} else {
					$this->_jqnoconflictpath = URI::root(true).'/media/syw_jqueryeasy/js/jquerynoconflict.js';
				//}
			}

			// jQuery UI

			if ($this->_usejQueryUI)
			{
				$jQueryUIVersion = $this->params->get('jqueryuiversion'.$suffix, '1.9');

				$jQueryUISubversion = trim($this->params->get('jqueryuisubversion'.$suffix, ''));

				$values_that_do_not_need_subversion = array('1.7', '1.8');
				if ($jQueryUISubversion == '' && !in_array($jQueryUIVersion, $values_that_do_not_need_subversion)) {
					$jQueryUISubversion = '0';
				}

				if ($jQueryUISubversion != '') {
					$jQueryUISubversion = '.'.$jQueryUISubversion;
				}

				$jQueryUITheme = $this->params->get('jqueryuitheme'.$suffix, 'none');

				// jQuery UI path

				if ($jQueryUIVersion == 'joomla') {
					$this->_jquipath = URI::root(true).'/media/vendor/jquery-ui/js/jquery.ui.core'.$compressed.'.js';
				} else {
					if ($jQueryUIVersion == 'local') {
						$localVersionPath = trim($this->params->get('localuiversion'.$suffix, ''));
						if ($localVersionPath) {
							if (File::exists(JPATH_ROOT.$localVersionPath)) {
								$this->_jquipath = URI::root(true).$localVersionPath;
							} else {
								if ($this->_showreport) {
									$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
								}
							}
						} else {
							if ($this->_showreport) {
								$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALFILE', 'jQuery UI');
							}
						}
					} else {
						$this->_jquipath = $protocole.'//ajax.googleapis.com/ajax/libs/jqueryui/'.$jQueryUIVersion.$jQueryUISubversion.'/jquery-ui'.$compressed.'.js';
					}
				}

				if (!empty($this->_jquipath)) {
					if ($jQueryUIVersion == 'joomla' || $jQueryUIVersion == 'local') {
						self::addScript('JQEASY_JQUILIB', $this->_versioning);
					} else {
						self::addScript('JQEASY_JQUILIB');
					}
				}

				// jQuery UI CSS path

				if ($jQueryUITheme != 'none') {
					if ($jQueryUITheme == 'custom' || $jQueryUIVersion == 'joomla' || $jQueryUIVersion == 'local') {
						$localVersionPath = trim($this->params->get('jqueryuithemecustom'.$suffix, ''));
						if ($localVersionPath) {
							if (File::exists(JPATH_ROOT.$localVersionPath)) {
								$this->_jquicsspath = URI::root(true).$localVersionPath;
							} else {
								if ($this->_showreport) {
									$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
								}
							}
						} else {
							if ($this->_showreport) {
								$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALCSSFILE');
							}
						}
					} else {
						$this->_jquicsspath = $protocole.'//ajax.googleapis.com/ajax/libs/jqueryui/'.$jQueryUIVersion.$jQueryUISubversion.'/themes/'.$jQueryUITheme.'/jquery-ui.css';
					}

					if (!empty($this->_jquicsspath)) {
						if ($jQueryUITheme == 'custom' || $jQueryUIVersion == 'joomla' || $jQueryUIVersion == 'local') {
							self::addStyleSheet('JQEASY_JQUICSS', $this->_versioning);
						} else {
							self::addStyleSheet('JQEASY_JQUICSS');
						}
					}
				}
			} // END jQuery UI
		} // END jQuery

		$time_end = microtime(true);
		$this->_timeafterroute = $time_end - $time_start;
	}

	function onBeforeRender()
	{
	    if (!$this->app->isClient('site')) {
			return;
		}

		if (!$this->_enabled) {
			return;
		}

		$doc = Factory::getDocument();

		$this->loadLanguage();

		$time_start = microtime(true);

		$headerdata = $doc->getHeadData();
		$scripts = (isset($headerdata['scripts']) && is_array($headerdata['scripts'])) ? $headerdata['scripts'] : array(); // could be empty string
		$headerdata['scripts'] = array();

		// check if jQuery and Bootstrap are used in the template (nothing in $headerdata before 'onBeforeRender' other than what has been added in the plugin)
		if ($this->_showreport) {

			//$headerdata = $doc->getHeadData();
			//$scripts = $headerdata['scripts'];

			$jquery_quoted_path = preg_quote('media/vendor/jquery/js/jquery', '/');
			$jqueryui_quoted_path = preg_quote('media/vendor/jquery-ui/js/jquery.ui', '/');
			$bootstrap_quoted_path = preg_quote('media/vendor/bootstrap/js/bootstrap', '/');

			$jquery_loaded_by_template = false;
			$jqueryui_loaded_by_template = false;
			$bootstrap_loaded_by_template = false;

			foreach ($scripts as $url => $type) {
				if (preg_match('#'.$jquery_quoted_path.'#s', $url)) {
					if (!$jquery_loaded_by_template) {
						$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_JQUERYLOADEDBYTEMPLATE');
						$jquery_loaded_by_template = true;
					}
				}

				if (preg_match('#'.$jqueryui_quoted_path.'#s', $url)) {
					if (!$jqueryui_loaded_by_template) {
						$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_JQUERYUILOADEDBYTEMPLATE');
						$jqueryui_loaded_by_template = true;
					}
				}

				if (preg_match('#'.$bootstrap_quoted_path.'#s', $url)) {
					if (!$bootstrap_loaded_by_template) {
						$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_BOOTSTRAPLOADEDBYTEMPLATE');
						$bootstrap_loaded_by_template = true;
					}
				}
			}
		}

		// at this point, jQuery and MooTools libraries are loaded in the wrong order, if jQuery is enabled
		// we have jQuery, MooTools and other libraries loaded in that order
		// take all 'media/system/js' libraries and put them in front of all others

		//$headerdata = $doc->getHeadData();

		//$ignore_caption = $this->params->get('disablecaptions', 0);

		// make sure we start with all jQuery Easy scripts

		$scripts_jqeasy = array();

		foreach (/*$headerdata['scripts']*/$scripts as $url => $type) {
			if (preg_match('#JQEASY_#s', $url)) {
				$scripts_jqeasy[$url] = $type;
			}
		}

		if (!empty($scripts_jqeasy)) {

			//$scripts = $headerdata['scripts'];
			//$headerdata['scripts'] = array();

			foreach ($scripts_jqeasy as $url_jqeasy => $type_jqeasy) {
				$headerdata['scripts'][$url_jqeasy] = $type_jqeasy;
				unset($scripts[$url_jqeasy]);
			}

			// then with MooTools and all system scripts

			$quoted_path = preg_quote('media/system/js/', '/');
			foreach ($scripts as $url => $type) {
				if (preg_match('#'.$quoted_path.'#s', $url)) {

					//if ($ignore_caption && preg_match('#'.$quoted_path.'legacy/caption#s', $url)) {
						//$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDCAPTIONLIBRARY');
					//} else {
						$headerdata['scripts'][$url] = $type;
					//}

					unset($scripts[$url]);
				}
			}

			// make sure we follow with all media/jui/js scripts

			// 			$quoted_path = preg_quote('media/jui/js/', '/');
			// 			foreach ($scripts as $url => $type) {
			// 				if (preg_match('#'.$quoted_path.'#s', $url)) {
			// 					$headerdata['scripts'][$url] = $type;
			// 					unset($scripts[$url]);
			// 				}
			// 			}

			// remaining scripts

			foreach ($scripts as $url => $type) {
				$headerdata['scripts'][$url] = $type;
			}

			if ($this->_showreport) {
				$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_REORDEREDLIBRARIES');
			}
		} //else {
// 			$quoted_path = preg_quote('media/system/js/', '/');

// 			foreach ($headerdata['scripts'] as $url => $type) {
// 				if ($ignore_caption && preg_match('#'.$quoted_path.'legacy/caption#s', $url)) {
// 					unset($headerdata['scripts'][$url]);
// 					$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDCAPTIONLIBRARY');
// 					break;
// 				}
// 			}
// 		}

		// also we have script declarations loaded alongside MooTools libraries
		// if getting rid of libraries, also need to get rid of script declarations associated to them
		// NOTE: JCaption is now called with jQuery (from Joomla 3.2), not MooTools anymore

// 		if ($ignore_caption) {

// 			$regexp = '([\s\w();,\':\.-]*)JCaption([\s\w();,\':\.-]*)';

// 			if ($this->_showreport) {
// 				$count = 0;
// 				$headerdata['script'] = preg_replace('#'.$regexp.'#', '', $headerdata['script'], -1, $count);
// 				if ($count > 0) {
// 					$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVECAPTION');
// 				}
// 			} else { // faster
// 				$headerdata['script'] = preg_replace('#'.$regexp.'#', '', $headerdata['script'], 1);
// 			}
// 		}

		$doc->setHeadData($headerdata);

		$time_end = microtime(true);
		$this->_timebeforerender = $time_end - $time_start;
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

		$suffix = 'frontend';

		$remove_empty_scripts = false;
		$remove_empty_links = false;

		$remainingScripts = trim( (string) $this->params->get('stripremainingscripts'.$suffix, ''));
		if ($remainingScripts) {
			$remainingScripts = array_map('trim', (array) explode("\n", $remainingScripts));
			foreach ($remainingScripts as $script) {
				$quoted_script = preg_quote($script, '/'); // prepares for regexp
				$count = 0;
				$body = preg_replace('#<script[^>]*'.$quoted_script.'[^>]*></script>#', '', $body, -1, $count);
				if ($count > 0 && $this->_showreport) {
					$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGSCRIPT', $script, $count);
				}
			}
		}

		$remainingStylesheets = trim( (string) $this->params->get('stripremainingcss'.$suffix, ''));
		if ($remainingStylesheets) {
			$remainingStylesheets = array_map('trim', (array) explode("\n", $remainingStylesheets));
			foreach ($remainingStylesheets as $stylesheet) {
				$quoted_stylesheet = preg_quote($stylesheet, '/'); // prepares for regexp
				$count = 0;
				$body = preg_replace('#<link[^>]*'.$quoted_stylesheet.'[^>]*/>#', '', $body, -1, $count);
				if ($count > 0 && $this->_showreport) {
					$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGCSS', $stylesheet, $count);
				}
			}
		}

		if ($this->_usejQuery) {

			$removejQueryNoConflict = $this->params->get('removenoconflict'.$suffix, 1);
			if ($removejQueryNoConflict) {

				// remove all '...jQuery.noConflict(...);' or '... $.noConflict(...);'

				$regexp = '[^}^;^\n^>]*(jQuery|\$)\.no[cC]onflict\((true|false|)\);';

				$matches = array();
				if (preg_match_all('#'.$regexp.'#', $body, $matches, PREG_SET_ORDER) > 0) {

					$quoted_javascript = preg_quote('<script type="text/javascript">', '/');

					foreach ($matches as $match) {
						$quoted_match = preg_quote($match[0], '#'); // prepares for regexp

						if (preg_match('#('.$quoted_javascript.'[\S\s]*?'.$quoted_match.')#', $body)) { // makes sure we are in a javascript tag with anything in between the script tag and the noConflict code
							$body = preg_replace('#'.$quoted_match.'#', '', $body, 1);
							if ($this->_showreport) {
								$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTDECLARATIONS', $match[0]);
							}
						}
					}

					$count = 0;
					$body = preg_replace('#<script type="text/javascript">[\s]*?</script>#', '', $body, -1, $count); // remove newly empty scripts, if any
					if ($count > 0 && $this->_showreport) {
						$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYSCRIPTTAGS', $count);
					}
				}

				// remove potential jquery-noconflict.js (different combinations)

				$regexp = 'src="([\\\/a-zA-Z0-9_:\.~-]*)jquery[.-]*no[.-]*[cC]onflict([0-9\.-]|min)*?.js(.*?)"';

				$count = 0;
				$body = preg_replace('#'.$regexp.'#', 'GARBAGE', $body, -1, $count);
				if ($count > 0) {
					$remove_empty_scripts = true;
					if ($this->_showreport) {
						$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTS', $count);
					}
				}
			}

			$do_not_add_libraries = false;
			$move_unique_library = false;

			$replace_when_unique = $this->params->get('replacewhenunique'.$suffix, 1);
			$add_when_missing = $this->params->get('addwhenmissing'.$suffix, 1);

			// remove all other references to jQuery library except some
			$ignoreScripts = trim( (string) $this->params->get('ignorescripts'.$suffix, ''));
			if ($ignoreScripts) {
				$ignoreScripts = array_map('trim', (array) explode("\n", $ignoreScripts));
			}

			$regexp = 'src="([\\\/a-zA-Z0-9_:\.~-]*)jquery([0-9\.-]|latest|core|min|pack)*?.js(.*?)"';

			if (empty($ignoreScripts) && $add_when_missing && $replace_when_unique) { // faster this way
				$count = 0;
				$body = preg_replace('#'.$regexp.'#', 'GARBAGE', $body, -1, $count); // find jQuery versions
				if ($count > 0) {
					$remove_empty_scripts = true;
					if ($this->_showreport) {
						$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERY', $count);
					}
				}
			} else {
				$matches = array();
				if (preg_match_all('#'.$regexp.'#', $body, $matches, PREG_SET_ORDER) >= 0) {

					$nbr_of_matches = sizeof($matches);
					if ($nbr_of_matches == 0 && !$add_when_missing) {
						$do_not_add_libraries = true;
						if ($this->_showreport) {
							$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_NOJQUERYLIBRARIESADDED');
						}
					} elseif ($nbr_of_matches == 1 && !$replace_when_unique) {
						foreach ($matches as $match) {
							$this->_jqpath = rtrim(substr($match[0], 5), '"');
							$move_unique_library = true;
							if ($this->_showreport) {
								$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUELIBRARY', $this->_jqpath);
							}
						}
					}

					foreach ($matches as $match) {
						$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
						$ignore = false;
						if ($ignoreScripts) {
							foreach ($ignoreScripts as $script) {
								if (stripos($match[0], $script) !== false) { // library needs to be ignored for removal
									$ignore = true;
									if ($this->_showreport) {
										$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_IGNORESCRIPT', $script);
									}
								}
							}
						}
						if (!$ignore) { // remove the library
							$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
							$remove_empty_scripts = true;
							if ($this->_showreport) {
								if ($nbr_of_matches == 1 && !$replace_when_unique) {
									// do not show any message
								} else {
									$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYLIBRARY', rtrim(substr($match[0], 5), '"'));
								}
							}
						}
					}
				}
			}

			// use jQuery version set in the plugin
			if (!empty($this->_jqpath)) {
				if ($do_not_add_libraries) {
					$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQLIB#', 'GARBAGE', $body, 1);
					$remove_empty_scripts = true;
				} else {
					$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQLIB#', $this->_jqpath, $body, 1);
					if ($this->_showreport) {
						if ($move_unique_library) {
							$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERY', $this->_jqpath);
						} else {
							$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERY', '<a href="'.$this->_jqpath.'" target="_blank">'.$this->_jqpath.'</a>');
						}
					}
				}
			} else {
				if ($this->_showreport) {
					$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERY');
				}
			}

			// remove all references to Migrate scripts

			$regexp = 'src="([\\\/a-zA-Z0-9_:\.~-]*)jquery([0-9\.-])*?migrate([0-9\.-]|latest|core|min|pack)*?.js(.*?)"';

			$count = 0;
			$body = preg_replace('#'.$regexp.'#', 'GARBAGE', $body, -1, $count); // find Migrate versions
			if ($count > 0) {
				$remove_empty_scripts = true;
				if ($this->_showreport) {
					$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDMIGRATE', $count);
				}
			}

			// use jQuery Migrate
			if (!empty($this->_jqmigratepath)) {
				$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQMIGRATELIB#', $this->_jqmigratepath, $body, 1);
				if ($this->_showreport) {
					$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYMIGRATE', '<a href="'.$this->_jqmigratepath.'" target="_blank">'.$this->_jqmigratepath.'</a>');
				}
			}

			// replace deleted occurences
			$addjQueryNoConflict = $this->params->get('addnoconflict'.$suffix, 2);
			if ($addjQueryNoConflict == 1) {
				if ($do_not_add_libraries) {
					$body = preg_replace('#JQEASY_JQNOCONFLICT#', '', $body, 1);
				} else {
					$body = preg_replace('#JQEASY_JQNOCONFLICT#', 'jQuery.noConflict();', $body, 1); // add unique jQuery.noConflict();
					if ($this->_showreport) {
						$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTDECLARATION');
					}
				}
			} elseif ($addjQueryNoConflict == 2) {
				if ($do_not_add_libraries) {
					$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQNOCONFLICT#', 'GARBAGE', $body, 1);
					$remove_empty_scripts = true;
				} else {
					$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQNOCONFLICT#', $this->_jqnoconflictpath, $body, 1); // add jquerynoconflict.js
					if ($this->_showreport) {
						$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTSCRIPT', $this->_jqnoconflictpath);
					}
				}
			}

			// replace '$(document).ready(function()' or '$(document).ready(function($)' with 'jQuery(document).ready(function($)'
			if ($this->params->get('replacedocumentready'.$suffix, 1)) {
				$count = 0;
				$body = preg_replace('#\$\(document\).ready\(function\([$]?\)#s', 'jQuery(document).ready(function($)', $body, -1, $count);
				if ($count > 0 && $this->_showreport) {
					$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REPLACEDDOCUMENTREADY', $count);
				}
			}

			if ($this->_usejQueryUI) {
				$move_unique_libraryui = false;
				$move_unique_cssui = false;

				// remove all other references to jQuery UI library

				$regexp = 'src="([\\\/a-zA-Z0-9_:\.~-]*)jquery[.-]*ui([0-9\.-]|latest|core|custom|min|pack)*?.js(.*?)"';

				if (!$replace_when_unique) {
					$matches = array();
					if (preg_match_all('#'.$regexp.'#', $body, $matches, PREG_SET_ORDER) > 0) {

						$nbr_of_matches = sizeof($matches);
						if ($nbr_of_matches == 1) {
							foreach ($matches as $match) {
								$this->_jquipath = rtrim(substr($match[0], 5), '"');
								$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
								$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
								$remove_empty_scripts = true;
								$move_unique_libraryui = true;
								if ($this->_showreport) {
									$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUELIBRARYUI', $this->_jquipath);
								}
							}
						} else {
							foreach ($matches as $match) {
								$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
								$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
								$remove_empty_scripts = true;
								if ($this->_showreport) {
									$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUILIBRARY', rtrim(substr($match[0], 5), '"'));
								}
							}
						}
					}
				} else { // faster this way
					$count = 0;
					$body = preg_replace('#'.$regexp.'#', 'GARBAGE', $body, -1, $count); // find jQuery UI versions
					if ($count > 0) {
						$remove_empty_scripts = true;
						if ($this->_showreport) {
							$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUI', $count);
						}
					}
				}

				// use jQuery UI version set in the plugin
				if (!empty($this->_jquipath)) {
					if ($do_not_add_libraries) {
						$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQUILIB#', 'GARBAGE', $body, 1);
						$remove_empty_scripts = true;
					} else {
						$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQUILIB#', $this->_jquipath, $body, 1);
						if ($this->_showreport) {
							if ($move_unique_libraryui) {
								$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERYUI', $this->_jquipath);
							} else {
								$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUI', '<a href="'.$this->_jquipath.'" target="_blank">'.$this->_jquipath.'</a>');
							}
						}
					}
				} else {
					if ($this->_showreport) {
						$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUI');
					}
				}

				// remove all other references to jQuery UI stylesheets

				$regexp = 'href="([\\\/a-zA-Z0-9_:\.~-]*)jquery[.-]*ui([0-9\.-]|latest|core|custom|min|pack)*?.css(.*?)"';

				if (!$replace_when_unique) {
					$matches = array();
					if (preg_match_all('#'.$regexp.'#', $body, $matches, PREG_SET_ORDER) > 0) {

						$nbr_of_matches = sizeof($matches);
						if ($nbr_of_matches == 1) {
							foreach ($matches as $match) {
								$this->_jquicsspath = rtrim(substr($match[0], 5), '"');
								$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
								$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
								$remove_empty_links = true;
								$move_unique_cssui = true;
								if ($this->_showreport) {
									$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUECSSUI', $this->_jquicsspath);
								}
							}
						} else {
							foreach ($matches as $match) {
								$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
								$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
								$remove_empty_links = true;
								if ($this->_showreport) {
									$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSSLINK', rtrim(substr($match[0], 5), '"'));
								}
							}
						}
					}
				} else { // faster this way
					$count = 0;
					$body = preg_replace('#'.$regexp.'#', 'GARBAGE', $body, -1, $count); // find jQuery UI CSS versions
					if ($count > 0) {
						$remove_empty_links = true;
						if ($this->_showreport) {
							$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSS', $count);
						}
					}
				}

				// use jQuery UI CSS set in the plugin
				if (!empty($this->_jquicsspath)) {
					if ($do_not_add_libraries) {
						$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQUICSS#', 'GARBAGE', $body, 1);
						$remove_empty_links = true;
					} else {
						$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)JQEASY_JQUICSS#', $this->_jquicsspath, $body, 1);
						if ($this->_showreport) {
							if ($move_unique_cssui) {
								$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERYUICSS', $this->_jquicsspath);
							} else {
								$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUICSS', '<a href="'.$this->_jquicsspath.'" target="_blank">'.$this->_jquicsspath.'</a>');
							}
						}
					}
				} else {
					if ($this->_showreport) {
						if ($this->params->get('jqueryuitheme'.$suffix, 'none') != 'none') {
							$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUICSS');
						}
					}
				}
			}
		} // END if $this->jQuery

		// remove all obsolete script tags
		if ($remove_empty_scripts) {
			$count = 0;
			$body = preg_replace('#<script[^>]*GARBAGE[^>]*></script>#', '', $body, -1, $count); // remove newly empty scripts
			if ($count > 0 && $this->_showreport) {
				$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYSCRIPTTAGS', $count);
			}
		}

		// remove all obsolete link tags
		if ($remove_empty_links) {
			$count = 0;
			$body = preg_replace('#<link[^>]*GARBAGE[^>]*/>#', '', $body, -1, $count); // remove newly empty stylesheets
			if ($count > 0 && $this->_showreport) {
				$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYLINKTAGS', $count);
			}
		}

		// TODO should this be done at the same time as the removal of JCaption?
		// remove newly empty script left after removal of new JCaption('img.caption');
// 		if ($this->params->get('disablecaptions', 0)) {
// 			if ($this->_showreport) {
// 				$count = 0;
// 				$body = preg_replace('#(jQuery|\$)\(window\).on\(\'load\',[\s]*?function\(\)[\s]*?{[\s]*?}\);#', '', $body, -1, $count);
// 				if ($count > 0) {
// 					$this->_verbose_array[] = Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYSCRIPTJQUERYON');
// 				}
// 			} else { // faster
// 				$body = preg_replace('#(jQuery|\$)\(window\).on\(\'load\',[\s]*?function\(\)[\s]*?{[\s]*?}\);#', '', $body, 1);
// 			}
// 		}

		// all scripts and stylesheets are added here instead of earlier so they don't get checked by the plugin

		if (!empty($this->_supplement_scripts)) {
			foreach($this->_supplement_scripts as $path) {
				$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)ADD_SCRIPT_HERE#', $path, $body, 1);
				if ($this->_showreport) {
					$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPT', $path);
				}
			}
		}

		$javascript_declaration = trim( (string) $this->params->get('addjavascriptdeclaration'.$suffix, ''));
		if (!empty($javascript_declaration)) {
			$body = preg_replace('#ADD_SCRIPT_DECLARATION_HERE#', $javascript_declaration, $body, 1);
			if ($this->_showreport) {
				$lines = array_map('trim', (array) explode("\n", $javascript_declaration));
				$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPTDECLARATION', $lines[0]);
			}
		}

		if (!empty($this->_supplement_stylesheets)) {
			foreach($this->_supplement_stylesheets as $path) {
				$body = preg_replace('#([\\\/a-zA-Z0-9_:\.~-]*)ADD_STYLESHEET_HERE#', $path, $body, 1);
				if ($this->_showreport) {
					$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEET', $path);
				}
			}
		}

		$css_declaration = trim( (string) $this->params->get('addcssdeclaration'.$suffix, ''));
		if (!empty($css_declaration)) {
			$body = preg_replace('#ADD_STYLESHEET_DECLARATION_HERE#', $css_declaration, $body, 1);
			if ($this->_showreport) {
				$lines = array_map('trim', (array) explode("\n", $css_declaration));
				$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEETDECLARATION', $lines[0]);
			}
		}

		if ($this->params->get('removeblanklines'.$suffix, 0)) {
			$count = 0;
			$body = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $body, -1, $count); // gets all of the empty lines in the source and replaces them with a simple carriage return to preserve the content structure.
			if ($count > 0 && $this->_showreport) {
				$this->_verbose_array[] = Text::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEBLANKLINES', $count);
			}
		}

		$time_end = microtime(true);
		$this->_timeafterrender = $time_end - $time_start;

		// show the report

		if ($this->_showreport) {
			$body = self::addReport($body, $this->_verbose_array, $this->_timeafterroute + $this->_timebeforerender + $this->_timeafterrender);
		}

		$this->app->setBody($body);

		return true;
	}

	static protected function addReport($body, $comments = array(), $execution_time = 0)
	{
		$replacement = array();

		$replacement[] = '<style type="text/css">#jqueryeasy_report code { white-space: normal; word-break: break-all; }</style>'.chr(13);

		$replacement[] = '<div id="jqueryeasy_report" style="z-index: 10000; display: block; overflow: hidden; position: fixed; top: 10px; left: 0; right: 0; width: 90%; max-width: 976px; margin: 0 auto; padding: 8px; box-sizing: border-box;">';

		$replacement[] = '<div style="position: relative; overflow: hidden; max-width: 960px; margin: 0 auto; border-radius: 4px; box-shadow: 0px 0px 8px #000; background: #fff; background: rgba(255, 255, 255, .9);">';

		// header

		$replacement[] = '<div style="position: relative; display: table; width: 100%; background-color: #d1ecf1; border-bottom: 1px dashed #0c5460;">';

		$replacement[] = '<span style="display: table-cell; padding: 5px 10px; color: #0c5460; font-weight: bold; font-size: 12px;">'.Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_JQUERYEASY').'</span>';

		$replacement[] = '<a href="" onclick="document.getElementById(\'jqueryeasy_report\').style.display = \'none\'; return false;" style="display: table-cell; text-align: right; font-size: 12px; padding: 5px 10px;">'.Text::_('JCANCEL').'</a>';

		$replacement[] = '</div>';

		// end header

		$replacement[] = '<dl style="padding: 0; margin: 15px; overflow: auto; max-height: 200px; max-height: 50vh">';

		$replacement[] = '<dt style="position: absolute; top: -9999px; left: -9999px;">'.Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_JQUERYEASY').'</dt>';

		if (!empty($comments)) {
			foreach ($comments as $comment) {

				switch (substr($comment, 0, 3)) {
					case 'INF': $color = '#0c5460'; $bgcolor = '#d1ecf1'; $label = '<span class="badge" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>'; break;
					case 'DEL': $color = '#856404'; $bgcolor = '#fff3cd'; $label = '<span class="badge" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>'; break;
					case 'ERR': $color = '#721c24'; $bgcolor = '#f8d7da'; $label = '<span class="badge" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>'; break;
					case 'ADD': $color = '#155724'; $bgcolor = '#d4edda'; $label = '<span class="badge" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>'; break;
					default: $color = '#1b1e21'; $bgcolor = '#d6d8d9'; $label = '<span class="badge" style="display: inline-block; background-color: '.$bgcolor.'; width: 15px; margin: 1px 5px 1px 0;">&nbsp;</span>'; break;
				}

				$replacement[] = '<dd style="color: '.$color.'; margin-bottom: 6px;">'.$label.substr($comment, 4).'</dd>';
			}
		} else {
			$replacement[] = '<dd>'.Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_NOCHANGESMADE').'</dd>';
		}

		$replacement[] = '</dl>';

		// footer

		$replacement[] = '<div style="position: relative; display: table; width: 100%; background-color: #d1ecf1; border-top: 1px dashed #0c5460;">';

		$replacement[] = '<span style="display: table-cell; padding: 5px 10px; color: #0c5460; font-size: 12px;">'.Text::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_EXECUTIONTIME').': '.number_format($execution_time, 4).'</span>';

		$replacement[] = '</div>';

		// end footer

		$replacement[] = '</div>';

		$replacement[] = '</div>';

		return preg_replace('#</body>#', implode('', $replacement).chr(13).'</body>', $body, 1);
	}

	static protected function addScript($url, $useversion = false, $type = 'text/javascript', $defer = false, $async = false)
	{
		$options = array();
		$attributes = array();

		if ($useversion) {
			$options['version'] = 'auto';
		}

		$attributes['defer'] = $defer;
		$attributes['async'] = $async;
		$attributes['type'] = $type;

		Factory::getDocument()->addScript($url, $options, $attributes);
	}

	static protected function addStyleSheet($url, $useversion = false, $type = 'text/css', $media = null, $attribs = array())
	{
		$options = array();
		$attributes = array();

		if ($useversion) {
			$options['version'] = 'auto';
		}

		$attributes['type'] = $type;
		if (isset($media)) {
			$attributes['media'] = $media;
		}
		$attributes = array_replace($attributes, $attribs);

		Factory::getDocument()->addStyleSheet($url, $options, $attributes);
	}

	static protected function path_compare($uri, $path, $use_backward_compatibility = false)
	{
		$first_pos = (strpos($path, '*') === 0) ? true: false;
		$last_pos = (strrpos($path, '*') === (strlen($path) - 1)) ? true: false;

		if (($first_pos && $last_pos && !$use_backward_compatibility) || ($first_pos && $use_backward_compatibility)) { // any URL containing $path
			$path = trim($path, '*');
			if (stripos($uri, $path) !== false) {
				return true;
			}
		} else if ($first_pos && !$last_pos && !$use_backward_compatibility) { // any URL ending with $path
			$path = ltrim($path, '*');
			$path_length = strlen($path);
			$uri_tip = substr($uri, -$path_length);
			if (strcasecmp($uri_tip, $path) == 0) { // compare end of URI with $path
				return true;
			}
		} else if (!$first_pos && $last_pos && !$use_backward_compatibility) { // any URL starting with $path
			$path = rtrim($path, '*');
			if (stripos($uri, URI::root().ltrim($path, '/')) !== false) {
				return true;
			}
		} else {
			if (strcasecmp($uri, URI::root().ltrim($path, '/')) == 0) { // case-insensitive string comparison
				return true;
			}
		}

		return false;
	}

}