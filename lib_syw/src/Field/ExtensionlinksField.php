<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class ExtensionlinksField extends FormField
{
	public $type = 'Extensionlinks';

	protected $forum;
	protected $demo;
	protected $review;
	protected $doc;
	protected $onlinedoc;
	protected $quickstart;
	protected $report;
	protected $support;
	protected $translate;
	protected $donate;
	protected $changelog;

	protected function getLabel()
	{
		return '';
	}

	protected function getButton($link, $icon, $label, $description = '', $class = '')
	{
	    $output = '';

	    $output .= '<a class="hasTooltip btn btn-dark' . ($class == '' ? '' : ' ' . $class) . '" style="margin: 0 10px 10px 0" title="'.HTMLHelper::_('tooltipText', Text::_($label), rtrim(Text::_($description), '.'), 0).'" href="'.$link.'" target="_blank">';
	    $output .= '<i class="'.$icon.'" style="font-size: 2em; padding: 5px; vertical-align: middle" aria-hidden="true"></i>';
	    $output .= '</a>';

	    return $output;
	}

	protected function getInput()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

		$html = '<div>';

		if ($this->changelog) {
		    $html .= self::getButton($this->changelog, 'SYWicon-search', 'LIB_SYW_EXTENSIONLINK_CHANGELOG_LABEL', 'LIB_SYW_EXTENSIONLINK_CHANGELOG_DESC');
		}

		if ($this->translate) {
		    $html .= self::getButton('https://simplifyyourweb.com/translators', 'SYWicon-translate', 'LIB_SYW_EXTENSIONLINK_TRANSLATE_LABEL', 'LIB_SYW_EXTENSIONLINK_TRANSLATE_DESC');
		}

		if ($this->quickstart) {
		    $html .= self::getButton($this->quickstart, 'SYWicon-timer', 'LIB_SYW_EXTENSIONLINK_QUICKSTART_LABEL', 'LIB_SYW_EXTENSIONLINK_QUICKSTART_DESC');
		}

		if ($this->doc) {
		    $html .= self::getButton($this->doc, 'SYWicon-local-library', 'LIB_SYW_EXTENSIONLINK_DOC_LABEL', 'LIB_SYW_EXTENSIONLINK_DOC_DESC');
		}

		if ($this->onlinedoc) {
		    $html .= self::getButton($this->onlinedoc, 'SYWicon-local-library', 'LIB_SYW_EXTENSIONLINK_ONLINEDOC_LABEL', 'LIB_SYW_EXTENSIONLINK_ONLINEDOC_DESC');
		}

		if ($this->forum) {
		    $html .= self::getButton($this->forum, 'SYWicon-chat', 'LIB_SYW_EXTENSIONLINK_FORUM_LABEL', 'LIB_SYW_EXTENSIONLINK_FORUM_DESC');
		}

		if ($this->forumbeta) {
		    $html .= self::getButton($this->forumbeta, 'SYWicon-chat', 'LIB_SYW_EXTENSIONLINK_FORUMBETA_LABEL', 'LIB_SYW_EXTENSIONLINK_FORUMBETA_DESC', 'btn-inverse');
		}

		if ($this->support) {
		    $html .= self::getButton($this->support, 'SYWicon-lifebuoy', 'LIB_SYW_EXTENSIONLINK_SUPPORT_LABEL', 'LIB_SYW_EXTENSIONLINK_SUPPORT_DESC');
		}

		if ($this->report) {
		    $html .= self::getButton($this->report, 'SYWicon-bug-report', 'LIB_SYW_EXTENSIONLINK_BUGREPORT_LABEL', 'LIB_SYW_EXTENSIONLINK_BUGREPORT_DESC');
		}

		if ($this->demo) {
		    $html .= self::getButton($this->demo, 'SYWicon-visibility', 'LIB_SYW_EXTENSIONLINK_DEMO_LABEL', 'LIB_SYW_EXTENSIONLINK_DEMO_DESC');
		}

		if ($this->review) {

		    $description = rtrim(Text::_('LIB_SYW_EXTENSIONLINK_REVIEW_DESC'), '.');
		    $description .= ' <i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i>';
		    $description .= '<i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i>';
		    $description .= '<i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i>';
		    $description .= '<i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i>';
		    $description .= '<i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i>';

		    $html .= self::getButton($this->review, 'SYWicon-thumb-up', 'LIB_SYW_EXTENSIONLINK_REVIEW_LABEL', $description);
		}

		if ($this->donate) {
		    $html .= self::getButton($this->donate, 'SYWicon-favorite', 'LIB_SYW_EXTENSIONLINK_DONATE_LABEL', 'LIB_SYW_EXTENSIONLINK_DONATE_DESC');
		}

		$html .= '</div>';

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->forum = isset($this->element['forum']) ? (string)$this->element['forum'] : '';
			$this->demo = isset($this->element['demo']) ? (string)$this->element['demo'] : '';
			$this->review = isset($this->element['review']) ? (string)$this->element['review'] : '';
			$this->doc = isset($this->element['doc']) ? (string)$this->element['doc'] : '';
			$this->onlinedoc = isset($this->element['onlinedoc']) ? (string)$this->element['onlinedoc'] : '';
			$this->quickstart = isset($this->element['quickstart']) ? (string)$this->element['quickstart'] : '';
			$this->report = isset($this->element['report']) ? (string)$this->element['report'] : '';
			$this->support = isset($this->element['support']) ? (string)$this->element['support'] : '';
			$this->translate = isset($this->element['translate']) ? (string)$this->element['translate'] : '';
			$this->donate = isset($this->element['donate']) ? (string)$this->element['donate'] : '';
			$this->changelog = isset($this->element['changelog']) ? (string)$this->element['changelog'] : '';
		}

		return $return;
	}

}
?>
