<?php

namespace Framework\Router;

use Mezzio\Router\RouteGroup;
use Mezzio\Router\RouteResult;
use Mezzio\Router\FastRouteRouter;
use Psr\Http\Message\ServerRequestInterface;

class Router extends FastRouteRouter
{

    /**
     * Add RouteGroup
     *
     * Ex:
     * ```
     * $router->group('/admin', function (RouteGroup $route) {
     *  $route->route('/acme/route1', 'AcmeController::actionOne', 'route1', [GET]);
     *  $route->route('/acme/route2', 'AcmeController::actionTwo', 'route2', [GET])->lazyMiddleware(Middleware::class);
     *  $route->route('/acme/route3', 'AcmeController::actionThree', 'route3', [GET]);
     * })
     * ->middleware(Middleware::class);
     * ```
     *
     * @param string $prefix
     * @param callable $callable
     * @return RouteGroup
     */
    public function group(string $prefix, callable $callable): RouteGroup
    {
        $group = new RouteGroup($prefix, $callable, $this);
        $this->groups[] = $group;

        return $group;
    }

    /**
     * Undocumented function
     *
     * @param string $prefixPath
     * @param string|callable $callable
     * @param string $prefixName
     * @return RouteGroup
     */
    public function crud(string $prefixPath, $callable, string $prefixName): RouteGroup
    {
        return $this->group(
            $prefixPath,
            function (RouteGroup $route) use ($callable, $prefixName) {
                $route->crud($callable, $prefixName);
            }
        );
    }

    public function match(ServerRequestInterface $request): RouteResult
    {
        $this->processGroups();
        return parent::match($request);
    }

    /**
     * Generate a URI based on a given route.
     *
     * Replacements in FastRoute are written as `{name}` or `{name:<pattern>}`;
     * this method uses `FastRoute\RouteParser\Std` to search for the best route
     * match based on the available substitutions and generates a uri.
     *
     * @param string $name Route name.
     * @param array $substitutions Key/value pairs to substitute into the route
     *     pattern.
     * @param array $options Key/value option pairs to pass to the router for
     *     purposes of generating a URI; takes precedence over options present
     *     in route used to generate URI.
     *
     * @return string URI path generated.
     * @throws Exception\RuntimeException if the route name is not known
     *     or a parameter value does not match its regex.
     */
    public function generateUri(string $name, array $substitutions = [], array $options = []): string
    {
        $this->processGroups();
        return parent::generateUri($name, $substitutions, $options);
    }

    /**
     * Process all groups
     *
     * Adds all of the group routes to the collection and determines if the group
     * strategy should be be used.
     *
     * @return void
     */
    protected function processGroups(): void
    {
        foreach ($this->groups as $key => $group) {
            unset($this->groups[$key]);
            $group();
        }
    }
}
