<?php
/**
 * Maverick Framework
 *
 * @author Alec Carpenter <alecgunnar@gmail.com>
 */

namespace Maverick\Router\Collection;

use FastRoute\RouteCollector;

class FastRouteRouteCollection extends RouteCollection
{
    /**
     * @param RouteCollector $collector
     */
    public function __invoke(RouteCollector $collector)
    {
        foreach ($this->routes as $route) {
            $collector->addRoute(
                $route->getMethods(),
                $route->getPath(),
                $route
            );
        }
    }
}