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

class DynamicSingleSelect extends ListField
{
	public $type = 'DynamicSingleSelect';

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

		HTMLHelper::_('bootstrap.tooltip');

		// build the script

		$wam->addInlineScript('
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState == "complete") {
					let my_object = document.getElementById("' . $this->id . '_elements");

					let input_field = document.getElementById("' . $this->id . '_id");
					let enabled_children = my_object.querySelectorAll(".element.enabled");
					for (let i = 0; i < enabled_children.length; i++) {
						if (enabled_children[i].getAttribute("data-option") == "' . $this->value . '") {
							enabled_children[i].classList.add("selected");
						}

						enabled_children[i].addEventListener("click", function(event) {
							input_field.value = this.getAttribute("data-option");
							input_field.dispatchEvent(new Event("change"));
							for (let j = 0; j < enabled_children.length; j++) {
								enabled_children[j].classList.remove("selected");
							}
							this.classList.add("selected");
						});
					}
				}
			});
		');

		// add the styles

		$wam->addInlineStyle("
			#".$this->id."_elements { display: -webkit-box; display: -ms-flexbox; display: -webkit-flex; display: flex; overflow: auto; -ms-flex-wrap: wrap; flex-wrap: wrap; }
			#".$this->id."_elements .element { display: inline-block; position: relative; vertical-align: top; relative; margin: 0 5px 5px 5px; padding: 15px;".(!empty($this->maxwidth) ? " max-width: ".$this->maxwidth."px;" : "")." text-align: center; cursor: pointer; -webkit-transition: all .2s ease-in-out; -o-transition: all .2s ease-in-out; transition: all .2s ease-in-out; }
			#".$this->id."_elements .element.enabled:hover { -webkit-transform: scale(0.8); -ms-transform: scale(0.8); transform: scale(0.8); }
			#".$this->id."_elements .element.selected.global { background-color: #2a6496; color: #fff }
			#".$this->id."_elements .element.selected.none { background-color: #c52827; color: #fff }
			#".$this->id."_elements .element.selected { background-color: ".$this->selectedcolor."; color: #fff }
			#".$this->id."_elements .element.disabled { opacity: 0.65; filter: alpha(opacity=65); cursor: default; }
			#".$this->id."_elements .images-container { display: inline-block; position: relative; width: ".$this->width."px; height: ".$this->height."px; margin-bottom: 5px;" . ($this->imagebgcolor ? " background-color: " . $this->imagebgcolor : "") . " }
			#".$this->id."_elements .images-container .imagelabel { position: absolute; top: 5px; left: 5px; z-index: 100 }
			#".$this->id."_elements .title { width: ".$this->width."px; }
			#".$this->id."_elements .description { width: ".$this->width."px; font-size: .8em }
			#".$this->id."_elements .element img { display: block; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); -webkit-transition: opacity .4s ease; transition: opacity .4s ease; max-width: ".$this->width."px; max-height: ".$this->height."px; }
			#".$this->id."_elements .element img.original { opacity: 1; filter: alpha(opacity=100); }
			#".$this->id."_elements .element img.hover { opacity: 0; filter: alpha(opacity=0); z-index: 2; }
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

		$html = '<div id="'.$this->id.'_elements" class="elements">';

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
			$html .= '<div class="images-container">';
			if (isset($option[3]) && !empty($option[3])) {

				$originalclass = '';
				if (isset($option[4]) && !empty($option[4])) {
					$originalclass = ' class="original"';
					$html .= '<img class="hover" alt="'.$option[1].'" src="'.$option[4].'" />';
				}

				$html .= '<img'.$originalclass.' alt="'.$option[1].'" src="'.$option[3].'" />';
			}

			if (isset($option[6])) {
				$html .= '<div class="badge badge-warning imagelabel">' . $option[6] . '</div>';
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
			$this->selectedcolor = '#2f7d32';//isset($this->element['selectedcolor']) ? $this->element['selectedcolor'] : '#6f6f6f';
			$this->disabledtitle = isset($this->element['disabledtitle']) ? (string)$this->element['disabledtitle'] : '';
			$this->imagebgcolor = isset($this->element['imagebgcolor']) ? (string)$this->element['imagebgcolor'] : '';
		}

		return $return;
	}
}
?>