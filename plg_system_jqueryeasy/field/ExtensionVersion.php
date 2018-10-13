<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class JFormFieldExtensionVersion extends FormField 
{		
	public $type = 'ExtensionVersion';
	
	protected $version;

	protected function getLabel() 
	{		
		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		$html = '';
		
		$html .= '<div style="clear: both;">'.\JText::_('PLG_SYSTEM_JQUERYEASY_VERSION_LABEL').'</div>';
		
		return $html;
	}

	protected function getInput() 
	{
		$html = '<div style="padding-top: 5px; overflow: inherit">';
		
		$html .= '<span class="badge badge-secondary">'.$this->version.'</span>';
		
		$html .= '</div>';
		
		return $html;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
	
		if ($return) {
			$this->version = isset($this->element['version']) ? $this->element['version'] : '';
		}
	
		return $return;
	}

}
?>
