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

class OrField extends FormField 
{		
	public $type = 'Or';

	protected function getLabel() 
	{			
		return '';
	}

	protected function getInput() 
	{
		$html = '';
		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		$html .= Text::_('LIB_SYW_OR_OR');
			
		return $html;
	}

}
?>
