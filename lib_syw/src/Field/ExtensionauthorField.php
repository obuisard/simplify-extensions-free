<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class ExtensionauthorField extends FormField
{
	public $type = 'Extensionauthor';

	protected function getLabel()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$html = '';

		$html .= '<div style="clear: both;">'.Text::_('LIB_SYW_EXTENSIONAUTHOR_AUTHOR_LABEL').'</div>';

		return $html;
	}

	protected function getInput()
	{
		$html = '<div style="padding-top: 5px; overflow: inherit">';

		$html .= 'Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank" style="margin-left: 5px; padding: 5px; border-radius: 3px; background-color: #fff">';
			$html .= '<img alt="Simplify Your Web" src="'.URI::root(true).'/media/syw/images/simplifyyourweb.png">';
		$html .= '</a>';

		$html .= '</div>';

		return $html;
	}

}
?>
