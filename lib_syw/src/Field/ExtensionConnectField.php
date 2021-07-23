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
	    $wam = Factory::getApplication()->getDocument()->getWebAssetManager();
	    
	    HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
	    
	    $wam->registerAndUseStyle('syw.font', 'syw/fonts-min.css', ['relative' => true, 'version' => 'auto']);

		$html = '<div style="padding-top: 5px; overflow: inherit">';

		$html .= '<a class="btn btn-sm btn-info hasTooltip" style="margin: 0 2px; background-color: #02b0e8; border-color: #02b0e8" title="@simplifyyourweb" href="https://twitter.com/simplifyyourweb" target="_blank"><i class="SYWicon-twitter" aria-hidden="true">&nbsp;</i>Twitter</a>';
		$html .= '<a class="btn btn-sm btn-info hasTooltip" style="margin: 0 2px; background-color: #43609c; border-color: #43609c" title="simplifyyourweb" href="https://www.facebook.com/simplifyyourweb" target="_blank"><i class="SYWicon-facebook" aria-hidden="true">&nbsp;</i>Facebook</a>';
		$html .= '<a class="btn btn-sm btn-info" style="margin: 0 2px; background-color: #ff8f00; border-color: #ff8f00" href="https://simplifyyourweb.com/latest-news?format=feed&amp;type=rss" target="_blank"><i class="SYWicon-rss" aria-hidden="true">&nbsp;</i>News feed</a>';

		$html .= '</div>';

		return $html;
	}

}
?>
