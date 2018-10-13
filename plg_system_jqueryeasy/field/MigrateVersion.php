<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class JFormFieldMigrateversion extends FormField 
{		
	public $type = 'Migrateversion';
	
	static $versions = array('3.0' => 'none', '3.1' => 'none', '3.2' => '1.2.1', '3.3' => '1.2.1', '3.4' => '1.2.1', '3.5' => '1.2.1', '3.6' => '1.4.1', '3.7' => '1.4.1', '3.8' => '1.4.1', '3.9' => '1.4.1', '4.0' => '1.4.1');

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
		
// 		$html .= '<script type="text/javascript">';
// 		$html .= '  jQuery(document).ready(function($) {';			
// 		$html .= '	  var version = $.migrateVersion ? $.migrateVersion : "'.$version.'";';			
// 		$html .= '    if (version != "undefined") { ';
// 		$html .= '      if (version != "none") { ';
// 		$html .= '        $(".migrateversion span").replaceWith("'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL').' <span class=\'label\'>Migrate " + version + "</span>");';
// 		$html .= '      } else {';
// 		$html .= '        $(".migrateversion span").replaceWith("'.JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISNOTPACKAGEDWITH_LABEL', 'Migrate').'");';
// 		$html .= '	    }';
// 		$html .= '    }';
// 		$html .= '  });';
// 		$html .= '</script>';
		
		$html .= '<div class="migrateversion alert alert-info" style="margin-bottom: 0">';		
		if ($version == 'undefined') {
			$html .= '  <span>'.\JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_UNDETERMINEDVERSION_LABEL', 'Migrate').'</span>';
		} else if ($version == 'none') {
			$html .= '  <span>'.\JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISNOTPACKAGEDWITH_LABEL', 'Migrate').'</span>';
		} else {
			$html .= '  <span>'.\JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL', 'Migrate '.$version).'</span>';
		}
		$html .= '</div>';
		
		Factory::getDocument()->addScriptDeclaration("
			jQuery(document).ready(function ($){
				$.getJSON('https://api.cdnjs.com/libraries/jquery-migrate?fields=version', function(data) {
                    if (data != undefined && data.version != undefined) {
                        $('.migrateversion').append('<br />".\JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSION_LABEL')." <span class=\'label\'>' + data.version + '</span> (".\JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSIONSOURCE_LABEL')." Cloudflare)');
                    }
                });
			});
		");
		
		return $html;
	}

}
?>