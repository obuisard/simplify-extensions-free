<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\Exception\ExecutionFailureException;

FormHelper::loadFieldClass('list');

class CustomfieldgroupslistField extends ListField
{
	public $type = 'Customfieldgroupslist';

	protected $context;

	static $core_fieldgroups = array();

	static function getCoreFieldGroups($context)
	{
		$usable_context = str_replace('.', '_', $context);

		if (!isset(self::$core_fieldgroups[$usable_context])) {

			$db = Factory::getDbo();

			$query = $db->getQuery(true);

			$query->select('id, title');
			$query->from('#__fields_groups');
			$query->where('state = 1');
			$query->where('context = ' . $db->quote($context));

			$db->setQuery($query);

			$results = array();
			try {
				$results = $db->loadObjectList();
			} catch (ExecutionFailureException $e) {
				Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			}

			self::$core_fieldgroups[$usable_context] = $results;
		}

		return self::$core_fieldgroups[$usable_context];
	}

	protected function getOptions()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$options = array();

		// get Joomla! field groups
		// test the fields folder first to avoid message warning that the component is missing
		if (is_dir(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams(explode('.', $this->context)[0])->get('custom_fields_enable', '1')) {

			$groups = self::getCoreFieldGroups($this->context);

			foreach ($groups as $group) {
				$options[] = HTMLHelper::_('select.option', $group->id, $group->title);
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
		}

		return $return;
	}
}
?>