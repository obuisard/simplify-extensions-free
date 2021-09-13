<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\JqueryEasy\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Field\DynamicsingleselectField;

class BootstrapselectField extends DynamicsingleselectField
{
	public $type = 'BootstrapSelect';

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		$path = URI::root(true) . '/media/plg_system_jqueryeasy/images';

		$options[] = array(0, Text::_('JNO'), '', $path . '/select_no.png');
		$options[] = array(1, Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_BOOTSTRAP') . ' (Pro)', '', $path . '/select_bootstrap.png', '', 'disabled', 'Pro');

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 101;
			$this->height = 96;
		}

		return $return;
	}
}
?>