<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

class CustomfieldslistField extends ListField
{
	public $type = 'Customfieldslist';

	protected $context;
	protected $allowed_types;
	protected $show_group;
	protected $show_on_client;

	static $core_fields = array();

	static function getCoreFields($context)
	{
		$usable_context = str_replace('.', '_', $context);

		//\JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

		if (!isset(self::$core_fields[$usable_context])) {
			self::$core_fields[$usable_context] = FieldsHelper::getFields($context);
		}

		return self::$core_fields[$usable_context];
	}

	protected function getOptions()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$options = array();

		// get Joomla! fields
		// test the fields folder first to avoid message warning that the component is missing
		if (is_dir(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams(explode('.', $this->context)[0])->get('custom_fields_enable', '1')) {

			$fields = self::getCoreFields($this->context);

			// organize the fields according to their group

			$fieldsPerGroup = array(
				0 => array()
			);

			$groupTitles = array(
				0 => Text::_('LIB_SYW_VALUE_NOGROUPFIELD')
			);

			foreach ($fields as $field) {

				if ($this->allowed_types != null && !in_array($field->type, $this->allowed_types)) {
					continue;
				}

				if ($this->show_on_client != null && $this->show_on_client == 'site' && $field->params->get('show_on') == 2) {
					continue;
				}

				if ($this->show_on_client != null && $this->show_on_client == 'administrator' && $field->params->get('show_on') == 1) {
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

				//$options[] = JHtml::_('select.optgroup', $groupTitles[$group_id]);

				foreach ($groupFields as $field) {
					if ($this->show_group) {
						$options[] = HTMLHelper::_('select.option', $field->id, $groupTitles[$group_id].': '.$field->title);
					} else {
						$options[] = HTMLHelper::_('select.option', $field->id, $field->title);
					}
				}

				//$options[] = JHtml::_('select.optgroup', $groupTitles[$group_id]);
			}
		}

		// merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->context = isset($this->element['context']) ? (string)$this->element['context'] : 'com_contact.contact';
			$this->allowed_types = isset($this->element['allowed_types']) ? explode(",", (string)$this->element['allowed_types']) : null;
			$this->show_group = isset($this->element['show_group']) ? filter_var($this->element['show_group'], FILTER_VALIDATE_BOOLEAN) : true;
			$this->show_on_client = isset($this->element['show_on_client']) ? (string)$this->element['show_on_client'] : null;
		}

		return $return;
	}
}
?>