<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;

use SYW\Library\Cache as SYWCache;

/**
 * Abstract class for the caching of header files
 */
abstract class HeaderFilesCache
{
	/**
	 * The extension that requests the caching
	 *
	 * @var string
	 */
	protected $extension;

	/**
	 * The parameters actually needed to generate the content
	 *
	 * @var array
	 */
	protected $params;

	/**
	 * The md5 content footprint
	 *
	 * @var string
	 */
	protected $footprint;

	/**
	 * The additions styles or script declarations to add to the content
	 *
	 * @var string
	 */
	protected $declaration;

	/**
	 * Method to instantiate the cache object.
	 *
	 * @param extension the extension
	 * @param params the parameters of the extension
	 */
	public function __construct($extension, $params = null)
	{
		$this->extension = $extension;
		$this->params = array();
		$this->footprint = '';
		$this->declaration = '';
	}

	/**
	 * Add compressed style or script declarations to the content
	 */
	public function addDeclaration($declaration = '', $type = 'css')
	{
		$remove_comments = false;
		if ($type == 'css') {
			$remove_comments = true;
		}

		if (!empty($declaration)) {
			$declaration = $this->compress($declaration, $remove_comments);
		}

		$this->declaration = $declaration;
	}

	/**
	 * Cache the stylesheet or script
	 *
	 * @param string $output_file
	 * @param boolean $reset avoids the re-creation of the files
	 */
	public function cache($output_file, $reset = true)
	{
		Log::addLogger(array('text_file' => 'syw.errors.php'), Log::ALL, array('syw'));

		$cache_path = $this->getCachePath(true);

		if (!$reset && is_file($cache_path . '/' . $output_file)) {
			return true;
		}

		$buffer = $this->getBuffer();
		
		/* Removed to avoid memory issues and not so useful checks, which time to complete

		$this->footprint = md5($buffer.$this->declaration);

		// check if footprint of file online is the same
		if (File::exists($cache_path . '/' . $output_file)) {
		    $content = @file_get_contents($cache_path . '/' . $output_file);
			if ($content === false) {
				if (defined('JDEBUG') && JDEBUG) {
					Log::add('SYWHeaderFilesCache:cache() - Warning with file_get_contents - Cannot check content footprint', Log::WARNING, 'syw');
				}
			} else if (md5($content) == $this->footprint) { // no need to re_create the file because there are no changes
			    return true;
			}
		}
		
		*/

		$result = @file_put_contents($cache_path . '/' . $output_file, $buffer.$this->declaration);
		if ($result === false) {
			Log::add('SYWHeaderFilesCache:cache() - Error in file_put_contents', Log::ERROR, 'syw');
			return false;
		}

		/* Removed so we have one write only

		if ($this->declaration) {
			$result = @file_put_contents($cache_path . '/' . $output_file, $this->declaration, FILE_APPEND);
			if ($result === false) {
				Log::add('SYWHeaderFilesCache:cache() - Error in file_put_contents when appending content', Log::ERROR, 'syw');
				return false;
			}
		}

		*/

		return true;
	}

	/**
	 * Get the cache path for the extension
	 *
	 * @param boolean $include_root
	 * @return string
	 */
	public function getCachePath($include_root = false)
	{
		$path = '';

		if ($include_root) {
			$path = JPATH_SITE . '/';
		}

		$path .= 'media/cache';

		if (SYWCache::isFolderReady($path, $this->extension)) {
			return $path . '/' . $this->extension;
		}

		return $path;
	}

	/**
	 * Output CSS or JavaScript when requested
	 */
	protected function getBuffer()
	{
		return '';
	}

	/**
	 * Add the header to the content
	 *
	 * @param String $type css or js
	 */
	protected function sendHttpHeaders($type = 'css')
	{
		// send the content-type header
		if ($type == 'css') {
			header("Content-type: text/css; charset=UTF-8");
		} else {
			header("Content-type: text/javascript; charset=UTF-8");
		}

		header('Cache-Control: must-revalidate');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT'); // 7 days
	}

	/**
	 * Remove empty characters and comments
	 * params are setup this way for backward compatibility
	 *
	 * @param mixed $buffer
	 * @param boolean $remove_comments
	 * @param string $type (css|js)
	 * @return mixed
	 */
	protected function compress($buffer, $remove_comments = true, $type = 'css') {

		// remove comments
		if ($remove_comments) {
			if ($type == 'css') {
				$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
			} else if ($type == 'js') {
				$buffer = preg_replace('!\/\*[\s\S]*?\*\/|\/\/.*!', '', $buffer);
			}
		}

		// remove tabs, spaces, newlines, etc...
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

		return $buffer;
	}

}