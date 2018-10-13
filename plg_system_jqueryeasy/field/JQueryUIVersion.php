<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class JFormFieldJQueryUIVersion extends FormField 
{		
	public $type = 'Jqueryuiversion';
	
	static $versions = array('4.0' => '1.9.2');
	
	protected function getLabel() 
	{		
		return '';
	}

	protected function getInput() 
	{		
		$html = '';
		
		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		$version = 'undefined';
		
		$numbers = explode('.', JVERSION);		
		$joomla_release = $numbers[0].'.'.$numbers[1];
		
		if (isset(self::$versions[$joomla_release])) {
			$version = self::$versions[$joomla_release];
		}
		
		// to check for jQuery UI
		// if ($.ui && $.ui.version) { // jQuery UI is loaded }
		
// 		$html .= '<script type="text/javascript">';
// 		$html .= '  jQuery(document).ready(function($) {';
// 		$html .= '    if ($.ui) {';
// 		$html .= '      var version = $.ui.version ? $.ui.version : "'.$version.'";';
// 		$html .= '      if (version != "undefined") { ';
// 		$html .= '        $(".jqueryuiversion span").replaceWith("<span>" + "'.\JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL').' <span class=\'label\'>jQuery UI " + version + "</span>");';
// 		$html .= '      }';
// 		$html .= '    } else {';
// 		$html .= '      var version = "'.$version.'";';
// 		$html .= '      if (version != "undefined") { ';
// 		$html .= '        $(".jqueryuiversion span").replaceWith("<span>" + "'.\JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL').' <span class=\'label\'>jQuery UI " + version + "</span>");';
// 		$html .= '      }';
// 		$html .= '    }';
// 		$html .= '  });';
// 		$html .= '</script>';
		
		$html .= '<div class="jqueryuiversion alert alert-info" style="margin-bottom: 0">';		
		if ($version == 'undefined') {
			$html .= '  <span>'.\JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_UNDETERMINEDVERSION_LABEL', 'jQuery UI').'</span>';		
		} else {
			$html .= '  <span>'.\JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL', 'jQuery UI '.$version).'</span>';
		}
		$html .= '</div>';
		
		Factory::getDocument()->addScriptDeclaration("
			jQuery(document).ready(function ($){
				$.getJSON('https://api.cdnjs.com/libraries/jqueryui?fields=version', function(data) {
                    if (data != undefined && data.version != undefined) {
                        $('.jqueryuiversion').append('<br />".\JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSION_LABEL')." <span class=\'label\'>' + data.version + '</span> (".\JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSIONSOURCE_LABEL')." Cloudflare)');
                    }
                });
			});
		");
		
		return $html;
	}

}
?>