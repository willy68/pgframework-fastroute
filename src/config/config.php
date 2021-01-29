<?php

use Framework\Jwt\JwtMiddlewareFactory;
use Psr\Container\ContainerInterface;
use Tuupola\Http\Factory\ResponseFactory;
use Grafikart\Csrf\CsrfMiddleware;
use Middlewares\Whoops;
use Framework\Twig\{
    CsrfExtension,
    FormExtension,
    TextExtension,
    TimeExtension,
    FlashExtension,
    PagerFantaExtension
};
use Framework\Router\FastRouteRouterFactory;
use Framework\Router\RouterTwigExtension;
use Framework\Session\PHPSession;
use Framework\Session\SessionInterface;
use Framework\Renderer\RendererInterface;
use Framework\Renderer\TwigRendererFactory;
use Framework\ActiveRecord\ActiveRecordFactory;
use Framework\Environnement\Environnement;
use Framework\Invoker\InvokerFactory;
use Framework\Validator\Filter\StriptagsFilter;
use Framework\Validator\Filter\TrimFilter;
use Framework\Validator\Validation\{
    DateFormatValidation,
    EmailConfirmValidation,
    EmailValidation,
    ExistsValidation,
    ExtensionValidation,
    MaxValidation,
    MinValidation,
    RangeValidation,
    RequiredValidation,
    SlugValidation,
    UniqueValidation,
    UploadedValidation,
    NotEmptyValidation
};
use Invoker\Invoker;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouterInterface;
use Tuupola\Middleware\JwtAuthentication;

use function DI\create;
use function DI\get;
use function DI\factory;

return [
    'env' => Environnement::getEnv('APP_ENV', 'dev'),
    //'env' => env('ENV', 'production'),
    'app' => Environnement::getEnv('APP', 'web'),
    'jwt.secret' => Environnement::getEnv('APP_KEY', 'abcdefghijklmnop123456789'),
    'twig.extensions' => [
        get(RouterTwigExtension::class),
        get(PagerFantaExtension::class),
        get(TextExtension::class),
        get(TimeExtension::class),
        get(FlashExtension::class),
        get(FormExtension::class),
        get(CsrfExtension::class),
    ],
    'form.validations' => \DI\add([
        'required' => RequiredValidation::class,
        'min' => MinValidation::class,
        'max' => MaxValidation::class,
        'date' => DateFormatValidation::class,
        'email' => EmailValidation::class,
        'emailConfirm' => EmailConfirmValidation::class,
        'notEmpty' => NotEmptyValidation::class,
        'range' => RangeValidation::class,
        'filetype' => ExtensionValidation::class,
        'uploaded' => UploadedValidation::class,
        'slug' => SlugValidation::class,
        'exists' => ExistsValidation::class,
        'unique' => UniqueValidation::class
    ]),
    'form.filters' => \DI\add([
        'trim' => TrimFilter::class,
        'striptags' => StriptagsFilter::class
    ]),
    SessionInterface::class => create(PHPSession::class),
    CsrfMiddleware::class =>
    create()->constructor(get(SessionInterface::class)),
    JwtAuthentication::class => factory(JwtMiddlewareFactory::class),
    Invoker::class => factory(InvokerFactory::class),
    RouterInterface::class => factory(FastRouteRouterFactory::class),
    FastRouteRouter::class => factory(FastRouteRouterFactory::class),
    'duplicate.route' => true,
    RouteCollector::class => \DI\autowire()
        ->constructorParameter("detectDuplicates", \DI\get('duplicate.route')),
    RendererInterface::class => factory(TwigRendererFactory::class),
    Whoops::class => function (ContainerInterface $c) {
        return new Whoops(null, new ResponseFactory());
    },
    'ActiveRecord' => factory(ActiveRecordFactory::class),
    PDO::class => function (ContainerInterface $c) {
        return new PDO(
            $c->get('database.sgdb') . ":host=" . $c->get('database.host') . ";dbname=" . $c->get('database.name'),
            $c->get('database.user'),
            $c->get('database.password'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ]
        );
    }/*,
    ServerRequestInterface::class => function (ContainerInterface $c) {
        return ServerRequest::fromGlobals();
    }*/

];
