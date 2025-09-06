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
use SYW\Library\Plugin as SYWPlugin;

class SywfontpickerField extends FormField
{
	public $type = 'Sywfontpicker';
	
	protected $use_global;
	
	protected $support_webfonts;
	
	/**
	 * 
	 * @var string a possible help string that will replace the default one
	 */
	protected $help;
	
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
		
		//$wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);
		
		$icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" viewBox="0 0 448 512" style="width: 1em; fill: currentcolor"><path d="M254 52.8C249.3 40.3 237.3 32 224 32s-25.3 8.3-30 20.8L57.8 416H32c-17.7 0-32 14.3-32 32s14.3 32 32 32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32h-1.8l18-48H303.8l18 48H320c-17.7 0-32 14.3-32 32s14.3 32 32 32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H390.2L254 52.8zM279.8 304H168.2L224 155.1 279.8 304z"/></svg>';
		
		$global_value = '';
		
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
		
		$wam->addInlineScript('
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState == "complete") {
			
					let input = document.getElementById("' . $this->id . '");
			
                    var standard_fonts = document.querySelectorAll(".standardfont_' . $this->id . '");
                    for (let i = 0; i < standard_fonts.length; i++) {
                        standard_fonts[i].addEventListener("click", function(event) {
                            let font_family = this.textContent;
                            input.value = font_family;
                            input.style.fontFamily = font_family;
							input.removeAttribute("disabled");
                        });
                    }
			
                    var web_fonts = document.querySelectorAll(".webfont_' . $this->id . '");
                    for (let i = 0; i < web_fonts.length; i++) {
                        web_fonts[i].addEventListener("click", function(event) {
                            let font_family = this.textContent;
                            input.value = font_family;
                            input.style.fontFamily = "inherit";
							input.removeAttribute("disabled");
                        });
                    }
			
                    document.getElementById("clear_' . $this->id . '").addEventListener("click", function(event) {
                        input.value = "";
			
						if (input.classList.contains("useGlobal")) {
							var textarea = document.createElement("textarea");
    						textarea.innerHTML = "' . Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', htmlspecialchars($global_value, ENT_QUOTES)) . '";
							input.setAttribute("placeholder", textarea.value);
							input.setAttribute("disabled", "disabled");
							input.style.fontFamily = "inherit";
						}
                    });
                }
			});
		');
		
		$disabled = '';
		$placeholder = '';
		if (empty($this->value) && $this->use_global) {
			$disabled = ' disabled';
			$placeholder = ' placeholder="' . Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', htmlspecialchars($global_value, ENT_QUOTES)) . '"';
		}
		
		$html = '<div class="input-group">';
		
		//$html .= '<span class="input-group-text"><i class="SYWicon-font" aria-hidden="true"></i></span>';
		$html .= '<span class="input-group-text">'.$icon_svg.'</span>';
		
		$html .= '<input id="'.$this->id.'" name="'.$this->name.'" class="form-control' . ($this->use_global ? ' useGlobal' : '') . '" type="text" value="'.htmlspecialchars($this->value).'" style="font-family:'.htmlspecialchars($this->value).'"'. $disabled . $placeholder .' />';
		
		$html .= '<button type="button" id="dropdownMenu'.$this->id.'" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent" aria-label="' . Text::_('LIB_SYW_FONTPICKER_SELECTFONT') . '"></button>';
		$html .= '<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenu'.$this->id.'" style="max-height: 200px; overflow-x: hidden; overflow-y: auto">';
		
		$html .= '<li><a class="dropdown-item webfont_'.$this->id.'" href="#" onclick="return false;">' . Text::_('LIB_SYW_FONTPICKER_WEBFONTFORMAT') . '</a></li>';
		
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
		
		$html .= '<button id="clear_' . $this->id . '" type="button" class="btn btn-danger hasTooltip" title="' . Text::_('JCLEAR') . '" aria-label="' . Text::_('JCLEAR') . '"><i class="icon-remove" aria-hidden="true"></i></button>';
		
		$html .= '</div>';
		
		$html .= '<span class="help-block" style="font-size: .8rem">' . $this->help . '</span>';
		
		return $html;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->use_global = ((string)$this->element['global'] == "true" || (string)$this->element['useglobal'] == "true") ? true : false;
			$this->support_webfonts = ((string)$this->element['webfonts'] == "true") ? true : false;
			$this->help = isset($this->element['help']) ? Text::_((string)$this->element['help']) : '';

			if (empty($this->help)) {
			    $help = Text::_('LIB_SYW_FONTPICKER_WEBFONTLINKHELP');

			    $webfont_service = $this->support_webfonts ? SYWPlugin::getWebfontService() : 'google';
    			if ($webfont_service === 'bunny') {
    			    $help .= '<br /><a href="https://fonts.bunny.net" target="_blank">' . Text::_('LIB_SYW_FONTPICKER_BUNNYFONTLINK') . '</a>';
    			} else {
                    $help .= '<br /><a href="https://fonts.google.com" target="_blank">' . Text::_('LIB_SYW_FONTPICKER_GOOGLEFONTLINK') . '</a>';
    			}

    			$this->help = $help;
			}
		}
		
		return $return;
	}
}
?>
