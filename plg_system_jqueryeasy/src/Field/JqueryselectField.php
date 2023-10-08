<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Plugin\System\JQueryEasy\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JqueryselectField extends ListField
{
	public $type = 'jqueryselect';

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		if (Factory::getApplication()->getDocument()->getWebAssetManager()->assetExists('script', 'jquery')) {
		    $options[] = HTMLHelper::_('select.option', 'joomla', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_JOOMLA'), 'value', 'text', $disable = false);
		}
		$options[] = HTMLHelper::_('select.option', 'local', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_LOCAL'), 'value', 'text', $disable = false);
		
		$options[] = HTMLHelper::_('select.option', '3.7#slim', '3.7.x slim (Pro)', 'value', 'text', $disable = true);
		$options[] = HTMLHelper::_('select.option', '3.7', '3.7.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.6#slim', '3.6.x slim (Pro)', 'value', 'text', $disable = true);
		$options[] = HTMLHelper::_('select.option', '3.6', '3.6.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.5#slim', '3.5.x slim (Pro)', 'value', 'text', $disable = true);
		$options[] = HTMLHelper::_('select.option', '3.5', '3.5.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.4#slim', '3.4.x slim (Pro)', 'value', 'text', $disable = true);
		$options[] = HTMLHelper::_('select.option', '3.4', '3.4.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.3#slim', '3.3.x slim (Pro)', 'value', 'text', $disable = true);
		$options[] = HTMLHelper::_('select.option', '3.3', '3.3.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.2#slim', '3.2.x slim (Pro)', 'value', 'text', $disable = true);
		$options[] = HTMLHelper::_('select.option', '3.2', '3.2.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.1#slim', '3.1.x slim (Pro)', 'value', 'text', $disable = true);
		$options[] = HTMLHelper::_('select.option', '3.1', '3.1.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.0#slim', '3.0.x slim (Pro)', 'value', 'text', $disable = true);
		$options[] = HTMLHelper::_('select.option', '3.0', '3.0.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '2.2', '2.2.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '2.1', '2.1.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '2.0', '2.0.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '1.12', '1.12.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '1.11', '1.11.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '1.10', '1.10.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '1.9', '1.9.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '1.8', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_LATEST18'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '1.7', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_LATEST17'), 'value', 'text', $disable = false);
// 		$options[] = HTMLHelper::_('select.option', '1.6', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_LATEST16'), 'value', 'text', $disable = false);
// 		$options[] = HTMLHelper::_('select.option', '1.5', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_LATEST15'), 'value', 'text', $disable = false);
// 		$options[] = HTMLHelper::_('select.option', '1.4', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_LATEST14'), 'value', 'text', $disable = false);
// 		$options[] = HTMLHelper::_('select.option', '1.3', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_LATEST13'), 'value', 'text', $disable = false);
		

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
?>