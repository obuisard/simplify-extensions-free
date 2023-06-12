<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use SYW\Library\Plugin as SYWPLugin;

class ImagetypeselectField extends ListField
{
	protected $type = 'Imagetypeselect';
	
	protected $include_avif;

	protected function getOptions() {

		$options = array();
		
		$image_library = SYWPlugin::getImageLibrary();
		
		$allow_webp = false;
		$allow_avif = false;
		
		if ($image_library === 'gd' && extension_loaded('gd')) {
		    if (function_exists('imagewebp')) {
		        $allow_webp = true;
		    }
		    if (function_exists('imageavif')) {
		        $allow_avif = true;
		    }
		}
		
		if ($image_library === 'imagick' && extension_loaded('imagick')) {
		    if (\Imagick::queryFormats('WEBP')) {
		        $allow_webp = true;
		    }
		    if (\Imagick::queryFormats('AVIF')) {
		        $allow_avif = true;
		    }
		}
		
		if ($allow_webp) {
		    $options[] = HTMLHelper::_('select.option', 'image/webp', 'image/webp', 'value', 'text', $disable = false);
		}
		
		if ($allow_avif && $this->include_avif) {
		    $options[] = HTMLHelper::_('select.option', 'image/avif', 'image/avif', 'value', 'text', $disable = false);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
	
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
	    $return = parent::setup($element, $value, $group);
	    
	    if ($return) {
	        $this->include_avif = isset($this->element['supportavif']) ? filter_var($this->element['supportavif'], FILTER_VALIDATE_BOOLEAN) : true;
	    }
	    
	    return $return;
	}
}
?>