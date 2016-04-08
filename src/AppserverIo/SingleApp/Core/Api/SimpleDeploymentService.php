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

use AppserverIo\Configuration\ConfigurationException;
use AppserverIo\Appserver\Core\Api\Node\ContextNode;
use AppserverIo\Appserver\Core\Api\DeploymentService;
use AppserverIo\Appserver\Core\Api\Node\ContainersNode;
use AppserverIo\Appserver\Core\Api\Node\DeploymentNode;
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

            // our webapp path is the actual working directory
            $webappPath = getcwd();

            // iterate through all server configurations (servers.xml), validate and merge them
            foreach ($this->globDir($webappPath . '/META-INF/containers.xml') as $containersConfigurationFile) {
                try {
                    // validate the application specific container configurations
                    $configurationService->validateFile($containersConfigurationFile, null);

                    // create a new containers node instance
                    $containersNodeInstance = new ContainersNode();
                    $containersNodeInstance->initFromFile($containersConfigurationFile);

                    // load the system properties
                    $properties = $this->getSystemProperties($containerNode);

                    // replace the host's application base directory with the parent directory
                    $properties->add(SystemPropertyKeys::HOST_APP_BASE, dirname($webappPath));

                    // append the application specific properties and replace the properties
                    $properties->add(SystemPropertyKeys::WEBAPP, $webappPath);
                    $properties->add(SystemPropertyKeys::WEBAPP_NAME, basename($webappPath));

                    /** @var AppserverIo\Appserver\Core\Api\Node\ContainerNodeInterface $containerNodeInstance */
                    foreach ($containersNodeInstance->getContainers() as $containerNodeInstance) {
                        // replace the properties for the found container node instance
                        $containerNodeInstance->replaceProperties($properties);
                        // query whether we've to merge or append the server node instance
                        if ($container = $systemConfiguration->getContainer($containerNodeInstance->getName())) {
                            $container->merge($containerNodeInstance);
                        } else {
                            $systemConfiguration->attachContainer($containerNodeInstance);
                        }
                    }

                } catch (ConfigurationException $ce) {
                    // load the logger and log the XML validation errors
                    $systemLogger = $this->getInitialContext()->getSystemLogger();
                    $systemLogger->error($ce->__toString());

                    // additionally log a message that server configuration will be missing
                    $systemLogger->critical(
                        sprintf(
                            'Will skip app specific server configuration because of invalid file %s',
                            $containersConfigurationFile
                        )
                    );
                }
            }
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
        $context = $this->loadContextInstance($container, getcwd());
        $contextInstances[$context->getName()] = $context;

        // return the array with the context instances
        return $contextInstances;
    }
}
