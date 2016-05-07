<?php

/**
 * AppserverIo\SingleApp\ServletEngine\SimpleServletEngine
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

namespace AppserverIo\SingleApp\ServletEngine;

use AppserverIo\Server\Dictionaries\ServerVars;
use AppserverIo\Server\Interfaces\RequestContextInterface;
use AppserverIo\Appserver\ServletEngine\ServletEngine;
use AppserverIo\Appserver\ServletEngine\BadRequestException;

/**
 * A servlet engine implementation to handle a single app container.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/single-app
 * @link      http://www.appserver.io
 */
class SimpleServletEngine extends ServletEngine
{

    /**
     * Simply returns the first application, assuming we only have one. If no application
     * has been deployed, an exception will be thrown.
     *
     * @param \AppserverIo\Server\Interfaces\RequestContextInterface $requestContext Context of the current request
     *
     * @return null|\AppserverIo\Psr\Application\ApplicationInterface
     * @throws \AppserverIo\Appserver\ServletEngine\BadRequestException Is thrown if no application is available
     */
    public function findRequestedApplication(RequestContextInterface $requestContext)
    {

        // return the first application (we only have one)
        foreach ($this->applications as $application) {
            return $application;
        }

        // if we did not find anything we should throw a bad request exception
        throw new BadRequestException(
            sprintf(
                'Can\'t find application for URL %s%s',
                $requestContext->getServerVar(ServerVars::HTTP_HOST),
                $requestContext->getServerVar(ServerVars::X_REQUEST_URI)
            ),
            404
        );
    }
}
