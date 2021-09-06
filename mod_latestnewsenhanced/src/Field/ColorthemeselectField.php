<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Field\DynamicsingleselectField;

class ColorthemeselectField extends DynamicsingleselectField
{
	public $type = 'Colorthemeselect';

	protected function getOptions()
	{
		$options = array();
		$options_disabled = array();

		$lang = Factory::getLanguage();

		$path = '/media/mod_latestnewsenhanced/styles/colors';
		$imagepath = '/media/mod_latestnewsenhanced/images/colors';
		
		$options[] = array('', Text::_('JGLOBAL_INHERIT'), '', '');

		$optionsArray = Folder::folders(JPATH_SITE.$path);

		foreach ($optionsArray as $option) {
			
			if (!File::exists(JPATH_ROOT . $imagepath . '/' . $option . '.png')) {
				continue;
			}

			$upper_option = strtoupper($option);

			//$lang->load('mod_latestnewsenhancedextended_style_overall_'.$option);

			$translated_option = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_STYLE_COLORTHEME_'.$upper_option.'_LABEL');

			$description = '';
			if (empty($translated_option) || substr_count($translated_option, 'LATESTNEWSENHANCEDEXTENDED') > 0) {
				$translated_option = ucfirst($option);
			} else {
				$description = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_STYLE_COLORTHEME_'.$upper_option.'_DESC');
				if (substr_count($description, 'LATESTNEWSENHANCEDEXTENDED') > 0) {
					$description = '';
				}
			}
			
			$options[] = array($option, $translated_option, $description, Uri::root(true) . $imagepath . '/' . $option . '.png', '');
		}

		$options = array_merge($options, $options_disabled);

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 90;
			$this->height = 90;
		}

		return $return;
	}
}
?>