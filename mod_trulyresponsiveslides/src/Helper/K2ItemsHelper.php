<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Utilities\ArrayHelper;

require_once (JPATH_SITE.'/components/com_k2/models/itemlist.php');

class K2ItemsHelper
{
	static function getItems($params, $module)
	{
		$db = Factory::getDbo();
		$app = Factory::getApplication();
		
		$user = $app->getIdentity();
		$view_levels = $user->getAuthorisedViewLevels();

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

		$query->select($db->quoteName(array('a.id', 'a.catid', 'a.title', 'a.alias', 'a.introtext', 'a.fulltext', 'a.params', 'a.metadata', 'a.metakey', 'a.metadesc', 'a.access', 'a.hits', 'a.featured', 'a.language', 'a.image_caption')));
		$query->select($db->quoteName('c.id', 'cat_id')); // keep for b/c

		$query->select($db->quoteName(array('a.checked_out', 'a.checked_out_time', 'a.created', 'a.created_by', 'a.created_by_alias')));
		
		// Use created if modified is 0
		$query->select('CASE WHEN ' . $query->isNullDatetime('a.modified') . ' THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.modified') . ' END AS ' . $db->quoteName('modified'));
		$query->select($db->quoteName('a.modified_by'));

		// Use created if publish_up is 0
		$query->select('CASE WHEN ' . $query->isNullDatetime('a.publish_up') . ' THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.publish_up') . ' END AS  ' . $db->quoteName('publish_up'));
		$query->select($db->quoteName('a.publish_down'));

		$query->select($subquery1);
		$query->select($subquery2);

		$query->from($db->quoteName('#__k2_items', 'a'));

		// join over the categories
		$query->select($db->quoteName(array('c.name', 'c.access', 'c.alias'), array('category_title', 'category_access', 'category_alias'))); // TODO check: was cat_alias originally: error ?
		$query->join('LEFT', $db->quoteName('#__k2_categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'));

		// access filter

		$query->whereIn($db->quoteName('a.access'), $view_levels);
		$query->whereIn($db->quoteName('c.access'), $view_levels);

		// publishing

		$nowDate = $db->quote(Factory::getDate()->toSql());

		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.trash') . ' = 0');

		$query->where('(' . $query->isNullDatetime('a.publish_up') . ' OR ' . $db->quoteName('a.publish_up') . ' <= ' . $nowDate . ')');
		$query->where('(' . $query->isNullDatetime('a.publish_down') . ' OR ' . $db->quoteName('a.publish_down') . ' >= ' . $nowDate . ')');

		$query->where($db->quoteName('c.published') . ' = 1');
		$query->where($db->quoteName('c.trash') . ' = 0');

		// category filter

		$categories_array = $params->get('k2catid', array());

		$array_of_category_values = array_count_values($categories_array);
		if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
			// take everything, so no category selection
		} else {
			// sub-category inclusion
			$get_sub_categories = $params->get('includesubcategories', 'no');
			if ($get_sub_categories != 'no') {
				$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
				$sub_categories_array = array();
				if ($get_sub_categories == 'all') {
					$sub_categories_array = $itemListModel->getCategoryTree($categories_array);
				} else {
					foreach ($categories_array as $category) {
						$sub_categories_rows = $itemListModel->getCategoryFirstChildren($category);
						foreach ($sub_categories_rows as $sub_categories_row) {
							$sub_categories_array[] = $sub_categories_row->id;
						}
					}
				}
				foreach ($sub_categories_array as $subcategory) {
					$categories_array[] = $subcategory;
				}
				$categories_array = array_unique($categories_array);
			}

			if (!empty($categories_array)) {
				$query->whereIn($db->quoteName('a.catid'), $categories_array);
			}
		}

		// tags filter

		$tags = $params->get('k2tags', array());

		if (!empty($tags)) {

			// if all selected, get all available tags
			$array_of_tag_values = array_count_values($tags);
			if (isset($array_of_tag_values['all']) && $array_of_tag_values['all'] > 0) { // 'all' was selected

				// get all tags

				$subQuery = $db->getQuery(true);

				$subQuery->select($db->quoteName('id'));
				$subQuery->from($db->quoteName('#__k2_tags'));
				$subQuery->where($db->quoteName('published') . ' = 1');

				$db->setQuery($subQuery);

				try {
					$tags = $db->loadColumn();
				} catch (ExecutionFailureException $e) {
					$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
					return null;
				}

				if (empty($tags)) { // won't return any k2 item if no k2 item has been associated to any tag (when include tags only)
					return array();
				}
			}
		}

		if (!empty($tags)) {
			
		    $query->select('COUNT(' . $db->quoteName('tags.id') . ') AS tags_count');
		    $query->join('LEFT', $db->quoteName('#__k2_tags_xref', 'tags_xref'), $db->quoteName('tags_xref.itemID') . ' = ' . $db->quoteName('a.id'));
		    $query->join('LEFT', $db->quoteName('#__k2_tags', 'tags'), $db->quoteName('tags_xref.tagID') . ' = ' . $db->quoteName('tags.id'));
		    $query->whereIn($db->quoteName('tags.id'), $tags);
		    // no access in database table
		    $query->where($db->quoteName('tags.published') . ' = 1');

			if ($params->get('tags_match', 'any') == 'all') {
				$query->having('COUNT(' . $db->quoteName('tags.id') . ') = ' . count($tags));
			}

			$query->group($db->quoteName('a.id'));
		}

		// language filter

		if ($params->get('filter_lang', 1) && Multilanguage::isEnabled()) {
			$query->whereIn($db->quoteName('a.language'), [$db->quote(Factory::getLanguage()->getTag()), $db->quote('*')]);
		}

		// ordering

		$ordering = array();

		// featured switch

		$featured = false;
		$featured_only = false;
		switch ($params->get('show_f'))
		{
		    case '1': // only
		        $featured = true;
		        $featured_only = true;
			    $query->where($db->quoteName('a.featured') . ' = 1');
				break;
			case '0': // hide
			    $query->where($db->quoteName('a.featured') . ' = 0');
				break;
			case '2': // first the featured ones
			    $featured = true;
			    $ordering[] = $db->quoteName('a.featured') . ' DESC';
			    break;
			default: // no discrimination between featured/unfeatured items
			    $featured = true;
		}

		// category order

		if (!$featured_only) {
    		switch ($params->get('cat_order', '')) 
    		{
    		    case 'o_asc': $ordering[] = $db->quoteName('c.parent') . ' ASC'; $ordering[] = $db->quoteName('c.ordering') . ' ASC'; break;
    		    case 'o_dsc': $ordering[] = $db->quoteName('c.parent') . ' DESC'; $ordering[] = $db->quoteName('c.ordering') . ' DESC'; break;
    		}
		}

		// general ordering

		switch ($params->get( 'order' ))
		{
		    case 'o_asc':
		        if ($featured) {
		            $ordering[] = 'CASE WHEN ' . $db->quoteName('a.featured') . ' = 1 THEN ' . $db->quoteName('a.featured_ordering') . ' ELSE ' . $db->quoteName('a.ordering') . ' END ASC';
		        } else {
		            $ordering[] = $db->quoteName('a.ordering') . ' ASC';
		        }
		        break;
		    case 'o_dsc':
		        if ($featured) {
		            $ordering[] = 'CASE WHEN ' . $db->quoteName('a.featured') . ' = 1 THEN ' . $db->quoteName('a.featured_ordering') . ' ELSE ' . $db->quoteName('a.ordering') . ' END DESC';
		        } else {
		            $ordering[] = $db->quoteName('a.ordering') . ' DESC';
		        }
		        break;
		    case 'p_asc': $ordering[] = $db->quoteName('a.publish_up') . ' ASC'; break;
		    case 'p_dsc': $ordering[] = $db->quoteName('a.publish_up') . ' DESC'; break;
		    case 'm_asc': $ordering[] = $db->quoteName('a.modified') . ' ASC'; $ordering[] = $db->quoteName('a.created') . ' ASC'; break;
		    case 'm_dsc': $ordering[] = $db->quoteName('a.modified') . ' DESC'; $ordering[] = $db->quoteName('a.created') . ' DESC'; break;
		    case 'c_asc': $ordering[] = $db->quoteName('a.created') . ' ASC'; break;
		    case 'c_dsc': $ordering[] = $db->quoteName('a.created') . ' DESC'; break;
		    case 'mc_asc': $ordering[] = 'CASE WHEN ' . $query->isNullDatetime('a.modified') . ' THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.modified') . ' END ASC'; break;
		    case 'mc_dsc': $ordering[] = 'CASE WHEN ' . $query->isNullDatetime('a.modified') . ' THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.modified') . ' END DESC'; break;
		    case 'random': $ordering[] = $query->rand(); break;
		    case 'hit': $ordering[] = $db->quoteName('a.hits') . ' DESC'; break;
		    case 'title_asc': $ordering[] = $db->quoteName('a.title') . ' ASC'; break;
		    case 'title_dsc': $ordering[] = $db->quoteName('a.title') . ' DESC'; break;
		    default: $ordering[] = $db->quoteName('a.publish_up') . ' DESC';
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
		if (!empty($articles_to_exclude)) {
		    $articles_to_exclude = ArrayHelper::toInteger($articles_to_exclude);
		    $query->whereNotIn($db->quoteName('a.id'), $articles_to_exclude);
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

		foreach ($items as &$item) {

			// image

			$item->image = '';
			$imagesrc = 'media/k2/items/cache/'.md5("Image".$item->id).'_XL.jpg'; // k2 image
			if (is_file(JPATH_ROOT.'/'.$imagesrc)) {
				$item->image = $imagesrc;
			}

			// n/a: links a, b and c
		}

		return $items;
	}

}
