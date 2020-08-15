<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\TrombinoscopeContacts\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Field\DynamicSingleSelect;

class StyleSelectField extends DynamicSingleSelect
{
	public $type = 'StyleSelect';

	protected $img_suffix;

	protected function getOptions()
	{
		$options = array();

		$lang = Factory::getLanguage();

		$path = '/media/mod_trombinoscopecontacts/styles/themes';
		$imagepath = '/media/mod_trombinoscopecontacts/images/themes';

		$optionsArray = Folder::folders(JPATH_SITE.$path);

		foreach($optionsArray as $option) {

			$upper_option = strtoupper($option);

			$lang->load('com_trombinoscopeextended_theme_'.$option);

			$translated_option = Text::_('MOD_TROMBINOSCOPE_THEME_'.$upper_option.'_LABEL');

			$description = '';
			if (empty($translated_option) || substr_count($translated_option, 'TROMBINOSCOPE') > 0) {
				$translated_option = ucfirst($option);
			} else {
				$description = Text::_('MOD_TROMBINOSCOPE_THEME_'.$upper_option.'_DESC');
				if (substr_count($description, 'TROMBINOSCOPE') > 0) {
					$description = '';
				}
			}

			$image_hover = '';

			$options[] = array($option, $translated_option, $description, URI::root(true) . $imagepath . '/' . $option . $this->img_suffix . '.png', $image_hover);
		}

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 240;
			$this->height = 125;
			$this->img_suffix = isset($this->element['imgsuffix']) ? $this->element['imgsuffix'] : '';
		}

		return $return;
	}
}
?>