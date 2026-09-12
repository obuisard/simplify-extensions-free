<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\Helpers\StringHelper;

class Text
{
	/**
	 * Get text from string with or without stripped html tags
	 * Strips out Joomla plugin tags
	 * Joomla 3.1+ only
	 *
	 */
    static function getText($text, $type='html', $max_letter_count = 0, $strip_tags = true, $tags_to_keep = '', $strip_plugin_tags = true, $split_last_word = false)
	{
		if ($max_letter_count == 0) {
			return '';
		}

		if ($max_letter_count > 0) {
			if ($type === 'html') {
			    $text = self::stripPluginTags($text);
				if ($strip_tags) {
				    $text = strip_tags($text, $tags_to_keep);
				    return StringHelper::truncateComplex($text, $max_letter_count, !$split_last_word);
				} else {
				    return StringHelper::truncateComplex($text, $max_letter_count, !$split_last_word);
				}
			} else { // 'txt'
				return StringHelper::truncate($text, $max_letter_count, !$split_last_word, false); // no html allowed
			}
		} else { // take everything
			if ($type === 'html') {
				if ($strip_plugin_tags) {
				    $text = self::stripPluginTags($text);
				}
				if ($strip_tags) {
					if ($tags_to_keep == '') {
					    $text = strip_tags($text);
					    return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
					} else {
						$text = strip_tags($text, $tags_to_keep);
						return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
					}
				} else {
				    return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
				}
			}
		}

		return $text;
	}

	static function stripPluginTags($output)
	{
		$plugins = array();

		preg_match_all('/\{\w*/', $output, $matches);
		foreach ($matches[0] as $match) {
			$match = str_replace('{', '', $match);
			if (strlen($match)) {
				$plugins[$match] = $match;
			}
		}

		$find = array();
		foreach ($plugins as $plugin) {
			$find[] = '\{'.$plugin.'\s?.*?\}.*?\{/'.$plugin.'\}';
			$find[] = '\{'.$plugin.'\s?.*?\}';
		}
		if(!empty($find)) {
			foreach($find as $key=>$f) {
				$f = '/'.str_replace('/','\/',$f).'/';
				$find[$key] = $f;
			}
			$output = preg_replace($find ,'', $output);
		}

		return $output;
	}

}
?>