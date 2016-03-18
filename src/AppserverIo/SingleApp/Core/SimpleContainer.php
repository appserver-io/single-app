<?php

/**
 * AppserverIo\SingleApp\Core\SimpleContainer
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

use AppserverIo\Appserver\Core\GenericContainer;
use AppserverIo\Appserver\Core\Api\Node\ParamNode;
use AppserverIo\Appserver\Core\Api\Node\ServerNodeInterface;

/**
 * Container implementation for a simple single app scenario.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/single-app
 * @link      http://www.appserver.io
 */
class SimpleContainer extends GenericContainer
{

    /**
     * Return's the prepared server node configuration.
     *
     * @param \AppserverIo\Appserver\Core\Api\Node\ServerNodeInterface $serverNode The server node
     *
     * @return \AppserverIo\Appserver\Core\ServerNodeConfiguration The server node configuration
     */
    protected function getServerNodeConfiguration(ServerNodeInterface $serverNode)
    {

        // override the document root
        $serverNode->setParam('documentRoot', ParamNode::TYPE_STRING, getcwd());

        // add the server node configuration
        return parent::getServerNodeConfiguration($serverNode);
    }

    /**
     * (non-PHPdoc)
     *
     * @return string The application base directory for this container
     * @see \AppserverIo\Appserver\Core\Api\ContainerService::getAppBase()
     */
    public function getAppBase()
    {
        return dirname(getcwd());
    }
}
