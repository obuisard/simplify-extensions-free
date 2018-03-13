<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldExtensionTranslators extends JFormField 
{
	public $type = 'ExtensionTranslators';
	
	protected function getLabel() 
	{		
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		$html = '';
		
		$html .= '<div style="clear: both;">';
		if (!empty($this->translators)) {
			$html .= JText::_('PLG_SYSTEM_JQUERYEASY_TRANSLATORS_LABEL');
		}
		$html .= '</div>';
		
		return $html;
	}

	protected function getInput() 
	{		
		$html = '';
		
		if (!empty($this->translators)) {
			$html .= '<div style="padding-top: 5px; overflow: inherit">';
			$html .= $this->translators;
			$html .= '</div>';
		}
		
		return $html;
	}
	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
	
		if ($return) {
			$this->translators = isset($this->element['translators']) ? JText::_($this->element['translators']) : NULL;
		}
	
		return $return;
	}

}
?>
