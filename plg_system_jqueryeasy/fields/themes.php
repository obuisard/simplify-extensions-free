<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldThemes extends JFormField {
		
	public $type = 'Themes';

	protected function getLabel() 
	{		
		return '';
	}

	protected function getInput() 
	{
		$html = '';
		
		// Add the script to the document head.
		$doc = JFactory::getDocument();
		$doc->addStylesheet(JURI::root(true).'/plugins/system/jqueryeasy/fields/themes/css/themes.css');
		
		$type = strtolower($this->type);
		
		ob_start();
		require_once dirname(__FILE__).'/'.$type.'/tmpl/default.php';
		$html .= ob_get_contents();
		ob_end_clean();
		
		return $html;		
	}

}
?>