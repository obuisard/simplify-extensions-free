<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\Exception\ExecutionFailureException;

class Fields
{
	/**
	 *
	 * @param string $item_id
	 * @param string $field_id
	 * @param boolean $include_params
	 * @return array or array of value arrays
	 */
	public static function getCustomFieldValues($field_id, $item_id, $include_params = false)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($include_params) {
			$query->select($db->quoteName(array('fv.value', 'f.label', 'f.name', 'f.params', 'f.fieldparams'), array('value', 'title', 'alias', 'fieldoptions', 'fieldparams')));
		} else {
			$query->select($db->quoteName('fv.value', 'value'));
		}

		$query->from($db->quoteName('#__fields_values', 'fv'));
		$query->where($db->quoteName('fv.field_id').' = ' . $field_id);
		$query->where($db->quoteName('fv.item_id').' = ' . $item_id);

		if ($include_params) {
			$query->join('LEFT', $db->quoteName('#__fields', 'f').' ON '.$db->quoteName('f.id').' = '.$db->quoteName('fv.field_id'));
		}

		$db->setQuery($query);

		try {
			$results = $db->loadAssocList();
			if (count($results) == 1) {
				return $results[0];
			}
		} catch (ExecutionFailureException $e) {
			//Factory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			return null;
		}
	}

}
?>
