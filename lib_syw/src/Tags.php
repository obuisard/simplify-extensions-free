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
use Joomla\Database\Exception\ExecutionFailureException;

class Tags
{
	/*
	* Get all tag objects for a specific content type (optional)
	*
	* @return array of tag objects (false if error)
	*/
    static function getTags($content_type = '', $whole = false, $tag_ids = array(), $include = true, $order = 'lft', $order_dir = 'ASC')
	{
		$tags = array();

		$db = Factory::getDBO();
		$query = $db->getQuery(true);

		if ($whole) { // get the whole object
			$query->select('a.*');
		} else {
			$query->select('a.id, a.path, a.title, a.level');
		}
		$query->from('#__tags AS a');
		$query->join('LEFT', $db->quoteName('#__tags').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');

		// get tags for a specific content type
		if (!empty($content_type)) { // get only tags associated with the content type
			$query->join('INNER', $db->quoteName('#__contentitem_tag_map').' AS m ON m.tag_id = a.id AND m.type_alias ='.$db->quote($content_type));
		}

		$query->where('a.published = 1');
		$query->where($db->quoteName('a.alias').' <> '.$db->quote('root'));

		// get tags with specific ids
		if (is_array($tag_ids) && count($tag_ids) > 0) {
			ArrayHelper::toInteger($tag_ids);
			$tag_ids = implode(',', $tag_ids);

			$test_type = $include ? 'IN' : 'NOT IN';
			$query->where($db->quoteName('a.id').' '.$test_type.' ('.$tag_ids.')');
		}

		// access groups
		$user = Factory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')');

		// language
		if (Multilanguage::isEnabled()) {
		    $language = ContentHelper::getCurrentLanguage();
			$query->where($db->quoteName('a.language').' IN ('.$db->quote($language).', '.$db->quote('*').')');
		}

		$query->group('a.id, a.title, a.level, a.lft, a.rgt, a.parent_id, a.path');
		$query->order('a.'.$order.' '.$order_dir);

		$db->setQuery($query);

		try {
			$tags = $db->loadObjectList();
		} catch (ExecutionFailureException $e) {
			return false;
		}

		return $tags;
	}

}