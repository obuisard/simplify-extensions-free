<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class DynamicsingleselectField extends ListField
{
	public $type = 'Dynamicsingleselect';

	protected $use_global;
	protected $noelement;
	protected $width;
	protected $maxwidth;
	protected $height;
	protected $selectedcolor;
	protected $disabledtitle;
	protected $imagebgcolor;

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */
	protected function getInput()
	{
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

		// build the script

		$wam->addInlineScript('
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState == "complete") {
					let my_object_' . $this->id . ' = document.getElementById("' . $this->id . '_elements");
					if (my_object_' . $this->id . ' != null) {                        
                        let input_' . $this->id . ' = document.getElementById("' . $this->id . '_id");						
						let enabled_' . $this->id . ' = my_object_' . $this->id . '.querySelectorAll(".element.enabled");

						for (let i = 0; i < enabled_' . $this->id . '.length; i++) {
							if (enabled_' . $this->id . '[i].getAttribute("data-option") == "' . $this->value . '") {
								enabled_' . $this->id . '[i].classList.add("selected");
							}

							enabled_' . $this->id . '[i].addEventListener("click", function(event) {
								input_' . $this->id . '.value = this.getAttribute("data-option");
								input_' . $this->id . '.dispatchEvent(new Event("change"));
								for (let j = 0; j < enabled_' . $this->id . '.length; j++) {
									enabled_' . $this->id . '[j].classList.remove("selected");
								}
								this.classList.add("selected");
							});
						}
					}

					document.addEventListener("subform-row-add", function(e) {
                        let sywd = e.detail.row.querySelector(".dynamicfield");
                        if (sywd != null) {
    						let sywd_enabled = sywd.querySelectorAll(".element.enabled");
    
    						for (let i = 0; i < sywd_enabled.length; i++) {
    							if (sywd_enabled[i].getAttribute("data-option") == "' . $this->default . '") {
    								sywd_enabled[i].classList.add("selected");
    							}
    
    							sywd_enabled[i].addEventListener("click", function(event) {
    								let inputfield = this.parentNode.parentNode.querySelector("input");
    								inputfield.value = this.getAttribute("data-option");
    								inputfield.dispatchEvent(new Event("change"));
    								for (let j = 0; j < sywd_enabled.length; j++) {
    									sywd_enabled[j].classList.remove("selected");
    								}
    								this.classList.add("selected");
    							});
    						}
                        }
					});
				}
			});
		');

		// add the styles

		$wam->addInlineStyle("
			#".$this->id."_elements { display: flex; flex-wrap: wrap; }
			#".$this->id."_elements .element { display: flex; flex-direction: column; align-items: center; position: relative; margin: 0 5px 5px 5px; padding: 15px;".($this->maxwidth ? " max-width: ".$this->maxwidth."px;" : "")." text-align: center; cursor: pointer; -webkit-transition: all .2s ease-in-out; -o-transition: all .2s ease-in-out; transition: all .2s ease-in-out; }
			#".$this->id."_elements .element.enabled:hover { -webkit-transform: scale(0.8); -ms-transform: scale(0.8); transform: scale(0.8); }
			#".$this->id."_elements .element.selected.global { background-color: #2a6496; color: #fff }
			#".$this->id."_elements .element.selected.none { background-color: #c52827; color: #fff }
			#".$this->id."_elements .element.selected { background-color: ".$this->selectedcolor."; color: #fff }
			#".$this->id."_elements .element.disabled { opacity: 0.65; filter: alpha(opacity=65); cursor: default; }
			#".$this->id."_elements .element-label { position: absolute; top: 5px; left: 5px; z-index: 10 }
			#".$this->id."_elements .description { font-size: .8em }
			#".$this->id."_elements .images-container { display: block; position: relative; ".($this->imagebgcolor ? "width" : "max-width").": ".$this->width."px; height: ".$this->height."px; margin-bottom: 15px;" . ($this->imagebgcolor ? " background-color: " . $this->imagebgcolor : "") . " }
			#".$this->id."_elements .element img { display: inline-block; position: relative; top: 50%; transform: translateY(-50%); -webkit-transition: opacity .4s ease; transition: opacity .4s ease; max-width: 100%; max-height: 100%; }
			#".$this->id."_elements .element img.original { opacity: 1; filter: alpha(opacity=100); }
			#".$this->id."_elements .element img.hover { position: absolute; left: 50%; transform: translate(-50%, -50%); opacity: 0; filter: alpha(opacity=0); z-index: 2; }
			#".$this->id."_elements .element:hover img.hover { opacity: 1; filter: alpha(opacity=100); }
			#".$this->id."_elements .element:hover img.original { opacity: 0; filter: alpha(opacity=0); }
		");

		$options = array();

		if ($this->noelement) {
			$options[] = array('', Text::_('JNONE'), '');
		}

		$options = array_merge($options, $this->getOptions());

		$value = $this->default;
		if (!empty($this->value)) {
			$value = $this->value;
		}

		$html = '<div class="dynamicfield">';
		$html .= '<div id="'.$this->id.'_elements" class="elements">';

		foreach ($options as $option) {

			$class_global = '';
			$class_disabled = '';
			$class_hastooltip = '';
			$title_attribute = '';

			if (isset($option[5]) && ($option[5] == 'disabled' || $option[5] == true)) {
				$class_disabled = ' disabled';
				if (!empty($this->disabledtitle)) {
					$title_attribute = ' title="'.Text::_($this->disabledtitle).'"';
					$class_hastooltip = ' hasTooltip';
				}
			} else {
				$class_disabled = ' enabled';
				$title_attribute = ' title="'.Text::_('JSELECT').'"';
				$class_hastooltip = ' hasTooltip';
			}

			if ($option[0] == '') {
				if ($this->use_global) {
					$class_global = ' global';
				} else {
					$class_global = ' none';
				}
			} else if ($option[0] == 'no' || $option[0] == 'none') {
				$class_global = ' none';
			}

			$html .= '<div class="element rounded shadow-sm'.$class_global.$class_hastooltip.$class_disabled.'" data-option="'.$option[0].'"'.$title_attribute.'>';
			
			if (isset($option[6])) {
				
				$tags = explode(',', $option[6]);
				
				$html .= '<div class="element-label">';
				
				foreach ($tags as $tag) {
					$html .= '<span class="badge bg-' . ($tag === 'Pro' ? 'danger' : 'warning') . '">' . $tag . '</span>&nbsp;';
				}
				
				$html .= '</div>';
			}
			
			$html .= '<div class="images-container">';
			if (isset($option[3]) && !empty($option[3])) {

				$originalclass = '';
				if (isset($option[4]) && !empty($option[4])) {
					$originalclass = ' class="original"';
					$html .= '<img class="hover" alt="'.$option[1].'" src="'.$option[4].'" />';
				}

				$html .= '<img'.$originalclass.' alt="'.$option[1].'" src="'.$option[3].'" />';
			}

			$html .= '</div>';

			$html .= '<div class="title">'.$option[1].'</div>';
			if (!empty($option[2])) {
				$html .= '<div class="description">'.$option[2].'</div>';
			}
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '<input type="hidden" id="'.$this->id.'_id" name="'.$this->name.'" value="'.$value.'" />';
		$html .= '</div>';

		return $html;
	}

	protected function getOptions()
	{
		$xml_options = parent::getOptions();
		$options = array();

		foreach ($xml_options as $option) {
			$options[] = array($option->value, $option->text, '', '', '', $option->disable);
		}

		// TODO problem 'none' has no value, like global value

		//		$options[] = array('option1', 'Option 1', 'Description 1', 'option1/option1.png', 'option1/option1_hover.png');
		//		$options[] = array('option2', 'Option 2', 'Description 2', 'option2/option2.png', 'option2/option2_hover.png');
		//		$options[] = array('option3', 'Option 3', 'Description 3', 'option3/option3.png', 'option3/option3_hover.png', 'disabled');

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->use_global = ((string)$this->element['global'] == "true" || (string)$this->element['useglobal'] == "true") ? true : false;
			$this->noelement = isset($this->element['noelement']) ? filter_var($this->element['noelement'], FILTER_VALIDATE_BOOLEAN) : false;
			$this->width = 100;
			$this->maxwidth = '';
			$this->height = 100;
			$this->selectedcolor = '#2f7d32';
			$this->disabledtitle = isset($this->element['disabledtitle']) ? (string)$this->element['disabledtitle'] : '';
			$this->imagebgcolor = isset($this->element['imagebgcolor']) ? (string)$this->element['imagebgcolor'] : '';
		}

		return $return;
	}
}
?>