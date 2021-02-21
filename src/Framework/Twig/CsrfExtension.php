<?php

namespace Framework\Twig;

use Exception;
use Twig\TwigFunction;
use Framework\Middleware\CsrfGetCookieMiddleware;

class CsrfExtension extends \Twig\Extension\AbstractExtension
{

    /**
     * @var CsrfGetCookieMiddleware
     */
    private $middleware;

    /**
     * CsrfExtension constructor.
     * @param CsrfGetCookieMiddleware $middleware
     */
    public function __construct(CsrfGetCookieMiddleware $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf_input', [$this, 'csrfInput'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function csrfInput()
    {
        return "<input type=\"hidden\" " .
        "name=\"{$this->middleware->getFormKey()}\" " .
        "value=\"{$this->middleware->generateToken()}\"/>";
    }
}
