<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;

/*
 * Checks if the plugin is enabled and report on the position used
 */
class JFormFieldJCHOptimizeTest extends FormField 
{	
	public $type = 'JCHOptimizetest';
	
	protected function getLabel()
	{
		return '';
	}
	
	protected function getInput()
	{		
		$html = '';
		
		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		if (PluginHelper::isEnabled('system', 'jch_optimize')) {
			
			$plugin = PluginHelper::getPlugin('system', 'jch_optimize');
			
			$registry = new Registry;
			$registry->loadString($plugin->params);
			
			$use_file_combination = $registry->get('combine_files_enable', true);
			
			$html .= '<div class="alert alert-warning" style="margin-bottom: 0">';
			if ($use_file_combination) {
				$html .= '<span>'.\JText::_('PLG_SYSTEM_JQUERYEASY_WARNING_JCHOPTIMIZEENABLED').'</span><br />';
			}
			$html .= '</div>';
		} 
		
		return $html;
	}
	
}
?>