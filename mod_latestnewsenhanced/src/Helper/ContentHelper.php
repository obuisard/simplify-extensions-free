<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use SYW\Library\Cache as SYWCache;
use SYW\Library\Tags as SYWTags;
use SYW\Library\Text as SYWText;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Library\Version as SYWVersion;

class ContentHelper
{
	/**
	 *
	 * @param object $params
	 * @param array $items
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

			$db = Factory::getDbo();

			$query = $db->getQuery(true);

			$query->select($db->quoteName('id'));
			$query->select($db->quoteName('description'));
			$query->from($db->quoteName('#__categories'));
			$query->whereIn($db->quoteName('id'), array_keys($categories));

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
	    $app = Factory::getApplication();

		$db = Factory::getDbo();

		$user = $app->getIdentity();
		$view_levels = $user->getAuthorisedViewLevels();

		$nowDate = $db->quote(Factory::getDate()->toSql());

		$jinput = $app->input;
		$option = $jinput->get('option');
		$view = $jinput->get('view');

		if (!$params->get('show_on_item_page', 1)) {
			if ($option === 'com_content' && ($view === 'article' || $view === 'form')) {
				return null;
			}
		}

		$item_on_page_id = '';
		$item_on_page_tagids = array();
		$item_on_page_keys = array();

		$related = (string) $params->get('related', '0'); // 0: no, 1: keywords, 2: tags articles only, 3: tags any content

		if ($related == '1') { // related by keyword

			if ($option === 'com_content' && $view === 'article') {
				$temp = $jinput->getString('id');
				$temp = explode(':', $temp);
				$item_on_page_id = $temp[0];
			}

			if ($item_on_page_id) {
			    
			    $query = $db->getQuery(true);

				$query->select($db->quoteName('metakey'));
				$query->from($db->quoteName('#__content'));
				$query->where($db->quoteName('id') . ' = :itemOnPageId');
				$query->bind(':itemOnPageId', $item_on_page_id, ParameterType::INTEGER);

				$db->setQuery($query);

				try {
					$result = $db->loadResult();
				} catch (ExecutionFailureException $e) {
					$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
					return null;
				}

				$result = trim($result);
				if (empty($result)) {
					return array(); // won't find a related article if no key is present
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
			} else {
				return null; // no result (was not on article page)
			}

		} else if ($related == '2' || $related == '3') { // related by tag

			$get_the_tags = false;
			if ($related == '2' && $option === 'com_content' && $view === 'article') {
				$get_the_tags = true;
			} else if ($related == '3') { // no restriction on the type of content
				$get_the_tags = true;

				if ($option === 'com_trombinoscopeextended' && $view === 'contact') { // because tags are recorded with com_contact
					$option = 'com_contact';
				}
			}

			if ($get_the_tags) {
				$temp = $jinput->getString('id');
				$temp = explode(':', $temp);
				$item_on_page_id = $temp[0];

				if ($item_on_page_id) {
					$helper_tags = new TagsHelper();
					$tag_objects = $helper_tags->getItemTags($option.'.'.$view, $item_on_page_id); // array of tag objects
					foreach ($tag_objects as $tag_object) {
						$item_on_page_tagids[] = $tag_object->tag_id;
					}
				}

				if (empty($item_on_page_tagids)) {
					return array(); // no result because no tag found for the object on the page
				}
			} else {
				return null; // no result (was not on article page)
			}
		}

		// START OF DATABASE QUERY
		
		$query = $db->getQuery(true);

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

		$query->select($db->quoteName(array('a.id', 'a.catid', 'a.title', 'a.alias', 'a.introtext', 'a.fulltext', 'a.state', 'a.images', 'a.urls', 'a.attribs', 'a.metadata', 'a.metakey', 'a.metadesc', 'a.access', 'a.hits', 'a.featured', 'a.language')));

		$query->select('CASE WHEN ' . $db->quoteName('a.fulltext') . ' IS NULL OR ' . $db->quoteName('a.fulltext') . ' = ' . $db->quote('') . ' THEN 0 ELSE 1 END AS ' . $db->quoteName('fulltexthascontent'));

		$query->select($db->quoteName(array('a.checked_out', 'a.checked_out_time', 'a.created', 'a.created_by', 'a.created_by_alias')));

		// Use created if modified is 0
		$query->select('CASE WHEN ' . $query->isNullDatetime('a.modified') . ' THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.modified') . ' END AS ' . $db->quoteName('modified'));
		$query->select($db->quoteName(array('a.modified_by', 'uam.name'), array('modified_by', 'modified_by_name')));

		// Use created if publish_up is 0
		$query->select('CASE WHEN ' . $query->isNullDatetime('a.publish_up') . ' THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.publish_up') . ' END AS  ' . $db->quoteName('publish_up'));
		$query->select($db->quoteName('a.publish_down'));

		$query->select($subquery1);
		$query->select($subquery2);

		$query->from($db->quoteName('#__content', 'a'));

		// join over the categories
		$query->select($db->quoteName(array('c.title', 'c.path', 'c.access', 'c.alias', 'c.params'), array('category_title', 'category_route', 'category_access', 'category_alias', 'category_params')));
		$query->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'));

		// join over the users for the author and modified_by names
		switch ($params->get('show_a', 'alias')) {
		    case 'full': $query->select($db->quoteName('ua.name', 'author')); break;
		    case 'user': $query->select($db->quoteName('ua.username', 'author')); break;
		    default: $query->select('CASE WHEN ' . $db->quoteName('a.created_by_alias') . ' > ' . $db->quote(' ') . ' THEN ' . $db->quoteName('a.created_by_alias') . ' ELSE ' . $db->quoteName('ua.name') . ' END AS ' . $db->quoteName('author'));
		}

		$query->select($db->quoteName('ua.email', 'author_email'));

		$query->join('LEFT', $db->quoteName('#__users', 'ua'), $db->quoteName('ua.id') . ' = ' . $db->quoteName('a.created_by'));
		$query->join('LEFT', $db->quoteName('#__users', 'uam'), $db->quoteName('uam.id') . ' = ' . $db->quoteName('a.modified_by'));

		// join over the categories to get parent category titles
		$query->select($db->quoteName(array('parent.title', 'parent.id', 'parent.path', 'parent.alias'), array('parent_title', 'parent_id', 'parent_route', 'parent_alias')));
		$query->join('LEFT', $db->quoteName('#__categories', 'parent'), $db->quoteName('parent.id') . ' = ' . $db->quoteName('c.parent_id'));

		$query->where($db->quoteName('c.published') . ' = 1');

		// join on voting table
		if (Helper::isInfoTypeRequired('rating', $params)) {
		    $query->select('ROUND(' . $db->quoteName('v.rating_sum') . ' / ' . $db->quoteName('v.rating_count') . ', 1) AS ' . $db->quoteName('rating'));
		    $query->select($db->quoteName('v.rating_count', 'rating_count'));
		    $query->join('LEFT', $db->quoteName('#__content_rating', 'v'), $db->quoteName('a.id') . ' = ' . $db->quoteName('v.content_id'));
		}

		// access filter

		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));

		$show_unauthorized_items = false; // no option to show unauthorized items in the free version

		if (!$show_unauthorized_items) { // show authorized items only
			$query->whereIn($db->quoteName('a.access'), $view_levels);
			$query->whereIn($db->quoteName('c.access'), $view_levels);
		}

		// filter by start and end dates

		$postdate = $params->get('post_d', 'published');

		if ($postdate != 'fin_pen' && $postdate != 'pending') {
		    $query->where('(' . $query->isNullDatetime('a.publish_up') . ' OR ' . $db->quoteName('a.publish_up') . ' <= ' . $nowDate . ')');
		}

		if ($postdate == 'pending') {
			$query->where($db->quoteName('a.publish_up') . ' > ' . $nowDate);
		}
		$query->where('(' . $query->isNullDatetime('a.publish_down') . ' OR ' . $db->quoteName('a.publish_down') . ' >= ' . $nowDate . ')');

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

		        // parameters are reversed (backward compatibility from early on version)

		        $range_from = $params->get('range_to', 'week'); // now, day, week, month, year options
		        $spread_from = $params->get('spread_to', 1);
		        $range_to = $params->get('range_from', 'now');
		        $spread_to = $params->get('spread_from', 1);

		        // test range 'from' and 'to' to see if it will be a future or a past range

		        $from = 0;
		        switch($range_from)
		        {
		            case 'day': $from += $spread_from; break;
		            case 'week': $from += $spread_from * 7; break;
		            case 'month': $from += $spread_from * 30; break; // arbitrary
		            case 'year': $from += $spread_from * 365; break; // arbitrary
		        }

		        $to = 0;
		        switch($range_to)
		        {
		            case 'day': $to += $spread_to; break;
		            case 'week': $to += $spread_to * 7; break;
		            case 'month': $to += $spread_to * 30; break; // arbitrary
		            case 'year': $to += $spread_to * 365; break; // arbitrary
		        }

		        if ($from < 0 && $to < 0 && $from <= $to) {
		        	// dates in the past (-3 to -2 months for instance)
		            $query->where($db->quoteName($dateField) . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' . abs($spread_from) . ' ' . $range_from . ')');
		            $query->where($db->quoteName($dateField) . ' <= DATE_SUB(' . $nowDate . ', INTERVAL ' . abs($spread_to) . ' ' . $range_to . ')');
		        }

		        if ($from < 0 && $to == 0) {
		        	// dates in the past (the last 2 months for instance)
		            $query->where($db->quoteName($dateField) . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' . abs($spread_from) . ' ' . $range_from . ')');
		            $query->where($db->quoteName($dateField) . ' <= ' . $nowDate);
		        }

		        if ($from < 0 && $to > 0) {
		        	// dates in the past and in the future (the last month to the next 2 months for instance)
		            $query->where($db->quoteName($dateField) . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' . abs($spread_from) . ' ' . $range_from . ')');
		            $query->where($db->quoteName($dateField) . ' <= DATE_ADD(' . $nowDate . ', INTERVAL ' . $spread_to . ' ' . $range_to . ')');
		        }

		        if ($from >= 0 && $to >= 0) {
		        	if ($from > $to) {
		        		// past dates
		        	    $query->where($db->quoteName($dateField) . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' . $spread_from . ' ' . $range_from . ')');
		        		if ($to == 0) {
		        		    $query->where($db->quoteName($dateField) . ' <= ' . $nowDate);
		        		} else {
		        		    $query->where($db->quoteName($dateField) . ' <= DATE_SUB(' . $nowDate . ', INTERVAL ' . $spread_to . ' ' . $range_to . ')');
		        		}
		        	} elseif ($from < $to) {
		        		// future dates
		        	    $query->where($db->quoteName($dateField) . ' <= DATE_ADD(' . $nowDate . ', INTERVAL ' . $spread_to . ' ' . $range_to . ')');
		        		if ($from == 0) {
		        		    $query->where($db->quoteName($dateField) . ' >= ' . $nowDate);
		        		} else {
		        		    $query->where($db->quoteName($dateField) . ' >= DATE_ADD(' . $nowDate . ', INTERVAL ' . $spread_from . ' ' . $range_from . ')');
		        		}
		        	} else {
		        		// $from and $to are equal
		        		if ($to == 0) {
		        		    $query->where($db->quoteName($dateField) . ' = ' . $nowDate);
		        		} else {
		        		    $query->where($db->quoteName($dateField) . ' = DATE_ADD(' . $nowDate . ', INTERVAL ' . $spread_from . ' ' . $range_from . ')');
		        		}
		        	}
		        }
			break;

			case 2: // range
				$startDateRange = $db->quote($params->get('start_date_range', $db->getNullDate()));
				$endDateRange = $db->quote($params->get('end_date_range', $db->getNullDate()));
				$query->where('(' . $db->quoteName($dateField) . ' >= ' . $startDateRange . ' AND ' . $db->quoteName($dateField) . ' <= ' . $endDateRange . ')');
			break;
		}

		// category filter

		$categories_array = $params->get('catid', array());

		$array_of_category_values = array_count_values($categories_array);
		if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected therefore no filtering
			// take everything, so no category selection
		} else {
			if (isset($array_of_category_values['auto']) && $array_of_category_values['auto'] > 0) { // 'auto' was selected

				$categories_array = array();

				if ($option === 'com_content') {
					switch($view)
					{
						case 'categories':
							$categories_array[] = $jinput->getInt('id'); // id is the top-level category (can be 0 if 'root' has been selected)
						break;
						case 'category':
							$categories_array[] = $jinput->getInt('id');
						break;
						case 'article':
							$article_id = $jinput->getInt('id');
							$catid = $jinput->getInt('catid');

							if (!$catid) {
								// Get an instance of the generic article model
								$article = BaseDatabaseModel::getInstance('Article', 'ContentModel', array('ignore_request' => true));

								$article->setState('params', $app->getParams());
								$article->setState('filter.published', 1);
								$article->setState('article.id', (int) $article_id);
								$item = $article->getItem();
								$categories_array[] = $item->catid;
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

					$levels = $params->get('levelsubcategories', 1);

					if (!$show_unauthorized_items) {
						$categories_object = Categories::getInstance('Content');
					} else {
						$categories_object = Categories::getInstance('Content', array('access' => false));
					}
					foreach ($categories_array as $category_id) {
						$category_object = $categories_object->get($category_id); // if category unpublished, unset
						if (isset($category_object) && $category_object->hasChildren()) {

							$sub_categories_array = $category_object->getChildren(true); // get all levels recursively
							foreach ($sub_categories_array as $subcategory_object) {
								$condition = ($get_sub_categories == 'all' || ($subcategory_object->level - $category_object->level) <= $levels);
								if ($condition) {
									//$categories_array[] = $subcategory_object->id;
								    $categories_ids_array[$category_id][] = $subcategory_object->id;
								}
							}
						}
					}

					//$categories_array = array_unique($categories_array);
					$final_categories_array = array();
					foreach ($categories_array as $category_id) {
					    $final_categories_array = array_merge($final_categories_array, $categories_ids_array[$category_id]);
					}

					$categories_array = array_unique($final_categories_array);
				}

				$test_type = $params->get('cat_inex', 1) ? 'IN' : 'NOT IN';
				$query->where($db->quoteName('a.catid') . ' ' . $test_type . ' (' . implode(',', $categories_array) . ')');
			}
		}

		// metakeys filter

		$metakeys = array();
		$keys = array_filter(explode(',', trim($params->get('keys', ''), ' ,')));

		// assemble any non-blank word(s)
		foreach ($keys as $key) {
			$metakeys[] = trim($key);
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
			
			//$query->where('('.$concat_string.' LIKE "%'.implode('%" OR '.$concat_string.' LIKE "%', $metakeys).'%")');
			
			$query_meta_array = array();
			foreach ($metakeys as $key) {
			    $query_meta_array[] = $concat_string . ' LIKE ' . $db->quote('%' . $db->escape($key, true) . '%');
			}
			
			$query->where('(' . implode(' OR ', $query_meta_array) . ')');
		}

		// tags filter

		$tags = $params->get('tags', array());
		$tags = array_filter($tags); // remove empty tags (array(1) { [0]=> string(0) "" }) wrongly returned from RL Advanced Module Manager

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

				if (empty($tags) && $params->get('tags_inex', 1)) { // won't return any article if no article has been associated to any tag (when include tags only)
					return array();
				}
			} else if ($params->get('include_tag_children', 0)) { // get tag children

				$tagTreeArray = array();
				$helper_tags = new TagsHelper();

				foreach ($tags as $tag) {
					$helper_tags->getTagTreeArray($tag, $tagTreeArray);
				}

				$tags = array_unique(array_merge($tags, $tagTreeArray));
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

			$query->select('COUNT(' . $db->quoteName('tags.id') . ') AS tags_count');
			$query->join('INNER', $db->quoteName('#__contentitem_tag_map', 'm'), $db->quoteName('m.content_item_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('m.type_alias') . ' = ' . $db->quote('com_content.article'));
			$query->join('INNER', $db->quoteName('#__tags', 'tags'), $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('tags.id'));
			$query->whereIn($db->quoteName('tags.access'), $view_levels);
			$query->where($db->quoteName('tags.published') . ' = 1');

			// keep all items with tags to be handled outside the query (when exclude all)
			if (!$params->get('tags_inex', 1) && $params->get('tags_match', 'any') == 'all') {
				// keep all tags
			} else {
				$test_type = $params->get('tags_inex', 1) ? 'IN' : 'NOT IN';
				$query->where($db->quoteName('tags.id') . ' ' . $test_type . ' (' . $tags_to_match . ')');
			}

			if (!$params->get('tags_inex', 1) && $params->get('tags_match', 'any') == 'all') {
				// handled outside the query
			} else {
				if (!$params->get('tags_inex', 1)) { // EXCLUDE TAGS
					$query->select('tags_per_items.tag_count_per_item');

				    $subquery = $db->getQuery(true);

					// subquery gets all the tags for all items
					$subquery->select($db->quoteName('mm.content_item_id', 'content_id'));
					$subquery->select('COUNT(' . $db->quoteName('tt.id') . ') AS tag_count_per_item');
					$subquery->from($db->quoteName('#__contentitem_tag_map', 'mm'));
					$subquery->join('INNER', $db->quoteName('#__tags', 'tt'), $db->quoteName('mm.tag_id') . ' = ' . $db->quoteName('tt.id'));
					$subquery->where($db->quoteName('tt.access') . ' IN (' . implode(',', $view_levels) . ')'); // DO NOT USE whereIn (or else we need prepared variables)
					$subquery->where($db->quoteName('tt.published') . ' = 1');
					$subquery->where($db->quoteName('mm.type_alias') . ' = ' . $db->quote('com_content.article'));
					$subquery->group($db->quoteName('content_id'));

					$query->join('INNER', '(' . (string) $subquery . ') AS tags_per_items', $db->quoteName('tags_per_items.content_id') . ' = ' . $db->quoteName('a.id'));

					// we keep items that have the same amount of tags before and after removals
					$query->having('COUNT(' . $db->quoteName('tags.id') . ') = ' . $db->quoteName('tags_per_items.tag_count_per_item'));
				} else { // INCLUDE TAGS
					if ($params->get('tags_match', 'any') == 'all') {
						$query->having('COUNT(' . $db->quoteName('tags.id') . ') = ' . count($tags));
					}
				}
			}

			$query->group($db->quoteName('a.id'));
		}

		// custom field filters

		$customfield_filters_arrays = array();

		$customfield_filters = $params->get('customfieldsfilter'); // string (if default), array or object

		if (!empty($customfield_filters) && !is_string($customfield_filters)) {

		    foreach ($customfield_filters as $customfield_filter) {

		        $customfield_filter = (array)$customfield_filter;

		        if ($customfield_filter['field'] !== 'none') {

		            $values = explode(',', $customfield_filter['values']);
		            foreach ($values as $key => $value) {
		                $value = trim($value);
		                if (empty($value)) {
		                    unset($values[$key]);
		                }
		            }

		            if (!empty($values)) {
		                $customfield_filters_arrays[] = array('id' => $customfield_filter['field'], 'values' => $values, 'inex' => $customfield_filter['inex']);
		            }
		        }
		    }
		}

		if (!empty($customfield_filters_arrays)) {

		    $article_id_arrays_from_cfields = array();

		    foreach ($customfield_filters_arrays as $customfield_filter) {

	            $subQuery = $db->getQuery(true);

	            $subQuery->select('DISTINCT ' . $db->quoteName('cfv.item_id')); // no unique results when joining with categories
	            $subQuery->from($db->quoteName('#__fields_values', 'cfv'));
	            $subQuery->join('LEFT', $db->quoteName('#__fields', 'f'), $db->quoteName('f.id') . ' = ' . $db->quoteName('cfv.field_id'));
	            $subQuery->where('(' . $db->quoteName('f.context') . ' IS NULL OR ' . $db->quoteName('f.context') . ' = ' . $db->quote('com_content.article') . ')');
	            $subQuery->where('(' . $db->quoteName('f.state') . ' IS NULL OR ' . $db->quoteName('f.state') . ' = 1)');
	            $subQuery->where('(' . $db->quoteName('f.access') . ' IS NULL OR ' . $db->quoteName('f.access') . ' IN (' . implode(',', $view_levels) . '))');
	            $subQuery->where($db->quoteName('cfv.field_id').' = :fieldId');
	            $subQuery->bind(':fieldId', $customfield_filter['id'], ParameterType::INTEGER);

	            if ($customfield_filter['inex']) {
	               $subQuery->where($db->quoteName('cfv.value') . " = '" . implode("' OR " . $db->quoteName('cfv.value') . " = '", $customfield_filter['values']) . "'");
	            } else {
	                $subQuery->where($db->quoteName('cfv.value') . " <> '" . implode("' AND " . $db->quoteName('cfv.value') . " <> '", $customfield_filter['values']) . "'");
	            }

	            if ($params->get('filter_lang', 1) && Multilanguage::isEnabled()) {
	                $subQuery->where('(' . $db->quoteName('f.language') . ' IS NULL OR ' . $db->quoteName('f.language') . ' IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . '))');
	            }

	            $db->setQuery($subQuery);

	            try {
	                $article_id_arrays_from_cfields[] = $db->loadColumn();
	            } catch (ExecutionFailureException $e) {
	                Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
	            }
		    }

	        if (!empty($article_id_arrays_from_cfields)) {

	            // keep only the ids found in all the arrays
	            if (count($article_id_arrays_from_cfields) > 1) {
	                $article_ids = call_user_func_array('array_intersect', $article_id_arrays_from_cfields);
	            } else {
	                $article_ids = $article_id_arrays_from_cfields[0];
	            }

	            if (!empty($article_ids)) {
	                $article_ids = ArrayHelper::toInteger($article_ids);
	                $query->whereIn($db->quoteName('a.id'), $article_ids); // include all articles that have custom field value(s) that correspond to the custom field value
	            } else {
	                $query->where($db->quoteName('a.id') . ' = 0'); // no article having all values selected
	            }
	        }
		}

		// user filter

		$include = $params->get('author_inex', 1);
		$authors_array = $params->get('created_by', array());

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
		
		$where_state = 'a.state = 1';
		$where_createdby = '';

		$array_of_authors_values = array_count_values($authors_array);
		if (isset($array_of_authors_values['all']) && $array_of_authors_values['all'] > 0) { // 'all' was selected
		    $test_type = $include ? '>' : '<';
		    $where_createdby = 'a.created_by ' . $test_type . ' 0'; // necessary so that the OR match returns good results
		    if ($params->get('allow_edit', 0) && (int)$user->get('id') > 0) {
				if ($user->authorise('core.edit', 'com_content')) {
					// logged user can see everyone's unpublished articles
				    $where_state = 'a.state IN (0, 1)';
				} else {
					// logged user can see his own unpublished articles only
				    $where_state = '(a.state = 1) OR (a.state = 0 AND a.created_by = ' . (int) $user->get('id') . ')';
				}
			}
		} else if (isset($array_of_authors_values['realauto']) && $array_of_authors_values['realauto'] > 0) { // 'realauto' was selected: check if author on page, if so, select it

			$found = false;
			if ($option === 'com_content' && $view === 'article') {
				$temp = $jinput->getString('id');
				$temp = explode(':', $temp);
				if ($temp[0]) {
					
					$subquery = $db->getQuery(true);

					$subquery->select($db->quoteName('created_by'));
					$subquery->from($db->quoteName('#__content'));
					$subquery->where($db->quoteName('id') . ' = :articleId');
					$subquery->bind(':articleId', $temp[0], ParameterType::INTEGER);

					$db->setQuery($subquery);
					
					try {
						$result = $db->loadResult();
						if ($result) {
							$found = true;
							$test_type = $include ? '=' : '<>';
							$where_createdby = 'a.created_by' . $test_type . $result;
							
							if ($include && $params->get('allow_edit', 0) && (int)$user->get('id') === $result) {
							    $where_state = 'a.state IN (0, 1)'; // show all articles for the logged author, published or not
							}
						}
					} catch (ExecutionFailureException $e) {
						$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
						return null;
					}
				}
			}
			
			if (!$found) {
				return array();
			}

		} else if (isset($array_of_authors_values['auto']) && $array_of_authors_values['auto'] > 0) { // 'auto' was selected: equivalent to check if the author is logged in
			$test_type = $include ? '=' : '<>';
			$where_createdby = 'a.created_by ' .$test_type.' '.(int) $user->get('id');
			if ($include && $params->get('allow_edit', 0) && (int)$user->get('id') > 0) {
			    $where_state = 'a.state IN (0, 1)'; // show all articles for the logged author, published or not
			}
		} else {
			$authors = implode(',', $authors_array);
			if ($authors) {
				$test_type = $include ? 'IN' : 'NOT IN';
				$where_createdby = 'a.created_by '.$test_type.' ('.$authors.')';
			}

			if ($params->get('allow_edit', 0) && (int)$user->get('id') > 0) {
				if ($user->authorise('core.edit', 'com_content')) {
					// logged user can see everyone's unpublished articles
				    $where_state = 'a.state IN (0, 1)';
				} else {
				    if (($include && in_array($user->get('id'), $authors_array)) || (!$include && !in_array($user->get('id'), $authors_array))) {
						// logged user can see his own unpublished articles only
						$where_state = '(a.state = 1) OR (a.state = 0 AND a.created_by = ' . (int) $user->get('id') . ')';
					}
				}
			}
		}

		// author alias filter
		
		$include = $params->get('author_alias_inex', 1);
		$authors_array = $params->get('created_by_alias', array());
		$author_match = (int)$params->get('author_match', 0);

		$where_createdbyalias = '';
		
		if ($author_match > 0 && count($authors_array) > 0) {
		
    		$array_of_authors_values = array_count_values($authors_array);
    		
    		if (isset($array_of_authors_values['all']) && $array_of_authors_values['all'] > 0) { // 'all' was selected
    		    
    		    if ($include) {
    		        $where_createdbyalias = 'a.created_by_alias != ' . $db->quote('');
    		    } else {
    		        $where_createdbyalias = 'a.created_by_alias = ' . $db->quote('');
    		    }
    		
    		} else if (isset($array_of_authors_values['auto']) && $array_of_authors_values['auto'] > 0) { // 'auto' was selected: check if author alias on page, if so, select it
    		
    		    $found = false;
    		    if ($option === 'com_content' && $view === 'article') {
    		        $temp = $jinput->getString('id');
    		        $temp = explode(':', $temp);
    		        if ($temp[0]) {
    		            
    		            $subquery = $db->getQuery(true);

    		            $subquery->select($db->quoteName('created_by_alias'));
    		            $subquery->from($db->quoteName('#__content'));
    		            $subquery->where($db->quoteName('id') . ' = :articleId');
    		            $subquery->bind(':articleId', $temp[0], ParameterType::INTEGER);
    		            
    		            $db->setQuery($subquery);
    		            
    		            try {
    		                $result = $db->loadResult();
    		                if ($result) {
    		                    $found = true;
    		                    $test_type = $include ? '=' : '!=';
    		                    $where_createdbyalias = 'a.created_by_alias' . $test_type . $db->quote($result);
    		                    if (!$include) {
    		                        $where_createdbyalias .= ' AND a.created_by_alias != ' . $db->quote('');
    		                    }
    		                }
    		            } catch (ExecutionFailureException $e) {
    		                $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
    		                return null;
    		            }
    		        }
        		}
    		    
    		    if (!$found) {
    		        return array();
    		    }
    		
    		} else {
    		    $quoted_array = array();
    		    foreach ($authors_array as $author) {
    		        $quoted_array[] = $db->quote($author);
    		    }
    		    
    		    $authors = implode(',', $quoted_array);
    		    if ($authors) {
    		        $test_type = $include ? 'IN' : 'NOT IN';
    		        $where_createdbyalias = 'a.created_by_alias '.$test_type.' ('.$authors.')';
    		        if (!$include) {
    		            $where_createdbyalias .= ' AND a.created_by_alias != ' . $db->quote('');
    		        }
    		    }
    		}
		}

		$query->where($where_state);

		if ($author_match === 2 && !empty($where_createdby) && !empty($where_createdbyalias)) {
		    $query->where('(' . $where_createdby . ' OR ' . '(' . $where_createdbyalias . ')' . ')');
		} else {
		    if ($where_createdby) {
		        $query->where($where_createdby);
		    }
		    if ($where_createdbyalias) {
		        $query->where('(' . $where_createdbyalias . ')');
		    }
		}

		// language filter

		if ($params->get('filter_lang', 1) && Multilanguage::isEnabled()) {
		    $query->whereIn($db->quoteName('a.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
		}

		$ordering = array();

		// author order

		switch ($params->get('author_order', ''))
		{
		    case 'selec_asc': $ordering[] = $db->quoteName('author') . ' ASC'; break;
		    case 'selec_dsc': $ordering[] = $db->quoteName('author') . ' DESC'; break;
		}

		// featured switch
		
		// NOTE cannot use binding or else bind all $nowDate and do $nowDate = Factory::getDate()->toSql();

		$featured = false;
		
		$query->select(
		    [
		        $db->quoteName('fp.featured_up'),
		        $db->quoteName('fp.featured_down'),
		    ]
		);
		
		switch ($params->get('show_f', 3))
		{
		    case '0': // hide
		        // featured articles out of range are no longer considered featured
		        $query->extendWhere(
    		        'AND',
    		        [
        		        $db->quoteName('a.featured') . ' = 0',
        		        '(' . $db->quoteName('fp.featured_up') . ' IS NOT NULL AND ' . $db->quoteName('fp.featured_up') . ' >= ' . $nowDate . ')',
        		        '(' . $db->quoteName('fp.featured_down') . ' IS NOT NULL AND ' . $db->quoteName('fp.featured_down') . ' <= ' . $nowDate . ')',
    		        ],
    		        'OR'
    		    );
		        
		        $query->join('LEFT', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'));
		        
		        break;
		        
			case '1': // only
				$featured = true;
				
				$query->where(
				    [
				        $db->quoteName('a.featured') . ' = 1',
				        '(' . $db->quoteName('fp.featured_up') . ' IS NULL OR ' . $db->quoteName('fp.featured_up') . ' <= ' . $nowDate . ')',
				        '(' . $db->quoteName('fp.featured_down') . ' IS NULL OR ' . $db->quoteName('fp.featured_down') . ' >= ' . $nowDate . ')',
				    ]
				);

				$query->join('INNER', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'));

				break;
				
			case '2': // first the featured ones
				$featured = true;
				
				$query->select('CASE WHEN ' . $db->quoteName('a.featured') . ' = 1 AND (' . $db->quoteName('fp.featured_up') . ' IS NULL OR ' . $db->quoteName('fp.featured_up') . ' <= ' . $nowDate . ') AND (' . $db->quoteName('fp.featured_down') . ' IS NULL OR ' . $db->quoteName('fp.featured_down') . ' >= ' . $nowDate . ') THEN 1 ELSE 0 END AS ' . $db->quoteName('featuredinrange'));
				
			    $query->join('LEFT', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'));
			    
			    $ordering[] = $db->quoteName('featuredinrange') . ' DESC';
			    
				break;
				
			default: // no discrimination between featured/unfeatured items (if featured but outside the range featured up/down, the article is considered 'unfeatured')
				$featured = true;
				
				$query->join('LEFT', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'));
		}

		// category order
		
		switch ($params->get('cat_order', ''))
		{
		    case 'o_asc': $ordering[] = $db->quoteName('c.lft') . ' ASC'; break;
		    case 'o_dsc': $ordering[] = $db->quoteName('c.lft') . ' DESC'; break;
		    case 't_asc': $ordering[] = $db->quoteName('c.title') . ' ASC'; break;
		    case 't_dsc': $ordering[] = $db->quoteName('c.title') . ' DESC'; break;
		}

		// general ordering

		switch ($params->get('order'))
		{
			case 'o_asc': 
				if ($featured) { 
					$ordering[] = 'CASE WHEN ' . $db->quoteName('a.featured') . ' = 1 THEN ' . $db->quoteName('fp.ordering') . ' ELSE ' . $db->quoteName('a.ordering') . ' END ASC'; 
				} else { 
					$ordering[] = $db->quoteName('a.ordering') . ' ASC'; 
				} 
				break;
			case 'o_dsc': 
			    if ($featured) { 
			        $ordering[] = 'CASE WHEN ' . $db->quoteName('a.featured') . ' = 1 THEN ' . $db->quoteName('fp.ordering') . ' ELSE ' . $db->quoteName('a.ordering') . ' END DESC'; 
			    } else { 
			        $ordering[] = $db->quoteName('a.ordering') . ' DESC'; 
			    } 
			    break;
			case 'p_asc': $ordering[] = $db->quoteName('a.publish_up') . ' ASC'; break;
			case 'p_dsc': $ordering[] = $db->quoteName('a.publish_up') . ' DESC'; break;
			case 'f_asc': $ordering[] = 'CASE WHEN ' . $db->quoteName('a.publish_down') . ' IS NULL THEN ' . $db->quoteName('a.publish_up') . ' ELSE ' . $db->quoteName('a.publish_down') . ' END ASC'; break;
			case 'f_dsc': $ordering[] = 'CASE WHEN ' . $db->quoteName('a.publish_down') . ' IS NULL THEN ' . $db->quoteName('a.publish_up') . ' ELSE ' . $db->quoteName('a.publish_down') . ' END DESC'; break;
			case 'm_asc': $ordering[] = $db->quoteName('a.modified') . ' ASC'; $ordering[] = $db->quoteName('a.created') . ' ASC'; break;
			case 'm_dsc': $ordering[] = $db->quoteName('a.modified') . ' DESC'; $ordering[] = $db->quoteName('a.created') . ' DESC'; break;
			case 'c_asc': $ordering[] = $db->quoteName('a.created') . ' ASC'; break;
			case 'c_dsc': $ordering[] = $db->quoteName('a.created') . ' DESC'; break;
			case 'mc_asc': $ordering[] = 'CASE WHEN ' . $db->quoteName('a.modified') . ' IS NULL THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.modified') . ' END ASC'; break;
			case 'mc_dsc': $ordering[] = 'CASE WHEN ' . $db->quoteName('a.modified') . ' IS NULL THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.modified') . ' END DESC'; break;
			case 'random': $ordering[] = $query->rand(); break;
			case 'hit': $ordering[] = $db->quoteName('a.hits') . ' DESC'; break;
			case 'title_asc': $ordering[] = $db->quoteName('a.title') . ' ASC'; break;
			case 'title_dsc': $ordering[] = $db->quoteName('a.title') . ' DESC'; break;
			case 'manual':
				$articles_to_include = array_filter(explode(',', trim($params->get('in', ''), ' ,')));
				if (!empty($articles_to_include)) {
					$manual_ordering = 'CASE a.id';
					foreach ($articles_to_include as $key => $id) {
					    $manual_ordering .= ' WHEN ' . $id . ' THEN ' . $key;
					}
					$ordering[] = $manual_ordering . ' ELSE 999 END, a.id'; // 'FIELD(a.id, ' . $articles_to_include . ')' is MySQL specific
				}
		}

		if (count($ordering) > 0) {
			$query->order($ordering);
		}

		// include only

		$articles_to_include = array_filter(explode(',', trim($params->get('in', ''), ' ,')));
		if (!empty($articles_to_include)) {
		    $articles_to_include = ArrayHelper::toInteger($articles_to_include);
		    $query->whereIn($db->quoteName('a.id'), $articles_to_include);
		}

		// exclude

		$articles_to_exclude = array_filter(explode(',', trim($params->get('ex', ''), ' ,')));

		$item_on_page_id = '';
		if ($params->get('ex_current_item', 0) && $option === 'com_content' && $view === 'article') {
			$temp = $jinput->getString('id');
			$temp = explode(':', $temp);
			$item_on_page_id = $temp[0];
		}

		if ($item_on_page_id) { // do not show the current article in the list
			$articles_to_exclude[] = $item_on_page_id;
		}

		if (!empty($articles_to_exclude)) {
		    $articles_to_exclude = ArrayHelper::toInteger($articles_to_exclude);
		    $query->whereNotIn($db->quoteName('a.id'), $articles_to_exclude);
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

		$image_types = array('image', 'imageintro', 'imagefull', 'allimagesasc', 'allimagesdesc', 'categoryimage');

		$show_image = false;

		if (in_array($head_type, $image_types)) {

			$show_image = true;

			$crop_picture = ($params->get('crop_pic', 0) && $params->get('create_thumb', 1));

			$create_highres_images = $params->get('create_highres', false);
			$lazyload = $params->get('lazyload', false);

			$allow_remote = $params->get('allow_remote', true);

			$thumbnail_mime_type = $params->get('thumb_mime_type', '');

			$maintain_height = $params->get('maintain_height', 0);
			$head_width = $params->get('head_w', 64);
			$head_height = $params->get('head_h', 64);
			$border_width = $params->get('border_w', 0);

			$head_width = $head_width - $border_width * 2;
			$head_height = $head_height - $border_width * 2;

			$filter = $params->get('filter', 'none');

			$quality_jpg = $params->get('quality_jpg', 75);
			$quality_png = $params->get('quality_png', 3);
			$quality_webp = $params->get('quality_webp', 80);
			$quality_avif = $params->get('quality_avif', 80);

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

			if ($quality_avif > 100) {
			    $quality_avif = 100;
			}
			if ($quality_avif < 0) {
			    $quality_avif = 0;
			}

			$image_qualities = array('jpg' => $quality_jpg, 'png' => $quality_png, 'webp' => $quality_webp, 'avif' => $quality_avif);

			$clear_cache = Helper::IsClearPictureCache($params);

			$subdirectory = 'thumbnails/lne';
			if ($params->get('thumb_path', 'cache') == 'cache') {
				$subdirectory = 'mod_latestnewsenhanced';
			}
			$tmp_path = SYWCache::getTmpPath($params->get('thumb_path', 'cache'), $subdirectory);

			$default_picture = trim($params->get('default_pic', ''));
			$category_picture_as_default = $params->get('default_cat_pic', 0);

			if ($clear_cache) {
				Helper::clearThumbnails($module->id, $tmp_path);

				SYWVersion::refreshMediaVersion('mod_latestnewsenhanced_' . $module->id);
			}
		}

		$text_type = $params->get('text', 'intro');
		$letter_count = trim($params->get('l_count', ''));
		$truncate_last_word = $params->get('trunc_l_w', 0);
		$keep_tags = trim($params->get('keep_tags', ''));
		$strip_tags = $params->get('strip_tags', 1);
		$always_show_readmore = $params->get('readmore_always_show', true);
		$trigger_OnContentPrepare = $params->get('trigger_events', false);
		$force_one_line = $params->get('force_one_line', false);
		$title_letter_count = trim($params->get('letter_count_title', ''));
		$title_truncate_last_word = $params->get('trunc_l_w_title', 0);
		//$show_date = $params->get('show_d', 'date');

		$link_to = $params->get('link_to', 'item');
		switch ($params->get('link_target', 'default')) {
			case 'same': $link_target = 0; break;
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
			
			// featured: when featured but outside the date range, set to featured to false
			
			if ($item->featured) {			    
			    if ((!empty($item->featured_up) && strtotime($item->featured_up) >= strtotime('now'))
			        || (!empty($item->featured_down) && strtotime($item->featured_down) <= strtotime('now'))) {
			        $item->featured = false;
			    }
			}

			// category link

			if (!$show_unauthorized_items || in_array($item->category_access, $authorised)) {
				$item->catlink = Route::_(RouteHelper::getCategoryRoute($item->cat_slug, $item->language));
				$item->category_authorized = true;
			} else {
				$catlink = new Uri(Route::_('index.php?option=com_users&view=login', false));

				$category = Categories::getInstance('Content', array('access' => false))->get($item->catid);
				$catlink->setVar('return', base64_encode(RouteHelper::getCategoryRoute($category, $item->language)));

				$item->catlink = $catlink;
				$item->category_authorized = false;
			}

			// item edit link

			if ($params->get('allow_edit', 0)) {
				$edit = false;
				if ($user->authorise('core.edit', 'com_content.article.' . $item->id)) {
					$edit = true;
				} else if ($user->get('id') && $user->authorise('core.edit.own', 'com_content.article.' . $item->id)) {
					if ($user->get('id') == $item->created_by) {
						$edit = true;
					}
				}

				if ($edit) {
					$item->link_edit = RouteHelper::getArticleRoute($item->slug, $item->cat_slug, $item->language);
					$item->link_edit .= '&task=article.edit&a_id=' . $item->id . '&return=' . base64_encode(Uri::getInstance());
					$item->link_edit = Route::_($item->link_edit);
				}
			}

			// item link

			$item->link = '';

			if ($item->state == 1) {

				//$item->linktarget = 0;
				$item->isinternal = true;

				$item->linktitle = $item->title;

				$link_string = RouteHelper::getArticleRoute($item->slug, $item->cat_slug, $item->language);

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
						$item->linktarget = 0;
					}

					// strange: no Itemid search in ContentHelperRoute::getArticleRoute starting in Joomla 3.7

					$item->link = Route::_($link_string);
					$item->authorized = true; // we know that user has the privilege to view the article
				} else {

					$link = new Uri(Route::_('index.php?option=com_users&view=login', false));

					//$link->setVar('return', base64_encode(JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->cat_slug, $item->language), false)));
					// returns /MyWork_3_4/index.php/latest-news/13-ipsum/9-phosfluorescently-engage-worldwide-methodologies-with-web-enabled-technology-5
					// does not work

					if ($item->category_authorized) {
						$link->setVar('return', base64_encode($link_string));
					} else {

						$link_string = self::getUnauthorizedArticleRoute($item->slug, $item->cat_slug, $item->language);

						if ($forced_itemid > 0) {
							if (strpos($link_string, 'Itemid') === false) {
								$link_string .= '&Itemid=' . $forced_itemid;
							} else {
								$link_string = preg_replace('#Itemid=([0-9]*)#', 'Itemid=' . $forced_itemid, $link_string);
							}
						}

						$link->setVar('return', base64_encode($link_string));
					}
					// works
					// returns 'index.php/latest-news/13-ipsum/9-phosfluorescently-engage-worldwide-methodologies-with-web-enabled-technology-5';

					$item->link = $link;
					$item->linktarget = 0; // cannot open in modal window in this case - too many cases where it might fail bacause the login form	opens first
					$item->authorized = false;
				}
			}

			// rating (to avoid call to rating plugin, use $item->vote)

			if (isset($item->rating)) {
				$item->vote = $item->rating; // to avoid calls to rating plugin
				$item->vote_count = $item->rating_count;
				unset($item->rating);
				unset($item->rating_count);
			} else {
				$item->vote = '';
				$item->vote_count = 0;
			}

			// tags

			if (!isset($item->tags)) {
				$item->tags = self::getItemTags($item->id);
			}

			// thumbnail image creation

			$item->imagetag = '';
			$item->error = array();

			if ($show_image) {

				// Convert the images field to an array
				$registry = new Registry();
				$registry->loadString($item->images);
				$images_array = $registry->toArray();

				$filename = '';
				$image_width = 0;
				$image_height = 0;

				// note: original images are not cached, therefore looking thru article content will be inefficient

				if (!$clear_cache && $params->get('create_thumb', 1)) {
				    $thumbnail_src = Helper::thumbnailExists($module->id, $item->id, $tmp_path, $create_highres_images);
					if ($thumbnail_src !== false) {
						$filename = $thumbnail_src; // found a corresponding thumbnail
					}
				}

				if (empty($filename)) {

					$imagesrc = '';

					if ($head_type == 'imageintro') {

						if ($images_array) {
							$imagesrc = trim($images_array['image_intro']);
						}

					} else if ($head_type == 'imagefull') {

						if ($images_array) {
							$imagesrc = trim($images_array['image_fulltext']);
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

						// if images not found, look into intro and full article
						if (empty($imagesrc)) {

							if ($images_array) {
								$imagesrc = trim($images_array['image_intro']);

								if (empty($imagesrc)) {
									$imagesrc = trim($images_array['image_fulltext']);
								}
							}
						}

					} else if ($head_type == 'allimagesdesc') {

						// look into image intro and full first
						if ($images_array) {
							$imagesrc = trim($images_array['image_intro']);

							if (empty($imagesrc)) {
								$imagesrc = trim($images_array['image_fulltext']);
							}
						}

						// if image full article not found, look into the article
						if (empty($imagesrc)) {

							if (isset($item->fulltext))	{
								$imagesrc = Helper::getImageSrcFromContent($item->introtext, $item->fulltext);
							} else {
								$imagesrc = Helper::getImageSrcFromContent($item->introtext);
							}
						}
					} else if ($head_type == 'categoryimage') {
					    
					    // get the image from the params
					    $category_params = json_decode($item->category_params);
					    if (isset($category_params->image)) {
					        $imagesrc = $category_params->image;
					    }
					}

					if (empty($imagesrc) && $category_picture_as_default) {
					    // get the image from the category params
					    $category_params = json_decode($item->category_params);
					    if (isset($category_params->image)) {
					        $imagesrc = $category_params->image;
					    }
					}

					// last resort, use default image if it exists
					$used_default_image = false;
					if (empty($imagesrc)) {
						if ($default_picture) {
							$imagesrc = $default_picture;
							$used_default_image = true;
						} else {
						    $imagesrc = '';
						}
					}

					if ($imagesrc) { // found an image

					    $image_object = HTMLHelper::cleanImageURL($imagesrc);
					    $imagesrc = $image_object->url;

						if (!$params->get('create_thumb', 1) || $head_width <= 0 || $head_height <= 0) { // no thumbnails are created, use the original image
					        // Use the original
							$filename = $imagesrc;

					        $image_width = $image_object->attributes['width'];
					        $image_height = $image_object->attributes['height'];

					    } else {
					        // Create the thumbnail
					        $result_array = Helper::getImageFromSrc($module->id, $item->id, $imagesrc, $tmp_path, $head_width, $head_height, $crop_picture, $image_qualities, $filter, $create_highres_images, $allow_remote, $thumbnail_mime_type);

					        if (isset($result_array['url']) && $result_array['url']) {
    							$filename = $result_array['url'];
    						}

    						if (isset($result_array['error']) && $result_array['error']) {

    						    $item->error[] = $result_array['error'];

    							// if error for the file found, try and use the default image instead
    						    if (!$used_default_image && $default_picture) { // if the default image was the one chosen, no use to retry

									$default_image_object = HTMLHelper::cleanImageURL($default_picture);

									$result_array = Helper::getImageFromSrc($module->id, $item->id, $default_image_object->url, $tmp_path, $head_width, $head_height, $crop_picture, $image_qualities, $filter, $create_highres_images, $allow_remote, $thumbnail_mime_type);

									if (isset($result_array['url']) && $result_array['url']) {
									    $filename = $result_array['url'];
									}

									if (isset($result_array['error']) && $result_array['error']) {
									    $item->error[] = $result_array['error'];
									}
    							}
    						}
					    }
					}
				}

				if ($filename) {

					$img_attributes = array();
					
					if ($image_width <= 0 || $image_height <= 0) {
					    if ($crop_picture) {
					        if ($head_width > 0 && $head_height > 0) {
    					        $image_width = $head_width;
    					        $image_height = $head_height;
					        }
					    } else {
					        try {
					            $image_properties = Image::getImageFileProperties($filename);
					            $image_width = $image_properties->width;
					            $image_height = $image_properties->height;
					        } catch (\Exception $e) {
					            $image_width = 0;
					            $image_height = 0;
					        }
					    }
					}

					if ($image_width > 0 && $image_height > 0) {
					    $img_attributes = array('width' => $image_width, 'height' => $image_height);
					}

					$extra_attributes = trim($params->get('image_attributes', ''));
					if ($extra_attributes) {
						$xml = new \SimpleXMLElement('<element ' . $extra_attributes . ' />');
						foreach ($xml->attributes() as $attribute_name => $attribute_value) {
							$img_attributes[$attribute_name] = $attribute_value;
						}
					}

					$item->imagetag = SYWUtilities::getImageElement($filename, $item->title, $img_attributes, $lazyload, $create_highres_images, null, true, SYWVersion::getMediaVersion('mod_latestnewsenhanced_' . $module->id));
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

			// calendar shows a custom field of type 'calendar'

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
				default: $use_intro = true;
			}

			if ($use_intro) { // use intro text
				$item->text = $item->introtext;

				if ($text_type === 'full') {
				    $item->text .= ($item->text ? ' ' : '') . $item->fulltext; 
				}

				if ($item->text) {
					if ($trigger_OnContentPrepare) { // will trigger events from plugins
						PluginHelper::importPlugin('content');
						Factory::getApplication()->triggerEvent('onContentPrepare', array('com_content.article', &$item, &$item->params, 0));
					}
					$item->text = SYWText::getText($item->text.$beacon, 'html', $number_of_letters, $strip_tags, $keep_tags, true, $truncate_last_word);
				}
			} else { // use meta text
				$item->text = SYWText::getText($item->metadesc.$beacon, 'txt', $number_of_letters, false, '', true, $truncate_last_word);
			}

			// the text won't be cropped if the ^ character is still present after processing (hopefully no ^ at the end of the text)
			$item->cropped = true;
			if (!$always_show_readmore) {
				$text_length = strlen($item->text);
				$item->text = rtrim($item->text, "^");
				if (strlen($item->text) < $text_length) { // The beacon was present, therefore we had the full content
				    // Now, the text can be the full text
				    if ($text_type === 'full' || !$item->fulltexthascontent) {
				        $item->cropped = false;
				    }
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

	// rewrite of article route in the case where the category is not authorized
	// Joomla should have handled the category node like in the category route code

	protected static function getUnauthorizedArticleRoute($id, $catid = 0, $language = 0)
	{
		$needles = array(
				'article'  => array((int) $id)
		);

		// Create the link
		$link = 'index.php?option=com_content&view=article&id=' . $id;

		if ((int) $catid > 1)
		{
			$categories = Categories::getInstance('Content', array('access' => false)); // important!
			$category   = $categories->get((int) $catid);

			if ($category)
			{
				$needles['category']   = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid=' . $catid;
			}
		}

		if ($language && $language != "*" && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
			$needles['language'] = $language;
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	protected static $lookup = array();

	protected static function _findItem($needles = null)
	{
		$app      = Factory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();

			$component  = ComponentHelper::getComponent('com_content');

			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}

					if (isset($item->query['id']))
					{
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();

		if ($active
				&& $active->component == 'com_content'
				&& ($language == '*' || in_array($active->language, array('*', $language)) || !Multilanguage::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}

	protected static function getItemTags($id)
	{
		$helper_tags = new TagsHelper();

		$tags_array = $helper_tags->getItemTags('com_content.article', $id); // array of tag objects
		if (count($tags_array) > 0) {
			return $tags_array;
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

}
?>