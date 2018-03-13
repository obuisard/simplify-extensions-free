<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldSubtitle extends JFormField
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
		
		JHtml::_('script', 'syw_jqueryeasy/fields.js', false, true);
		JHtml::_('stylesheet', 'syw_jqueryeasy/fields.css', false, true);

		$inline_style = array();

		$inline_style[] = 'display: inherit; ';
		$inline_style[] = 'position: relative; ';
		$inline_style[] = 'background: '.$this->color.'; background: linear-gradient(to right, '.$this->color.' 0%, #fff 100%); ';
		$inline_style[] = 'height: 5px; ';
		$inline_style[] = 'margin: 15px 0; ';

		$html .= '<div class="syw_header" style="'.implode($inline_style).'">';

		if ($this->title) {
				
			$inline_style = array();

			$inline_style[] = 'font-family: "Courier New", Courier, monospace; ';
			$inline_style[] = 'font-size: 10px; ';
			//$inline_style[] = 'font-weight: bold; ';
			$inline_style[] = 'letter-spacing: 2px; ';
			$inline_style[] = 'background-color: #fff; ';
			$inline_style[] = 'color: '.$this->color.'; ';
			$inline_style[] = 'padding: 0 8px 0 10px; ';
			$inline_style[] = 'position: absolute; ';
			$inline_style[] = 'left: 20px; ';
			$inline_style[] = 'top: -6px; ';
				
			$html .= '<div style=\''.implode($inline_style).'\'>'.JText::_($this->title).'</div>';
		}

		$html .= '</div>';

		return $html;
	}
	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->title = isset($this->element['title']) ? trim($this->element['title']) : '';
			$this->color = '#6f6f6f'; // isset($this->element['color']) ? $this->element['color'] : '#6f6f6f';
		}
		
		return $return;
	}

}
?>