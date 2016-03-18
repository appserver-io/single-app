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

use AppserverIo\Appserver\Core\Api\DeploymentService;
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
