<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Plugin as SYWPLugin;

class SywimagefilterpickerField extends DynamicsingleselectField
{
	public $type = 'Sywimagefilterpicker';

	protected $filters;
	protected $extended;
	protected $gd_filters = array('blur', 'sharpen', 'sepia', 'grayscale', 'sketch', 'negate', 'emboss', 'edgedetect');
	protected $gd_filters_extended = array('duotone', 'brightness', 'contrast', 'pixelate');
	protected $css_filters = array('sepia', 'grayscale', 'negate');
	protected $css_filters_extended = array('blur', 'brightness', 'contrast', 'saturate', 'hue');
	protected $include_gd_filters;
	protected $include_css_filters;

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$path = URI::root(true).'/media/syw/images/filters/';

		if ($this->use_global) {

			$component  = Factory::getApplication()->getInput()->getCmd('option');
			if ($component == 'com_menus') { // we are in the context of a menu item
				$uri = new URI($this->form->getData()->get('link'));
				$component = $uri->getVar('option', 'com_menus');

				$config_params = ComponentHelper::getParams($component);

				$config_value = $config_params->get($this->fieldname);

				if (!is_null($config_value)) {
					$config_value = str_replace('_css', '', $config_value);
					$options[] = array('', Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', Text::_('LIB_SYW_IMAGEFILTERPICKER_'.strtoupper($config_value))), '', $path . $config_value . '.jpg', '');
				} else {
					$options[] = array('', Text::_('JGLOBAL_USE_GLOBAL'), '('.Text::_('LIB_SYW_GLOBAL_UNKNOWN').')', '', '');
				}
			} else {
				$options[] = array('', Text::_('JGLOBAL_USE_GLOBAL'), '('.Text::_('LIB_SYW_GLOBAL_UNKNOWN').')', '', '');
			}

			//$options[] = array('', Text::_('JGLOBAL_USE_GLOBAL'), '('.Text::_('LIB_SYW_GLOBAL_UNKNOWN').')', $path.'global.jpg');
		}

		$options[] = array('none', Text::_('LIB_SYW_IMAGEFILTERPICKER_ORIGINAL'), '', $path.'original.jpg');

		$filters = explode(',', $this->filters);

		$mixed_filters = $this->include_gd_filters && $this->include_css_filters;

		if ($this->extended) {
			$this->gd_filters = array_merge($this->gd_filters, $this->gd_filters_extended);
			$this->css_filters = array_merge($this->css_filters, $this->css_filters_extended);
		}

		if ($this->include_gd_filters) {
		    
		    $library_tag = SYWPLugin::getImageLibrary();
		    
			foreach ($filters as $filter) {
				if (in_array($filter, $this->gd_filters)) {
					if ($mixed_filters) {
					    $options[] = array($filter, Text::_('LIB_SYW_IMAGEFILTERPICKER_'.strtoupper($filter)), '', $path.$filter.'.jpg', '', false, $library_tag);
					} else {
						$options[] = array($filter, Text::_('LIB_SYW_IMAGEFILTERPICKER_'.strtoupper($filter)), '', $path.$filter.'.jpg');
					}
				}
			}
		}

		if ($this->include_css_filters) {
			foreach ($filters as $filter) {
				if (in_array($filter, $this->css_filters)) {
					if ($mixed_filters) {
						$options[] = array($filter . '_css', Text::_('LIB_SYW_IMAGEFILTERPICKER_'.strtoupper($filter)), '', $path.$filter.'.jpg', '', false, 'css');
					} else {
						$options[] = array($filter . '_css', Text::_('LIB_SYW_IMAGEFILTERPICKER_'.strtoupper($filter)), '', $path.$filter.'.jpg');
					}
				}
			}
		}

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 80;
			$this->height = 80;
			// 'extended' for filters requiring an additional input value
			$this->extended = isset($this->element['extended']) ? filter_var($this->element['extended'], FILTER_VALIDATE_BOOLEAN) : false;
			$this->include_gd_filters = isset($this->element['gdfilters']) ? filter_var($this->element['gdfilters'], FILTER_VALIDATE_BOOLEAN) : true;
			$this->include_css_filters = isset($this->element['cssfilters']) ? filter_var($this->element['cssfilters'], FILTER_VALIDATE_BOOLEAN) : false;
			$default_filters = 'sepia,grayscale,sketch,negate,emboss,edgedetect';
			$this->filters = isset($this->element['filters']) ? (string)$this->element['filters'] : ($this->extended ? $default_filters . ',blur,duotone,brightness,contrast,saturate,hue,pixelate' : $default_filters);
		}

		return $return;
	}
}
?>