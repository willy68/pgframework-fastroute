<?php

namespace Framework\Router;

use Mezzio\Router\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Undocumented class
 */
class RouterTwigExtension extends AbstractExtension
{

  /**
   * Undocumented variable
   *
   * @var RouterInterface
   */
    private $router;

    /**
     * Undocumented function
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('path', [$this, 'pathFor']),
            new TwigFunction('is_subpath', [$this, 'isSubPath'])
        ];
    }

    /**
     * Genère un lien html
     *
     * @param string $path
     * @param array $params
     * @return string
     */
    public function pathFor(string $path, array $params = []): string
    {
        return $this->router->generateUri($path, $params);
    }

    /**
     * Détermine si un chemin est un sous chemin
     *
     * @param string $path
     * @return bool
     */
    public function isSubPath(string $path): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $expected = $this->router->generateUri($path);
        return strpos($uri, $expected) !== false;
    }
}
