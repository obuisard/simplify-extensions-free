<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;

FormHelper::loadFieldClass('list');

class SYWVerboseTextUnitsField extends \JFormFieldList
{
	protected $type = 'SYWVerboseTextUnits';
	
	protected $max;
	protected $min;
	protected $units;
	protected $default_unit;
	protected $icon;
	protected $help;
	protected $maxLength;
	
	protected $values = array();
	
	protected $forceMultiple = true;
	
	protected function getInput()
	{
		$html = '';
		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		$size = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$style = empty($size) ? '' : ' style="width:auto"';
		
		$min = isset($this->min) ? \JText::_('LIB_SYW_SYWVERBOSETEXT_MIN').': '.$this->min : '';
		$max = isset($this->max) ? \JText::_('LIB_SYW_SYWVERBOSETEXT_MAX').': '.$this->max : '';
		
		$range = (!empty($min) && !empty($max)) ? $min.' - '.$max : '';
		if (empty($range)) {
			$range = !empty($min) ? $min : '';
		}
		if (empty($range)) {
			$range = !empty($max) ? $max : '';
		}
		
		$hint = $this->translateHint ? \JText::_($this->hint) : $this->hint;
		$hint = $hint ? ' placeholder="'.$hint.'"' : (!empty($range) ? ' placeholder="'.$range.'"' : '');
		
		$overall_class = empty($this->icon) ? '' : 'input-prepend';
		$overall_class .= empty($this->units) ? '' : ' input-append';
		$overall_class = trim($overall_class);
		$overall_class = empty($overall_class) ? '' : ' class="'.$overall_class.'"';
		
		$html .= '<div'.$overall_class.'>';
		
		if ($this->icon) {
			\JHtml::_('stylesheet', 'syw/fonts-min.css', false, true);
			$html .= '<div class="add-on"><i class="'.$this->icon.'"></i></div>';
		}
		
		$this->values['value'] = $this->default;
		
		if (is_array($this->value)) {
			$this->values['value'] = $this->value[0];
		}
		
		$html .= '<input type="text" name="'.$this->name.'" value="'.htmlspecialchars($this->values['value'], ENT_COMPAT, 'UTF-8').'"'.$style.$size.$this->maxLength.$hint.' />';
		
		if ($this->units) {
			
			$unit_selection = explode(',', $this->units);
			
			if (count($unit_selection) == 1) {
				$html .= '<div class="add-on">'.$this->units.'</div>';
			} else {
				
				\JHtml::_('bootstrap.tooltip');
				
				$this->values['unit'] = $this->default_unit;
				if (is_array($this->value)) {
					$this->values['unit'] = $this->value[1];
				}
				
				$script = 'jQuery(document).ready(function () {';
					$script .= 'jQuery(\'.unit_'.$this->id.'\').click(function() { ';
						$script .= 'var unit = jQuery(this).text();';
						$script .= 'jQuery(\'#'.$this->id.'_unit\').val(unit);';
						$script .= 'jQuery(\'#'.$this->id.'_unit_text\').html(unit);';
					$script .= '}); ';
				$script .= '});';
				
				Factory::getDocument()->addScriptDeclaration($script);
				
				$html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'_unit" value="'.$this->values['unit'].'" size="3" />';
				
				$html .= '<div class="btn-group">';
				$html .= '<button class="btn dropdown-toggle hasTooltip" data-toggle="dropdown" title="'.\JText::_('LIB_SYW_VERBOSETEXT_UNIT').'">';
				$html .= '<span id="'.$this->id.'_unit_text">'.$this->values['unit'].'</span>&nbsp;';
				$html .= '<span class="caret" style="margin-bottom:auto"></span>';
				$html .= '</button>';
				$html .= '<ul class="dropdown-menu">';
				foreach ($unit_selection as $unit) {
					$html .= '<li><a class="unit_'.$this->id.'" href="#" onclick="return false;">'.$unit.'</a></li>';
				}
				$html .= '</ul>';
				$html .= '</div>';
			}
		}
		
		$html .= '</div>';
		
		if ($this->help) {
			$html .= '<span class="help-block">'.\JText::_($this->help).'</span>';
		}
		
		return $html;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->max = isset($this->element['max']) ? $this->element['max'] : null;
			$this->min = isset($this->element['min']) ? $this->element['min'] : null;
			$this->units = isset($this->element['units']) ? $this->element['units'] : '';
			$this->default_unit = isset($this->element['defaultunit']) ? $this->element['defaultunit'] : '';
			$this->help = isset($this->element['help']) ? $this->element['help'] : '';
			$this->icon = isset($this->element['icon']) ? $this->element['icon'] : '';
			$this->maxLength = isset($this->element['maxlength']) ? ' maxlength="' . $this->maxLength . '"' : '';
		}
		
		return $return;
	}
	
}
?>