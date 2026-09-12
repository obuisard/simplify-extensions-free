<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class SubtitleField extends FormField
{
	public $type = 'Subtitle';

	protected $title;
	protected $color;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$html = '';

		$inline_style = array();

		$html .= '<h3 class="syw_header syw_subtitle" style="'.implode($inline_style).'">';

		if ($this->title) {

			$inline_style = array();

			$html .= '<span style=\''.implode($inline_style).'\'>'.Text::_($this->title).'</span>';
		}

		$html .= '</h3>';

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->title = isset($this->element['title']) ? trim((string)$this->element['title']) : '';
			$this->color = '#6f6f6f'; // isset($this->element['color']) ? $this->element['color'] : '#6f6f6f';
		}

		return $return;
	}

}
?>