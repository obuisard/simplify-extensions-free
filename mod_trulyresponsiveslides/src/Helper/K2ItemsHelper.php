<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Registry\Registry;

require_once (JPATH_SITE.'/components/com_k2/models/itemlist.php');

class K2ItemsHelper
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
		$cc_id = $query->castAsChar('c.id');
		$subquery2 .= $query->concatenate(array($cc_id, 'c.alias'), ':');
		$subquery2 .= ' ELSE ';
		$subquery2 .= $cc_id.' END AS cat_slug';

		$query->select('a.*, c.id AS cat_id, c.name AS category_title, c.alias AS cat_alias');

		$query->select($subquery1);
		$query->select($subquery2);

		$query->from('#__k2_items AS a');

		// join over the categories

		$query->join('INNER', '#__k2_categories AS c ON c.id = a.catid');

		// access filter

		$user = Factory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));

		$query->where('a.access IN ('.$groups.')');
		$query->where('c.access IN ('.$groups.')');

		// publishing

		//$nullDate = $db->quote($db->getNullDate());
		$nowDate = $db->quote(Factory::getDate()->toSql());

		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.trash') . ' = 0');
		$query->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= '.$nowDate.')');
		$query->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= '.$nowDate.')');

		$query->where($db->quoteName('c.published') . ' = 1');

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
				$categories = implode(',', $categories_array);
				$query->where('c.id IN ('.$categories.')');
			}
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

				if (empty($tags) /*&& $params->get('tags_inex', 1)*/) { // won't return any k2 item if no k2 item has been associated to any tag (when include tags only)
					return array();
				}
			}
		}

		if (!empty($tags)) {

			$tags_to_match = implode(',', $tags);

			$query->select('COUNT(tags.id) AS tags_count');
			$query->join('LEFT', '#__k2_tags_xref tags_xref ON tags_xref.itemID = a.id LEFT JOIN #__k2_tags tags ON tags.id = tags_xref.tagID');
			$query->where('tags.published = 1');
			$query->where('tags.id IN ('.$tags_to_match.')');

			if ($params->get('tags_match', 'any') == 'all') {
				$query->having('COUNT('.$db->quoteName('tags.id').') = '.count($tags));
			}

			$query->group($db->quoteName('a.id'));
		}

		// filter by language

		if ($params->get('filter_lang', 1) && Multilanguage::isEnabled()) {
			$query->where('a.language IN ('.$db->quote(Factory::getLanguage()->getTag()).','.$db->quote('*').')');
		}

		// ordering

		$ordering = '';

		// featured switch

		switch ($params->get('show_f'))
		{
			case '1': // only
				$query->where('a.featured = 1');
				break;
			case '0': // hide
				$query->where('a.featured = 0');
				break;
			case '2':
				$ordering .= 'a.featured DESC,';
			default: // show
		}

		// category order

		switch ($params->get('cat_order')) {
			case 'o_asc' :
				$ordering .= "c.lft ASC,";
				break;
			case 'o_dsc' :
				$ordering .= "c.lft DESC,";
				break;
			default :
		}

		// Set ordering

		switch ($params->get( 'order' ))
		{
			case 'o_asc': $ordering .= 'a.ordering ASC'; break;
			case 'o_dsc': $ordering .= 'a.ordering DESC'; break;
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

		foreach ($items as &$item) {

			// image

			$item->image = '';
			$imagesrc = 'media/k2/items/cache/'.md5("Image".$item->id).'_XL.jpg'; // k2 image
			if (is_file(JPATH_ROOT.'/'.$imagesrc)) {
				$item->image = $imagesrc;
			}

			// n/a: links a, b and c

			// convert the plugins field to an array
			$registry = new Registry();
			$registry->loadString($item->plugins);
			$item->plugins = $registry->toArray();
		}

		return $items;
	}

}
