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

class K2categoryField extends ListField
{
	public $type = 'K2category';

	/* hide category selection if no k2 */
	public function getLabel()
	{
		if (SYWK2::exists()) {
			return parent::getLabel();
		}

		return '';
	}

	public function getInput()
	{
		$html = '';

		if (SYWK2::exists()) {
			return parent::getInput();
		} else {
			$lang = Factory::getLanguage();
			$lang->load('lib_syw.sys', JPATH_SITE);

			$html .= '<div style="margin: 0" class="alert alert-danger">';
				$html .= '<span>'.Text::_('LIB_SYW_K2CATEGORY_MISSING').'</span>';
			$html .= '</div>';
		}

		return $html;
	}

	public function getOptions()
	{
		$options = array();

		if (isset($this->element['show_root']))	{
			array_unshift($options, HTMLHelper::_('select.option', '0', Text::_('JGLOBAL_ROOT')));
		}

		if (SYWK2::exists()) {

			$db = Factory::getDBO();
			$query = $db->getQuery(true);

			$query->select('k2cat.*');
			$query->from('#__k2_categories AS k2cat');
			$query->where('k2cat.published=1');
			$query->where('k2cat.trash=0');
			$query->order('k2cat.parent');

			$db->setQuery($query);

			try {
				$categories = $db->loadObjectList();

				$children = array();
				if ($categories != "" ) {
					if ($categories) {
						foreach ($categories as $category) {
							$category->title = $category->name;
							$category->parent_id = $category->parent;
							$parent = $category->parent;
							$list = @$children[$parent] ? $children[$parent] : array();
							array_push($list, $category);
							$children[$parent] = $list;
						}
					}
					$list = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);

					foreach ($list as $item) {
						$options[] = HTMLHelper::_('select.option', $item->id, $item->treename);
					}
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
