<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

/*
 * Checks if an extension is installed
 * If not, link to download, otherwise link to enable/disable
 */
class SywextensionpresencetestField extends FormField
{
	public $type = 'Sywextensionpresencetest';

	protected $extensiontype;
	protected $extensionelement;
	protected $extensionfolder;
	//protected $minversion;
	protected $downloadlink;
	protected $downloadtext;
	protected $title;
	protected $imagesrc;
	protected $alertlevel;
	protected $message;

	protected function getLabel()
	{
	    $html = '';

	    HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

	    //$html .= '<div>';

	    //$html .= '<a class="btn hasTooltip" href="'.$this->downloadlink.'" target="_blank" title="'.Text::_($this->title).'">';
	    if ($this->imagesrc) {
	        $html .= '<img src="'.URI::root().$this->imagesrc.'" alt="'.Text::_($this->title).'">';
	    } else {
	        $html .= Text::_($this->title);
	    }
	    //$html .= '</a>';

	    //$html .= '</div>';

	    return $html;
	}

	protected function getInput()
	{
		$html = '';

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		if ($this->message) {
			$html .= '<span style="display: inline-block; padding-bottom: 10px">' . $this->message . '</span><br />';
		}

		$missing_extension = false;
		$alert = '';

		if ($this->extensiontype == 'plugin') {
		    if (!is_dir(JPATH_ROOT.'/plugins/'.$this->extensionfolder.'/'.$this->extensionelement)) {
		        $missing_extension = true;
		    } else {
		        if (PluginHelper::isEnabled((string)$this->extensionfolder, (string)$this->extensionelement)) {
		            $alert = ' success';
    		        $html .= '<a class="btn btn-secondary btn-sm" href="index.php?option=com_plugins&view=plugins&filter[folder]='.$this->extensionfolder.'&filter[element]='.$this->extensionelement.'&filter[enabled]=1">'.Text::_('LIB_SYW_SYWEXTENSIONTEST_DISABLEPLUGIN').'</a>';
    		    } else {
    		        $alert = ' '.$this->alertlevel;
    		        $html .= '<a class="btn btn-primary btn-sm" href="index.php?option=com_plugins&view=plugins&filter[folder]='.$this->extensionfolder.'&filter[element]='.$this->extensionelement.'&filter[enabled]=0">'.Text::_('LIB_SYW_SYWEXTENSIONTEST_ENABLEPLUGIN').'</a>';
    		    }
		    }
		} else if ($this->extensiontype == 'component') {
		    if (is_dir(JPATH_ADMINISTRATOR . '/components/'.$this->extensionelement)) {
		        if (ComponentHelper::isEnabled((string)$this->extensionelement)) {
		            $alert = ' success';
		            $html .= '<span class="badge bg-success">'.Text::_('JENABLED').'</span>'; // index.php?option=com_installer&view=manage&filter_status=1&filter_type=component
		        } else {
		            $alert = ' '.$this->alertlevel;
		            $html .= '<span class="badge bg-warning">'.Text::_('JDISABLED').'</span>'; // index.php?option=com_installer&view=manage&filter_status=0&filter_type=component
		        }
		    } else {
		        $missing_extension = true;
		    }
		}

		if ($missing_extension) {
		    $alert = ' '.$this->alertlevel;
		    $html .= '<a class="btn btn-sm btn-' . $this->alertlevel . '" href="'.$this->downloadlink.'" target="_blank">'.Text::_($this->downloadtext).'</a>';
		}

		return '<div class="syw_info'.$alert.'" style="padding-top: 5px; overflow: inherit">'.$html.'</div>';
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->extensiontype = (string)$this->element['extensiontype'];
			$this->extensionelement = (string)$this->element['extensionelement'];
			$this->extensionfolder = isset($this->element['extensionfolder']) ? (string)$this->element['extensionfolder'] : '';
		    //$this->minversion = isset($this->element['minversion']) ? $this->element['minversion'] : '';
			$this->downloadlink = (string)$this->element['downloadlink'];
			$this->downloadtext = isset($this->element['downloadtext']) ? trim((string)$this->element['downloadtext']) : 'LIB_SYW_SYWEXTENSIONTEST_DOWNLOAD';
			$this->title = isset($this->element['title']) ? trim((string)$this->element['title']) : '';
			$this->imagesrc = isset($this->element['imagesrc']) ? (string)$this->element['imagesrc'] : ''; // ex: modules/mod_latestnews/images/icon.png
			$this->alertlevel = isset($this->element['alertlevel']) ? (string)$this->element['alertlevel'] : 'info';
			$this->message = isset($this->element['message']) ? trim(Text::_((string)$this->element['message'])) : '';
		}

		return $return;
	}

}
?>