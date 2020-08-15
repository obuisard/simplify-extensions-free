<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Field\GroupedListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\Exception\ExecutionFailureException;
use SYW\Library\K2 as SYWK2;

class DetailSelectField extends GroupedListField
{
	public $type = 'DetailSelect';

	static $core_fields = null;
	static $k2_fields = null;

	static function getCoreFields()
	{
		if (!isset(self::$core_fields)) {
			self::$core_fields = FieldsHelper::getFields('com_content.article');
		}

		return self::$core_fields;
	}

	static function getK2Fields()
	{
		if (!isset(self::$k2_fields)) {

			$db = Factory::getDBO();

			$query = $db->getQuery(true);

			$query->select('fields.id');
			$query->select('fields.type');
			$query->select('fields.name');
			$query->select('groups.name AS group_name');
			$query->from('#__k2_extra_fields AS fields');
			$query->where($db->quoteName('fields.published').'= 1');
			$query->order($db->quoteName('fields.ordering'));

			$query->innerJoin('#__k2_extra_fields_groups AS groups ON groups.id = fields.group');

			$db->setQuery($query);

			try {
				$fields = $db->loadObjectList();
			} catch (ExecutionFailureException $e) {
				$fields = array();
			}

			self::$k2_fields = $fields;
		}

		return self::$k2_fields;
	}

	protected function getGroups()
	{
		$groups = array();

		$groups['-'] = array();

		$groups['-'][] = HTMLHelper::_('select.option', 'hits', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_HITS'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'rating', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_RATING'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'author', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_AUTHOR'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'date', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_DATE'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'ago', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_AGO'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'agomhd', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_AGOMHD'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'agohm', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_AGOHM'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'time', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_TIME'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'category', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_CATEGORY'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'linkedcategory', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKEDCATEGORY'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'tags', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_TAGS') . ' (Pro)', 'value', 'text', $disable = true);
		$groups['-'][] = HTMLHelper::_('select.option', 'selectedtags', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_SELECTEDTAGS') . ' (Pro)', 'value', 'text', $disable = true);
		$groups['-'][] = HTMLHelper::_('select.option', 'linkedtags', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKEDTAGS') . ' (Pro)', 'value', 'text', $disable = true);
		$groups['-'][] = HTMLHelper::_('select.option', 'linkedselectedtags', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKEDSELECTEDTAGS') . ' (Pro)', 'value', 'text', $disable = true);
		$groups['-'][] = HTMLHelper::_('select.option', 'keywords', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_KEYWORDS'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'readmore', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_READMORE'), 'value', 'text', $disable = false);
		$groups['-'][] = HTMLHelper::_('select.option', 'share', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_SHAREICONS') . ' (Pro)', 'value', 'text', $disable = true);

		$groups['-'][] = HTMLHelper::_('select.option', 'linka', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKA') . ' (Pro)', 'value', 'text', $disable = true);
		$groups['-'][] = HTMLHelper::_('select.option', 'linkb', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKB') . ' (Pro)', 'value', 'text', $disable = true);
		$groups['-'][] = HTMLHelper::_('select.option', 'linkc', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKC') . ' (Pro)', 'value', 'text', $disable = true);
		$groups['-'][] = HTMLHelper::_('select.option', 'links', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKS') . ' (Pro)', 'value', 'text', $disable = true);
		$groups['-'][] = HTMLHelper::_('select.option', 'linksnl', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKSNEWLINE') . ' (Pro)', 'value', 'text', $disable = true);

		if (Folder::exists(JPATH_ADMINISTRATOR . '/components/com_jcomments') && ComponentHelper::isEnabled('com_jcomments')) {
			$groups['-'][] = HTMLHelper::_('select.option', 'jcommentscount', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_JCOMMENTSCOUNT') . ' (Pro)', 'value', 'text', $disable = true);
			$groups['-'][] = HTMLHelper::_('select.option', 'linkedjcommentscount', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKEDJCOMMENTSCOUNT') . ' (Pro)', 'value', 'text', $disable = true);
		}

		if (SYWK2::exists()) {
			//$groups['-'][] = HTMLHelper::_('select.option', 'k2_user', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_K2USER'), 'value', 'text', $disable = false);
			$groups['-'][] = HTMLHelper::_('select.option', 'k2commentscount', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_K2COMMENTSCOUNT') . ' (Pro)', 'value', 'text', $disable = true);
			$groups['-'][] = HTMLHelper::_('select.option', 'linkedk2commentscount', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_LINKEDK2COMMENTSCOUNT') . ' (Pro)', 'value', 'text', $disable = true);

			// get K2 extra fields

			$fields = self::getK2Fields();

			// supported field types
			$allowed_types = array('textfield', 'textarea', 'select', 'multipleSelect', 'radio', 'link', /*'labels',*/ 'date');

			$group_name = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_K2EXTRAFIELDS');
			$groups[$group_name] = array();

			foreach ($fields as $field) {
				if (in_array($field->type, $allowed_types)) {
					$groups[$group_name][] = HTMLHelper::_('select.option', 'k2field:'.$field->type.':'.$field->id, 'K2: '.$field->group_name.': '.$field->name . ' (Pro)', 'value', 'text', $disable = true);
				}
			}
		}

		// get Joomla! fields
		// test the fields folder first to avoid message warning that the component is missing
		if (Folder::exists(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_content')->get('custom_fields_enable', '1')) {

			$fields = self::getCoreFields();

			// supported field types
			$allowed_types = array('calendar', 'checkboxes', 'integer', 'list', 'radio', 'text', 'textarea', 'url', 'editor');

			// organize the fields according to their group

			$fieldsPerGroup = array(
					0 => array()
			);

			$groupTitles = array(
				0 => Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_NOGROUPFIELD')
			);

			$fields_exist = false;
			foreach ($fields as $field) {

				if (!in_array($field->type, $allowed_types)) {
					continue;
				}

				if (!array_key_exists($field->group_id, $fieldsPerGroup)) {
					$fieldsPerGroup[$field->group_id] = array();
					$groupTitles[$field->group_id] = $field->group_title;
				}

				$fieldsPerGroup[$field->group_id][] = $field;
				$fields_exist = true;
			}

			// loop trough the groups

			if ($fields_exist) {

				$group_name = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_JOOMLAFIELDS');
				$groups[$group_name] = array();

// 				$options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_JOOMLAFIELDS'));

				foreach ($fieldsPerGroup as $group_id => $groupFields) {

					if (!$groupFields) {
						continue;
					}

					foreach ($groupFields as $field) {
						$groups[$group_name][] = HTMLHelper::_('select.option', 'jfield:'.$field->type.':'.$field->id, $groupTitles[$group_id].': '.$field->title . ' (Pro)', 'value', 'text', $disable = true);
					}
				}

// 				$options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_JOOMLAFIELDS'));
			}
		}

		// Merge any additional options in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}
}
?>