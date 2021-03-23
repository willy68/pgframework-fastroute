<?php

namespace Framework\Event;

use Invoker\Invoker;
use League\Event\Listener;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Invoker\ParameterResolver\{
    ResolverChain,
    DefaultValueResolver,
    NumericArrayResolver,
    AssociativeArrayResolver};
use Framework\Invoker\ParameterResolver\ActiveRecordResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Framework\Invoker\ParameterResolver\ActiveRecordAnnotationsResolver;

/**
 * Listener that depend on Container
 */
class InvokerListener implements Listener
{
    protected $callback;

    protected $container;

    protected $invoker;

    /**
     *
     * @param mixed $callback
     * @param ContainerInterface $container
     */
    public function __construct($callback, ContainerInterface $container)
    {
        $this->callback = $callback;
        $this->container = $container;
    }

    /**
     * call the callback with $event parameter
     *
     * @param object $event
     * @return void
     */
    public function __invoke(object $event): void
    {
        $this->getInvoker($this->container)->call($this->callback, [$event]);
    }

    /**
     * crée un Invoker
     *
     * @param ContainerInterface $container
     * @return InvokerInterface
     */
    protected function getInvoker(ContainerInterface $container): InvokerInterface
    {
        if (!$this->invoker) {
            $parameterResolver = new ResolverChain([
                new ActiveRecordAnnotationsResolver(),
                new ActiveRecordResolver(),
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new DefaultValueResolver(),
                new TypeHintContainerResolver($container)
            ]);
            $this->invoker = new Invoker($parameterResolver, $container);
        }
        return $this->invoker;
    }
}
