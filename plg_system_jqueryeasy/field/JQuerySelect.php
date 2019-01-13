<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

FormHelper::loadFieldClass('dynamicsingleselectjqe');

class JFormFieldJQuerySelect extends DynamicSingleSelectJQE
{
	public $type = 'jQuerySelect';

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		$path = '/plugins/system/jqueryeasy';
		
		$options[] = array(0, Text::_('JNO'), '', Uri::root(true).$path.'/images/select_no.png');
		$options[] = array(1, Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_JQUERY'), '', Uri::root(true).$path.'/images/select_jquery.png');
		$options[] = array(2, Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_JQUERYUI'), '', Uri::root(true).$path.'/images/select_jquery_ui.png');
		$options[] = array(3, Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_JQUERYMOBILE'), '', Uri::root(true).$path.'/images/select_jquery_mobile.png', '', 'disabled');

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 101;
			$this->height = 96;
		}

		return $return;
	}
}
?>