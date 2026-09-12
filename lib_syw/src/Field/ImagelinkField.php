<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class ImagelinkField extends FormField
{
	public $type = 'Imagelink';

	protected $title;
	protected $text;
	protected $titleintext;
	protected $link;
	protected $image_src;

	protected function getLabel()
	{
		$html = '';

		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

		$html .= '<div>';

		$html .= '<a href="'.$this->link.'" target="_blank" class="hasTooltip" title="'.Text::_($this->title).'">';
		if ($this->image_src) {
			$html .= '<img src="'.URI::root().$this->image_src.'" alt="'.Text::_($this->title).'">';
		} else {
			$html .= Text::_($this->title);
		}
		$html .= '</a>';

		$html .= '</div>';

		return $html;
	}

	protected function getInput()
	{
		$html = '';

		$html .= '<div style="padding-top: 5px; overflow: inherit">';

		if ($this->titleintext) {
			$html .= '<strong>'.Text::_($this->title).'</strong>: ';
		}

		if ($this->text) {
			$html .= Text::sprintf($this->text, $this->link);
		}

		$html .= '</div>';

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->title = isset($this->element['title']) ? trim((string)$this->element['title']) : '';
			$this->text = isset($this->element['text']) ? trim((string)$this->element['text']) : '';
			$this->titleintext = isset($this->element['titleintext']) ? filter_var($this->element['titleintext'], FILTER_VALIDATE_BOOLEAN) : false;
			$this->link = isset($this->element['link']) ? (string)$this->element['link'] : '';
			$this->image_src = isset($this->element['imagesrc']) ? (string)$this->element['imagesrc'] : ''; // ex: ../modules/mod_latestnews/images/icon.png
		}

		return $return;
	}

}
?>