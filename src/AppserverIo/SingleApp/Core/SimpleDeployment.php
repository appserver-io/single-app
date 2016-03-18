<?php

/**
 * AppserverIo\SingleApp\Core\SimpleDeployment
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

namespace AppserverIo\SingleApp\Core;

use AppserverIo\Appserver\Core\GenericDeployment;

/**
 * Deployment implementation for container's with a single app configuration.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/single-app
 * @link      http://www.appserver.io
 */
class SimpleDeployment extends GenericDeployment
{

    /**
     * Returns the deployment service instance.
     *
     * @return \AppserverIo\Appserver\Core\Api\DeploymentService The deployment service instance
     */
    public function getDeploymentService()
    {
        if ($this->deploymentService == null) {
            $this->deploymentService = $this->newService('AppserverIo\SingleApp\Core\Api\SimpleDeploymentService');
        }
        return $this->deploymentService;
    }

    /**
     * Return's the container's directory with applications to be deployed.
     *
     * @return string The container's application base directory
     */
    protected function getAppBase()
    {
        return getcwd();
    }

    /**
     * Load's and return's the context instances for the container.
     *
     * @return array The array with the container's context instances
     */
    protected function loadContextInstances()
    {
        return $this->getDeploymentService()->loadContextInstancesByWorkingDir($this->getContainer());
    }
}
