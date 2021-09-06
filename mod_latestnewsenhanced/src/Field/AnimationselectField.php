<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\LatestNewsEnhanced\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Field\DynamicsingleselectField;

class AnimationselectField extends DynamicsingleselectField
{
	public $type = 'AnimationSelect';

	protected function getOptions()
	{
		$options = array();
		$options_disabled = array();

		$lang = Factory::getLanguage();

		$path = '/media/mod_latestnewsenhanced/styles/animations';
		$imagepath = '/media/mod_latestnewsenhanced/images/animations';

		$options[] = array('', Text::_('JNONE'), '', Uri::root(true).'/media/mod_latestnewsenhanced/images/select_no.png');

		$optionsArray = Folder::folders(JPATH_SITE.$path);

		foreach ($optionsArray as $option) {

			if ($option != 'justpagination') {
				
				if (!File::exists(JPATH_ROOT . $imagepath . '/' . $option . '.png')) {
					continue;
				}

				$upper_option = strtoupper($option);

				//$lang->load('mod_latestnewsenhancedextended_animation_'.$option);

				$translated_option = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_ANIMATION_'.$upper_option.'_LABEL');

				$description = '';
				if (empty($translated_option) || substr_count($translated_option, 'LATESTNEWSENHANCEDEXTENDED') > 0) {
					$translated_option = ucfirst($option);
				} else {
					$description = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_ANIMATION_'.$upper_option.'_DESC');
					if (substr_count($description, 'LATESTNEWSENHANCEDEXTENDED') > 0) {
						$description = '';
					}
				}

				$image_hover = '';
				if (File::exists(JPATH_ROOT . $imagepath . '/' . $option . '_hover.png')) {
					$image_hover = Uri::root(true) . $imagepath . '/' . $option . '_hover.png';
				}
				
				$badge = 'jQuery';
				if (strpos($upper_option, 'PURE') !== false) {
					$badge = 'javascript';
				}

				if (File::exists(JPATH_ROOT.$path.'/'.$option.'/style.css.php')) {
					$options[] = array($option, $translated_option, $description, Uri::root(true) . $imagepath . '/' . $option . '.png', $image_hover, false, $badge);
				} else {
					$options_disabled[] = array($option, $translated_option . ' (Pro)', $description, Uri::root(true) . $imagepath . '/' . $option . '.png', $image_hover, true, 'Pro,' . $badge);
				}
			}
		}

		$options = array_merge($options, $options_disabled);

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 150;
			$this->maxwidth = 180;
			$this->height = 100;
		}

		return $return;
	}
}
?>