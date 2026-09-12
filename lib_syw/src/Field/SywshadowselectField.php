<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class SywshadowselectField extends DynamicsingleselectField
{
    public $type = 'Sywshadowselect';

    protected $direction;
    protected $items;

    protected function getOptions()
    {
        $options = array();

        $lang = Factory::getLanguage();
        $lang->load('lib_syw.sys', JPATH_SITE);

        $imagefolder = URI::root(true) . '/media/syw/images/shadows/';

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

            $this->width = 90;
            $this->height = 90;

            $this->items = array();
            $this->items['none'] = array('label' => Text::_('JNONE'), 'image' => 'shadow_none');
            $this->items['ss'] = array('label' => Text::_('LIB_SYW_SHADOW_VALUE_SOFTSMALL'), 'image' => 'shadow_ss'); // soft small
            $this->items['sm'] = array('label' => Text::_('LIB_SYW_SHADOW_VALUE_SOFTMEDIUM'), 'image' => 'shadow_sm'); // soft medium
            $this->items['sl'] = array('label' => Text::_('LIB_SYW_SHADOW_VALUE_SOFTLARGE'), 'image' => 'shadow_sl'); // soft large
            $this->items['s'] = array('label' => Text::_('LIB_SYW_SHADOW_VALUE_SMALL'), 'image' => 'shadow_s'); // small
            $this->items['m'] = array('label' => Text::_('LIB_SYW_SHADOW_VALUE_MEDIUM'), 'image' => 'shadow_m'); // medium
            $this->items['l'] = array('label' => Text::_('LIB_SYW_SHADOW_VALUE_LARGE'), 'image' => 'shadow_l'); // large
        }

        return $return;
    }
}
?>