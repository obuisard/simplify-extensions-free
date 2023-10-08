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

class BootstrapselectField extends ListField
{
	public $type = 'bootstrapselect';

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		if (Factory::getApplication()->getDocument()->getWebAssetManager()->assetExists('script', 'bootstrap.es5')) {
		    $options[] = HTMLHelper::_('select.option', 'joomla', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_JOOMLA'), 'value', 'text', $disable = false);
		}
		
		$options[] = HTMLHelper::_('select.option', 'local', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_LOCAL'), 'value', 'text', $disable = false);
		
		$options[] = HTMLHelper::_('select.option', '5.3', '5.3.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '5.2', '5.2.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '5.1', '5.1.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '5.0', '5.0.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '4.6', '4.6.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '4.5', '4.5.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '4.4', '4.4.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '4.3', '4.3.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '4.2', '4.2.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '4.1', '4.1.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '4.0', '4.0.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.4', '3.4.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.3', '3.3.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.2', '3.2.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.1', '3.1.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '3.0', '3.0.x', 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', '2.3.2', '2.3.2', 'value', 'text', $disable = false);
		

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
?>