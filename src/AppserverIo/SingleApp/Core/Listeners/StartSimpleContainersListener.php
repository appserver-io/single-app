<?php

/**
 * AppserverIo\SingleApp\Core\Listeners\StartSimpleContainersListener
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

namespace AppserverIo\SingleApp\Core\Listeners;

use AppserverIo\Appserver\Core\Listeners\StartContainersListener;

/**
 * Listener that initializes and binds the containers found in the system configuration.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/single-app
 * @link      http://www.appserver.io
 */
class StartSimpleContainersListener extends StartContainersListener
{

    /**
     * Return's the deployment service to use for container initialization.
     *
     * @return \AppserverIo\SingleApp\Core\Api\SimpleDeploymentService The deployment service
     */
    protected function getDeploymentService()
    {
        return $this->getApplicationServer()->newService('AppserverIo\SingleApp\Core\Api\SimpleDeploymentService');
    }
}
