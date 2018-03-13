<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldSYWOnlineHelp extends JFormField
{
	protected $type = 'SYWOnlineHelp';

	protected $title;
	protected $heading;
	protected $layer_class;
	protected $url;
	
	protected function getLabel()
	{
		return '';
	}
	
	protected function getInput()
	{
		JHtml::_('script', 'syw_jqueryeasy/fields.js', false, true);
		JHtml::_('stylesheet', 'syw_jqueryeasy/fields.css', false, true);

		$html = array();

		$html[] = !empty($this->title) ? '<'.$this->heading.'>'.JText::_($this->title).'</'.$this->heading.'>' : '';

		$html[] = '<table style="width: 100%"><tr>';
		$html[] = !empty($this->description) ? '<td style="background-color: transparent">'.JText::_($this->description).'</td>' : '';
		if ($this->url) {
			$html[] = '<td style="text-align: right; background-color: transparent">';
			$html[] = '<a href="'.$this->url.'" target="_blank" class="btn btn-info btn-small"><img src="'.JURI::root().'plugins/system/jqueryeasy/images/local-library.png"> <span>'.JText::_('JHELP').'</span></a>';
			$html[] = '</td>';
		}
		$html[] = '</tr></table>';

		return '<div class="syw_help'.$this->layer_class.'" style="margin-bottom: 0">'.implode($html).'</div>';
	}

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->title = !empty($this->element['label']) ? $this->element['label'] : (isset($this->element['title']) ? $this->element['title'] : '');
			$this->heading = isset($this->element['heading']) ? $this->element['heading'] : 'h4';
			$this->layer_class = isset($this->class) ? ' '.$this->class : (isset($this->element['class']) ? ' '.$this->element['class']: '');
			$this->url = isset($this->element['url']) ? $this->element['url'] : '';
		}
		
		return $return;
	}

}
