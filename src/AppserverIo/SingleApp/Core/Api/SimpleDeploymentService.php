<?php

/**
 * AppserverIo\SingleApp\Core\Api\SimpleDeploymentService
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/single-app
 * @link      http://www.appserver.io
 */

namespace AppserverIo\SingleApp\Core\Api;

use AppserverIo\Properties\PropertiesInterface;
use AppserverIo\Appserver\Core\Api\DeploymentService;
use AppserverIo\Appserver\Core\Utilities\SystemPropertyKeys;
use AppserverIo\Appserver\Core\Interfaces\ContainerInterface;

/**
 * A service providing functionality for a single app deployment.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/single-app
 * @link      http://www.appserver.io
 */
class SimpleDeploymentService extends DeploymentService
{

    /**
     * Prepare's the system properties for the actual mode, which is the runner mode in our case.
     *
     * @param \AppserverIo\Properties\PropertiesInterface $properties The properties to prepare
     * @param string                                      $webappPath The path of the web application to prepare the properties with
     *
     * @return void
     */
    protected function prepareSystemProperties(PropertiesInterface $properties, $webappPath)
    {

        // let the parent method also prepare the properties
        parent::prepareSystemProperties($properties, $webappPath);

        // replace the host's application base directory with the parent directory
        $properties->add(SystemPropertyKeys::HOST_APP_BASE, dirname($webappPath));
    }

    /**
     * Loads the containers, defined by the applications, merges them into
     * the system configuration and returns the merged system configuration.
     *
     * @return \AppserverIo\Appserver\Core\Interfaces\SystemConfigurationInterface The merged system configuration
     */
    public function loadContainerInstances()
    {

        // load the system configuration
        /** @var AppserverIo\Appserver\Core\Interfaces\SystemConfigurationInterface $systemConfiguration */
        $systemConfiguration = $this->getSystemConfiguration();

        // if applications are NOT allowed to override the system configuration
        if ($systemConfiguration->getAllowApplicationConfiguration() === false) {
            return $systemConfiguration;
        }

        // load the service to validate the files
        /** @var AppserverIo\Appserver\Core\Api\ConfigurationService $configurationService */
        $configurationService = $this->newService('AppserverIo\Appserver\Core\Api\ConfigurationService');

        /** @var AppserverIo\Appserver\Core\Api\Node\ContainerNodeInterface $containerNodeInstance */
        foreach ($systemConfiguration->getContainers() as $containerNode) {
            $this->loadContainerInstance($containerNode, $systemConfiguration, getcwd());
        }

        // returns the merged system configuration
        return $systemConfiguration;
    }

    /**
     * Initializes the available application contexts and returns them.
     *
     * @param \AppserverIo\Appserver\Core\Interfaces\ContainerInterface $container The container we want to add the applications to
     *
     * @return array The array with the application contexts
     */
    public function loadContextInstancesByWorkingDir(ContainerInterface $container)
    {

        // initialize the array for the context instances
        $contextInstances = array();

        // attach the context to the context instances
        $context = $this->loadContextInstance($container->getContainerNode(), getcwd());
        $contextInstances[$context->getName()] = $context;

        // return the array with the context instances
        return $contextInstances;
    }
}
