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

class SywcolorpickerField extends FormField
{
	public $type = 'Sywcolorpicker';
	
	protected $use_global;
	protected $icon; // null|fill|outline|text
	protected $help;
	
	/*
	 * The color format (rgb, rgba, hsl, hsla, hex, hex8)
	 */
	protected $format;
	
	/*
	 * Whether the color uses alpha values
	 */
	protected $alpha = false;
	
	/*
	 * Possible standard keyword values (transparent, currentcolor, inherit, initial)
	 */
	protected $keyword_values;
	
	/*
	 * Add a clear button
	 */
	protected $clear;
	
	/*
	 * deprecated
	 * Empty value is allowed and if there is an empty value, it is transformed into a transparency value
	 */
	protected $allow_transparency;
	
	/*
	 * deprecated
	 */
	protected $rgba;
	
	/**
	 * Method to get the field input markup
	 *
	 * @return string The field input markup
	 */
	protected function getInput()
	{
		$html = '';
		
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();
		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
		
		$wam->registerAndUseStyle('field.sywcolorpicker', 'syw/colorpicker/color-picker.min.css', ['relative' => true, 'version' => 'auto']);
		$wam->registerAndUseScript('field.sywcolorpicker.iro', 'syw/colorpicker/iro.min.js', ['relative' => true, 'version' => 'auto']);
		$wam->registerAndUseScript('field.sywcolorpicker', 'syw/colorpicker/color-picker.min.js', ['relative' => true, 'version' => 'auto'], ['type' => 'module']);
		
		$icon_svg = '';
		if (isset($this->icon)) {
			if ($this->icon === 'outline') {
				$icon_svg .= '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" viewBox="0 0 16 16" style="width: 1em; fill: currentcolor">';
				$icon_svg .= '<path fill-rule="evenodd" d="M7.21.8C7.69.295 8 0 8 0c.109.363.234.708.371 1.038.812 1.946 2.073 3.35 3.197 4.6C12.878 7.096 14 8.345 14 10a6 6 0 0 1-12 0C2 6.668 5.58 2.517 7.21.8zm.413 1.021A31.25 31.25 0 0 0 5.794 3.99c-.726.95-1.436 2.008-1.96 3.07C3.304 8.133 3 9.138 3 10a5 5 0 0 0 10 0c0-1.201-.796-2.157-2.181-3.7l-.03-.032C9.75 5.11 8.5 3.72 7.623 1.82z"/>';
				$icon_svg .= '<path fill-rule="evenodd" d="M4.553 7.776c.82-1.641 1.717-2.753 2.093-3.13l.708.708c-.29.29-1.128 1.311-1.907 2.87l-.894-.448z"/>';
				$icon_svg .= '</svg>';
			} else if ($this->icon === 'text') {
				$icon_svg .= '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" style="width: 1em; fill: currentcolor">';
				$icon_svg .= '<path fill-rule="nonzero" d="M17.75 14.5A2.25 2.25 0 0 1 20 16.75v3A2.25 2.25 0 0 1 17.75 22H5.25A2.25 2.25 0 0 1 3 19.75v-3a2.25 2.25 0 0 1 2.25-2.25h12.5ZM7.053 11.97l3.753-9.496c.236-.595 1.043-.63 1.345-.104l.05.105 3.747 9.5a.75.75 0 0 1-1.352.643l-.044-.092L13.556 10H9.443l-.996 2.52a.75.75 0 0 1-.876.454l-.097-.031a.75.75 0 0 1-.453-.876l.032-.098 3.753-9.495-3.753 9.495Zm4.45-7.178L10.036 8.5h2.928l-1.461-3.708Z"/>';
				$icon_svg .= '</svg>';
			} else {
				$icon_svg .= '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" viewBox="0 0 16 16" style="width: 1em; fill: currentcolor">';
				$icon_svg .= '<path d="M8 16a6 6 0 0 0 6-6c0-1.655-1.122-2.904-2.432-4.362C10.254 4.176 8.75 2.503 8 0c0 0-6 5.686-6 10a6 6 0 0 0 6 6ZM6.646 4.646l.708.708c-.29.29-1.128 1.311-1.907 2.87l-.894-.448c.82-1.641 1.717-2.753 2.093-3.13Z"/>';
				$icon_svg .= '</svg>';
			}
		}

		$color = strtolower($this->value);
		
		if (!$color || $color == 'none') {
			$color = '';
		}
		
		if ($color && !in_array($color, $this->keyword_values) && $this->format === 'hex' && $color['0'] !== '#') {
			$color = '#' . $color;
		}
		
		$this->value = $color;
		
		$global_value = ''; //Text::_('JNONE');
		
		if ($this->use_global) {
			$component  = Factory::getApplication()->getInput()->getCmd('option');
			if ($component == 'com_menus') { // we are in the context of a menu item
				$uri = new Uri($this->form->getData()->get('link'));
				$component = $uri->getVar('option', 'com_menus');
				
				$config_params = ComponentHelper::getParams($component);
				
				$config_value = $config_params->get($this->fieldname);
				
				if (!is_null($config_value)) {
					$global_value = $config_value;
				}
			}
		}
		
		$direction = $lang->isRtl() ? ' dir="rtl"' : '';
		
		if (isset($this->icon) || $this->clear) {
			$html .= '<div class="colorpicker input-group"' . $direction . '>';
		} else {
			$html .= '<div class="colorpicker"' . $direction . '>';
		}
		
		if (isset($this->icon)) {
			//$html .= '<span class="input-group-text"><i class="'.$icon.'" aria-hidden="true"></i></span>';
			$html .= '<span class="input-group-text">'.$icon_svg.'</span>';
		}
		
		if (!$this->clear) {
			
			$html .= '<colour-picker>';
			$html .= '<input type="text" data-name="input-color" name="'.$this->name.'" id="'.$this->id.'"'.' value="' . htmlspecialchars($this->value, ENT_QUOTES) . '" data-color-format="' . $this->format . '" class="form-control' . ($this->alpha ? ' hasAlpha' : '') . '" />';
			$html .= '</colour-picker>';
			
		} else {
			
			$disabled = '';
			$placeholder = '';
			
			if (in_array($this->value, $this->keyword_values)) {
				$disabled = ' disabled';
			}
			
			if (empty($this->value) && $this->use_global) {
				$placeholder = ' placeholder="' . Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $global_value) . '"';
			}
			
			$html .= '<colour-picker>';
			$html .= '<input type="text" data-name="visible-input-color" name="visible_'.$this->name.'" id="visible_'.$this->id.'"'.' value="' . htmlspecialchars($this->value, ENT_QUOTES) . '" data-color-format="' . $this->format . '" class="form-control' . ($this->use_global ? ' useGlobal' : '') . ($this->alpha ? ' hasAlpha' : '') . '"'.$disabled.$placeholder.' />';
			$html .= '<input type="hidden" data-name="input-color" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';
			$html .= '</colour-picker>';
		}
		
		if (!empty($this->keyword_values)) {
			$html .= '<button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="' . Text::_('LIB_SYW_COLORPICKER_SELECTCOLOR') . '"></button>';
			$html .= '<ul id="select_'.$this->id.'" class="dropdown-menu dropdown-menu-end">';
			
			foreach ($this->keyword_values as $keyword) {
				$active_class = '';
				if ($this->value == $keyword) {
					$active_class = ' active';
				}
				$html .= '<li data-keyword="' . $keyword . '"><span class="dropdown-item' . $active_class . '">' . $keyword . '</span></li>';
			}
			
			$html .= '</ul>';
		}
		
		if ($this->clear) { // use a_ rather than clear_ for backward compatibility
			$html .= '<button type="button" id="a_'.$this->id.'" data-name="clear" class="btn btn-danger hasTooltip" title="'.Text::_('JCLEAR').'" aria-label="' . Text::_('JCLEAR') . '"><i class="icon-remove" aria-hidden="true"></i></button>';
		}
		
		$html .= '</div>';
		
		if ($this->help) {
			$html .= '<span class="help-block" style="font-size: .8rem">' . $this->help . '</span>';
		}
		
		if ($this->clear) {
			$wam->addInlineScript('
				document.addEventListener("readystatechange", function(event) {
					if (event.target.readyState == "complete") {
				
						let input = document.getElementById("' . $this->id . '");
						let visible_input = document.getElementById("visible_' . $this->id . '");
				
						if (input && input.value == "" && visible_input && visible_input.classList.contains("useGlobal")) {
							visible_input.parentNode.querySelector("colour-swatch").style.backgroundColor = "' . $global_value . '";
						}

						if (visible_input) {
							visible_input.addEventListener("input", function(event) {
								input.value = this.value;
							});
						}
				
						let select = document.getElementById("select_' . $this->id . '");
						if (select != null) {
							select.querySelectorAll("li[data-keyword]").forEach (function (option) {
								option.addEventListener("click", function(event) {
									input.value = this.getAttribute("data-keyword");
									visible_input.value = this.getAttribute("data-keyword");
									visible_input.setAttribute("disabled", "disabled");
									this.parentNode.previousSibling.previousSibling.querySelector("colour-swatch").style.backgroundColor = "";
				
									select.querySelectorAll(".dropdown-item").forEach (function (option) {
										option.classList.remove("active");
									});
									this.querySelector(".dropdown-item").classList.add("active");
								});
							});
						}

						let clear = document.getElementById("a_' . $this->id . '");
						if (clear != null) {
							clear.addEventListener("click", function(event) {
				
								if (select != null) {
									this.previousSibling.previousSibling.previousSibling.querySelector("colour-swatch").style.backgroundColor = "";
								} else {
									this.previousSibling.querySelector("colour-swatch").style.backgroundColor = "";
								}
				
								if (visible_input && visible_input.classList.contains("useGlobal")) {
									visible_input.setAttribute("placeholder", "' . Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $global_value) . '");
									if (select != null) {
										this.previousSibling.previousSibling.previousSibling.querySelector("colour-swatch").style.backgroundColor = "' . $global_value . '";
									} else {
										this.previousSibling.querySelector("colour-swatch").style.backgroundColor = "' . $global_value . '";
									}
								}
				
								input.value = "";
								if (visible_input) {
									visible_input.value = "";
									visible_input.removeAttribute("disabled");
								}
				
								if (select != null) {
									select.querySelectorAll(".dropdown-item").forEach (function (option) {
									  option.classList.remove("active");
									});
								}
							});
						}
				
						document.addEventListener("subform-row-add", function(e) {
                        	let sywcp = e.detail.row.querySelector(".colorpicker");
                        	if (sywcp != null) {
								let sywcp_input = sywcp.querySelector("input[data-name=input-color]");
								let sywcp_visible_input = sywcp.querySelector("input[data-name=visible-input-color]");
								let sywcp_options = sywcp.querySelectorAll("li[data-keyword]");

								if (sywcp_visible_input) {
									sywcp_visible_input.addEventListener("input", function(event) {
										sywcp_input.value = this.value;
									});
								}
				
								if (sywcp_options) {
									sywcp_options.forEach (function (option) {
										option.addEventListener("click", function(event) {
											sywcp_input.value = this.getAttribute("data-keyword");
											sywcp_visible_input.value = this.getAttribute("data-keyword");
											sywcp_visible_input.setAttribute("disabled", "disabled");
											this.parentNode.previousSibling.previousSibling.querySelector("colour-swatch").style.backgroundColor = "";
											sywcp_options.forEach (function (option) {
												option.firstElementChild.classList.remove("active");
											});
											this.querySelector(".dropdown-item").classList.add("active");
										});
									});
								}
									
								sywcp_clear = sywcp.querySelector("button[data-name=clear]");
								if (sywcp_clear) {
									sywcp_clear.addEventListener("click", function(event) {
										if (sywcp_options != null) {
											this.previousSibling.previousSibling.previousSibling.querySelector("colour-swatch").style.backgroundColor = "";
										} else {
											this.previousSibling.querySelector("colour-swatch").style.backgroundColor = "";
										}
										sywcp_input.value = "";
										if (sywcp_visible_input) {
											sywcp_visible_input.value = "";
											sywcp_visible_input.removeAttribute("disabled");
										}
										if (sywcp_options != null) {
											sywcp_options.forEach (function (option) {
											  option.firstElementChild.classList.remove("active");
											});
										}
									});
								}
							}
						});
					}
				});
			');
		}
		
		return $html;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->use_global = ((string)$this->element['global'] == "true" || (string)$this->element['useglobal'] == "true") ? true : false;
			
			$this->icon = isset($this->element['icon']) ? (string)$this->element['icon'] : null;
			$this->format = isset($this->element['format']) ? (string)$this->element['format'] : 'hex'; // rgb, rgba, hsl, hsla, hex, hex8
			
			$this->allow_transparency = isset($this->element['transparency']) ? filter_var($this->element['transparency'], FILTER_VALIDATE_BOOLEAN) : false; // deprecated
			
			$this->keyword_values = array();
			$keywords = isset($this->element['keywords']) ? (string)$this->element['keywords'] : ''; // transparent, currentcolor, inherit, initial
			if ($keywords) {
				$this->keyword_values = explode(',', $keywords);
			}
			if ($this->allow_transparency && !in_array('transparent', $this->keyword_values)) {
				$this->keyword_values[] = 'transparent';
			}
			
			$this->clear = isset($this->element['clear']) ? filter_var($this->element['clear'], FILTER_VALIDATE_BOOLEAN) : ($this->allow_transparency ? $this->allow_transparency : false);
			if (!empty($this->keyword_values) || $this->use_global) {
				$this->clear = true;
			}
			
			$this->rgba = isset($this->element['rgba']) ? filter_var($this->element['rgba'], FILTER_VALIDATE_BOOLEAN) : false; // deprecated
			if ($this->rgba) {
				$this->format = 'rgba';
			}
			
			if (in_array($this->format, ['rgba', 'hsla', 'hex8'])) {
				$this->alpha = true;
			}
			
			if (isset($this->element['help'])) {
				$this->help = Text::_((string)$this->element['help']);
			} else {
				switch ($this->format) {
					case 'rgb': $this->help = Text::_('LIB_SYW_COLORPICKER_HELP_RGB'); break;
					case 'rgba': $this->help = Text::_('LIB_SYW_COLORPICKER_HELP_RGBA'); break;
					case 'hsl': $this->help = Text::_('LIB_SYW_COLORPICKER_HELP_HSL'); break;
					case 'hsla': $this->help = Text::_('LIB_SYW_COLORPICKER_HELP_HSLA'); break;
					case 'hex8': $this->help = Text::_('LIB_SYW_COLORPICKER_HELP_HEX8'); break;
					default: $this->help = Text::_('LIB_SYW_COLORPICKER_HELP_HEX'); // hex
				}
			}
		}
		
		return $return;
	}
}
?>
