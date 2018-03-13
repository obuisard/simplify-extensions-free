<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldLink extends JFormField 
{
	public $type = 'Link';
	
	protected $title;
	protected $text;
	protected $titleintext;
	protected $link;
	protected $image_src;

	protected function getLabel() 
	{		
		$html = '';
		
		JHtml::_('bootstrap.tooltip');
		
		$html .= '<div>';
		
		$html .= '<a href="'.$this->link.'" target="_blank" class="hasTooltip" title="'.JText::_($this->title).'">';
		if ($this->image_src) {
			$html .= '<img src="'.$this->image_src.'" alt="'.JText::_($this->title).'">';
		} else {
			$html .= JText::_($this->title);
		}
		$html .= '</a>';
		
		$html .= '</div>';
		
		return $html;
	}

	protected function getInput() 
	{		
		$html = '';
		
		$html .= '<div style="padding-top: 5px">';
			
		if ($this->titleintext) {
			$html .= '<strong>'.JText::_($this->title).'</strong>: ';
		}
				
		if ($this->text) {
			$html .= JText::sprintf($this->text, $this->link);
		}
		
		$html .= '</div>';

		return $html;
	}
	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->title = isset($this->element['title']) ? trim($this->element['title']) : '';
			$this->text = isset($this->element['text']) ? trim($this->element['text']) : '';
			$this->titleintext = isset($this->element['titleintext']) ? $this->element['titleintext'] : false;
			$this->link = isset($this->element['link']) ? $this->element['link'] : '';
			$this->image_src = isset($this->element['imagesrc']) ? $this->element['imagesrc'] : ''; // ex: ../modules/mod_latestnews/images/icon.png
		}
		
		return $return;
	}

}
?>