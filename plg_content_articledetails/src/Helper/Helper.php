<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\Content\ArticleDetails\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;
use Joomla\Component\Tags\Site\Helper\RouteHelper as TagsRouteHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Registry\Registry;
use SYW\Library\Fonts as SYWFonts;
use SYW\Library\Utilities as SYWUtilities;

class Helper
{
	static $contacts = array();

	static function date_to_counter($date, $date_in_future = false) {

		$date_origin = new Date($date);
		$now = new Date(); // now

		if ($date_in_future) {
			$difference = $date_origin->toUnix() - $now->toUnix();
		} else {
			$difference = $now->toUnix() - $date_origin->toUnix();
		}

		//$difference = $date_origin->diff($now); // object PHP 5.3 [y] => 0 [m] => 0 [d] => 26 [h] => 23 [i] => 11 [s] => 32 [invert] => 0 [days] => 26

		$nbr_days = 0;
		$nbr_hours = 0;
		$nbr_mins = 0;
		$nbr_secs = 0;

		if ($difference < 60) { // less than 1 minute
			$nbr_secs = $difference;
		} else if ($difference < 3600) { // less than 1 hour
			$nbr_mins = $difference / 60;
			$nbr_secs = $difference % 60;
		} else if ($difference < 86400) { // less than 1 day
			$nbr_hours = $difference / 3600;
			$nbr_mins = ($difference % 3600) / 60;
			$nbr_secs = $difference % 60;
		} else { // 1 day or more
			$nbr_days = $difference / 86400;
			$nbr_hours = ($difference % 86400) / 3600;
			$nbr_mins = ($difference % 3600) / 60;
			$nbr_secs = $difference % 60;
		}

		return array('days' => $nbr_days, 'hours' => $nbr_hours, 'mins' => $nbr_mins, 'secs' => $nbr_secs);
	}

	/**
	 * Create the first part of the <a> tag for links a, b and c
	 */
	static function getATagLinks($url, $urltext, $target, $tooltip = true, $popup_width = '600', $popup_height = '500', $css_classes = '')
	{
		// do not add tooltips in case links are internal

		switch ($target) {
			case 1:	// open in a new window
				return '<a class="'.$css_classes.'" href="'.htmlspecialchars($url).'" target="_blank">';
				break;
			case 2: case 3:	// open in a popup window
				$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width='.$popup_width.',height='.$popup_height;
				return '<a class="'.$css_classes.'" href="'.$url.'" onclick="window.open(this.href, \'targetWindow\', \''.$attribs.'\'); return false;">';
				break;
			default: // open in parent window
				return '<a class="'.$css_classes.'" href="'.htmlspecialchars($url).'">';
		}
	}

	/**
	 * Get detail parameters
	 *
	 * @return array
	 */
	private static function getDetails($params, $view, $prefix = '', $subform = '') {

		$infos = array();

		$user = Factory::getUser();
		$groups	= $user->getAuthorisedViewLevels();

		// get data from subform items

		$information_blocs = $params->get($prefix.$subform); // array of objects
		if (!empty($information_blocs) && is_object($information_blocs)) {
			foreach ($information_blocs as $information_bloc) {
				if ($information_bloc->info != 'none' && in_array($information_bloc->access, $groups)) {

					if ((($information_bloc->showing_in == '' || $information_bloc->showing_in == 2) && $view == 'article')
							|| (($information_bloc->showing_in == '' || $information_bloc->showing_in == 1) && $view != 'article')) {

								$details = array();
								$details['info'] = $information_bloc->info;
								$details['prepend'] = $information_bloc->prepend;
								$details['show_icon'] = $information_bloc->show_icons == 1 ? true : false;
								$details['icon'] = '';
								$details['extra_classes'] = isset($information_bloc->extra_classes) ? $information_bloc->extra_classes : '';

								$infos[] = $details;

								if ($information_bloc->new_line == 1) {
									$infos[] = array('info' => 'newline', 'prepend' => '', 'show_icon' => false, 'extra_classes' => '');
								}
							}
				}
			}
		}

		return $infos;
	}

	/**
	 * Get icon and label pre-data, if any
	 */
	static function getPreData($label, $default_label, $show_icon, $default_icon, $icon = "") {

		$html = "";

		if ($show_icon && Factory::getDocument()->getDirection() != 'rtl') {
			$icon = empty($icon) ? $default_icon : $icon;
			$html .= '<i class="SYWicon-'.$icon.'"></i>';
		}

		$prepend = !empty($default_label) ? $default_label : $label;
		if (!empty($prepend) && Factory::getDocument()->getDirection() != 'rtl') {
			$html .= '<span class="detail_label">'.$prepend.'</span>';
		}

		return $html;
	}

	/**
	 * Get icon and label post-data, if any
	 */
	static function getPostData($label, $default_label, $show_icon, $default_icon, $icon = "") {

		$html = "";

		$prepend = !empty($default_label) ? $default_label : $label;
		if (!empty($prepend) && Factory::getDocument()->getDirection() == 'rtl') {
			$html .= '<span class="detail_label">'.$prepend.'</span>';
		}

		if ($show_icon && Factory::getDocument()->getDirection() == 'rtl') {
			$icon = empty($icon) ? $default_icon : $icon;
			$html .= '<i class="SYWicon-'.$icon.'"></i>';
		}

		return $html;
	}

	/**
	 * Get block information
	 */
	static function getInfoBlock($params, $item, $item_params, $view, $position) {

		$info_block = '';

		$infos = self::getDetails($params, $view, $position . '_', 'information_blocks');

		if (empty($infos)) {
			return $info_block;
		}

		$bootstrap_version = $params->get('bootstrap_version', 'joomla');
		$load_bootstrap = false;
		if ($bootstrap_version === 'joomla') {
			$bootstrap_version = 5;
			$load_bootstrap = true;
		} else {
			$bootstrap_version = intval($bootstrap_version);
		}

		if ($load_bootstrap) {
		    HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
		}

		$db = Factory::getDbo();
		$app = Factory::getApplication();

		$show_date = $params->get('show_d', 'date');

		$date_format = Text::_('PLG_CONTENT_ARTICLEDETAILS_FORMAT_DATE');
		if (empty($date_format)) {
			$date_format = $params->get('d_format', 'd F Y');
		}

		$time_format = Text::_('PLG_CONTENT_ARTICLEDETAILS_FORMAT_TIME');
		if (empty($time_format)) {
			$time_format = $params->get('t_format', 'H:i');
		}

		$separator = htmlspecialchars($params->get('separator', ''));
		$separator = empty($separator) ? ' ' : $separator;

		$info_block .= '<dt>'.Text::_('PLG_CONTENT_ARTICLEDETAILS_INFORMATION_LABEL').'</dt>';

		$info_block .= '<dd class="details">';
		$has_info_from_previous_detail = false;

		$force_show = $params->get('force_show', 0);

		foreach ($infos as $key => $value) {

			$extraclasses = $value['extra_classes'] ? ' ' . $value['extra_classes'] : '';

			switch ($value['info']) {
				case 'newline':
					$info_block .= '</dd><dd class="details">';
					$has_info_from_previous_detail = false;
				break;

				case 'hits':

				    if (isset($item->hits) && ($item_params->get('show_hits') || $force_show)) {

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_hits' . $extraclasses . '">';

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_HITS'), $value['show_icon'], 'eye', $value['icon']);

						$info_block .= '<span class="detail_data">';

						$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_HITS', $item->hits);

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_HITS'), $value['show_icon'], 'eye', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'rating':

				    /* if no rating, still need to be able to show that there is none */

				    if (/*isset($item->rating) && */($item_params->get('ad_show_vote') || $force_show)) {

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_rating' . $extraclasses . '">';

						$icon_default = 'star-outline';
						if (!empty($item->rating)) {
							if (intval($item->rating) == 5) {
								$icon_default = 'star';
							} else {
								$icon_default = 'star-half';
							}
						}

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_RATING'), $value['show_icon'], $icon_default, $value['icon']);

						$info_block .= '<span class="detail_data">';

						if (!empty($item->rating)) {
							if ($params->get('show_rating') == 'text') {
								$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_RATING', $item->rating).' ';
								$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_FROMUSERS', $item->rating_count);
							} else { // use stars

								$whole = intval($item->rating);

								$stars = '';
								for ($i = 0; $i < $whole; $i++) {
									$stars .= '<i class="SYWicon-star" aria-hidden="true"></i>';
								}

								if ($whole < 5) { // Joomla rounds the rating, therefore there will never be a fraction

									// get fraction

									$fraction = $item->rating - $whole;
									if ($fraction > .4) {
										$stars .= '<i class="SYWicon-star-half" aria-hidden="true"></i>';
									} else {
										$stars .= '<i class="SYWicon-star-outline" aria-hidden="true"></i>';
									}

									for ($i = $whole + 1; $i < 5; $i++) {
										$stars .= '<i class="SYWicon-star-outline" aria-hidden="true"></i>';
									}
								}

								$info_block .= $stars;
							}
						} else {
							$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_NORATING');
						}

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_RATING'), $value['show_icon'], $icon_default, $value['icon']);

						$info_block .= '</span>';

						if ($view == 'article' && $item->state == 1 && !$app->getInput()->getBool('print')) {
							
							$uri = clone Uri::getInstance();
							$uri->setVar('hitcount', '0');

							$options = array();
							$options[] = HTMLHelper::_('select.option', 5, Text::_('PLG_CONTENT_ARTICLEDETAILS_VOTE5'));
							for ($i = 4; $i > 0; $i--) {
							    $options[] = HTMLHelper::_('select.option', $i, Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_VOTE', $i));
							}
						
							$info_block .= '<form method="post" action="' . htmlspecialchars($uri->toString(), ENT_COMPAT, 'UTF-8') . '" class="form-inline">';
							$info_block .= '<span class="article_vote">';							
							$info_block .= '<label class="visually-hidden" for="article_vote_' . $item->id . '">' . Text::_('PLG_CONTENT_ARTICLEDETAILS_PLEASEVOTE') . '</label>';
							$info_block .= HTMLHelper::_('select.genericlist', $options, 'user_rating', 'class="form-select form-select-sm w-auto"', 'value', 'text', '5', 'article_vote_' . $item->id);
							$info_block .= '&#160;<input class="btn btn-sm btn-primary align-baseline" type="submit" name="submit_vote" value="' . Text::_('PLG_CONTENT_ARTICLEDETAILS_RATE') . '" />';
							$info_block .= '<input type="hidden" name="task" value="article.vote" />';
							$info_block .= '<input type="hidden" name="hitcount" value="0" />';
							$info_block .= '<input type="hidden" name="url" value="' . htmlspecialchars($uri->toString(), ENT_COMPAT, 'UTF-8') . '" />';
							$info_block .= HTMLHelper::_('form.token');
							$info_block .= '</span>';
							$info_block .= '</form>';
						}

						$has_info_from_previous_detail = true;
					}
				break;

				case 'author':
				case 'authorcb':

				    if (isset($item->author) && ($item_params->get('show_author') || $force_show)) {

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_author' . $extraclasses . '">';

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_AUTHOR'), $value['show_icon'], 'user', $value['icon']);

						$info_block .= '<span class="detail_data">';

						$author = $item->created_by_alias ? $item->created_by_alias : $item->author;

						if ($value['info'] == 'author') {
							if (isset($item->contact_link) && !empty($item->contact_link) && $item_params->get('link_author') && !$app->getInput()->getBool('print')) { // 'contact_link' comes from contact plugin
								$info_block .= HTMLHelper::_('link', $item->contact_link, $author);
							} else {
								$info_block .= $author;
							}
						} else { // author links to Community Builder
							if (is_dir(JPATH_ADMINISTRATOR . '/components/com_comprofiler') && ComponentHelper::isEnabled('com_comprofiler')) {
							    if ($item_params->get('link_author') && Factory::getUser()->id != 0 && !$app->getInput()->getBool('print')) {
									$info_block .= HTMLHelper::_('link', 'index.php?option=com_comprofiler&task=userprofile&user='.$item->created_by, $author);
								} else {
									$info_block .= $author;
								}
							} else {
								$info_block .= $author;
							}
						}

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_AUTHOR'), $value['show_icon'], 'user', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
					break;

				case 'keywords':
				case 'keywordssearch':
				case 'keywordsfinder':

					$keywords = preg_split ('/[\s]*[,][\s]*/', $item->metakey, -1, PREG_SPLIT_NO_EMPTY); // deals with "key1  ,key2,   key3  "
					// empty($keyword) in the following code should be unnecessary since we used PREG_SPLIT_NO_EMPTY

					if (!empty($keywords)) {

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						if ($params->get('distinct_keywords', 0)) { // keywords as distinct entities

							$info_block .= '<span class="detail detail_keywords' . $extraclasses . '">';

							$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_KEYWORDS'), $value['show_icon'], 'tag', $value['icon']);

							$info_block .= '<span class="detail_multi_data">';

							foreach ($keywords as $i => $keyword) {
								if (!empty($keyword)) {

									$info_block .= '<span class="distinct">';

									$info_block .= self::getPreData($params->get('prepend_keywords', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_KEYWORD'), $params->get('show_icon_keywords', 0), 'tag', $params->get('icon_keywords', ''));

									if ($value['info'] == 'keywordssearch' && !$app->getInput()->getBool('print')) {

										// Find the menu item for the search
										$menu  = $app->getMenu();
										$items = $menu->getItems('link', 'index.php?option=com_search&view=search');
										$searchUriAddition = '';
										if (isset($items[0])) {
											$searchUriAddition = '&Itemid='.$items[0]->id;
										}

										$info_block .= '<a class="detail_data" href="'.Route::_(Uri::base().'index.php?option=com_search&searchword='.$keyword.'&searchphrase=all'.$searchUriAddition).'">'.$keyword.'</a>';

									} else if ($value['info'] == 'keywordsfinder' && !$app->getInput()->getBool('print')) {

										// Find the menu item for the search
										$menu  = $app->getMenu();
										$items = $menu->getItems('link', 'index.php?option=com_finder&view=search');
										$searchUriAddition = '';
										if (isset($items[0])) {
											$searchUriAddition = '&Itemid='.$items[0]->id;
										}

										$info_block .= '<a class="detail_data" href="'.Route::_(Uri::base().'index.php?option=com_finder&q='.$keyword.$searchUriAddition).'">'.$keyword.'</a>';
									} else {
										$info_block .= '<span class="detail_data">'.$keyword.'</span>';
									}

									$info_block .= self::getPostData($params->get('prepend_keywords', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_KEYWORD'), $params->get('show_icon_keywords', 0), 'tag', $params->get('icon_keywords', ''));

									$info_block .= '</span>';
								}

								if ($i < count($keywords) - 1) {
									if (!empty($keyword)) {
										$info_block .= '<span class="delimiter"> </span>';
									}
								}

								$info_block .= '</span>';

								$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_KEYWORDS'), $value['show_icon'], 'tag', $value['icon']);

								$info_block .= '</span>';

								$has_info_from_previous_detail = true;
							}

						} else { // keywords as list of items

							$info_block .= '<span class="detail detail_keywords' . $extraclasses . '">';

							$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_KEYWORDS'), $value['show_icon'], 'tag', $value['icon']);

							$info_block .= '<span class="detail_data">';

							// clean the keyword's list
							foreach ($keywords as $i => $keyword) {
								if (!empty($keyword)) {

									if ($value['info'] == 'keywordssearch' && !$app->getInput()->getBool('print')) {

										// Find the menu item for the search
										$menu  = $app->getMenu();
										$items = $menu->getItems('link', 'index.php?option=com_search&view=search');
										$searchUriAddition = '';
										if (isset($items[0])) {
											$searchUriAddition = '&Itemid='.$items[0]->id;
										}

										$keyword = '<a href="'.Route::_(Uri::base().'index.php?option=com_search&searchword='.$keyword.'&searchphrase=all'.$searchUriAddition).'">'.$keyword.'</a>';

									} else if ($value['info'] == 'keywordsfinder' && !$app->getInput()->getBool('print')) {

										// Find the menu item for the search
										$menu  = $app->getMenu();
										$items = $menu->getItems('link', 'index.php?option=com_finder&view=search');
										$searchUriAddition = '';
										if (isset($items[0])) {
											$searchUriAddition = '&Itemid='.$items[0]->id;
										}

										$keyword = '<a href="'.Route::_(Uri::base().'index.php?option=com_finder&q='.$keyword.$searchUriAddition).'">'.$keyword.'</a>';
									}

									$info_block .= $keyword;
								}

								if ($i < count($keywords) - 1) {
									if (!empty($keyword)) {
										$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_KEYWORDSSSEPARATOR');
									}
								}
							}

							$info_block .= '</span>';

							$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_KEYWORDS'), $value['show_icon'], 'tag', $value['icon']);

							$info_block .= '</span>';

							$has_info_from_previous_detail = true;
						}
					}
				break;

				case 'parentcategory':

				    if (isset($item->parent_title) && $item->parent_id !== 1 && ($item_params->get('show_parent_category') || $force_show)) { // do not show any parent info if the parent is root

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_parentcategory' . $extraclasses . '">';

						if ($item_params->get('link_parent_category')) {
							$icon_default = 'folder-open';
						} else {
							$icon_default = 'folder';
						}

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_PARENTCATEGORY'), $value['show_icon'], $icon_default, $value['icon']);

						if ($item_params->get('link_parent_category') && !$app->getInput()->getBool('print')) {
							if ($view == 'article') {
								if (!empty($item->parent_slug)) {
									$info_block .= '<a class="detail_data" href="'.Route::_(ContentRouteHelper::getCategoryRoute($item->parent_slug)).'">'.$item->parent_title.'</a>';
								} else {
									$info_block .= '<span class="detail_data">'.$item->parent_title.'</span>';
								}
							} else {

								// No linking if the parent category is the one the view is in

								$cat_link = Route::_(ContentRouteHelper::getCategoryRoute($item->parent_id));
								$current_link = Uri::current();
								if (substr( $current_link, strlen( $current_link ) - strlen( $cat_link ) ) != $cat_link) { // the current links does not end with the parent category link
									$info_block .= '<a class="detail_data" href="'.Route::_(ContentRouteHelper::getCategoryRoute($item->parent_id)).'">'.$item->parent_title.'</a>';
								} else {
									$info_block .= '<span class="detail_data">'.$item->parent_title.'</span>';
								}
							}
						} else {
							$info_block .= '<span class="detail_data">'.$item->parent_title.'</span>';
						}

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_PARENTCATEGORY'), $value['show_icon'], $icon_default, $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'category':

				    if (isset($item->category_title) && ($item_params->get('show_category') || $force_show)) {

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_category' . $extraclasses . '">';

						if ($item_params->get('link_category')) {
							$icon_default = 'folder-open';
						} else {
							$icon_default = 'folder';
						}

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_CATEGORY'), $value['show_icon'], $icon_default, $value['icon']);

						if ($item_params->get('link_category') && !$app->getInput()->getBool('print')) {
							if ($view == 'article') {
								if (!empty($item->catslug)) {
									$info_block .= '<a class="detail_data" href="'.Route::_(ContentRouteHelper::getCategoryRoute($item->catslug)).'">'.$item->category_title.'</a>';
								} else {
									$info_block .= '<span class="detail_data">'.$item->category_title.'</span>';
								}
							} else {

								// No linking if the category is the one the view is in

								$cat_link = Route::_(ContentRouteHelper::getCategoryRoute($item->catid));
								$current_link = Uri::current();
								if (substr( $current_link, strlen( $current_link ) - strlen( $cat_link ) ) != $cat_link) { // the current links does not end with the category link
									$info_block .= '<a class="detail_data" href="'.Route::_(ContentRouteHelper::getCategoryRoute($item->catid)).'">'.$item->category_title.'</a>'; // keep linking in category view because of sub-categories
								} else {
									$info_block .= '<span class="detail_data">'.$item->category_title.'</span>';
								}
							}
						} else {
							$info_block .= '<span class="detail_data">'.$item->category_title.'</span>';
						}

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_CATEGORY'), $value['show_icon'], $icon_default, $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'combocategories':

					if ($item_params->get('show_category') || $force_show) {

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_categories' . $extraclasses . '">';

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_COMBOCATEGORIES'), $value['show_icon'], 'folder-open', $value['icon']);

						$info_block .= '<span class="detail_data">';

						if (($item_params->get('show_parent_category') || $force_show) && $item->parent_id != 1) { // do not show any parent info if the parent is root
							if ($item_params->get('link_parent_category') && !$app->getInput()->getBool('print')) {
								if ($view == 'article') {
									if (!empty($item->parent_slug)) {
										$info_block .= '<a href="'.Route::_(ContentRouteHelper::getCategoryRoute($item->parent_slug)).'">'.$item->parent_title.'</a>';
										$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_COMBOCATEGORIESSEPARATOR');
									} else {
										$info_block .= $item->parent_title;
										$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_COMBOCATEGORIESSEPARATOR');
									}
								} else {

									// No linking if the parent category is the one the view is in

									$cat_link = Route::_(ContentRouteHelper::getCategoryRoute($item->parent_id));
									$current_link = Uri::current();
									if (substr( $current_link, strlen( $current_link ) - strlen( $cat_link ) ) != $cat_link) { // the current links does not end with the parent category link
										$info_block .= '<a href="'.Route::_(ContentRouteHelper::getCategoryRoute($item->parent_id)).'">'.$item->parent_title.'</a>';
										$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_COMBOCATEGORIESSEPARATOR');
									} else {
										$info_block .= $item->parent_title;
										$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_COMBOCATEGORIESSEPARATOR');
									}
								}
							} else {
								$info_block .= $item->parent_title;
								$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_COMBOCATEGORIESSEPARATOR');
							}
						}

						//if ($item_params->get('show_category')) {
						if ($item_params->get('link_category') && !$app->getInput()->getBool('print')) {
							if ($view == 'article') {
								if (!empty($item->catslug)) {
									$info_block .= '<a href="'.Route::_(ContentRouteHelper::getCategoryRoute($item->catslug)).'">'.$item->category_title.'</a>';
								} else {
									$info_block .= $item->category_title;
								}
							} else {

								// No linking if the category is the one the view is in

								$cat_link = Route::_(ContentRouteHelper::getCategoryRoute($item->catid));
								$current_link = Uri::current();
								if (substr( $current_link, strlen( $current_link ) - strlen( $cat_link ) ) != $cat_link) { // the current links does not end with the category link
									$info_block .= '<a href="'.Route::_(ContentRouteHelper::getCategoryRoute($item->catid)).'">'.$item->category_title.'</a>'; // keep linking in category view because of sub-categories
								} else {
									$info_block .= $item->category_title;
								}
							}
						} else {
							$info_block .= $item->category_title;
						}
						//}

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_COMBOCATEGORIES'), $value['show_icon'], 'folder-open', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'created':
				case 'modified':
				case 'published':

					$date = $item->publish_up;
					if ($value['info'] == 'created') {
						$date = $item->created;
					} else if ($value['info'] == 'modified') {
						$date = $item->modified;
					}

					if ($date == $db->getNullDate() || empty($date)) {
						//$info_block .= '<span class="detail detail_date"><span class="article_nodate"></span></span>';
					} else {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_date' . $extraclasses . '">';

						$label_default = Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_PUBLISHED');
						if ($value['info'] == 'created') {
							$label_default = Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_CREATED');
						} else if ($value['info'] == 'modified') {
							$label_default = Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_MODIFIED');
						}

						$info_block .= self::getPreData($value['prepend'], $label_default, $value['show_icon'], 'calendar', $value['icon']);

						$info_block .= '<span class="detail_data">';

						$nbr_seconds = -1;
						$nbr_minutes = -1;
						$nbr_hours = -1;
						$nbr_days = -1;

						if ($show_date == 'ago' || $show_date == 'agomhd' || $show_date == 'agohm') {
							if (!empty($date)) {
								$details = self::date_to_counter($date, false);

								$nbr_seconds  = intval($details['secs']);
								$nbr_minutes  = intval($details['mins']);
								$nbr_hours = intval($details['hours']);
								$nbr_days = intval($details['days']);
							}
						}

						if ($show_date == 'date') {
							$info_block .= HTMLHelper::_('date', $date, $date_format);
						} else if ($show_date == 'ago') {
							if ($nbr_days == 0) {
								$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_TODAY');
							} else if ($nbr_days == 1) {
								$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_YESTERDAY');
							} else {
								$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_DAYSAGO', $nbr_days);
							}
						} else if ($show_date == 'agomhd') {
							if ($nbr_days > 0) {
								if ($nbr_days == 1) {
									$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_DAYAGO');
								} else {
									$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_DAYSAGO', $nbr_days);
								}
							} else if ($nbr_hours > 0) {
								if ($nbr_hours == 1) {
									$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_HOURAGO');
								} else {
									$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_HOURSAGO', $nbr_hours);
								}
							} else {
								if ($nbr_minutes == 1) {
									$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_MINUTEAGO');
								} else {
									$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_MINUTESAGO', $nbr_minutes);
								}
							}
						} else {
							if ($nbr_days > 0) {
								$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_DAYSHOURSMINUTESAGO', $nbr_days, $nbr_hours, $nbr_minutes);
							} else if ($nbr_hours > 0) {
								$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_HOURSMINUTESAGO', $nbr_hours, $nbr_minutes);
							} else {
								if ($nbr_minutes == 1) {
									$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_MINUTEAGO');
								} else {
									$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_MINUTESAGO', $nbr_minutes);
								}
							}
						}

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], $label_default, $value['show_icon'], 'calendar', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'finished':

					if ($item->publish_down == $db->getNullDate() || empty($item->publish_down)) {
						//$info_block .= '<span class="detail detail_date"><span class="article_nodate"></span></span>';
					} else {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_date' . $extraclasses . '">';

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_FINISHED'), $value['show_icon'], 'calendar', $value['icon']);

						$info_block .= '<span class="detail_data">';

						$nbr_seconds = -1;
						$nbr_minutes = -1;
						$nbr_hours = -1;
						$nbr_days = -1;

						if ($show_date == 'ago' || $show_date == 'agomhd' || $show_date == 'agohm') {
							if (!empty($item->publish_down)) {
								$details = self::date_to_counter($item->publish_down, true);

								$nbr_seconds = intval($details['secs']);
								$nbr_minutes = intval($details['mins']);
								$nbr_hours = intval($details['hours']);
								$nbr_days = intval($details['days']);
							}
						}

						if ($show_date == 'date') {
							$info_block .= HTMLHelper::_('date', $item->publish_down, $date_format);
						} else if ($show_date == 'ago') {
							if ($nbr_days == 0) {
								$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_TODAY');
							} else if ($nbr_days == 1) {
								$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_TOMORROW');
							} else {
								$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_INDAYSONLY', $nbr_days);
							}
						} else if ($show_date == 'agomhd') {
							if ($nbr_days > 0) {
								if ($nbr_days == 1) {
									$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_INADAY');
								} else {
									$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_INDAYSONLY', $nbr_days);
								}
							} else if ($nbr_hours > 0) {
								if ($nbr_hours == 1) {
									$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_INANHOUR');
								} else {
									$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_INHOURS', $nbr_hours);
								}
							} else {
								if ($nbr_minutes == 1) {
									$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_INAMINUTE');
								} else {
									$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_INMINUTES', $nbr_minutes);
								}
							}
						} else {
							if ($nbr_days > 0) {
								$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_INDAYSHOURSMINUTES', $nbr_days, $nbr_hours, $nbr_minutes);
							} else if ($nbr_hours > 0) {
								$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_INHOURSMINUTES', $nbr_hours, $nbr_minutes);
							} else {
								if ($nbr_minutes == 1) {
									$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_INAMINUTE');
								} else {
									$info_block .= Text::sprintf('PLG_CONTENT_ARTICLEDETAILS_INMINUTES', $nbr_minutes);
								}
							}
						}

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_FINISHED'), $value['show_icon'], 'calendar', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'createdtime':
				case 'modifiedtime':
				case 'publishedtime':
				case 'finishedtime':

					$date = $item->publish_up;
					if ($value['info'] == 'createdtime') {
						$date = $item->created;
					} else if ($value['info'] == 'modifiedtime') {
						$date = $item->modified;
					} else if ($value['info'] == 'finishedtime') {
						$date = $item->publish_down;
					}

					if ($date == $db->getNullDate() || empty($date)) {
						//$info_block .= '<span class="detail detail_time"><span class="article_notime"></span></span>';
					} else {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_time' . $extraclasses . '">';

						$label_default = Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_PUBLISHEDTIME');
						if ($value['info'] == 'createdtime') {
							$label_default = Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_CREATEDTIME');
						} else if ($value['info'] == 'modifiedtime') {
							$label_default = Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_MODIFIEDTIME');
						} else if ($value['info'] == 'finishedtime') {
							$label_default = Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_FINISHEDTIME');
						}

						$info_block .= self::getPreData($value['prepend'], $label_default, $value['show_icon'], 'clock', $value['icon']);

						$info_block .= '<span class="detail_data">';

						$info_block .= HTMLHelper::_('date', $date, $time_format);

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], $label_default, $value['show_icon'], 'clock', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'linka':
				case 'linkb':
				case 'linkc':
				case 'links':
				case 'linksnl':

					if (isset($item->urls)) {

						$urls = json_decode($item->urls);

						if ($urls && (!empty($urls->urla) || !empty($urls->urlb) || !empty($urls->urlc))) {

							$globalparams = ComponentHelper::getParams('com_content');

							$targeta = $globalparams->get('targeta', 0);
							if (!empty($urls->targeta)) {
								$targeta = $urls->targeta;
							}

							$targetb = $globalparams->get('targetb', 0);
							if (!empty($urls->targetb)) {
								$targetb = $urls->targetb;
							}

							$targetc = $globalparams->get('targetc', 0);
							if (!empty($urls->targetc)) {
								$targetc = $urls->targetc;
							}

							if ($has_info_from_previous_detail) {
								$info_block .= '<span class="delimiter">'.$separator.'</span>';
							}

							// if all links a b c
							if ($value['info'] == 'links' || $value['info'] == 'linksnl') {

								$info_block .= '<span class="detail detail_links' . $extraclasses . '">';

								$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINKS'), $value['show_icon'], 'link', $value['icon']);

								$info_block .= '<span class="detail_multi_data">';

								if (!empty($urls->urla)) {

									$info_block .= '<span class="distinct distinct_linka">';

									$info_block .= self::getPreData($params->get('prepend_links', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $params->get('show_icon_links', 0), 'link', $params->get('icon_links', ''));

									if (!$app->getInput()->getBool('print')) {
										$info_block .= self::getATagLinks($urls->urla, $urls->urlatext, $targeta, false, '600', '500', 'detail_data');
									} else {
										$info_block .= '<span class="detail_data">';
									}

									if (!empty($urls->urlatext)) {
										$info_block .= $urls->urlatext;
									} else {
										if (!$params->get('protocol', 1)) {
											$info_block .= self::remove_protocol($urls->urla);
										} else {
											$info_block .= $urls->urla;
										}
									}

									if (!$app->getInput()->getBool('print')) {
										$info_block .= '</a>';
									} else {
										$info_block .= '</span>';
									}

									$info_block .= self::getPostData($params->get('prepend_links', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $params->get('show_icon_links', 0), 'link', $params->get('icon_links', ''));

									$info_block .= '</span>';

									if ($value['info'] == 'linksnl' && (!empty($urls->urlb) || !empty($urls->urlc))) {
										$info_block .= '<br />';
									} else if ($value['info'] == 'links' && (!empty($urls->urlb) || !empty($urls->urlc))) {
										//$info_block .= '<span class="delimiter"> </span>';
										$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_LINKSSEPARATOR');
									}
								}

								if (!empty($urls->urlb)) {

									$info_block .= '<span class="distinct distinct_linkb">';

									$info_block .= self::getPreData($params->get('prepend_links', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $params->get('show_icon_links', 0), 'link', $params->get('icon_links', ''));

									if (!$app->getInput()->getBool('print')) {
										$info_block .= self::getATagLinks($urls->urlb, $urls->urlbtext, $targetb, false, '600', '500', 'detail_data');
									} else {
										$info_block .= '<span class="detail_data">';
									}

									if (!empty($urls->urlbtext)) {
										$info_block .= $urls->urlbtext;
									} else {
										if (!$params->get('protocol', 1)) {
											$info_block .= self::remove_protocol($urls->urlb);
										} else {
											$info_block .= $urls->urlb;
										}
									}

									if (!$app->getInput()->getBool('print')) {
										$info_block .= '</a>';
									} else {
										$info_block .= '</span>';
									}

									$info_block .= self::getPostData($params->get('prepend_links', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $params->get('show_icon_links', 0), 'link', $params->get('icon_links', ''));

									$info_block .= '</span>';

									if ($value['info'] == 'linksnl' && !empty($urls->urlc)) {
										$info_block .= '<br />';
									} else if ($value['info'] == 'links' && !empty($urls->urlc)) {
										//$info_block .= '<span class="delimiter"> </span>';
										$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_LINKSSEPARATOR');
									}
								}

								if (!empty($urls->urlc)) {

									$info_block .= '<span class="distinct distinct_linkc">';

									$info_block .= self::getPreData($params->get('prepend_links', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $params->get('show_icon_links', 0), 'link', $params->get('icon_links', ''));

									if (!$app->getInput()->getBool('print')) {
										$info_block .= self::getATagLinks($urls->urlc, $urls->urlctext, $targetc, false, '600', '500', 'detail_data');
									} else {
										$info_block .= '<span class="detail_data">';
									}

									if (!empty($urls->urlctext)) {
										$info_block .= $urls->urlctext;
									} else {
										if (!$params->get('protocol', 1)) {
											$info_block .= self::remove_protocol($urls->urlc);
										} else {
											$info_block .= $urls->urlc;
										}
									}

									if (!$app->getInput()->getBool('print')) {
										$info_block .= '</a>';
									} else {
										$info_block .= '</span>';
									}

									$info_block .= self::getPostData($params->get('prepend_links', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $params->get('show_icon_links', 0), 'link', $params->get('icon_links', ''));

									$info_block .= '</span>';
								}

								$info_block .= '</span>';

								$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINKS'), $value['show_icon'], 'link', $value['icon']);

								$info_block .= '</span>';

								$has_info_from_previous_detail = true;
							} // end all links a b c

							// link a
							if ($value['info'] == 'linka' && !empty($urls->urla)) {

								$info_block .= '<span class="detail detail_link detail_linka' . $extraclasses . '">';

								$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $value['show_icon'], 'link', $value['icon']);

								if (!$app->getInput()->getBool('print')) {
									$info_block .= self::getATagLinks($urls->urla, $urls->urlatext, $targeta, false, '600', '500', 'detail_data');
								} else {
									$info_block .= '<span class="detail_data">';
								}

								if (!empty($urls->urlatext)) {
									$info_block .= $urls->urlatext;
								} else {
									if (!$params->get('protocol', 1)) {
										$info_block .= self::remove_protocol($urls->urla);
									} else {
										$info_block .= $urls->urla;
									}
								}

								if (!$app->getInput()->getBool('print')) {
									$info_block .= '</a>';
								} else {
									$info_block .= '</span>';
								}

								$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $value['show_icon'], 'link', $value['icon']);

								$info_block .= '</span>';

								$has_info_from_previous_detail = true;
							}

							// link b
							if ($value['info'] == 'linkb' && !empty($urls->urlb)) {

								$info_block .= '<span class="detail detail_link detail_linkb' . $extraclasses . '">';

								$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $value['show_icon'], 'link', $value['icon']);

								if (!$app->getInput()->getBool('print')) {
									$info_block .= self::getATagLinks($urls->urlb, $urls->urlbtext, $targetb, false, '600', '500', 'detail_data');
								} else {
									$info_block .= '<span class="detail_data">';
								}

								if (!empty($urls->urlbtext)) {
									$info_block .= $urls->urlbtext;
								} else {
									if (!$params->get('protocol', 1)) {
										$info_block .= self::remove_protocol($urls->urlb);
									} else {
										$info_block .= $urls->urlb;
									}
								}

								if (!$app->getInput()->getBool('print')) {
									$info_block .= '</a>';
								} else {
									$info_block .= '</span>';
								}

								$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $value['show_icon'], 'link', $value['icon']);

								$info_block .= '</span>';

								$has_info_from_previous_detail = true;
							}

							if ($value['info'] == 'linkc' && !empty($urls->urlc)) {

								$info_block .= '<span class="detail detail_link detail_linkc' . $extraclasses . '">';

								$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $value['show_icon'], 'link', $value['icon']);

								if (!$app->getInput()->getBool('print')) {
									$info_block .= self::getATagLinks($urls->urlc, $urls->urlctext, $targetc, false, '600', '500', 'detail_data');
								} else {
									$info_block .= '<span class="detail_data">';
								}

								if (!empty($urls->urlctext)) {
									$info_block .= $urls->urlctext;
								} else {
									if (!$params->get('protocol', 1)) {
										$info_block .= self::remove_protocol($urls->urlc);
									} else {
										$info_block .= $urls->urlc;
									}
								}

								if (!$app->getInput()->getBool('print')) {
									$info_block .= '</a>';
								} else {
									$info_block .= '</span>';
								}

								$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_LINK'), $value['show_icon'], 'link', $value['icon']);

								$info_block .= '</span>';

								$has_info_from_previous_detail = true;
							}
						}
					}
				break;

				case 'tags':
				case 'linkedtags':

				    if (isset($item->tags) && !empty($item->tags->itemTags) && ($item_params->get('ad_show_tags') || $force_show)) {

						$item_tags = $item->tags->itemTags;

						// remove tags to hide
						$tags_to_hide = $params->get('hide_tags');
						if (!empty($tags_to_hide)) {
							foreach ($item_tags as $key => $item_tag) {
								if (in_array($item_tag->id, $tags_to_hide)) {
									unset($item_tags[$key]);
								}
							}
						}

						if (!empty($item_tags)) {

							// order tags

							switch ($params->get('order_tags', 'none')) {
							    case 'console': usort($item_tags, array(__CLASS__, 'compare_tags_by_console')); break;
							    case 'alpha': usort($item_tags, array(__CLASS__, 'compare_tags_by_name')); break;
							}

							if ($has_info_from_previous_detail) {
								$info_block .= '<span class="delimiter">'.$separator.'</span>';
							}

							if ($params->get('distinct_tags', 0)) {  // tags as distinct entities

								$info_block .= '<span class="detail detail_tags' . $extraclasses . '">';

								$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_TAGS'), $value['show_icon'], 'tags', $value['icon']);

								$info_block .= '<span class="detail_multi_data">';

								foreach ($item_tags as $i => $tag) {

									if (Factory::getLanguage()->hasKey($tag->title)) {
										$tag->title = Text::_($tag->title);
									}

									$info_block .= '<span class="distinct distinct_tag tag_'.$tag->id.'">';

									$info_block .= self::getPreData($params->get('prepend_tags', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_TAG'), $params->get('show_icon_tags', 0), 'tag2', $params->get('icon_tags', ''));

									$info_block .= '<span class="detail_data">';

									$tag_class_attribute = '';
									if ($params->get('bootstrap_tags', 0)) { // in fact, get classes per tag from the console
										$tagParams = new Registry($tag->params);
										$tag_class_attribute = ' '.$tagParams->get('tag_link_class', SYWUtilities::getBootstrapProperty('label label-info', $bootstrap_version));
									} else if (trim($params->get('tag_classes', ''))) {
										$tag_class_attribute = ' '.trim($params->get('tag_classes'));
									}

									if ($value['info'] == 'linkedtags' && !$app->getInput()->getBool('print')) {
										$info_block .= '<a href="'.Route::_(TagsRouteHelper::getTagRoute($tag->id . ':' . $tag->alias)).'" class="detail_data'.$tag_class_attribute.'">'.$tag->title.'</a>';
									} else {
										$info_block .= '<span class="detail_data'.$tag_class_attribute.'">'.$tag->title.'</span>';
									}

									$info_block .= '</span>';

									$info_block .= self::getPostData($params->get('prepend_tags', ''), Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_TAG'), $params->get('show_icon_tags', 0), 'tag2', $params->get('icon_tags', ''));

									$info_block .= '</span>';

									if ($i < count($item_tags) - 1) {
										$info_block .= '<span class="delimiter"> </span>';
									}
								}

								$info_block .= '</span>';

								$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_TAGS'), $value['show_icon'], 'tags', $value['icon']);

								$info_block .= '</span>';

								$has_info_from_previous_detail = true;

							} else {  // tags as list of items

								$info_block .= '<span class="detail detail_tags' . $extraclasses . '">';

								$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_TAGS'), $value['show_icon'], 'tags', $value['icon']);

								$info_block .= '<span class="detail_data">';

								foreach ($item_tags as $i => $tag) {

									if (Factory::getLanguage()->hasKey($tag->title)) {
										$tag->title = Text::_($tag->title);
									}

									if ($value['info'] == 'tags') {
										$info_block .= $tag->title;
									} else {
										if (!$app->getInput()->getBool('print')) {
											$info_block .= '<a href="'.Route::_(TagsRouteHelper::getTagRoute($tag->id . ':' . $tag->alias)).'">';
											$info_block .= $tag->title;
											$info_block .= '</a>';
										} else {
											$info_block .= $tag->title;
										}
									}

									if ($i < count($item_tags) - 1) {
										$info_block .= Text::_('PLG_CONTENT_ARTICLEDETAILS_TAGSSEPARATOR');
									}
								}

								$info_block .= '</span>';

								$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_TAGS'), $value['show_icon'], 'tags', $value['icon']);

								$info_block .= '</span>';

								$has_info_from_previous_detail = true;
							}
						} // end not empty tags
					}
				break;

				case 'share':
					if (!empty($item->link) && !$app->getInput()->getBool('print')) {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_social' . $extraclasses . '">';

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_SHARE'), $value['show_icon'], 'share2', $value['icon']);

						$info_block .= '<span class="detail_data">';

						$root_path = rtrim(Uri::root(), "/");

						$url = str_replace(array("tmpl=component", "print=1"), "", $item->link);
						$url = rtrim($url, "?&amp;");

						$base_path = Uri::base(true);

						// remove base path from item link if it is already there
						if ($base_path && strpos($url, $base_path) === 0) {
							$url = substr($url, strlen($base_path));
						}

						// backward compatibility

						$icons_to_show = array('none');
						if ($params->get('share_email', 0)) {
							$icons_to_show[] = 'email';
						}
						if ($params->get('share_facebook', 0)) {
							$icons_to_show[] = 'facebook';
						}
						if ($params->get('share_twitter', 0)) {
							$icons_to_show[] = 'twitter';
						}
						if ($params->get('share_linkedin', 0)) {
							$icons_to_show[] = 'linkedin';
						}

						// end backward compatibility

						$share_classes = trim($params->get('share_classes', ''));
						$share_classes = empty($share_classes) ? '' : ' '.$share_classes;

						$social_networks = $params->get('social_networks'); // array of objects
						if (!empty($social_networks) && is_object($social_networks)) {

							foreach ($social_networks as $social_network) {
								switch ($social_network->social_network) {
									case 'email': $info_block .= self::sendToFriendIcon($item->title, $root_path.$url, $share_classes); break;
									case 'facebook': $info_block .= self::getFacebookButton(htmlspecialchars($item->title), $root_path.$url, $share_classes); break;
									case 'twitter': $info_block .= self::getTwitterButton(htmlspecialchars($item->title), $root_path.$url, $share_classes); break;
									case 'linkedin': $info_block .= self::getLinkedInButton(htmlspecialchars($item->title), $root_path.$url, $share_classes); break;
								}
							}
						}

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_SHARE'), $value['show_icon'], 'share2', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'email':

				    if ($item->link && !$app->getInput()->getBool('print')) {

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_email' . $extraclasses . '">';

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_EMAIL'), $value['show_icon'], 'email', $value['icon']);

						$info_block .= '<span class="detail_data">';
						
						$root_path = rtrim(Uri::root(), "/");

						$url = str_replace(array("tmpl=component", "print=1"), "", $item->link);
						$url = rtrim($url, "?&amp;");
						
						$base_path = Uri::base(true);
						
						// remove base path from item link if it is already there
						if ($base_path && strpos($url, $base_path) === 0) {
						    $url = substr($url, strlen($base_path));
						}

						$attribs = array(
							'title'   => Text::_('JGLOBAL_EMAIL'),
							'class' => 'hasTooltip'
// 							'onclick' => "window.open(this.href,'win2','"width=400,height=350,menubar=yes,resizable=yes"'); return false;"
						);

						$text = '<i class="SYWicon-email"></i><span>' . Text::_('JGLOBAL_EMAIL') . '</span>';

						$info_block .= HTMLHelper::_('link', 'mailto:?subject=' . urlencode(htmlspecialchars($item->title)) . '&amp;body=' . urlencode($root_path . $url), $text, $attribs);

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_EMAIL'), $value['show_icon'], 'email', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'print':

					if (isset($item->slug) && !$app->getInput()->getBool('print')) {
						// only article and blog views get slug property

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_print' . $extraclasses . '">';

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_PRINT'), $value['show_icon'], 'print', $value['icon']);

						$info_block .= '<span class="detail_data">';

						if (isset($item->language)) {
							$url  = ContentRouteHelper::getArticleRoute($item->slug, $item->catid, $item->language);
						} else {
							$url  = ContentRouteHelper::getArticleRoute($item->slug, $item->catid);
						}
						$url .= '&tmpl=component&print=1&layout=default&page=' . @ $app->getInput()->get('request')->limitstart;

						$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

						$attribs = array(
							'title'   => Text::_('JGLOBAL_PRINT'),
							'class' => 'hasTooltip',
							'onclick' => "window.open(this.href,'win2','".$status."'); return false;",
							'rel'     => 'nofollow'
						);

						$text = '<i class="SYWicon-print"></i><span>'.Text::_('JGLOBAL_PRINT').'</span>';

						$info_block .= HTMLHelper::_('link', $url, $text, $attribs);

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_PRINT'), $value['show_icon'], 'print', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'associations':

				    if (isset($item->associations) && !empty($item->associations) && ($item_params->get('show_associations') || $force_show)) {

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_associations' . $extraclasses . '">';

						$info_block .= self::getPreData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_ASSOCIATIONS'), $value['show_icon'], 'language', $value['icon']);

						$info_block .= '<span class="detail_data">';

						foreach ($item->associations as $association) {
							if ($item_params->get('flags', 1) && $association['language']->image) {
								$flag = HTMLHelper::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('class' => 'hasTooltip', 'title' => $association['language']->title_native), true);
								$info_block .= '&nbsp;<a href="'.Route::_($association['item']).'">'.$flag.'</a>&nbsp;';
							} else {
								$class = 'label label-association label-' . $association['language']->sef;
								$info_block .= '&nbsp;<a class="'.$class.'" href="'.Route::_($association['item']).'">'.strtoupper($association['language']->sef).'</a>&nbsp;';
							}
						}

						$info_block .= '</span>';

						$info_block .= self::getPostData($value['prepend'], Text::_('PLG_CONTENT_ARTICLEDETAILS_PREPEND_ASSOCIATIONS'), $value['show_icon'], 'language', $value['icon']);

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;
			}
		}

		$info_block .= '</dd>';

		// when using the vote form, potential delimiter if the next info is on the same line
		// we should never have a delimiter at the start of the line
		if (!empty($separator)) {
			 $info_block = str_replace('<dd class="details"><span class="delimiter">'.$separator.'</span>', '<dd class="details">', $info_block);
		} else {
			 $info_block = str_replace('<dd class="details"><span class="delimiter"> </span>', '<dd class="details">', $info_block);
		}

		// remove potential <dd class="details"></dd> when no data is available
		$info_block = str_replace('<dd class="details"></dd>', '', $info_block);

		if (strpos($info_block, 'dd') === false) {
			return ''; // accessibility rule: if no dd then no dt is allowed
		}

		return $info_block;
	}

	/**
	 *
	 * Generate a link that displays a popup with e-mail form.
	 * The form can be used to send page to friends
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	public static function sendToFriendIcon($title, $link, $classes = '')
	{
		$link = rawurldecode($link);

		$attribs = array(
			'title' => Text::_('JGLOBAL_EMAIL'),
			'class' => 'hasTooltip sendtofriend'.$classes
		);

		$text = '<span class="svg_container">';
		$text .= '<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z"></path></svg>';
		$text .= '</span>';

		$output = HTMLHelper::_('link', 'mailto:?subject=' . rawurlencode($title) . '&amp;body=' . $link, $text, $attribs);

		return $output;
	}

	public static function getFacebookButton($title, $link, $classes = '')
	{
		$html = '';

		$html .= '<a class="hasTooltip facebook'.$classes.'" href="https://www.facebook.com/sharer.php?u='.$link.'&amp;t='.urlencode($title).'" aria-label="'.Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "Facebook").'" title="'.Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "Facebook").'" target="_blank" >';
			$html .= '<span class="svg_container">';
				$html .= '<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="m 371.14,288 14.22,-92.66 h -88.91 v -60.13 c 0,-25.35 12.42,-50.06 52.24,-50.06 h 40.42 V 6.26 C 389.11,6.26 352.43,0 317.36,0 244.14,0 196.28,44.38 196.28,124.72 v 70.62 H 114.89 V 288 h 81.39 V 512 H 296.45 V 288 Z"></path></svg>';
			$html .= '</span>';
		$html .= '</a>';

		return $html;
	}

	/*
	 * deprecated - no longer exists
	 */
	public static function getGoogleButton($link, $classes = '')
	{
		$html = '';

// 		$html .= '<a class="hasTooltip googleplus'.$classes.'" href="https://plus.google.com/share?url='.$link.'" aria-label="'.Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "Google Plus").'" title="'.Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "Google Plus").'" target="_blank" >';
// 			$html .= '<i class="SYWicon-googleplus" aria-hidden="true"></i>';
// 		$html .= '</a>';

		return $html;
	}

	/*
	 * Stumbleupon has been replaced with Mix - not used
	 */
	public static function getStumbleuponButton($title, $link, $classes = '')
	{
		$html = '';

		$html .= '<a class="hasTooltip stumbleupon mix' . $classes . '" href="https://mix.com/add?url=' . $link . '" aria-label="'.Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "Mix").'" title="' . Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "Mix").'" target="_blank" >';
    		$html .= '<span class="svg_container">';
				$html .= '<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="m 30,64 v 348.9 c 0,56.2 88,58.1 88,0 V 174.3 c 7.9,-52.9 88,-50.4 88,6.5 v 175.3 c 0,57.9 96,58 96,0 V 240 c 5.3,-54.7 88,-52.5 88,4.3 v 23.8 c 0,59.9 88,56.6 88,0 V 64 Z"></path></svg>';
    		$html .= '</span>';
		$html .= '</a>';

		return $html;
	}

	public static function getTwitterButton($title, $link, $classes = '')
	{
		$html = '';

		$html .= '<a class="hasTooltip twitter'.$classes.'" href="https://twitter.com/intent/tweet?text='.urlencode($title)."&amp;url=".$link.'" aria-label="'.Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "X-Twitter").'" title="'.Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "X-Twitter").'" target="_blank" >';
    		$html .= '<span class="svg_container">';
				$html .= '<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>';
    		$html .= '</span>';
		$html .= '</a>';

		return $html;
	}

	public static function getLinkedInButton($title, $link, $classes = '')
	{
		$html = '';

		$html .= '<a class="hasTooltip linkedin'.$classes.'" href="https://www.linkedin.com/shareArticle?mini=true&amp;url='.$link.'&amp;title='.urlencode($title).'" aria-label="'.Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "LinkedIn").'" title="'.Text::sprintf("PLG_CONTENT_ARTICLEDETAILS_SHAREWITH", "LinkedIn").'" target="_blank" >';
    		$html .= '<span class="svg_container">';
				$html .= '<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="M 132.28,448 H 39.4 V 148.9 h 92.88 z M 85.79,108.1 C 56.09,108.1 32,83.5 32,53.8 a 53.79,53.79 0 0 1 107.58,0 c 0,29.7 -24.1,54.3 -53.79,54.3 z M 479.9,448 H 387.22 V 302.4 c 0,-34.7 -0.7,-79.2 -48.29,-79.2 -48.29,0 -55.69,37.7 -55.69,76.7 V 448 H 190.46 V 148.9 h 89.08 v 40.8 h 1.3 c 12.4,-23.5 42.69,-48.3 87.88,-48.3 94,0 111.28,61.9 111.28,142.3 V 448 Z"></path></svg>';
    		$html .= '</span>';
		$html .= '</a>';

		return $html;
	}

	// TODO add Pinterest support

	static function remove_protocol($url)
	{
		$disallowed = array('http://', 'https://');
		foreach($disallowed as $d) {
			if(strpos($url, $d) === 0) {
				return str_replace($d, '', $url);
			}
		}
		return $url;
	}

	static function getInlineStyles($params)
	{
		$_style = '';

		// additional user styles
		$_user_style = trim($params->get('style_overrides', ''));
		if (!empty($_user_style)) {
			$_style .= $_user_style.' ';
		}

		// font details

		$font_details = $params->get('fontdetails', '');
		if (!empty($font_details)) {
			$font_details = str_replace('\'', '"', $font_details); // " lost, replaced by '

			$google_font = SYWUtilities::getGoogleFont($font_details); // get Google font, if any
			if ($google_font) {
				SYWFonts::loadGoogleFont($google_font);
			}

			$_style .= '.articledetails .info .details {';
			$_style .= 'font-family: '.$font_details;
			$_style .= '} ';
		}

		// get elements to override

		if (!$params->get('autohide_title', 0)) {
			$title_element = trim($params->get('title_element', ''));
			if (!empty($title_element)) {
				$elements = explode(',', $title_element);
				foreach ($elements as $element) {
					$_style .= $element.',';
				}
				$_style = rtrim($_style, ',');
				$_style .= ' { display:none; } ';
			}
		}

		$info_element = trim($params->get('info_element', '.article-info'));
		if (!empty($info_element)) {
			$elements = explode(',', $info_element);
			foreach ($elements as $element) {
				$_style .= $element.',';
			}
			$_style = rtrim($_style, ',');
			$_style .= ' { display:none; } ';
		}

		$links_element = trim($params->get('links_element', ''));
		if (!empty($links_element)) {
			$elements = explode(',', $links_element);
			foreach ($elements as $element) {
				$_style .= $element.',';
			}
			$_style = rtrim($_style, ',');
			$_style .= ' { display:none; } ';
		}

		if (!$params->get('autohide_tags', 0)) {
			$tags_element = trim($params->get('tags_element', ''));
			if (!empty($tags_element)) {
				$elements = explode(',', $tags_element);
				foreach ($elements as $element) {
					$_style .= $element.',';
				}
				$_style = rtrim($_style, ',');
				$_style .= ' { display:none; } ';
			}
		}

		$icons_element = trim($params->get('icons_element', ''));
		if (!empty($icons_element)) {
			$elements = explode(',', $icons_element);
			foreach ($elements as $element) {
				$_style .= $element.',';
			}
			$_style = rtrim($_style, ',');
			$_style .= ' { display:none; } ';
		}

		$fields_element = trim($params->get('fields_element', ''));
		if (!empty($fields_element)) {
			$elements = explode(',', $fields_element);
			foreach ($elements as $element) {
				$_style .= $element.',';
			}
			$_style = rtrim($_style, ',');
			$_style .= ' { display:none; } ';
		}

		$images_element = trim($params->get('images_element', ''));
		if (!empty($images_element)) {
			$elements = explode(',', $images_element);
			foreach ($elements as $element) {
				$_style .= $element.',';
			}
			$_style = rtrim($_style, ',');
			$_style .= ' { display:none; } ';
		}

		return $_style;
	}

	static function compare_tags_by_name($tag1, $tag2)
	{
	    $title_1 = $tag1->title ?? '';
	    $title_2 = $tag2->title ?? '';
	    
	    return strcmp($title_1, $title_2);
	}

	static function compare_tags_by_console($tag1, $tag2)
	{
		return (intval($tag1->lft) > intval($tag2->lft) ) ? 1 : -1;
	}

	static function getContact($author_id)
	{
	    if (isset(self::$contacts[$author_id])) {
	        return self::$contacts[$author_id];
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query->select('MAX(' . $db->quoteName('id') . ') AS contactid');
		$query->select($db->quoteName(array('alias', 'catid', 'webpage', 'email_to'), array('alias', 'catid', 'webpage', 'email')));
		$query->from($db->quoteName('#__contact_details'));
		$query->where($db->quoteName('published') . ' = 1');
		$query->where($db->quoteName('user_id') . ' = :userId');
		$query->bind(':userId', $author_id, ParameterType::INTEGER);

		if (Multilanguage::isEnabled()) {
		    $query->where('(' . $db->quoteName('language') . ' IS NULL OR ' . $db->quoteName('language') . ' IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . '))');
		}

		$db->setQuery($query);

		try {
		    self::$contacts[$author_id] = $db->loadObject();
		} catch (ExecutionFailureException $e) {
			//Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			return null;
		}

		return self::$contacts[$author_id];
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

}
?>