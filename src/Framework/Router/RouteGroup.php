<?php

namespace Framework\Router;

use Mezzio\Router\Route;
use Mezzio\Router\RouteInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Router\RouteCollectionTrait;
use Mezzio\Router\Middleware\Stack\MiddlewareAwareStackTrait;
use Mezzio\Router\RouteGroup as RouterRouteGroup;

/**
 * Ex:
 * ```
 * $router->group('/admin', function (RouteGroup $route) {
 * $route->route('/acme/route1', 'AcmeController::actionOne', 'route1', [GET]);
 * $route->route('/acme/route2', 'AcmeController::actionTwo', 'route2', [GET])->setScheme('https');
 * $route->route('/acme/route3', 'AcmeController::actionThree', 'route3', [GET]);
 * })
 * ->middleware(Middleware::class);
 * ```
 *
 */
class RouteGroup extends RouterRouteGroup
{
    use MiddlewareAwareStackTrait;
    use RouteCollectionTrait;

    /**
     * Route prefix for this group
     *
     * @var string
     */
    private $prefix;

    /**
     * Called by router
     *
     * @var callable
     */
    private $callable;

    /**
     * Router
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * Construct
     *
     * @param string $prefix
     * @param callable $callable
     * @param RouterInterface $router
     */
    public function __construct(string $prefix, callable $callable, RouterInterface $router)
    {
        $this->prefix = $prefix;
        $this->callable = $callable;
        $this->router = $router;
    }

    /**
     * Run $callable
     *
     * @return void
     */
    public function __invoke()
    {
        ($this->callable)($this);
    }

    /**
     * Add route
     *
     * @param string $uri
     * @param string|callable $callable
     * @param string|null $name
     * @param array|null $method
     * @return RouteInterface
     */
    public function addRoute(Route $route): Route
    {
        $uri = $route->getPath();
        $path  = ($uri === '/') ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($uri, '/'));
        /** @var Route $route */
        $route->setPath($path);

        $name = $route->getName();
        $method = $route->getAllowedMethods();
        if ($name === null) {
            $name = ($method === null) ? $this->prefix . $path : $this->prefix . $path . '^' . join(':', $method);
        }
        $route->setName($name);

        $route->setParentGroup($this);

        $route = $this->router->addRoute($route);
        return $route;
    }

    /**
     * Perfom all crud routes for a given class controller
     *
     * @param string|callable $callable the class name generally
     * @param string $prefixName
     * @return self
     */
    public function crud($callable, string $prefixName): self
    {
        $this->get("/", $callable . '::index', "$prefixName.index");
        $this->get("/new", $callable . '::create', "$prefixName.create");
        $this->post("/new", $callable . '::create', "$prefixName.create.post");
        $this->get("/{id:\d+}", $callable . '::edit', "$prefixName.edit");
        $this->post("/{id:\d+}", $callable . '::edit', "$prefixName.edit.post");
        $this->delete("/{id:\d+}", $callable . '::delete', "$prefixName.delete");
        return $this;
    }

    /**
     * Get the value of prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}