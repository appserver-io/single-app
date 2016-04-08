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

use AppserverIo\Http\HttpResponseStates;
use AppserverIo\Server\Dictionaries\ServerVars;
use AppserverIo\Server\Dictionaries\ModuleHooks;
use AppserverIo\Server\Exceptions\ModuleException;
use AppserverIo\Psr\HttpMessage\Protocol;
use AppserverIo\Psr\HttpMessage\RequestInterface;
use AppserverIo\Psr\HttpMessage\ResponseInterface;
use AppserverIo\Server\Interfaces\RequestContextInterface;
use AppserverIo\Appserver\ServletEngine\Http\Request;
use AppserverIo\Appserver\ServletEngine\Http\Response;
use AppserverIo\Appserver\ServletEngine\ServletEngine;
use AppserverIo\Appserver\ServletEngine\RequestHandler;
use AppserverIo\Appserver\ServletEngine\BadRequestException;
use AppserverIo\Appserver\ServletEngine\SessionManagerInterface;
use AppserverIo\Appserver\ServletEngine\Security\AuthenticationManagerInterface;

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
    protected function findRequestedApplication(RequestContextInterface $requestContext)
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

    /**
     * Process servlet request.
     *
     * @param \AppserverIo\Psr\HttpMessage\RequestInterface          $request        A request object
     * @param \AppserverIo\Psr\HttpMessage\ResponseInterface         $response       A response object
     * @param \AppserverIo\Server\Interfaces\RequestContextInterface $requestContext A requests context instance
     * @param integer                                                $hook           The current hook to process logic for
     *
     * @return boolean
     *
     * @throws \AppserverIo\Server\Exceptions\ModuleException
     */
    public function process(
        RequestInterface $request,
        ResponseInterface $response,
        RequestContextInterface $requestContext,
        $hook
    ) {

        // if false hook is coming do nothing
        if (ModuleHooks::REQUEST_POST !== $hook) {
            return;
        }

        // check if we are the handler that has to process this request
        if ($requestContext->getServerVar(ServerVars::SERVER_HANDLER) !== $this->getModuleName()) {
            return;
        }

        // load the application associated with this request
        $application = $this->findRequestedApplication($requestContext);
        $application->registerClassLoaders();

        // check if the application has already been connected
        if ($application->isConnected() === false) {
            throw new \Exception(sprintf('Application %s has not connected yet', $application->getName()), 503);
        }

        // create a copy of the valve instances
        $valves = $this->valves;
        $handlers = $this->handlers;

        // create a new request instance from the HTTP request
        $servletRequest = new Request();
        $servletRequest->injectHandlers($handlers);
        $servletRequest->injectHttpRequest($request);
        $servletRequest->injectServerVars($requestContext->getServerVars());
        $servletRequest->init();

        // initialize servlet response
        $servletResponse = new Response();
        $servletResponse->init();

        // load the session and the authentication manager
        $sessionManager = $application->search(SessionManagerInterface::IDENTIFIER);
        $authenticationManager = $application->search(AuthenticationManagerInterface::IDENTIFIER);

        // inject the sapplication and servlet response
        $servletRequest->injectContext($application);
        $servletRequest->injectResponse($servletResponse);
        $servletRequest->injectSessionManager($sessionManager);
        $servletRequest->injectAuthenticationManager($authenticationManager);

        // prepare the request instance
        $servletRequest->prepare();

        // initialize static request and application context
        RequestHandler::$requestContext = $servletRequest;
        RequestHandler::$applicationContext = $application;

        // process the valves
        foreach ($valves as $valve) {
            $valve->invoke($servletRequest, $servletResponse);
            if ($servletRequest->isDispatched() === true) {
                break;
            }
        }

        // copy response values to the HTTP response
        $response->setState($servletResponse->getState());
        $response->setVersion($servletResponse->getVersion());
        $response->setStatusCode($servletResponse->getStatusCode());
        $response->setStatusReasonPhrase($servletResponse->getStatusReasonPhrase());

        // copy the body content to the HTTP response
        $response->appendBodyStream($servletResponse->getBodyStream());

        // copy headers to the HTTP response
        foreach ($servletResponse->getHeaders() as $headerName => $headerValue) {
            $response->addHeader($headerName, $headerValue);
        }

        // copy cookies to the HTTP response
        $response->setCookies($servletResponse->getCookies());

        // append the servlet engine's signature
        $response->addHeader(Protocol::HEADER_X_POWERED_BY, get_class($this), true);

        // set response state to be dispatched after this without calling other modules process
        $response->setState(HttpResponseStates::DISPATCH);
    }
}
