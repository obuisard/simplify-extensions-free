<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrombinoscopeContacts\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Contact\Site\Helper\RouteHelper as ContactRouteHelper;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Registry\Registry;
use SYW\Library\Cache as SYWCache;
use SYW\Library\Image as SYWImage;
use SYW\Library\Tags as SYWTags;
use SYW\Library\Text as SYWText;
use SYW\Library\Utilities as SYWUtilities;

abstract class Helper
{
	protected static $sort_locale;
	protected static $contact_globals;

	protected static $flipScriptLoaded = false;
	protected static $commonStylesLoaded = false;
	protected static $userStylesLoaded = false;

	/**
	 * Is the picture set to be shown?
	 * @return boolean
	 */
	public static function isShowPicture($params)
	{
		return $params->get('s_pic', true);
	}

	/**
	 * Get the picture border width
	 * @return number
	 */
	public static function getPictureBorderWidth($params)
	{
		return intval($params->get('border_w', 0));
	}

	/**
	 * Get the picture width
	 * @return number
	 */
	public static function getPictureWidth($params)
	{
		return intval($params->get('pic_w', 100)) - self::getPictureBorderWidth($params) * 2;
	}

	/**
	 * Get the picture height
	 * @return number
	 */
	public static function getPictureHeight($params)
	{
		return intval($params->get('pic_h', 120) - self::getPictureBorderWidth($params) * 2);
	}

	/**
	 * Get the picture quality
	 * @return number
	 */
	public static function getPictureQuality($params)
	{
		$quality = intval($params->get('quality', 100));
		if ($quality > 100) {
			return 100;
		}
		if ($quality < 0) {
			return 0;
		}
		return $quality;
	}

	/**
	 * Get the picture filter(s)
	 * @return array[][]
	 */
	public static function getPictureFilters($params)
	{
		$filters = array();

		$image_filters = $params->get('image_filters', null);
		if (!empty($image_filters) && is_object($image_filters)) {
			foreach ($image_filters as $image_filter) {
				if ($image_filter->filter && $image_filter->filter != 'none') {
					$filters[] = $image_filter->filter;
				}
			}
		}

		return array('filters' => array_unique($filters));
	}

	/**
	 * Is the picture set to be cropped?
	 * @return boolean
	 */
	public static function isCropPicture($params)
	{
		return $params->get('crop_pic', 0);
	}

	/**
	 * Is the high definition picture needed?
	 * @return boolean
	 */
	public static function isCreateHighResolutionPicture($params)
	{
		return $params->get('create_highres', false);
	}

	/**
	 * Get the image temporary path
	 * @return string the path
	 */
	public static function getPictureTemporaryPath($params)
	{
		$thumb_path = $params->get('thumb_path', 'images');
		$subdirectory = 'thumbnails/tc';
		if ($thumb_path == 'cache') {
			$subdirectory = 'mod_trombinoscopecontacts';
		}
		return SYWCache::getTmpPath($thumb_path, $subdirectory);
	}

	/**
	 * Get the site mode
	 * @return string (dev|prod|adv)
	 */
	public static function getSiteMode($params)
	{
		return $params->get('site_mode', 'adv');
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
		return $params->get('clear_css_cache', 'true');
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
	 * Get the Bootstrap version the extension must be compatible with
	 * @return number
	 */
	public static function getBootstrapVersion($params)
	{
		$bootstrap_version = $params->get('bootstrap_version', 'joomla');
		if ($bootstrap_version === 'joomla') {
			return 5; //version_compare(JVERSION, '4.0.0', 'lt') ? 2 : 5;
		}
		return intval($bootstrap_version);
	}

	/**
	 * Does Bootstrap need to be loaded ?
	 * @return boolean
	 */
	public static function isLoadBootstrap($params)
	{
		if ($params->get('bootstrap_version', 'joomla') === 'joomla') {
			return true;
		}
		return false;
	}

	public static function getContacts($params, $module)
	{
		$app = Factory::getApplication();
		$option = $app->input->get('option', '');
		$view = $app->input->get('view', '');

		if (!$params->get('show_on_contact_page', 1)) {
			if ($option === 'com_contact' && $view === 'contact') {
				return null;
			}
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$related_id = ''; // to avoid the contact to be visible in the list of related contacts

		//self::$address_format = $params->get('a_fmt', 'zss');
		//self::$text_type = $params->get('t', 'info');
		self::$sort_locale = $params->get('sort_locale', 'en_US');

		// contact selection
		$selection = $params->get('selection', 'categories');

		$metakeys = array();
		$tags = array();

		if ($selection != 'contact') { // we don't want to go through metakeys and tags if we just want the contact selected

			// metakeys filtering

			//$metakeys = array();
			$item_on_page_keys = array();

			if ($selection == 'related') {

				$item_on_page_id = '';
				if (($option == 'com_contact' || $option == 'com_trombinoscopeextended') && $view == 'contact') {
					$temp = $app->input->getString('id');
					$temp = explode(':', $temp);
					$item_on_page_id = $temp[0];
				}

				if ($item_on_page_id) { // the content is a standard contact or a TCP contact page

					$query->select($db->quoteName('metakey'));
					$query->from($db->quoteName('#__contact_details'));
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
						return array(); // won't find a related contact if no key is present
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

					$related_id = $item_on_page_id;
				} else {
					return null; // no result (was not on contact page)
				}
			}

			// explode the meta keys on a comma
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

			// tags filtering

			$tags = $params->get('tags', array());
			$item_on_page_tagids = array();

			if ($selection == 'relatedbytags' || $selection == 'relatedcontactbytags') {

				if ($option == 'com_trombinoscopeextended' && $view == 'contact') { // because tags are recorded with com_contact
					$option = 'com_contact';
				}

				$get_the_tags = false;
				if ($selection == 'relatedcontactbytags' && $option == 'com_contact' && $view == 'contact') {
					$get_the_tags = true;
				} else if ($selection == 'relatedbytags') {
					$get_the_tags = true;
				}

				if ($get_the_tags) {
					$temp = $app->input->getString('id');
					$temp = explode(':', $temp);
					$item_on_page_id = $temp[0];

					if ($item_on_page_id) {
						$helper_tags = new TagsHelper();
						$tag_objects = $helper_tags->getItemTags($option.'.'.$view, $item_on_page_id);
						foreach ($tag_objects as $tag_object) {
							$item_on_page_tagids[] = $tag_object->tag_id;
						}
					}

					if (empty($item_on_page_tagids)) {
						return array(); // no result because no tag found for the object on the page
					}

					if ($option == 'com_contact' && $view == 'contact') { // we do get rid of the contact only if the related element is a contact
						$related_id = $item_on_page_id;
					}
				} else {
					return null; // no result (was not on contact page)
				}
			}

			if (!empty($tags)) {

				// if all selected, get all available tags
				$array_of_tag_values = array_count_values($tags);
				if (isset($array_of_tag_values['all']) && $array_of_tag_values['all'] > 0) { // 'all' was selected
					$tags = array();
					$tag_objects = SYWTags::getTags('com_contact.contact');
					if ($tag_objects !== false) {
						foreach ($tag_objects as $tag_object) {
							$tags[] = $tag_object->id;
						}
					}

					if (empty($tags) && $params->get('tags_inex', 1)) { // won't return any contact if no contact has been associated to any tag
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
			}
		}

		$user = Factory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		// START OF DATABASE QUERY

		$subquery1 = ' CASE WHEN ';
		$subquery1 .= $query->charLength('cd.alias');
		$subquery1 .= ' THEN ';
		$cd_id = $query->castAsChar('cd.id');
		$subquery1 .= $query->concatenate(array($cd_id, 'cd.alias'), ':');
		$subquery1 .= ' ELSE ';
		$subquery1 .= $cd_id.' END AS slug';

		$subquery2 = ' CASE WHEN ';
		$subquery2 .= $query->charLength('cc.alias');
		$subquery2 .= ' THEN ';
		$cc_id = $query->castAsChar('cc.id');
		$subquery2 .= $query->concatenate(array($cc_id, 'cc.alias'), ':');
		$subquery2 .= ' ELSE ';
		$subquery2 .= $cc_id.' END AS catslug';

		$subquery = $subquery1.','.$subquery2;

		// get only the fields we are interested in

		$fields_to_fetch = array();

		$detail_blocs = $params->get('detail_blocks'); // array of objects
		if (!empty($detail_blocs) && is_object($detail_blocs)) {
		    foreach ($detail_blocs as $i => $detail_bloc) {
		        if ($detail_bloc->f != 'none') {
	                $core_fields = self::getSelectedCoreFields($params, $detail_bloc->f);
		        	if (is_array($core_fields)) {
		            	$fields_to_fetch = array_merge($fields_to_fetch, $core_fields);
		        	} else if (!empty($core_fields)) {
		            	$fields_to_fetch[] = $core_fields;
		        	}
		    	}
			}
		}

		$detaillink_blocs = $params->get('detaillink_blocks'); // array of objects
		if (!empty($detaillink_blocs) && is_object($detaillink_blocs)) {
		    foreach ($detaillink_blocs as $i => $detaillink_bloc) {
		        if ($detaillink_bloc->lf != 'none') {
		            $core_fields = self::getSelectedCoreFields($params, $detaillink_bloc->lf);
		        	if (is_array($core_fields)) {
		            	$fields_to_fetch = array_merge($fields_to_fetch, $core_fields);
		        	} else if (!empty($core_fields)) {
		            	$fields_to_fetch[] = $core_fields;
		        	}
		    	}
			}
		}

		$query->select($db->quoteName(array('cd.id', 'cd.catid', 'cd.name', 'cc.title', 'cc.lft', 'cd.user_id', 'cd.featured', 'cd.image', 'cd.params', 'cd.language'), array('id', 'catid', 'name', 'category', 'c_order', 'user_id', 'featured', 'image', 'params', 'language')));
		$query->select($subquery);

		foreach (array_unique($fields_to_fetch) as $key => $core_field) {
		    $query->select($db->quoteName('cd.' . $core_field, $core_field));
		}

		$query->from($db->quoteName('#__contact_details', 'cd'));
		$query->join('INNER', '#__categories AS cc ON cd.catid = cc.id');

		$count = '';
		$startat = 1;

		if ($selection == 'contact') {
			$contact_id = $params->get('contact_id', '');
			if (!empty($contact_id)) {
				$query->where('cd.id='.$contact_id);
			} else {
				return null;
			}
// 		} else if ($selection == 'user') {
// 			if ($user->id > 0) {
// 				$query->where('cd.user_id='.$user->id);
// 			} else {
// 				return null;
// 			}
		} else {

			if ($selection == 'user') {
				if ($user->id > 0) {
					$query->where('cd.user_id='.$user->id);
				} else {
					return null;
				}
			}

			$count = trim($params->get('count', ''));
			$startat = $params->get('startat', 1);
			if ($startat < 1) {
				$startat = 1;
			}

			// filter by category

			$categories = self::getCategories($params->get('cat', array()), $params->get('includesubcat', 'no'), $params->get('levelsubcategories', 1));
			if ($categories != '') {
				$test_type = $params->get('cat_inex', 1) ? 'IN' : 'NOT IN';
				$query->where('cd.catid ' . $test_type . ' ('.$categories.')');
			}

			// include

			$contact_ids_include = trim($params->get('in', ''));
			if (!empty($contact_ids_include)) {
				$query->where('cd.id IN ('.$contact_ids_include.')');
			}

			// exclude

			$contact_ids_exclude = array_filter(explode(",", trim($params->get('ex', ''))));

			if (!empty($related_id)) {
				$contact_ids_exclude[] = $related_id;
			}

			if (!empty($contact_ids_exclude)) {
				$query->where('cd.id NOT IN ('.implode(",", $contact_ids_exclude).')');
			}

			// filter by metakeys

			if (!empty($metakeys)) {
				$concat_string = $query->concatenate(array('","', ' REPLACE(cd.metakey, ", ", ",")', ' ","')); // remove single space after commas in keywords
				$query->where('('.$concat_string.' LIKE "%'.implode('%" OR '.$concat_string.' LIKE "%', $metakeys).'%")');
			}

			// filter by tags

			if (!empty($tags)) {
				
				$tags_to_match = implode(',', $tags);
				
				$query->select('COUNT(tags.id) AS tags_count');
				$query->join('INNER', $db->quoteName('#__contentitem_tag_map', 'm').' ON '.$db->quoteName('m.content_item_id').' = '.$db->quoteName('cd.id').' AND '.$db->quoteName('m.type_alias').' = '.$db->quote('com_contact.contact'));
				$query->join('INNER', $db->quoteName('#__tags', 'tags') . ' ON '.$db->quoteName('m.tag_id').' = '.$db->quoteName('tags.id'));
				
				$query->where($db->quoteName('tags.access').' IN ('.$groups.')');
				$query->where($db->quoteName('tags.published').' = 1');
				
				$test_type = $params->get('tags_inex', 1) ? 'IN' : 'NOT IN';
				$query->where($db->quoteName('tags.id').' ' . $test_type . ' ('.$tags_to_match.')');
				
				if ($params->get('tags_inex', 1)) {
					if ($params->get('tags_match', 'any') == 'all') {
						$query->having('COUNT('.$db->quoteName('tags.id').') = '.count($tags));
					}
				} else {
					$query->select('tags_per_items.tag_count_per_item');
					
					// subquery gets all the tags for all items - VERY INNEFICIENT
					$subquery = 'SELECT mm.content_item_id AS content_id, COUNT(tt.id) AS tag_count_per_item FROM #__contentitem_tag_map AS mm';
					$subquery .= ' INNER JOIN #__tags AS tt ON mm.tag_id = tt.id';
					$subquery .= ' WHERE tt.access IN ('.$groups.') AND tt.published = 1 AND mm.type_alias = \'com_contact.contact\' GROUP BY content_id';
					
					$query->join('INNER', '(' . $subquery . ') AS tags_per_items ON tags_per_items.content_id = cd.id');
					
					// we keep items that have the same amount of tags before and after removals
					$query->having('COUNT('.$db->quoteName('tags.id').') = '.$db->quoteName('tags_per_items.tag_count_per_item'));
				}

				$query->group($db->quoteName('cd.id'));
			}

			// featured switch

			$featured = $params->get('f', 's');
			if ($featured == 'o') {
				$query->where('cd.featured = 1');
			} else if ($featured == 'h') {
				$query->where('cd.featured = 0');
			} else if ($featured == 'sf') {
				$query->order("cd.featured DESC");
			}

			// category order

			$catorder = $params->get('c_order', '');
			switch ($catorder) {
				case 'oa' : $query->order('cc.lft ASC'); break;
				case 'od' : $query->order('cc.lft DESC'); break;
				case 'na' : $query->order('cc.title ASC'); break;
				case 'nd' : $query->order('cc.title DESC'); break;
				default : break;
			}

			// general ordering

			$order = $params->get('order', 'oa');
			switch ($order) {
				case 'oa' : $query->order('cd.ordering ASC'); break;
				case 'od' : $query->order('cd.ordering DESC'); break;
				case 'na' : $query->order('cd.name ASC'); break;
				case 'nd' : $query->order('cd.name DESC'); break;
				case 'fnf_fa' : $query->order('cd.ordering ASC'); break;
				case 'fnf_fd' : $query->order('cd.ordering DESC'); break;
				case 'fnf_la' : $query->order('cd.ordering ASC'); break;
				case 'fnf_ld' : $query->order('cd.ordering DESC'); break;
				case 'random' : $query->order('rand()'); break;
				case 'manual' :
					$manual_order_ids = trim($params->get('manual_ids', '')); // TODO trim the commas as well? actually, do this in all fields with lists of elements with commas
					if (!empty($manual_order_ids)) {
						//$query->order('FIELD(cd.id, '.self::$manual_order_ids.')'); // MySQL specific

						$array_ids = explode(',', $manual_order_ids);
						$order = 'CASE cd.id';
						$i = 0;
						foreach ($array_ids as $id) {
							$order .= ' WHEN '.$id.' THEN '.$i++;
						}
						$order .= ' ELSE 999 END, cd.id';
						$query->order($order);
					} else {
						$query->order('cd.id ASC');
					}
					break;
				case 'sna' : $query->order($db->escape('cd.sortname1').' ASC');
				$query->order($db->escape('cd.sortname2').' ASC');
				$query->order($db->escape('cd.sortname3').' ASC');
				break;
				case 'snd' : $query->order($db->escape('cd.sortname1').' DESC');
				$query->order($db->escape('cd.sortname2').' DESC');
				$query->order($db->escape('cd.sortname3').' DESC');
				break;
				default : $query->order('cd.ordering ASC'); break;
			}
		}

		// access filter

		$query->where('cd.access IN ('.$groups.')');
		$query->where('cc.access IN ('.$groups.')');

		$query->where($db->quoteName('cc.published').' = 1');

		// date filter

		//$nullDate = $db->quote($db->getNullDate());
		$nowDate = $db->quote(Factory::getDate()->toSql());
		$query->where($db->quoteName('cd.published') . ' = 1');
		$query->where('(' . $db->quoteName('cd.publish_up') . ' IS NULL OR ' . $db->quoteName('cd.publish_up') . ' <= '.$nowDate.')');
		$query->where('(' . $db->quoteName('cd.publish_down') . ' IS NULL OR ' . $db->quoteName('cd.publish_down') . ' >= '.$nowDate.')');

		// language filter

		if ($params->get('filter_lang', 0) && $app->getLanguageFilter()) {
			$query->where('cd.language IN ('.$db->quote(Factory::getLanguage()->getTag()).','.$db->quote('*').')');
		}

		// launch query

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

		// ITEM DATA ADDITIONS

		$helper_tags = new TagsHelper();

		foreach ($items as $item) {
			$item->firstpart = self::_substring_index(trim($item->name), ' ', 1);
			$item->secondpart = self::_substring_index(self::_substring_index(trim($item->name), ' ', 2), ' ', -1);
			$item->lastpart = self::_substring_index(trim($item->name), ' ', -1);

			// keep original image (needed if showing picture in popup)
			$item->original_image = $item->image;

			if (self::isShowPicture($params)) {

				$picture_output = '';
				
				if ($item->image) {
					if (self::isCropPicture($params)) {
						$picture_output = self::getCroppedImage($module->id, $item->id, $item->image, self::getPictureTemporaryPath($params), self::IsClearPictureCache($params), self::getPictureWidth($params), self::getPictureHeight($params), self::isCropPicture($params), self::getPictureQuality($params), self::getPictureFilters($params), self::isCreateHighResolutionPicture($params));
					} else {
						$picture_output = (File::exists(JPATH_SITE . '/' . $item->image)) ? $item->image : 'error';
					}
				}
				
				if ($picture_output == 'error' || $picture_output == '') {
					$default_image = $params->get('d_pic', '');
					if ($default_image) {
						if (self::isCropPicture($params)) {
							$picture_output = self::getCroppedImage($module->id, 'default', $default_image, self::getPictureTemporaryPath($params), self::IsClearPictureCache($params), self::getPictureWidth($params), self::getPictureHeight($params), self::isCropPicture($params), self::getPictureQuality($params), self::getPictureFilters($params), self::isCreateHighResolutionPicture($params));
						} else {
							$picture_output = (File::exists(JPATH_SITE . '/' . $default_image)) ? $default_image : 'error';
						}
					}
				}
				
				if ($picture_output == 'error' || $picture_output == '') {
					$global_image = self::getContactGlobalParams()->get('default_image');
					if ($global_image) {
						if (self::isCropPicture($params)) {
							$picture_output = self::getCroppedImage($module->id, 'global', $global_image, self::getPictureTemporaryPath($params), self::IsClearPictureCache($params), self::getPictureWidth($params), self::getPictureHeight($params), self::isCropPicture($params), self::getPictureQuality($params), self::getPictureFilters($params), self::isCreateHighResolutionPicture($params));
						} else {
							$picture_output = (File::exists(JPATH_SITE . '/' . $global_image)) ? $global_image : 'error';
						}
					}
				}
				
				$item->image = $picture_output;

				if ($picture_output == 'error') {
					if (self::isCropPicture($params)) {
						$item->error[] = Text::_('MOD_TROMBINOSCOPE_ERROR_CREATINGTHUMBNAIL');
					} else {
						$item->error[] = Text::_('MOD_TROMBINOSCOPE_ERROR_RETRIEVINGIMAGE');
					}
					$item->image = '';
				}
			}

			// get tags

			$show_tags = $params->get('s_tag', 'h') != 'h' ? true : false;
			if ($show_tags) { // get the tags if needed for show
				$item->tags = $helper_tags->getItemTags('com_contact.contact', $item->id);
			}

			// get the individual background

			$individual_bg_option = $params->get('individual_bg_pic', '');
			if ($individual_bg_option) {
				if ($individual_bg_option == 'def_bg') { // default bg picture selected
					if ($params->get('d_bg_pic', '')) {
						$item->individual_bg = $params->get('d_bg_pic');
					}
				} else if ($individual_bg_option == 'pic') { // contact picture selected
					if (!empty($item->image)) {
						$item->individual_bg = $item->image;
					} else if ($params->get('d_bg_pic', '')) {
						$item->individual_bg = $params->get('d_bg_pic');
					}
				} else {
					$query->clear();

					$query->select($db->quoteName('value'));
					$query->from($db->quoteName('#__fields_values'));
					$query->where($db->quoteName('field_id').' = '.$individual_bg_option);
					$query->where($db->quoteName('item_id').' = '.$item->id);

					$db->setQuery($query);

					$item->individual_bg = '';
					try {
						$item->individual_bg = $db->loadResult();
					} catch (ExecutionFailureException $e) {
						Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
					}

					if (empty($item->individual_bg) && $params->get('d_bg_pic', '')) {
						$item->individual_bg = $params->get('d_bg_pic');
					}
				}
			}
		}

		// SPECIFIC ORDERING

		if ($selection != 'contact' && $selection != 'user') {

			$format_style = $params->get('name_fmt', 'none');
			if ($format_style != '') {
				if ($catorder == "oa") {
					switch ($order) {
						case 'fnf_fa' : // follow the name format - order on 1st part (asc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortCascSascFasc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortCascFascSasc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortCascLascFasc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortCascFascLasc"); break;
								default : break;
							}
							break;
						case 'fnf_fd' : // follow the name format - order on 1st part (desc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortCascSdescFdesc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortCascFdescSdesc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortCascLdescFdesc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortCascFdescLdesc"); break;
								default : break;
							}
							break;
						case 'fnf_la' : // follow the name format - order on 2nd part (asc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortCascFascSasc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortCascSascFasc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortCascFascLasc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortCascLascFasc"); break;
								default : break;
							}
							break;
						case 'fnf_ld' : // follow the name format - order on 2nd part (desc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortCascFdescSdesc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortCascSdescFdesc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortCascFdescLdesc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortCascLdescFdesc"); break;
								default : break;
							}
							break;
						default : break;
					}
				} else if ($catorder == "od") {
					switch ($order) {
						case 'fnf_fa' : // follow the name format - order on 1st part (asc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortCdescSascFasc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortCdescFascSasc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortCdescLascFasc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortCdescFascLasc"); break;
								default : break;
							}
							break;
						case 'fnf_fd' : // follow the name format - order on 1st part (desc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortCdescSdescFdesc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortCdescFdescSdesc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortCdescLdescFdesc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortCdescFdescLdesc"); break;
								default : break;
							}
							break;
						case 'fnf_la' : // follow the name format - order on 2nd part (asc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortCdescFascSasc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortCdescSascFasc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortCdescFascLasc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortCdescLascFasc"); break;
								default : break;
							}
							break;
						case 'fnf_ld' : // follow the name format - order on 2nd part (desc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortCdescFdescSdesc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortCdescSdescFdesc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortCdescFdescLdesc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortCdescLdescFdesc"); break;
								default : break;
							}
							break;
						default : break;
					}
				} else {
					switch ($order) {
						case 'fnf_fa' : // follow the name format - order on 1st part (asc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortSascFasc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortFascSasc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortLascFasc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortFascLasc"); break;
								default : break;
							}
							break;
						case 'fnf_fd' : // follow the name format - order on 1st part (desc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortSdescFdesc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortFdescSdesc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortLdescFdesc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortFdescLdesc"); break;
								default : break;
							}
							break;
						case 'fnf_la' : // follow the name format - order on 2nd part (asc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortFascSasc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortSascFasc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortFascLasc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortLascFasc"); break;
								default : break;
							}
							break;
						case 'fnf_ld' : // follow the name format - order on 2nd part (desc)
							switch ($format_style) {
								case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($items, "modTrombinoscopeHelper::sortFdescSdesc"); break;
								case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($items, "modTrombinoscopeHelper::sortSdescFdesc"); break;
								case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($items, "modTrombinoscopeHelper::sortFdescLdesc"); break;
								case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($items, "modTrombinoscopeHelper::sortLdescFdesc"); break;
								default : break;
							}
							break;
						default : break;
					}
				}
			}
		}

		return $items;
	}

	protected static function getSelectedCoreFields($params, $selected_field)
	{
	    switch ($selected_field) {
	        case 'c_p': return 'con_position';
	        case 'tel': return 'telephone';
	        case 'mob': return 'mobile';
	        case 'fax': return 'fax';
	        case 'mail': return 'email_to';
	        case 'web': return 'webpage';
	        case 'add': return 'address';
	        case 'sub': return 'suburb';
	        case 'st': return 'state';
	        case 'p_c': return 'postcode';
	        case 'cou': return 'country';
	        case 'misc':
	            if ($params->get('t', 'info') == 'info') { // take misc
	                return 'misc';
	            } else { // take metadescription
	                return 'metadesc';
	            }
	        case 'f_f_a':
	            switch ($params->get('a_fmt', 'zss')) {
	                case 'ssz' :
	                case 'zss' :
	                    return array('address', 'suburb', 'state', 'postcode');
	                case 'zs' :
	                case 'sz' :
	                    return array('address', 'suburb', 'postcode');
	                case 'ss' :
	                    return array('address', 'suburb', 'state');
	            }
	        default: return '';
	    }

	    return null;
	}

	/**
	 * Get the categories as a string for the sql query
	 */
	protected static function getCategories($categories_array, $get_sub_categories, $levels)
	{
		$categories = '';

		if (count($categories_array) > 0) {

			$array_of_category_values = array_count_values($categories_array);
			if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
				return $categories;
			}

			if ($get_sub_categories != 'no') {
				$categories_object = Categories::getInstance('Contact'); // new JCategories('com_contact');
				foreach ($categories_array as $category) {
					$category_object = $categories_object->get($category); // if category unpublished, unset
					if (isset($category_object) && $category_object->hasChildren()) {

						//if ($get_sub_categories == 'all') {
							$sub_categories_array = $category_object->getChildren(true); // true is for recursive
						//} else {
							//$sub_categories_array = $category_object->getChildren();
						//}

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
				$categories = implode(',', $categories_array);
			}
		}

		return $categories;
	}

	public static function getFormattedName($name, $style, $uppercase)
	{
		$formatted_name = $name;

		// case Olivier-Daniel Buisard Something Jr
		// firstpart -> Olivier-Daniel
		// remainingpart -> Buisard Something Jr
		$name_parts = explode(' ', $name);

		$firstpart = $name_parts[0];
		if ($uppercase) {
			$firstpart = ucfirst($firstpart);
		}

		unset($name_parts[0]);
		$remainingpart = '';
		if (count($name_parts) > 0) {
			if ($uppercase) {
				$remainingpart = ucwords(implode(' ', $name_parts)); // make sure there is no extra space when testing
			} else {
				$remainingpart = implode(' ', $name_parts);
			}
		}

		// case Buisard Something Jr Olivier-Daniel
		// lastpart -> Olivier-Daniel
		// previouspart -> Buisard Something Jr
		$name_parts = explode(' ', $name);
		$name_parts_length = count($name_parts);

		$lastpart = $name_parts[$name_parts_length - 1];
		if ($uppercase) {
			$lastpart = ucfirst($lastpart);
		}

		unset($name_parts[$name_parts_length - 1]);
		$previouspart = '';
		if (count($name_parts) > 0) {
			if ($uppercase) {
				$previouspart = ucwords(implode(' ', $name_parts)); // make sure there is no extra space when testing
			} else {
				$previouspart = implode(' ', $name_parts);
			}
		}

		switch ($style) {
			case 'rsf' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $remainingpart." ".$formatted_name;
				}
				break;
			case 'fsr' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $formatted_name." ".$remainingpart;
				}
				break;
			case 'rcf' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $remainingpart.", ".$formatted_name;
				}
				break;
			case 'fcr' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $formatted_name.", ".$remainingpart;
				}
				break;
			case 'psl' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $previouspart." ".$formatted_name;
				}
				break;
			case 'lsp' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $formatted_name." ".$previouspart;
				}
				break;
			case 'pcl' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $previouspart.", ".$formatted_name;
				}
				break;
			case 'lcp' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $formatted_name.", ".$previouspart;
				}
				break;
			case 'rsfd' :
				$formatted_name = substr($firstpart, 0, 1).'.';
				if (!empty($remainingpart)) {
					$formatted_name = $remainingpart." ".$formatted_name;
				}
				break;
			case 'fdsr' :
				$formatted_name = substr($firstpart, 0, 1).'.';
				if (!empty($remainingpart)) {
					$formatted_name = $formatted_name." ".$remainingpart;
				}
				break;
			case 'psld' :
				$formatted_name = substr($lastpart, 0, 1).'.';
				if (!empty($previouspart)) {
					$formatted_name = $previouspart." ".$formatted_name;
				}
				break;
			case 'ldsp' :
				$formatted_name = substr($lastpart, 0, 1).'.';
				if (!empty($previouspart)) {
					$formatted_name = $formatted_name." ".$previouspart;
				}
				break;
			case 'rdsf' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = substr($remainingpart, 0, 1).". ".$formatted_name;
				}
				break;
			case 'fsrd' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $formatted_name." ".substr($remainingpart, 0, 1).'.';
				}
				break;
			case 'pdsl' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = substr($previouspart, 0, 1).". ".$formatted_name;
				}
				break;
			case 'lspd' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $formatted_name." ".substr($previouspart, 0, 1).'.';
				}
				break;
			default :
				if ($uppercase) {
					$formatted_name = ucwords($formatted_name);
				}
				break;
		}

		return $formatted_name;
	}

	public static function getTooltipClass($show_tooltip = false)
	{
	    if ($show_tooltip) {
	        return ' hasTooltip';
	    }

	    return '';
	}

	public static function getTitleAttribute($title, $show_tooltip = false)
	{
	    if ($show_tooltip) {
	        return ' title="'.$title.'"';
	    }

	    return '';
	}

	public static function getClassAttribute($show_tooltip = false, $existing_classes = '')
	{
	    $classes = trim($existing_classes);

	    if ($show_tooltip) {
	        $classes .= ' hasTooltip';
	    }

	    if ($classes) {
	        return ' class="'.ltrim($classes).'"';
	    }

	    return '';
	}

	public static function getContactGlobalParams()
	{
		if (!isset(self::$contact_globals)) {

			self::$contact_globals = new Registry();

			$global_contact_params = ComponentHelper::getParams('com_contact');

			self::$contact_globals->set("linka_name", trim($global_contact_params->get('linka_name', '')));
			self::$contact_globals->set("linkb_name", trim($global_contact_params->get('linkb_name', '')));
			self::$contact_globals->set("linkc_name", trim($global_contact_params->get('linkc_name', '')));
			self::$contact_globals->set("linkd_name", trim($global_contact_params->get('linkd_name', '')));
			self::$contact_globals->set("linke_name", trim($global_contact_params->get('linke_name', '')));

			self::$contact_globals->set("default_image", $global_contact_params->get('image', ''));
		}

		return self::$contact_globals;
	}

	public static function renderName($params, $value, $extraclass = '')
	{
	    $html = '';

	    $show_label = ($params->get('s_name_lbl', 0) == 1) ? true : false;
	    $show_icon = ($params->get('s_name_lbl', 0) == 2) ? true : false;
	    $label_by_default = ($params->get('s_f_lbl', 0) == 1) ? true : false;
	    $icon_by_default = ($params->get('s_f_lbl', 0) == 2) ? true : false;
	    $label_separator = $params->get('lbl_separator', '');

	    $label = empty($params->get('name_lbl', '')) ? Text::_('MOD_TROMBINOSCOPE_LABEL_NAME') : $params->get('name_lbl', '');
	    $icon = empty($params->get('name_icon', '')) ? 'user' : $params->get('name_icon', '');

	    if (!$params->get('force_one_line', 1)) {
	    	$extraclass .= ' wrap';

	    	if ($params->get('wrap_pre', 1) == 0) {
	    		$extraclass .= ' alignself';
	    	}
	    } else {
	    	$extraclass .= ' nowrap';
	    }

	    if ($params->get('wrap_pre', 1) == 2) {
	    	$extraclass .= ' beneath';
	    }

	    $extraclass = trim($extraclass);

	    $html .= '<div class="personfield index0 fieldname' . ($extraclass ? ' ' . $extraclass : '') . '">';

	    if (Factory::getDocument()->getDirection() != 'rtl') {
	        if ($show_label) { // labels
	            $html .= '<span class="fieldlabel">'.$label.$label_separator.'</span>';
	        } else if ($show_icon) { // icons
	            $html .= '<i class="icon SYWicon-'.$icon.'" aria-hidden="true"></i>';
	        } else { // no icon or no label for the field
	            if ($label_by_default) { // force 'no label' even if there is one
	                $html .= '<span class="nolabel"></span>';
	            } else if ($icon_by_default) { // force 'no icon' even if one exists for the field
	                $html .= '<i class="noicon" aria-hidden="true"></i>';
	            }
	        }
	    }

	    $html .= $value;

	    if (Factory::getDocument()->getDirection() == 'rtl') {
	        if ($show_label) { // labels
	            $html .= '<span class="fieldlabel">'.$label.$label_separator.'</span>';
	        } else if ($show_icon) { // icons
	            $html .= '<i class="icon SYWicon-'.$icon.'" aria-hidden="true"></i>';
	        } else { // no icon or no label for the field
	            if ($label_by_default) { // force 'no label' even if there is one
	                $html .= '<span class="nolabel"></span>';
	            } else if ($icon_by_default) { // force 'no icon' even if one exists for the field
	                $html .= '<i class="noicon" aria-hidden="true"></i>';
	            }
	        }
	    }

	    $html .= '</div>';

	    return $html;
	}

	public static function getRequestedLinks($params, $prefix = '', $subform = '')
	{
		$links = array();

		$user = Factory::getUser();
		$groups	= $user->getAuthorisedViewLevels();

		// get data from subform items

		$detail_blocs = $params->get($prefix . ($subform ? $subform : 'detaillink_blocks')); // array of objects
		if (!empty($detail_blocs) && is_object($detail_blocs)) {
			$j = 0;
			foreach ($detail_blocs as $i => $detail_bloc) {
				$j++;
				if ($detail_bloc->lf != 'none' && in_array($detail_bloc->lf_access, $groups)) {

					$info_details = array();

					$info_details['name'] = $detail_bloc->lf; // name
					$info_details['show_what'] = 2;
					$info_details['label'] = '';
					$info_details['icon'] = $detail_bloc->lf_icon;
					$info_details['show_tooltip'] = true;
					$info_details['classes'] = '';

					$links[$j] = $info_details;
				}
			}
		}

		return $links;
	}

	public static function renderLink($params, $item, $index, $requested_link, $extraclass = '')
	{
	    return self::getFieldOutput($index, $requested_link, $params, $item, $extraclass, true);
	}

	public static function getRequestedInfos($params, $prefix = '', $subform = '')
	{
		$infos = array();

		$user = Factory::getUser();
		$groups	= $user->getAuthorisedViewLevels();

		// get data from subform items

		$detail_blocs = $params->get($prefix . ($subform ? $subform : 'detail_blocks')); // array of objects
		if (!empty($detail_blocs) && is_object($detail_blocs)) {
			$j = 0;
			foreach ($detail_blocs as $i => $detail_bloc) {
				$j++;
				if ($detail_bloc->f != 'none' && in_array($detail_bloc->f_access, $groups)) {

					$info_details = array();

					$info_details['name'] = $detail_bloc->f; // name
					$info_details['show_what'] = $detail_bloc->s_f_lbl;
					$info_details['label'] = $detail_bloc->f_lbl;
					$info_details['icon'] = $detail_bloc->f_icon;
					$info_details['show_tooltip'] = $detail_bloc->f_tooltip == 1 ? true : false;
					$info_details['one_line'] = (!isset($detail_bloc->f_one_line) || (isset($detail_bloc->f_one_line) && $detail_bloc->f_one_line == 1)) ? true : false;
					$info_details['classes'] = isset($detail_bloc->f_classes) ? $detail_bloc->f_classes : '';

					$infos[$j] = $info_details;
				}
			}
		}

		return $infos;
	}

	public static function renderInfo($params, $item, $index, $requested_info, $extraclass = '')
	{
	    return self::getFieldOutput($index, $requested_info, $params, $item, $extraclass);
	}

	public static function getFieldOutput($index, $info_details, $params, $item, $extraclass = '', $iconlinkonly = false)
	{
		// restricted access

		// 		$user = Factory::getUser();
		// 		$groups	= $user->getAuthorisedViewLevels();
		// 		if (!in_array($fieldaccess, $groups)) {
		// 			return $html;
		// 		}

		$html = '';

		$fieldname = $info_details['name'];
		$prefield = $info_details['show_what'];
		$fieldlabel = $info_details['label'];
		$fieldicon = $info_details['icon'];
		$fieldtooltip = $info_details['show_tooltip'];

		if (!$iconlinkonly) {
			if (!$info_details['one_line']) {
				$extraclass .= ' wrap';

				if ($params->get('wrap_pre', 1) == 0) {
					$extraclass .= ' alignself';
				}
			} else {
				$extraclass .= ' nowrap';
			}

			if ($params->get('wrap_pre', 1) == 2) {
				$extraclass .= ' beneath';
			}
		}

		$extraclass .= ' ' . trim($info_details['classes']);
		$extraclass = trim($extraclass);

		$item_params = new Registry();
		$item_params->loadString($item->params);

		$value = '';
		$substitute_value = '';
		$value_is_link = false;
		//$show_link = false;
		$target = '';
		$label = '';
		//$title = '';
		$class = '';
		$icon_class = '';
		$generated_link_tag = '';

		switch ($fieldname) {
			case 'empty' :
				$class = 'empty';
				break;

			case 'c_p' : // con_position
			    $value = trim($item->con_position);
			    $class = 'fieldposition';
			    if ($value) {
    			    if (strpos($value, 'POSITION_') !== false) {
    			        $field_array = explode(',', $value);
    					$field_array_fixed = array();
    					$last_field = '';
    					foreach ($field_array as $field_element) {
    						$field_array_fixed[] = Text::_(trim($field_element));
    					}
    					$count = count($field_array);
    					if ($count > 1) {
    						$last_field = $field_array_fixed[$count - 1];
    						unset($field_array_fixed[$count - 1]);
    						$value = implode(', ', $field_array_fixed);
    						$value .= Text::_('TROMBINOSCOPEEXTENDED_AND').' '.$last_field;
    					} else {
    					    $value = Text::_($value);
    					}
    				}
    				$label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_POSITION') : $fieldlabel;
    				$icon_class = !empty($fieldicon) ? $fieldicon : 'briefcase';
			    }
				break;

			case 'tel' : // telephone
			    $value = trim($item->telephone);
			    $class = 'fieldtel';
			    if ($value) {
    				if (SYWUtilities::isMobile()) {
    					$value_is_link = true;
    					//$show_link = true;
    					$substitute_value = $value;
    					//$title = $value;
    					$value = 'tel:'.$value;
    				}
    				$label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_TELEPHONE') : $fieldlabel;
    				$icon_class = !empty($fieldicon) ? $fieldicon : 'phone';
			    }
				break;

			case 'mob' : // mobile
			    $value = trim($item->mobile);
			    $class = 'fieldmobile';
			    if ($value) {
    				if (SYWUtilities::isMobile()) {
    					$value_is_link = true;
    					//$show_link = true;
    					$substitute_value = $value;
    					//$title = $value;
    					$value = 'tel:'.$value;
    				}
    				$label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_MOBILE') : $fieldlabel;
    				$icon_class = !empty($fieldicon) ? $fieldicon : 'mobile';
			    }
				break;

			case 'fax' : // fax
			    $value = trim($item->fax);
			    $class = 'fieldfax';
			    if ($value) {
			        if (SYWUtilities::isMobile()) {
			            $value_is_link = true;
			            //$show_link = true;
			            $substitute_value = $value;
			            //$title = $value;
			            $value = 'tel:'.$value;
			        }
    				$label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_FAX') : $fieldlabel;
    				$icon_class = !empty($fieldicon) ? $fieldicon : 'fax';
			    }
				break;

			case 'mail' : // email_to
			    $initial_value = trim($item->email_to);
			    $class = 'fieldemail';

			    if (trim($params->get('e_substitut', '')) == '') {
			    	$class .= ' breakall';
			    }

			    if ($initial_value) {

			        $label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_EMAIL') : $fieldlabel;
			        $icon_class = !empty($fieldicon) ? $fieldicon : 'mail';

    			    $substitute_value = (trim($params->get('e_substitut', '')) == '') ? $initial_value : $params->get('e_substitut', '');
    				switch ($params->get('link_e', 1)) {
    					case 1: // mailto
					        $value = 'mailto:'.$initial_value;
					        //$title = $initial_value;
							$value_is_link = true;
							$target = '_blank';
							//$show_link = true;
							if ($params->get('cloak_e', false)) {
							    $generated_link_tag = '<span class="fieldvalue'.self::getTooltipClass($fieldtooltip).'" aria-label="'.$label.'" '.self::getTitleAttribute($label, $fieldtooltip).'>';
								if ($params->get('e_substitut', '') != '') {
								    $generated_link_tag .= HTMLHelper::_('email.cloak', $initial_value, true, $params->get('e_substitut', ''), false);
								} else {
								    $generated_link_tag .= HTMLHelper::_('email.cloak', $initial_value);
								}
								$generated_link_tag .= '</span>';
							}
    						break;
    					case 2: // contact
							$value_is_link = true;
							//$show_link = true;
							//$title = Text::_('MOD_TROMBINOSCOPE_LABEL_EMAIL');
							$value = Route::_(ContactRouteHelper::getContactRoute($item->slug, $item->catid, $item->language));
    						break;
    					default: // no link
    						if (!$iconlinkonly) {
    						    $value = $initial_value;
    						}
    				}
			    }
				break;

			case 'web' : // webpage
			    $value = trim($item->webpage);
			    $class = 'fieldwebpage';

			    if (trim($params->get('w_substitut', '')) == '') {
			    	$class .= ' breakall';
			    }

			    if ($value) {
    				$value_is_link = true;
    				//$show_link = true;

    				if (!Uri::isInternal($value)) {
    				    $target = '_blank';
					}

					if (!$params->get('protocol', true)) {
						//$title = self::remove_protocol($value);
					    $substitute_value = (trim($params->get('w_substitut', '')) == '') ? self::remove_protocol($value) : $params->get('w_substitut', '');
					} else {
						//$title = $value;
						$substitute_value = (trim($params->get('w_substitut', '')) == '') ? $value : $params->get('w_substitut', '');
					}

    				$label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_WEBPAGE') : $fieldlabel;
    				$icon_class = !empty($fieldicon) ? $fieldicon : 'earth';
			    }
				break;

			case 'add' : // address
			    $value = trim($item->address, ", \t\n\r\0\x0B"); // single quotes won't work
			    $class = 'fieldaddress';
			    if ($value) {
    				//$title = $value;
    				$value = nl2br($value);
    				$label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_ADDRESS') : $fieldlabel;
    				$icon_class = !empty($fieldicon) ? $fieldicon : 'home';
			    }
				break;

			case 'f_f_a' : // address + zipcode... formatted

			    $address = trim($item->address, ", \t\n\r\0\x0B");
			    if ($address) {
			        $address .= "\n";
			    }
			    switch ($params->get('a_fmt', 'ssz')) {
			        case 'ssz' :
			            $value = $address . trim($item->suburb) . (trim($item->state) == '' ? '' : ', ' . trim($item->state)) . ' ' . trim($item->postcode);
			            break;
			        case 'zss' :
			            $value = $address . trim($item->postcode) . (trim($item->suburb) == '' ? '' : ' ' . trim($item->suburb)) . ', ' . trim($item->state);
			            break;
			        case 'zs' :
			            $value = $address . trim($item->postcode) . ' ' . trim($item->suburb);
			            break;
			        case 'sz' :
			            $value = $address . trim($item->suburb) . ' ' . trim($item->postcode);
			            break;
			        case 'ss' :
			            $value = $address . trim($item->suburb) . ', ' . trim($item->state);
			            break;
			        default :
			            $value = '';
			    }

			    $value = trim($value, ", \t\n\r\0\x0B");

			    $class = 'fieldformattedaddress';
			    if ($value) {

			        $label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_FORMATTEDADDRESS') : $fieldlabel;
			        $icon_class = !empty($fieldicon) ? $fieldicon : 'home';

    				//$title = $value;
			        if ($params->get('a_link_map', 0) == 1) { // auto
			            $substitute_value = nl2br($value);
						$value = self::getAutoMapLink($value, trim($params->get('auto_map_params', '')));
						$value_is_link = true;
						$target = '_blank';
						//$generated_link_tag = '<a class="fieldvalue'.self::getTooltipClass($fieldtooltip).'" href="'.$mapvalue.'" target="_blank"'.self::getTitleAttribute($label, $fieldtooltip).'>'.nl2br($value).'</a>';
					} else {
						$value = nl2br($value);
					}
			    }
				break;

			case 'sub' : // suburb
			    $value = trim($item->suburb);
			    $class = 'fieldsuburb';
			    if ($value) {
			        $label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_SUBURB') : $fieldlabel;
			        $icon_class = !empty($fieldicon) ? $fieldicon : '';
			    }
				break;

			case 'st' : // state
			    $value = trim($item->state);
			    $class = 'fieldstate';
			    if ($value) {
			        $label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_STATE') : $fieldlabel;
			        $icon_class = !empty($fieldicon) ? $fieldicon : '';
			    }
				break;

			case 'p_c' : // postcode
			    $value = trim($item->postcode);
			    $class = 'fieldpostcode';
			    if ($value) {
			        $label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_POSTCODE') : $fieldlabel;
			        $icon_class = !empty($fieldicon) ? $fieldicon : '';
			    }
				break;

			case 'cou' : // country
			    $value = trim($item->country);
			    $class = 'fieldcountry';
			    if ($value) {
				    $label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_COUNTRY') : $fieldlabel;
				    $icon_class = !empty($fieldicon) ? $fieldicon : 'flag2';
			    }
				break;

			case 'misc' :

				$letter_count = trim($params->get('l_c', ''));
				$number_of_letters = -1;
				if ($letter_count != '') {
					$number_of_letters = (int)$letter_count;
				}
				if ($params->get('t', 'info') == 'info') {
					$value = SYWText::getText(trim($item->misc), 'html', $number_of_letters, $params->get('s_t', 1), trim($params->get('keep_tags', '')), !$params->get('process_miscinfo', 0), $params->get('trunc_l_w', 0));
				} else {
					$value = SYWText::getText(trim($item->metadesc), 'txt', $number_of_letters, false, '', true, $params->get('trunc_l_w', 0));
				}
				$class = 'fieldmisc';

				if ($value) {
    				$label = empty($fieldlabel) ? Text::_('MOD_TROMBINOSCOPE_LABEL_MISC') : $fieldlabel;
    				//$title = $label;
    				$icon_class = !empty($fieldicon) ? $fieldicon : 'info';
				}
				break;

			case 'a': case 'b': case 'c': case 'd': case 'e': // links a .. e
			case 'a_sw': case 'b_sw': case 'c_sw': case 'd_sw': case 'e_sw':
				$value = trim($item_params->get('link' . str_replace('_sw', '', $info_details['name']), ''));
				$class = 'fieldlink' . str_replace('_sw', '', $info_details['name']);
				if ($value) {
				    if (!$params->get('protocol', true)) {
				        $substitute_value = self::remove_protocol($value);
				    }

				    $value_is_link = true;
				    $label = empty($fieldlabel) ? ($params->get('linkae_l_as_s', 0) ? Text::_('MOD_TROMBINOSCOPE_LABEL_LINK') : self::getLabelForLink('link' . str_replace('_sw', '', $info_details['name']), $value, $item_params, false)) : $fieldlabel;
				    $icon_class = !empty($fieldicon) ? $fieldicon : self::getIconForLink($value);

				    if (strpos($info_details['name'], '_sw') === false) {
				        $target = '_blank';
				    }

				    if ($params->get('linkae_l_as_s', 0)) {
				    	$linkX_label = self::getLabelForLink('link' . str_replace('_sw', '', $info_details['name']), $value, $item_params, true);
					    if ($linkX_label) {
					    	$substitute_value = $linkX_label;
					    }
				    }
				}
				break;
		}

		// html code building

		if ($iconlinkonly) {
			if ($value) {
			    $html .= '<li class="iconlink index'.$index.' '.$class.($extraclass ? ' '.$extraclass : '').'">';
			     $html .= '<a class="fieldvalue'.self::getTooltipClass($fieldtooltip).'" aria-label="'.$label.'" href="'.$value.'"'.($target ? ' target="'.$target.'"' : '').self::getTitleAttribute($label, $fieldtooltip).'>';
                        $html .= '<i class="icon SYWicon-'.$icon_class.'" aria-hidden="true"></i>';
                        $html .= '<span>'.($substitute_value ? $substitute_value : $value).'</span>'; // hidden
    				$html .= '</a>';
				$html .= '</li>';
			}
		} else {
			if (!$params->get('k_s', 1) && empty($value) && $class != 'empty') {
				return '';
			} else {
			    $html .= '<div class="personfield index'.$index.' '.$class.($extraclass ? ' '.$extraclass : '').'">';
				if (empty($value)) {
				    $html .= '<span>&nbsp;</span>';
				} else {

				    if (Factory::getDocument()->getDirection() != 'rtl') {
    					if ($prefield == 1) { // labels
    						$html .= '<span class="fieldlabel">'.$label.$params->get('lbl_separator', '').'</span>';
    					} else if ($prefield == 2) { // icons
    						if (!empty($icon_class)) {
    							$html .= '<i class="icon SYWicon-'.$icon_class.'" aria-hidden="true"></i>';
    						} else {
    							$html .= '<i class="noicon" aria-hidden="true"></i>';
    						}
    					} else { // no icon or no label for the field
    						if ($params->get('s_f_lbl', 0) == 1 && $class != 'fieldname') { // force 'no label' even if there is one
    							$html .= '<span class="nolabel"></span>';
    						} else if ($params->get('s_f_lbl', 0) == 2 && $class != 'fieldname') { // force 'no icon' even if one exists for the field
    							$html .= '<i class="noicon" aria-hidden="true"></i>';
    						}
    					}
				    }

					if ($value_is_link) {
						if (!empty($generated_link_tag)) {
							$html .= $generated_link_tag;
						} else {
							//if ($show_link) {
								//$label_as_title = empty($title) ? $label : $title;
                                $html .= '<a class="fieldvalue'.self::getTooltipClass($fieldtooltip).'" aria-label="'.$label.'" href="'.$value.'"'.($target ? ' target="'.$target.'"' : '').self::getTitleAttribute($label/*$label_as_title*/, $fieldtooltip).'>';
                                    $html .= '<span>'.($substitute_value ? $substitute_value : $value).'</span>';
								$html .= '</a>';
							//} else {
								//$value_as_title = empty($title) ? $value : $title;
								//$html .= '<a class="fieldvalue'.self::getTooltipClass($fieldtooltip).'" href="'.$value.'"'.($target ? ' target="'.$target.'"' : '').self::getTitleAttribute($value_as_title, $fieldtooltip).'>';
                                    //$html .= '<span>'.$label.'</span>';
								//$html .= '</a>';
							//}
						}
					} else {
						//$value_as_title = empty($title) ? $value : $title;
					    $html .= '<span class="fieldvalue'.self::getTooltipClass($fieldtooltip).'" aria-label="'.$label.'"'.self::getTitleAttribute($label/*$value_as_title*/, $fieldtooltip).'>'.($substitute_value ? $substitute_value : $value).'</span>';
					}

					if (Factory::getDocument()->getDirection() == 'rtl') {
					    if ($prefield == 1) { // labels
					        $html .= '<span class="fieldlabel">'.$label.$params->get('lbl_separator', '').'</span>';
					    } else if ($prefield == 2) { // icons
					        if (!empty($icon_class)) {
					            $html .= '<i class="icon SYWicon-'.$icon_class.'" aria-hidden="true"></i>';
					        } else {
					            $html .= '<i class="noicon" aria-hidden="true"></i>';
					        }
					    } else { // no icon or no label for the field
					        if ($params->get('s_f_lbl', 0) == 1 && $class != 'fieldname') { // force 'no label' even if there is one
					            $html .= '<span class="nolabel"></span>';
					        } else if ($params->get('s_f_lbl', 0) == 2 && $class != 'fieldname') { // force 'no icon' even if one exists for the field
					            $html .= '<i class="noicon" aria-hidden="true"></i>';
					        }
					    }
					}
				}

				$html .= '</div>';
			}
		}

		return $html;
	}

	protected static $social_networks_labels = array('facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'twitter' => 'Twitter', 'plus.google' => 'Google+', 'instagram' => 'Instagram', 'tumblr' => 'Tumblr', 'pinterest' => 'Pinterest', 'youtube' => 'YouTube', 'vimeo' => 'Vimeo', 'wordpress' => 'Wordpress', 'skype' => 'Skype', 'blogspot' => 'Blogger');

	public static function getLabelForLink($field, $link, $params, $is_substitute)
	{
		$label = trim($params->get($field.'_name'));

		if ($label) {
			return $label;
		} else {
		    $globalparams = self::getContactGlobalParams();
		    if ($globalparams->get($field.'_name')) {
		        return $globalparams->get($field.'_name');
		    }
		}

		foreach (self::$social_networks_labels as $key => $value) {
		    if (strpos($link, $key) > 0) {
		        return $value;
			}
		}

		if ($is_substitute) {
			return '';
		}

		return Text::_('MOD_TROMBINOSCOPE_LABEL_LINK');
	}

	protected static $social_networks_icons = array('facebook' => 'facebook', 'linkedin' => 'linkedin', 'twitter' => 'twitter', 'plus.google' => 'googleplus', 'instagram' => 'instagram', 'tumblr' => 'tumblr', 'pinterest' => 'pinterest', 'youtube' => 'youtube', 'vimeo' => 'vimeo', 'wordpress' => 'wordpress', 'skype' => 'skype', 'blogspot' => 'blogger');

	public static function getIconForLink($link)
	{
		foreach (self::$social_networks_icons as $key => $value) {
	        if (strpos($link, $key) > 0) {
	            return $value;
	        }
	    }

		return 'earth';
	}

	/**
	 * Create the cropped image
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
	 * @return the thumbnail path if no error, 'error' if error, the original path otherwise if conditions are not met to create the thumbnail
	 */
	public static function getCroppedImage($module_id, $item_id, $imagesrc, $tmp_path, $clear_cache, $head_width, $head_height, $crop_picture, $quality, $filter, $create_highres_images = false)
	{
		$extensions = get_loaded_extensions();
		if (!in_array('gd', $extensions)) {
			return $imagesrc;
		}

// 		$imageext = explode('.', $imagesrc);
// 		$imageext = $imageext[count($imageext) - 1];
// 		$imageext = strtolower($imageext);

		$imageext = File::getExt($imagesrc);

		$filename = $tmp_path.'/thumb_'.$module_id.'_'.$item_id.'.'.$imageext;
		$filename_highres = $tmp_path.'/thumb_'.$module_id.'_'.$item_id.'@2x.'.$imageext;
		if ((!$clear_cache && !$create_highres_images && File::exists(JPATH_ROOT . '/' . $filename))
			|| (!$clear_cache && $create_highres_images && File::exists(JPATH_ROOT . '/' . $filename) && File::exists(JPATH_ROOT . '/' . $filename_highres))) {

			// thumbnail already exists

		} else { // create the thumbnail

			$image = new SYWImage($imagesrc);

			if (is_null($image->getImagePath())) {
				return 'error';
			} else if (is_null($image->getImageMimeType())) {
				return 'error';
			} else if (is_null($image->getImage()) || $image->getImageWidth() == 0) {
				return 'error';
			} else {

				// START find image compression plugin

				$compression_plugin = null;
				$compression_plugins_exist = PluginHelper::importPlugin('imagecompression');

				if ($compression_plugins_exist) {

					$plugins_available_for_compression = Factory::getApplication()->triggerEvent('onImageCompressionCheckAvailability', array($imageext, true));

					foreach ($plugins_available_for_compression as $plugin_available_for_compression) {

						$plugin_name = array_keys($plugin_available_for_compression)[0];
						$available = $plugin_available_for_compression[$plugin_name];

						if ($available) {
							$filename_temp = $tmp_path.'/thumb_temp_'.$module_id.'_'.$item_id.'.'.$imageext;
							$filename_highres_temp = $tmp_path.'/thumb_temp_'.$module_id.'_'.$item_id.'@2x.'.$imageext;

							$plugin = PluginHelper::getPlugin('imagecompression', $plugin_name);
							$classname = 'plgImageCompression'.$plugin_name;
							$compression_plugin = new $classname($dispatcher, (array) $plugin);

							break;
							// uses the first plugin that returns true - useful if a plugin is limited (reached a limit of use)
							// therefore the order of the plugins is important
						}
					}
				}

				// END find image compression plugin

				$filters_output = array();

				$filters = $filter["filters"];
				foreach ($filters as $filter) {

					switch ($filter) {
						case 'sepia': $filters_output[] = IMG_FILTER_GRAYSCALE; $filters_output[] = array('type' => IMG_FILTER_COLORIZE, 'arg1' => 70, 'arg2' => 35, 'arg3' => 0); break;
						case 'grayscale': $filters_output[] = IMG_FILTER_GRAYSCALE; break;
						case 'sketch': $filters_output[] = IMG_FILTER_MEAN_REMOVAL; break;
						case 'negate': $filters_output[] = IMG_FILTER_NEGATE; break;
						case 'emboss': $filters_output[] = IMG_FILTER_EMBOSS; break;
						case 'edgedetect': $filters_output[] = IMG_FILTER_EDGEDETECT; break;

						//case 'duotone': $filters_output[] = array('type' => IMG_FILTER_COLORIZE, 'arg1' => 20, 'arg2' => 60, 'arg3' => 230); break; // needs color
						case 'blur': $filters_output[] = IMG_FILTER_GAUSSIAN_BLUR; break;
						//case 'pixelate': $filters_output[] = array('type' => IMG_FILTER_PIXELATE, 'arg1' => 8, 'arg2' => true); break; // needs pixel box size
						case 'sharpen': $filters_output[] = array('type' => IMG_FILTER_SMOOTH, 'arg1' => -9); break;

						default: // nothing
					}
				}

				if (!is_null($compression_plugin)) {
					$creation_success = $image->toThumbnail($filename_temp, '', $head_width, $head_height, $crop_picture, $quality, $filters_output, $create_highres_images);
					if ($creation_success && $image->getImageMimeType() === 'image/webp') {
						$creation_success = $image->toThumbnail($tmp_path.'/thumb_temp_'.$module_id.'_'.$item_id.'.png', 'image/png', $head_width, $head_height, $crop_picture, $quality, $filters_output, $create_highres_images);
					}
				} else {
					$creation_success = $image->toThumbnail($filename, '', $head_width, $head_height, $crop_picture, $quality, $filters_output, $create_highres_images);
					if ($creation_success && $image->getImageMimeType() === 'image/webp') {
						$creation_success = $image->toThumbnail($tmp_path.'/thumb_'.$module_id.'_'.$item_id.'.png', 'image/png', $head_width, $head_height, $crop_picture, $quality, $filters_output, $create_highres_images);
					}
				}

				if (!$creation_success) {
					return 'error';
				}

				// START image compression

				if (!is_null($compression_plugin)) {

					//$optimization_success = $dispatcher->trigger('onImageCompressionCompress', array($filename_temp, $filename));
					$optimization_success = $compression_plugin->onImageCompressionCompress($filename_temp, $filename);
					if ($optimization_success) {
						//$dispatcher->trigger('onImageCompressionSuccess', array(false));
						$compression_plugin->onImageCompressionSuccess(false);
					} else {
						//$dispatcher->trigger('onImageCompressionFailure');
						$compression_plugin->onImageCompressionFailure();
					}

					if ($create_highres_images) {
						//$optimization_highres_success = $dispatcher->trigger('onImageCompressionCompress', array($filename_highres_temp, $filename_highres));
						$optimization_highres_success = $compression_plugin->onImageCompressionCompress($filename_highres_temp, $filename_highres);
						if ($optimization_highres_success) {
							//$dispatcher->trigger('onImageCompressionSuccess', array(false));
							$compression_plugin->onImageCompressionSuccess(false);
						} else {
							//$dispatcher->trigger('onImageCompressionFailure');
							$compression_plugin->onImageCompressionFailure();
						}
					}
				}

				// END image compression
			}

			$image->destroy();
		}

		return $filename;
	}

	public static function remove_protocol($url)
	{
		$disallowed = array('http://', 'https://');
		foreach($disallowed as $d) {
			if(strpos($url, $d) === 0) {
				return str_replace($d, '', $url);
			}
		}
		return $url;
	}

	public static function getAutoMapLink($address, $params = '', $embed = false) {

		$address_array = explode("\n", $address);
		$address = '';
		foreach ($address_array as $address_line) {
			$address_line = str_replace(',', ' ', $address_line);
			$address_line = str_replace('#', ' ', $address_line);
			$address .= trim($address_line, ", \t\n\r\0\x0B").' ';
		}

		$address = trim($address, ", \t\n\r\0\x0B");

		$address = preg_replace('/\s+/', ' ', $address); // to replace multiple occurences of white space into one

		$url = 'https://maps.google.com/maps?q='.urlencode($address);

		if (!empty($params)) {
			$url .= '&'.$params;
		}

		if ($embed) {
			$url .= '&output=embed';
		}

		return $url;
	}

	protected static function compare($a, $b) {
		if (class_exists('Collator') && self::$sort_locale !== 'en_US' && self::$sort_locale !== 'en_GB') { // needs php_intl
			$collator = new \Collator(self::$sort_locale);
			return $collator->compare($a, $b);
		} else {
			return ($a < $b) ? -1 : 1;
		}
	}

	/* sort by category ASC lastpart ASC firstpart ASC */
	protected static function sortCascLascFasc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->lastpart == $b->lastpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart < $b->firstpart) ? -1 : 1;
				return self::compare($a->firstpart, $b->firstpart);
			}
			//return ($a->lastpart < $b->lastpart) ? -1 : 1;
			return self::compare($a->lastpart, $b->lastpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}

	/* sort by category ASC by lastpart DESC firstpart DESC */
	protected static function sortCascLdescFdesc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->lastpart == $b->lastpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart > $b->firstpart) ? -1 : 1;
				return self::compare($b->firstpart, $a->firstpart);
			}
			//return ($a->lastpart > $b->lastpart) ? -1 : 1;
			return self::compare($b->lastpart, $a->lastpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}

	/* sort by category ASC by firstpart ASC lastpart ASC */
	protected static function sortCascFascLasc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->lastpart == $b->lastpart) {
					return 0;
				}
				//return ($a->lastpart < $b->lastpart) ? -1 : 1;
				return self::compare($a->lastpart, $b->lastpart);
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}

	/* sort by category ASC by firstpart DESC lastpart DESC */
	protected static function sortCascFdescLdesc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->lastpart == $b->lastpart) {
					return 0;
				}
				//return ($a->lastpart > $b->lastpart) ? -1 : 1;
				return self::compare($b->lastpart, $a->lastpart);
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}

	/* sort by category ASC by secondpart ASC firstpart ASC */
	protected static function sortCascSascFasc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->secondpart == $b->secondpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart < $b->firstpart) ? -1 : 1;
				return self::compare($a->firstpart, $b->firstpart);
			}
			//return ($a->secondpart < $b->secondpart) ? -1 : 1;
			return self::compare($a->secondpart, $b->secondpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}

	/* sort by category ASC by secondpart DESC firstpart DESC */
	protected static function sortCascSdescFdesc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->secondpart == $b->secondpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart > $b->firstpart) ? -1 : 1;
				return self::compare($b->firstpart, $a->firstpart);
			}
			//return ($a->secondpart > $b->secondpart) ? -1 : 1;
			return self::compare($b->secondpart, $a->secondpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}

	/* sort by category ASC by firstpart ASC secondpart ASC */
	protected static function sortCascFascSasc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->secondpart == $b->secondpart) {
					return 0;
				}
				//return ($a->secondpart < $b->secondpart) ? -1 : 1;
				return self::compare($a->secondpart, $b->secondpart);
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}

	/* sort by category ASC by firstpart DESC secondpart DESC */
	protected static function sortCascFdescSdesc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->secondpart == $b->secondpart) {
					return 0;
				}
				//return ($a->secondpart > $b->secondpart) ? -1 : 1;
				return self::compare($b->secondpart, $a->secondpart);
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}

	/* sort by category DESC lastpart ASC firstpart ASC */
	protected static function sortCdescLascFasc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->lastpart == $b->lastpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart < $b->firstpart) ? -1 : 1;
				return self::compare($a->firstpart, $b->firstpart);
			}
			//return ($a->lastpart < $b->lastpart) ? -1 : 1;
			return self::compare($a->lastpart, $b->lastpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}

	/* sort by category DESC by lastpart DESC firstpart DESC */
	protected static function sortCdescLdescFdesc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->lastpart == $b->lastpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart > $b->firstpart) ? -1 : 1;
				return self::compare($b->firstpart, $a->firstpart);
			}
			//return ($a->lastpart > $b->lastpart) ? -1 : 1;
			return self::compare($b->lastpart, $a->lastpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}

	/* sort by category DESC by firstpart ASC lastpart ASC */
	protected static function sortCdescFascLasc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->lastpart == $b->lastpart) {
					return 0;
				}
				//return ($a->lastpart < $b->lastpart) ? -1 : 1;
				return self::compare($a->lastpart, $b->lastpart);
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}

	/* sort by category DESC by firstpart DESC lastpart DESC */
	protected static function sortCdescFdescLdesc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->lastpart == $b->lastpart) {
					return 0;
				}
				//return ($a->lastpart > $b->lastpart) ? -1 : 1;
				return self::compare($b->lastpart, $a->lastpart);
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}

	/* sort by category DESC by secondpart ASC firstpart ASC */
	protected static function sortCdescSascFasc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->secondpart == $b->secondpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart < $b->firstpart) ? -1 : 1;
				return self::compare($a->firstpart, $b->firstpart);
			}
			//return ($a->secondpart < $b->secondpart) ? -1 : 1;
			return self::compare($a->secondpart, $b->secondpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}

	/* sort by category DESC by secondpart DESC firstpart DESC */
	protected static function sortCdescSdescFdesc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->secondpart == $b->secondpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart > $b->firstpart) ? -1 : 1;
				return self::compare($b->firstpart, $a->firstpart);
			}
			//return ($a->secondpart > $b->secondpart) ? -1 : 1;
			return self::compare($b->secondpart, $a->secondpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}

	/* sort by category DESC by firstpart ASC secondpart ASC */
	protected static function sortCdescFascSasc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->secondpart == $b->secondpart) {
					return 0;
				}
				//return ($a->secondpart < $b->secondpart) ? -1 : 1;
				return self::compare($a->secondpart, $b->secondpart);
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}

	/* sort by category DESC by firstpart DESC secondpart DESC */
	protected static function sortCdescFdescSdesc($a, $b) {

		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->secondpart == $b->secondpart) {
					return 0;
				}
				//return ($a->secondpart > $b->secondpart) ? -1 : 1;
				return self::compare($b->secondpart, $a->secondpart);
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}

	/* sort by lastpart ASC firstpart ASC */
	protected static function sortLascFasc($a, $b) {

		if ($a->lastpart == $b->lastpart) {
			if ($a->firstpart == $b->firstpart) {
				return 0;
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		//return ($a->lastpart < $b->lastpart) ? -1 : 1;
		return self::compare($a->lastpart, $b->lastpart);
	}

	/* sort by lastpart DESC firstpart DESC */
	protected static function sortLdescFdesc($a, $b) {

		if ($a->lastpart == $b->lastpart) {
			if ($a->firstpart == $b->firstpart) {
				return 0;
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		//return ($a->lastpart > $b->lastpart) ? -1 : 1;
		return self::compare($b->lastpart, $a->lastpart);
	}

	/* sort by firstpart ASC lastpart ASC */
	protected static function sortFascLasc($a, $b) {

		if ($a->firstpart == $b->firstpart) {
			if ($a->lastpart == $b->lastpart) {
				return 0;
			}
			//return ($a->lastpart < $b->lastpart) ? -1 : 1;
			return self::compare($a->lastpart, $b->lastpart);
		}
		//return ($a->firstpart < $b->firstpart) ? -1 : 1;
		return self::compare($a->firstpart, $b->firstpart);
	}

	/* sort by firstpart DESC lastpart DESC */
	protected static function sortFdescLdesc($a, $b) {

		if ($a->firstpart == $b->firstpart) {
			if ($a->lastpart == $b->lastpart) {
				return 0;
			}
			//return ($a->lastpart > $b->lastpart) ? -1 : 1;
			return self::compare($b->lastpart, $a->lastpart);
		}
		//return ($a->firstpart > $b->firstpart) ? -1 : 1;
		return self::compare($b->firstpart, $a->firstpart);
	}

	/* sort by secondpart ASC firstpart ASC */
	protected static function sortSascFasc($a, $b) {

		if ($a->secondpart == $b->secondpart) {
			if ($a->firstpart == $b->firstpart) {
				return 0;
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		//return ($a->secondpart < $b->secondpart) ? -1 : 1;
		return self::compare($a->secondpart, $b->secondpart);
	}

	/* sort by secondpart DESC firstpart DESC */
	protected static function sortSdescFdesc($a, $b) {

		if ($a->secondpart == $b->secondpart) {
			if ($a->firstpart == $b->firstpart) {
				return 0;
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		//return ($a->secondpart > $b->secondpart) ? -1 : 1;
		return self::compare($b->secondpart, $a->secondpart);
	}

	/* sort by firstpart ASC secondpart ASC */
	protected static function sortFascSasc($a, $b) {

		if ($a->firstpart == $b->firstpart) {
			if ($a->secondpart == $b->secondpart) {
				return 0;
			}
			//return ($a->secondpart < $b->secondpart) ? -1 : 1;
			return self::compare($a->secondpart, $b->secondpart);
		}
		//return ($a->firstpart < $b->firstpart) ? -1 : 1;
		return self::compare($a->firstpart, $b->firstpart);
	}

	/* sort by firstpart DESC secondpart DESC */
	protected static function sortFdescSdesc($a, $b) {

		if ($a->firstpart == $b->firstpart) {
			if ($a->secondpart == $b->secondpart) {
				return 0;
			}
			//return ($a->secondpart > $b->secondpart) ? -1 : 1;
			return self::compare($b->secondpart, $a->secondpart);
		}
		//return ($a->firstpart > $b->firstpart) ? -1 : 1;
		return self::compare($b->firstpart, $a->firstpart);
	}

	protected static function _substring_index($subject, $delim, $count)
	{
		if ($count < 0) {
			return implode($delim, array_slice(explode($delim, $subject), $count));
		} else {
			return implode($delim, array_slice(explode($delim, $subject), 0, $count));
		}
	}

	/**
	 * Load flipcards script for all module instances
	 */
	public static function loadFlipCards()
	{
		if (self::$flipScriptLoaded) {
			return;
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$minified = (JDEBUG) ? '' : '.min';

		$wam->registerAndUseScript('tc.flipcards', 'mod_trombinoscopecontacts/flipcards' . $minified . '.js', ['relative' => true, 'version' => 'auto']); // no defer

		self::$flipScriptLoaded = true;
	}

	/**
	 * Load common stylesheet to all module instances
	 */
	public static function loadCommonStylesheet()
	{
		if (self::$commonStylesLoaded) {
			return;
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$minified = (JDEBUG) ? '' : '-min';

		$wam->registerAndUseStyle('tc.common_styles', 'mod_trombinoscopecontacts/common_styles' . $minified . '.css', ['relative' => true, 'version' => 'auto']);

		self::$commonStylesLoaded = true;
	}

	/**
	 * Load user stylesheet to all module instances
	 * if the file has 'substitute' in the name, it will replace all module styles
	 */
	public static function loadUserStylesheet($styles_substitute = false)
	{
		if (self::$userStylesLoaded) {
			return;
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$prefix = 'common_user';
		if ($styles_substitute) {
			$prefix = 'substitute';
		}

		if (!File::exists(JPATH_ROOT . '/media/mod_trombinoscopecontacts/css/' . $prefix . '_styles-min.css') || JDEBUG) {
			$wam->registerAndUseStyle('tc.' . $prefix . '_styles', 'mod_trombinoscopecontacts/' . $prefix . '_styles.css', ['relative' => true, 'version' => 'auto']);
		} else {
			$wam->registerAndUseStyle('tc.' . $prefix . '_styles', 'mod_trombinoscopecontacts/' . $prefix . '_styles-min.css', ['relative' => true, 'version' => 'auto']);
		}

		self::$userStylesLoaded = true;
	}

}
