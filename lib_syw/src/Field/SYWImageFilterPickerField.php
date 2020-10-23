<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

FormHelper::loadFieldClass('dynamicsingleselect');

class SYWImageFilterPickerField extends DynamicSingleSelect
{
	public $type = 'SYWImageFilterPicker';
	
	protected $filters;

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$path = URI::root(true).'/media/syw/images/filters/';
		
		if ($this->use_global) {
			$options[] = array('', Text::_('JGLOBAL_USE_GLOBAL'), '('.Text::_('LIB_SYW_GLOBAL_UNKNOWN').')', $path.'global.jpg');
		}
		
		$options[] = array('none', Text::_('LIB_SYW_IMAGEFILTERPICKER_ORIGINAL'), '', $path.'original.jpg');
		
		$filters = explode(',', $this->filters);
		foreach ($filters as $filter) {
			$options[] = array($filter, Text::_('LIB_SYW_IMAGEFILTERPICKER_'.strtoupper($filter)), '', $path.$filter.'.jpg');
		}

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 80;
			$this->height = 80;
			$this->filters = isset($this->element['filters']) ? $this->element['filters'] : 'sepia,grayscale,sketch,negate,emboss,edgedetect';
		}

		return $return;
	}
}
?>