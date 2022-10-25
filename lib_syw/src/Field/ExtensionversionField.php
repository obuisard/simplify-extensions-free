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

class ExtensionversionField extends FormField
{
	public $type = 'Extensionversion';

	protected $version;
	protected $extension;

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

		if ($this->version) {
			$html .= '<span class="badge bg-dark">'.$this->version.'</span>';
		} else if ($this->extension) {
			$extension_parts = explode('_', $this->extension);
			$path = '';
			switch ($extension_parts[0]) {
				case 'plg': $path = JPATH_SITE . '/plugins/' . $extension_parts[1] . '/' . $extension_parts[2] . '/' . $extension_parts[2] . '.xml'; break;
				case 'com': $path = JPATH_ADMINISTRATOR . '/components/' . $this->extension . '/' . $extension_parts[1]. '.xml'; break;
				//case 'lib': $path = JPATH_SITE . '/libraries/' . $extension_parts[1] . '/' . $extension_parts[1]. '.xml'; break;
				case 'tpl': $path = JPATH_SITE . '/templates/' . $extension_parts[1] . '/templateDetails.xml'; break;
				default: $path = JPATH_SITE . '/modules/' . $this->extension . '/' . $this->extension . '.xml';
			}
			
			if ($path) {
				$html .= '<span class="badge bg-dark">' . strval(simplexml_load_file($path)->version) . '</span>';
			}
		}

		$html .= '</div>';

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->version = isset($this->element['version']) ? (string)$this->element['version'] : '';
			$this->extension = isset($this->element['extension']) ? (string)$this->element['extension'] : '';
		}

		return $return;
	}

}
?>
