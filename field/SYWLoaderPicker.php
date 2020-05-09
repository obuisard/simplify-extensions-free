<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

FormHelper::loadFieldClass('dynamicsingleselect');

class SYWLoaderPicker extends DynamicSingleSelect
{
	public $type = 'SYWLoaderPicker';

	protected $loaders;

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$path = URI::root(true).'/media/syw/images/loaders/';

// 		if ($this->use_global) {
// 			$options[] = array('', JText::_('JGLOBAL_USE_GLOBAL'), '('.JText::_('LIB_SYW_GLOBAL_UNKNOWN').')', $path.'global.jpg');
// 		}

		$options[] = array('default', Text::_('JDEFAULT'), '', $path.'spin.gif');

		$loaders = explode(',', $this->loaders);
		foreach ($loaders as $loader) {
			$options[] = array($loader, ucfirst($loader), '', $path.$loader.'.svg');
		}

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 80;
			$this->height = 80;
			$this->loaders = 'spin,disk,triangle';
		}

		return $return;
	}
}
?>