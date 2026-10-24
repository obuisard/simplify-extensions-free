<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Plugin\Content\ArticleDetails\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

class HeadselectField extends GroupedlistField
{
	public $type = 'Headselect';

	protected $location;

	static $core_fields = array();

	static function getCoreFields($allowed_types = array(), $origin = 'com_content.article')
	{
		if (!isset(self::$core_fields[$origin])) {
			$fields = FieldsHelper::getFields($origin);

			self::$core_fields[$origin] = array();

			if (!empty($fields)) {
				foreach ($fields as $field) {
					if (!empty($allowed_types) && !in_array($field->type, $allowed_types)) {
						continue;
					}
					self::$core_fields[$origin][] = $field;
				}
			}
		}

		return self::$core_fields[$origin];
	}

	protected function getGroups()
	{
		$lang = Factory::getLanguage();
		$lang->load('plg_content_articledetails');

		$customfields = array();

		if (is_dir(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields')) {
			// get the custom fields
			if (ComponentHelper::getParams('com_content')->get('custom_fields_enable', '1')) {
				$customfields['com_content'] = self::getCoreFields(array('media', 'calendar', 'sywicon'));
			}
			if (ComponentHelper::getParams('com_users')->get('custom_fields_enable', '1')) {
				$customfields['com_users'] = self::getCoreFields(array('media'), 'com_users.user');
			}
		}

		$groups = array();

		// images

		$group_name = Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_IMAGEGROUP');
		$groups[$group_name] = array();

		//$options[] = HTMLHelper::_('select.optgroup', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_IMAGEGROUP'));

		$groups[$group_name][] = HTMLHelper::_('select.option', 'contact', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_LINKEDCONTACT') . ' (Pro)', 'value', 'text', $disable = true);
		$groups[$group_name][] = HTMLHelper::_('select.option', 'gravatar', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_GRAVATAR') . ' (Pro)', 'value', 'text', $disable = true);

		if (isset($customfields['com_users'])) {
			$group_options = self::getFieldGroup('com_users', $customfields['com_users'], 'media');
			$groups[$group_name] = array_merge($groups[$group_name], $group_options);
		}

		if (isset($customfields['com_content'])) {
			$group_options = self::getFieldGroup('com_content', $customfields['com_content'], 'media');
			$groups[$group_name] = array_merge($groups[$group_name], $group_options);
		}

		//$options[] = HTMLHelper::_('select.optgroup', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_IMAGEGROUP'));

		// icons

		if ($this->location == 'header') {
			if (isset($customfields['com_content']) && PluginHelper::isEnabled('fields', 'sywicon')) {
				$group_options = self::getFieldGroup('com_content', $customfields['com_content'], 'sywicon');
				if (!empty($group_options)) {

					$group_name = Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_ICONGROUP');
					$groups[$group_name] = $group_options;

					//$options[] = HTMLHelper::_('select.optgroup', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_ICONGROUP'));
					//$groups[$group_name] = array_merge($groups[$group_name], $group_options);
					//$options[] = HTMLHelper::_('select.optgroup', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_ICONGROUP'));
				}
			}
		}

		// calendars

		if ($this->location == 'header') {

			$group_name = Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_CALENDARGROUP');
			$groups[$group_name] = array();

			//$options[] = HTMLHelper::_('select.optgroup', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_CALENDARGROUP'));

			$groups[$group_name][] = HTMLHelper::_('select.option', 'calendar', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_CALENDAR'), 'value', 'text', $disable = false);

			if (isset($customfields['com_content'])) {
				$group_options = self::getFieldGroup('com_content', $customfields['com_content'], 'calendar');
				$groups[$group_name] = array_merge($groups[$group_name], $group_options);
			}

			//$options[] = HTMLHelper::_('select.optgroup', Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_CALENDARGROUP'));
		}

		// merge any additional options in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}

	protected function getFieldGroup($option, $fields, $type)
	{
		$groups = array();

		if (empty($fields)) {
			return $groups;
		}

		// organize the fields according to their group

		$fieldsPerGroup = array(
			0 => array()
		);

		$groupTitles = array(
			0 => Text::_('PLG_CONTENT_ARTICLEDETAILS_VALUE_NOGROUPFIELD')
		);

		$fields_exist = false;
		foreach ($fields as $field) {

			if ($field->type != $type) {
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

		$prefix = 'jfield';
		if ($option != 'com_content') {
			$prefix .= str_replace('com_', '' , $option);
		}

		if ($fields_exist) {

			foreach ($fieldsPerGroup as $group_id => $groupFields) {

				if (!$groupFields) {
					continue;
				}

				foreach ($groupFields as $field) {
					$groups[] = HTMLHelper::_('select.option', $prefix.':'.$field->type.':'.$field->id, $groupTitles[$group_id].': '.$field->title . ' (Pro)', 'value', 'text', $disable = true);
				}
			}
		}

		return $groups;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->location = isset($this->element['location']) ? $this->element['location'] : 'header';
		}

		return $return;
	}

}
?>