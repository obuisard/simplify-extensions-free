<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\Content\ArticleDetails\Extension;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Contact\Site\Helper\RouteHelper as ContactRouteHelper;
use Joomla\Component\Content\Site\Helper\AssociationHelper as ContentAssociationHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;
use Joomla\Registry\Registry;
use SYW\Component\TrombinoscopeExtended\Site\Helper\RouteHelper as TrombinoscopeExtendedRouteHelper;
use SYW\Library\Fonts as SYWFonts;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Plugin\Content\ArticleDetails\Cache\CSSFileCache;
use SYW\Plugin\Content\ArticleDetails\Cache\CSSPrintFileCache;
use SYW\Plugin\Content\ArticleDetails\Helper\CalendarHelper;
use SYW\Plugin\Content\ArticleDetails\Helper\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class ArticleDetails extends CMSPlugin
{
    /**
     * Application object.
     * Needed for compatibility with Joomla 4 < 4.2
     * Ultimately, we should use $this->getApplication() in Joomla 6
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     */
    protected $app;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     */
    protected $autoloadLanguage = true;
    
    /**
     * The supported form contexts
     *
     * @var    array
     */
    protected $supportedContext = [
        'com_content.article',
        'com_content.category',
        'com_content.featured',
    ];
    
    protected $_library_loaded = true;
    
    protected $_syntax_exists = false;
    
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        
        if (!$this->app) {
            $this->app = Factory::getApplication();
        }
        
        if (!PluginHelper::isEnabled('system', 'syw')) {
            $this->app->enqueueMessage(Text::_('PLG_CONTENT_ARTICLEDETAILS_WARNING_MISSINGLIBRARY'), 'error');
            $this->_library_loaded = false;
            return;
        }
    }
    
    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        if (!$this->_library_loaded) {
            return;
        }
            
        // add missing info in case 'force showing' is enabled and some info is missing
        if (in_array($context, $this->supportedContext) && $this->params->get('force_show', 0)) {
            $this->_addMissingInfo($row, $params);
        }
        
        if (!isset($row->text)) {
            return;
        }
        
        $there_is_a_match = false;
        
        $regex_header = '/{articledetails-header}/i';
        $regex_footer = '/{articledetails-footer}/i';
        
        // find all instances of plugin and put in $matches for articledetails-header
        preg_match_all($regex_header, $row->text, $matches, PREG_SET_ORDER);
        
        if ($matches) {
            
            //if (isset($row->publish_up) && $view != 'category' && $view != 'featured') { // some components do not get the full fledge article (like tags or search)
            if ($context == 'com_content.article') {
                
                $there_is_a_match = true;
                
                // auto-hide elements
                $this->_autoHide($params, $this->params);
                
                // add missing info
                $this->_addMissingInfo($row, $params);
                
                $done_once = false;
                foreach ($matches as $match) {
                    if (!$done_once) {
                        $row->text = preg_replace($regex_header, $this->_createOutputBefore($context, $row, $params, $page, 'article'), $row->text, 1); // do only once, in place
                        $done_once = true;
                    } else {
                        $row->text = preg_replace($regex_header, '', $row->text, 1);
                    }
                }
            } else {
                // find all instances of articledetails-header and remove them
                preg_match_all($regex_header, $row->text, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $row->text = preg_replace($regex_header, '', $row->text, 1);
                }
            }
        }
        
        // find all instances of plugin and put in $matches for articledetails-footer
        preg_match_all($regex_footer, $row->text, $matches, PREG_SET_ORDER);
        
        if ($matches) {
            
            if ($context == 'com_content.article') { // footer is only applied to full articles
                
                $there_is_a_match = true;
                
                // auto-hide elements
                $this->_autoHide($params, $this->params);
                
                // add missing info
                $this->_addMissingInfo($row, $params);
                
                // find all instances of plugin and put in $matches for articledetails-footer
                preg_match_all($regex_footer, $row->text, $matches, PREG_SET_ORDER);
                
                $done_once = false;
                foreach ($matches as $match) {
                    $row->text = preg_replace($regex_footer, '', $row->text, 1); // remove all occurences
                    if (!$done_once) {
                        $row->text .= $this->_createOutputAfter($context, $row, $params, $page, 'article'); // do only once
                        $done_once = true;
                    }
                }
            } else {
                // find all instances of articledetails-footer and remove them
                preg_match_all($regex_footer, $row->text, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $row->text = preg_replace($regex_footer, '', $row->text, 1);
                }
            }
        }
        
        if ($there_is_a_match) {
            
            $this->_syntax_exists = true;
            
            $wam = $this->app->getDocument()->getWebAssetManager();
            
            // add styles
            
            if ($this->params->get('load_icon_font', true)) {
                SYWFonts::loadIconFont();
            }
            
            $additional_inline_styles = Helper::getInlineStyles($this->params);
            $additional_inline_styles .= CalendarHelper::getCalendarInlineStyles($this->params);
            
            $clear_header_files_cache = Helper::IsClearHeaderCache($this->params);
            
            $cache_css = new CSSFileCache('plg_content_articledetails', $this->params);
            $cache_css->addDeclaration($additional_inline_styles);
            $result = $cache_css->cache('style_article.css', $clear_header_files_cache);
            
            if ($result) {
                $wam->registerAndUseStyle('adp.article_style', $cache_css->getCachePath() . '/style_article.css');
            }
            
            $cache_css_print = new CSSPrintFileCache('plg_content_articledetails', $this->params);
            $result = $cache_css_print->cache('print_article.css', $clear_header_files_cache);
            
            if ($result) {
                $wam->registerAndUseStyle('adp.article_print_style', $cache_css_print->getCachePath() . '/print_article.css', [], ['media' => 'print']);
            }
        }
    }
    
    public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
    {
        if (!$this->_library_loaded) {
            return '';
        }
        
        $html = '';

        if (!in_array($context, $this->supportedContext)) {
            return $html;
        }
        
        if ($this->_syntax_exists) {
            return $html;
        }
        
        $view = $this->app->getInput()->getCmd('view', '');
        
        if ($view != 'article') {
            if ($this->params->get('disable_in_list_views', false)) {
                return $html;
            }
        }
        
        if ($view == 'article' || $view == 'category' || $view == 'featured') {
            
            if ($this->_foundCategory($row->catid)) {
                
                $wam = $this->app->getDocument()->getWebAssetManager();
                
                // heads
                
                if ($view == 'article') {
                    $head_type = $this->params->get('head_type', 'none');
                } else {
                    $head_type = $this->params->get('lists_head_type', 'none');
                }
                
                // auto-hide elements
                $this->_autoHide($params, $this->params);
                
                $show_calendar = false;
                if ($head_type == 'calendar') {
                    $show_calendar = true;
                }
                
                // add styles
                
                if ($this->params->get('load_icon_font', true)) {
                    SYWFonts::loadIconFont();
                }
                
                $this->params->set('view', $view);
                
                $additional_inline_styles = Helper::getInlineStyles($this->params);
                if ($show_calendar) {
                    $additional_inline_styles .= CalendarHelper::getCalendarInlineStyles($this->params);
                }
                
                $clear_header_files_cache = Helper::IsClearHeaderCache($this->params);
                
                $cache_css = new CSSFileCache('plg_content_articledetails', $this->params);
                $cache_css->addDeclaration($additional_inline_styles);
                $result = $cache_css->cache('style_'.$view.'.css', $clear_header_files_cache);
                
                if ($result) {
                    $wam->registerAndUseStyle('adp.' . $view . '_style', $cache_css->getCachePath() . '/style_' . $view . '.css');
                }
                
                $cache_css_print = new CSSPrintFileCache('plg_content_articledetails', $this->params);
                $result = $cache_css_print->cache('print_'.$view.'.css', $clear_header_files_cache);
                
                if ($result) {
                    $wam->registerAndUseStyle('adp.' . $view . '_print_style', $cache_css_print->getCachePath() . '/print_' . $view . '.css', [], ['media' => 'print']);
                }
                
                return $this->_createOutputBefore($context, $row, $params, $page, $view);
            }
        }
        
        return $html;
    }
    
    public function onContentAfterDisplay($context, &$row, &$params, $page = 0)
    {
        if (!$this->_library_loaded) {
            return '';
        }
        
        // for content after the article (like author)
        $html = '';
        
        $canProceed = ($context == 'com_content.article');
        if (!$canProceed) {
            return $html;
        }
        
        $view = $this->app->getInput()->getCmd('view', '');
        
        if ($view == 'article') {
            
            if ($this->_syntax_exists) {
                return $html;
            }
            
            if ($this->_foundCategory($row->catid)) {
                $row->text .= $this->_createOutputAfter($context, $row, $params, $page, $view);
                // $html is not used in order for the footer of the article to be before the navigation or any plugin that would call onContentAfterDisplay
            }
        }
        
        return $html;
    }
    
    protected function _createOutputBefore($context, &$row, &$params, &$page = 0, $view = 'article')
    {
        $output = '';
        $head_output = '';
        
        $db = Factory::getDbo();
        
        $bootstrap_version = $this->params->get('bootstrap_version', 'joomla');
        $load_bootstrap = false;
        if ($bootstrap_version === 'joomla') {
            $bootstrap_version = 5;
            $load_bootstrap = true;
        } else {
            $bootstrap_version = intval($bootstrap_version);
        }
        
        // set article link
        
        $row->link = '';
        if ($view == 'article' && !empty($row->readmore_link)) {
            $row->link = $row->readmore_link;
        } else if ($params->get('access-view')) {
            if (isset($row->language)) {
                $row->link = Route::_(ContentRouteHelper::getArticleRoute($row->slug, $row->catid, $row->language));
            } else {
                $row->link = Route::_(ContentRouteHelper::getArticleRoute($row->slug, $row->catid));
            }
        }
        
        // title
        
        $title_html_tag = $this->params->get('t_tag', '2');
        
        // create head block
        
        if ($view == 'article') {
            $head_type = $this->params->get('head_type', 'none');
        } else {
            $head_type = $this->params->get('lists_head_type', 'none');
        }
        
        if ($head_type != 'none') {
            
            $show_calendar = false;
            if ($head_type == "calendar") {
                $show_calendar = true;
            }
            
            if ($show_calendar) {
                
                $calendar_date = $row->publish_up;
                switch ($this->params->get('post_d', 'published')) {
                    case 'created': $calendar_date = $row->created; break;
                    case 'modified': $calendar_date = $row->modified; break;
                    case 'finished': $calendar_date = $row->publish_down; break;
                }
                
                if ($calendar_date != $db->getNullDate() && !empty($calendar_date)) {
                    
                    $date_params = CalendarHelper::getCalendarBlockData($this->params, $calendar_date);
                    
                    $head_output .= '<div class="head">';
                    $head_output .= '<div class="calendar noimage">';
                    foreach ($date_params as $counter => $date_array) {
                        if (!empty($date_array)) {
                            $head_output .= '<span class="position'.($counter + 1).' '.key($date_array).'">'.$date_array[key($date_array)].'</span>';
                        }
                    }
                    $head_output .= '</div>';
                    $head_output .= '</div>';
                }
            }
        }
        
        // create output
        
        $additional_class = SYWUtilities::isMobile() ? ' mobile' : '';
        $additional_class .= ' id-' . $row->id;
        if (isset($row->catid)) {
            $additional_class .= ' catid-' . $row->catid;
        }
        
        $output .= '<div class="articledetails articledetails-header' . $additional_class . '">';
        
        $output .= $head_output;
        
        // create info
        
        $output .= '<div class="info">';
        
        // publication status
        
        if ($this->params->get('show_pub_status', 1)) {
            
            $publishing_status_output = '';
            if ($row->state == 0) {
                $publishing_status_output .= '<span class="article_unpublished label label-warning">'.Text::_('JUNPUBLISHED').'</span>';
            }
            if (strtotime($row->publish_up) > strtotime(Factory::getDate())) {
                $publishing_status_output .= '<span class="article_notpublishedyet label label-warning">'.Text::_('JNOTPUBLISHEDYET').'</span>';
            }
            if (!is_null($row->publish_down) && strtotime($row->publish_down) < strtotime(Factory::getDate())) {
                $publishing_status_output .= '<span class="article_expired label label-warning">'.Text::_('JEXPIRED').'</span>';
            }
            
            if ($publishing_status_output) {
                $output .= '<div class="publishing_status">'.$publishing_status_output.'</div>';
            }
        }
        
        // details
        
        $info_block = Helper::getInfoBlock($this->params, $row, $params, $view, 'before_title');
        
        if (!empty($info_block)) {
            $output .= '<dl class="item_details before_title">'.$info_block.'</dl>';
        }
        
        // title
        
        $edit_addition = '';
        if ($params->get('access-edit') && !$this->app->getInput()->getBool('print', false) /*&& !$params->get('popup')*/) {
            
            if ($load_bootstrap) {
                HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
            }
            
            if ($row->checked_out > 0 && $row->checked_out != Factory::getUser()->get('id')) {
                $checkoutUser = Factory::getUser($row->checked_out);
                $edit_addition = '<span class="article_checked_out hasTooltip" title="'.Text::sprintf('COM_CONTENT_CHECKED_OUT_BY', $checkoutUser->name).'"><i class="SYWicon-lock"></i></span>';
            } else {
                $edit_url = 'index.php?option=com_content&task=article.edit&a_id=' . $row->id . '&return=' . base64_encode(Uri::getInstance());
                //$edit_addition = '&nbsp;<span class="article_edit"><i class="SYWicon-create"></i>&nbsp;<a href="'.$edit_url.'">'.Text::_('JGLOBAL_EDIT').'</a></span>';
                $edit_addition = '<a href="'.$edit_url.'" class="article_edit hasTooltip" title="'.Text::_('JGLOBAL_EDIT').'"><i class="SYWicon-create"></i></a>';
            }
        }
        
        if ($params->get('ad_show_title') && !empty($row->title)) {
            if ( $view == 'category' || $view == 'featured') {
                if ($params->get('link_titles') && $params->get('access-view') && !$this->app->getInput()->getBool('print')) {
                    $output .= '<h'.$title_html_tag.' class="article_title"><a href="'.$row->link.'">'.$row->title.'</a>'.$edit_addition.'</h'.$title_html_tag.'>';
                } else {
                    $output .= '<h'.$title_html_tag.' class="article_title">'.$row->title.$edit_addition.'</h'.$title_html_tag.'>';
                }
            } else {
                $output .= '<h'.$title_html_tag.' class="article_title">'.$row->title.$edit_addition.'</h'.$title_html_tag.'>';
            }
        } else {
            $output .= '<h'.$title_html_tag.' class="article_title">'.$edit_addition.'</h'.$title_html_tag.'>';
        }
        
        // details
        
        $info_block = Helper::getInfoBlock($this->params, $row, $params, $view, 'after_title');
        
        if (!empty($info_block)) {
            $output .= '<dl class="item_details after_title">'.$info_block.'</dl>';
        }
        
        $output .= '</div>'; // end info
        
        $output .= '</div>'; // end articledetails
        
        return $output;
    }
    
    protected function _createOutputAfter($context, &$row, &$params, &$page = 0, $view = 'article')
    {
        $output = '';
        
        // set article link (needed here before getting $something_to_show)
        
        $row->link = '';
        if (!empty($row->readmore_link)) {
            $row->link = $row->readmore_link;
        } else if ($params->get('access-view')) {
            if (isset($row->language)) {
                $row->link = Route::_(ContentRouteHelper::getArticleRoute($row->slug, $row->catid, $row->language));
            } else {
                $row->link = Route::_(ContentRouteHelper::getArticleRoute($row->slug, $row->catid));
            }
        }
        
        $info_block_footer = Helper::getInfoBlock($this->params, $row, $params, $view, 'footer');
        
        if ($info_block_footer) {
            
            // create output
            
            $additional_class = SYWUtilities::isMobile() ? ' mobile' : '';
            $additional_class .= ' id-' . $row->id;
            if (isset($row->catid)) {
                $additional_class .= ' catid-' . $row->catid;
            }
            
            $output .= '<div class="articledetails articledetails-footer' . $additional_class . '">';
            if ($info_block_footer) {
                $output .= '<div class="info">';
                $output .= '<dl class="item_details">'.$info_block_footer.'</dl>';
                $output .= '</div>';
            }
            $output .= '</div>';
        }
        
        return $output;
    }
    
    protected function _foundCategory($category_id)
    {
        static $found = array();
        
        if (isset($found[$category_id])) {
            return $found[$category_id];
        }
        
        $found[$category_id] = false;
        
        $categories_array = $this->params->get('catid', array());
        
        $array_of_category_values = array_count_values($categories_array);
        if (isset($array_of_category_values['none']) && $array_of_category_values['none'] > 0) { // 'none' was selected
            return false;
        }
        if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
            $found[$category_id] = true;
        } else {
            // sub-category inclusion
            $get_sub_categories = $this->params->get('includesubcategories', 'no');
            if ($get_sub_categories != 'no') {
                $categories_object = Categories::getInstance('Content');
                foreach ($categories_array as $category) {
                    $category_object = $categories_object->get($category); // if category unpublished, unset
                    if (isset($category_object) && $category_object->hasChildren()) {
                        if ($get_sub_categories == 'all') {
                            $sub_categories_array = $category_object->getChildren(true); // true is for recursive
                        } else {
                            $sub_categories_array = $category_object->getChildren();
                        }
                        foreach ($sub_categories_array as $subcategory_object) {
                            $categories_array[] = $subcategory_object->id;
                        }
                    }
                }
                $categories_array = array_unique($categories_array);
            }
            
            foreach ($categories_array as $category) {
                if ($category_id == intval($category)) {
                    $found[$category_id] = true;
                }
            }
        }
        
        return $found[$category_id];
    }
    
    protected function _autoHide(&$params, $extension_params)
    {
        // title
        $params->set('ad_show_title', $params->get('show_title'));
        if ($extension_params->get('autohide_title', 0)) {
            $params->set('show_title', 0);
        }
        
        // info will show if (warning: tags are included if those are set also)
        // 			$params->get('show_modify_date')
        // 			|| $params->get('show_publish_date')
        // 			|| $params->get('show_create_date')
        // 			|| $params->get('show_hits')
        // 			|| $params->get('show_category')
        // 			|| $params->get('show_parent_category')
        // 			|| $params->get('show_author')
        // 			|| (JLanguageAssociations::isEnabled() && $params->get('show_associations'))
        
        // tags
        $params->set('ad_show_tags', $params->get('show_tags'));
        if ($extension_params->get('autohide_tags', 0)) {
            $params->set('show_tags', 0);
        }
        
        // vote
        $params->set('ad_show_vote', $params->get('show_vote'));
        if ($extension_params->get('autohide_vote', 0)) {
            $params->set('show_vote', 0);
        }
    }
    
    protected function _addMissingInfo(&$row, &$params)
    {
        // missing contact_link
        if (!isset($row->contact_link) && $params->get('link_author')) {
            
            $row->contactid = '';
            $row->contact_link = '';
            
            $contact = Helper::getContact($row->created_by);
            
            if (!empty($contact)) {
                $row->contactid = $contact->contactid;
                if (is_dir(JPATH_ADMINISTRATOR . '/components/com_trombinoscopeextended') && ComponentHelper::isEnabled('com_trombinoscopeextended' && PluginHelper::isEnabled('content', 'tcpcontact'))) {
                    
                    $plugin = PluginHelper::getPlugin('content', 'tcpcontact');
                    $params_plugin = new Registry($plugin->params);
                    
                    $url_addition = '';
                    $default_view = $params_plugin->get('default_view', 0);
                    
                    if ($default_view > 0) {
                        
                        if (Multilanguage::isEnabled()) {
                            $currentLanguage = Factory::getLanguage()->getTag();
                            $langAssociations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $default_view, 'id', '', '');
                            foreach ($langAssociations as $langAssociation) {
                                if ($langAssociation->language == $currentLanguage) {
                                    $default_view = $langAssociation->id;
                                    break;
                                }
                            }
                        }
                        
                        $url_addition = '&Itemid=' . $default_view;
                    }
                    
                    $row->contact_link = Route::_(TrombinoscopeExtendedRouteHelper::getContactRoute('trombinoscopeextended', $contact->contactid . ':' . $contact->alias, $contact->catid) . $url_addition);
                } else if (PluginHelper::isEnabled('content', 'contact')) {
                    
                    $plugin = PluginHelper::getPlugin('content', 'contact');
                    $params_plugin = new Registry($plugin->params);
                    
                    if ($contact->contactid && $params_plugin->get('url', 'url') === 'url') {
                        $row->contact_link = Route::_(ContactRouteHelper::getContactRoute($contact->contactid . ':' . $contact->alias, $contact->catid));
                    } else if ($contact->webpage && $params_plugin->get('url', 'url') === 'webpage') {
                        $row->contact_link = $contact->webpage;
                    } else if ($contact->email && $params_plugin->get('url', 'url') === 'email') {
                        $row->contact_link = 'mailto:' . $contact->email;
                    }
                }
            }
        }
        
        if (!isset($row->slug)) {
            $row->slug  = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;
            $row->catslug = $row->category_alias ? ($row->catid . ':' . $row->category_alias) : $row->catid;
            $row->parent_slug = $row->parent_alias ? ($row->parent_id . ':' . $row->parent_alias) : $row->parent_id;
            
            // No link for ROOT category
            if ($row->parent_alias == 'root') {
                $row->parent_slug = null;
            }
        }
        
        if (!isset($row->tags)) {
            $row->tags = new TagsHelper();
            $row->tags->getItemTags('com_content.article', $row->id);
        }
        
        if (!isset($row->associations) && $params->get('show_associations')) {
            $row->associations = ContentAssociationHelper::displayAssociations($row->id);
        }
    }
    
}
