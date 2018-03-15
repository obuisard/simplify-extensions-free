<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\JqueryEasy\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class JQueryMobileVersionField extends FormField 
{		
	public $type = 'Jquerymobileversion';
	
	protected function getLabel() 
	{		
		return '';
	}

	protected function getInput() 
	{
		$html = '';
		
		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
				
		$html .= '<div class="migrateversion alert alert-info" style="margin-bottom: 0">';		
		$html .= '  <span>'.\JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISNOTPACKAGEDWITH_LABEL', 'jQuery Mobile').'</span>';		
		$html .= '</div>';
		
		return $html;
	}

}
?>