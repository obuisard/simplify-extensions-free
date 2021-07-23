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
		return '<li><a class="dropdown-item standardfont_'.$this->id.'" style="font-family: '.htmlspecialchars($fontfamily).'" href="#" onclick="return false;">'.$fontfamily.'</a></li>';
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

		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
		HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle'); 

		$wam->registerAndUseStyle('syw.font', 'syw/fonts-min.css', ['relative' => true, 'version' => 'auto']);
		
		$wam->addInlineScript('
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState == "complete") {

                    var standard_fonts = document.querySelectorAll(".standardfont_' . $this->id . '");
                    for (let i = 0; i < standard_fonts.length; i++) {
                        standard_fonts[i].addEventListener("click", function(event) {
                            let font_family = this.textContent;
                            document.getElementById("' . $this->id . '").value = font_family;
                            document.getElementById("' . $this->id . '").style.fontFamily = font_family;
                        });
                    }

                    var google_fonts = document.querySelectorAll(".googlefont_' . $this->id . '");
                    for (let i = 0; i < google_fonts.length; i++) {
                        google_fonts[i].addEventListener("click", function(event) {
                            let font_family = this.textContent;
                            document.getElementById("' . $this->id . '").value = font_family;
                            document.getElementById("' . $this->id . '").style.fontFamily = "inherit";
                        });
                    }

                    document.getElementById("clear_' . $this->id . '").addEventListener("click", function(event) {
                        document.getElementById("' . $this->id . '").value = "";
                    });
                }
			});
		');

		$html = '<div class="input-group">';

			$html .= '<span class="input-group-text"><i class="SYWicon-font" aria-hidden="true"></i></span>';

			$html .= '<input id="'.$this->id.'" name="'.$this->name.'" class="form-control" type="text" value="'.htmlspecialchars($this->value).'" style="font-family:'.htmlspecialchars($this->value).'" />';

			$html .= '<div class="btn-group">';
				$html .= '<button type="button" id="dropdownMenu'.$this->id.'" class="btn btn-secondary dropdown-toggle" style="border-radius:0" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">';
				$html .= '<span class="visually-hidden">' . Text::_('LIB_SYW_FONTPICKER_SELECTFONT') . '</span>';
				$html .= '</button>';
				$html .= '<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenu'.$this->id.'" style="max-height: 200px; overflow-x: hidden; overflow-y: auto">';

					$html .= '<li><a class="dropdown-item googlefont_'.$this->id.'" href="#" onclick="return false;">' . Text::_('LIB_SYW_FONTPICKER_GOOGLEFONTFORMAT') . '</a></li>';

					$html .= '<li><h6 class="dropdown-header">Serif</h6></li>';
					$html .= self::getSerifFontFamilies();

					$html .= '<li><h6 class="dropdown-header">Sans-Serif</h6></li>';
					$html .= self::getSansSerifFontFamilies();

					$html .= '<li><h6 class="dropdown-header">Cursive</h6></li>';
					$html .= self::getCursiveFontFamilies();

					$html .= '<li><h6 class="dropdown-header">Fantasy</h6></li>';
					$html .= self::getFantasyFontFamilies();

					$html .= '<li><h6 class="dropdown-header">Monospace</h6></li>';
					$html .= self::getMonospaceFontFamilies();

				$html .= '</ul>';
			$html .= '</div>';
			$html .= '<button id="clear_' . $this->id . '" type="button" class="btn btn-secondary hasTooltip" title="' . Text::_('JCLEAR') . '" aria-label="' . Text::_('JCLEAR') . '"><i class="icon-remove"></i></button>';

		$html .= '</div>';
		$html .= '<span class="help-block">'.Text::_('LIB_SYW_FONTPICKER_GOOGLEFONTLINKHELP').'</span><br />';
		$html .= '<a href="https://fonts.google.com/" target="_blank">'.Text::_('LIB_SYW_FONTPICKER_GOOGLEFONTLINK').'</a>';

		return $html;
	}

}
?>
