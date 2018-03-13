<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined( '_JEXEC' ) or die;

JFormHelper::loadFieldClass('list');

class JFormFieldMigrateSelect extends JFormFieldList
{
	public $type = 'MigrateSelect';

	protected function getOptions() 
	{
		$options = array();
		
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		//$options[] = JHTML::_('select.option', '3.1', '3.1.x', 'value', 'text', $disable = false);
		$options[] = JHTML::_('select.option', '3.0.0', '3.0.x', 'value', 'text', $disable = false); // 3.0.0 kept for backward compatibility
		$options[] = JHTML::_('select.option', '1.4.1', '1.4.1', 'value', 'text', $disable = false);
		$options[] = JHTML::_('select.option', '1.3.0', '1.3.0', 'value', 'text', $disable = false);
		$options[] = JHTML::_('select.option', '1.2.1', '1.2.1', 'value', 'text', $disable = false);
		$options[] = JHTML::_('select.option', 'local', JText::_('PLG_SYSTEM_JQUERYEASY_VALUE_LOCAL'), 'value', 'text', $disable = false);
		
		if (version_compare(JVERSION, '3.2', 'ge')) {
			$options[] = JHTML::_('select.option', 'joomla', JText::_('PLG_SYSTEM_JQUERYEASY_VALUE_JOOMLA'), 'value', 'text', $disable = false);
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
?>