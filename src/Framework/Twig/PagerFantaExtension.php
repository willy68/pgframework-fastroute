<?php

namespace Framework\Twig;

use Mezzio\Router\RouterInterface;
use Pagerfanta\Pagerfanta;
use Twig\Extension\AbstractExtension;
use Pagerfanta\View\TwitterBootstrap4View;
use Twig\TwigFunction;

class PagerFantaExtension extends AbstractExtension
{

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('paginate', [$this, 'paginate'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param Pagerfanta $paginatedResults
     * @param string $route
     * @param array $routerParams
     * @param array $queryArgs
     * @return string
     */
    public function paginate(
        Pagerfanta $paginatedResults,
        string $route,
        array $routerParams = [],
        array $queryArgs = []
    ): string {
        $view = new TwitterBootstrap4View();
        return $view->render($paginatedResults, function (int $page) use ($route, $routerParams, $queryArgs) {
            if ($page > 1) {
                $queryArgs['p'] = $page;
            }
            $uri = $this->router->generateUri($route, $routerParams);
            if (!empty($queryArgs)) {
                return $uri . '?' . http_build_query($queryArgs);
            }
            return $uri;
        });
    }
}
