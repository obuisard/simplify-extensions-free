<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class SYWColorPickerField extends FormField 
{		
	public $type = 'SYWColorPicker';
	
	protected $use_global;
	protected $allow_transparency;
	protected $icon;
	protected $help;
	protected $rgba;
	
	protected function getInput() 
	{		
		$doc = Factory::getDocument();	
		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		HTMLHelper::_('bootstrap.tooltip');
					
		$html = '';
			
		$color = strtolower($this->value);
			
		if (!$color || in_array($color, array('none', 'transparent'))) {
			$color = '';
		} elseif (!$this->rgba && $color['0'] != '#') {
			$color = '#'.$color;
		}
		
		$direction = $lang->isRtl() ? ' dir="ltr" style="text-align:right"' : '';
		
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'vendor/minicolors/jquery.minicolors.min.js', ['version' => 'auto', 'relative' => true]);
		HTMLHelper::_('stylesheet', 'vendor/minicolors/jquery.minicolors.css', ['version' => 'auto', 'relative' => true]);
		HTMLHelper::_('script', 'system/fields/color-field-adv-init.min.js', ['version' => 'auto', 'relative' => true]);
		
		$icon = isset($this->icon) ? $this->icon : '';
		if (!empty($icon)) {
		    HTMLHelper::_('stylesheet', 'syw/fonts-min.css', ['version' => 'auto', 'relative' => true]);
		}
		
		if ($icon || $this->allow_transparency || $this->use_global) {
		    $html .= '<div class="input-group">';	
		} else {
		    $html .= '<div>';
		}
		
		if (!empty($icon)) {
			$html .= '<div class="input-group-prepend"><span class="input-group-text"><i class="'.$icon.'"></i></span></div>';
		}
		
		$data_rgba = '';
		if ($this->rgba) {
		    $data_rgba = ' data-format="rgba" style="width: auto"';
		}

		if (!$this->allow_transparency && !$this->use_global) {
		    $html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($color, ENT_COMPAT, 'UTF-8').'"'.' class="form-control minicolors"'.$direction.$data_rgba.' />';
		} else {
			$disabled = '';
			if (empty($this->value) && $this->use_global) {
				$disabled = ' disabled';
			}
			
			$html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';
			$html .= '<input style="height:auto" type="text" name="visible_'.$this->name.'" id="visible_'.$this->id.'"'.' value="'.htmlspecialchars($color, ENT_COMPAT, 'UTF-8').'"'.' class="form-control minicolors"'.$direction.$data_rgba.$disabled.' />';
		}

		if ($this->use_global || $this->allow_transparency) {
		    $html .= '<div class="input-group-append">';
		}
		
		if ($this->use_global) {
			$class = 'btn hasTooltip';
			if (empty($this->value)) {
				$class .= ' btn-primary active';
			}
			$html .= '<button type="button" id="global_'.$this->id.'" class="'.$class.'" title="'.Text::_('JGLOBAL_USE_GLOBAL').'><span>'.Text::_('JGLOBAL_USE_GLOBAL').'</span></button>';
		}
			
		if ($this->allow_transparency) {
			$html .= '<button type="button" id="a_'.$this->id.'" class="btn btn-secondary hasTooltip" title="'.Text::_('JLIB_FORM_BUTTON_CLEAR').'"><i class="icon-remove"></i></button>';
		}

		if ($this->use_global || $this->allow_transparency) {
		    $html .= '</div>';
		}
		
		$html .= '</div>';
		
		if ($this->help) {
			$html .= '<span class="help-block">'.Text::_($this->help).'</span>';
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
			$this->rgba = ($this->element['rgba'] == "true") ? true : false;
		}

		return $return;
	}

}
?>
