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

class PhpextensiontestField extends FormField
{
	public $type = 'Phpextensiontest';

	protected $extension;
	protected $message;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$html = '';

		$extensions = get_loaded_extensions();

		if (!in_array($this->extension, $extensions)) {
			$html .= '<div style="margin: 0" class="alert alert-error">';
				if ($this->message) {
					$html .= '<span style="display: inline-block; padding-bottom: 10px">'. $this->message .'</span><br />';
				}
				$html .= '<span>'.Text::sprintf('LIB_SYW_PHPEXTENSION_NOTINSTALLED', $this->extension).'</span>';
			$html .= '</div>';
		} else {
			$html .= '<div style="margin: 0" class="alert alert-success">';
				if ($this->message) {
					$html .= '<span style="display: inline-block; padding-bottom: 10px">'. $this->message .'</span><br />';
				}
				$html .= '<span>'.Text::sprintf('LIB_SYW_PHPEXTENSION_INSTALLED', $this->extension).'</span>';
			$html .= '</div>';
		}

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->extension = isset($this->element['extension']) ? trim((string)$this->element['extension']) : '';
			$this->message = isset($this->element['message']) ? trim(Text::_((string)$this->element['message'])) : '';
		}

		return $return;
	}

}
?>
