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
	 * Cache for field parameters
	 * @var array
	 */
	protected static $fields = array();
	
	/**
	 *
	 * @param string $item_id
	 * @param string $field_id
	 * @param boolean $include_params
	 * @return string, array or array of value arrays
	 */
	public static function getCustomFieldValues($field_id, $item_id, $include_params = false, $force_multiple_array = false)
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
		
		$results = array();

		try {
			$results = $db->loadAssocList();
		} catch (ExecutionFailureException $e) {
			return null;
		}
		
		if (!$force_multiple_array && count($results) == 1) {
		    if ($include_params) {
		        return $results[0]; // return array ('value', 'title', 'alias', 'fieldoptions', 'fieldparams')
		    } else {
		        return $results[0]['value']; // return value string
		    }
		}
		
		return $results; // return multi-dimensional array
	}

	/**
	 * 
	 * @param unknown $field_id
	 * @return array of parameters
	 */
	public static function getCustomFieldParams($field_id)
	{
		if (isset(static::$fields[$field_id])) {
			return static::$fields[$field_id];
		}
		
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select($db->quoteName(array('f.label', 'f.name', 'f.params', 'f.fieldparams', 'f.context', 'f.type', 'f.default_value'), array('title', 'alias', 'fieldoptions', 'fieldparams', 'context', 'type', 'default_value')));
		
		$query->from($db->quoteName('#__fields', 'f'));
		$query->where($db->quoteName('f.id').' = ' . $field_id);
		
		$db->setQuery($query);
		
		$results = array();
		
		try {
			$results = $db->loadAssoc();			
			static::$fields[$field_id] = $results;
		} catch (ExecutionFailureException $e) {
			return null;
		}
		
		return $results;
	}

}
?>
