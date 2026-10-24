<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\Content\ArticleDetails\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use SYW\Library\Fonts;
use SYW\Library\Utilities;

class CalendarHelper
{
	static function getCalendarBlockData($params, $date)
	{
		$data = array();

		$db = Factory::getDbo();

		if ($date == $db->getNullDate() || $date == null) {
			return null;
		}

		$weekday_format = $params->get('fmt_w', 'D');
		$month_format = $params->get('fmt_m', 'M');
		$day_format = $params->get('fmt_d', 'd');
		$time_format = Text::_('PLG_CONTENT_ARTICLEDETAILS_FORMAT_TIME');
		if (empty($time_format)) {
			$time_format = $params->get('t_format', 'H:i');
		}

		$article_date = new Date($date);

		$position_1 = $params->get('pos_1', 'w');
		$position_2 = $params->get('pos_2', 'd');
		$position_3 = $params->get('pos_3', 'm');
		$position_4 = $params->get('pos_4', 'y');
		$position_5 = $params->get('pos_5', 't');

		$keys = array($position_1, $position_2, $position_3, $position_4, $position_5);

		foreach ($keys as $key) {
			switch ($key) {
				case 'w' :
					$data[] = array('weekday' => $article_date->format($weekday_format)); // 3 letters or full - translate from language .ini file
					break;
				case 'd' :
					$data[] = array('day' => $article_date->format($day_format)); // 01-31 or 1-31
					break;
				case 'm' :
					$data[] = array('month' => $article_date->format($month_format));
					break;
				case 'y' :
					$data[] = array('year' => $article_date->format('Y'));
					break;
				case 't' :
				    $data[] = array('time' => HTMLHelper::_('date', $date, $time_format)); //date_format(new DateTime($date), $time_format);
					break;
				case 'e' :
					$data[] = array('empty' => '&nbsp;');
					break;
				default :
					$data[] = array();
			}
		}

		return $data;
	}

	static function getCalendarInlineStyles($params)
	{
		$styles = '';

		$font_calendar = $params->get('fontcalendar', '');
		if (!empty($font_calendar)) {
			$font_calendar = str_replace('\'', '"', $font_calendar); // " lost, replaced by '

			$google_font = Utilities::getGoogleFont($font_calendar); // get Google font, if any
			if ($google_font) {
				Fonts::loadGoogleFont($google_font);
			}

			$styles .= '.articledetails .head .calendar {';
				$styles .= 'font-family: '.$font_calendar;
			$styles .= '} ';
		}

		return $styles;
	}

}