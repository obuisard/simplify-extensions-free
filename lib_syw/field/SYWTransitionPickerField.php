<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class SYWTransitionPickerField extends FormField 
{		
	public $type = 'SYWTransitionPicker';

	protected $use_global;
	protected $transitions;
	protected $transitiongroups;
	protected $icon;
	protected $help;
	protected $sampleimage;
	protected $sampleicon;
		
	protected function getTransitionGroup($transitiongroup, $image, $icon) 
	{
		$transitions = array();
		
		switch ($transitiongroup) {
			case '2d':
				$transitions[] = 'hvr-grow';
				$transitions[] = 'hvr-shrink';
				$transitions[] = 'hvr-pulse';
				$transitions[] = 'hvr-pulse-grow';
				$transitions[] = 'hvr-pulse-shrink';
				$transitions[] = 'hvr-push';
				$transitions[] = 'hvr-pop';
				$transitions[] = 'hvr-bounce-in';
				$transitions[] = 'hvr-bounce-out';
				$transitions[] = 'hvr-rotate';
				$transitions[] = 'hvr-grow-rotate';
				$transitions[] = 'hvr-wobble-vertical';
				$transitions[] = 'hvr-wobble-horizontal';
				$transitions[] = 'hvr-buzz';
				$transitions[] = 'hvr-buzz-out';
			break;
			case 'background':
				$transitions[] = 'hvr-fade';
				$transitions[] = 'hvr-back-pulse';
				$transitions[] = 'hvr-sweep-to-right';
				$transitions[] = 'hvr-sweep-to-left';
				$transitions[] = 'hvr-sweep-to-bottom';
				$transitions[] = 'hvr-sweep-to-top';
				$transitions[] = 'hvr-bounce-to-right';
				$transitions[] = 'hvr-bounce-to-left';
				$transitions[] = 'hvr-bounce-to-bottom';
				$transitions[] = 'hvr-bounce-to-top';
				$transitions[] = 'hvr-radial-in';
				$transitions[] = 'hvr-radial-out';
				$transitions[] = 'hvr-rectangle-in';
				$transitions[] = 'hvr-rectangle-out';
				$transitions[] = 'hvr-shutter-in-horizontal';
				$transitions[] = 'hvr-shutter-out-horizontal';
				$transitions[] = 'hvr-shutter-in-vertical';
				$transitions[] = 'hvr-shutter-out-vertical';
			break;			
		}
		
		$transitionlist = '';
		foreach ($transitions as $transition_item) {
			$transition_item = str_replace('hvr-', '', $transition_item);
			$transitionlist .= '<li style="width: auto; float: left; margin: 4px;" data-transition="'.$transition_item.'">';
			$transitionlist .= '<a href="#" class="label hvr-'.$transition_item.'" style="padding: 8px; color: #fff; font-size: 1em" title="'.$transition_item.'" onclick="return false;">';
			
			if (!empty($image)) {
				$transitionlist .= '<img src="'.URI::root().$image.'" alt="'.$transition_item.'" title="'.$transition_item.'">';
			} else if (!empty($icon)) {
				$transitionlist .= '<i class="'.$icon.'" style="font-size: 2.4em" title="'.$transition_item.'"></i>';
			} else {
				$transitionlist .= $transition_item;
			}
					
			$transitionlist .= '</a>';
			$transitionlist .= '</li>';
		}
		
		return $transitionlist;
	}
	
	protected function getTransitions($image, $icon) 
	{		
	    $li_transitions = '';
	    
	    $transitiongrouplist = array('2d', 'background');
			
	    foreach ($transitiongrouplist as $i => $transitiongrouplist_item) {
			$li_transitions .= self::getTransitionGroup($transitiongrouplist_item, $image, $icon);
			if ($i < count($transitiongrouplist) - 1) {
				$li_transitions .= '<li class="divider" style="clear: both; width: auto;"></li>';
			}
		}
		
		return $li_transitions;
	}
	
	protected function getInput() 
	{		
		$doc = Factory::getDocument();	
		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		\JHtml::_('bootstrap.tooltip');
		
		\JHtml::_('stylesheet', 'syw/fonts-min.css', false, true);
		if (isset($this->transitions) || (isset($this->transitiongroups) && strpos($this->transitiongroups, '2d') !== false) || (!isset($this->transitions) && !isset($this->transitiongroups))) {
		    \JHtml::_('stylesheet', 'syw/2d-transitions-min.css', false, true);
		}
		if (isset($this->transitions) || (isset($this->transitiongroups) && strpos($this->transitiongroups, 'background') !== false) || (!isset($this->transitions) && !isset($this->transitiongroups))) {
		    \JHtml::_('stylesheet', 'syw/bg-transitions-min.css', false, true);
		}
		
		$script = 'jQuery(document).ready(function () {';
		
		// after load, select the saved value
		$script .= '    if (jQuery("#'.$this->id.'").val() == "") {';
		$script .= '         jQuery("#'.$this->id.'_disabled").val("");';
		$script .= '    }';
		$script .= '    if (jQuery("#'.$this->id.'").val() == "none") {';
		$script .= '         jQuery("#'.$this->id.'_disabled").val("'.\JText::_('JNONE').'");';
		$script .= '    }';
		$script .= '    if (jQuery("#'.$this->id.'").val() != "" && jQuery("#'.$this->id.'").val() != "none") {';
		$script .= '         jQuery("#'.$this->id.'_disabled").val(jQuery("#'.$this->id.'").val());';
		$script .= '         jQuery("#'.$this->id.'_select li a").each(function() {';
		$script .= '             if (jQuery(this).parent().attr(\'data-transition\') == jQuery("#'.$this->id.'").val()) {';
		$script .= '                  jQuery(this).addClass("label-success");';
		$script .= '             }';
		$script .= '         });';
		$script .= '    }';		
		
		//$script .= '    jQuery("#'.$this->id.'_select li a").hover(function() { jQuery(this).addClass("label-warning") }, function() { jQuery(this).removeClass("label-warning") });';
		
		$script .= '    jQuery("#'.$this->id.'_select li").click(function() {';		
		// de-select the previous value
		$script .= '         jQuery("#'.$this->id.'_select li a").each(function() {';
		$script .= '              jQuery(this).removeClass("label-success");';
		$script .= '         });';
		//
		$script .= '         jQuery("#'.$this->id.'").val(jQuery(this).attr(\'data-transition\'));';
		$script .= '         jQuery("#'.$this->id.'_disabled").val(jQuery(this).attr(\'data-transition\'));';
		if ($this->use_global) {
			$script .= '         jQuery("#'.$this->id.'_global").removeClass("btn-primary");';
			$script .= '         jQuery("#'.$this->id.'_global").removeClass("active");';
		}
		$script .= '         jQuery(this).children(":first").addClass("label-success");';
		//$script .= '         jQuery(this).children(":first").removeClass("label-inverse");';
		$script .= '    });';
		$script .= '    jQuery("#'.$this->id.'_none").click(function() {';
		$script .= '         jQuery("#'.$this->id.'").val("none");';
		$script .= '         jQuery("#'.$this->id.'_disabled").val("'.\JText::_('JNONE').'");';
		if ($this->use_global) {
			$script .= '         jQuery("#'.$this->id.'_global").removeClass("btn-primary");';
			$script .= '         jQuery("#'.$this->id.'_global").removeClass("active");';
		}
		$script .= '         jQuery("#'.$this->id.'_select li a").removeClass("label-success");';
		//$script .= '         jQuery("#'.$this->id.'_select li a").addClass("label-inverse");';
		$script .= '    });';		
		if ($this->use_global) {
			$script .= '    jQuery("#'.$this->id.'_global").click(function() {';
			$script .= '         jQuery("#'.$this->id.'").val("");';
			$script .= '         jQuery("#'.$this->id.'_disabled").val("");';
			$script .= '         jQuery("#'.$this->id.'_global").addClass("btn-primary");';
			$script .= '         jQuery("#'.$this->id.'_global").addClass("active");';
			$script .= '         jQuery("#'.$this->id.'_select li a").removeClass("label-success");';
			//$script .= '         jQuery("#'.$this->id.'_select li a").addClass("label-inverse");';		
			$script .= '    });';
		}
		$script .= '});';
		
		$doc->addScriptDeclaration($script);
					
		$html = '';
		
		$transition = '';
		if ($this->value != '') {
			$transition = $this->value;
		}
		
		$icon = isset($this->icon) ? $this->icon : 'SYWicon-stack-overflow';
		
		$html .= '<div class="input-prepend input-append">';		
		$html .= '    <div class="add-on"><i class="'.$icon.'"></i></div>';
		$html .= '    <input type="text" class="input-small" name="'.$this->name.'_disabled" id="'.$this->id.'_disabled"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" disabled="disabled" />';
		
		$html .= '    <input type="hidden" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';
				
		$html .= '    <div class="btn-group" style="display:inline-block;vertical-align:middle">';
		$html .= '        <button id="'.$this->id.'_caret" style="border-radius:0;margin-left:-1px;min-width:auto" class="btn dropdown-toggle hasTooltip" data-toggle="dropdown" title="' . \JText::_('LIB_SYW_TRANSITIONPICKER_SELECTTRANSITION') . '">';
		$html .= '            <span class="caret" style="margin-bottom:auto"></span>';
		$html .= '        </button>';
		$html .= '        <ul id="'.$this->id.'_select" class="dropdown-menu" style="min-width: 250px; max-height: 110px; overflow: auto">';
						
		if (isset($this->transitions)) {			
			$transitions = explode(",", $this->transitions);
			foreach ($transitions as $transition_item) {
				$transition_item = str_replace('hvr-', '', $transition_item); // just in case
				$html .= '<li style="width: auto; float: left; margin: 4px;" data-transition="'.$transition_item.'">';
				$html .= '<a href="#" class="label hvr-'.$transition_item.'" style="padding: 8px; color: #fff; font-size: 1em" title="'.$transition_item.'" onclick="return false;">';
			
				if (!empty($this->sampleimage)) {
					$html .= '<img src="'.URI::root().$this->sampleimage.'" alt="'.$transition_item.'" title="'.$transition_item.'">';
				} else if (!empty($this->sampleicon)) {
					$html .= '<i class="'.$this->sampleicon.'" style="font-size: 2.4em" title="'.$transition_item.'"></i>';
				} else {
					$html .= $transition_item;
				}
			
				$html .= '</a>';
				$html .= '</li>';
			}			
		} else if (isset($this->transitiongroups)) {
			$transitiongroups = explode(",", $this->transitiongroups);
			foreach ($transitiongroups as $i => $transitiongroup_item) {
				$html .= self::getTransitionGroup($transitiongroup_item, $this->sampleimage, $this->sampleicon);
				if ($i < count($transitiongroups) - 1) {
					$html .= '<li class="divider" style="clear: both; width: auto;"></li>';
				}
			}
		} else {
			$html .= self::getTransitions($this->sampleimage, $this->sampleicon); // TODO use jQuery append
		}		
		
		$html .= '        </ul>';
		$html .= '    </div>';
		
		if ($this->use_global) {
			$class = 'btn hasTooltip';
			if (empty($this->value)) {
				$class .= ' btn-primary active';
			}
			$html .= '    <a id="'.$this->id.'_global" class="'.$class.'" title="'.\JText::_('JGLOBAL_USE_GLOBAL').'" href="#" onclick="return false;"><span>'.\JText::_('JGLOBAL_USE_GLOBAL').'</span></a>';
		}
		
		$html .= '    <a id="'.$this->id.'_none" class="btn hasTooltip" title="' . \JText::_('JLIB_FORM_BUTTON_CLEAR') . '"' . ' href="#" onclick="return false;"><i class="icon-remove"></i></a>';		
		$html .= '</div>';
		
		if (isset($this->help)) {
			$html .= '<span class="help-block">'.\JText::_($this->help).'</span>';
		}
		
		return $html;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->use_global = ($this->element['global'] == "true") ? true : false;
			$this->transitions = isset($this->element['transitions']) ? $this->element['transitions'] : null;
			$this->transitiongroups = isset($this->element['transitiongroups']) ? $this->element['transitiongroups'] : null;
			$this->icon = isset($this->element['icon']) ? $this->element['icon'] : null;
			$this->help = isset($this->element['help']) ? $this->element['help'] : null;
			$this->sampleimage = isset($this->element['sampleimage']) ? $this->element['sampleimage'] : null;
			$this->sampleicon = isset($this->element['sampleicon']) ? $this->element['sampleicon'] : null;
		}

		return $return;
	}

}
?>
