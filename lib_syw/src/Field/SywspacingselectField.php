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

class SywspacingselectField extends DynamicsingleselectField
{
	public $type = 'Sywspacingselect';

	protected $items;    
	
	/*
	* return the true CSS value rather than a short version of it
	*/
	protected $true_value; 

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
        	
        	$this->true_value = isset($this->element['truevalue']) ? filter_var($this->element['truevalue'], FILTER_VALIDATE_BOOLEAN) : false;

        	$lang = Factory::getLanguage();
        	$lang->load('lib_syw.sys', JPATH_SITE);

            $this->width = 50;
            $this->height = 50;

            $this->items = array();
            $this->items['fs'] = array('true_value' => 'start', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_START'), 'image' => 'start');
            $this->items['c'] = array('true_value' => 'center', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_CENTER'), 'image' => 'center');
            $this->items['fe'] = array('true_value' => 'end', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_END'), 'image' => 'end');
            $this->items['sb'] = array('true_value' => 'space-between', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_SPACEBETWEEN'), 'image' => 'spacebetween');
            $this->items['sa'] = array('true_value' => 'space-around', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_SPACEAROUND'), 'image' => 'spacearound');
            $this->items['se'] = array('true_value' => 'space-evenly', 'label' => Text::_('LIB_SYW_ALIGN_VALUE_SPACEEVENLY'), 'image' => 'spaceevenly');
        }

        return $return;
    }
}
?>