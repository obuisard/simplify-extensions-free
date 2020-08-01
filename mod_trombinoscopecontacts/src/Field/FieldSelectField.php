<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\TrombinoscopeContacts\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

class FieldSelectField extends ListField
{
	public $type = 'FieldSelect';

	static $core_fields = null;

	static function getCoreFields()
	{
		if (!isset(self::$core_fields)) {
			self::$core_fields = FieldsHelper::getFields('com_contact.contact');
		}

		return self::$core_fields;
	}

	protected function getOptions()
	{
		$options = array();

		$options[] = HTMLHelper::_('select.option', 'empty', Text::_('MOD_TROMBINOSCOPE_VALUE_EMPTY'), 'value', 'text', $disable = false);

		$options[] = HTMLHelper::_('select.option', 'c_p', Text::_('MOD_TROMBINOSCOPE_VALUE_POSITION'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'tel', Text::_('MOD_TROMBINOSCOPE_VALUE_TELEPHONE'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'mob', Text::_('MOD_TROMBINOSCOPE_VALUE_MOBILE'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'fax', Text::_('MOD_TROMBINOSCOPE_VALUE_FAX'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'mail', Text::_('MOD_TROMBINOSCOPE_VALUE_EMAIL'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'web', Text::_('MOD_TROMBINOSCOPE_VALUE_WEBPAGE'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'add', Text::_('MOD_TROMBINOSCOPE_VALUE_ADDRESS'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'sub', Text::_('MOD_TROMBINOSCOPE_VALUE_SUBURB'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'st', Text::_('MOD_TROMBINOSCOPE_VALUE_STATE'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'p_c', Text::_('MOD_TROMBINOSCOPE_VALUE_POSTCODE'), 'value', 'text', $disable = false);
		//$options[] = HTMLHelper::_('select.option', 'f_a', Text::_('MOD_TROMBINOSCOPE_VALUE_FORMATTEDADDRESS'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'f_f_a', Text::_('MOD_TROMBINOSCOPE_VALUE_FULLYFORMATTEDADDRESS'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'cou', Text::_('MOD_TROMBINOSCOPE_VALUE_COUNTRY'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'misc', Text::_('MOD_TROMBINOSCOPE_VALUE_MISC'), 'value', 'text', $disable = false);

		// gender
		$options[] = HTMLHelper::_('select.option', 'gen', Text::_('MOD_TROMBINOSCOPE_VALUE_GENDER') . ' (Pro)', 'value', 'text', $disable = true);

		// birthdate
		$options[] = HTMLHelper::_('select.option', 'dob', Text::_('MOD_TROMBINOSCOPE_VALUE_BIRTHDATE') . ' (Pro)', 'value', 'text', $disable = true);
		$options[] = HTMLHelper::_('select.option', 'age', Text::_('MOD_TROMBINOSCOPE_VALUE_AGE') . ' (Pro)', 'value', 'text', $disable = true);

		// company
		$options[] = HTMLHelper::_('select.option', 'com', Text::_('MOD_TROMBINOSCOPE_VALUE_COMPANY') . ' (Pro)', 'value', 'text', $disable = true);

		// department
		$options[] = HTMLHelper::_('select.option', 'dep', Text::_('MOD_TROMBINOSCOPE_VALUE_DEPARTMENT') . ' (Pro)', 'value', 'text', $disable = true);

		// map
		$options[] = HTMLHelper::_('select.option', 'map', Text::_('MOD_TROMBINOSCOPE_VALUE_MAP') . ' (Pro)', 'value', 'text', $disable = true);

		// summary
		$options[] = HTMLHelper::_('select.option', 'sum', Text::_('MOD_TROMBINOSCOPE_VALUE_SUMMARY') . ' (Pro)', 'value', 'text', $disable = true);

		// Facebook
		$options[] = HTMLHelper::_('select.option', 'facebook', 'Facebook' . ' (Pro)', 'value', 'text', $disable = true);

		// Twitter
		$options[] = HTMLHelper::_('select.option', 'twitter', 'Twitter' . ' (Pro)', 'value', 'text', $disable = true);

		// LinkedIn
		$options[] = HTMLHelper::_('select.option', 'linkedin', 'LinkedIn' . ' (Pro)', 'value', 'text', $disable = true);

		// Google+
		$options[] = HTMLHelper::_('select.option', 'googleplus', 'Google+' . ' (Pro)', 'value', 'text', $disable = true);

		// YouTube
		$options[] = HTMLHelper::_('select.option', 'youtube', 'YouTube' . ' (Pro)', 'value', 'text', $disable = true);

		// Instagram
		$options[] = HTMLHelper::_('select.option', 'instagram', 'Instagram' . ' (Pro)', 'value', 'text', $disable = true);

		// Pinterest
		$options[] = HTMLHelper::_('select.option', 'pinterest', 'Pinterest' . ' (Pro)', 'value', 'text', $disable = true);

		// Skype
		$options[] = HTMLHelper::_('select.option', 'skype', 'Skype' . ' (Pro)', 'value', 'text', $disable = true);

		$options[] = HTMLHelper::_('select.option', 'a', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKA'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'a_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKA_SAMEWINDOW'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'b', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKB'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'b_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKB_SAMEWINDOW'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'c', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKC'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'c_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKC_SAMEWINDOW'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'd', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKD'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'd_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKD_SAMEWINDOW'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'e', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKE'), 'value', 'text', $disable = false);
		$options[] = HTMLHelper::_('select.option', 'e_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKE_SAMEWINDOW'), 'value', 'text', $disable = false);

		// get Joomla! fields
		// test the fields folder first to avoid message warning that the component is missing
		if (Folder::exists(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_contact')->get('custom_fields_enable', '1')) {

			$fields = self::getCoreFields();

			// supported field types
			$allowed_types = array('calendar', 'checkboxes', 'email', 'integer', 'list', 'radio', 'tel', 'text', 'textarea', 'url');

			// organize the fields according to their group

			$fieldsPerGroup = array(
				0 => array()
			);

			$groupTitles = array(
				0 => Text::_('MOD_TROMBINOSCOPE_VALUE_NOGROUPFIELD')
			);

			foreach ($fields as $field) {

				if (!in_array($field->type, $allowed_types)) {
					continue;
				}

				if (!array_key_exists($field->group_id, $fieldsPerGroup)) {
					$fieldsPerGroup[$field->group_id] = array();
					$groupTitles[$field->group_id] = $field->group_title;
				}

				$fieldsPerGroup[$field->group_id][] = $field;
			}

			// loop trough the groups

			foreach ($fieldsPerGroup as $group_id => $groupFields) {

				if (!$groupFields) {
					continue;
				}

				//$options[] = HTMLHelper::_('select.optgroup', $groupTitles[$group_id]);

				foreach ($groupFields as $field) {
					$options[] = HTMLHelper::_('select.option', 'jfield:'.$field->type.':'.$field->id, $field->title . ' (Pro)', 'value', 'text', $disable = true);
				}

				//$options[] = HTMLHelper::_('select.optgroup', $groupTitles[$group_id]);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
?>
