<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use SYW\Library\Plugin as SYWPLugin;

class ImagelibrarytestField extends FormField
{
	public $type = 'Imagelibrarytest';

	protected $supported_types; // can be gif jpg png webp avif
	protected $message;
	protected $check_all; // checks all libraries for info (true in SYW library plugin only)

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		$image_library = '';
		
		if (!$this->check_all) {		    
		    $image_library = SYWPlugin::getImageLibrary();
		}

		$html = '';

		if (!extension_loaded('gd') && !extension_loaded('imagick')) {
			$html .= '<div style="margin: 0" class="alert alert-warning">';
				$html .= '<span style="display: inline-block; padding-bottom: 10px">'. $this->message .'</span><br />';
				$html .= '<span>'.Text::_('LIB_SYW_IMAGELIBRARYTEST_NOLIBRARYLOADED').'</span>';
			$html .= '</div>';

			return $html;
		} 
		
		$html .= '<div style="margin: 0" class="alert alert-success">';
		
		if ($this->check_all) {
		    $html .= '<span>'. $this->message .'</span><br /><br />';
		}
		
		if (($image_library === 'gd' || $image_library === '') && extension_loaded('gd')) {
				
		    if ($this->check_all) {
		        $html .= '<span>' . Text::sprintf('LIB_SYW_PHPEXTENSION_INSTALLED', 'gd') . ' (' . GD_VERSION . ')' . '</span><br />';
		    } else {
		        $html .= '<span>' . Text::sprintf('LIB_SYW_IMAGELIBRARYTEST_USED', 'gd') . '</span><br />';
		    }

			if (in_array('gif', $this->supported_types)) {
				if (imagetypes() & IMG_GIF) {
					$html .= '<span class="badge bg-success">GIF '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="badge bg-warning">GIF '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}

			if (in_array('jpg', $this->supported_types)) {
				if (imagetypes() & IMG_JPG) {
					$html .= '<span class="badge bg-success">JPG '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="badge bg-warning">JPG '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}

			if (in_array('png', $this->supported_types)) {
				if (imagetypes() & IMG_PNG) {
					$html .= '<span class="badge bg-success">PNG '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="badge bg-warning">PNG '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}

			if (in_array('webp', $this->supported_types)) {
				if (imagetypes() & IMG_WEBP) {
					$html .= ' <span class="badge bg-success">WEBP '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= ' <span class="badge bg-warning">WEBP '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}
			
			if (in_array('avif', $this->supported_types)) {
			    if (function_exists('imageavif')) {
			        $html .= ' <span class="badge bg-success">AVIF '.lcfirst(Text::_('JENABLED')).'</span> ';
			    } else {
			        $html .= ' <span class="badge bg-warning">AVIF '.lcfirst(Text::_('JDISABLED')).'</span> ';
			    }
			}
		} else {
		    if ($this->check_all) {
		        $html .= '<span>' . Text::sprintf('LIB_SYW_PHPEXTENSION_NOTINSTALLED', 'gd') . '</span>';
		    }
		}
		
		if (($image_library === 'imagick' || $image_library === '') && extension_loaded('imagick')) {
		    
		    if ($this->check_all) {
		        $html .= '<br /><br />';
		    }
		    
		    if ($this->check_all) {		        
		        $version = \Imagick::getVersion();
		        $html .= '<span>' . Text::sprintf('LIB_SYW_PHPEXTENSION_INSTALLED', 'imagick') . ' (' . $version['versionString'] . ')' . '</span><br />';
		    } else {
		        $html .= '<span>' . Text::sprintf('LIB_SYW_IMAGELIBRARYTEST_USED', 'imagick') . '</span><br />';
		    }
		    
		    if (in_array('gif', $this->supported_types)) {
		        if (\Imagick::queryFormats('GIF')) {
		            $html .= '<span class="badge bg-success">GIF '.lcfirst(Text::_('JENABLED')).'</span> ';
		        } else {
		            $html .= '<span class="badge bg-warning">GIF '.lcfirst(Text::_('JDISABLED')).'</span> ';
		        }
		    }
		    
		    if (in_array('jpg', $this->supported_types)) {
		        if (\Imagick::queryFormats('JPG')) {
		            $html .= '<span class="badge bg-success">JPG '.lcfirst(Text::_('JENABLED')).'</span> ';
		        } else {
		            $html .= '<span class="badge bg-warning">JPG '.lcfirst(Text::_('JDISABLED')).'</span> ';
		        }
		    }
		    
		    if (in_array('png', $this->supported_types)) {
		        if (\Imagick::queryFormats('PNG')) {
		            $html .= '<span class="badge bg-success">PNG '.lcfirst(Text::_('JENABLED')).'</span> ';
		        } else {
		            $html .= '<span class="badge bg-warning">PNG '.lcfirst(Text::_('JDISABLED')).'</span> ';
		        }
		    }
		    
		    if (in_array('webp', $this->supported_types)) {
		        if (\Imagick::queryFormats('WEBP')) {
		            $html .= ' <span class="badge bg-success">WEBP '.lcfirst(Text::_('JENABLED')).'</span> ';
		        } else {
		            $html .= ' <span class="badge bg-warning">WEBP '.lcfirst(Text::_('JDISABLED')).'</span> ';
		        }
		    }
		    
		    if (in_array('avif', $this->supported_types)) {
		        if (\Imagick::queryFormats('AVIF')) {
		            $html .= ' <span class="badge bg-success">AVIF '.lcfirst(Text::_('JENABLED')).'</span> ';
		        } else {
		            $html .= ' <span class="badge bg-warning">AVIF '.lcfirst(Text::_('JDISABLED')).'</span> ';
		        }
		    }
		} else {
		    if ($this->check_all) {
		        $html .= '<br /><br /><span>' . Text::sprintf('LIB_SYW_PHPEXTENSION_NOTINSTALLED', 'imagick') . '</span>';
		    }
		}
		
		if (!$this->check_all) {
		    if (($image_library === 'gd' && extension_loaded('imagick')) || ($image_library === 'imagick' && extension_loaded('gd'))) {
		        $html .= '<br /><br /><span>' . Text::_('LIB_SYW_IMAGELIBRARYTEST_SWITCHLIBRARYINPLUGIN') . '</span>';
    		}
		}
		
		$html .= '</div>';

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
		    
		    $lang = Factory::getLanguage();
		    $lang->load('lib_syw.sys', JPATH_SITE);
		    
			$supportedtypes = isset($this->element['supportedtypes']) ? strtolower(str_replace(' ', '', (string)$this->element['supportedtypes'])) : 'gif,jpg,png,webp,avif';
			$this->supported_types = explode(',', $supportedtypes);
			$this->message = isset($this->element['message']) ? trim(Text::_((string)$this->element['message'])) : Text::_('LIB_SYW_IMAGELIBRARYTEST_LIBRARYREQUIREDTOPROCESSIMAGES');
			$this->check_all = isset($this->element['checkall']) ? filter_var($this->element['checkall'], FILTER_VALIDATE_BOOLEAN) : false;
		}

		return $return;
	}

}
?>
