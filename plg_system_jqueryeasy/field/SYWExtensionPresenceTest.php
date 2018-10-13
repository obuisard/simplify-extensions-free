<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

/*
 * Checks if an extension is installed
 * If not, link to download, otherwise link to enable/disable
 */
class JFormFieldSYWExtensionPresenceTest extends FormField 
{
	public $type = 'SYWExtensionPresenceTest';
	
	protected $extensiontype;
	protected $extensionelement;
	protected $extensionfolder;
	//protected $minversion;
	protected $downloadlink;
	protected $downloadtext;
	protected $title;
	protected $imagesrc;
	protected $alertlevel;
	
	protected function getLabel()
	{
	    $html = '';
	    
	    \JHtml::_('bootstrap.tooltip');
	    
	    $html .= '<div>';
	    
	    $html .= '<a href="'.$this->downloadlink.'" target="_blank" class="hasTooltip" title="'.\JText::_($this->title).'">';
	    if ($this->imagesrc) {
	        $html .= '<img src="'.URI::root().$this->imagesrc.'" alt="'.\JText::_($this->title).'" style="border: 3px solid #fff">';
	    } else {
	        $html .= \JText::_($this->title);
	    }
	    $html .= '</a>';
	    
	    $html .= '</div>';
	    
	    return $html;
	}
	
	protected function getInput()
	{
		$html = '';
		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		$html .= '<span style="display: inline-block; padding-bottom: 10px">'.\JText::_($this->description).'</span><br />';
		
		$missing_extension = false;
		$alert = '';
		
		if ($this->extensiontype == 'plugin') {
		    if (!\JFolder::exists(JPATH_ROOT.'/plugins/'.$this->extensionfolder.'/'.$this->extensionelement)) {
		        $missing_extension = true;
		    } else {		    
		        if (PluginHelper::isEnabled((string)$this->extensionfolder, (string)$this->extensionelement)) {
		            $alert = ' success';
    		        //$html .= '<span class="icon-publish"></span> <span>'.\JText::_('JENABLED').'</span> <a class="btn btn-mini" href="index.php?option=com_plugins&view=plugins&filter_folder='.$this->extensionfolder.'&filter_enabled=1">'.\JText::_('LIB_SYW_SYWEXTENSIONTEST_DISABLEPLUGIN').'</a>';
    		        $html .= '<span class="label label-success">'.\JText::_('JENABLED').'</span> <a class="btn btn-mini" href="index.php?option=com_plugins&view=plugins&filter_folder='.$this->extensionfolder.'&filter_enabled=1">'.\JText::_('LIB_SYW_SYWEXTENSIONTEST_DISABLEPLUGIN').'</a>';
    		    } else {
    		        $alert = ' '.$this->alertlevel;
    		        //$html .= '<span class="icon-unpublish"></span> <span>'.\JText::_('JDISABLED').'</span> <a class="btn btn-mini" href="index.php?option=com_plugins&view=plugins&filter_folder='.$this->extensionfolder.'&filter_enabled=0">'.\JText::_('LIB_SYW_SYWEXTENSIONTEST_ENABLEPLUGIN').'</a>';
    		        $html .= '<span class="label label-important">'.\JText::_('JDISABLED').'</span> <a class="btn btn-mini" href="index.php?option=com_plugins&view=plugins&filter_folder='.$this->extensionfolder.'&filter_enabled=0">'.\JText::_('LIB_SYW_SYWEXTENSIONTEST_ENABLEPLUGIN').'</a>';
    		    }
		    }
		} else if ($this->extensiontype == 'component') {
		    if (\JFolder::exists(JPATH_ADMINISTRATOR . '/components/'.$this->extensionelement)) {
		        if (ComponentHelper::isEnabled((string)$this->extensionelement)) {
		            $alert = ' success';
		            $html .= '<span class="label label-success">'.\JText::_('JENABLED').'</span>'; // index.php?option=com_installer&view=manage&filter_status=1&filter_type=component
		        } else {
		            $alert = ' '.$this->alertlevel;
		            $html .= '<span class="label label-important">'.\JText::_('JDISABLED').'</span>'; // index.php?option=com_installer&view=manage&filter_status=0&filter_type=component
		        }
		    } else {
		        $missing_extension = true;
		    }
		}
		
		if ($missing_extension) {
		    $alert = ' '.$this->alertlevel;
		    $html .= '<a href="'.$this->downloadlink.'" target="_blank">'.\JText::_($this->downloadtext).'</a>';
		}
		
		return '<div class="syw_info'.$alert.'" style="padding-top: 5px; overflow: inherit">'.$html.'</div>';
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
		    $this->extensiontype = $this->element['extensiontype'];
		    $this->extensionelement = $this->element['extensionelement'];
		    $this->extensionfolder = isset($this->element['extensionfolder']) ? $this->element['extensionfolder'] : '';
		    //$this->minversion = isset($this->element['minversion']) ? $this->element['minversion'] : '';
			$this->downloadlink = $this->element['downloadlink'];
			$this->downloadtext = isset($this->element['downloadtext']) ? trim($this->element['downloadtext']) : 'LIB_SYW_SYWEXTENSIONTEST_DOWNLOAD';
			$this->title = isset($this->element['title']) ? trim($this->element['title']) : '';
			$this->imagesrc = isset($this->element['imagesrc']) ? $this->element['imagesrc'] : ''; // ex: modules/mod_latestnews/images/icon.png			
			$this->alertlevel = isset($this->element['alertlevel']) ? $this->element['alertlevel'] : 'info';
		}
		
		return $return;
	}
	
}
?>