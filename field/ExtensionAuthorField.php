<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class ExtensionAuthorField extends FormField 
{		
	public $type = 'ExtensionAuthor';
	
	protected function getLabel() 
	{		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		$html = '';
		
		$html .= '<div style="clear: both;">'.\JText::_('LIB_SYW_EXTENSIONAUTHOR_AUTHOR_LABEL').'</div>';
		
		return $html;
	}

	protected function getInput() 
	{
		$html = '<div style="padding-top: 5px; overflow: inherit">';
		
		$html .= 'Olivier Buisard @ <a href="http://www.simplifyyourweb.com" target="_blank">';
			$html .= '<img alt="Simplify Your Web" src="'.\JURI::root(true).'/media/syw/images/SimplifyYourWeb_24.png">';
		$html .= '</a>';
		
		$html .= '</div>';
		
		return $html;
	}

}
?>
