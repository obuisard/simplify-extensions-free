<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class JFormFieldBootstrapVersion extends FormField 
{		
	public $type = 'Bootstrapversion';
	
	static $versions = array('4.0' => '4.0.0');
	
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
// 		$html .= '    var version = $.fn.tooltip.Constructor.VERSION ? $.fn.tooltip.Constructor.VERSION : "2.3.2";';
// 		$html .= '    if (version != "undefined") { ';
// 		$html .= '      $(".bootstrapversion span").replaceWith("<span>" + "'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL').' <span class=\'label\'>Bootstrap " + version + "</span>");';
// 		$html .= '    }';
// 		$html .= '  });';
// 		$html .= '</script>';
		
		$html .= '<div class="bootstrapversion alert alert-info" style="margin-bottom: 0">';
		$html .= '  <span>'.Text::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL', 'Bootstrap '.$version).'</span>';
		$html .= '</div>';
		
		Factory::getDocument()->addScriptDeclaration("
			jQuery(document).ready(function ($){
				$.getJSON('https://api.cdnjs.com/libraries/twitter-bootstrap?fields=version', function(data) {
                    if (data != undefined && data.version != undefined) {
                        $('.bootstrapversion').append('<br />".Text::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSION_LABEL')." <span class=\'label\'>' + data.version + '</span> (".Text::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSIONSOURCE_LABEL')." Cloudflare)');
                    }
                });
			});
		");
		
		return $html;
	}

}
?>