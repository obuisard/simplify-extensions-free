<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined( '_JEXEC' ) or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

FormHelper::loadFieldClass('dynamicsingleselectjqe');

class JFormFieldjQueryUIThemeSelect extends DynamicSingleSelectJQE
{
	public $type = 'jQueryUIThemeSelect';

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		$path = '/plugins/system/jqueryeasy/field/themes/images';
		
		$options[] = array('none', Text::_('JNONE'), '', URI::root(true).$path.'/none.png');
		
		$options[] = array('custom', Text::_('PLG_SYSTEM_JQUERYEASY_VALUE_CUSTOMLOCAL'), '', URI::root(true).$path.'/custom.png');
		
		$options[] = array('base', 'Base', '', URI::root(true).$path.'/base.png');
		$options[] = array('black-tie', 'Black Tie', '', URI::root(true).$path.'/black_tie.png');
		$options[] = array('blitzer', 'Blitzer', '', URI::root(true).$path.'/blitzer.png');
		$options[] = array('cupertino', 'Cupertino', '', URI::root(true).$path.'/cupertino.png');
		$options[] = array('dark-hive', 'Dark Hive', '', URI::root(true).$path.'/dark_hive.png');
		$options[] = array('dot-luv', 'Dot Luv', '', URI::root(true).$path.'/dot_luv.png');
		$options[] = array('eggplant', 'Eggplant', '', URI::root(true).$path.'/eggplant.png');
		$options[] = array('excite-bike', 'Excite Bike', '', URI::root(true).$path.'/excite_bike.png');
		$options[] = array('flick', 'Flick', '', URI::root(true).$path.'/flick.png');
		$options[] = array('hot-sneaks', 'Hot Sneaks', '', URI::root(true).$path.'/hot_sneaks.png');
		$options[] = array('humanity', 'Humanity', '', URI::root(true).$path.'/humanity.png');
		$options[] = array('le-frog', 'Le Frog', '', URI::root(true).$path.'/le_frog.png');
		$options[] = array('mint-choc', 'Mint Choc', '', URI::root(true).$path.'/mint_choco.png');
		$options[] = array('overcast', 'Overcast', '', URI::root(true).$path.'/overcast.png');
		$options[] = array('pepper-grinder', 'Pepper Grinder', '', URI::root(true).$path.'/pepper_grinder.png');
		$options[] = array('redmond', 'Redmond', '', URI::root(true).$path.'/windoze.png');
		$options[] = array('smoothness', 'Smoothness', '', URI::root(true).$path.'/smoothness.png');
		$options[] = array('south-street', 'South Street', '', URI::root(true).$path.'/south_street.png');
		$options[] = array('start', 'Start', '', URI::root(true).$path.'/start_menu.png');
		$options[] = array('sunny', 'Sunny', '', URI::root(true).$path.'/sunny.png');
		$options[] = array('swanky-purse', 'Swanky Purse', '', URI::root(true).$path.'/swanky_purse.png');
		$options[] = array('trontastic', 'Trontastic', '', URI::root(true).$path.'/trontastic.png');
		$options[] = array('ui-darkness', 'UI Darkness', '', URI::root(true).$path.'/ui_dark.png');
		$options[] = array('ui-lightness', 'UI Lightness', '', URI::root(true).$path.'/ui_light.png');
		$options[] = array('vader', 'Vader', '', URI::root(true).$path.'/black_matte.png');

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