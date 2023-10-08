<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\JQueryEasy\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/*
 * Checks if the plugin is enabled and report on the position used
 */
class JchoptimizetestField extends FormField
{
	public $type = 'Jchoptimizetest';

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$html = '';

		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		if (PluginHelper::isEnabled('system', 'jch_optimize')) {

			$plugin = PluginHelper::getPlugin('system', 'jch_optimize');

			$registry = new Registry;
			$registry->loadString($plugin->params);

			$use_file_combination = $registry->get('combine_files_enable', true);

			$html .= '<div class="alert alert-warning" style="margin: 0">';
			if ($use_file_combination) {
				$html .= '<span>'.Text::_('PLG_SYSTEM_JQUERYEASY_WARNING_JCHOPTIMIZEENABLED').'</span><br />';
			}
			$html .= '</div>';
		}

		return $html;
	}

}
?>