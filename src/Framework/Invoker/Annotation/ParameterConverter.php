<?php

namespace Framework\Invoker\Annotation;

use Framework\Invoker\Exception\InvalidAnnotation;

/**
 * "ParameterConverter" annotation.
 *
 * Marks a method as an injection point
 *
 * First param is the method parameter to convert from route param
 * ```
 * Ex @ParameterConverter("post", options={"id"="post_id"})
 * ```
 *
 * @api
 *
 * @Annotation
 * @Target({"METHOD"})
 *
 */
final class ParameterConverter
{

    /**
     * Parameters, indexed by the parameter number (index) or name.
     *
     * Used if the annotation is set on a method
     * @var array
     */
    private $parameters = [];

    /**
     * @throws InvalidAnnotation
     */
    public function __construct(array $parameters)
    {
        // Method param name
        if (!isset($parameters['value'])) {
            throw new InvalidAnnotation(sprintf(
                '@ParameterConverter("name", options={"id = "value"}) expects parameter "name", %s given.',
                json_encode($parameters)
            ));
            return;
        }

        $this->parameters = $parameters;
    }

    /**
     * @return array Parameters, indexed by the parameter number (index) or name
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
