<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class SywverbosetextField extends FormField
{
	protected $type = 'Sywverbosetext';

	protected $prefix;
	protected $postfix;
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

		$hint = '';

		if ($this->element['useglobal'])
		{
			$component = Factory::getApplication()->getInput()->getCmd('option');

			// Get correct component for menu items
			if ($component === 'com_menus')
			{
				$link      = $this->form->getData()->get('link');
				$uri       = new Uri($link);
				$component = $uri->getVar('option', 'com_menus');
			}

			$params = ComponentHelper::getParams($component);
			$value  = $params->get($this->fieldname);

			// Try with global configuration
			if (\is_null($value))
			{
				$value = Factory::getApplication()->get($this->fieldname);
			}

			// Try with menu configuration
			if (\is_null($value) && Factory::getApplication()->getInput()->getCmd('option') === 'com_menus')
			{
				$value = ComponentHelper::getParams('com_menus')->get($this->fieldname);
			}

			if (!\is_null($value))
			{
				$hint = Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', (string) $value);
			}
		}

		if (empty($hint) && (isset($this->min) || isset($this->max)))
		{
			$min = isset($this->min) ? Text::sprintf('LIB_SYW_SYWVERBOSETEXT_MIN', $this->min) : '';
			$max = isset($this->max) ? Text::sprintf('LIB_SYW_SYWVERBOSETEXT_MAX', $this->max) : '';

			$hint = ($min && $max) ? $min . ' - ' . $max : '';

			if (empty($hint))
			{
				$hint = $min ? $min : '';
			}

			if (empty($hint))
			{
				$hint = $max ? $max : '';
			}
			
			$hint = Text::sprintf('LIB_SYW_SYWVERBOSETEXT_HINT', $hint);
		}

		if (empty($hint) && $this->hint)
		{
			$hint = $this->translateHint ? Text::_($this->hint) : Text::sprintf('LIB_SYW_SYWVERBOSETEXT_HINT', $this->hint);
		}

		$hint = $hint ? ' placeholder="' . $hint . '"' : '';

		$size = $this->size ? ' size="' . $this->size . '"' : '';

		$style = $size ? ' style="width:auto"' : '';

		$class = $this->class ? ' class="form-control ' . $this->class . '"' : ' class="form-control"';

		$html .= '<div class="input-group">';

		if ($this->icon)
		{
			$wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);

			$html .= '<span class="input-group-text"><i class="' . $this->icon . '"></i></span>';
		}

		if ($this->prefix)
		{
			$html .= '<span class="input-group-text">' . $this->prefix . '</span>';
		}

		$html .= '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $style . $size . $this->maxLength . $hint . ' />';

		if ($this->postfix)
		{
			$html .= '<span class="input-group-text">' . $this->postfix . '</span>';
		}

		if ($this->unit)
		{
			$html .= '<span class="input-group-text">' . $this->unit . '</span>';
		}

		$html .= '</div>';

		if ($this->help)
		{
			$html .= '<span class="help-block" style="font-size: .8rem">' . Text::_($this->help) . '</span>';
		}

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->prefix = isset($this->element['prefix']) ? (string)$this->element['prefix'] : '';
			$this->postfix = isset($this->element['postfix']) ? (string)$this->element['postfix'] : '';
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