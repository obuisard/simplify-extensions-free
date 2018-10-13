<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class JFormFieldJQueryMobileVersion extends FormField 
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
				
		$html .= '<div class="jquerymobileversion alert alert-info" style="margin-bottom: 0">';		
		$html .= '  <span>'.\JText::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISNOTPACKAGEDWITH_LABEL', 'jQuery Mobile').'</span>';		
		$html .= '</div>';
		
		Factory::getDocument()->addScriptDeclaration("
			jQuery(document).ready(function ($){
				$.getJSON('https://api.cdnjs.com/libraries/jquery-mobile?fields=version', function(data) {
                    if (data != undefined && data.version != undefined) {
                        $('.jquerymobileversion').append('<br />".\JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSION_LABEL')." <span class=\'label\'>' + data.version + '</span> (".\JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSIONSOURCE_LABEL')." Cloudflare)');
                    }
                });
			});
		");
		
		return $html;
	}

}
?>