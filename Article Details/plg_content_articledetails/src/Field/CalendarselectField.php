<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\Content\ArticleDetails\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use SYW\Library\Field\DynamicsingleselectField;

class CalendarselectField extends DynamicsingleselectField
{
	public $type = 'Calendarselect';

	protected function getOptions()
	{
		$options = array();
		$options_disabled = array();

		$lang = Factory::getLanguage();
		$lang->load('plg_content_articledetails');

		$path = '/media/plg_content_articledetails/styles/calendars';
		$imagepath = '/media/plg_content_articledetails/images/calendars';

		$optionsArray = Folder::folders(JPATH_SITE.$path);

		foreach ($optionsArray as $option) {

			$upper_option = strtoupper($option);

			$translated_option = Text::_('PLG_CONTENT_ARTICLEDETAILS_STYLE_CALENDAR_'.$upper_option.'_LABEL');

			$description = '';
			if (empty($translated_option) || substr_count($translated_option, 'ARTICLEDETAILS') > 0) {
				$translated_option = ucfirst($option);
			} else {
				$description = Text::_('PLG_CONTENT_ARTICLEDETAILS_STYLE_CALENDAR_'.$upper_option.'_DESC');
				if (substr_count($description, 'ARTICLEDETAILS') > 0) {
					$description = '';
				}
			}

			$image_hover = '';
			if (is_file(JPATH_ROOT . $imagepath . '/' . $option . '_hover.png')) {
				$image_hover = Uri::root(true) . $imagepath . '/' . $option . '_hover.png';
			}

			if (is_file(JPATH_ROOT.$path.'/'.$option.'/style.css.php')) {
				$options[] = array($option, $translated_option, $description, Uri::root(true) . $imagepath . '/' . $option . '.png', $image_hover);
			} else {
				$options_disabled[] = array($option, $translated_option . ' (Pro)', $description, Uri::root(true) . $imagepath . '/' . $option . '.png', $image_hover, 'disabled');
			}
		}

		$options = array_merge($options, $options_disabled);

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 192;
			$this->height = 96;
		}

		return $return;
	}
}
?>