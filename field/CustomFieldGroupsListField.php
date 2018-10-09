<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined( '_JEXEC' ) or die;

//use Joomla\Filesystem\Folder;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Component\ComponentHelper;

FormHelper::loadFieldClass('list');

class CustomFieldGroupsListField extends \JFormFieldList
{
	public $type = 'CustomFieldGroupsList';

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
			} catch (\RuntimeException $e) {
				Factory::getApplication()->enqueueMessage(\JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
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
		if (\JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_fields') && ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams(explode('.', $this->context)[0])->get('custom_fields_enable', '1')) {

			$groups = self::getCoreFieldGroups($this->context);

			foreach ($groups as $group) {
				$options[] = \JHTML::_('select.option', $group->id, $group->title);
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
			$this->context = isset($this->element['context']) ? $this->element['context'] : 'com_contact.contact';
		}

		return $return;
	}
}
?>