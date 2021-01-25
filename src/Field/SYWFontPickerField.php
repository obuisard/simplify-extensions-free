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

class SYWFontPickerField extends FormField
{
	public $type = 'SYWFontPicker';

	protected function getFontTag($fontfamily)
	{
		return '<li class="dropdown-item"><a class="standardfont_'.$this->id.'" style="font-family: '.htmlspecialchars($fontfamily).'" href="#" onclick="return false;">'.$fontfamily.'</a></li>';
	}

	protected function getSerifFontFamilies()
	{
		$html = '';

		$html .= self::getFontTag('serif');
		$html .= self::getFontTag('Georgia, serif');
		$html .= self::getFontTag('"Palatino Linotype", "Book Antiqua", Palatino, serif');
		$html .= self::getFontTag('"MS Serif", "New York", serif');
		$html .= self::getFontTag('"Times New Roman", Times, serif');

		return $html;
	}

	protected function getSansSerifFontFamilies()
	{
		$html = '';

		$html .= self::getFontTag('sans-serif');
		$html .= self::getFontTag('Arial, Helvetica, sans-serif');
		$html .= self::getFontTag('"Arial Black", Gadget, sans-serif');
		$html .= self::getFontTag('"Comic Sans MS", cursive, sans-serif');
		$html .= self::getFontTag('Impact, Charcoal, sans-serif');
		$html .= self::getFontTag('"Lucida Sans Unicode", "Lucida Grande", sans-serif');
		$html .= self::getFontTag('Tahoma, Geneva, sans-serif');
		$html .= self::getFontTag('"Trebuchet MS", Helvetica, sans-serif');
		$html .= self::getFontTag('"MS Sans Serif", Geneva, sans-serif');
		$html .= self::getFontTag('Verdana, Geneva, sans-serif');

		return $html;
	}

	protected function getMonospaceFontFamilies()
	{
		$html = '';

		$html .= self::getFontTag('monospace');
		$html .= self::getFontTag('"Courier New", Courier, monospace');
		$html .= self::getFontTag('"Lucida Console", Monaco, monospace');

		return $html;
	}

	protected function getCursiveFontFamilies()
	{
		$html = '';

		$html .= self::getFontTag('cursive');

		return $html;
	}

	protected function getFantasyFontFamilies()
	{
		$html = '';

		$html .= self::getFontTag('fantasy');

		return $html;
	}

	/**
	 * Method to get the field input markup.
	 *
	 */
	protected function getInput()
	{
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		HTMLHelper::_('bootstrap.tooltip');

		HTMLHelper::_('stylesheet', 'syw/fonts-min.css', ['version' => 'auto', 'relative' => true]);

		$script = 'jQuery(document).ready(function () {';
			$script .= 'jQuery(\'.standardfont_'.$this->id.'\').click(function() { ';
				$script .= 'var fontfamily = jQuery(this).text();';
				$script .= 'jQuery(\'#'.$this->id.'\').val(fontfamily);';
				$script .= 'jQuery(\'#'.$this->id.'\').css(\'font-family\', fontfamily);';
			$script .= '}); ';
			$script .= 'jQuery(\'.googlefont_'.$this->id.'\').click(function() { ';
				$script .= 'var fontfamily = jQuery(this).text();';
				$script .= 'jQuery(\'#'.$this->id.'\').val(fontfamily);';
				$script .= 'jQuery(\'#'.$this->id.'\').css(\'font-family\', \'inherit\');';
			$script .= '}); ';
			$script .= 'jQuery(\'.clear_'.$this->id.'\').click(function() { ';
				$script .= 'jQuery(\'#'.$this->id.'\').val(\'\');';
			$script .= '}); ';
		$script .= '});';

		$wam->addInlineScript($script);

		$html = '<div class="input-group">';

			$html .= '<div class="input-group-prepend"><span class="input-group-text"><i class="SYWicon-font"></i></span></div>';

			$html .= '<input id="'.$this->id.'" name="'.$this->name.'" class="form-control" type="text" value="'.htmlspecialchars($this->value).'" style="font-family:'.htmlspecialchars($this->value).'" />';

			$html .= '<div class="btn-group" style="display:inline-block;vertical-align:middle">';
				$html .= '<button style="border-radius:0;margin-left:-1px;min-width:auto" class="btn btn-secondary dropdown-toggle hasTooltip" data-toggle="dropdown" title="' . Text::_('LIB_SYW_FONTPICKER_SELECTFONT') . '">';
					//$html .= '<span class="caret" style="margin-bottom:auto"></span>';
				$html .= '</button>';
				$html .= '<ul class="dropdown-menu" style="max-height: 200px; overflow-x: hidden; overflow-y: auto;">';

		// if use global
		//$html .= '<li><a href="#" onclick="';
		//$html .= '        document.getElementById(\'' . $this->id . '\').value=\''.JText::_('JGLOBAL_USE_GLOBAL').'\';';
		//$html .= '        document.getElementById(\'' . $this->id . '\').setStyle(\'font-family\', \'inherit\');';
		//$html .= '        return false;';
		//$html .= '">'.JText::_('JGLOBAL_USE_GLOBAL').'</a></li>';

					$html .= '<li class="dropdown-item"><a class="googlefont_'.$this->id.'" href="#" onclick="return false;">"Google font", fallback, fonts</a></li>';

					$html .= '<li class="dropdown-header">Serif</li>';
					$html .= self::getSerifFontFamilies();

					$html .= '<li class="dropdown-header">Sans-Serif</li>';
					$html .= self::getSansSerifFontFamilies();

					$html .= '<li class="dropdown-header">Cursive</li>';
					$html .= self::getCursiveFontFamilies();

					$html .= '<li class="dropdown-header">Fantasy</li>';
					$html .= self::getFantasyFontFamilies();

					$html .= '<li class="dropdown-header">Monospace</li>';
					$html .= self::getMonospaceFontFamilies();

				$html .= '</ul>';
			$html .= '</div>';
			$html .= '<div class="input-group-append">';
			     $html .= '<button type="button" class="btn btn-secondary hasTooltip clear_'.$this->id.'" title="' . Text::_('JLIB_FORM_BUTTON_CLEAR') . '"' . '><i class="icon-remove"></i></button>';
		    $html .= '</div>';
		$html .= '</div>';
		$html .= '<span class="help-block">'.Text::_('LIB_SYW_FONTPICKER_GOOGLEFONTLINKHELP').'</span><br />';
		$html .= '<a href="http://www.google.com/webfonts" target="_blank">'.Text::_('LIB_SYW_FONTPICKER_GOOGLEFONTLINK').'</a>';

		return $html;
	}

}
?>
