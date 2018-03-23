<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class SYWColorPickerField extends FormField 
{		
	public $type = 'SYWColorPicker';
	
	protected $use_global;
	protected $allow_transparency;
	protected $icon;
	protected $help;
	
	protected function getInput() 
	{		
		$doc = Factory::getDocument();	
		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		\JHtml::_('bootstrap.tooltip');
					
		$html = '';
			
		$color = strtolower($this->value);
			
		if (!$color || in_array($color, array('none', 'transparent'))) {
			$color = '';
		} elseif ($color['0'] != '#') {
			$color = '#'.$color;
		}
		
		\JHtml::_('behavior.colorpicker');
		
		$icon = isset($this->icon) ? $this->icon : '';
		if (!empty($icon)) {
			\JHtml::_('stylesheet', 'syw/fonts-min.css', false, true);
		}
		
		$overall_class = empty($icon) ? '' : 'input-prepend';
		$overall_class .= ($this->allow_transparency || $this->use_global) ? ' input-append' : '';
		$overall_class = trim($overall_class);
		$overall_class = empty($overall_class) ? '' : ' class="'.$overall_class.'"';
			
		$html .= '<div'.$overall_class.'>';	
		
		if (!empty($icon)) {
			$html .= '<div class="add-on"><i class="'.$icon.'"></i></div>';
		}	

		if (!$this->allow_transparency && !$this->use_global) {
			$html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($color, ENT_COMPAT, 'UTF-8').'"'.' class="minicolors" />';
		} else {
			$disabled = '';
			if (empty($this->value) && $this->use_global) {
				$disabled = ' disabled';
			}
			
			$html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';
			$html .= '<input style="height:auto" type="text" name="visible_'.$this->name.'" id="visible_'.$this->id.'"'.' value="'.htmlspecialchars($color, ENT_COMPAT, 'UTF-8').'"'.' class="minicolors"'.$disabled.' />';
		}
		
		if ($this->use_global) {
			$class = 'btn hasTooltip';
			if (empty($this->value)) {
				$class .= ' btn-primary active';
			}
			$html .= '<a id="global_'.$this->id.'" class="'.$class.'" title="'.\JText::_('JGLOBAL_USE_GLOBAL').'" href="#" onclick="return false;">';
			$html .= '<span>'.\JText::_('JGLOBAL_USE_GLOBAL').'</span>';
			$html .= '</a>';
		}
			
		if ($this->allow_transparency) {
			$html .= '<a id="a_'.$this->id.'" class="btn hasTooltip" title="'.\JText::_('JLIB_FORM_BUTTON_CLEAR').'" href="#" onclick="return false;">';
			$html .= '<i class="icon-remove"></i>';
			$html .= '</a>';
		}
		
		$html .= '</div>';
		
		if ($this->help) {
			$html .= '<span class="help-block">'.\JText::_($this->help).'</span>';
		}
			
		if ($this->allow_transparency || $this->use_global) {
			$script = 'jQuery(document).ready(function (){';
			
			$script .= 'jQuery("#visible_'.$this->id.'").change(function() { jQuery("#'.$this->id.'").val(jQuery("#visible_'.$this->id.'").val()) });';
			$script .= 'jQuery("#visible_'.$this->id.'").parent().find("span").first().children(".minicolors-panel").click(function() { jQuery("#visible_'.$this->id.'").change() });';
			$script .= 'jQuery("#visible_'.$this->id.'").next(".minicolors-panel").mouseup(function() { setTimeout(function(){ jQuery("#'.$this->id.'").val(jQuery("#visible_'.$this->id.'").val());}, 500); });';
			
			if ($this->use_global) {
				$script .= 'jQuery("#global_'.$this->id.'").click(function() {';
				$script .= 'jQuery("#visible_'.$this->id.'").parent().find("span").first().children().css("background-color","transparent");';
				$script .= 'if (jQuery("#global_'.$this->id.'").hasClass("btn-primary")) { jQuery("#global_'.$this->id.'").removeClass("btn-primary") } else { jQuery("#global_'.$this->id.'").addClass("btn-primary"); }';
				$script .= 'if (jQuery("#global_'.$this->id.'").hasClass("active")) { jQuery("#global_'.$this->id.'").removeClass("active") } else { jQuery("#global_'.$this->id.'").addClass("active"); }';
				if ($this->allow_transparency) {
					$script .= 'if (jQuery("#global_'.$this->id.'").hasClass("btn-primary")) { jQuery("#visible_'.$this->id.'").val(""); jQuery("#'.$this->id.'").val(""); jQuery("#visible_'.$this->id.'").prop("disabled", true) } else { jQuery("#'.$this->id.'").val("transparent"); jQuery("#visible_'.$this->id.'").prop("disabled", false) }';
				} else {
					$script .= 'if (jQuery("#global_'.$this->id.'").hasClass("btn-primary")) { jQuery("#visible_'.$this->id.'").val(""); jQuery("#'.$this->id.'").val(""); jQuery("#visible_'.$this->id.'").prop("disabled", true) } else { jQuery("#visible_'.$this->id.'").val("#ffffff"); jQuery("#'.$this->id.'").val("#ffffff"); jQuery("#visible_'.$this->id.'").parent().find("span").first().children().css("background-color","#ffffff"); jQuery("#visible_'.$this->id.'").prop("disabled", false) }';
				}
				$script .= '});';
			}
			
			if ($this->allow_transparency) {
				$script .= 'jQuery("#a_'.$this->id.'").click(function() {';
				$script .= 'jQuery("#visible_'.$this->id.'").parent().find("span").first().children().css("background-color","transparent");';
				$script .= 'jQuery("#visible_'.$this->id.'").val(""); jQuery("#'.$this->id.'").val("transparent");';
				$script .= '});';
			}
			
			$script .= '});';
		
			$doc->addScriptDeclaration($script);
		} 
		
		return $html;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->use_global = ($this->element['global'] == "true") ? true : false;
			$this->allow_transparency = isset($this->element['transparency']) ? filter_var($this->element['transparency'], FILTER_VALIDATE_BOOLEAN) : false;
			$this->icon = isset($this->element['icon']) ? $this->element['icon'] : null;			
			$this->help = isset($this->element['help']) ? $this->element['help'] : '';
		}

		return $return;
	}

}
?>
