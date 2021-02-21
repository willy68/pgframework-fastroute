<?php

namespace Framework\Router;

use Framework\Router\RouteGroup;
use Mezzio\Router\Route as RouterRoute;
use Mezzio\Router\RouteGroup as RouterRouteGroup;

class Route extends RouterRoute
{

    /**
     * parent group
     *
     * @var RouteGroup
     */
    private $group;



    /**
     * Get the parent group
     *
     * @return RouteGroup
     */
    public function getParentGroup(): ?RouteGroup
    {
        return $this->group;
    }

    /**
     * Set the parent group
     *
     * @param RouteGroup $group
     *
     * @return Route
     */
    public function setParentGroup(RouterRouteGroup $group): self
    {
        $this->group = $group;
        $prefix      = $this->group->getPrefix();
        $path        = $this->getPath();

        if (strcmp($prefix, substr($path, 0, strlen($prefix))) !== 0) {
            $path = $prefix . $path;
            $this->path = $path;
        }

        return $this;
    }
}
