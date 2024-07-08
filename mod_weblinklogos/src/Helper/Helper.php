<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\WeblinkLogos\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use SYW\Library\Image as SYWImage;
use SYW\Library\Tags as SYWTags;
use SYW\Library\Text as SYWText;

class Helper
{
	protected static $weblinks_config_params;

	protected static $image_extension_types = array('png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'svg');

	/**
	 * Load the script that handles click feedback
	 */
	static function loadClickedScript($id) {

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$script = 'document.addEventListener("readystatechange", function(event) { ';
			$script .= 'if (event.target.readyState === "complete") { ';
				$script .= 'var items = document.querySelectorAll("#weblinklogo_' . $id . ' .weblink_item"); ';
				$script .= 'for (var i = 0; i < items.length; i++) { ';
					$script .= 'var links = items[i].querySelectorAll("a"); ';
					$script .= 'for (var j = 0; j < links.length; j++) { ';
						$script .= 'links[j].addEventListener("click", function(e) { ';
							$script .= 'this.classList.add("clicked");';
						$script .= '}.bind(items[i])); '; // the binding here replaces 'this' with items[i]
					$script .= '} ';
				$script .= '} ';
			$script .= '} ';
		$script .= '}); ';

		$wam->addInlineScript($script);
	}

	static function getList($params)
	{
		$db = Factory::getDbo();
		$app = Factory::getApplication();

		$jinput = $app->input;
		$option = $jinput->get('option');
		$view = $jinput->get('view');

		$related = $params->get('related', 0); // 0: no, 1: keywords, 2: tags weblinks only, 3: tags any content

		$item_on_page_id = '';
		$item_on_page_tagids = array();

		if ($related == 3) { // related by tag any content
			
			if ($option === 'com_trombinoscopeextended' && $view === 'contact') { // because tags are recorded with com_contact
				$option = 'com_contact';
			}

			$temp = $jinput->getString('id');
			$temp = explode(':', $temp);
			$item_on_page_id = $temp[0];

			if ($item_on_page_id) {
				$helper_tags = new TagsHelper();
				$tags = $helper_tags->getItemTags($option.'.'.$view, $item_on_page_id); // array of tag objects
				foreach ($tags as $tag) {
					$item_on_page_tagids[] = $tag->tag_id;
				}
			}

			if (empty($item_on_page_tagids)) {
				return array(); // no result because no tag found for the object on the page
			}
		}

		$query = $db->getQuery(true);

		$user = Factory::getApplication()->getIdentity();
		$view_levels = $user->getAuthorisedViewLevels();

		// START OF DATABASE QUERY

		$case_when1 = ' CASE WHEN ';
		$case_when1 .= $query->charLength('a.alias', '!=', '0');
		$case_when1 .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$case_when1 .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$case_when1 .= ' ELSE ';
		$case_when1 .= $a_id . ' END AS slug';

		$case_when2 = ' CASE WHEN ';
		$case_when2 .= $query->charLength('c.alias', '!=', '0');
		$case_when2 .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when2 .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when2 .= ' ELSE ';
		$case_when2 .= $c_id . ' END AS catslug';

		$query->select('a.*,' . $case_when1 . ',' . $case_when2);
		$query->select($db->quoteName(array('c.title', 'c.path', 'c.access', 'c.alias'), array('category_title', 'category_route', 'category_access', 'category_alias')));
		$query->from($db->quoteName('#__weblinks', 'a'));
		$query->whereIn($db->quoteName('a.access'), $view_levels);

		// filter by categories

		$categories_array = $params->get('category', array());

		$array_of_category_values = array_count_values($categories_array);
		if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
			// keep categories = ''
			if (!$params->get('cat_inex', 1)) {
				return array(); // if all categories excluded, then there should be no result
			}
		} else {
			// sub-category inclusion
			$get_sub_categories = $params->get('includesubcategories', 'no');
			if ($get_sub_categories != 'no') {

				$levels = $params->get('levelsubcategories', 1);

				$categories_object = Categories::getInstance('Weblinks');
				foreach ($categories_array as $category) {
					$category_object = $categories_object->get($category);
					if (isset($category_object) && $category_object->hasChildren()) {

						$sub_categories_array = $category_object->getChildren(true); // true is for recursive
						foreach ($sub_categories_array as $subcategory_object) {
							$condition = ($get_sub_categories == 'all' || ($subcategory_object->level - $category_object->level) <= $levels);
							if ($condition) {
								$categories_array[] = $subcategory_object->id;
							}
						}
					}

				}

				$categories_array = array_unique($categories_array);
			}

			if (!empty($categories_array)) {
				$test_type = $params->get('cat_inex', 1) ? 'IN' : 'NOT IN';
				$query->where($db->quoteName('a.catid') . ' ' . $test_type . ' (' . implode(',', $categories_array) . ')');
			}
		}

		$query->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'));
		$query->whereIn($db->quoteName('c.access'), $view_levels);
		$query->where($db->quoteName('c.published') . ' = 1');

		// filter by tags

		$tags = $params->get('tags', array());
		$tags = array_filter($tags); // remove empty tags (array(1) { [0]=> string(0) "" }) wrongly returned from RL Advanced Module Manager

		if (!empty($tags)) {

			// if all selected, get all available tags - we may not get any
			$array_of_tag_values = array_count_values($tags);
			if (isset($array_of_tag_values['all']) && $array_of_tag_values['all'] > 0) { // 'all' was selected
				$tags = array();
				$tag_objects = SYWTags::getTags('com_weblinks.weblink');
				if ($tag_objects !== false) {
					foreach ($tag_objects as $tag_object) {
						$tags[] = $tag_object->id;
					}
				}

				if (empty($tags) && $params->get('tags_inex', 1)) { // won't return any weblink if no weblink has been associated to any tag (TODO when include tags only)
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
		}

		if (!empty($tags)) {

			$tags_to_match = implode(',', $tags);

			$query->select('COUNT(' . $db->quoteName('tags.id') . ') AS tags_count');
			$query->join('INNER', $db->quoteName('#__contentitem_tag_map', 'm'), $db->quoteName('m.content_item_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('m.type_alias') . ' = ' . $db->quote('com_weblinks.weblink'));
			$query->join('INNER', $db->quoteName('#__tags', 'tags'), $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('tags.id'));

			$test_type = $params->get('tags_inex', 1) ? 'IN' : 'NOT IN';
			$query->where($db->quoteName('tags.id') . ' ' . $test_type . ' (' . $tags_to_match . ')');

			$query->whereIn($db->quoteName('tags.access'), $view_levels);
			$query->where($db->quoteName('tags.published') . ' = 1');

			if (!$params->get('tags_inex', 1)) { // EXCLUDE TAGS
			    $query->select($db->quoteName('tags_per_items.tag_count_per_item'));
			    
			    $subquery = $db->getQuery(true);

			    // subquery gets all the tags for all items
			    $subquery->select($db->quoteName('mm.content_item_id', 'content_id'));
			    $subquery->select('COUNT(' . $db->quoteName('tt.id') . ') AS tag_count_per_item');
			    $subquery->from($db->quoteName('#__contentitem_tag_map', 'mm'));
			    $subquery->join('INNER', $db->quoteName('#__tags', 'tt'), $db->quoteName('mm.tag_id') . ' = ' . $db->quoteName('tt.id'));
			    $subquery->where($db->quoteName('tt.access') . ' IN (' . implode(',', $view_levels) . ')'); // DO NOT USE whereIn (or else we need prepared variables)
			    $subquery->where($db->quoteName('tt.published') . ' = 1');
			    $subquery->where($db->quoteName('mm.type_alias') . ' = ' . $db->quote('com_weblinks.weblink'));
			    $subquery->group($db->quoteName('content_id'));

			    $query->join('INNER', '(' . (string) $subquery . ') AS tags_per_items', $db->quoteName('tags_per_items.content_id') . ' = ' . $db->quoteName('a.id'));

				// we keep items that have the same amount of tags before and after removals
				$query->having('COUNT(' . $db->quoteName('tags.id') . ') = ' . $db->quoteName('tags_per_items.tag_count_per_item'));
			} else { // INCLUDE TAGS
				if ($params->get('tags_match', 'any') == 'all') {
					$query->having('COUNT(' . $db->quoteName('tags.id') . ') = ' . count($tags));
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

			$weblink_id_arrays_from_cfields = array();

			foreach ($customfield_filters_arrays as $customfield_filter) {

				$subQuery = $db->getQuery(true);

				$subQuery->select('DISTINCT ' . $db->quoteName('cfv.item_id')); // no unique results when joining with categories
				$subQuery->from($db->quoteName('#__fields_values', 'cfv'));
				$subQuery->join('LEFT', $db->quoteName('#__fields', 'f'), $db->quoteName('f.id') . ' = ' . $db->quoteName('cfv.field_id'));
				$subQuery->where('(' . $db->quoteName('f.context') . ' IS NULL OR ' . $db->quoteName('f.context') . ' = ' . $db->quote('com_weblinks.weblink') . ')');
				$subQuery->where('(' . $db->quoteName('f.state') . ' IS NULL OR ' . $db->quoteName('f.state') . ' = 1)');
				$subQuery->where('(' . $db->quoteName('f.access') . ' IS NULL OR ' . $db->quoteName('f.access') . ' IN (' . implode(',', $view_levels) . '))');
				$subQuery->where($db->quoteName('cfv.field_id') . ' = :fieldId');
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
					$weblink_id_arrays_from_cfields[] = $db->loadColumn();
				} catch (ExecutionFailureException $e) {
					Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				}
			}
			
			if (!empty($weblink_id_arrays_from_cfields)) {
				
				// keep only the ids found in all the arrays
				if (count($weblink_id_arrays_from_cfields) > 1) {
					$weblink_ids = call_user_func_array('array_intersect', $weblink_id_arrays_from_cfields);
				} else {
					$weblink_ids = $weblink_id_arrays_from_cfields[0];
				}
				
				if (!empty($weblink_ids)) {
				    $weblink_ids = ArrayHelper::toInteger($weblink_ids);
				    $query->whereIn($db->quoteName('a.id'), $weblink_ids); // include all weblinks that have custom field value(s) that correspond to the custom field value
				} else {
				    $query->where($db->quoteName('a.id') . ' = 0'); // no weblink having all values selected
				}
			}
		}

		// filter by state

		$query->where($db->quoteName('a.state') . ' = 1');

		// filter by start and end dates

		$nowDate = $db->quote(Factory::getDate()->toSql());

		$query->where('(' . $query->isNullDatetime('a.publish_up') . ' OR ' . $db->quoteName('a.publish_up') . ' <= ' . $nowDate . ')');
		$query->where('(' . $query->isNullDatetime('a.publish_down') . ' OR ' . $db->quoteName('a.publish_down') . ' >= ' . $nowDate . ')');

		// filter by language

		if ($params->get('filter_lang', 1) && Multilanguage::isEnabled()) {
			$query->whereIn($db->quoteName('a.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
		}

		// ordering

		$ordering = array();

		// category order

		switch ($params->get('cat_order', ''))
		{
		    case 'o_asc': $ordering[] = $db->quoteName('c.lft') . ' ASC'; break;
			case 'o_dsc': $ordering[] = $db->quoteName('c.lft') . ' DESC'; break;
			case 'n_asc': $ordering[] = $db->quoteName('c.title') . ' ASC'; break;
		    case 'n_dsc': $ordering[] = $db->quoteName('c.title') . ' DESC'; break;
		}

		// items order

		switch ($params->get('ordering', 'title'))
		{
		    case 'title': $ordering[] = $db->quoteName('a.title') . ' ' . strtoupper($params->get('direction', 'asc')); break;
		    case 'order': $ordering[] = $db->quoteName('a.ordering') . ' ' . strtoupper($params->get('direction', 'asc')); break;
		    case 'random': $ordering[] = $query->rand(); break;
		    case 'hits': $ordering[] = $db->quoteName('a.hits') . ' ' . strtoupper($params->get('direction', 'asc')); break;

			case 'created': $ordering[] = $db->quoteName('a.created') . ' ' . strtoupper($params->get('direction', 'asc')); break;
			case 'modified': $ordering[] = $db->quoteName('a.modified') . ' ' . strtoupper($params->get('direction', 'asc')); break;
			case 'published': $ordering[] = $db->quoteName('a.publish_up') . ' ' . strtoupper($params->get('direction', 'asc')); break;

			case 'manual':
				$weblinks_to_include = array_filter(explode(',', trim($params->get('in', ''), ' ,')));
				if (!empty($weblinks_to_include)) {
					$manual_ordering = 'CASE a.id';
					foreach ($weblinks_to_include as $key => $id) {
					    $manual_ordering .= ' WHEN ' . $id . ' THEN ' . $key;
					}
					$ordering[] = $manual_ordering . ' ELSE 999 END, a.id'; // 'FIELD(a.id, ' . $weblinks_to_include . ')' is MySQL specific
				}
		}

		if (count($ordering) > 0) {
			$query->order($ordering);
		}

		// include only

		$weblinks_to_include = array_filter(explode(',', trim($params->get('in', ''), ' ,')));
		if (!empty($weblinks_to_include)) {
		    $weblinks_to_include = ArrayHelper::toInteger($weblinks_to_include);
		    $query->whereIn($db->quoteName('a.id'), $weblinks_to_include);
		}

		// exclude

		$weblinks_to_exclude = array_filter(explode(',', trim($params->get('ex', ''), ' ,')));
		if (!empty($weblinks_to_exclude)) {
		    $weblinks_to_exclude = ArrayHelper::toInteger($weblinks_to_exclude);
		    $query->whereNotIn($db->quoteName('a.id'), $weblinks_to_exclude);
		}

		if (intval($params->get('count', '')) > 0) {
			$query->setLimit(intval($params->get('count')));
		}

		// launch query

		$count = trim($params->get('count', ''));
		$startat = $params->get('startat', 1);
		if ($startat < 1) {
			$startat = 1;
		}

		if (!empty($count)) {
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

		// tags filtering

// 		if (!empty($tags)) {

// 			if ($params->get('tags_match', 'any') == 'all') { // only keep items that have all tags

// 				$number_of_tags_needed = count($tags);
// 				$items_to_keep = array();

// 				foreach ($items as $item) {
// 					if ($item->tags_count == $number_of_tags_needed) {
// 						$items_to_keep[] = $item;
// 					}
// 				}

// 				$items = $items_to_keep;
// 			}
// 		}

		// modify item data

		foreach ($items as $i => $item) {

			// images

			$images = json_decode($item->images);
			$item->image_first = '';
			$item->image_second = '';
			$item->alt_first = ''; // $item->title;
			$item->caption_first = $item->title;
			$item->alt_second = ''; // $item->title;
			$item->caption_second = $item->title;
			if (isset($images->image_first)) {

				$image_object = HTMLHelper::cleanImageURL($images->image_first);
				$item->image_first = $image_object->url;

				if (!empty($images->image_first_alt)) {
					$item->alt_first = $images->image_first_alt;
				}
				if (!empty($images->image_first_caption)) {
					$item->caption_first = $images->image_first_caption;
				}
			}
			if (isset($images->image_second)) {

				$image_object = HTMLHelper::cleanImageURL($images->image_second);
				$item->image_second = $image_object->url;

				if (!empty($images->image_second_alt)) {
					$item->alt_second = $images->image_second_alt;
				}
				if (!empty($images->image_second_caption)) {
					$item->caption_second = $images->image_second_caption;
				}
			}

			if (empty($item->image_first)) {
				if ($params->get('d_logo', '') != '') {
					$default_image_object = HTMLHelper::cleanImageURL($params->get('d_logo', ''));
					$item->image_first = $default_image_object->url;
				} else {

					$show_errors = self::isShowErrors($params);

					if (!$show_errors) {
						unset($items[$i]);
						continue;
					}
				}
			}

			$weblink_params = json_decode($item->params);

			// link

			$count_clicks = $params->get('count_clicks', 0);
			if ($count_clicks == 'link') {
				$count_clicks = false;
				if (isset($weblink_params->count_clicks)) {
					if ($weblink_params->count_clicks == '') {
						$count_clicks = self::getWeblinksConfig()->get('count_clicks');
					} else {
						$count_clicks = $weblink_params->count_clicks;
					}
				}
			}

			if ($count_clicks) {
				$item->link	= Route::_('index.php?option=com_weblinks&task=weblink.go&catid=' . $item->catslug . '&id=' . $item->slug);
			} else {
				$item->link = $item->url;
			}

			// target

			$item->target = $params->get('target', 1);
			if ($item->target == 'link') {
				$item->target = 1;
				if (isset($weblink_params->target)) {
					if ($weblink_params->target == '') {
						$item->target = self::getWeblinksConfig()->get('target');
					} else {
						$item->target = $weblink_params->target;
					}
				}
			}

			// text

			if ($params->get('description', 0)) {

				$letter_count = trim($params->get('l_count', ''));
				if (empty($letter_count)) {
					$letter_count = -1;
				} else {
					$letter_count = (int)($letter_count);
				}

				$strip_tags = $params->get('strip_tags', 1);
				$keep_tags = trim($params->get('keep_tags', ''));
				$trigger_events = $params->get('trigger_events', false);
				$truncate_last_word = $params->get('trunc_l_w', 0);

				if ($trigger_events) {
					$item->text = $item->description;
					Factory::getApplication()->triggerEvent('onContentPrepare', array('com_weblinks.weblink', &$item, &$params, 0));
					$item->description = SYWText::getText($item->text, 'html', $letter_count, $strip_tags, $keep_tags, true, $truncate_last_word);
				} else {
					$item->description = SYWText::getText($item->description, 'html', $letter_count, $strip_tags, $keep_tags, true, $truncate_last_word);
				}
			} else {
				$item->description = '';
			}
		}

		return $items;
	}

	/**
	* Create the thumbnail(s), if possible
	*
	* @param string $module_id
	* @param string $item_id
	* @param string $imagesrc
	* @param string $tmp_path
	* @param boolean $clear_cache
	* @param integer $head_width
	* @param integer $head_height
	* @param boolean $crop_picture
	* @param array $image_quality_array
	* @param string $filter
	* @param boolean $create_high_resolution
	*
	* @return array the original image path if errors before thumbnail creation
	*  or no thumbnail path if errors during thumbnail creation
	*  or thumbnail path if no error
	*/
	static function getThumbnailPath($module_id, $item_id, $imagesrc, $tmp_path, $clear_cache, $head_width, $head_height, $crop_picture, $image_quality_array, $filter, $create_highres_images = false, $allow_remote = true, $thumbnail_mime_type = '')
	{
		$result = array(null, null); // image link and error

		if ($head_width == 0 || $head_height == 0) {
			// keep original image
			$result[0] = $imagesrc;
			$result[1] = Text::_('MOD_WEBLINKLOGO_INFO_USINGORIGINALIMAGE'); // necessary to avoid high resolution syntax

			return $result;
		}

		if (!extension_loaded('gd') && !extension_loaded('imagick')) {
			// missing image library
			$result[0] = $imagesrc;
			$result[1] = Text::_('MOD_WEBLINKLOGO_ERROR_GDNOTLOADED');

			return $result;
		}

		$original_imagesrc = $imagesrc;

		// there may be extra info in the path
		// example: http://www.tada.com/image.jpg?x=3
		// thubmnails cannot be created if ? in the path

		$url_array = explode("?", $imagesrc);
		$imagesrc = $url_array[0];

		$imageext = strtolower(File::getExt($imagesrc));
		$original_imageext = $imageext;

		if (!in_array($imageext, self::$image_extension_types)) {

			// case where image is a URL with no extension (generated image)
			// example: http://argos.scene7.com/is/image/Argos/7491801_R_Z001A_UC1266013?$TMB$&wid=312&hei=312
			// thubmnails cannot be created from generated images external paths
			// or image has another file type like .tiff

			$result[0] = $original_imagesrc;
			$result[1] = Text::sprintf('MOD_WEBLINKLOGO_ERROR_UNSUPPORTEDFILETYPE', $original_imagesrc);

			return $result;
		}
		
		// Special case with SVG: no creation of thumbnails
		if ($imageext === 'svg') {
		    return [$original_imagesrc, ''];
		}

		// URL works only if 'allow url fopen' is 'on', which is a security concern
		// retricts images to the ones found on the site, external URLs are not allowed (for security purposes)
		if (substr_count($imagesrc, 'http') <= 0) { // if the image is internal
			if (substr($imagesrc, 0, 1) == '/') {
				// take the slash off
				$imagesrc = ltrim($imagesrc, '/');
			}
		} else {
			$base = Uri::base(); // Uri::base() is http://www.mysite.com/subpath/
			$imagesrc = str_ireplace($base, '', $imagesrc);
		}

		// we end up with all $imagesrc paths as 'images/...'
		// if not, the URL was from an external site

		if (substr_count($imagesrc, 'http') > 0) {
			// we have an external URL
			if (/*!ini_get('allow_url_fopen') || */!$allow_remote) {
				$result[0] = $original_imagesrc;
				$result[1] = Text::sprintf('MOD_WEBLINKLOGO_ERROR_EXTERNALURLNOTALLOWED', $imagesrc);

				return $result;
			}
		}

		switch ($thumbnail_mime_type) {
			case 'image/jpg': $imageext = 'jpg'; break;
			case 'image/png': $imageext = 'png'; break;
			case 'image/webp': $imageext = 'webp'; break;
			case 'image/avif': $imageext = 'avif';
		}

		if ($filter == 'none' || strpos($filter, '_css') !== false) {
			$filtername = '';
		} else {
			$filtername = '_' . $filter;
		}

		if (!empty($module_id)) {
			$module_id = '_' . $module_id;
		}

		$filename = $tmp_path . '/thumb' . $module_id . '_' . $item_id . $filtername . '.' . $imageext;
		$filename_highres = $tmp_path . '/thumb' . $module_id . '_' . $item_id . $filtername . '@2x.' . $imageext;
		if ((is_file(JPATH_ROOT . '/' . $filename) && !$clear_cache && !$create_highres_images)
			|| (is_file(JPATH_ROOT . '/' . $filename) && !$clear_cache && $create_highres_images && is_file(JPATH_ROOT . '/' . $filename_highres))) {

			// thumbnail(s) already exist

		} else { // create the thumbnail

			$image = new SYWImage($imagesrc);

			if (is_null($image->getImagePath())) {
				$result[1] = Text::sprintf('MOD_WEBLINKLOGO_ERROR_IMAGEFILEDOESNOTEXIST', $imagesrc);
			} else if (is_null($image->getImageMimeType())) {
				$result[1] = Text::sprintf('MOD_WEBLINKLOGO_ERROR_UNABLETOGETIMAGEPROPERTIES', $imagesrc);
			} else if (is_null($image->getImage()) || $image->getImageWidth() == 0) {
				$result[1] = Text::sprintf('MOD_WEBLINKLOGO_ERROR_UNSUPPORTEDFILETYPE', $imagesrc);
			} else {

				$quality = self::getImageQualityFromExt($imageext, $image_quality_array);

				// negative values force the creation of the thumbnails with size of original image
				// great to create high-res of original image and/or to use quality parameters to create an image with smaller file size
				if ($head_width < 0 || $head_height < 0) {
					$head_width = $image->getImageWidth();
					$head_height = $image->getImageHeight();
				}

				if ($image->toThumbnail($filename, $thumbnail_mime_type, $head_width, $head_height, $crop_picture, $quality, $filter, $create_highres_images)) {

					if ($image->getImageMimeType() === 'image/webp' || $thumbnail_mime_type === 'image/webp' || $image->getImageMimeType() === 'image/avif' || $thumbnail_mime_type === 'image/avif') { // create fallback

						$fallback_extension = 'png';
						$fallback_mime_type = 'image/png';

						// create fallback with original image mime type when the original is not webp or avif
						if ($image->getImageMimeType() !== 'image/webp' && $image->getImageMimeType() !== 'image/avif') {
							$fallback_extension = $original_imageext;
							$fallback_mime_type = $image->getImageMimeType();
						}

						$quality = self::getImageQualityFromExt($fallback_extension, $image_quality_array);

						if (!$image->toThumbnail($tmp_path . '/thumb' . $module_id . '_' . $item_id . $filtername . '.' . $fallback_extension, $fallback_mime_type, $head_width, $head_height, $crop_picture, $quality, $filter, $create_highres_images)) {
							$result[1] = Text::sprintf('MOD_WEBLINKLOGO_ERROR_THUMBNAILCREATIONFAILED', $imagesrc);
						}
					}
				} else {
					$result[1] = Text::sprintf('MOD_WEBLINKLOGO_ERROR_THUMBNAILCREATIONFAILED', $imagesrc);
				}
			}

			$image->destroy();
		}

		if (empty($result[1])) {
			$result[0] = $filename;
		}

		return $result;
	}

	static protected function getImageQualityFromExt($image_extension, $qualities = array('jpg' => 75, 'png' => 3, 'webp' => 80, 'avif' => 80))
	{
		$quality = -1;
		
		switch ($image_extension){
			case 'jpg': case 'jpeg': $quality = $qualities['jpg']; break; // 0 to 100
			case 'png': $quality = round(11.111111 * (9 - $qualities['png'])); break; // compression: 0 to 9
			case 'webp': $quality = $qualities['webp']; break; // 0 to 100
			case 'avif': $quality = $qualities['avif']; // 0 to 100
		}
		
		return $quality;
	}

	/**
	 * Delete all thumbnails for a module instance
	 *
	 * @param string $tmp_path
	 * @param string $module_id
	 *
	 * @return false if the glob function failed, true otherwise
	 */
	static function clearThumbnails($tmp_path, $module_id = '') {

		Log::addLogger(array('text_file' => 'syw.errors.php'), Log::ALL, array('syw'));

		if ($module_id) {
			$module_id = '_'.$module_id;
		}

		if (function_exists('glob')) {
			$filenames = glob(JPATH_ROOT.'/'.$tmp_path.'/thumb'.$module_id.'_*.*');
			if ($filenames == false) {
				Log::add('modWeblinklogoHelper:clearThumbnails() - Error on glob - No permission on files/folder or old system', Log::ERROR, 'syw');
				return false;
			}

			foreach ($filenames as $filename) {
				File::delete($filename); // returns false if deleting failed - won't log to avoid making the log file huge
			}

			return true;
		} else {
			Log::add('modWeblinklogoHelper:clearThumbnails() - glob - function does not exist', Log::ERROR, 'syw');
		}

		return false;
	}

	/**
	 * Load common stylesheet to all module instances
	 */
	static function loadCommonStylesheet() {

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$wam->registerAndUseStyle('wl.common_styles', 'mod_weblinklogos/common_styles.min.css', ['relative' => true, 'version' => 'auto']);
	}

	/**
	 * Load user stylesheet to all module instances
	 * if the file has 'substitute' in the name, it will replace all module styles
	 */
	static function loadUserStylesheet($styles_substitute = false) {

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$prefix = 'common_user';
		if ($styles_substitute) {
			$prefix = 'substitute';
		}

		if (File::exists(JPATH_ROOT . '/media/mod_weblinklogos/css/' . $prefix . '_styles-min.css')) { //  B/C
			if (JDEBUG && File::exists(JPATH_ROOT . '/media/mod_weblinklogos/css/' . $prefix . '_styles.css')) {
				$wam->registerAndUseStyle('wl.' . $prefix . '_styles', 'mod_weblinklogos/' . $prefix . '_styles.css', ['relative' => true, 'version' => 'auto']);
			} else {
				$wam->registerAndUseStyle('wl.' . $prefix . '_styles', 'mod_weblinklogos/' . $prefix . '_styles-min.css', ['relative' => true, 'version' => 'auto']);
			}
		} else {
			$wam->registerAndUseStyle('wl.' . $prefix . '_styles', 'mod_weblinklogos/' . $prefix . '_styles.min.css', ['relative' => true, 'version' => 'auto']);
		}
	}

	/**
	 * Get the site mode
	 * @return string (dev|prod|adv)
	 */
	public static function getSiteMode($params)
	{
		return $params->get('site_mode', 'dev');
	}

	/**
	 * Is the picture cache set to be cleared
	 * @return boolean
	 */
	public static function IsClearPictureCache($params)
	{
		if (self::getSiteMode($params) == 'dev') {
			return true;
		}
		if (self::getSiteMode($params) == 'prod') {
			return false;
		}
		return $params->get('clear_cache', true);
	}

	/**
	 * Is the style/script cache set to be cleared
	 * @return boolean
	 */
	public static function IsClearHeaderCache($params)
	{
		if (self::getSiteMode($params) == 'dev') {
			return true;
		}
		if (self::getSiteMode($params) == 'prod') {
			return false;
		}
		return $params->get('clear_header_files_cache', 'true');
	}

	/**
	 * Are errors shown ?
	 * @return boolean
	 */
	public static function isShowErrors($params)
	{
		if (self::getSiteMode($params) == 'dev') {
			return true;
		}
		if (self::getSiteMode($params) == 'prod') {
			return false;
		}
		return $params->get('show_errors', false);
	}

	/**
	 * Are white spaces removed ?
	 * @return boolean
	 */
	public static function isRemoveWhitespaces($params)
	{
		if (self::getSiteMode($params) == 'dev') {
			return false;
		}
		if (self::getSiteMode($params) == 'prod') {
			return true;
		}
		return $params->get('remove_whitespaces', false);
	}

	/**
	 * Get the Weblinks component's configuration parameters
	 * @return \Joomla\Registry\Registry
	 */
	public static function getWeblinksConfig()
	{
		if (!isset(self::$weblinks_config_params)) {

			self::$weblinks_config_params = new Registry();

			if (File::exists(JPATH_ADMINISTRATOR . '/components/com_weblinks/config.xml')) {
				self::$weblinks_config_params = ComponentHelper::getParams('com_weblinks');
			}
		}

		return self::$weblinks_config_params;
	}

}
