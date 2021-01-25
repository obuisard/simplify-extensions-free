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

class SYWVerboseTextUnitsField extends ListField
{
	protected $type = 'SYWVerboseTextUnits';

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

		$html .= '<div class="input-group">';

		if ($this->icon) {
		    HTMLHelper::_('stylesheet', 'syw/fonts-min.css', ['version' => 'auto', 'relative' => true]);
			$html .= '<div class="input-group-prepend"><span class="input-group-text"><i class="'.$this->icon.'"></i></span></div>';
		}

		$html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'.htmlspecialchars($this->values['value'], ENT_COMPAT, 'UTF-8').'"'.$class.$style.$size.$this->maxLength.$hint.' />';

		if ($this->units) {

			$unit_selection = explode(',', $this->units);

			if (count($unit_selection) == 1) {
				$html .= '<div class="input-group-append"><span class="input-group-text">'.$this->units.'</span></div>';
			} else {

				HTMLHelper::_('bootstrap.tooltip');

				$this->values['unit'] = $this->default_unit;
				if (is_array($this->value)) {
					$this->values['unit'] = $this->value[1];
				}

				$script = 'jQuery(document).ready(function () {';
					$script .= 'jQuery(\'.unit_'.$this->id.'\').click(function() { ';
						$script .= 'var unit = jQuery(this).text();';
						$script .= 'jQuery(\'#'.$this->id.'_unit\').val(unit);';
						$script .= 'jQuery(\'#'.$this->id.'_unit_text\').html(unit);';
					$script .= '}); ';
				$script .= '});';

				$wam->addInlineScript($script);

				$html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'_unit" value="'.$this->values['unit'].'" size="3" />';

				$html .= '<div class="input-group-append">';
				$html .= '<button class="btn btn-secondary dropdown-toggle hasTooltip" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="'.Text::_('LIB_SYW_VERBOSETEXT_UNIT').'">';
				$html .= '<span id="'.$this->id.'_unit_text">'.$this->values['unit'].'</span>&nbsp;';
				$html .= '<span class="caret" style="margin-bottom:auto"></span>';
				$html .= '</button>';
				$html .= '<div class="dropdown-menu">';
				foreach ($unit_selection as $unit) {
					$html .= '<li><a class="dropdown-item unit_'.$this->id.'" href="#" onclick="return false;">'.$unit.'</a></li>';
				}
				$html .= '</div>';
				$html .= '</div>';
			}
		}

		$html .= '</div>';

		if ($this->help) {
			$html .= '<span class="help-block">'.Text::_($this->help).'</span>';
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