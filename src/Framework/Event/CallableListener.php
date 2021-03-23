<?php

namespace Framework\Event;

use League\Event\Listener;

/**
 * Listener that don't depend on Container
 */
class CallableListener implements Listener
{
    protected $callback;

    /**
     *
     * @param mixed $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * call the callback with $event parameter
     *
     * @param object $event
     * @return void
     */
    public function __invoke(object $event): void
    {
        call_user_func_array($this->getCallback(), [$event]);
    }

    protected function getCallback(): callable
    {
        $callback = $this->callback;

        // Shortcut for a very common use case
        if ($callback instanceof \Closure) {
            return $callback;
        }

        // If it's already a callable there is nothing to do
        if (is_callable($callback)) {
            // TODO with PHP 8 that should not be necessary to check this anymore
            if (!$this->isStaticCallToNonStaticMethod($callback)) {
                return $callback;
            }
        }

        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback, 2);
        }

        if (is_array($callback) && is_string($callback[0])) {
            $callback[0] = new $callback[0];
        }

        if (is_string($callback) && method_exists($callback, '__invoke')) {
            $callback = new $callback;
        }

        if (! is_callable($callback)) {
            throw new \RuntimeException("Le callback $callback n'est pas un callable");
        }

        return $callback;
    }

    /**
     * Check if the callable represents a static call to a non-static method.
     *
     * @param mixed $callable
     * @throws \ReflectionException
     */
    private function isStaticCallToNonStaticMethod($callable): bool
    {
        if (is_array($callable) && is_string($callable[0])) {
            [$class, $method] = $callable;
            $reflection = new \ReflectionMethod($class, $method);

            return !$reflection->isStatic();
        }

        return false;
    }
}
