<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class SywonlinehelpField extends FormField
{
	protected $type = 'Sywonlinehelp';

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
	    $wam = Factory::getApplication()->getDocument()->getWebAssetManager();
	    
	    $wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);

		$html = array();

		$html[] = !empty($this->title) ? '<'.$this->heading.'>'.Text::_($this->title).'</'.$this->heading.'>' : '';

		$html[] = '<table style="width: 100%"><tr>';
		$html[] = !empty($this->syw_description) ? '<td style="background-color: transparent">'.Text::_($this->syw_description).'</td>' : '';
		if ($this->url) {
			$html[] = '<td style="text-align: right; background-color: transparent">';
			$html[] = '<a href="'.$this->url.'" target="_blank" class="btn btn-info btn-sm"><i class="SYWicon-local-library" aria-hidden="true"></i> <span>'.Text::_('JHELP').'</span></a>';
			$html[] = '</td>';
		}
		$html[] = '</tr></table>';

		return '<div class="syw_help'.$this->layer_class.'" style="margin: 0">'.implode($html).'</div>';
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->title = !empty($this->element['label']) ? (string)$this->element['label'] : (isset($this->element['title']) ? (string)$this->element['title'] : '');
			$this->syw_description= isset($this->element['sywdescription']) ? (string)$this->element['sywdescription'] : '';
			$this->heading = isset($this->element['heading']) ? (string)$this->element['heading'] : 'h4';
			$this->layer_class = isset($this->class) ? ' '.$this->class : (isset($this->element['class']) ? ' ' . ((string)$this->element['class']) : '');
			$this->url = isset($this->element['url']) ? (string)$this->element['url'] : '';
		}

		return $return;
	}

}
