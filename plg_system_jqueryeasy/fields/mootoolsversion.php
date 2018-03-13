<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldMootoolsversion extends JFormField {
		
	public $type = 'Mootoolsversion';

	protected function getLabel() 
	{		
		return '';
	}

	protected function getInput() 
	{
		$html = '';
		
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		$html .= '<div class="mootoolsversion alert alert-info" style="margin-bottom: 0">';		
		$html .= '  <span>'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_MOOTOOLSVERSIONPACKAGED_LABEL').'</span>';		
		$html .= '</div>';
		
		return $html;
	}

}
?>