<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Utilities\ArrayHelper;

class K2 
{
	static $k2_exists = NULL;
	
	static function exists()
	{	
		if (isset(self::$k2_exists)) {
			return self::$k2_exists;
		}
		
		self::$k2_exists = true;
		
		$db = Factory::getDbo();
		
		$query = $db->getQuery(true);
		
		$query->select('extension_id AS id, element AS "option", params, enabled');
		$query->from('#__extensions');
		$query->where($query->qn('type') . ' = ' . $db->quote('component'));
		$query->where($query->qn('element') . ' = ' . $db->quote('com_k2'));
		
		$db->setQuery($query);
		
		try {
			$cache = Factory::getCache('_system', 'callback');			
			$k2_component = $cache->get(array($db, 'loadObject'), null, 'com_k2', false);
		} catch (\RuntimeException $e) {
			self::$k2_exists = false;
		}
		
		if (empty($k2_component)) {
			self::$k2_exists = false;
		}
		
		return self::$k2_exists;
	}	
	
	/*
	 * Get all tag objects for k2
	 *
	 * @return array of tag objects (false if error)
	 */
	static function getTags($whole = false, $tag_ids = array(), $include = true, $order = 'name', $order_dir = 'ASC')
	{
		$tags = array();
		
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		
		if ($whole) { // get the whole object
			$query->select('tag.id, tag.name AS title, tag.published');
		} else {
			$query->select('tag.id, tag.name AS title');
		}
		$query->from('#__k2_tags AS tag');
		
		$query->join('LEFT', $db->quoteName('#__k2_tags_xref').' AS xref ON tag.id = xref.tagID');
		$query->join('LEFT', $db->quoteName('#__k2_items').' AS items ON xref.itemID= items.id');
				
		// access groups
		$user = Factory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('items.access IN (' . $groups . ')');
		
		// language
		if (Multilanguage::isEnabled()) {
		    $language = ContentHelper::getCurrentLanguage();
			$query->where($db->quoteName('items.language').' IN ('.$db->quote($language).', '.$db->quote('*').')');
		}		
		
		$query->where('tag.published = 1');
				
		// get tags with specific ids
		if (is_array($tag_ids) && count($tag_ids) > 0) {
			ArrayHelper::toInteger($tag_ids);
			$tag_ids = implode(',', $tag_ids);
			
			$test_type = $include ? 'IN' : 'NOT IN';
			$query->where($db->quoteName('tag.id').' '.$test_type.' ('.$tag_ids.')');
		}
		
		//$query->order('xref.id ASC');
		$query->order('tag.'.$order.' '.$order_dir);
		
		$db->setQuery($query);
		
		try {
			$tags = $db->loadObjectList();
		} catch (\RuntimeException $e) {
			return false;
		}
		
		return $tags;
	}
	
}
