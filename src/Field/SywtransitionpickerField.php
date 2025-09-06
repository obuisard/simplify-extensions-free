<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class SywtransitionpickerField extends FormField
{
	public $type = 'Sywtransitionpicker';
	
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
			$transitionlist .= '<li data-transition="'.$transition_item.'">';
			$transitionlist .= '<a href="#" class="dropdown-item hvr-'.$transition_item.'" style="display: inline-block; padding: 8px; font-size: 1em" aria-label="'.$transition_item.'" onclick="return false;">';
			
			if (!empty($image)) {
				$transitionlist .= '<img src="'.URI::root().$image.'" alt="'.$transition_item.'"><span style="margin-left: 10px">'.$transition_item.'</span>';
			} else if (!empty($icon)) {
				$transitionlist .= '<i class="'.$icon.'" aria-hidden="true" style="font-size: 2.4em"></i><span style="margin-left: 10px">'.$transition_item.'</span>';
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
				$li_transitions .= '<li><hr class="dropdown-divider"></li>';
			}
		}
		
		return $li_transitions;
	}
	
	protected function getInput()
	{
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();
		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
		HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');
		
		$wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);
		
		if (isset($this->transitions) || (isset($this->transitiongroups) && strpos($this->transitiongroups, '2d') !== false) || (!isset($this->transitions) && !isset($this->transitiongroups))) {
			$wam->registerAndUseStyle('syw.transitions.2d', 'syw/2d-transitions.min.css', ['relative' => true, 'version' => 'auto']);
		}
		
		if (isset($this->transitions) || (isset($this->transitiongroups) && strpos($this->transitiongroups, 'background') !== false) || (!isset($this->transitions) && !isset($this->transitiongroups))) {
			$wam->registerAndUseStyle('syw.transitions.bg', 'syw/bg-transitions.min.css', ['relative' => true, 'version' => 'auto']);
		}
		
		$global_value = '';
		
		if ($this->use_global) {
			$component  = Factory::getApplication()->getInput()->getCmd('option');
			if ($component == 'com_menus') { // we are in the context of a menu item
				$uri = new Uri($this->form->getData()->get('link'));
				$component = $uri->getVar('option', 'com_menus');
				
				$config_params = ComponentHelper::getParams($component);
				
				$config_value = $config_params->get($this->fieldname);
				
				if (!is_null($config_value)) {
					$global_value = $config_value;
					
					if ($global_value === 'none') {
						$global_value = Text::_('JNONE');
					}
				}
			}
		}
		
		$wam->addInlineScript('
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState == "complete") {
			
                    let select_' . $this->id . ' = document.getElementById("' . $this->id . '_select");
                    if (select_' . $this->id . ' != null) {
                        let options_' . $this->id . ' = select_' . $this->id . '.querySelectorAll("li[data-transition]");
                        let input_' . $this->id . ' = document.getElementById("' . $this->id . '");
                        let input_disabled_' . $this->id . ' = document.getElementById("' . $this->id . '_disabled");
			
						if (input_' . $this->id . '.value == "none") {
				 			input_disabled_' . $this->id . '.value = "' . Text::_('JNONE') . '";
				 			select_' . $this->id . '.querySelector(".none").querySelector(".dropdown-item").classList.add("active");
				 		} else if (input_' . $this->id . '.value != "") {
				 			input_disabled_' . $this->id . '.value = input_' . $this->id . '.value;
				 			let entry_value = select_' . $this->id . '.querySelector("li[data-transition=\'' . $this->value . '\']");
				 			entry_value.querySelector(".dropdown-item").classList.add("active");
				 		}
			
						options_' . $this->id . '.forEach (function (option) {
                            option.addEventListener("click", function(event) {
			
                                if (input_' . $this->id . '.value != "" && input_' . $this->id . '.value != "none") {
                                    let entry_value = select_' . $this->id . '.querySelector("li[data-transition=" + input_' . $this->id . '.value + "]");
                                    entry_value.querySelector(".dropdown-item").classList.remove("active");
                                }
								this.querySelector(".dropdown-item").classList.add("active");
			
								select_' . $this->id . '.querySelector(".none").querySelector(".dropdown-item").classList.remove("active");
			
                                let selected_transition = this.getAttribute("data-transition");
                                input_' . $this->id . '.value = selected_transition;
                                document.getElementById("' . $this->id . '_disabled").value = selected_transition;
                            });
                        });
			
						if (input_disabled_' . $this->id . '.classList.contains("useGlobal")) {
	                        document.getElementById("' . $this->id . '_clear").addEventListener("click", function(event) {
			
	                            if (input_' . $this->id . '.value != "" && input_' . $this->id . '.value != "none") {
	                                let entry_value = select_' . $this->id . '.querySelector("li[data-transition=" + input_' . $this->id . '.value + "]");
									entry_value.querySelector(".dropdown-item").classList.remove("active");
	                            }
			
								select_' . $this->id . '.querySelector(".none").querySelector(".dropdown-item").classList.remove("active");
			
	                            input_' . $this->id . '.value = "";
	                            input_disabled_' . $this->id . '.value = "";
			
								if (input_disabled_' . $this->id . '.classList.contains("useGlobal")) {
									input_disabled_' . $this->id . '.setAttribute("placeholder", "' . Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $global_value) . '");
								}
	                        });
						}
			
						select_' . $this->id . '.querySelector(".none").addEventListener("click", function(event) {
							this.querySelector(".dropdown-item").classList.add("active");
			
							if (input_' . $this->id . '.value != "" && input_' . $this->id . '.value != "none") {
								let entry_value = select_' . $this->id . '.querySelector("li[data-transition=" + input_' . $this->id . '.value + "]");
								entry_value.querySelector(".dropdown-item").classList.remove("active");
							}
			
							input_' . $this->id . '.value = "none";
							input_disabled_' . $this->id . '.value = "' . Text::_('JNONE') . '";
						});
                    }
                }
			});
		');
		
		$html = '';
		
		$icon_svg = '';
		if (!isset($this->icon)) {
			$icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" viewBox="0 0 24 24" style="width: 1em; fill: transparent"><path d="M15.8189 13.3287L10.4948 19.3183C9.69924 20.2134 8.30076 20.2134 7.50518 19.3183L2.18109 13.3287C1.50752 12.571 1.50752 11.429 2.18109 10.6713L7.50518 4.68167C8.30076 3.78664 9.69924 3.78664 10.4948 4.68167L15.8189 10.6713C16.4925 11.429 16.4925 12.571 15.8189 13.3287Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 6.375L13.5052 4.68167C14.3008 3.78664 15.6992 3.78664 16.4948 4.68167L21.8189 10.6713C22.4925 11.429 22.4925 12.571 21.8189 13.3287L16.4948 19.3183C15.6992 20.2134 14.3008 20.2134 13.5052 19.3183L12 17.625" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		}

		$placeholder = '';
		if (empty($this->value) && $this->use_global) {
			$placeholder = ' placeholder="' . Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $global_value) . '"';
		}
		
		$html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';
				
		$html .= '<div class="input-group">';
		
		if ($icon_svg) {
			$html .= '<span class="input-group-text">'.$icon_svg.'</span>';
		} else {
			$html .= '<span class="input-group-text"><i class="'.$this->icon.'" aria-hidden="true"></i></span>';
		}
		
		$html .= '<input type="text" class="form-control' . ($this->use_global ? ' useGlobal' : '') . '" name="'.$this->name.'_disabled" id="'.$this->id.'_disabled"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" disabled="disabled"' . $placeholder . ' />';
		
		$html .= '<button type="button" id="'.$this->id.'_caret" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="' . Text::_('LIB_SYW_TRANSITIONPICKER_SELECTTRANSITION') . '"></button>';
		
		$html .= '<ul id="'.$this->id.'_select" class="dropdown-menu dropdown-menu-end" aria-labelledby="'.$this->id.'_caret" style="min-width: 250px; max-height: 200px; overflow: auto">';
		
		$html .= '<li class="none"><span class="dropdown-item">' . Text::_('JNONE') . '</span></li>';
		
		if (isset($this->transitions)) {
			$transitions = explode(",", $this->transitions);
			foreach ($transitions as $transition_item) {
				$transition_item = str_replace('hvr-', '', $transition_item); // just in case
				$html .= '<li data-transition="'.$transition_item.'">';
				$html .= '<a href="#" class="dropdown-item hvr-'.$transition_item.'" style="display: inline-block; padding: 8px; font-size: 1em" aria-label="'.$transition_item.'" onclick="return false;">';
				
				if (!empty($this->sampleimage)) {
					$html .= '<img src="'.URI::root().$this->sampleimage.'" alt="'.$transition_item.'"><span style="margin-left: 10px">'.$transition_item.'</span>';
				} else if (!empty($this->sampleicon)) {
					$html .= '<i class="'.$this->sampleicon.'" aria-hidden="true" style="font-size: 2.4em"></i><span style="margin-left: 10px">'.$transition_item.'</span>';
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
					$html .= '<li><hr class="dropdown-divider"></li>';
				}
			}
		} else {
			$html .= self::getTransitions($this->sampleimage, $this->sampleicon);
		}
		
		$html .= '</ul>';
		
		if ($this->use_global) {
			$html .= '<button type="button" id="'.$this->id.'_clear" class="btn btn-danger hasTooltip" title="' . Text::_('JCLEAR') . '" aria-label="' . Text::_('JCLEAR') . '"><i class="icon-remove" aria-hidden="true"></i></button>';
		}
		
		$html .= '</div>';
		
		if ($this->help) {
			$html .= '<span class="help-block" style="font-size: .8rem">'.Text::_($this->help).'</span>';
		}
		
		return $html;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->use_global = ((string)$this->element['global'] == "true" || (string)$this->element['useglobal'] == "true") ? true : false;
			$this->transitions = isset($this->element['transitions']) ? (string)$this->element['transitions'] : null;
			$this->transitiongroups = isset($this->element['transitiongroups']) ? (string)$this->element['transitiongroups'] : null;
			$this->icon = isset($this->element['icon']) ? (string)$this->element['icon'] : null;
			$this->help = isset($this->element['help']) ? (string)$this->element['help'] : '';
			$this->sampleimage = isset($this->element['sampleimage']) ? (string)$this->element['sampleimage'] : null;
			$this->sampleicon = isset($this->element['sampleicon']) ? (string)$this->element['sampleicon'] : null;
		}
		
		return $return;
	}
}
?>
