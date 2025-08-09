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
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Utilities\ArrayHelper;

class K2
{
	static function exists()
	{
// 		if (is_dir(JPATH_ADMINISTRATOR . '/components/com_k2') && ComponentHelper::isEnabled('com_k2')) {
// 			return true;
// 		}

	    // K2 is disabled until a version for Joomla 4 exists and is tested with my own extensions

		return false;
	}

	/**
	 * Get all K2 extra fields
	 *
	 * @param an optional array of types needed
	 * @return array
	 */
	static function getK2Fields($types = array())
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true);

		$query->select('f.id');
		$query->select('f.type');
		$query->select('f.name');
		$query->select('g.name AS group_name');
		$query->from('#__k2_extra_fields AS f');
		$query->where($db->quoteName('f.published').' = 1');
		$query->order($db->quoteName('f.ordering'));

		if (!empty($types)) {
			$query->where($db->quoteName('f.type') . ' IN ("' . implode('","', $types) . '")');
		}

		$query->innerJoin('#__k2_extra_fields_groups AS g ON g.id = f.group');

		$db->setQuery($query);

		$fields = array();
		try {
			$fields = $db->loadObjectList();
		} catch (ExecutionFailureException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $fields;
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
		} catch (ExecutionFailureException $e) {
			return false;
		}

		return $tags;
	}

}
