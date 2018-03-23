<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class SYWFontPickerField extends FormField 
{		
	public $type = 'SYWFontPicker';
	
	protected function getFontTag($fontfamily) 
	{		
		return '<li><a class="standardfont_'.$this->id.'" href="#" onclick="return false;">'.$fontfamily.'</a></li>';
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
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		\JHtml::_('bootstrap.tooltip');
		
		\JHtml::_('stylesheet', 'syw/fonts-min.css', false, true);		

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
		
		Factory::getDocument()->addScriptDeclaration($script);
			
		$html = '<div class="input-prepend input-append">';
            
			$html .= '<div class="add-on"><i class="SYWicon-font"></i></div>';
            
			$html .= '<input id="'.$this->id.'" name="'.$this->name.'" class="input-medium" type="text" value="'.htmlspecialchars($this->value).'" style="font-family:'.htmlspecialchars($this->value).'" />';
            
			$html .= '<div class="btn-group" style="display:inline-block;vertical-align:middle">';
				$html .= '<button style="border-radius:0;margin-left:-1px;min-width:auto" class="btn dropdown-toggle hasTooltip" data-toggle="dropdown" title="' . \JText::_('LIB_SYW_FONTPICKER_SELECTFONT') . '">';            
					$html .= '<span class="caret" style="margin-bottom:auto"></span>';
				$html .= '</button>';
				$html .= '<ul class="dropdown-menu">';
    
		// if use global
		//$html .= '<li><a href="#" onclick="';
		//$html .= '        document.getElementById(\'' . $this->id . '\').value=\''.JText::_('JGLOBAL_USE_GLOBAL').'\';';
		//$html .= '        document.getElementById(\'' . $this->id . '\').setStyle(\'font-family\', \'inherit\');';
		//$html .= '        return false;';
		//$html .= '">'.JText::_('JGLOBAL_USE_GLOBAL').'</a></li>';
            
					$html .= '<li><a class="googlefont_'.$this->id.'" href="#" onclick="return false;">"Google font", fallback, fonts</a></li>';
			            
					$html .= '<li class="nav-header">Serif</li>';            
					$html .= self::getSerifFontFamilies();
			                
					$html .= '<li class="nav-header">Sans-Serif</li>'; 
					$html .= self::getSansSerifFontFamilies();                
			            
					$html .= '<li class="nav-header">Cursive</li>'; 
					$html .= self::getCursiveFontFamilies();
			            
					$html .= '<li class="nav-header">Fantasy</li>'; 
					$html .= self::getFantasyFontFamilies();
			            
					$html .= '<li class="nav-header">Monospace</li>'; 
					$html .= self::getMonospaceFontFamilies();           
                
				$html .= '</ul>';
			$html .= '</div>';
			$html .= '<a class="btn hasTooltip" href="http://www.google.com/webfonts" target="_blank" title="'.\JText::_('LIB_SYW_FONTPICKER_GOOGLEFONTLINK').'"><i class="SYWicon-google"></i></a>';            
			$html .= '<a class="btn hasTooltip clear_'.$this->id.'" title="' . \JText::_('JLIB_FORM_BUTTON_CLEAR') . '"' . ' href="#" onclick="return false;"><i class="icon-remove"></i></a>';			
		$html .= '</div>';			
		$html .= '<span class="help-block">'.\JText::_('LIB_SYW_FONTPICKER_GOOGLEFONTLINKHELP').'</span>';
       		
		return $html;
	}

}
?>
