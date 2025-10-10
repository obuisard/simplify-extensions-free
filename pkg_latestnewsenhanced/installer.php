<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/**
 * Script file for the packaged Latest News Enhanced module
 */
class Pkg_LatestNewsEnhancedInstallerScript extends InstallerScript
{

    /*
     * Minimum extensions library version required
     */
    protected $minimumLibrary = '2.7.1';

    /**
     * Available languages
     */
    protected $availableLanguages = array('da-DK', 'de-DE', 'en-GB', 'es-ES', 'fi-FI', 'fr-FR', 'hu-HU', 'it-IT', 'ja-JP', 'nl-NL', 'pl-PL', 'pt-BR', 'pt-PT', 'ru-RU', 'sl-SI', 'sv-SE', 'tr-TR');

    /**
     * Extensions library link for download
     */
    protected $libraryDownloadLink = 'https://simplifyyourweb.com/downloads/syw-extension-library';

    /**
     * Link to the change logs
     */
    protected $changelogLink = 'https://simplifyyourweb.com/documentation/latest-news/installation/updating-older-versions';

    /**
     * Link to the translation page
     */
    protected $translationLink = 'https://simplifyyourweb.com/translators';

    /**
     * Link to the quick start page
     */
    protected $quickstartLink = 'https://simplifyyourweb.com/documentation/latest-news/quickstart-guide';

    /**
     * Extension script constructor
     */
    public function __construct($installer)
    {
        $this->extension = 'pkg_latestnewsenhanced';
        $this->minimumJoomla = '4.0.0';
        // $this->minimumPhp = JOOMLA_MINIMUM_PHP; // not needed
    }

    /**
     * Called before any type of action
     *
     * @param string $action Which action is happening (install|uninstall|discover_install|update)
     * @param InstallerAdapter $installer The class calling this method
     *
     * @return boolean True on success
     */
    public function preflight($action, $installer)
    {
        if ($action === 'uninstall') {
            return true;
        }

        // checks minimum PHP and Joomla versions and that an upgrade is performed
        if (!parent::preflight($action, $installer)) {
            return false;
        }

        // make sure the library is installed and that it is compatible with the extension
        return $this->installOrUpdateLibrary($installer);
    }

    /**
     * method to install the component
     *
     * @return boolean True on success
     */
    public function install($installer)
    {
    }

    /**
     * method to uninstall the component
     *
     * @return void
     */
    public function uninstall($installer)
    {
    }

    /**
     * method to update the component
     *
     * @return boolean True on success
     */
    public function update($installer)
    {
    }

    /**
     * Called after any type of action
     *
     * @param string $action Which action is happening (install|uninstall|discover_install|update)
     * @param InstallerAdapter $installer The object responsible for running this script
     *
     * @return boolean True on success
     */
    public function postflight($action, $installer)
    {
        if ($action === 'uninstall') {
            return true;
        }

        echo '<p style="margin: 10px 0 20px 0">';
        echo HTMLHelper::image('mod_latestnewsenhanced/logo.png', 'Latest News Enhanced', null, true);
        echo '<br /><br /><span class="badge bg-dark">' . Text::sprintf('PKG_LATESTNEWSENHANCED_VERSION', $this->release) . '</span>';
        echo '<br /><br />Olivier Buisard @ <a href="https://simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
        echo '</p>';

        // language test

        $current_language = Factory::getLanguage()->getTag();
        if (!in_array($current_language, $this->availableLanguages)) {
            Factory::getApplication()->enqueueMessage('The ' . Factory::getLanguage()->getName() . ' language is missing for this component.<br /><a href="' . $this->translationLink .
                '" target="_blank">Please consider contributing to its translation</a> and get a license upgrade for your help!', 'info');
        }

        if ($action === 'install') {

            // link to Quickstart

            echo '<p><a class="btn btn-dark text-light" href="' . $this->quickstartLink . '" target="_blank"><i class="fa fa-stopwatch"></i> ' . Text::_('PKG_LATESTNEWSENHANCED_BUTTON_QUICKSTART') . '</a></p>';
        }

        if ($action === 'update') {

            // update warning

            echo '<p><a class="btn btn-dark text-light" href="' . $this->changelogLink . '" target="_blank">' . Text::_('PKG_LATESTNEWSENHANCED_BUTTON_UPDATENOTES') . '</a></p>';

            // remove old cached headers which may interfere with fixes, updates or new additions

            if (function_exists('glob')) {

                $filenames = glob(JPATH_SITE . '/media/cache/mod_latestnewsenhanced/style_*.{css,js}', GLOB_BRACE);
                if ($filenames != false) {
                    $this->deleteFiles = array_merge($this->deleteFiles, $filenames);
                }

                $filenames = glob(JPATH_SITE . '/media/cache/mod_latestnewsenhanced/animation_*.js');
                if ($filenames != false) {
                    $this->deleteFiles = array_merge($this->deleteFiles, $filenames);
                }
            }

            // +++ Migration Joomla 3 to Joomla 4

            // the old folders have not been removed on update so safe to do it here
            if (is_dir(JPATH_SITE . '/modules/mod_latestnewsenhanced/images')) {

                // move user files (substitutes)

                $this->moveFile('common_user_styles.css', '/modules/mod_latestnewsenhanced/styles', '/media/mod_latestnewsenhanced/css', '-min');
                $this->moveFile('substitute_styles.css', '/modules/mod_latestnewsenhanced/styles', '/media/mod_latestnewsenhanced/css', '-min');

                // remove data from /cache if coming from Joomla 3.10

                $this->deleteFolders[] = '/cache/mod_latestnewsenhanced';

                // delete files and folders

                $this->deleteFiles[] = '/modules/mod_latestnewsenhanced/headerfilesmaster.php';

                $this->deleteFolders[] = '/modules/mod_latestnewsenhanced/animations';
                $this->deleteFolders[] = '/modules/mod_latestnewsenhanced/fields';
                $this->deleteFolders[] = '/modules/mod_latestnewsenhanced/helpers';
                $this->deleteFolders[] = '/modules/mod_latestnewsenhanced/images';
                $this->deleteFolders[] = '/modules/mod_latestnewsenhanced/js';
                $this->deleteFolders[] = '/modules/mod_latestnewsenhanced/styles';
            }

            // +++ End Migration

            // remove files

            $this->deleteFiles[] = '/media/mod_latestnewsenhanced/css/common_styles-min.css';
            
            // Remove K2 files
            
            $this->deleteFiles[] = '/modules/mod_latestnewsenhanced/src/Helper/K2Helper.php';
            $this->deleteFiles[] = '/modules/mod_latestnewsenhanced/tmpl/accordionk2.php';
            $this->deleteFiles[] = '/modules/mod_latestnewsenhanced/tmpl/k2.php';

            // fix manual configuration errors made before v6.8.0
            // v6.8.0 does not return any more results when authors are excluded and authors are set to 'all'

            $this->fixConfigErrors();
        }

        $this->removeFiles();

        return true;
    }

    private function fixConfigErrors()
    {
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

        $query->select('id');
        $query->select('params');
        $query->from('#__modules');
        $query->where($db->quoteName('module') . '=' . $db->quote('mod_latestnewsenhanced'));

        $db->setQuery($query);

        $lne_instances = array();
        try {
            $lne_instances = $db->loadObjectList();
        } catch (ExecutionFailureException $e) {
            return false;
        }

        foreach ($lne_instances as $lne_instance) {

            $instance_params = json_decode($lne_instance->params, true);

            $changes_made = false;

            if (!isset($instance_params['author_match'])) { // before 6.8.0, 'author_match' does not exist
                if (isset($instance_params['author_inex']) && (int) $instance_params['author_inex'] === 0) { // 'excluded' is selected
                    if (isset($instance_params['datasource'])) {

                        $created_by = '';
                        if ($instance_params['datasource'] === 'articles') {
                            $created_by = 'created_by';
                        }

                        if ($created_by && isset($instance_params[$created_by])) {
                            $authors_array = (array) $instance_params[$created_by];
                            $array_of_authors_values = array_count_values($authors_array);
                            if (isset($array_of_authors_values['all']) && $array_of_authors_values['all'] > 0) { // 'all' was selected
                                $changes_made = true;
                                $instance_params['author_inex'] = '1';
                            }
                        }
                    }
                }
            }
            
            // New in/ex UX
            if (!isset($instance_params['ex_articles'])) {
                $instance_params['ex_articles'] = [];
                if (isset($instance_params['ex']) && $instance_params['ex'] !== '') {
                    $articles_to_exclude = array_filter(explode(',', trim($instance_params['ex'], ' ,')));
                    if (!empty($articles_to_exclude)) {
                        $values = [];
                        foreach($articles_to_exclude as $key => $article_id) {
                            $values['ex_articles' . $key] = ['id' => (string) $article_id];
                        }
                        $instance_params['ex_articles'] = json_encode($values);
                    }
                }
                
                $changes_made = true;
            }
            
            if (!isset($instance_params['in_articles'])) {
                $instance_params['in_articles'] = [];
                if (isset($instance_params['in']) && $instance_params['in'] !== '') {
                    $articles_to_include = array_filter(explode(',', trim($instance_params['in'], ' ,')));
                    if (!empty($articles_to_include)) {
                        $values = [];
                        foreach($articles_to_include as $key => $article_id) {
                            $values['in_articles' . $key] = ['id' => (string) $article_id];
                        }
                        $instance_params['in_articles'] = json_encode($values);
                    }
                }
                
                $changes_made = true;                
            }

            if ($changes_made) {

                $query->clear();

                $query->update('#__modules');
                $query->set($db->quoteName('params') . '=' . $db->quote(json_encode($instance_params)));
                $query->where($db->quoteName('id') . '=' . $db->quote($lne_instance->id));

                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (ExecutionFailureException $e) {
                    return false;
                }
            }
        }

        return true;
    }

    private function isFolderReady($extra_path)
    {
        $path = JPATH_SITE;
        $folders = explode('/', trim($extra_path, '/'));

        foreach ($folders as $folder) {
            $path .= '/' . $folder;
            if (!is_dir($path)) {
                if (Folder::create($path)) {
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    private function moveFile($file, $source, $destination, $minified_version = '')
    {
        if (is_file(JPATH_SITE . $source . '/' . $file)) {
            if (!$this->isFolderReady($destination) || !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
                Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_LATESTNEWSENHANCED_ERROR_CANNOTMOVEFILE', $file), 'warning');
            }
        }

        if ($minified_version) {
            $file_name = File::stripExt($file);
            
            if (class_exists('\Joomla\Filesystem\File') && method_exists('\Joomla\Filesystem\File', 'getExt')) {
                // Joomla 5 and 6
                $file_extension = \Joomla\Filesystem\File::getExt($file);
            } else {
                // Joomla 4 fallback
                $file_extension = \Joomla\CMS\Filesystem\File::getExt($file);
            }
            
            $file = $file_name . $minified_version . '.' . $file_extension;

            if (is_file(JPATH_SITE . $source . '/' . $file)) {
                if (!$this->isFolderReady($destination) || !File::move(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
                    Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_LATESTNEWSENHANCED_ERROR_CANNOTMOVEFILE', $file), 'warning');
                }
            }
        }
    }

    private function copyFile($file, $source, $destination)
    {
        if (is_file(JPATH_SITE . $source . '/' . $file)) {
            if (!$this->isFolderReady($destination) || !File::copy(JPATH_SITE . $source . '/' . $file, JPATH_SITE . $destination . '/' . $file)) {
                Factory::getApplication()->enqueueMessage(Text::sprintf('PKG_LATESTNEWSENHANCED_WARNING_COULDNOTCOPYFILE', $file), 'warning');
            }
        }
    }

    private function enableExtension($type, $element, $folder = '', $enable = true)
    {
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__extensions'));
        if ($enable) {
            $query->set($db->quoteName('enabled') . ' = 1');
        } else {
            $query->set($db->quoteName('enabled') . ' = 0');
        }
        $query->where($db->quoteName('type') . ' = ' . $db->quote($type));
        $query->where($db->quoteName('element') . ' = ' . $db->quote($element));
        if ($folder) {
            $query->where($db->quoteName('folder') . ' = ' . $db->quote($folder));
        }

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (ExecutionFailureException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }

        return true;
    }

    private function getDefaultTemplate()
    {
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

        $query->select('template');
        $query->from('#__template_styles');
        $query->where($db->quoteName('client_id') . '= 0');
        $query->where($db->quoteName('home') . '= 1');

        $db->setQuery($query);

        $defaultemplate = '';

        try {
            $defaultemplate = $db->loadResult();
        } catch (ExecutionFailureException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
        }

        return $defaultemplate;
    }

    private function removeUpdateSite($type, $element, $folder = '', $location = '')
    {
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

        $query->select('extension_id');
        $query->from('#__extensions');
        $query->where($db->quoteName('type') . '=' . $db->quote($type));
        $query->where($db->quoteName('element') . '=' . $db->quote($element));
        if ($folder) {
            $query->where($db->quoteName('folder') . '=' . $db->quote($folder));
        }

        $db->setQuery($query);

        $extension_id = '';
        try {
            $extension_id = $db->loadResult();
        } catch (ExecutionFailureException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }

        if ($extension_id) {

            $query->clear();

            $query->select('update_site_id');
            $query->from('#__update_sites_extensions');
            $query->where($db->quoteName('extension_id') . '=' . $db->quote($extension_id));

            $db->setQuery($query);

            $updatesite_id = array(); // can have several results
            try {
                $updatesite_id = $db->loadColumn();
            } catch (ExecutionFailureException $e) {
                Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
                return false;
            }

            if (empty($updatesite_id)) {
                return false;
            } else if (count($updatesite_id) == 1) {

                $query->clear();

                $query->delete($db->quoteName('#__update_sites'));
                $query->where($db->quoteName('update_site_id') . ' = ' . $db->quote($updatesite_id[0]));

                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (ExecutionFailureException $e) {
                    Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
                    return false;
                }
            } else { // several update sites exist for the same extension therefore we need to specify which to delete

                if ($location) {
                    $query->clear();

                    $query->delete($db->quoteName('#__update_sites'));
                    $query->where($db->quoteName('update_site_id') . ' IN (' . implode(',', $updatesite_id) . ')');
                    $query->where($db->quoteName('location') . ' = ' . $db->quote($location));

                    $db->setQuery($query);

                    try {
                        $db->execute();
                    } catch (ExecutionFailureException $e) {
                        Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;
    }

    private function installOrUpdatePackage($installer, $package_name, $installation_type = 'install')
    {
        // Get the path to the package
        $sourcePath = $installer->getParent()->getPath('source');
        $sourcePackage = $sourcePath . '/packages/' . $package_name . '.zip';

        // Extract and install the package

        $package = InstallerHelper::unpack($sourcePackage);
        if ($package === false || (is_array($package) && $package['type'] === false)) {
            return false;
        }

        $tmpInstaller = new Installer();
        
        // Joomla 6+ requires the database to be set explicitly
        if (method_exists($tmpInstaller, 'setDatabase')) {
            $tmpInstaller->setDatabase(Factory::getDbo());
        }

        if ($installation_type === 'install') {
            return $tmpInstaller->install($package['dir']);
        } else {
            return $tmpInstaller->update($package['dir']);
        }
    }

    /**
     * Install the library and its plugin if missing or outdated
     */
    private function installOrUpdateLibrary($installer)
    {
        if (!is_dir(JPATH_ROOT . '/libraries/syw') || !is_dir(JPATH_ROOT . '/plugins/system/syw')) {

            if (!$this->installOrUpdatePackage($installer, 'pkg_sywlibrary')) {
                Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_INSTALLFAILED') . '<br /><a href="' . $this->libraryDownloadLink . '" target="_blank">' . Text::_('SYWLIBRARY_DOWNLOAD') .
                    '</a>', 'error');
                return false;
            }

            Factory::getApplication()->enqueueMessage(Text::sprintf('SYWLIBRARY_INSTALLED', $this->minimumLibrary), 'message');
        } else {

            if (!SYW\Library\Version::isCompatible($this->minimumLibrary)) {

                // $library_version = strval(simplexml_load_file(JPATH_ADMINISTRATOR . '/manifests/libraries/syw.xml')->version);
                // if (!version_compare($library_version, $this->minimumLibrary, 'ge')) {

                if (!$this->installOrUpdatePackage($installer, 'pkg_sywlibrary', 'update')) {
                    Factory::getApplication()->enqueueMessage(Text::_('SYWLIBRARY_UPDATEFAILED') . '<br />' . Text::_('SYWLIBRARY_UPDATE'), 'error');
                    return false;
                }

                Factory::getApplication()->enqueueMessage(Text::sprintf('SYWLIBRARY_UPDATED', $this->minimumLibrary), 'message');
            }
        }

        return true;
    }
}
?>