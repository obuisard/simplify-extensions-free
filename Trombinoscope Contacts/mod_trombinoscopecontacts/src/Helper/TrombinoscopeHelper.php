<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrombinoscopeContacts\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Session\Session;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Ajax Helper for TrombinoscopeContacts module, exposed through the module's
 * services/provider.php HelperFactory registration.
 *
 * com_ajax's module bridge derives this class name from the 'module' request
 * parameter: index.php?option=com_ajax&module=trombinoscope&method=methodName&format=json
 * resolves to 'Mod' . ucfirst('trombinoscope') . 'Helper' = 'ModTrombinoscopeHelper',
 * then strips the leading 'Mod' before asking the HelperFactory for it, so this
 * class must be named exactly 'TrombinoscopeHelper'.
 *
 * Each public *Ajax() method must return its result (array/object) or throw an
 * Exception on failure; com_ajax wraps that into the JSON response envelope
 * ({success, data, message}).
 */
class TrombinoscopeHelper
{
    /**
     * Search contacts by name, scoped to the module instance's own selection
     * (categories, tags, keywords, language, featured setting - same as the
     * normal list), and rendered through the same 'card' layout the normal
     * list uses, so results look identical to the configured theme.
     *
     * Called via: index.php?option=com_ajax&module=trombinoscope&method=searchContacts&format=json
     *
     * @return array
     */
    public function searchContactsAjax()
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        // Security check
        if (!Session::checkToken('request')) {
            throw new \Exception('Invalid token');
        }

        // com_ajax's module bridge only loads the module's language file for
        // its legacy helper.php path, not for a HelperFactory-resolved helper
        // like this one, so it's never loaded by the time we get here.
        $app->getLanguage()->load('mod_trombinoscope', JPATH_SITE)
            || $app->getLanguage()->load('mod_trombinoscope', JPATH_SITE . '/modules/mod_trombinoscope');

        $moduleId = $input->getInt('module_id', 0);
        $searchTerm = trim($input->getString('q', ''));
        $limit = $input->getInt('limit', 50);

        if ($searchTerm === '') {
            return array('html' => '');
        }

        $params = $this->getModuleParams($moduleId);

        if (!$params) {
            throw new \Exception('Module not found');
        }

        // Search within the module's own configured selection, just without
        // its on-page Max Count limit.
        $params->set('search_filter', $searchTerm);
        $params->set('count', $limit);
        $params->set('startat', 1);

        $module = (object) array('id' => $moduleId);

        $contacts = Helper::getContacts($params, $module);

        if (empty($contacts)) {
            return array('html' => '<div class="no-contacts">' . Text::_('MOD_TROMBINOSCOPE_NO_CONTACTS_FOUND') . '</div>');
        }

        return array('html' => $this->renderCards($contacts, $params, $module));
    }

    /**
     * Renders a list of contacts through the shared 'card' layout, the same
     * one the normal list rendering uses - no carousel, no headers.
     *
     * @param array $contacts
     * @param Registry $params
     * @param \stdClass $module
     * @return string
     */
    private function renderCards($contacts, $params, $module)
    {
        $groups = Factory::getApplication()->getIdentity()->getAuthorisedViewLevels();
        $cardSettings = Helper::getCardSettings($params);
        $cardLayout = new FileLayout('card', JPATH_SITE . '/modules/mod_trombinoscope/layouts');

        $html = '';
        $i_header = 0;

        foreach ($contacts as $contact) {
            $html .= $cardLayout->render(array_merge($cardSettings, array(
                'item' => $contact,
                'params' => $params,
                'module' => $module,
                'groups' => $groups,
                'i_header' => $i_header,
            )));

            $i_header++;
        }

        return $html;
    }

    /**
     * Get module parameters by module ID
     *
     * @param int $moduleId
     * @return Registry|false
     */
    private function getModuleParams($moduleId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('params'))
              ->from($db->quoteName('#__modules'))
              ->where($db->quoteName('id') . ' = :moduleId')
              ->where($db->quoteName('module') . ' = ' . $db->quote('mod_trombinoscope'));

        $query->bind(':moduleId', $moduleId, ParameterType::INTEGER);

        $db->setQuery($query);
        $paramsString = $db->loadResult();

        if ($paramsString) {
            return new Registry($paramsString);
        }

        return false;
    }
}
