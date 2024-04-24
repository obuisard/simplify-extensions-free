<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use SYW\Library\K2 as SYWK2;

class HeadselectField extends GroupedlistField
{
	public $type = 'Headselect';

	static $core_fields = null;
	static $k2_fields = null;

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

	static function getK2Fields($allowed_types = array())
	{
		if (!isset(self::$k2_fields)) {
			self::$k2_fields = SYWK2::getK2Fields($allowed_types);
		}

		return self::$k2_fields;
	}

	protected function getGroups()
	{
		$groups = array();

		$k2extrafields = array();
		$customfields = array();

		if (SYWK2::exists()) {
			// get K2 extra fields
			$k2extrafields = self::getK2Fields(array('date', 'image'));
		}

		if (Folder::exists(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_content')->get('custom_fields_enable', '1')) {

			$field_types = array('calendar', 'media');

			if (PluginHelper::isEnabled('fields', 'sywicon')) {
				$field_types[] = 'sywicon';
			}

			if (PluginHelper::isEnabled('fields', 'acfyoutube')) {
			    $field_types[] = 'acfyoutube';
			}

			// get the custom fields
			$customfields = self::getCoreFields($field_types);
		}

		// images

		$group_name = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_IMAGEGROUP');
		$groups[$group_name] = array();

// 		$options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_IMAGEGROUP'));

		$groups[$group_name][] = HTMLHelper::_('select.option', 'image', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_IMAGE'), 'value', 'text', false);
		if (SYWK2::exists()) {
			$groups[$group_name][] = HTMLHelper::_('select.option', 'imageintro', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_IMAGEINTRO_WITHK2'), 'value', 'text', false);
			$groups[$group_name][] = HTMLHelper::_('select.option', 'imagefull', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_IMAGEFULL_WITHK2'), 'value', 'text', false);
			$groups[$group_name][] = HTMLHelper::_('select.option', 'allimagesasc', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_ALLIMAGESASC_WITHK2'), 'value', 'text', false);
			$groups[$group_name][] = HTMLHelper::_('select.option', 'allimagesdesc', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_ALLIMAGESDESC_WITHK2'), 'value', 'text', false);
		} else {
			$groups[$group_name][] = HTMLHelper::_('select.option', 'imageintro', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_IMAGEINTRO'), 'value', 'text', false);
			$groups[$group_name][] = HTMLHelper::_('select.option', 'imagefull', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_IMAGEFULL'), 'value', 'text', false);
			$groups[$group_name][] = HTMLHelper::_('select.option', 'allimagesasc', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_ALLIMAGESASC'), 'value', 'text', false);
			$groups[$group_name][] = HTMLHelper::_('select.option', 'allimagesdesc', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_ALLIMAGESDESC'), 'value', 'text', false);
			$groups[$group_name][] = HTMLHelper::_('select.option', 'categoryimage', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_CATEGORYIMAGE'), 'value', 'text', false);
		}

		$groups[$group_name][] = HTMLHelper::_('select.option', 'author', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_AUTHORCONTACT') . ' (Pro)', 'value', 'text', true);
		if (SYWK2::exists()) {
			$groups[$group_name][] = HTMLHelper::_('select.option', 'authork2user', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_AUTHORK2USER') . ' (Pro)', 'value', 'text', true);
		}

		$group_options = self::getFieldGroup('com_content', $customfields, 'media');
		$groups[$group_name] = array_merge($groups[$group_name], $group_options);

		if (SYWK2::exists()) {
			$group_options = self::getFieldGroup('com_k2', $k2extrafields, 'image');
			$groups[$group_name] = array_merge($groups[$group_name], $group_options);
		}

// 		$options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_IMAGEGROUP'));

		// icons

		if (PluginHelper::isEnabled('fields', 'sywicon')) {
			$group_options = self::getFieldGroup('com_content', $customfields, 'sywicon');
			if (!empty($group_options)) {

				$group_name = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_ICONGROUP');
				$groups[$group_name] = $group_options;

// 				$options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_ICONGROUP'));
//				$groups[$group_name] = array_merge($groups[$group_name], $group_options);
// 				$options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_ICONGROUP'));
			}
		}

		// calendars

		$group_name = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_CALENDARGROUP');
		$groups[$group_name] = array();

// 		$options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_CALENDARGROUP'));

		$groups[$group_name][] = HTMLHelper::_('select.option', 'calendar', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_CALENDAR'), 'value', 'text', false);

		$group_options = self::getFieldGroup('com_content', $customfields, 'calendar');
		$groups[$group_name] = array_merge($groups[$group_name], $group_options);

		if (SYWK2::exists()) {
			$group_options = self::getFieldGroup('com_k2', $k2extrafields, 'date');
			$groups[$group_name] = array_merge($groups[$group_name], $group_options);
		}

// 		$options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_CALENDARGROUP'));

		// videos

		if (PluginHelper::isEnabled('fields', 'acfdailymotion')
		    || PluginHelper::isEnabled('fields', 'acffacebookvideo')
		    || PluginHelper::isEnabled('fields', 'acfhtml5video')
		    || PluginHelper::isEnabled('fields', 'acfvimeo')
		    || PluginHelper::isEnabled('fields', 'acfyoutube')) {

	    	$group_name = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_VIDEOGROUP');
	    	$groups[$group_name] = array();

// 		    $options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_VIDEOGROUP'));

    		if (PluginHelper::isEnabled('fields', 'acfdailymotion')) {
    		    $group_options = self::getFieldGroup('com_content', $customfields, 'acfdailymotion');
    		    if (!empty($group_options)) {
    		    	$groups[$group_name] = array_merge($groups[$group_name], $group_options);
    		    }
    		}

    		if (PluginHelper::isEnabled('fields', 'acffacebookvideo')) {
    		    $group_options = self::getFieldGroup('com_content', $customfields, 'acffacebookvideo');
    		    if (!empty($group_options)) {
    		    	$groups[$group_name] = array_merge($groups[$group_name], $group_options);
    		    }
    		}

    		if (PluginHelper::isEnabled('fields', 'acfhtml5video')) {
    		    $group_options = self::getFieldGroup('com_content', $customfields, 'acfhtml5video');
    		    if (!empty($group_options)) {
    		    	$groups[$group_name] = array_merge($groups[$group_name], $group_options);
    		    }
    		}

    		if (PluginHelper::isEnabled('fields', 'acfvimeo')) {
    		    $group_options = self::getFieldGroup('com_content', $customfields, 'acfvimeo');
    		    if (!empty($group_options)) {
    		    	$groups[$group_name] = array_merge($groups[$group_name], $group_options);
    		    }
    		}

    		if (PluginHelper::isEnabled('fields', 'acfyoutube')) {
    		    $group_options = self::getFieldGroup('com_content', $customfields, 'acfyoutube');
    		    if (!empty($group_options)) {
    		    	$groups[$group_name] = array_merge($groups[$group_name], $group_options);
    		    }
    		}

//     		$options[] = HTMLHelper::_('select.optgroup', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_VIDEOGROUP'));
		}

		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}

	protected function getFieldGroup($option, $fields, $type)
	{
		$groups = array();

		if (empty($fields)) {
			return $groups;
		}

		if ($option == 'com_k2') {

			foreach ($fields as $field) {

				if ($field->type != $type) {
					continue;
				}
				$groups[] = HTMLHelper::_('select.option', 'k2field:'.$field->type.':'.$field->id, 'K2: '.$field->group_name.': '.$field->name . ' (Pro)', 'value', 'text', true);
			}
		}

		if ($option == 'com_content') {

			// organize the fields according to their group

			$fieldsPerGroup = array(
				0 => array()
			);

			$groupTitles = array(
				0 => Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_NOGROUPFIELD')
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

				foreach ($fieldsPerGroup as $group_id => $groupFields) {

					if (!$groupFields) {
						continue;
					}

					foreach ($groupFields as $field) {
						$groups[] = HTMLHelper::_('select.option', 'jfield:'.$field->type.':'.$field->id, $groupTitles[$group_id].': '.$field->title . ' (Pro)', 'value', 'text', true);
					}
				}
			}
		}

		return $groups;
	}
}
?>