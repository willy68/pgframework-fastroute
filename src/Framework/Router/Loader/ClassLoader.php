<?php

namespace Framework\Router\Loader;

use Framework\Router\Annotation\Exception\RouteAnnotationException;


class ClassLoader extends MethodLoader
{

    /**
     * Get the annotation class
     *
     * @param \ReflectionClass $class
     * @return object|null
     */
    protected function getClassAnnotation(\ReflectionClass $class): ?object
    {
        if ($class->isAbstract()) {
            return null;
        }
        
        // Look for @Route annotation
        try {
            $annotation = $this->getAnnotationReader()
                ->getClassAnnotation(
                    $class,
                    $this->annotationClass
                );
        } catch (\Exception $e) {
            throw new RouteAnnotationException(sprintf(
                '@Route annotation on %s is malformed. %s',
                $class->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        if ($annotation instanceof $this->annotationClass) {
            return $annotation;
        }

        return null;
    }
}
