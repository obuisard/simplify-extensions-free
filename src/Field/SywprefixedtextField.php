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

/* deprecated - use verbosetext */
class SywprefixedtextField extends FormField
{
	protected $type = 'Sywprefixedtext';

	protected $prefix;
	protected $postfix;
	protected $icon;
	protected $help;
	protected $maxLength;

	protected function getInput()
	{
		$html = '';

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$size = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$style = empty($size) ? '' : ' style="width:auto"';

		$hint = $this->translateHint ? Text::_($this->hint) : $this->hint;
		$hint = $hint ? ' placeholder="'.$hint.'"' : '';

		$class = !empty($this->class) ? 'class="form-control '.$this->class.'"' : 'class="form-control"';

		$html .= '<div class="input-group">';

		if ($this->prefix) {

			if ($this->icon) {
			    HTMLHelper::_('stylesheet', 'syw/fonts.min.css', ['version' => 'auto', 'relative' => true]);
				$html .= '<span class="input-group-text"><i class="'.$this->icon.'"></i></span>';
			}

			$html .= '<span class="input-group-text">'.$this->prefix.'</span>';
		}

		$html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"'.$class.$style.$size.$this->maxLength.$hint.' />';

		if ($this->postfix) {
			$html .= '<div class="input-group-text">'.$this->postfix.'</div>';
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
			$this->prefix = isset($this->element['prefix']) ? (string)$this->element['prefix'] : '';
			$this->postfix = isset($this->element['postfix']) ? (string)$this->element['postfix'] : '';
			$this->help = isset($this->element['help']) ? (string)$this->element['help'] : '';
			$this->icon = isset($this->element['icon']) ? (string)$this->element['icon'] : '';
			$this->maxLength = isset($this->element['maxlength']) ? ' maxlength="' . ((string)$this->maxLength) . '"' : '';
		}

		return $return;
	}

}
?>