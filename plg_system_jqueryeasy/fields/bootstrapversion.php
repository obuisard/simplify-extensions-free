<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldBootstrapversion extends JFormField {
		
	public $type = 'Bootstrapversion';
	
	protected function getLabel() 
	{
		return '';
	}

	protected function getInput() 
	{
		$html = '';
		
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		$version = '2.3.2';
		
// 		$html .= '<script type="text/javascript">';
// 		$html .= '  jQuery(document).ready(function($) {';
// 		$html .= '    var version = $.fn.tooltip.Constructor.VERSION ? $.fn.tooltip.Constructor.VERSION : "2.3.2";';
// 		$html .= '    if (version != "undefined") { ';
// 		$html .= '      $(".bootstrapversion span").replaceWith("<span>" + "'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL').' <span class=\'label\'>Bootstrap " + version + "</span>");';
// 		$html .= '    }';
// 		$html .= '  });';
// 		$html .= '</script>';
		
		$html .= '<div class="bootstrapversion alert alert-info" style="margin-bottom: 0">';
		$html .= '  <span>'.JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL', 'Bootstrap '.$version).'</span>';
		$html .= '</div>';
		
		return $html;
	}

}
?>