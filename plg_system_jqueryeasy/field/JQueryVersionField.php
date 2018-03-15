<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\JqueryEasy\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;

use Joomla\CMS\Factory;

class JQueryVersionField extends FormField 
{		
	public $type = 'Jqueryversion';
	
	static $versions = array('4.0' => '3.2.1');

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
// 		$html .= '    var version = $.fn.jquery ? $.fn.jquery : "'.$version.'";';		
// 		$html .= '    if (version != "undefined") { $(".jqueryversion span").replaceWith("'.\JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL').' <span class=\'label\'>jQuery " + version + "</span>"); }';
// 		$html .= '  });';
// 		$html .= '</script>';
		
		$html .= '<div class="jqueryversion alert alert-info" style="margin-bottom: 0">';		
		if ($version == 'undefined') {
			$html .= '  <span>'.\JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_UNDETERMINEDVERSION_LABEL', 'jQuery').'</span>';
		} else {
			$html .= '  <span>'.\JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL', 'jQuery '.$version).'</span>';
		}
		$html .= '</div>';
		
		return $html;
	}

}
?>