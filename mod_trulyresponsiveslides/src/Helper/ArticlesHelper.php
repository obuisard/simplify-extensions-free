<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use SYW\Library\Tags as SYWTags;

class ArticlesHelper
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

		$query->select($db->quoteName(array('a.id', 'a.catid', 'a.title', 'a.alias', 'a.introtext', 'a.fulltext', /*'a.state',*/ 'a.images', 'a.urls', 'a.attribs', 'a.metadata', 'a.metakey', 'a.metadesc', 'a.access', 'a.hits', 'a.featured', 'a.language')));

		$query->select($db->quoteName(array('a.checked_out', 'a.checked_out_time', 'a.created', 'a.created_by', 'a.created_by_alias')));

		// Use created if modified is 0
		$query->select('CASE WHEN ' . $query->isNullDatetime('a.modified') . ' THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.modified') . ' END AS ' . $db->quoteName('modified'));
		$query->select($db->quoteName('a.modified_by'));

		// Use created if publish_up is 0
		$query->select('CASE WHEN ' . $query->isNullDatetime('a.publish_up') . ' THEN ' . $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.publish_up') . ' END AS  ' . $db->quoteName('publish_up'));
		$query->select($db->quoteName('a.publish_down'));

		$query->select($subquery1);
		$query->select($subquery2);

		$query->from($db->quoteName('#__content', 'a'));

		// join over the categories
		$query->select($db->quoteName(array('c.title', 'c.path', 'c.access', 'c.alias'), array('category_title', 'category_route', 'category_access', 'category_alias')));
		$query->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'));

		// access filter

		$query->whereIn($db->quoteName('a.access'), $view_levels);
		$query->whereIn($db->quoteName('c.access'), $view_levels);

		// publishing

		$nowDate = $db->quote(Factory::getDate()->toSql());

		$query->where($db->quoteName('a.state') . ' = 1');

		$query->where('(' . $query->isNullDatetime('a.publish_up') . ' OR ' . $db->quoteName('a.publish_up') . ' <= ' . $nowDate . ')');
		$query->where('(' . $query->isNullDatetime('a.publish_down') . ' OR ' . $db->quoteName('a.publish_down') . ' >= ' . $nowDate . ')');

		$query->where($db->quoteName('c.published') . ' = 1'); // does not check for category published state in parent categories up the tree

		// category filter

		$categories_array = $params->get('cat_id', array());

		$array_of_category_values = array_count_values($categories_array);
		if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
			// take everything, so no category selection
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

			if (!empty($categories_array)) {
				$query->whereIn($db->quoteName('a.catid'), $categories_array);
			}
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

				if (empty($tags)) { // won't return any article if no article has been associated to any tag (when include tags only)
					return array();
				}
			}
		}

		if (!empty($tags)) {

			$query->select('COUNT(' . $db->quoteName('tags.id') . ') AS tags_count');
			$query->join('INNER', $db->quoteName('#__contentitem_tag_map', 'm'), $db->quoteName('m.content_item_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('m.type_alias') . ' = ' . $db->quote('com_content.article'));
			$query->join('INNER', $db->quoteName('#__tags', 'tags'), $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('tags.id'));
			$query->whereIn($db->quoteName('tags.id'), $tags);
			$query->whereIn($db->quoteName('tags.access'), $view_levels);
			$query->where($db->quoteName('tags.published') . ' = 1');

			if ($params->get('tags_match', 'any') == 'all') {
				$query->having('COUNT(' . $db->quoteName('tags.id') . ') = ' . count($tags));
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

		// language filter

		if ($params->get('filter_lang', 1) && Multilanguage::isEnabled()) {
			$query->whereIn($db->quoteName('a.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
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
				if ($params->get('order') == 'o_asc' || $params->get('order') == 'o_dsc') {
					$query->join('LEFT', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'));
				}
				break;
			case '0': // hide
			    $query->where($db->quoteName('a.featured') . ' = 0');
				break;
			case '2': // first the featured ones
				$featured = true;
				if ($params->get('order') == 'o_asc' || $params->get('order') == 'o_dsc') {
				    $query->join('LEFT', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'));
				}
				$ordering[] = $db->quoteName('a.featured') . ' DESC';
				break;
			default: // no discrimination between featured/unfeatured items
				$featured = true;
				if ($params->get('order') == 'o_asc' || $params->get('order') == 'o_dsc') {
				    $query->join('LEFT', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'));
				}
		}

		// category order

		if (!$featured_only) {
			switch ($params->get('cat_order', '')) 
			{
			    case 'o_asc' : $ordering[] = $db->quoteName('c.lft') . ' ASC'; break;
			    case 'o_dsc' : $ordering[] = $db->quoteName('c.lft') . ' DESC'; break;
			}
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
