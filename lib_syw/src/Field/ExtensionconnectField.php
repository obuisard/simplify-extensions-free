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

class ExtensionconnectField extends FormField
{
	public $type = 'Extensionconnect';

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
	    
	    $wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);

		$html = '<div style="padding-top: 5px; overflow: inherit">';

		$html .= '<a class="btn btn-sm btn-info hasTooltip" style="margin: 0 2px; background-color: #02b0e8; border-color: #02b0e8" title="@simplifyyourweb" href="https://twitter.com/simplifyyourweb" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewbox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg> X-Twitter</a>';
		$html .= '<a class="btn btn-sm btn-info hasTooltip" style="margin: 0 2px; background-color: #43609c; border-color: #43609c" title="simplifyyourweb" href="https://www.facebook.com/simplifyyourweb" target="_blank"><i class="SYWicon-facebook" aria-hidden="true">&nbsp;</i>Facebook</a>';
		$html .= '<a class="btn btn-sm btn-info" style="margin: 0 2px; background-color: #ff8f00; border-color: #ff8f00" href="https://simplifyyourweb.com/latest-news?format=feed&amp;type=rss" target="_blank"><i class="SYWicon-rss" aria-hidden="true">&nbsp;</i>News feed</a>';

		$html .= '</div>';

		return $html;
	}

}
?>
