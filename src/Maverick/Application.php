<?php

/**
 * Maverick Framework
 *
 * (c) Alec Carpenter <gunnar94@me.com>
 */

namespace Maverick;

use Maverick\Http\Request,
    Maverick\Http\Router,
    Maverick\Http\Response,
    Maverick\Http\Session,
    Maverick\Controller\ExceptionController,
    Maverick\DependencyManagement\ServiceManager,
    Maverick\Exception\InvalidValueException,
    Maverick\Exception\NoRouteException,
    Exception;

class Application {
    /**
     * The current version
     *
     * @var string
     */
    const VERSION = '0.2.0';

    /**
     * Debug level for the app
     *
     * @var int
     */
    private $debugLevel;

    /**
     * Various debug levels
     *
     * @var int
     */
    const DEBUG_LEVEL_DEV  = 1005;
    const DEBUG_LEVEL_TEST = 1010;
    const DEBUG_LEVEL_BETA = 1015;
    const DEBUG_LEVEL_PROD = 1020;

    /** 
     * The current request being worked with
     *
     * @var Maverick\Http\Request
     */
    public $request;

    /**
     * The router for the current request
     *
     * @var Maverick\Http\Router
     */
    public $router;

    /**
     * The response for the current request
     *
     * @var Maverick\Http\Response
     */
    public $response;

    /**
     * The session for the current request
     *
     * @var Maverick\Http\Session
     */
    public $session;

    /**
     * The service manager to be used by the app
     *
     * @var Maverick\DependencyManagement\ServiceManager
     */
    public $services;

    /**
     * Constructor
     *
     * @param int $debugLevel=null
     */
    public function __construct($debugLevel=null) {
        $this->debugLevel = $debugLevel ?: self::DEBUG_LEVEL_PROD;

        $this->registerErrorHandler();

        $this->services = new ServiceManager();

        $this->registerDefaultServices();

        $this->request  = $this->services->get('request');
        $this->response = $this->services->get('response');
        $this->router   = $this->services->get('router');
        $this->session  = $this->services->get('session');
    }

    /**
     * Gets the debug level
     *
     * @codeCoverageIgnore
     * @return int
     */
    public function getDebugLevel() {
        return $this->debugLevel;
    }

    /**
     * Compares the debug level to another
     *
     * All comparisions are handled in this way:
     *
     * {current level} {method} {compare level}
     *
     * @param  string $method
     * @param  int    $comareTo
     * @return boolean
     */
    public function debugCompare($method, $compareTo) {
        switch($method) {
            case '>':
                return $this->debugLevel > $compareTo;
            case '>=':
                return $this->debugLevel >= $compareTo;
            case '<':
                return $this->debugLevel < $compareTo;
            case '<=':
                return $this->debugLevel <= $compareTo;
            case '==':
            case '===':
                return $this->debugLevel === $compareTo;
            case '!=':
                return $this->debugLevel != $compareTo;
            default:
                throw new InvalidValueException($method . ' is not a valid compare method. Please try: >, >=, <, <=, == or !=.');
        }

        return false;
    }

    /**
     * Registers default services with the container
     *
     * @codeCoverageIgnore
     */
    private function registerDefaultServices() {
        $app = $this;

        $this->services->register('request', function() {
            return new Request();
        });

        $this->services->register('response', function($mgr) {
            return new Response($mgr->get('request'));
        });

        $this->services->register('router', function($mgr) {
            return new Router($mgr->get('request'), $mgr->get('response'));
        });

        $this->services->register('session', function() {
            return new Session();
        });

        $this->services->register('exception.controller', function() use($app) {
            return new ExceptionController($app);
        });
    }

    /**
     * Registers the error handler
     *
     * @codeCoverageIgnore
     */
    private function registerErrorHandler() {
        ini_set('error_reporting', 'E_ALL');
        ini_set('display_errors', 0);

        $shutdown = function(Exception $exception) {
            $code   = 500;
            $method = 'error500Action';

            if(get_class($exception) == 'Maverick\Exception\NoRouteException') {
                $code   = 404;
                $method = 'error404Action';
            }

            $this->response->setBody($this->services->call('exception.controller->' . $method, [$exception]));

            $this->response->setStatus($code);
            $this->response->send();
        };

        $errorHandler = function($num, $str, $file, $line) use($shutdown) {
            $shutdown(new Exception($str . ' in ' . $file . ' on line ' . $line . '.'));
        };

        set_exception_handler($shutdown);
        set_error_handler($errorHandler);

        register_shutdown_function(function() use($errorHandler) {
            if($err = error_get_last()) {
                call_user_func_array($errorHandler, $err);
            }
        });
    }

    /**
     * Finishes off the request, and sends the response
     *
     * @throws Maverick\Exception\NoRouteException
     */
    public function finish() {
        if(!$this->router->hasRouted()) {
            throw new NoRouteException('No route exists for ' . htmlentities($this->request->getUrn()) . ' using method ' . htmlentities($this->request->getMethod()) . ' and ' . ($this->request->isHttps() ? 'https' : 'http'));
        }

        $this->response->send();
    }
}