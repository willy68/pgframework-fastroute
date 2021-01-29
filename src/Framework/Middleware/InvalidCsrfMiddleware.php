<?php

namespace Framework\Middleware;

use Framework\Response\ResponseRedirect;
use Framework\Session\FlashService;
use Grafikart\Csrf\InvalidCsrfException;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\{
    RequestHandlerInterface,
    MiddlewareInterface
};

class InvalidCsrfMiddleware implements MiddlewareInterface
{

    /**
     * Undocumented variable
     *
     * @var FlashService
     */
    private $flashService;

    /**
     * InvalidCsrfMiddleware constructor.
     * @param FlashService $flashService
     */
    public function __construct(FlashService $flashService)
    {
        $this->flashService = $flashService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (InvalidCsrfException $e) {
            $this->flashService->error('Vous n\'avez pas de token valid pour executer cette action');
            return new ResponseRedirect('/');
        }
    }
}
