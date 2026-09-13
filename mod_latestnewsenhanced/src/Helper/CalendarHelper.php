<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\LatestNewsEnhanced\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Fonts as SYWFonts;

class CalendarHelper
{
	static function getCalendarBlockData($params, $date)
	{
		$data = array();

		$weekday_format = $params->get('fmt_w', 'D');
		$month_format = $params->get('fmt_m', 'M');
		$day_format = $params->get('fmt_d', 'd');
		$time_format = $params->get('t_format', 'H:i');

		$position_1 = $params->get('pos_1', 'w');
		$position_2 = $params->get('pos_2', 'd');
		$position_3 = $params->get('pos_3', 'm');
		$position_4 = $params->get('pos_4', 'y');
		$position_5 = $params->get('pos_5', 't');

		$keys = array($position_1, $position_2, $position_3, $position_4, $position_5);

		$offset = true; // default

		foreach ($keys as $key) {
			switch ($key) {
				case 'w' :
					$data[] = array('weekday' => HTMLHelper::_('date', $date, $weekday_format, $offset)); // 3 letters or full - translate from language .ini file
					break;
				case 'd' :
					$data[] = array('day' => HTMLHelper::_('date', $date, $day_format, $offset)); // 01-31 or 1-31
					break;
				case 'm' :
					$data[] = array('month' => HTMLHelper::_('date', $date, $month_format, $offset));
					break;
				case 'y' :
					$data[] = array('year' => HTMLHelper::_('date', $date, 'Y', $offset));
					break;
				case 't' :
					$data[] = array('time' => HTMLHelper::_('date', $date, $time_format, $offset));
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

	static function getCalendarInlineStyles($params, $suffix)
	{
		$styles = '';

		$font_calendar = $params->get('fontcalendar', '');
		if (!empty($font_calendar)) {
		    SYWFonts::loadWebFonts(SYWFonts::getWebfontsFromFamily($font_calendar));

			$styles .= '#lnee_'.$suffix.' .calendar {';
			$styles .= 'font-family: '.$font_calendar.' !important;';
			$styles .= '} ';
		}

		$calendar_bg = $params->get('cal_bg', '');
		if ($calendar_bg) {
			$styles .= "#lnee_".$suffix." .newshead .calendar.image {";
			$styles .= "background: transparent url(".Uri::base().$calendar_bg.") top center no-repeat !important;";
			$styles .= "} ";
		}

		return $styles;
	}

}