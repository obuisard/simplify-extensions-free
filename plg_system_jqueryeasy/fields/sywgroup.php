<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

class JFormFieldSYWGroup extends JFormField
{
	public $type = 'SYWGroup';
	
	protected $section;

	protected function getLabel()
	{
		return '';
	}
	
	protected function getInput()
	{
		$html = '';

// 		$class = '';
		
// 		$html .= '</div></div>';

// 		if ($this->section == 'begin') {
// 			$html .= '<div class="well well-small">';
// 			$html .= '<div'.$class.'>';
			
// 			if (isset($this->element['label'])) {
// 				$html .= '<h4>'.JText::_($this->element['label']);
// 			}
			
// 			if (isset($this->element['label'])) {
// 				$html .= '</h4>';
// 			}
			
// 			$html .= $this->description.'<br />';
			
// 			$html .= '</div><div>';
// 		} else if ($this->section == 'end') {
// 			$html .= '</div></div>';
// 		}

// 		$html .= '<div><div>';

		return $html;
	}
	
	/**
	* @since      3.2
	* @deprecated 3.2.3 Use renderField() instead
	*/
	public function getControlGroup()
	{		
		return '';
	}
	
	public function renderField($options = array())
	{		
		return '';
	}
	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			
			$this->section = isset($this->element['section']) ? $this->element['section'] : null;
			//$this->description = !empty($this->description) ? '<p><em>'.JText::_($this->description).'</em></p>' : '';
		}
		
		return $return;
	}

}
?>
