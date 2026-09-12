<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class SywalignmentselectField extends DynamicsingleselectField
{
	public $type = 'Sywalignmentselect';

    protected $items;
    
    /*
     * return the true CSS value rather than a short version of it
     */
    protected $true_value;
    
    /*
     * horizontal or vertical values
     */
    protected $direction;

    protected function getOptions()
    {
        $options = array();

        $lang = Factory::getLanguage();
        $lang->load('lib_syw.sys', JPATH_SITE);

        $imagefolder = URI::root(true) . '/media/syw/images/alignment/';

        if ($this->use_global) {
            
        	$component  = Factory::getApplication()->getInput()->getCmd('option');
        	if ($component == 'com_menus') { // we are in the context of a menu item
        		$uri = new URI($this->form->getData()->get('link'));
        		$component = $uri->getVar('option', 'com_menus');
        		
        		$config_params = ComponentHelper::getParams($component);
        		
        		$config_value = $config_params->get($this->fieldname);
        		
        		if (!is_null($config_value)) {
        			$options[] = array('', Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $this->items[$config_value]['label']), '', $imagefolder . $this->items[$config_value]['image'] . '.png', '');
        		} else {
        			$options[] = array('', Text::_('JGLOBAL_USE_GLOBAL'), '('.Text::_('LIB_SYW_GLOBAL_UNKNOWN').')', '', '');
        		}
        	} else {
        		$options[] = array('', Text::_('JGLOBAL_USE_GLOBAL'), '('.Text::_('LIB_SYW_GLOBAL_UNKNOWN').')', '', '');
        	}
        }
        
        foreach ($this->items as $key => $value) {
        	$options[] = array(($this->true_value ? $value['true_value'] : $key), $value['label'], '', $imagefolder . $value['image'] . '.png');
        }

        return $options;
    }

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
        	
        	$lang = Factory::getLanguage();
        	$lang->load('lib_syw.sys', JPATH_SITE);
        	
        	$this->true_value = isset($this->element['truevalue']) ? filter_var($this->element['truevalue'], FILTER_VALIDATE_BOOLEAN) : false;
        	
        	$this->direction = isset($this->element['direction']) ? (string)$this->element['direction'] : 'horizontal';
        	$remove_values = isset($this->element['removevalues']) ? explode(',', (string)$this->element['removevalues']) : array();
        	$this->width = 50;
        	$this->height = 50;

            $this->items = array();
            if ($this->direction === 'horizontal') {
                if (!in_array('s', $remove_values)) { $this->items['s'] = array('true_value' => 'stretch', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_STRETCH'), 'image' => 'valign_stretch'); }
                if (!in_array('fs', $remove_values)) { $this->items['fs'] = array('true_value' => 'start', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_START'), 'image' => 'valign_start'); }
                if (!in_array('c', $remove_values)) { $this->items['c'] = array('true_value' => 'center', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_CENTER'), 'image' => 'valign_center'); }
                if (!in_array('fe', $remove_values)) { $this->items['fe'] = array('true_value' => 'end', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_END'), 'image' => 'valign_end'); }
            } else {
            	if (!in_array('s', $remove_values)) { $this->items['s'] = array('true_value' => 'stretch', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_STRETCH'), 'image' => 'col_valign_stretch'); }
            	if (!in_array('fs', $remove_values)) { $this->items['fs'] = array('true_value' => 'start', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_START'), 'image' => 'col_valign_start'); }
            	if (!in_array('c', $remove_values)) { $this->items['c'] = array('true_value' => 'center', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_CENTER'), 'image' => 'col_valign_center'); }
            	if (!in_array('fe', $remove_values)) { $this->items['fe'] = array('true_value' => 'end', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_END'), 'image' => 'col_valign_end'); }
            }
        }

        return $return;
    }
}
?>