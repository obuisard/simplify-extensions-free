<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Plugin\Content\ArticleDetails\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

class DetailselectField extends GroupedlistField
{
	public $type = 'Detailselect';

	static $core_fields = null;

	static function getCoreFields()
	{
		if (!isset(self::$core_fields)) {
			self::$core_fields = FieldsHelper::getFields('com_content.article');
		}

		return self::$core_fields;
	}

	protected function getGroups()
	{
		$groups = array();

		$group_name = Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_STANDARDFIELDS');
		$groups[$group_name] = array();

		$groups[$group_name][] = HTMLHelper::_('select.option', 'hits', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_HITS'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'rating', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_RATING'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'author', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_AUTHOR'), 'value', 'text', $disable = false);

		if (is_dir(JPATH_ADMINISTRATOR . '/components/com_comprofiler') && ComponentHelper::isEnabled('com_comprofiler')) {
			$groups[$group_name][] = HTMLHelper::_('select.option', 'authorcb', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_AUTHORCB'), 'value', 'text', $disable = false);
		}

		$groups[$group_name][] = HTMLHelper::_('select.option', 'created', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_CREATEDDATE'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'modified', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_MODIFIEDDATE'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'published', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_PUBLISHEDDATE'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'finished', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_FINISHEDDATE'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'createdtime', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_CREATEDTIME'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'modifiedtime', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_MODIFIEDTIME'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'publishedtime', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_PUBLISHEDTIME'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'finishedtime', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_FINISHEDTIME'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'parentcategory', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_PARENTCATEGORY'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'category', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_CATEGORY'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'combocategories', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_COMBOCATEGORIES'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'tags', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_TAGS'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'linkedtags', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_LINKEDTAGS'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'keywords', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_KEYWORDS'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'keywordssearch', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_KEYWORDSSEARCH'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'keywordsfinder', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_KEYWORDSFINDER'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'linka', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_LINKA'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'linkb', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_LINKB'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'linkc', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_LINKC'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'links', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_LINKS'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'linksnl', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_LINKSNEWLINE'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'share', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_SHAREICONS'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'email', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_EMAIL'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'print', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_PRINT'), 'value', 'text', $disable = false);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'associations', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_ASSOCIATIONS'), 'value', 'text', $disable = false);

// 		if (File::exists(JPATH_ROOT . '/components/com_jcomments/jcomments.php')) {
// 			$groups[$group_name][] = HTMLHelper::_('select.option', 'jcommentscount', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_JCOMMENTSCOUNT'), 'value', 'text', $disable = false);
// 			$groups[$group_name][] = HTMLHelper::_('select.option', 'linkedjcommentscount', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_LINKEDJCOMMENTSCOUNT'), 'value', 'text', $disable = false);
// 		}

		// get Joomla! fields
		// test the fields folder first to avoid message warning that the component is missing
		if (is_dir(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_content')->get('custom_fields_enable', '1')) {

			$fields = self::getCoreFields();

			// supported field types
			$allowed_types = array('calendar', 'checkboxes', 'editor', 'email', 'integer', 'list', 'radio', 'sql', 'tel', 'text', 'textarea', 'url');

			// organize the fields according to their group

			$fieldsPerGroup = array(
				0 => array()
			);

			$groupTitles = array(
				0 => Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_NOGROUPFIELD')
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

				$group_name = Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_JOOMLAFIELDS');
				$groups[$group_name] = array();

				//$options[] = HTMLHelper::_('select.optgroup', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_JOOMLAFIELDS'));

				foreach ($fieldsPerGroup as $group_id => $groupFields) {

					if (!$groupFields) {
						continue;
					}

					foreach ($groupFields as $field) {
						$groups[$group_name][] = HTMLHelper::_('select.option', 'jfield:'.$field->type.':'.$field->id, $groupTitles[$group_id].': '.$field->title . ' (Pro)', 'value', 'text', $disable = true);
					}
				}

				//$options[] = HTMLHelper::_('select.optgroup', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_JOOMLAFIELDS'));
			}
		}

		$group_name = Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_PLUGINFIELDS');
		$groups[$group_name] = array();
		$groups[$group_name][] = HTMLHelper::_('select.option', 'pluginpro', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_ADDYOUROWN') . ' (Pro)', 'value', 'text', $disable = true);

		// Merge any additional options in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}
}
?>