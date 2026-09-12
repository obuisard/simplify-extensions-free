<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Database\Exception\ExecutionFailureException;
use SYW\Library\K2 as SYWK2;

class K2tagsField extends ListField
{
	public $type = 'K2tags';

	/* hide category selection if no k2 */
	public function getLabel()
	{
		if (SYWK2::exists()) {
			return parent::getLabel();
		}

		return '';
	}

	protected function getInput()
	{
		$html = '';

		if (SYWK2::exists()) {
			return parent::getInput();
		} else {
			$lang = Factory::getLanguage();
			$lang->load('lib_syw.sys', JPATH_SITE);

			$html .= '<div style="margin: 0" class="alert alert-danger">';
				$html .= '<span>'.Text::_('LIB_SYW_K2TAGS_MISSING').'</span>';
			$html .= '</div>';
		}

		return $html;
	}

	protected function getOptions()
	{
		$options = array();

		if (SYWK2::exists()) {

			$db = Factory::getDBO();
			$query = $db->getQuery(true);

			$query->select('a.*');
			$query->from('#__k2_tags a');
 			$query->where('a.published = 1');

			$db->setQuery($query);

			try {
				$items = $db->loadObjectList();

				foreach ($items as $item) {
					$options[] = HTMLHelper::_('select.option', $item->id, $item->name);
				}
			} catch (ExecutionFailureException $e) {
				//return false;
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
