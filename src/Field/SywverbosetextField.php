<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class SywverbosetextField extends FormField
{
	protected $type = 'Sywverbosetext';

	protected $max;
	protected $min;
	protected $unit;
	protected $icon;
	protected $help;
	protected $maxLength;

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

		$hint = $this->translateHint ? Text::_($this->hint) : $this->hint;
		$hint = $hint ? ' placeholder="'.$hint.'"' : (!empty($range) ? ' placeholder="'.$range.'"' : '');

		$class = !empty($this->class) ? 'class="form-control '.$this->class.'"' : 'class="form-control"';

		$html .= '<div class="input-group">';

		if ($this->icon) {
		    $wam->registerAndUseStyle('syw.font', 'syw/fonts-min.css', ['relative' => true, 'version' => 'auto']);
		    
			$html .= '<span class="input-group-text"><i class="'.$this->icon.'"></i></span>';
		}

		$html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"'.$class.$style.$size.$this->maxLength.$hint.' />';

		if ($this->unit) {
			$html .= '<span class="input-group-text">'.$this->unit.'</span>';
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
			$this->unit = isset($this->element['unit']) ? (string)$this->element['unit'] : '';
			$this->help = isset($this->element['help']) ? (string)$this->element['help'] : '';
			$this->icon = isset($this->element['icon']) ? (string)$this->element['icon'] : '';
			$this->maxLength = isset($this->element['maxlength']) ? ' maxlength="' . ((string)$this->maxLength) . '"' : '';
		}

		return $return;
	}

}
?>