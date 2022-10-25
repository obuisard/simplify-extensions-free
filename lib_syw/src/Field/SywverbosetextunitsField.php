<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class SywverbosetextunitsField extends ListField
{
	protected $type = 'Sywverbosetextunits';

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

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$size = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$style = empty($size) ? '' : ' style="width:auto"';

		$min = isset($this->min) ? Text::_('LIB_SYW_SYWVERBOSETEXT_MIN').': '.$this->min : '';
		$max = isset($this->max) ? Text::_('LIB_SYW_SYWVERBOSETEXT_MAX').': '.$this->max : '';

		$range = (!empty($min) && !empty($max)) ? $min.' - '.$max : '';
		if (empty($range)) {
			$range = !empty($min) ? $min : '';
		}
		if (empty($range)) {
			$range = !empty($max) ? $max : '';
		}

		$this->values['value'] = $this->default;

		if (is_array($this->value)) {
			$this->values['value'] = $this->value[0];
		}

		$hint = $this->translateHint ? Text::_($this->hint) : $this->hint;
		$hint = $hint ? ' placeholder="'.$hint.'"' : (!empty($range) ? ' placeholder="'.$range.'"' : '');

		$class = !empty($this->class) ? 'class="form-control '.$this->class.'"' : 'class="form-control"';

		$html .= '<div class="textunitfield input-group">';

		if ($this->icon) {
		    $wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);
		    
			$html .= '<span class="input-group-text"><i class="'.$this->icon.'"></i></span>';
		}

		$html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'.htmlspecialchars($this->values['value'], ENT_COMPAT, 'UTF-8').'"'.$class.$style.$size.$this->maxLength.$hint.' />';

		if ($this->units) {

			$unit_selection = explode(',', $this->units);

			if (count($unit_selection) == 1) {
				$html .= '<span class="input-group-text">'.$this->units.'</span>';
			} else {

			    HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
			    HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle'); 

				$this->values['unit'] = $this->default_unit;
				if (is_array($this->value)) {
					$this->values['unit'] = $this->value[1];
				}

				$wam->addInlineScript('
					document.addEventListener("readystatechange", function(event) {
						if (event.target.readyState == "complete") {
							let units = document.querySelectorAll(".unit_' . $this->id . '");
							for (let i = 0; i < units.length; i++) {
								units[i].addEventListener("click", function(event) {
									document.getElementById("' . $this->id . '_unit").value = this.textContent;
									document.getElementById("' . $this->id . '_unit_text").innerHTML = this.textContent;
								});
							}

                            document.addEventListener("subform-row-add", function(e) {
                                let sywvtu = e.detail.row.querySelector(".textunitfield");
                                if (sywvtu != null) {
                                    let sywvtu_unit_input = sywvtu.querySelector("input[type=\'hidden\']");
                                    let sywvtu_unit_text = sywvtu.querySelector(".unittext");
                                    let sywvtu_units = sywvtu.querySelectorAll(".dropdown-item");
                                    for (let i = 0; i < sywvtu_units.length; i++) {
								        sywvtu_units[i].addEventListener("click", function(event) {
									       sywvtu_unit_input.value = this.textContent;
									       sywvtu_unit_text.innerHTML = this.textContent;
								        });
                                    }
                                }
                            });
						}
					});
				');

				$html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'_unit" value="'.$this->values['unit'].'" size="3" />';

				$html .= '<div class="btn-group">';
					$html .= '<button type="button" id="'.$this->id.'_ddb" class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
						$html .= '<span class="unittext" id="'.$this->id.'_unit_text">'.$this->values['unit'].'</span>';
					$html .= '</button>';
					$html .= '<ul class="dropdown-menu" aria-labelledby="'.$this->id.'_ddb">';
					foreach ($unit_selection as $unit) {
						$html .= '<li><a class="dropdown-item unit_'.$this->id.'" href="#" onclick="return false;">'.$unit.'</a></li>';
					}
					$html .= '</ul>';
				$html .= '</div>';
			}
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
			$this->max = isset($this->element['max']) ? (string)$this->element['max'] : null;
			$this->min = isset($this->element['min']) ? (string)$this->element['min'] : null;
			$this->units = isset($this->element['units']) ? (string)$this->element['units'] : '';
			$this->default_unit = isset($this->element['defaultunit']) ? (string)$this->element['defaultunit'] : '';
			$this->help = isset($this->element['help']) ? (string)$this->element['help'] : '';
			$this->icon = isset($this->element['icon']) ? (string)$this->element['icon'] : '';
			$this->maxLength = isset($this->element['maxlength']) ? ' maxlength="' . ((string)$this->maxLength) . '"' : '';
		}

		return $return;
	}

}
?>