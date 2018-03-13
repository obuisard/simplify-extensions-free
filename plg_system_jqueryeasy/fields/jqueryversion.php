<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldJqueryversion extends JFormField {
		
	public $type = 'Jqueryversion';
	
	static $versions = array('3.0' => '1.8.1', '3.1' => '1.8.3', '3.2' => '1.11.0', '3.3' => '1.11.1', '3.4' => '1.11.3', '3.5' => '1.11.3', '3.6' => '1.12.4', '3.7' => '1.12.4', '3.8' => '1.12.4');

	protected function getLabel() 
	{		
		return '';
	}

	protected function getInput() 
	{
		$html = '';
		
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		$version = 'undefined';
		
		$numbers = explode('.', JVERSION);
		$joomla_release = $numbers[0].'.'.$numbers[1];
		
		if (isset(self::$versions[$joomla_release])) {
			$version = self::$versions[$joomla_release];
		}
		
// 		$html .= '<script type="text/javascript">';
// 		$html .= '  jQuery(document).ready(function($) {';
// 		$html .= '    var version = $.fn.jquery ? $.fn.jquery : "'.$version.'";';		
// 		$html .= '    if (version != "undefined") { $(".jqueryversion span").replaceWith("'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL').' <span class=\'label\'>jQuery " + version + "</span>"); }';
// 		$html .= '  });';
// 		$html .= '</script>';
		
		$html .= '<div class="jqueryversion alert alert-info" style="margin-bottom: 0">';		
		if ($version == 'undefined') {
			$html .= '  <span>'.JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_UNDETERMINEDVERSION_LABEL', 'jQuery').'</span>';
		} else {
			$html .= '  <span>'.JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL', 'jQuery '.$version).'</span>';
		}
		$html .= '</div>';
		
		return $html;
	}

}
?>