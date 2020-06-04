<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldTitle extends FormField
{
	public $type = 'Title';

	protected $title;
	protected $image_src;
	protected $color;
	
	protected function getLabel()
	{
		return '';
	}
	
	protected function getInput()
	{
		$html = '';
		
		HTMLHelper::_('script', 'syw_jqueryeasy/fields.js', false, true);
		HTMLHelper::_('stylesheet', 'syw_jqueryeasy/fields.css', false, true);

		$inline_style = array();

		$html .= '<h2 class="syw_header syw_title" style="'.implode($inline_style).'">';

		if ($this->image_src) {
		    $alt_attribute = '';
		    if ($this->title) {
		        $alt_attribute = ' alt="' . Text::_($this->title) . '"';
		    }
			$html .= '<img style="margin: -1px 4px 0 0; padding: 0; width: 24px; height: 24px" src="'.$this->image_src.'"' . $alt_attribute . '>';
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
			$this->title = isset($this->element['title']) ? trim($this->element['title']) : '';
			$this->image_src = isset($this->element['imagesrc']) ? $this->element['imagesrc'] : ''; // ex: ../modules/mod_latestnews/images/icon.png (16x16)
			$this->color = '#6f6f6f'; // isset($this->element['color']) ? $this->element['color'] : '#6f6f6f';
		}
		
		return $return;
	}

}
?>