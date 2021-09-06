<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use SYW\Library\K2 as SYWK2;

/**
 * Shows messages when K2 is installed or missing
 */
class K2messageField extends MessageField
{		
	public $type = 'K2message';
	
	protected $when_missing;
	
	static $k2_exists;
	
	static function k2exists()
	{
		if (!isset(self::$k2_exists)) {
			
			self::$k2_exists = SYWK2::exists();
		}
		
		return self::$k2_exists;
	}
	
	public function getLabel() 
	{
		if (self::k2exists() && !$this->when_missing || !self::k2exists() && $this->when_missing) {
			return parent::getLabel();
		}
			
		return '';
	}
	
	public function getInput()
	{
		if (self::k2exists() && !$this->when_missing || !self::k2exists() && $this->when_missing) {
			return parent::getInput();
		}
			
		return '';
	}
	
	public function renderField($options = array())
	{
		if (self::k2exists() && !$this->when_missing || !self::k2exists() && $this->when_missing) {
			return parent::renderField();
		}
		
		return '';
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->when_missing = isset($this->element['when_missing']) ? filter_var($this->element['when_missing'], FILTER_VALIDATE_BOOLEAN) : false;
		}
		
		return $return;
	}

}
?>