<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/**
 * Supports a modal article picker
 */
class ArticleField extends FormField
{
	protected $type = 'Article';

	protected function getInput() 
	{ 		
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		HTMLHelper::_('bootstrap.tooltip');
	
		$db = Factory::getDBO();
 
		// Build the script
		
		$script = array();
		
		$script[] = '	function jSelectArticle_'.$this->id.'(id, title, catid, object) {';
		$script[] = '		jQuery("#'.$this->id.'_id").val(id);';
		$script[] = '		jQuery("#'.$this->id.'_name").val(title);';
		$script[] = '		jQuery("#'.$this->id.'_clear").removeClass("hidden");';
		$script[] = '		jQuery("#modalArticle'.$this->id.'").modal("hide");';		
		if ($this->required) {
			$script[] = '		document.formvalidator.validate(jQuery("#'.$this->id.'_id"));';
			$script[] = '		document.formvalidator.validate(jQuery("#'.$this->id.'_name"));';
		}
		$script[] = '	}';
		
		static $scriptClear;		
		if (!$scriptClear) {
			$scriptClear = true;
			
			$script[] = '	function jClearArticle(id) {';
			$script[] = '		jQuery("#" + id + "_id").val("");';
			$script[] = '		jQuery("#" + id + "_name").val("'.htmlspecialchars(Text::_('LIB_SYW_ARTICLE_SELECTARTICLE', true), ENT_COMPAT, 'UTF-8').'");';
			$script[] = '		jQuery("#" + id + "_clear").addClass("hidden");';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));
		
		// Setup display
		
		$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_'.$this->id;
		$link .= '&amp;'.Session::getFormToken().'=1';

		$title = '';
		if ((int) $this->value > 0) {
			$query = $db->getQuery(true);
			
			$query->select($db->quoteName('title'));
			$query->from($db->quoteName('#__content'));
			$query->where($db->quoteName('id').' = '.(int) $this->value);
			
			$db->setQuery($query);
		
			try {
				$title = $db->loadResult();
			} catch (\RuntimeException $e) {
				Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			}
		}
		
		if (empty($title)) {
			$title = Text::_('LIB_SYW_ARTICLE_SELECTARTICLE');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
		
		if (0 == (int) $this->value) {
			$value = '';
		} else {
			$value = (int) $this->value;
		}
		
		$html = '<div class="input-group">';
            $html .= '<input type="text" class="form-control" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" />';		
    		$html .= '<div class="input-group-append">';
    		  $html .= '<a href="#modalArticle'.$this->id.'" class="btn btn-primary hasTooltip" role="button" data-toggle="modal" title="'.Text::_('LIB_SYW_ARTICLE_SELECTARTICLE').'"><i class="icon-file"></i></a>';
    		  $html .= '<a id="'.$this->id.'_clear" href="#" class="btn btn-secondary hasTooltip'.($value ? '' : ' hidden').'" title="'.Text::_('JCLEAR').'" onclick="return jClearArticle(\''.$this->id.'\')"><i class="icon-remove"></i></a>';
    		$html .= '</div>';
		$html .= '</div>';
			
		$class = '';
		if ($this->required) {
			$class = 'required ';
		}
		
		$html .= '<input type="hidden" id="'.$this->id.'_id" name="'.$this->name.'" class="'.$class.'modal-value" value="'.$value.'" />';
		
		$modal_params = array();
		$modal_params['url'] = $link;
		$modal_params['title'] = Text::_('LIB_SYW_ARTICLE_SELECTARTICLE');
		$modal_params['width'] = '800px';
		$modal_params['height'] = '300px';
		$modal_params['footer'] = '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'.Text::_("JLIB_HTML_BEHAVIOR_CLOSE").'</button>';
		
		$html .= HTMLHelper::_('bootstrap.renderModal', 'modalArticle'.$this->id, $modal_params);

		return $html;  
	}
}