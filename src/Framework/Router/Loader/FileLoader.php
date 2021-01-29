<?php

namespace Framework\Router\Loader;

use ReflectionMethod;
use Mezzio\Router\Route;
use Mezzio\Router\RouteGroup;
use Doctrine\Common\Annotations\Reader;
use Framework\Router\Parser\PhpTokenParser;
use Mezzio\Router\RouteCollector;

class FileLoader extends ClassLoader
{
    protected $collector;

    public function __construct(
        RouteCollector $collector,
        ?Reader $reader = null
    ) {
        if (!\function_exists('token_get_all')) {
            throw new \LogicException("Function token_get_all don't exists in this system");
        }

        parent::__construct($reader);
        $this->collector = $collector;
    }

    /**
     * Parse annotations @Route and add routes to the router
     *
     * @param string $file
     * @return Route[]|null
     */
    public function load(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }

        $class = PhpTokenParser::findClass($file);
        if (!$class || !class_exists($class)) {
            return null;
        }

        $reflectionClass = new \ReflectionClass($class);
        if ($reflectionClass->isAbstract()) {
            return null;
        }

        $classAnnotation = $this->getClassAnnotation($reflectionClass);

        $routes = [];
        foreach ($reflectionClass->getMethods() as $method) {
            foreach ($this->getMethodAnnotations($method) as $methodAnnotation) {
                $routes[] = $this->addRoute($methodAnnotation, $method, $classAnnotation);
            }
        }

        if (empty($routes) && $classAnnotation && $reflectionClass->hasMethod('__invoke')) {
            $routes[] = $this->collector->route(
                $classAnnotation->getPath(),
                $reflectionClass->getName(),
                $classAnnotation->getName(),
                $classAnnotation->getMethods());
        }

        gc_mem_caches();
        return $routes;
    }

    /**
     * Add route to router
     *
     * @param object $methodAnnotation
     * @param \ReflectionMethod $method
     * @param object|null $classAnnotation
     * @return Route
     */
    protected function addRoute(
        object $methodAnnotation,
        ReflectionMethod $method,
        ?object $classAnnotation
    ): Route {

        $path = $methodAnnotation->getPath();
        if ($classAnnotation) {
            $path = $classAnnotation->getPath() . $path;
        }
        return $this->collector->route(
            $path,
            $method->getDeclaringClass()->getName() . "::" . $method->getName(),
            $methodAnnotation->getName(),
            $methodAnnotation->getMethods()
        );
    }
}
