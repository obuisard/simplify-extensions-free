<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

class ExtensionConnectField extends FormField
{
	public $type = 'ExtensionConnect';
	
	protected function getLabel()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		$html = '';
		return $html;
	}
	
	protected function getInput()
	{
	    HTMLHelper::_('stylesheet', 'syw/fonts-min.css', ['version' => 'auto', 'relative' => true]);
		HTMLHelper::_('bootstrap.tooltip');
		
		$html = '<div style="padding-top: 5px; overflow: inherit">';
		
		$html .= '<a class="badge hasTooltip" style="background-color: #02b0e8; color: #fff; padding: 4px 8px; margin: 0 3px 0 0;" title="@simplifyyourweb" href="https://twitter.com/simplifyyourweb" target="_blank"><i class="SYWicon-twitter">&nbsp;</i>Twitter</a>';
		$html .= '<a class="badge hasTooltip" style="background-color: #db4437; color: #fff; padding: 4px 8px; margin: 0 3px;" title="+Simplifyyourweb" href="https://plus.google.com/+Simplifyyourweb" target="_blank"><i class="SYWicon-google">&nbsp;</i>Google+</a>';
		$html .= '<a class="badge hasTooltip" style="background-color: #43609c; color: #fff; padding: 4px 8px; margin: 0 3px;" title="simplifyyourweb" href="https://www.facebook.com/simplifyyourweb" target="_blank"><i class="SYWicon-facebook">&nbsp;</i>Facebook</a>';
		$html .= '<a class="badge" style="background-color: #ff8f00; color: #fff; padding: 4px 8px; margin: 0 3px;" href="https://simplifyyourweb.com/latest-news?format=feed&amp;type=rss" target="_blank"><i class="SYWicon-rss">&nbsp;</i>News feed</a>';
		
		$html .= '</div>';
		
		return $html;
	}
	
}
?>
