<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class GDTestField extends FormField 
{		
	public $type = 'Gdtest';
	
	protected $show_gif;

	protected function getLabel() 
	{		
		return '';
	}

	protected function getInput() 
	{		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
							
		$extensions = get_loaded_extensions();
		
		$html = '';
		
		if( !in_array( 'gd', $extensions ) ) {			
			$html .= '<div style="margin-bottom:0" class="alert alert-error">';			
				$html .= '<span>'.\JText::_('LIB_SYW_GDTEST_NOTLOADED').'</span>';
			$html .= '</div>';
			
			return $html;
		} else {
			$html .= '<div style="margin-bottom:0" class="alert alert-success">';			
				$html .= '<span>'.\JText::_('LIB_SYW_GDTEST_LOADED').' ('.GD_VERSION.')'.'</span><br />';
			
			if ($this->show_gif) {
				if (imagetypes() & IMG_GIF) {
					$html .= '<span class="label label-success">GIF '.lcfirst(\JText::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="label label-warning">GIF '.lcfirst(\JText::_('JDISABLED')).'</span> ';
				}
			}
			
			if (imagetypes() & IMG_JPG) {
				$html .= '<span class="label label-success">JPG '.lcfirst(\JText::_('JENABLED')).'</span> ';
			} else {
				$html .= '<span class="label label-warning">JPG '.lcfirst(\JText::_('JDISABLED')).'</span> ';
			}
			
			if (imagetypes() & IMG_PNG) {
				$html .= '<span class="label label-success">PNG '.lcfirst(\JText::_('JENABLED')).'</span>';
			} else {
				$html .= '<span class="label label-warning">PNG '.lcfirst(\JText::_('JDISABLED')).'</span>';
			}
			
			$html .= '</div>';
		}	
		
		return $html;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->show_gif = isset($this->element['showgif']) ? filter_var($this->element['showgif'], FILTER_VALIDATE_BOOLEAN) : true;
		}
		
		return $return;
	}

}
?>
