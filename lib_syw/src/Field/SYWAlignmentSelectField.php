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
use SYW\Library\Field\DynamicSingleSelect;

class SYWAlignmentSelectField extends DynamicSingleSelect
{
	public $type = 'SYWAlignmentSelect';

	protected $direction;
    protected $items;

    protected function getOptions()
    {
        $options = array();

        $lang = Factory::getLanguage();
        $lang->load('lib_syw.sys', JPATH_SITE);

        $imagefolder = URI::root(true) . '/media/syw/images/alignment/';

        if ($this->use_global) {
            
        	$component  = Factory::getApplication()->input->getCmd('option');
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
        	$options[] = array($key, $value['label'], '', $imagefolder . $value['image'] . '.png');
        }

        return $options;
    }

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
        	
        	$lang = Factory::getLanguage();
        	$lang->load('lib_syw.sys', JPATH_SITE);
        	
        	$this->direction = isset($this->element['direction']) ? (string)$this->element['direction'] : 'horizontal';
        	$this->width = 50;
        	$this->height = 50;

            $this->items = array();
            if ($this->direction === 'horizontal') {
            	$this->items['fs'] = array('label' => Text::_('LIB_SYW_ALIGN_VALUE_START'), 'image' => 'valign_start');
            	$this->items['c'] = array('label' => Text::_('LIB_SYW_ALIGN_VALUE_CENTER'), 'image' => 'valign_center');
            	$this->items['fe'] = array('label' => Text::_('LIB_SYW_ALIGN_VALUE_END'), 'image' => 'valign_end');
            } else {
            	$this->items['s'] = array('label' => Text::_('LIB_SYW_ALIGN_VALUE_STRETCH'), 'image' => 'col_valign_stretch');
	            $this->items['fs'] = array('label' => Text::_('LIB_SYW_ALIGN_VALUE_START'), 'image' => 'col_valign_start');
	            $this->items['c'] = array('label' => Text::_('LIB_SYW_ALIGN_VALUE_CENTER'), 'image' => 'col_valign_center');
	            $this->items['fe'] = array('label' => Text::_('LIB_SYW_ALIGN_VALUE_END'), 'image' => 'col_valign_end');
            }
        }

        return $return;
    }
}
?>