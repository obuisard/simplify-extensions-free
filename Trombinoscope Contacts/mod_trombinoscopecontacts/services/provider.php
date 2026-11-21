<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\SYW\\Module\\TrombinoscopeContacts'));
        $container->registerServiceProvider(new HelperFactory('\\SYW\\Module\\TrombinoscopeContacts\\Site\\Helper'));

        $container->registerServiceProvider(new Module());
    }
};
