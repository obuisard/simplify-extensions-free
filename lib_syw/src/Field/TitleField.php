<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class TitleField extends FormField
{
	public $type = 'Title';

	protected $title;
	protected $image_src;
	protected $icon;
	protected $color;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$html = '';
		
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$inline_style = array();

		$html .= '<h2 class="syw_header syw_title" style="'.implode($inline_style).'">';

		if ($this->image_src) {
			$alt_attribute = '';
			if ($this->title) {
				$alt_attribute = ' alt="' . Text::_($this->title) . '"';
			}
			$html .= '<img style="margin: -1px 4px 0 0; padding: 0; width: 24px; height: 24px" src="'.$this->image_src.'"' . $alt_attribute . '>';
		} else if ($this->icon) {
		    $wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);
		    
			$html .= '<i style="margin: -1px 4px 0 0; font-size: inherit; vertical-align: baseline" class="SYWicon-'.$this->icon.'" aria-hidden="true"></i>';
		}

		if ($this->title) {
			$html .= '<span>'.Text::_($this->title).'</span>';
		}

		$html .= '</h2>';

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->title = isset($this->element['title']) ? trim((string)$this->element['title']) : '';
			$this->image_src = isset($this->element['imagesrc']) ? (string)$this->element['imagesrc'] : ''; // ex: ../modules/mod_latestnews/images/icon.png (16x16)
			$this->icon = isset($this->element['icon']) ? (string)$this->element['icon'] : ''; // ex: thumb-up
			$this->color = '#6f6f6f'; // isset($this->element['color']) ? $this->element['color'] : '#6f6f6f';
		}

		return $return;
	}

}
?>