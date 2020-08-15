<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\JqueryEasy\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Field\DynamicSingleSelect;

class JQueryUIThemeSelectField extends DynamicSingleSelect
{
	public $type = 'jQueryUIThemeSelect';

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		$path = URI::root(true) . '/media/plg_system_jqueryeasy/images/themes';

		$options[] = array('none', Text::_('JNONE'), '', $path . '/none.png');

		$options[] = array('custom', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_CUSTOMLOCAL'), '', $path . '/custom.png');

		$options[] = array('base', 'Base', '', $path . '/base.png');
		$options[] = array('black-tie', 'Black Tie', '', $path . '/black_tie.png');
		$options[] = array('blitzer', 'Blitzer', '', $path . '/blitzer.png');
		$options[] = array('cupertino', 'Cupertino', '', $path . '/cupertino.png');
		$options[] = array('dark-hive', 'Dark Hive', '', $path . '/dark_hive.png');
		$options[] = array('dot-luv', 'Dot Luv', '', $path . '/dot_luv.png');
		$options[] = array('eggplant', 'Eggplant', '', $path . '/eggplant.png');
		$options[] = array('excite-bike', 'Excite Bike', '', $path . '/excite_bike.png');
		$options[] = array('flick', 'Flick', '', $path . '/flick.png');
		$options[] = array('hot-sneaks', 'Hot Sneaks', '', $path . '/hot_sneaks.png');
		$options[] = array('humanity', 'Humanity', '', $path . '/humanity.png');
		$options[] = array('le-frog', 'Le Frog', '', $path . '/le_frog.png');
		$options[] = array('mint-choc', 'Mint Choc', '', $path . '/mint_choco.png');
		$options[] = array('overcast', 'Overcast', '', $path . '/overcast.png');
		$options[] = array('pepper-grinder', 'Pepper Grinder', '', $path . '/pepper_grinder.png');
		$options[] = array('redmond', 'Redmond', '', $path . '/windoze.png');
		$options[] = array('smoothness', 'Smoothness', '', $path . '/smoothness.png');
		$options[] = array('south-street', 'South Street', '', $path . '/south_street.png');
		$options[] = array('start', 'Start', '', $path . '/start_menu.png');
		$options[] = array('sunny', 'Sunny', '', $path . '/sunny.png');
		$options[] = array('swanky-purse', 'Swanky Purse', '', $path . '/swanky_purse.png');
		$options[] = array('trontastic', 'Trontastic', '', $path . '/trontastic.png');
		$options[] = array('ui-darkness', 'UI Darkness', '', $path . '/ui_dark.png');
		$options[] = array('ui-lightness', 'UI Lightness', '', $path . '/ui_light.png');
		$options[] = array('vader', 'Vader', '', $path . '/black_matte.png');

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 95;
			$this->height = 95;
		}

		return $return;
	}
}
?>