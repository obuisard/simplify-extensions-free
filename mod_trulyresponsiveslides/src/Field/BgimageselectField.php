<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\TrulyResponsiveSlides\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

class BgimageselectField extends GroupedlistField
{
	public $type = 'Bgimageselect';

	static $core_fields = null;

	static function getCoreFields($allowed_types = array())
	{
		if (!isset(self::$core_fields)) {
			$fields = FieldsHelper::getFields('com_content.article');

			self::$core_fields = array();

			if (!empty($fields)) {
				foreach ($fields as $field) {
					if (!empty($allowed_types) && !in_array($field->type, $allowed_types)) {
						continue;
					}
					self::$core_fields[] = $field;
				}
			}
		}

		return self::$core_fields;
	}

	protected function getGroups()
	{
		$groups = array();

		$customfields = array();

		if (is_dir(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_content')->get('custom_fields_enable', '1')) {
			// get the custom fields
			$customfields = self::getCoreFields(array('media'));
		}

		//$options[] = HTMLHelper::_('select.option', 'default', Text::_('JDEFAULT'), 'value', 'text', $disable = false);

		$group_options = self::getFieldGroup('com_content', $customfields, 'media');
		$groups = array_merge($groups, $group_options);

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

		if ($option == 'com_content') {

			// organize the fields according to their group

			$fieldsPerGroup = array(
				0 => array()
			);

			$groupTitles = array(
				0 => Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_NOGROUPFIELD')
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

			if ($fields_exist) {

				$group_name = Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_JOOMLAFIELDS');
				$groups[$group_name] = array();

				//$options[] = JHtml::_('select.optgroup', Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_JOOMLAFIELDS'));

				foreach ($fieldsPerGroup as $group_id => $groupFields) {

					if (!$groupFields) {
						continue;
					}

					foreach ($groupFields as $field) {
						$groups[$group_name][] = HTMLHelper::_('select.option', 'jfield:'.$field->type.':'.$field->id, $groupTitles[$group_id].': '.$field->title . ' (Pro)', 'value', 'text', $disable = true);
					}
				}

				//$options[] = JHtml::_('select.optgroup', Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_JOOMLAFIELDS'));
			}
		}

		return $groups;
	}
}
?>