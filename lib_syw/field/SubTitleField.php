<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;

class SubTitleField extends FormField
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
		
		\JHtml::_('script', 'syw/fields.js', false, true);
		\JHtml::_('stylesheet', 'syw/fields.css', false, true);
		
		$inline_style = array();
		
		$inline_style[] = 'background: '.$this->color.'; background: linear-gradient(to right, '.$this->color.' 0%, #fff 100%); ';
		$inline_style[] = 'height: 5px; ';
		
		$html .= '<div class="syw_header syw_subtitle" style="'.implode($inline_style).'">';
		
		if ($this->title) {
			
			$inline_style = array();
			
			$inline_style[] = 'background-color: #fff; ';
			$inline_style[] = 'color: '.$this->color.'; ';
			
			$html .= '<div class="syw_subtitle_text" style=\''.implode($inline_style).'\'>'.\JText::_($this->title).'</div>';
		}
		
		$html .= '</div>';
		
		return $html;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
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