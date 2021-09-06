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
            $transitionlist .= '<a href="#" class="dropdown-item badge bg-light text-dark hasTooltip hvr-'.$transition_item.'" style="display: inline-block; padding: 8px; font-size: 1em" title="'.$transition_item.'" onclick="return false;" title="'.$transition_item.'">';

            if (!empty($image)) {
                $transitionlist .= '<img src="'.URI::root().$image.'" alt="'.$transition_item.'"><span style="margin-left: 10px">'.$transition_item.'</span>';
            } else if (!empty($icon)) {
                $transitionlist .= '<i class="'.$icon.'" style="font-size: 2.4em"></i><span style="margin-left: 10px">'.$transition_item.'</span>';
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

        $wam->registerAndUseStyle('syw.font', 'syw/fonts-min.css', ['relative' => true, 'version' => 'auto']);
        
        if (isset($this->transitions) || (isset($this->transitiongroups) && strpos($this->transitiongroups, '2d') !== false) || (!isset($this->transitions) && !isset($this->transitiongroups))) {
            $wam->registerAndUseStyle('syw.transitions.2d', 'syw/2d-transitions-min.css', ['relative' => true, 'version' => 'auto']);
        }
        
        if (isset($this->transitions) || (isset($this->transitiongroups) && strpos($this->transitiongroups, 'background') !== false) || (!isset($this->transitions) && !isset($this->transitiongroups))) {
            $wam->registerAndUseStyle('syw.transitions.bg', 'syw/bg-transitions-min.css', ['relative' => true, 'version' => 'auto']);
        }
        
        $wam->addInlineScript('
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState == "complete") {

                    let select_' . $this->id . ' = document.getElementById("' . $this->id . '_select");
                    if (select_' . $this->id . ' != null) {
                        let options_' . $this->id . ' = select_' . $this->id . '.querySelectorAll("li[data-transition]");
                        let input_' . $this->id . ' = document.getElementById("' . $this->id . '");
                        let input_disabled_' . $this->id . ' = document.getElementById("' . $this->id . '_disabled");
                        ' . ($this->use_global ? '
                        let global_' . $this->id . ' = document.getElementById("' . $this->id . '_global");
                        ' : '
                        ') . '
                
                        if (input_' . $this->id . '.value == "") {
                            input_disabled_' . $this->id . '.value = "";
                        } else if (input_' . $this->id . '.value == "none") {
                            input_disabled_' . $this->id . '.value = "' . Text::_('JNONE') . '";
                        } else {
                            input_disabled_' . $this->id . '.value = input_' . $this->id . '.value;
    
                            let entry_value = select_' . $this->id . '.querySelector("li[data-transition=\'' . $this->value . '\']");
                            entry_value.querySelector("a").classList.add("bg-primary", "text-light");
                            entry_value.querySelector("a").classList.remove("bg-light", "text-dark");
                        }
    
                        for (let i = 0; i < options_' . $this->id . '.length; i++) {
                            options_' . $this->id . '[i].addEventListener("click", function(event) {
    
                                if (input_' . $this->id . '.value != "" && input_' . $this->id . '.value != "none") {
                                    let entry_value = select_' . $this->id . '.querySelector("li[data-transition=" + input_' . $this->id . '.value + "]");
                                    entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                    entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                                }
    
                                this.querySelector("a").classList.add("bg-primary", "text-light");
                                this.querySelector("a").classList.remove("bg-light", "text-dark");
    
                                let selected_transition = this.getAttribute("data-transition");
                                input_' . $this->id . '.value = selected_transition;
                                document.getElementById("' . $this->id . '_disabled").value = selected_transition;
    
                                ' . ($this->use_global ? '
                                global_' . $this->id . '.classList.remove("btn-primary", "active");
                                global_' . $this->id . '.classList.add("btn-outline-primary");
                                ' : '
                                ') . '
                            });
                        }
    
                        document.getElementById("' . $this->id . '_none").addEventListener("click", function(event) {
    
                            if (input_' . $this->id . '.value != "" && input_' . $this->id . '.value != "none") {
                                let entry_value = select_' . $this->id . '.querySelector("li[data-transition=" + input_' . $this->id . '.value + "]");
                                entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                            }
    
                            input_' . $this->id . '.value = "none";
                            input_disabled_' . $this->id . '.value = "' . Text::_('JNONE') . '";
    
                            ' . ($this->use_global ? '
                            global_' . $this->id . '.classList.remove("btn-primary", "active");
                            global_' . $this->id . '.classList.add("btn-outline-primary");
                            ' : '
                            ') . '
                        });
    
                        ' . ($this->use_global ? '
                        global_' . $this->id . '.addEventListener("click", function(event) {
    
                            if (input_' . $this->id . '.value != "" && input_' . $this->id . '.value != "none") {
                                let entry_value = select_' . $this->id . '.querySelector("li[data-transition=" + input_' . $this->id . '.value + "]");
                                entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                            }
    
                            input_' . $this->id . '.value = "";
                            input_disabled_' . $this->id . '.value = "";
    
                            this.classList.add("btn-primary", "active");
                            this.classList.remove("btn-outline-primary");
                        });
                        ' : '
                        ') . '
                    }
                }
			});
		');

        $html = '';

        $transition = '';
        if ($this->value != '') {
            $transition = $this->value;
        }

        $icon = isset($this->icon) ? $this->icon : 'SYWicon-stack-overflow';

        $html .= '<div class="input-group">';
        	$html .= '<span class="input-group-text"><i class="'.$icon.'" aria-hidden="true"></i></span>';
        	$html .= '<input type="text" class="form-control" name="'.$this->name.'_disabled" id="'.$this->id.'_disabled"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" disabled="disabled" />';

        	$html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';

        	$html .= '<div class="btn-group">';
        		$html .= '<button type="button" id="'.$this->id.'_caret" style="border-radius:0" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">';
        		$html .= '<span class="visually-hidden">' . Text::_('LIB_SYW_TRANSITIONPICKER_SELECTTRANSITION') . '</span>'; // can't have tooltip on dropdown
        		$html .= '</button>';
        		$html .= '<ul id="'.$this->id.'_select" class="dropdown-menu dropdown-menu-end" aria-labelledby="'.$this->id.'_caret" style="min-width: 250px; max-height: 200px; overflow: auto">';

        if (isset($this->transitions)) {
            $transitions = explode(",", $this->transitions);
            foreach ($transitions as $transition_item) {
                $transition_item = str_replace('hvr-', '', $transition_item); // just in case
                $html .= '<li data-transition="'.$transition_item.'">';
                $html .= '<a href="#" class="dropdown-item badge bg-light text-dark hvr-'.$transition_item.'" style="display: inline-block; padding: 8px; font-size: 1em" title="'.$transition_item.'" aria-label="'.$transition_item.'" onclick="return false;">';

                if (!empty($this->sampleimage)) {
                    $html .= '<img src="'.URI::root().$this->sampleimage.'" alt="'.$transition_item.'" title="'.$transition_item.'"><span style="margin-left: 10px">'.$transition_item.'</span>';
                } else if (!empty($this->sampleicon)) {
                    $html .= '<i class="'.$this->sampleicon.'" style="font-size: 2.4em" title="'.$transition_item.'"></i><span style="margin-left: 10px">'.$transition_item.'</span>';
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
        $html .= '</div>';

        if ($this->use_global) {
            $class = 'btn hasTooltip';
            if (empty($this->value)) {
                $class .= ' btn-primary active';
            } else {
                $class .= ' btn-outline-primary';
            }
            $html .= '    <button type="button" id="'.$this->id.'_global" class="'.$class.'" title="'.Text::_('JGLOBAL_USE_GLOBAL').'"><span>'.Text::_('JGLOBAL_USE_GLOBAL').'</span></button>';
        }

        $html .= '    <button type="button" id="'.$this->id.'_none" class="btn btn-secondary hasTooltip" title="' . Text::_('JCLEAR') . '" aria-label="' . Text::_('JCLEAR') . '"><i class="icon-remove"></i></button>';

        $html .= '</div>';

        if (isset($this->help)) {
            $html .= '<span class="help-block">'.Text::_($this->help).'</span>';
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
        	$this->help = isset($this->element['help']) ? (string)$this->element['help'] : null;
        	$this->sampleimage = isset($this->element['sampleimage']) ? (string)$this->element['sampleimage'] : null;
        	$this->sampleicon = isset($this->element['sampleicon']) ? (string)$this->element['sampleicon'] : null;
        }

        return $return;
    }

}
?>
