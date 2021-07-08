<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Registry\Registry;
use SYW\Library\Tags as SYWTags;

class ArticlesHelper
{
	static function getItems($params, $module)
	{
		$db = Factory::getDbo();
		$app = Factory::getApplication();

		$query = $db->getQuery(true);

		// START OF DATABASE QUERY

		$subquery1 = ' CASE WHEN ';
		$subquery1 .= $query->charLength('a.alias');
		$subquery1 .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$subquery1 .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$subquery1 .= ' ELSE ';
		$subquery1 .= $a_id.' END AS slug';

		$subquery2 = ' CASE WHEN ';
		$subquery2 .= $query->charLength('c.alias');
		$subquery2 .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$subquery2 .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$subquery2 .= ' ELSE ';
		$subquery2 .= $c_id.' END AS cat_slug';

		$query->select('DISTINCT a.id, a.title, a.alias, a.introtext, a.fulltext, '.
				'a.checked_out, a.checked_out_time, '.
				'a.catid, a.created, a.created_by, a.created_by_alias, '.
				// Use created if modified is 0
				'CASE WHEN a.modified IS NULL THEN a.created ELSE a.modified END as modified, '.
				'a.modified_by, '.
				// Use created if publish_up is 0
				'CASE WHEN a.publish_up IS NULL THEN a.created ELSE a.publish_up END as publish_up, '.
				'a.publish_down, a.images, a.urls, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, a.hits, a.featured, a.language');

		$query->select($subquery1);
		$query->select($subquery2);

		$query->from('#__content AS a');

		// join over the categories

		$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// access filter

		$user = Factory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));

		$query->where('a.access IN ('.$groups.')');
		$query->where('c.access IN ('.$groups.')');

		// publishing

		//$nullDate = $db->quote($db->getNullDate());
		$nowDate = $db->quote(Factory::getDate()->toSql());

		$query->where($db->quoteName('a.state') . ' = 1');
		$query->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= '.$nowDate.')');
		$query->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= '.$nowDate.')');

		$query->where($db->quoteName('c.published') . ' = 1'); // does not check for category published state in parent categories up the tree

		// category filter

		$categories_array = $params->get('cat_id', array());

		$array_of_category_values = array_count_values($categories_array);
		if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
			// keep categories = ''
		} else {
			// sub-category inclusion
			$get_sub_categories = $params->get('includesubcategories', 'all');
			if ($get_sub_categories != 'no') {
				$categories_object = Categories::getInstance('Content'); // if category unpublished, unset
				foreach ($categories_array as $category) {
					$category_object = $categories_object->get($category);
					if (isset($category_object) && $category_object->hasChildren()) {
						if ($get_sub_categories == 'all') {
							$sub_categories_array = $category_object->getChildren(true); // true is for recursive
						} else {
							$sub_categories_array = $category_object->getChildren();
						}
						foreach ($sub_categories_array as $subcategory_object) {
							$categories_array[] = $subcategory_object->id;
						}
					}
				}
				$categories_array = array_unique($categories_array);
			}
		}

		if (!empty($categories_array)) {
			$categories = implode(',', $categories_array);
			if (!empty($categories)) {
				$query->where('a.catid IN ('.$categories.')');
			}
		}

		// tags filter

		$tags = $params->get('tags', array());

		if (!empty($tags)) {

			// if all selected, get all available tags
			$array_of_tag_values = array_count_values($tags);
			if (isset($array_of_tag_values['all']) && $array_of_tag_values['all'] > 0) { // 'all' was selected
				$tags = array();
				$tag_objects = SYWTags::getTags('com_content.article');
				if ($tag_objects !== false) {
					foreach ($tag_objects as $tag_object) {
						$tags[] = $tag_object->id;
					}
				}

				if (empty($tags) /*&& $params->get('tags_inex', 1)*/) { // won't return any article if no article has been associated to any tag (when include tags only)
					return array();
				}
			}
		}

		if (!empty($tags)) {

			$tags_to_match = implode(',', $tags);

			//$query->select($db->quoteName('t.id', 'tagid'));
			$query->select('COUNT(t.id) AS tags_count');
			$query->join('INNER', $db->quoteName('#__contentitem_tag_map', 'm').' ON '.$db->quoteName('m.content_item_id').' = '.$db->quoteName('a.id').' AND '.$db->quoteName('m.type_alias').' = '.$db->quote('com_content.article'));
			$query->join('INNER', $db->quoteName('#__tags', 't') . ' ON '.$db->quoteName('m.tag_id').' = '.$db->quoteName('t.id'));
			$query->where($db->quoteName('t.id').' IN ('.$tags_to_match.')');
			$query->where($db->quoteName('t.access').' IN ('.$groups.')');
			$query->where($db->quoteName('t.published').' = 1');

			if ($params->get('tags_match', 'any') == 'all') {
				$query->having('COUNT('.$db->quoteName('t.id').') = '.count($tags));
			}

			$query->group($db->quoteName('a.id'));
		}

		// language filter

		if ($params->get('filter_lang', 1) && $app->getLanguageFilter()) {
			$query->where('a.language IN ('.$db->quote(Factory::getLanguage()->getTag()).','.$db->quote('*').')');
		}

		// ordering

		$ordering = '';

		// featured switch

		$featured = false;
		$featured_only = false;
		switch ($params->get('show_f'))
		{
			case '1': // only
				$featured = true;
				$featured_only = true;
				$query->where('a.featured = 1');
				if ($params->get('order') == 'o_asc' || $params->get('order') == 'o_dsc') {
					$query->join('LEFT', '#__content_frontpage AS fp ON fp.content_id = a.id');
				}
				break;
			case '0': // hide
				$query->where('a.featured = 0');
				break;
			case '2': // first the featured ones
				$featured = true;
				if ($params->get('order') == 'o_asc' || $params->get('order') == 'o_dsc') {
					$query->join('LEFT', '#__content_frontpage AS fp ON fp.content_id = a.id');
				}
				$ordering .= 'a.featured DESC,';
				break;
			default: // no discrimination between featured/unfeatured items
				$featured = true;
				if ($params->get('order') == 'o_asc' || $params->get('order') == 'o_dsc') {
					$query->join('LEFT', '#__content_frontpage AS fp ON fp.content_id = a.id');
				}
		}

		// category order

		if (!$featured_only) {
			switch ($params->get('cat_order')) {
				case 'o_asc' :
					$ordering .= "c.lft ASC,";
					break;
				case 'o_dsc' :
					$ordering .= "c.lft DESC,";
					break;
				default :
			}
		}

		// general ordering

		switch ($params->get('order'))
		{
			case 'o_asc': if ($featured) { $ordering .= 'CASE WHEN (a.featured = 1) THEN fp.ordering ELSE a.ordering END ASC'; } else { $ordering .= 'a.ordering ASC'; } break;
			case 'o_dsc': if ($featured) { $ordering .= 'CASE WHEN (a.featured = 1) THEN fp.ordering ELSE a.ordering END DESC'; } else { $ordering .= 'a.ordering DESC'; } break;
			case 'p_asc': $ordering .= 'a.publish_up ASC'; break;
			case 'p_dsc': $ordering .= 'a.publish_up DESC'; break;
			case 'm_asc': $ordering .= 'a.modified ASC, a.created ASC'; break;
			case 'm_dsc': $ordering .= 'a.modified DESC, a.created DESC'; break;
			case 'c_asc': $ordering .= 'a.created ASC'; break;
			case 'c_dsc': $ordering .= 'a.created DESC'; break;
			case 'mc_asc': $ordering .= 'CASE WHEN (a.modified IS NULL) THEN a.created ELSE a.modified END ASC'; break;
			case 'mc_dsc': $ordering .= 'CASE WHEN (a.modified IS NULL) THEN a.created ELSE a.modified END DESC'; break;
			case 'random': $ordering .= 'rand()'; break;
			case 'hit': $ordering .= 'a.hits DESC'; break;
			case 'title_asc': $ordering .= 'a.title ASC'; break;
			case 'title_dsc': $ordering .= 'a.title DESC'; break;
			default: $ordering .= 'a.publish_up DESC';
		}

		$query->order($ordering);

		// include only

		$articles_to_include = trim($params->get('in'));
		if (!empty($articles_to_include)) {
			$query->where('a.id IN ('.$articles_to_include.')');
		}

		// exclude

		$articles_to_exclude = trim($params->get('ex'));
		if (!empty($articles_to_exclude)) {
			$query->where('a.id NOT IN ('.$articles_to_exclude.')');
		}

		// launch query

		$count = trim($params->get('count', ''));

		if (!empty($count)) {
			$db->setQuery($query, 0, $count);
		} else {
			$db->setQuery($query);
		}

		try {
			$items = $db->loadObjectList();
		} catch (ExecutionFailureException $e) {
			$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			return null;
		}

		// END OF DATABASE QUERY

		if (empty($items)) {
			return array();
		}

		foreach ($items as $item) {

			// Convert the images field to an array
			$registry = new Registry();
			$registry->loadString($item->images);
			$item->images = $registry->toArray();
			
			// clean image URLs
			
			if (isset($item->images['image_fulltext']) && $item->images['image_fulltext']) {
                $fulltext_image_object = HTMLHelper::cleanImageURL($item->images['image_fulltext']);
                $item->images['image_fulltext'] = $fulltext_image_object->url;
			}

			// Convert the urls field to an array
			$registry = new Registry();
			$registry->loadString($item->urls);
			$item->urls = $registry->toArray();
		}

		return $items;
	}

}
