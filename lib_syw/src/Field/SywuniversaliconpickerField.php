<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use SYW\Library\Stylesheets as SYWStylesheets;
use Joomla\Registry\Registry;

class SywuniversaliconpickerField extends FormField
{
	public $type = 'Sywuniversaliconpicker';

	protected $icons;
	protected $icongroups;
	protected $emptyicon;
	protected $buttonlabel;
	protected $buttonrole;
	protected $help;
	protected $sets;
	protected $editable;

	static protected $iconsets = null;

	static protected function getIconSet($iconset_name, $path = JPATH_ROOT . '/media/syw/js/iconsets/')
	{
	    if (!isset(self::$iconsets[$iconset_name])) {
	        $iconset = new Registry();
	        $iconset->loadFile($path . $iconset_name . '.json');

	        self::$iconsets[$iconset_name] = $iconset;
	    }

	    return self::$iconsets[$iconset_name];
	}

	protected function prepareIconList($icons)
	{
	    $iconlist = '';

	    foreach ($icons as $icon_item) {
	        $iconlist .= '<li style="width: auto; display: inline-block; border: none; margin: 2px;" data-icon="'.$icon_item.'">';
	        $iconlist .= '<a href="#" class="dropdown-item badge bg-light text-dark hvr-radial-out hasTooltip" style="padding: 8px; font-size: 1.4em" title="'.$icon_item.'" aria-label="'.$icon_item.'" onclick="return false;">';
	        $iconlist .= '<i class="'.$icon_item.'"></i></a></li>';
	        $iconlist .= '</a>';
	        $iconlist .= '</li>';
	    }

	    return $iconlist;
	}

	protected function prepareIconGroup($icongroup)
	{
	    $icons = array();

	    if (self::$iconsets !== null && is_array(self::$iconsets)) {

	        foreach (self::$iconsets as $setname => $iconset) {

	            if (!in_array('syw', $this->sets) && $setname === 'syw' || !in_array('icomoon', $this->sets) && $setname === 'icomoon') {
	                continue;
	            }

	            $iconset_groups = $iconset->get('groups');

	            if (isset($iconset_groups->$icongroup)) {
	                sort($iconset_groups->$icongroup);
	                $icons[] = array('prefix' => $iconset->get('prefix'), 'icons' => $iconset_groups->$icongroup);
	            }
	        }
	    }

	    $iconlist = '';

	    foreach ($icons as $icons_set) {
	        foreach ($icons_set['icons'] as $index => $icon) {
	            $iconlist .= '<li style="width: auto; display: inline-block; border: none; margin: 2px;" data-icon="'.$icons_set['prefix'].$icon.'">';
	            $iconlist .= '<a href="#" class="dropdown-item badge bg-light text-dark hvr-radial-out hasTooltip" style="padding: 8px; font-size: 1.4em" title="'.$icons_set['prefix'].$icon.'" aria-label="'.$icons_set['prefix'].$icon.'" onclick="return false;">';
	            $iconlist .= '<i class="'.$icons_set['prefix'].$icon.'"></i>';
	            $iconlist .= '</a>';
	            $iconlist .= '</li>';
	        }
	    }

	    return $iconlist;
	}

	protected function prepareIconGroups($icongrouplist = array('communications', 'equipment', 'transportation', 'location', 'social', 'agenda', 'finances', 'files', 'systems', 'accessibility', 'design', 'media', 'other'))
	{
	    $iconlist = '';

	    foreach ($icongrouplist as $icongrouplist_item) {
	        $iconlist .= '<li><h6 class="dropdown-header" style="padding-top: 2rem">'.Text::_('LIB_SYW_ICONPICKER_ICONGROUP_'.strtoupper($icongrouplist_item)).'</h6></li>';
	        $iconlist .= self::prepareIconGroup($icongrouplist_item);
	    }

	    return $iconlist;
	}

	protected function getInput()
	{
	    $wam = Factory::getApplication()->getDocument()->getWebAssetManager();

	    $lang = Factory::getLanguage();
	    $lang->load('lib_syw.sys', JPATH_SITE);

	    HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
	    HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');

	    $transition_method = SYWStylesheets::getTransitionMethod('hvr-radial-out');
	    SYWStylesheets::$transition_method();

	    if ($this->sets !== null) {
	        $wam->addInlineScript('
    			document.addEventListener("readystatechange", function(event) {
    				if (event.target.readyState == "complete") {

                        let select_' . $this->id . ' = document.getElementById("' . $this->id . '_select");
                        if (select_' . $this->id . ' != null) {
                            let options_' . $this->id . ' = select_' . $this->id . '.querySelectorAll("li[data-icon]");
        		            let input_' . $this->id . ' = document.getElementById("' . $this->id . '");
                            let icon_' . $this->id . ' = document.getElementById("' . $this->id . '_icon");

                            if (input_' . $this->id . '.value != "") {
                                let entry_value = select_' . $this->id . '.querySelector("li[data-icon=\'' . $this->value . '\']");
                                if (entry_value != null) {
                                    entry_value.querySelector("a").classList.add("bg-primary", "text-light");
                                    entry_value.querySelector("a").classList.remove("bg-light", "text-dark");
                                }
                            }

                            for (let i = 0; i < options_' . $this->id . '.length; i++) {
                                options_' . $this->id . '[i].addEventListener("click", function(event) {

                                    if (input_' . $this->id . '.value != "") {
                                        let entry_value = select_' . $this->id . '.querySelector("li[data-icon=\'" + input_' . $this->id . '.value + "\']");
                                        if (entry_value != null) {
                                            entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                            entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                                        }
                                    }

                                    this.querySelector("a").classList.add("bg-primary", "text-light");
                                    this.querySelector("a").classList.remove("bg-light", "text-dark");

                                    let selected_icon = this.getAttribute("data-icon");
                                    input_' . $this->id . '.value = selected_icon;
                                    icon_' . $this->id . '.setAttribute("class", selected_icon);
									input_' . $this->id . '.dispatchEvent(new Event("change"));
                                });
                            }

                            document.getElementById("' . $this->id . '_default").addEventListener("click", function(event) {
                                if (input_' . $this->id . '.value != "") {
                                    let entry_value = select_' . $this->id . '.querySelector("li[data-icon=\'" + input_' . $this->id . '.value + "\']");
                                    if (entry_value != null) {
                                        entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                        entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                                    }
                                }
                                ' . (empty($this->default) ? '
                                input_' . $this->id . '.value = "";
                                icon_' . $this->id . '.setAttribute("class", "' . $this->emptyicon . '");
                                ' : '
                                input_' . $this->id . '.value = "' . $this->default . '";
                                icon_' . $this->id . '.setAttribute("class", "' . $this->default . '");
                                ') . '
								input_' . $this->id . '.dispatchEvent(new Event("change"));
                            });

                            ' . ($this->editable ? '
                            input_' . $this->id . '.addEventListener("change", function(event) {
                                if (this.value != "") {

                                    icon_' . $this->id . '.setAttribute("class", this.value);

                                    for (let i = 0; i < options_' . $this->id . '.length; i++) {
                                        if (this.value == options_' . $this->id . '[i].getAttribute("data-icon")) {
                                            options_' . $this->id . '[i].querySelector("a").classList.add("bg-primary", "text-light");
                                            options_' . $this->id . '[i].querySelector("a").classList.remove("bg-light", "text-dark");
                                        } else {
                                            options_' . $this->id . '[i].querySelector("a").classList.remove("bg-primary", "text-light");
                                            options_' . $this->id . '[i].querySelector("a").classList.add("bg-light", "text-dark");
                                        }
                                    }
                                } else {
                                    ' . (empty($this->default) ? '
                                    icon_' . $this->id . '.setAttribute("class", "' . $this->emptyicon . '");
                                    ' : '
                                    input_' . $this->id . '.value = "' . $this->default . '";
                                    icon_' . $this->id . '.setAttribute("class", "' . $this->default . '");
                                    ') . '

                                    for (let i = 0; i < options_' . $this->id . '.length; i++) {
                                        options_' . $this->id . '[i].querySelector("a").classList.remove("bg-primary", "text-light");
                                        options_' . $this->id . '[i].querySelector("a").classList.add("bg-light", "text-dark");
                                    }
                                }
                            });
                            ' : '
                            ') . '
                        }

                        document.addEventListener("subform-row-add", function(e) {
                            let sywip = e.detail.row.querySelector(".iconpicker");
                            if (sywip != null) {
                                let sywip_options = sywip.querySelectorAll("li[data-icon]");
                                let sywip_input = sywip.querySelector("input[data-name=input-icon]");
                                let sywip_icon = sywip.querySelector("i[data-name=icon]");

                                for (let i = 0; i < sywip_options.length; i++) {
                                    sywip_options[i].addEventListener("click", function(event) {

                                        if (sywip_input.value != "") {
                                            let entry_value = sywip.querySelector("li[data-icon=\'" + sywip_input.value + "\']");
                                            if (entry_value != null) {
                                                entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                                entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                                            }
                                        }

                                        this.querySelector("a").classList.add("bg-primary", "text-light");
                                        this.querySelector("a").classList.remove("bg-light", "text-dark");

                                        let selected_icon = this.getAttribute("data-icon");
                                        sywip_input.value = selected_icon;
                                        sywip_icon.setAttribute("class", "icon-" + selected_icon);
										sywip_input.dispatchEvent(new Event("change"));
                                    });
                                }

                                sywip.querySelector("button[data-name=default-icon]").addEventListener("click", function(event) {
                                    if (sywip_input.value != "") {
                                        let entry_value = sywip.querySelector("li[dataicon=\'" + sywip_input.value + "\']");
                                        if (entry_value != null) {
                                            entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                            entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                                        }
                                    }
                                    ' . (empty($this->default) ? '
                                    sywip_input.value = "";
                                    sywip_icon.setAttribute("class", "' . $this->emptyicon . '");
                                    ' : '
                                    sywip_input.value = "' . $this->default . '";
                                    sywip_icon.setAttribute("class", "' . $this->default . '");
                                    ') . '
									sywip_input.dispatchEvent(new Event("change"));
                                });

                                ' . ($this->editable ? '
                                sywip_input.addEventListener("change", function(event) {
                                    if (this.value != "") {

                                        sywip_icon.setAttribute("class", this.value);

                                        for (let i = 0; i < sywip_options.length; i++) {
                                            if (this.value == sywip_options[i].getAttribute("data-icon")) {
                                                sywip_options[i].querySelector("a").classList.add("bg-primary", "text-light");
                                                sywip_options[i].querySelector("a").classList.remove("bg-light", "text-dark");
                                            } else {
                                                sywip_options[i].querySelector("a").classList.remove("bg-primary", "text-light");
                                                sywip_options[i].querySelector("a").classList.add("bg-light", "text-dark");
                                            }
                                        }
                                    } else {
                                        ' . (empty($this->default) ? '
                                        sywip_icon.setAttribute("class", "' . $this->emptyicon . '");
                                        ' : '
                                        sywip_input.value = "' . $this->default . '";
                                        sywip_icon.setAttribute("class", "' . $this->default . '");
                                        ') . '

                                        for (let i = 0; i < sywip_options.length; i++) {
                                            sywip_options[i].querySelector("a").classList.remove("bg-primary", "text-light");
                                            sywip_options[i].querySelector("a").classList.add("bg-light", "text-dark");
                                        }
                                    }
                                });
                                ' : '
                                ') . '
                            }
                        });
                    }
    			});
    		');
	    } else { // there is no selection of icons, just editing
	        $wam->addInlineScript('
    			document.addEventListener("readystatechange", function(event) {
    				if (event.target.readyState == "complete") {

    		            let input_' . $this->id . ' = document.getElementById("' . $this->id . '");
                        if (input_' . $this->id . ' != null) {
                            let icon_' . $this->id . ' = document.getElementById("' . $this->id . '_icon");

                            document.getElementById("' . $this->id . '_default").addEventListener("click", function(event) {
                                ' . (empty($this->default) ? '
                                input_' . $this->id . '.value = "";
                                icon_' . $this->id . '.setAttribute("class", "' . $this->emptyicon . '");
                                ' : '
                                input_' . $this->id . '.value = "' . $this->default . '";
                                icon_' . $this->id . '.setAttribute("class", "' . $this->default . '");
                                ') . '
								input_' . $this->id . '.dispatchEvent(new Event("change"));
                            });

                            ' . ($this->editable ? '
                            input_' . $this->id . '.addEventListener("change", function(event) {
                                if (this.value != "") {
                                    icon_' . $this->id . '.setAttribute("class", this.value);
                                } else {
                                    ' . (empty($this->default) ? '
                                    icon_' . $this->id . '.setAttribute("class", "' . $this->emptyicon . '");
                                    ' : '
                                    input_' . $this->id . '.value = "' . $this->default . '";
                                    icon_' . $this->id . '.setAttribute("class", "' . $this->default . '");
                                    ') . '
                                }
                            });
                            ' : '
                            ') . '
                        }

                        document.addEventListener("subform-row-add", function(e) {
                            let sywip = e.detail.row.querySelector(".iconpicker");
                            if (sywip != null) {
                                let sywip_input = sywip.querySelector("input[data-name=input-icon]");
                                let sywip_icon = sywip.querySelector("i[data-name=icon]");

                                sywip.querySelector("button[data-name=default-icon]").addEventListener("click", function(event) {
                                    ' . (empty($this->default) ? '
                                    sywip_input.value = "";
                                    sywip_icon.setAttribute("class", "' . $this->emptyicon . '");
                                    ' : '
                                    sywip_input.value = "' . $this->default . '";
                                    sywip_icon.setAttribute("class", "' . $this->default . '");
                                    ') . '
									sywip_input.dispatchEvent(new Event("change"));
                                });

                                ' . ($this->editable ? '
                                sywip_input.addEventListener("change", function(event) {
                                    if (this.value != "") {
                                        sywip_icon.setAttribute("class", this.value);
                                    } else {
                                        ' . (empty($this->default) ? '
                                        sywip_icon.setAttribute("class", "' . $this->emptyicon . '");
                                        ' : '
                                        sywip_input.value = "' . $this->default . '";
                                        sywip_icon.setAttribute("class", "' . $this->default . '");
                                        ') . '
                                    }
                                });
                                ' : '
                                ') . '
                            }
                        });
                    }
    			});
    		');
	    }

	    $html = '';

	    $html .= '<div class="iconpicker input-group">';

	    if (!empty($this->value)) {
	        $html .= '<span class="input-group-text"><i id="'.$this->id.'_icon" class="'.$this->value.'" data-name="icon" aria-hidden="true"></i></span>';
	    } else {
	        if (empty($this->default)) {
	            $html .= '<span class="input-group-text"><i id="'.$this->id.'_icon" class="'.$this->emptyicon.'" data-name="icon" aria-hidden="true"></i></span>';
	        } else {
	            $html .= '<span class="input-group-text"><i id="'.$this->id.'_icon" class="'.$this->default.'" data-name="icon" aria-hidden="true"></i></span>';
	        }
	    }

	    if ($this->editable) {
	        $html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"'.' data-name="input-icon" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" class="form-control" />';
	    } else {
	        //$html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';
	        $html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"'.' data-name="input-icon" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" readonly="readonly" class="form-control" />';
	    }

	    if ($this->sets !== null) {
	        $html .= '<div class="btn-group" style="margin: 0">';
	        $html .= '<button type="button" id="'.$this->id.'_caret"'.($this->disabled ? ' disabled="disabled"' : '').' style="border-radius:0" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="' . Text::_('LIB_SYW_ICONPICKER_SELECTICON') . '"></button>';
	        $html .= '<ul id="'.$this->id.'_select" class="dropdown-menu dropdown-menu-end" aria-labelledby="'.$this->id.'_caret" style="min-width: 250px; max-height: 200px; overflow: auto;">';

	        if (isset($this->icons)) {
	            $html .= self::prepareIconList($this->icons);
	        } else if (isset($this->icongroups)) {
	            $html .= self::prepareIconGroups($this->icongroups);
	        } else {
	            $html .= self::prepareIconGroups();
	        }

	        $html .= '</ul>';
	        $html .= '</div>';
	    }

	    $default_class_extra = '';
	    if ($this->buttonrole == 'default') {
	        $default_class_extra = ' btn-secondary';
	    }

	    if ($this->buttonrole == 'clear') {
	        $default_class_extra = ' btn-danger';
	    }

	    $html .= '<button type="button" data-name="default-icon" id="'.$this->id.'_default"'.($this->disabled ? ' disabled="disabled"' : '').' class="btn'.$default_class_extra.' hasTooltip" title="' . htmlspecialchars($this->buttonlabel, ENT_COMPAT, 'UTF-8') . '" aria-label="' . htmlspecialchars($this->buttonlabel, ENT_COMPAT, 'UTF-8') . '">';
	    if ($this->buttonrole == 'clear') {
	        $html .= '<i class="icon-remove"></i>';
	    } else {
	        $html .= $this->buttonlabel;
	    }
	    $html .= '</button>';

	    $html .= '</div>';

	    if ($this->help) {
	        $html .= '<span class="help-block" style="font-size: .8rem">'. $this->help . '</span>';
	    }

	    return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
	    $return = parent::setup($element, $value, $group);

	    if ($return) {
	        $this->icons = isset($this->element['icons']) ? explode(",", (string)$this->element['icons']) : null;
	        $this->icongroups = isset($this->element['icongroups']) ? explode(",", (string)$this->element['icongroups']) : null;
	        $this->help = isset($this->element['help']) ? Text::_((string)$this->element['help']) : Text::sprintf('LIB_SYW_UNIVERSALICONPICKER_FONTAWESOMEHELP', (version_compare(JVERSION, '5.0', 'lt') ? '5' : '6'));

	        $this->sets = isset($this->element['iconsets']) ? explode(",", (string)$this->element['iconsets']) : null;

	        $this->editable = true; //isset($this->element['editable']) ? filter_var($this->element['editable'], FILTER_VALIDATE_BOOLEAN) : false;
	        $this->buttonrole = isset($this->element['buttonrole']) ? Text::_((string)$this->element['buttonrole']) : 'default';
	        $this->buttonlabel = isset($this->element['buttonlabel']) ? Text::_((string)$this->element['buttonlabel']) : ($this->buttonrole == 'clear' ? Text::_('JCLEAR') : Text::_('JDEFAULT'));
	        $this->emptyicon = isset($this->element['emptyicon']) ? (string)$this->element['emptyicon'] : ($this->buttonrole == 'default' ? 'SYWicon-question' : '');

	        // load assets

	        $wam = Factory::getApplication()->getDocument()->getWebAssetManager();

	        $asset_names = isset($this->element['assets']) ? explode(',', (string)$this->element['assets']) : array(); // comma separated list of assets to add
	        $asset_names[] = 'fontawesome';

	        foreach ($asset_names as $asset_name) {
	        	$wam->useStyle($asset_name); // loads fontawesome and icomoon B/C from web asset, probably already loaded on the page
	        }

	        $wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);

	        // B/C compatibility

	        $bc_mode = isset($this->element['bcmode']) ? filter_var($this->element['bcmode'], FILTER_VALIDATE_BOOLEAN) : false;

	        if (!empty($this->value) && $bc_mode) {

	        	$ignore_prefix = isset($this->element['bcprefixes']) ? explode(',', (string)$this->element['bcprefixes']) : array(); // comma separated list of prefixes to ignore under B/C mode

			    $temp_value = explode('-', $this->value);

			    $ignore = array_merge(array('SYWicon', 'icon', 'bi bi', 'fa fa', 'fas fa', 'fal fa', 'fab fa', 'far fa', 'fad fa'), $ignore_prefix);

			    if (!in_array($temp_value[0], $ignore)) {
			        $count_replacements = 0;
			        $this->value = str_replace('icomoon-', 'icon-', $this->value, $count_replacements);
			        if ($count_replacements <= 0) {
			            $this->value = 'SYWicon-' . $this->value;
			        }
			    }
			}

	        // load icon sets once for all icon pickers
			if ($this->sets !== null && in_array('syw', $this->sets)) {
			    self::getIconSet('syw');
			}
			if ($this->sets !== null && in_array('icomoon', $this->sets)) {
			    self::getIconSet('icomoon');
			}

	        //self::getIconSet('fontawesome'); only offer input editing because there are too many icons
	    }

	    return $return;
	}

}
?>
