<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

JFormHelper::loadFieldClass('dynamicsingleselectjqe');

class JFormFieldBootstrapSelect extends JFormFieldDynamicSingleSelectJQE
{
	public $type = 'BootstrapSelect';
	
	protected function getOptions()
	{
		$options = array();
		
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		$path = '/plugins/system/jqueryeasy';
	
		$options[] = array(0, JText::_('JNO'), '', JURI::root(true).$path.'/images/select_no.png');
		$options[] = array(1, JText::_('PLG_SYSTEM_JQUERYEASY_VALUE_BOOTSTRAP'), '', JURI::root(true).$path.'/images/select_bootstrap.png', '', 'disabled');
		
		return $options;
	}
		
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->width = 101;
			$this->height = 96;
		}
		
		return $return;
	}
}
?>