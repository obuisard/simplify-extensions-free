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


class BGImageSelectField extends ListField
{
	public $type = 'BGImageSelect';

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

		// get Joomla! fields
		// test the fields folder first to avoid message warning that the component is missing
		if (Folder::exists(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_contact')->get('custom_fields_enable', '1')) {

			$fields = self::getCoreFields();

			// supported field types
			$allowed_types = array('media');

			foreach ($fields as $field) {
				if (in_array($field->type, $allowed_types)) {

					$field_category_title = $field->group_title; // group may be missing
					if (empty($field_category_title)) {
						$field_category_title = Text::_('MOD_TROMBINOSCOPE_VALUE_NOGROUPFIELD');
					}

					$options[] = HTMLHelper::_('select.option', $field->id, $field_category_title.': '.$field->title, 'value', 'text', $disable = true);
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
?>