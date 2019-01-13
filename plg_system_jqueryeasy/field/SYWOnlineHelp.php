<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class JFormFieldSYWOnlineHelp extends FormField
{
	protected $type = 'SYWOnlineHelp';

	protected $title;
	protected $syw_description;
	protected $heading;
	protected $layer_class;
	protected $url;
		
	protected function getLabel()
	{
		return '';
	}
	
	protected function getInput()
	{
		HTMLHelper::_('script', 'syw_jqueryeasy/fields.js', false, true);
		HTMLHelper::_('stylesheet', 'syw_jqueryeasy/fields.css', false, true);

		$html = array();

		$html[] = !empty($this->title) ? '<'.$this->heading.'>'.Text::_($this->title).'</'.$this->heading.'>' : '';

		$html[] = '<table style="width: 100%"><tr>';
		$html[] = !empty($this->syw_description) ? '<td style="background-color: transparent">'.Text::_($this->syw_description).'</td>' : '';
		if ($this->url) {
			$html[] = '<td style="text-align: right; background-color: transparent">';
			$html[] = '<a href="'.$this->url.'" target="_blank" class="btn btn-info btn-sm"><img src="'.Uri::root().'plugins/system/jqueryeasy/images/local-library.png"> <span>'.Text::_('JHELP').'</span></a>';
			$html[] = '</td>';
		}
		$html[] = '</tr></table>';

		return '<div class="syw_help'.$this->layer_class.'" style="margin-bottom: 0">'.implode($html).'</div>';
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->title = !empty($this->element['label']) ? $this->element['label'] : (isset($this->element['title']) ? $this->element['title'] : '');
			$this->syw_description= isset($this->element['sywdescription']) ? $this->element['sywdescription'] : '';
			$this->heading = isset($this->element['heading']) ? $this->element['heading'] : 'h4';
			$this->layer_class = isset($this->class) ? ' '.$this->class : (isset($this->element['class']) ? ' '.$this->element['class']: '');
			$this->url = isset($this->element['url']) ? $this->element['url'] : '';
		}
		
		return $return;
	}

}
