<?php

namespace Framework\Router\Loader;

use ReflectionMethod;
use Framework\Router\Annotation\Route;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Framework\Router\Annotation\Exception\RouteAnnotationException;

class MethodLoader
{
    protected $reader;

    protected $annotationClass = Route::class;

    public function __construct(?Reader $reader = null)
    {
        $this->reader = $reader;
    }

    /**
     * Change Annotation class
     *
     * @param string $class
     * @return void
     */
    public function setAnnotation(string $class)
    {
        $this->annotationClass = $class;
    }

    /**
     * Recherche les annotations de route
     *
     * @param \ReflectionMethod $method
     * @return iterable|null
     */
    protected function getMethodAnnotations(ReflectionMethod $method): ?iterable
    {
        // Look for @Route annotation
        try {
            $annotations = $this->getAnnotationReader()
                ->getMethodAnnotations(
                    $method
                );
        } catch (\Exception $e) {
            throw new RouteAnnotationException(sprintf(
                '@Route annotation on %s::%s is malformed. %s',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $this->annotationClass) {
                yield $annotation;
            }
        }
        return null;
    }

    /**
     * @return AnnotationReader The annotation reader
     */
    public function getAnnotationReader(): Reader
    {
        if ($this->reader === null) {
            AnnotationRegistry::registerLoader('class_exists');
            $this->reader = new AnnotationReader();
        }

        return $this->reader;
    }
}
