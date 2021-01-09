<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\Exception\ExecutionFailureException;
use SYW\Library\Cache as SYWCache;
use SYW\Library\Text as SYWText;
use SYW\Library\Utilities as SYWUtilities;

require_once (JPATH_SITE.'/components/com_k2/helpers/route.php');
require_once (JPATH_SITE.'/components/com_k2/helpers/permissions.php');
require_once (JPATH_SITE.'/components/com_k2/models/itemlist.php');

class K2Helper
{
	/**
	 *
	 * @param unknown $params
	 * @param unknown $items
	 * @throws \Exception
	 * @return array of categories (id, description, article count)
	 */
	static function getCategoryList($params, $items)
	{
		$categories = array();

		// get all categories and how many articles are in them
		foreach ($items as $item) {
			if (array_key_exists($item->catid, $categories)) {
				$categories[$item->catid]++;
			} else {
				$categories[$item->catid] = 1;
			}
		}

		if ($params->get('show_cat_description', 0)) { // need description

			$categories_string = implode(',', array_keys($categories));

			$db = Factory::getDbo();

			$query = $db->getQuery(true);

			$query->select($db->quoteName('id'));
			$query->select($db->quoteName('description'));
			$query->from($db->quoteName('#__k2_categories'));
			$query->where($db->quoteName('id').' IN ('.$categories_string.')');

			$db->setQuery($query);

			try {
				$categories_list = $db->loadObjectList('id');
			} catch (ExecutionFailureException $e) {
			    Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				return null;
			}

			foreach ($categories_list as $category) {
				$category->count = $categories[$category->id];
			}
		} else {
			$categories_list = array();

			foreach ($categories as $key => $value) {
				$categories_list[$key] = (Object) array('id' => $key, 'count' => $value);
			}
		}

		return $categories_list;
	}

	static function getList($params, $module)
	{
		$db = Factory::getDbo();
		$app = Factory::getApplication();

		$user = Factory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		//$nullDate = $db->quote($db->getNullDate());
		$nowDate = $db->quote(Factory::getDate()->toSql());

		$jinput = $app->input;
		$option = $jinput->get('option');
		$view = $jinput->get('view');

		if (!$params->get('show_on_item_page', 1)) {
			if ($option === 'com_k2' && $view === 'item') {
				return null;
			}
		}

		$query = $db->getQuery(true);

		$item_on_page_id = '';
		$item_on_page_tagids = array();
		$item_on_page_keys = array();

		$related = (string) $params->get('related', '0'); // 0: no, 1: keywords, 2: tags articles only, 3: tags any content

		if ($related == '1') { // related by keyword

			if ($option === 'com_k2' && $view === 'item') {
				$temp = $jinput->getString('id');
				$temp = explode(':', $temp);
				$item_on_page_id = $temp[0];
			}

			if ($item_on_page_id) {

				$query->select($db->quoteName('metakey'));
				$query->from($db->quoteName('#__k2_items'));
				$query->where($db->quoteName('id').' = '.$item_on_page_id);

				$db->setQuery($query);

				try {
					$result = $db->loadResult();
				} catch (ExecutionFailureException $e) {
					$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
					return null;
				}

				$result = trim($result);
				if (empty($result)) {
					return array(); // won't find a related item if no key is present
				}

				$keys = explode(',', $result);

				// assemble any non-blank word(s)
				foreach ($keys as $key) {
					$key = trim($key);
					if ($key) {
						$item_on_page_keys[] = $key;
					}
				}

				if (empty($item_on_page_keys)) {
					return array();
				}

				$query->clear();
			} else {
				return null; // no result (was not on item page)
			}

		} else if ($related == '2' || $related == '3') { // related by tag. '3' does not apply to K2

			if ($option !== 'com_k2' || $view !== 'item') {
				return null; // no result (was not on item page)
			}

			$temp = $jinput->getString('id');
			$temp = explode(':', $temp);
			$item_on_page_id = $temp[0];

			// get tags of k2 item on the page

			$query->select($db->quoteName('tag.id'));
			$query->from($db->quoteName('#__k2_tags', 'tag'));
			$query->join('LEFT', $db->quoteName('#__k2_tags_xref', 'xref').' ON '.$db->quoteName('tag.id').' = '.$db->quoteName('xref.tagID'));
			$query->where($db->quoteName('tag.published').' = 1');
			$query->where($db->quoteName('xref.itemID').' = '.$item_on_page_id);

			$db->setQuery($query);

			try {
				$item_on_page_tagids = $db->loadColumn();
			} catch (ExecutionFailureException $e) {
				$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				return null;
			}

			if (empty($item_on_page_tagids)) {
				return array(); // no result because no tag found for the object on the page
			}

			$query->clear();
		}

		// START OF DATABASE QUERY

		$fulltext_query = 'a.fulltext, ';

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

		$query->select('DISTINCT a.id, a.title, a.alias, a.introtext, '.$fulltext_query.

			'CASE WHEN a.fulltext IS NULL OR a.fulltext = \'\' THEN 0 ELSE 1 END AS fulltexthascontent, '.

			'a.checked_out, a.checked_out_time, '.
			'a.catid, a.created, a.created_by, a.created_by_alias, '.

			'a.published AS state, '.

			// Use created if modified is 0
			'CASE WHEN a.modified IS NULL THEN a.created ELSE a.modified END as modified, '.
			'a.modified_by, uam.name as modified_by_name, '.

			// Use created if publish_up is 0
			'CASE WHEN a.publish_up IS NULL THEN a.created ELSE a.publish_up END as publish_up, '.
			'a.publish_down, a.params, a.metadata, a.metakey, a.metadesc, a.access, a.hits, a.featured, a.language');

		$query->select($subquery1);
		$query->select($subquery2);

		$query->from('#__k2_items AS a');

		// join over the categories
		$query->select('c.name AS category_title, c.access AS category_access, c.alias AS cat_alias');
		$query->join('INNER', '#__k2_categories AS c ON c.id = a.catid');

		$query->where('a.trash = 0');
		$query->where('c.published = 1 AND c.trash = 0');

		// join over the users for the author and modified_by names
		switch ($params->get('show_a', 'alias')) {
			case 'full': $query->select("ua.name AS author"); break;
			case 'user': $query->select("ua.username AS author"); break;
			default: $query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author");
		}

		$query->select("ua.email AS author_email");

		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		$query->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

		// access filter

		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));

		$show_unauthorized_items = false; // do not allow to show unauthorized items in the free version

		if (!$show_unauthorized_items) { // show authorized items only
			$query->where('a.access IN ('.$groups.')');
			$query->where('c.access IN ('.$groups.')');
		}

		// filter by start and end dates

		$postdate = $params->get('post_d', 'published');

		if ($postdate != 'fin_pen' && $postdate != 'pending') {
			$query->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= '.$nowDate.')');
		}
		if ($postdate == 'pending') {
			$query->where($db->quoteName('a.publish_up') . ' > ' . $nowDate);
		}
		$query->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= '.$nowDate.')');

		// filter by date range

		switch ($postdate)
		{
			case 'created' : $dateField = 'a.created'; break;
			case 'modified' : $dateField = 'a.modified'; break;
			case 'finished' : case 'fin_pen' : /*case 'pending' :*/ $dateField = 'a.publish_down'; break;
			default: $dateField = 'a.publish_up';
		}

		$query->select($db->quoteName($dateField, 'date'));

		switch ($params->get('use_range', 0))
		{
			case 1: // relative
				$range_from = $params->get('range_from', 'now'); // now, day, week, month, year
				$spread_from = $params->get('spread_from', 1);
				$range_to = $params->get('range_to', 'week');
				$spread_to = $params->get('spread_to', 1);

				if ($range_from == 'now') {
					$query->where($dateField.' <= '.$nowDate);
				} else {
// 					if ($dateField != 'a.publish_down') {
// 						$query->where($dateField.' <= DATE_SUB('.$nowDate.', INTERVAL '.$spread_from.' '.$range_from.')');
// 					} else {
// 						$query->where($dateField.' <= DATE_ADD('.$nowDate.', INTERVAL '.$spread_from.' '.$range_from.')');
// 					}
					if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
						$query->where($dateField.' <= DATE_ADD('.$nowDate.', INTERVAL '.$spread_from.' '.$range_from.')');
					} else {
						$query->where($dateField.' <= DATE_SUB('.$nowDate.', INTERVAL '.$spread_from.' '.$range_from.')');
					}
				}
				if ($range_to == 'now') {
					$query->where($dateField.' >= '.$nowDate);
				} else {
// 					if ($dateField != 'a.publish_down') {
// 						$query->where($dateField.' >= DATE_SUB('.$nowDate.', INTERVAL '.$spread_to.' '.$range_to.')');
// 					} else {
// 						$query->where($dateField.' >= DATE_ADD('.$nowDate.', INTERVAL '.$spread_to.' '.$range_to.')');
// 					}
					if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
						$query->where($dateField.' >= DATE_ADD('.$nowDate.', INTERVAL '.$spread_to.' '.$range_to.')');
					} else {
						$query->where($dateField.' >= DATE_SUB('.$nowDate.', INTERVAL '.$spread_to.' '.$range_to.')');
					}
				}
			break;

			case 2: // range
				$startDateRange = $db->quote($params->get('start_date_range', $db->getNullDate()));
				$endDateRange = $db->quote($params->get('end_date_range', $db->getNullDate()));

				$query->where('('.$dateField.' >= '.$startDateRange.' AND '.$dateField.' <= '.$endDateRange.')');
			break;
		}

		// category filter

		$categories_array = $params->get('k2catid', array());

		$array_of_category_values = array_count_values($categories_array);
		if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected therefore no filtering
			// take everything, so no category selection
		} else {
			if (isset($array_of_category_values['auto']) && $array_of_category_values['auto'] > 0) { // 'auto' was selected

				$categories_array = array();

				if ($option === 'com_k2') {
					switch($view)
					{
						case 'itemlist':
							if ($jinput->getInt('id')) { // id missing when on a 'categories' menu item where any categories can be selected
								$categories_array[] = $jinput->getInt('id');
							}
						break;
						case 'item':
							$item_id = $jinput->getInt('id');
							$catid = $jinput->getInt('catid');

							if (!$catid) {
								$query2 = $db->getQuery(true);
								$query2->select('catid');
								$query2->from('#__k2_items');
								$query2->where('id = '.$item_id);
								$db->setQuery($query2);
								$result = trim($db->loadResult());

								if ($error = $db->getErrorMsg()) {
									throw new \Exception($error);
								}

								$categories_array[] = $result;
							} else {
								$categories_array[] = $catid;
							}
						break;
					}
				}

				if (empty($categories_array)) {
					return null; // no result if not in the category page
				}
			}

			if (!empty($categories_array)) {

			    $categories_ids_array = array();
			    foreach ($categories_array as $category_id) {
			        $categories_ids_array[$category_id] = array($category_id);
			    }

				// sub-category inclusion
				$get_sub_categories = $params->get('includesubcategories', 'no');
				if ($get_sub_categories != 'no') {

					$sub_categories_array = array();

					if ($get_sub_categories == 'all') {

					    foreach ($categories_array as $category_id) {
					        $sub_categories_array[$category_id] = self::getCategoryChildren($category_id, -1, !$show_unauthorized_items);
				            $categories_ids_array[$category_id] = array_merge($categories_ids_array[$category_id], $sub_categories_array[$category_id]);
					    }

					} else {

					    $levels = $params->get('levelsubcategories', 1);

					    foreach ($categories_array as $category_id) {
					        $sub_categories_array[$category_id] = self::getCategoryChildren($category_id, $levels, !$show_unauthorized_items);
				            $categories_ids_array[$category_id] = array_merge($categories_ids_array[$category_id], $sub_categories_array[$category_id]);
						}
					}

					foreach ($sub_categories_array as $subcategory) {
					    $categories_array = array_merge($categories_array, $subcategory);
					}
				}

				$categories = implode(',', $categories_array);

				$test_type = $params->get('cat_inex', 1) ? 'IN' : 'NOT IN';

				$query->where('a.catid '.$test_type.' ('.$categories.')');
			}
		}

		// metakeys filter

		$metakeys = array();
		$keys = explode(',', $params->get('keys', ''));

		// assemble any non-blank word(s)
		foreach ($keys as $key) {
			$key = trim($key);
			if ($key) {
				$metakeys[] = $key;
			}
		}

		if (!empty($item_on_page_keys)) {
			if (!empty($metakeys)) { // if none of the tags we filter are in the content item on the page, return nothing

				$keys_in_common = array_intersect($item_on_page_keys, $metakeys);
				if (empty($keys_in_common)) {
					return array();
				}

				$metakeys = $keys_in_common;

			} else {
				$metakeys = $item_on_page_keys;
			}
		}

		if (!empty($metakeys)) {
			$concat_string = $query->concatenate(array('","', ' REPLACE(a.metakey, ", ", ",")', ' ","')); // remove single space after commas in keywords
			$query->where('('.$concat_string.' LIKE "%'.implode('%" OR '.$concat_string.' LIKE "%', $metakeys).'%")');
		}

		// tags filter

		$tags = $params->get('k2tags', array());

		if (!empty($tags)) {

			// if all selected, get all available tags
			$array_of_tag_values = array_count_values($tags);
			if (isset($array_of_tag_values['all']) && $array_of_tag_values['all'] > 0) { // 'all' was selected

				// get all tags

				$query2 = $db->getQuery(true);

				$query2->select($db->quoteName('id'));
				$query2->from($db->quoteName('#__k2_tags'));
				$query2->where('published = 1');

				$db->setQuery($query2);

				try {
					$tags = $db->loadColumn();
				} catch (ExecutionFailureException $e) {
					$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
					return null;
				}

				$query2->clear();

				if (empty($tags) && $params->get('tags_inex', 1)) { // won't return any k2 item if no k2 item has been associated to any tag (when include tags only)
					return array();
				}
			}
		}

		if (!empty($item_on_page_tagids)) {
			if (!empty($tags)) { // if none of the tags we filter are in the content item on the page, return nothing

				// take the tags common to the item on the page and the module selected tags
				$tags_in_common = array_intersect($item_on_page_tagids, $tags);
				if (empty($tags_in_common)) {
					return array();
				}

				if ($params->get('tags_match', 'any') == 'all') {
					if (count($tags_in_common) != count($tags)) {
						return array();
					}
				}

				$tags = $tags_in_common;

			} else {
				$tags = $item_on_page_tagids;
			}

			// Note: does not work if 'exclude' tags, which is normal
		}

		if (!empty($tags)) {

			$tags_to_match = implode(',', $tags);

			$query->select('COUNT(tags.id) AS tags_count');
			$query->join('LEFT', $db->quoteName('#__k2_tags_xref', 'tags_xref').' ON '.$db->quoteName('tags_xref.itemID').' = '.$db->quoteName('a.id'));
			$query->join('LEFT', $db->quoteName('#__k2_tags', 'tags').' ON '.$db->quoteName('tags_xref.tagID').' = '.$db->quoteName('tags.id'));

			// no group access in database table
			$query->where($db->quoteName('tags.published').' = 1');

			// keep all items with tags to be handled outside the query (when exclude all)
			if (!$params->get('tags_inex', 1) && $params->get('tags_match', 'any') == 'all') {
				// keep all tags
			} else {
				$test_type = $params->get('tags_inex', 1) ? 'IN' : 'NOT IN';
				$query->where($db->quoteName('tags.id').' '.$test_type.' ('.$tags_to_match.')');
			}

			if (!$params->get('tags_inex', 1) && $params->get('tags_match', 'any') == 'all') {
				// handled outside the query
			} else {
				if (!$params->get('tags_inex', 1)) { // EXCLUDE TAGS
					$query->select('tags_per_items.tag_count_per_item');

					// subquery gets all the tags for all items
					$subquery = 'SELECT ttags_xref.itemID AS content_id, COUNT(tt.id) AS tag_count_per_item FROM #__k2_tags_xref AS ttags_xref LEFT JOIN #__k2_tags AS tt ON ttags_xref.tagID = tt.id WHERE tt.published = 1 GROUP BY content_id';
					$query->join('INNER', '(' . $subquery . ') AS tags_per_items ON tags_per_items.content_id = a.id');

					// we keep items that have the same amount of tags before and after removals
					$query->having('COUNT('.$db->quoteName('tags.id').') = '.$db->quoteName('tags_per_items.tag_count_per_item'));

				} else { // INCLUDE TAGS
					if ($params->get('tags_match', 'any') == 'all') {
						$query->having('COUNT('.$db->quoteName('tags.id').') = '.count($tags));
					}
				}
			}

			$query->group($db->quoteName('a.id'));
		}

		// user filter

		$include = $params->get('author_inex', 1);
		$authors_array = $params->get('k2_created_by', array());

		// old parameter - backward compatibility
		$old_authors = $params->get('user_id', '');
		if ($old_authors) {
			switch ($old_authors)
			{
				case 'by_me': $include = true; $authors_array[] = 'auto'; break;
				case 'not_me': $include = false; $authors_array[] = 'auto'; break;
				case 'all': default: $authors_array[] = 'all';
			}
		}

		$array_of_authors_values = array_count_values($authors_array);
		if (isset($array_of_authors_values['all']) && $array_of_authors_values['all'] > 0) { // 'all' was selected
			if ($params->get('allow_edit', 0)) {
				// logged user can see his own unpublished items only
				$query->where('(a.published = 1) OR (a.published = 0 AND a.created_by = ' . (int) $user->get('id') . ')');
			} else {
				$query->where('a.published = 1');
			}
		} else if (isset($array_of_authors_values['auto']) && $array_of_authors_values['auto'] > 0) { // 'auto' was selected
			$test_type = $include ? '=' : '<>';
			$query->where('a.created_by ' .$test_type.' '.(int) $user->get('id'));
			if ($include && $params->get('allow_edit', 0)) {
				$query->where('a.published IN (0, 1)'); // show all items for the logged author, published or not
			} else {
				$query->where('a.published = 1');
			}
		} else {
			$authors = implode(',', $authors_array);
			if ($authors) {
				$test_type = $include ? 'IN' : 'NOT IN';
				$query->where('a.created_by '.$test_type.' ('.$authors.')');
			}

			if ($params->get('allow_edit', 0)) {
				if ($include) {
					if (in_array($user->get('id'), $authors_array)) {
						// the user is part of the selected authors

						// logged user can see his own unpublished items only
						$query->where('(a.published = 1) OR (a.published = 0 AND a.created_by = ' . (int) $user->get('id') . ')');

					} else {
						$query->where('a.published = 1');
					}
				} else {
					if (!in_array($user->get('id'), $authors_array)) {
						// the user is not part of the discarded authors but may not be an author

						// logged user can see his own unpublished items only
						$query->where('(a.published = 1) OR (a.published = 0 AND a.created_by = ' . (int) $user->get('id') . ')');

					} else {
						$query->where('a.published = 1');
					}
				}
			} else {
				$query->where('a.published = 1');
			}

//			if ($include && $params->get('allow_edit', 0) && count($authors_array) == 1 && $authors_array[0] == (int) $user->get('id')) {
//				$query->where('a.published IN (0, 1)'); // show all items for the logged author, published or not
//			} else {
//				$query->where('a.published = 1');
//			}
		}

		// language filter

		if ($params->get('filter_lang', 1) && $app->getLanguageFilter()) {
			$query->where('a.language IN ('.$db->quote(Factory::getLanguage()->getTag()).','.$db->quote('*').')');
		}

		$ordering = '';

		// author order

		switch ($params->get('author_order', ''))
		{
			case 'selec_asc': $ordering .= "author ASC,"; break;
			case 'selec_dsc': $ordering .= "author DESC,"; break;
		}

		// featured switch

		$featured = false;
		$featured_only = false;
		switch ($params->get('show_f', 3))
		{
			case '1': // only
				$featured = true;
				$featured_only = true;
				$query->where('a.featured = 1');
				break;
			case '0': // hide
				$query->where('a.featured = 0');
				break;
			case '2': // first the featured ones
				$featured = true;
				$ordering .= 'a.featured DESC,';
				break;
			default: // no discrimination between featured/unfeatured items
				$featured = true;
				break;
		}

		// category order

		if (!$featured_only) {
			switch ($params->get('cat_order', ''))
			{
				case 'o_asc': $ordering .= 'c.parent ASC, c.ordering ASC,'; break;
				case 'o_dsc': $ordering .= 'c.parent DESC, c.ordering DESC,'; break;
				case 't_asc': $ordering .= 'c.name ASC,'; break;
				case 't_dsc': $ordering .= 'c.name DESC,'; break;
			}
		}

		// general ordering

		switch ($params->get( 'order' ))
		{
			case 'o_asc': if ($featured) { $ordering .= 'CASE WHEN (a.featured = 1) THEN a.featured_ordering ELSE a.ordering END ASC'; } else { $ordering .= 'a.ordering ASC'; } break;
			case 'o_dsc': if ($featured) { $ordering .= 'CASE WHEN (a.featured = 1) THEN a.featured_ordering ELSE a.ordering END DESC'; } else { $ordering .= 'a.ordering DESC'; } break;
			case 'p_asc': $ordering .= 'a.publish_up ASC'; break;
			case 'p_dsc': $ordering .= 'a.publish_up DESC'; break;
			case 'f_asc': $ordering .= 'CASE WHEN (a.publish_down IS NULL) THEN a.publish_up ELSE a.publish_down END ASC'; break;
			case 'f_dsc': $ordering .= 'CASE WHEN (a.publish_down IS NULL) THEN a.publish_up ELSE a.publish_down END DESC'; break;
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
			default: $ordering .= 'a.publish_up DESC'; break;
		}

		$query->order($ordering);

		// include only

		$articles_to_include = trim($params->get('in', ''));
		if (!empty($articles_to_include)) {
			$query->where('a.id IN ('.$articles_to_include.')');
		}

		// exclude

		$articles_to_exclude = array_filter(explode(",", trim($params->get('ex', ''))));

		$item_on_page_id = '';
		if ($params->get('ex_current_item', 0) && $option === 'com_k2' && $view === 'item') {
			$temp = $jinput->getString('id');
			$temp = explode(':', $temp);
			$item_on_page_id = $temp[0];
		}

		if ($item_on_page_id) { // do not show the current item in the list
			$articles_to_exclude[] = $item_on_page_id;
		}

		if (!empty($articles_to_exclude)) {
			$query->where('a.id NOT IN ('.implode(",", $articles_to_exclude).')');
		}

		// launch query

		$count = trim($params->get('count', ''));
		$startat = $params->get('startat', 1);
		if ($startat < 1) {
			$startat = 1;
		}

		if (!empty($tags) && !$params->get('tags_inex', 1) && $params->get('tags_match', 'any') == 'all') {
			$db->setQuery($query);
		} else if (!empty($count) && $params->get('count_for', 'articles') == 'articles') {
			$db->setQuery($query, $startat - 1, intval($count));
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

		// ITEM DATA MODIFICATIONS AND ADDITIONS

		$still_need_to_slice_count = false;

		// exclude all

		if (!empty($tags) && !$params->get('tags_inex', 1) && $params->get('tags_match', 'any') == 'all') {

			$total_tags = count($tags);

			foreach ($items as $key => &$item) {

				if (!isset($item->tags)) {
					$item->tags = self::getItemTags($item->id);
				}

				if (self::getItemTagsCountIn($item->tags, $tags) == $total_tags) {
					unset($items[$key]);
				}
			}

			$still_need_to_slice_count = true;
		}

		// restrict articles per author or category
		// drawback : forces grouping per author or category

		$count_for = $params->get('count_for', 'articles');

		if ($count_for == 'catid' || $count_for == 'author') {

			$grouped = array();
			$pass = array();
			$ordered_items = array();

			if ($count_for == 'catid' && isset($categories_ids_array) && count($categories_ids_array) > 1) {

			    $array_keys = array_keys($categories_ids_array);
			    $last_key = array_pop($array_keys);

			    foreach ($categories_ids_array as $category_id => $categories_id_array) {

			        $grouped[$category_id] = array();
			        $pass[$category_id] = array();

			        foreach ($items as $key => $item) {

			            if (in_array($item->catid, $categories_id_array)) {

			                if (count($pass[$category_id]) < ($startat - 1)) {
			                    $pass[$category_id][] = $item->id;
			                } else {
			                    if ($count) {
			                        if (count($grouped[$category_id]) < intval($count)) {
			                            $grouped[$category_id][] = $item->catid;
			                            $ordered_items[$key] = $item;
			                        } else {
			                            if ($category_id == $last_key) {
			                                break; // only break on the last category
			                            }
			                        }
			                    } else {
			                        $ordered_items[$key] = $item;
			                    }
			                }
			                unset($items[$key]);
			            }
			        }
			    }

			    ksort($ordered_items); // the ordre may have been lost

			} else {

				foreach ($items as $key => $item) {

					if (!isset($grouped[$item->$count_for])) {
						$grouped[$item->$count_for] = array();
						$pass[$item->$count_for] = array();
					}

					if (count($pass[$item->$count_for]) < ($startat - 1)) {
						$pass[$item->$count_for][] = $item->id;
					} else {
						if ($count) {
							if (count($grouped[$item->$count_for]) < intval($count)) {
								$grouped[$item->$count_for][] = $item->id;
    							$ordered_items[$key] = $item;
							}
						} else {
    						$ordered_items[$key] = $item;
						}
					}
				}
			}

			$items = $ordered_items;
		}

		// limit to count

		if ($still_need_to_slice_count) {
			if (!empty($count)) {
				$items = array_slice($items, $startat - 1, intval($count));
			} else {
				$items = array_slice($items, $startat - 1);
			}
		}

		// parameters for all

		$head_type = $params->get('head_type', 'none');

		$image_types = array('image', 'imageintro', 'imagefull', 'allimagesasc', 'allimagesdesc');

		$show_image = false;

		if (in_array($head_type, $image_types)) {

			$show_image = true;

			$crop_picture = $params->get('crop_pic', 0);

			$allow_remote = $params->get('allow_remote', true);

			$maintain_height = $params->get('maintain_height', 0);
			$head_width = $params->get('head_w', 64);
			$head_height = $params->get('head_h', 64);
			$border_width = $params->get('border_w', 0);

			$head_width = $head_width - $border_width * 2;
			$head_height = $head_height - $border_width * 2;

			$filter = $params->get('filter', 'none');

			$quality_jpg = $params->get('quality_jpg', 100);
			$quality_png = $params->get('quality_png', 0);
			$quality_webp = $params->get('quality_webp', 80);

			if ($quality_jpg > 100) {
				$quality_jpg = 100;
			}
			if ($quality_jpg < 0) {
				$quality_jpg = 0;
			}

			if ($quality_png > 9) {
				$quality_png = 9;
			}
			if ($quality_png < 0) {
				$quality_png = 0;
			}

			if ($quality_webp > 100) {
				$quality_webp = 100;
			}
			if ($quality_webp < 0) {
				$quality_webp = 0;
			}

			$image_qualities = array('jpg' => $quality_jpg, 'png' => $quality_png, 'webp' => $quality_webp);

			$clear_cache = $params->get('clear_cache', 0);
			if ($params->get('site_mode', 'adv') == 'dev') {
				$clear_cache = 1;
			} else if ($params->get('site_mode', 'adv') == 'prod') {
				$clear_cache = 0;
			}

			$subdirectory = 'thumbnails/lne';
			if ($params->get('thumb_path', 'images') == 'cache') {
				$subdirectory = 'mod_latestnewsenhanced';
			}
			$tmp_path = SYWCache::getTmpPath($params->get('thumb_path', 'images'), $subdirectory);

			$default_picture = trim($params->get('default_pic', ''));

			if ($clear_cache) {
				Helper::clearThumbnails($module->id, $tmp_path);
			}
		}

		$text_type = $params->get('text', 'intro');
		$letter_count = trim($params->get('l_count'));
		$truncate_last_word = $params->get('trunc_l_w', 0);
		$keep_tags = $params->get('keep_tags');
		$strip_tags = $params->get('strip_tags', 1);
		$always_show_readmore = $params->get('readmore_always_show', true);
		$trigger_OnContentPrepare = $params->get('trigger_events', false);
		$force_one_line = $params->get('force_one_line', false);
		$title_letter_count = trim($params->get('letter_count_title', ''));
		$title_truncate_last_word = $params->get('trunc_l_w_title', 0);
		//$show_date = $params->get('show_d', 'date');

		$link_to = $params->get('link_to', 'article');
		switch ($params->get('link_target', 'default')) {
			case 'same': $link_target = ''; break;
			case 'inline': $link_target = 4; break;
			case 'new': $link_target = 1; break;
			case 'modal': $link_target = 3; break;
			case 'popup': $link_target = 2; break;
			default: $link_target = 'default';
		}

		$when_no_date = $params->get('when_no_date', 0);
		$items_with_no_date = array();

		foreach ($items as $key => &$item) {

			// date

			if ($item->date == $db->getNullDate() || $item->date == null) {

				if ($when_no_date == 0) {
					unset($items[$key]);
					continue;
				}

				$item->date = '';
			}

			// category link

			if (!$show_unauthorized_items || in_array($item->category_access, $authorised)) {
				$item->catlink = urldecode(Route::_(K2HelperRoute::getCategoryRoute($item->cat_slug)));
				$item->category_authorized = true;
			} else {
				$catlink = new Uri(Route::_('index.php?option=com_users&view=login', false));
				$catlink->setVar('return', base64_encode(K2HelperRoute::getCategoryRoute($item->cat_slug)));

				$item->catlink = $catlink;
				$item->category_authorized = false;
			}

			// item edit link

			if ($params->get('allow_edit', 0)) {
				if ($option !== 'com_k2') {
					K2HelperPermissions::setPermissions();
				}

				if (K2HelperPermissions::canEditItem($item->created_by, $item->catid)) {
					$item->link_edit = Route::_('index.php?option=com_k2&view=item&task=edit&cid=' . $item->id . '&tmpl=component&template=system');
				}
			}

			// item link

			$item->link = '';

			if ($item->state == 1) {

				//$item->linktarget = '';
				//$item->isinternal = true;
				$item->linktitle = $item->title;

				$link_string = K2HelperRoute::getItemRoute($item->slug, $item->cat_slug);

				$forced_itemid = intval($params->get('force_itemid', ''));
				if ($forced_itemid > 0) {

					if (Multilanguage::isEnabled()) {
						$currentLanguage = Factory::getLanguage()->getTag();
						$langAssociations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $forced_itemid, 'id', '', '');
						foreach ($langAssociations as $langAssociation) {
							if ($langAssociation->language == $currentLanguage) {
								$forced_itemid = $langAssociation->id;
								break;
							}
						}
					}

					if (strpos($link_string, 'Itemid') === false) {
						$link_string .= '&Itemid=' . $forced_itemid;
					} else {
						$link_string = preg_replace('#Itemid=([0-9]*)#', 'Itemid=' . $forced_itemid, $link_string);
					}
				}

				if ($item->category_authorized && (!$show_unauthorized_items || in_array($item->access, $authorised))) {

					if ($link_target !== 'default') {
						$item->linktarget = $link_target;
					} else {
						$item->linktarget = '';
					}

					$item->link = urldecode(Route::_($link_string));
					$item->authorized = true; // we know that user has the privilege to view the article

				} else {

					$link = new Uri(Route::_('index.php?option=com_users&view=login', false));
					$link->setVar('return', base64_encode($link_string));

					$item->link = $link;
					$item->linktarget = ''; // cannot open in modal window in this case - too many cases where it might fail bacause the login form	opens first
					$item->authorized = false;
				}
			}

			// rating (to avoid call to rating plugin, use $item->vote)

			$query->clear();

			$query->select('ROUND(v.rating_sum / v.rating_count, 1) AS rating');
			$query->select($db->quoteName('v.rating_count', 'rating_count'));
			$query->from($db->quoteName('#__k2_rating', 'v'));
			$query->where($db->quoteName('v.itemID').' = '.$item->id);

			$db->setQuery($query);

			$item->vote = '';
			$item->vote_count = 0;
			try {
				$ratings = $db->loadObjectList();
				foreach ($ratings as $rating) {
					$item->vote = $rating->rating;
					$item->vote_count = $rating->rating_count;
				}
			} catch (ExecutionFailureException $e) {
				//$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			}

			// tags

			if (!isset($item->tags)) {
				$item->tags = self::getItemTags($item->id);
			}

			// thumbnail image creation

			$item->imagetag = '';
			$item->error = array();

			if ($show_image) {

				$thumbnails_exist = false;
				$filename = '';

				if (!$clear_cache && $params->get('create_thumb', 1)) {
					$thumbnails_exist_tmp = Helper::thumbnailExists($module->id, $item->id, $tmp_path);
					if ($thumbnails_exist_tmp != false) {
						$filename = $thumbnails_exist_tmp;
						$thumbnails_exist = true;
					}
				}

				if (!$thumbnails_exist) {
					// thumbnail(s) do not exist

					$imagesrc = '';

					if ($head_type == 'imageintro' || $head_type == 'imagefull') { // K2 image

						$k2imagesrc = 'media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg';
						if (is_file(JPATH_ROOT.'/'.$k2imagesrc)) { // makes sure the K2 image exists
							$imagesrc = $k2imagesrc;
						}

					} else if ($head_type == 'image') {
						if (isset($item->fulltext))	{
							$imagesrc = Helper::getImageSrcFromContent($item->introtext, $item->fulltext);
						} else {
							$imagesrc = Helper::getImageSrcFromContent($item->introtext);
						}

					} else if ($head_type == 'allimagesasc') {
						if (isset($item->fulltext))	{
							$imagesrc = Helper::getImageSrcFromContent($item->introtext, $item->fulltext);
						} else {
							$imagesrc = Helper::getImageSrcFromContent($item->introtext);
						}

						// if images not found, look into K2 image
						if (empty($imagesrc)) {
							$k2imagesrc = 'media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg';
							if (is_file(JPATH_ROOT.'/'.$k2imagesrc)) { // makes sure the k2 image exists
								$imagesrc = $k2imagesrc;
							}
						}

					} else if ($head_type == 'allimagesdesc') {

						$k2imagesrc = 'media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg';
						if (is_file(JPATH_ROOT.'/'.$k2imagesrc)) { // makes sure the k2 image exists
							$imagesrc = $k2imagesrc;
						}

						// if K2 image not found, look into the K2 items
						if (empty($imagesrc)) {

							if (isset($item->fulltext))	{
								$imagesrc = Helper::getImageSrcFromContent($item->introtext, $item->fulltext);
							} else {
								$imagesrc = Helper::getImageSrcFromContent($item->introtext);
							}
						}
					}

					// last resort, use default image if it exists
					$used_default_image = false;
					if (empty($imagesrc)) {
						if ($default_picture) {
							$imagesrc = $default_picture;
							$used_default_image = true;
						}
					}

					if ($imagesrc) { // found an image
					    if (!$params->get('create_thumb', 1)) {
					        $filename = $imagesrc;
					    } else {
					    	$result_array = Helper::getImageFromSrc($module->id, $item->id, $imagesrc, $tmp_path, $head_width, $head_height, $crop_picture, $image_qualities, $filter, false, $allow_remote);

    						if (!empty($result_array[0])) {
    							$filename = $result_array[0];
    						}

    						if (!empty($result_array[1])) {
    							// if error for the file found, try and use the default image instead
    							if (!$used_default_image && $default_picture) { // if the default image was the one chosen, no use to retry
    								$result_array = Helper::getImageFromSrc($module->id, $item->id, $default_picture, $tmp_path, $head_width, $head_height, $crop_picture, $image_qualities, $filter, false, $allow_remote);

    								if (!empty($result_array[0])) {
    									$filename = $result_array[0];
    								}

    								if (!empty($result_array[1])) {
    									$item->error[] = $result_array[1];
    								}
    							} else {
    								$item->error[] = $result_array[1];
    							}
    						}
					    }
					}

					if ($filename && empty($item->error)) {
						$thumbnails_exist = true;
					}
				}

				if ($filename) {

// 					$extra_styling = '';

// 					if ($thumbnails_exist) {
						// thumbnails have been created

// 						if (!$crop_picture && $maintain_height) {

// 							$imagesize = @getimagesize($filename); // @ to avoid warnings
// 							if ($imagesize !== FALSE) {
// 								$imageheight = $imagesize[1];

// 								$top = intval(($head_height - $imageheight) / 2); // to center the image, when no cropping
// 								$extra_styling = ' style="position: relative; top: '.$top.'px"';
// 							}
// 						}

// 						$filename = Uri::base(true).'/'.$filename;
// 					}

					$img_attributes = array();
					if ($crop_picture) {
						$img_attributes = array('width' => $head_width, 'height' => $head_height);
					}

					//$item->imagetag = '<img alt="'.$item->title.'" src="'.$filename.'"'.$extra_styling.' />';
					$item->imagetag = SYWUtilities::getImageElement($filename, $item->title, $img_attributes, true);
				}
			}

			// ago

			if ($item->date) {
				$details = Helper::date_to_counter($item->date, ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') ? true : false);

				$item->nbr_seconds  = intval($details['secs']);
				$item->nbr_minutes  = intval($details['mins']);
				$item->nbr_hours = intval($details['hours']);
				$item->nbr_days = intval($details['days']);
				$item->nbr_months = intval($details['months']);
				$item->nbr_years = intval($details['years']);
			}

			// calendar shows an extra field of type 'date'

			if ($head_type == 'calendar') {
				$item->calendar_date = $item->date;
			}

			// title

			if (!$force_one_line) {
				if (strlen($title_letter_count) > 0) {
					$item->title = SYWText::getText($item->title, 'txt', (int)$title_letter_count, true, '', true, $title_truncate_last_word);
				}
			}

			// text

			$item->text = '';

			$number_of_letters = -1;
			if ($letter_count != '') {
				$number_of_letters = (int)($letter_count);
			}

			$beacon = '';
			if (!$always_show_readmore) {
				$beacon = '^';
			}

			switch ($text_type)
			{
				case 'intrometa': $use_intro = (trim($item->introtext) != '') ? true : false; break;
				case 'metaintro': $use_intro = (trim($item->metadesc) != '') ? false : true; break;
				case 'meta': $use_intro = false; break;
				default: case 'intro': $use_intro = true;
			}

			if ($use_intro) { // use intro text
				$item->text = $item->introtext;
				if ($item->text) {
					if ($trigger_OnContentPrepare) { // will trigger events from plugins
						$item->text = HTMLHelper::_('content.prepare', $item->text);
					}
					$item->text = SYWText::getText($item->text.$beacon, 'html', $number_of_letters, $strip_tags, trim($keep_tags), true, $truncate_last_word);
				}
			} else { // use meta text
				$item->text = SYWText::getText($item->metadesc.$beacon, 'txt', $number_of_letters, false, '', true, $truncate_last_word);
			}

			// the text won't be cropped if the ^ character is still present after processing (hopefully no ^ at the end of the text)
			$item->cropped = true;
			if (!$always_show_readmore) {
				$text_length = strlen($item->text);
				$item->text = rtrim($item->text, "^");
				if (strlen($item->text) < $text_length && !$item->fulltexthascontent) {
					$item->cropped = false;
				}
			}

			// re-order items with no dates
			if (empty($item->date) && ($when_no_date == 1 || $when_no_date == 2)) {
				$items_with_no_date[] = $item;
				unset($items[$key]);
			}
		}

		if ($when_no_date == 1) {
			return array_merge($items_with_no_date, $items);
		} else if ($when_no_date == 2) {
			return array_merge($items, $items_with_no_date);
		}

		return $items;
	}

	protected static function getItemTags($id)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query->select('tag.*');
		$query->from($db->quoteName('#__k2_tags', 'tag'));
		$query->join('LEFT', $db->quoteName('#__k2_tags_xref', 'xref').' ON '.$db->quoteName('tag.id').' = '.$db->quoteName('xref.tagID'));
		$query->where($db->quoteName('tag.published').' = 1');
		$query->where($db->quoteName('xref.itemID').' = '.$id);
		$query->order($db->quoteName('tag.name').' ASC');

		$db->setQuery($query);

		try {
			$tags_array = $db->loadObjectList();
			if (count($tags_array) > 0) {
				return $tags_array;
			}
		} catch (ExecutionFailureException $e) {
			return array(); // should return null so we know there is an error
		}

		return array();
	}

	protected static function getItemTagsCountIn($item_tags, $tags)
	{
		if (empty($item_tags)) {
			return 0;
		}

		$count_tags_in_item = 0;

		foreach ($item_tags as $tag) {
			if (in_array($tag->id, $tags)) {
				$count_tags_in_item++;
			}
		}

		return $count_tags_in_item;
	}

	protected static function getCategoryChildren($category_id, $level = -1, $limited_access = true, $reset = true)
	{
	    static $array = array();
	    if ($reset) {
	        $array = array();
	    }

	    $db = Factory::getDbo();

	    $query = $db->getQuery(true);

	    $query->select($db->quoteName('id'));
	    $query->from($db->quoteName('#__k2_categories'));
	    $query->where($db->quoteName('parent').'='.$category_id);
	    $query->where($db->quoteName('published').'=1');
	    $query->where($db->quoteName('trash').'=0');

	    if ($limited_access) {
	        $query->where($db->quoteName('access').' IN ('.implode(',', Factory::getUser()->getAuthorisedViewLevels()).')');
	    }

	    if (Factory::getApplication()->getLanguageFilter()) {
	        $query->where($db->quoteName('language').' IN ('.$db->quote(Factory::getLanguage()->getTag()).','.$db->quote('*').')');
	    }

	    $db->setQuery($query);

	    try {
            $rows = $db->loadColumn();
            foreach ($rows as $row) {
                array_push($array, $row);
                if ($level < 0) {
                    self::getCategoryChildren($row, -1, $limited_access, false);
                } else if ($level > 1) {
                    self::getCategoryChildren($row, $level - 1, $limited_access, false);
                }
            }
	    } catch (ExecutionFailureException $e) {
	        $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	        return null;
	    }

        return $array;
	}

}
?>
