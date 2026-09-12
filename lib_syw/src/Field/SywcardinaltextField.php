<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class SywcardinaltextField extends ListField
{
	protected $type = 'Sywcardinaltext';

	protected $unit;
	protected $icons;
	protected $tooltips;
	protected $help;
	protected $maxLength;
	protected $layout;

	protected $values = array();

	protected $forceMultiple = true;

	protected function getInput()
	{
		$html = '';
		
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
		
		$wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']); // TODO if icons to show

		$size = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$style = empty($size) ? '' : ' style="width:auto"';

		$hint = $this->translateHint ? Text::_($this->hint) : $this->hint;
		$hint = $hint ? ' placeholder="'.$hint.'"' : '';

		$default_top = null;
		$default_right = null;
		$default_bottom = null;
		$default_left = null;
		if ($this->default) {
			$defaults = explode(",", $this->default);
			if (count($defaults) == 1) {
				$defaults[] = $defaults[0];
				$defaults[] = $defaults[0];
				$defaults[] = $defaults[0];
			}
			$default_top = $defaults[0];
			$default_right = $defaults[1];
			$default_bottom = $defaults[2];
			$default_left = $defaults[3];
		}

		$this->values['top'] = $default_top;
		$this->values['right'] = $default_right;
		$this->values['bottom'] = $default_bottom;
		$this->values['left'] = $default_left;

		if (is_array($this->value)) {
			foreach ($this->value as $i => $value) {
				if ($i == 0) {
					if ($this->layout == 'corners') {
						$this->values['top'] = $value;
					} else {
						$this->values['top'] = $value;
					}
				}
				if ($i == 1) {
					if ($this->layout == 'corners') {
						$this->values['right'] = $value;
					} else {
						$this->values['left'] = $value;
					}
				}
				if ($i == 2) {
					if ($this->layout == 'corners') {
						$this->values['left'] = $value;
					} else {
						$this->values['right'] = $value;
					}
				}
				if ($i == 3) {
					if ($this->layout == 'corners') {
						$this->values['bottom'] = $value;
					} else {
						$this->values['bottom'] = $value;
					}
				}
			}
		}

		$html .= '<table cellpadding="2" cellspacing="2">';

		if ($this->layout == 'corners') {

			$html .= '<tr>';

			$html .= '<td>'.self::createField('top', $style, $size, $this->maxLength, $hint).'</td>';
			$html .= '<td></td>';
			$html .= '<td>'.self::createField('right', $style, $size, $this->maxLength, $hint).'</td>';

			$html .= '</tr>';

			$html .= '<tr>';

			$html .= '<td></td>';
			$html .= '<td style="width: 100px; height: 100px; background-color: #efefef; padding: 0; border: 10px solid #fff;">';
			$html .= '<table style="width: 100%; height: 100%"><tr style="height:15%"><td style="width:15%; background-color: #555"></td><td style="width:70%"></td><td style="width:15%; background-color: #555"></td></tr><tr style="height:70%"><td></td><td></td><td></td></tr><tr style="height:15%"><td style="background-color: #555"></td><td></td><td style="background-color: #555"></td></tr></table>';
			$html .= '</td>';
			$html .= '<td></td>';

			$html .= '</tr>';

			$html .= '<tr>';

			$html .= '<td>'.self::createField('left', $style, $size, $this->maxLength, $hint).'</td>';
			$html .= '<td></td>';
			$html .= '<td>'.self::createField('bottom', $style, $size, $this->maxLength, $hint).'</td>';

			$html .= '</tr>';

		} else { // default

			$html .= '<tr>';

			$html .= '<td colspan="3" style="text-align: center">'.self::createField('top', $style, $size, $this->maxLength, $hint).'</td>';

			$html .= '</tr>';

			$html .= '<tr>';

			$html .= '<td>'.self::createField('left', $style, $size, $this->maxLength, $hint).'</td>';
			$html .= '<td style="width: 100px; height: 100px; background-color: #efefef; padding: 0; border: 10px solid #fff;">';
			$html .= '<table style="width: 100%; height: 100%"><tr style="height:15%"><td style="width:15%"></td><td style="width:70%; background-color: #555"></td><td style="width:15%"></td></tr><tr style="height:70%"><td style="background-color: #555"></td><td></td><td style="background-color: #555"></td></tr><tr style="height:15%"><td></td><td style="background-color: #555"></td><td></td></tr></table>';
			$html .= '</td>';
			$html .= '<td>'.self::createField('right', $style, $size, $this->maxLength, $hint).'</td>';

			$html .= '</tr>';

			$html .= '<tr>';

			$html .= '<td colspan="3" style="text-align: center">'.self::createField('bottom', $style, $size, $this->maxLength, $hint).'</td>';

			$html .= '</tr>';
		}

		$html .= '</table>';

		if ($this->help) {
			$html .= '<span class="help-block" style="font-size: .8rem">'.Text::_($this->help).'</span>';
		}

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->unit = isset($this->element['unit']) ? (string)$this->element['unit'] : '';
			$this->help = isset($this->element['help']) ? (string)$this->element['help'] : '';

			$icons = isset($this->element['icons']) ? explode(",", (string)$this->element['icons']) : array('');
			if (count($icons) == 1) {
				$icons[] = $icons[0];
				$icons[] = $icons[0];
				$icons[] = $icons[0];
			}
			$this->icons['top'] = $icons[0];
			$this->icons['right'] = $icons[1];
			$this->icons['bottom'] = $icons[2];
			$this->icons['left'] = $icons[3];

			$tooltips = isset($this->element['tooltips']) ? explode(",", (string)$this->element['tooltips']) : array('');
			if (count($tooltips) == 1) {
				$tooltips[] = $tooltips[0];
				$tooltips[] = $tooltips[0];
				$tooltips[] = $tooltips[0];
			}
			$this->tooltips['top'] = empty($tooltips[0]) ? '' : Text::_($tooltips[0]);
			$this->tooltips['right'] = empty($tooltips[1]) ? '' : Text::_($tooltips[1]);
			$this->tooltips['bottom'] = empty($tooltips[2]) ? '' : Text::_($tooltips[2]);
			$this->tooltips['left'] = empty($tooltips[3]) ? '' : Text::_($tooltips[3]);

			$this->maxLength = isset($this->element['maxlength']) ? ' maxlength="' . ((string)$this->maxLength) . '"' : '';

			$this->layout = isset($this->element['layout']) ? (string)$this->element['layout'] : 'default';
		}

		return $return;
	}

	protected function createField($cardinal_point, $style = '', $size = '', $maxLength = '', $hint = '')
	{
		$html = '';

		$tooltip = empty($this->tooltips[$cardinal_point]) ? '' : ' hasTooltip';
		$title = empty($this->tooltips[$cardinal_point]) ? '' : ' title="'.$this->tooltips[$cardinal_point].'"';
		$class = ' class="form-control"';

		$html .= '<div class="input-group'. $tooltip .'"'. $title .'>';

		if ($this->icons[$cardinal_point]) {
			$html .= '<span class="input-group-text"><i class="'.$this->icons[$cardinal_point].'"></i></span>';
		}

		$html .= '<input type="text" name="'.$this->name.'" value="'.htmlspecialchars($this->values[$cardinal_point], ENT_COMPAT, 'UTF-8').'"'.$class.$style.$size.$maxLength.$hint.' />';

		if ($this->unit) {
			$html .= '<span class="input-group-text">'.$this->unit.'</span>';
		}

		$html .= '</div>';

		return $html;
	}

}
?>