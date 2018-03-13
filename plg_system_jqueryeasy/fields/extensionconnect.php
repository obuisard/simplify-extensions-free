<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldExtensionConnect extends JFormField
{
	public $type = 'ExtensionConnect';
	
	protected function getLabel()
	{
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy', JPATH_SITE);
		
		$html = '';
		
		//$html .= '<div style="clear: both;">'.JText::_('PLG_SYSTEM_JQUERYEASY_CONNECT_LABEL').'</div>';
		
		return $html;
	}
	
	protected function getInput()
	{
		JHtml::_('bootstrap.tooltip');
		
		$html = '<div style="padding-top: 5px; overflow: inherit">';
		
		$html .= '<a class="label hasTooltip" style="background-color: #02b0e8; padding: 4px 8px; margin: 0 3px 0 0;" title="@simplifyyourweb" href="https://twitter.com/simplifyyourweb" target="_blank">Twitter</a>';
		$html .= '<a class="label hasTooltip" style="background-color: #db4437; padding: 4px 8px; margin: 0 3px;" title="+Simplifyyourweb" href="https://plus.google.com/+Simplifyyourweb" target="_blank">Google+</a>';
		$html .= '<a class="label hasTooltip" style="background-color: #43609c; padding: 4px 8px; margin: 0 3px;" title="simplifyyourweb" href="https://www.facebook.com/simplifyyourweb" target="_blank">Facebook</a>';
		$html .= '<a class="label" style="background-color: #ff8f00; padding: 4px 8px; margin: 0 3px;" href="https://simplifyyourweb.com/latest-news?format=feed&amp;type=rss" target="_blank">News feed</a>';
		
		$html .= '</div>';
		
		return $html;
	}
	
}
?>
