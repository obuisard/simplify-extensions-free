<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\Exception\ExecutionFailureException;

class Fields
{
	/**
	 * Cache for field parameters
	 * @var array
	 */
	protected static $fields = array();
	
	/**
	 * Cache for field categories
	 * @var array
	 */
	protected static $fields_categories = array();
	
	/**
     * Get the values of a custom field for a specific item
	 *
	 * @param string $item_id
	 * @param string $field_id
	 * @param boolean $include_params - deprecated
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
     * Get the parameters of a custom field
     *
     * @param integer $field_id
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
		$query->where($db->quoteName('f.id').' = :fieldId');
		$query->bind(':fieldId', $field_id, ParameterType::INTEGER);
		
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

    /**
     * Check if a custom field is restricted to one or several categories
     * 
     * @param int $field_id
     * @return array categories the field is restricted to - an empty array means the field is available in ALL categories
     */
    public static function getAssignedCategories($field_id)
    {
    	if (isset(static::$fields_categories[$field_id])) {
    		return static::$fields_categories[$field_id];
    	}
    	
    	$db = Factory::getDbo();
    	$query = $db->getQuery(true);
    	
    	$query->select($db->quoteName('category_id'));
    	$query->from($db->quoteName('#__fields_categories'));
    	$query->where('field_id = ' . $field_id);
    	
    	$db->setQuery($query);
    	
    	$results = array();
    	
    	try {
    		$results = $db->loadColumn();
    		static::$fields_categories[$field_id] = $results;
    	} catch (ExecutionFailureException $e) {
    		//
    	}
    
    	return $results;
    }
    
    /**
     * For a custom field that has a list of items, translates the options and returns the coma separated list of values ready for display
     *
     * @param object $field
     */
    public static function prepareCustomFieldValueFromOptions(&$field)
    {
        $field_params = json_decode($field->fieldparams);
        
        if (isset($field_params->options) && is_object($field_params->options)) {
            
            $options = array();
            
            foreach ($field_params->options as $key => $value) {
                $options[$value->value] = $value->name;
            }
            
            $cfield_values = array();
            
            if (!is_array($field->value)) {
                $field->value = array($field->value);
            }
            
            foreach ($field->value as $result) {
                if (!empty($options)) {
                    if (isset($options[$result]) && trim($options[$result]) !== '') {
                        if (Factory::getLanguage()->hasKey($options[$result])) {
                            $cfield_values[] = Text::_($options[$result]);
                        } else {
                            $cfield_values[] = trim($options[$result]);
                        }
                    } else {
                        //$cfield_val[] = ''; // could happen, for instance 3 values then get down to 2
                    }
                } else {
                    if (trim($result) !== '') {
                        $cfield_values[] = trim($result);
                    }
                }
            }
            
            $field->value = implode(', ', $cfield_values);
        }
    }
    
    /**
     * Prepares the value of a custom field for display
     *
     * @param string $context
     * @param object $item
     * @param object $field
     */
    public static function prepareCustomFieldValue($context, $item, &$field)
    {
        PluginHelper::importPlugin('fields');
        
        // Event allow plugins to modify the output of the field before it is prepared
        Factory::getApplication()->triggerEvent('onCustomFieldsBeforePrepareField', array($context, $item, &$field));
        
        // Gathering the value for the field
        $prepared_value = Factory::getApplication()->triggerEvent('onCustomFieldsPrepareField', array($context, $item, &$field));
        
        if (is_array($prepared_value)) {
            $prepared_value = implode(' ', $prepared_value);
        }
        
        // Event allow plugins to modify the output of the prepared field
        Factory::getApplication()->triggerEvent('onCustomFieldsAfterPrepareField', array($context, $item, $field, &$prepared_value));
        
        $field->value = $prepared_value;
    }
    
}
?>
