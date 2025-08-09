<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class DemotestField extends FormField
{
	public $type = 'Demotest';

	protected $demo_folder;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$html = '';

		if (is_dir($this->demo_folder)) {
			$html .= '<div style="margin: 0" class="alert alert-warning">';
				$html .= '<span style="text-transform: uppercase;">'.Text::_('LIB_SYW_DEMOTEST_THISISADEMO').'</span>';
			$html .= '</div>';
		}

		return $html;
	}

	public function renderField($options = array())
	{
		if (is_dir($this->demo_folder)) {
			return parent::renderField();
		}

		return '';
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->demo_folder = JPATH_ROOT . $this->element['demofolder'];
		}

		return $return;
	}

}
?>