<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

class JFormFieldThemes extends FormField 
{		
	public $type = 'Themes';

	protected function getLabel() 
	{		
		return '';
	}

	protected function getInput() 
	{
		$html = '';
		
		// Add the script to the document head.
		$doc = Factory::getDocument();
		$doc->addStylesheet(Uri::root(true).'/plugins/system/jqueryeasy/field/themes/css/themes.css');
		
		$type = strtolower($this->type);
		
		ob_start();
		require_once dirname(__FILE__).'/'.$type.'/tmpl/default.php';
		$html .= ob_get_contents();
		ob_end_clean();
		
		return $html;		
	}

}
?>