<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Factory;
use Joomla\Database\Exception\ExecutionFailureException;

class ViewsField extends GroupedlistField
{
	public $type = 'Views';

	protected $extension_option;
	protected $extension_view;

	protected function getGroups()
	{
		$groups = array();

		$db = Factory::getDBO();

		$additional_tag = '';
		if (Multilanguage::isEnabled()) {
			$additional_tag = ', " (", a.language, ")"';
		}

		$extension_views = explode(",", $this->extension_view);

		if (count($extension_views) > 1) {

			foreach ($extension_views as $extension_view) {

				$query = $db->getQuery(true);

				$query->select('DISTINCT a.id AS value, CONCAT(a.title, " (", a.alias, ")"'.$additional_tag.') AS text, a.alias, a.level, a.menutype, a.type, a.template_style_id, a.checked_out');
				$query->from('#__menu AS a');
				$query->join('LEFT', $db->quoteName('#__menu') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
				$query->where('a.link LIKE '.$db->quote('%option='.$this->extension_option.'&view='.$extension_view.'%'));
				$query->where('a.published = 1');

				$db->setQuery($query);
				try {
					$results = $db->loadObjectList();

					if (count($results) > 0) {
						$groups['[' . $extension_view . ']'] = array();
						foreach ($results as $result) {
							$groups['[' . $extension_view . ']'][] = HTMLHelper::_('select.option', $extension_view . ':' . $result->value, $result->text, 'value', 'text', $disable = false);
						}
					}
				} catch (ExecutionFailureException $e) {
					//return false;
				}
			}
		} else {
			$query = $db->getQuery(true);

			$query->select('DISTINCT a.id AS value, CONCAT(a.title, " (", a.alias, ")"'.$additional_tag.') AS text, a.alias, a.level, a.menutype, a.type, a.template_style_id, a.checked_out');
			$query->from('#__menu AS a');
			$query->join('LEFT', $db->quoteName('#__menu') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
			$query->where('a.link LIKE '.$db->quote('%option='.$this->extension_option.'&view='.$extension_views[0].'%'));
			$query->where('a.published = 1');

			$db->setQuery($query);

			try {
				$results = $db->loadObjectList();

				if (count($results) > 0) {
					$groups[0] = array();
					foreach ($results as $result) {
						$groups[0][] = HTMLHelper::_('select.option', $result->value, $result->text, 'value', 'text', $disable = false);
					}
				}
			} catch (ExecutionFailureException $e) {
				//return false;
			}
		}

		// Merge any additional options in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->extension_option = isset($this->element['option']) ? trim((string)$this->element['option']) : '';
			$this->extension_view = isset($this->element['view']) ? (string)$this->element['view'] : '';
		}

		return $return;
	}

}
