<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class ExtensionVersionField extends FormField
{
	public $type = 'ExtensionVersion';

	protected $version;

	protected function getLabel()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$html = '';

		$html .= '<div style="clear: both;">'.Text::_('LIB_SYW_EXTENSIONVERSION_VERSION_LABEL').'</div>';

		return $html;
	}

	protected function getInput()
	{
		$html = '<div style="padding-top: 5px; overflow: inherit">';

		//$version = strval(simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_trombinoscopeextended/trombinoscopeextended.xml')->version);

		$html .= '<span class="badge bg-dark">'.$this->version.'</span>';

		$html .= '</div>';

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->version = isset($this->element['version']) ? (string)$this->element['version'] : '';
		}

		return $return;
	}

}
?>
