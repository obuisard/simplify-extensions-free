<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class GlobalgroupedlistField extends GroupedlistField
{
	public $type = 'Globalgroupedlist';
	
	protected $use_global;

	protected function getGroups()
	{
	    $groups = array();
	    
	    $global_value = '';
	    
	    $parent_groups = parent::getGroups();
		
		if ($this->use_global) {
		    
		    $component  = Factory::getApplication()->getInput()->getCmd('option');
		    if ($component == 'com_menus') { // we are in the context of a menu item
		        $uri = new Uri($this->form->getData()->get('link'));
		        $component = $uri->getVar('option', 'com_menus');
		    }
		    
		    $params = ComponentHelper::getParams($component);
		    $value  = $params->get($this->fieldname);
		    
		    if (!is_null($value)) {
		        $value = (string) $value;
		        
		        foreach ($this->element->xpath('option') as $option) {
		            if (isset($option['value']) && (string) $option['value'] === $value) {
		                
		                $global_value = Text::_((string) $option);
		                
		                break;
		            }
		        }
		    }
		    
		    $tmp = HTMLHelper::_('select.option', '', $global_value ? Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $global_value) : Text::_('JGLOBAL_USE_GLOBAL'), 'value', 'text', false);
		    
		    array_unshift($parent_groups[0], $tmp);		    
		}

		// Merge any additional options in the XML definition.
		$groups = array_merge($parent_groups, $groups);

		return $groups;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
	    $return = parent::setup($element, $value, $group);
	    
	    if ($return) {
	        $this->use_global = ((string)$this->element['global'] == "true" || (string)$this->element['useglobal'] == "true") ? true : false;
	    }
	    
	    return $return;
	}
}
?>