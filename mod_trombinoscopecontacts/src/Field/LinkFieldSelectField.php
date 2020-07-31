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

class LinkFieldSelectField extends ListField
{
	public $type = 'LinkFieldSelect';

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

		$options[] = HTMLHelper::_('select.option', 'mail', Text::_('MOD_TROMBINOSCOPE_VALUE_EMAIL'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'web', Text::_('MOD_TROMBINOSCOPE_VALUE_WEBPAGE'), 'value', 'text', $disable=false);

		// map
		$options[] = HTMLHelper::_('select.option', 'map', Text::_('MOD_TROMBINOSCOPE_VALUE_MAP'), 'value', 'text', $disable=true);

		// Facebook
		$options[] = HTMLHelper::_('select.option', 'facebook', 'Facebook', 'value', 'text', $disable=true);

		// Twitter
		$options[] = HTMLHelper::_('select.option', 'twitter', 'Twitter', 'value', 'text', $disable=true);

		// LinkedIn
		$options[] = HTMLHelper::_('select.option', 'linkedin', 'LinkedIn', 'value', 'text', $disable=true);

		// Google+
		$options[] = HTMLHelper::_('select.option', 'googleplus', 'Google+', 'value', 'text', $disable=true);

		// YouTube
		$options[] = HTMLHelper::_('select.option', 'youtube', 'YouTube', 'value', 'text', $disable=true);

		// Instagram
		$options[] = HTMLHelper::_('select.option', 'instagram', 'Instagram', 'value', 'text', $disable=true);

		// Pinterest
		$options[] = HTMLHelper::_('select.option', 'pinterest', 'Pinterest', 'value', 'text', $disable=true);

		// Skype
		$options[] = HTMLHelper::_('select.option', 'skype', 'Skype', 'value', 'text', $disable=true);

		$options[] = HTMLHelper::_('select.option', 'a', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKA'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'a_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKA_SAMEWINDOW'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'b', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKB'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'b_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKB_SAMEWINDOW'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'c', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKC'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'c_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKC_SAMEWINDOW'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'd', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKD'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'd_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKD_SAMEWINDOW'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'e', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKE'), 'value', 'text', $disable=false);
		$options[] = HTMLHelper::_('select.option', 'e_sw', Text::_('MOD_TROMBINOSCOPE_VALUE_LINKE_SAMEWINDOW'), 'value', 'text', $disable=false);

		// get Joomla! fields
		// test the fields folder first to avoid message warning that the component is missing
		if (Folder::exists(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_contact')->get('custom_fields_enable', '1')) {

			$fields = self::getCoreFields();

			// supported field types
			$allowed_types = array('email', 'url');

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
					$options[] = HTMLHelper::_('select.option', 'jfield:'.$field->type.':'.$field->id, $field->title, 'value', 'text', $disable=true);
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