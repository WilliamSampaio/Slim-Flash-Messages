<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use SlimFlashMessages\Flash;
use SlimFlashMessages\FlashMiddleware;
use SlimFlashMessages\FlashProvider;
use SlimFlashMessages\FlashTwigExtension;

require __DIR__ . '/../vendor/autoload.php';

// Create a new DI Container
$container = new Container();

// Add a FlashProvider to the container
$container->set('flash', function () {
    session_start();

    // Important! if the storage is not passed to the constructor,
    // $_SESSION will be used
    return Flash::getInstance();
});

// Set container to create App with on AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->setBasePath('/example2');  // Optional

// Add FlashMiddleware from container
$app->add(FlashMiddleware::createFromContainer($app, 'flash'));

// Create Twig and add FlashTwigExtension
$twig = Twig::create(__DIR__ . '/templates', ['cache' => false]);
$twig->addExtension(FlashTwigExtension::createFromContainer($app, 'flash'));

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {
    // Get Twig and FlashProvider from request
    $view = Twig::fromRequest($request);

    // FlashMiddleware previously took care of adding the FlashProvider to the request
    $flash = FlashProvider::fromRequest($request, 'flash');

    $alerts = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];

    // The 'add' method allows you to add a flash message or data (as an array, if you prefer!)
    $flash->add('simple', 'Hello World!');

    $flash->add('messages', [
        'alert' => $alerts[array_rand($alerts)],
        'text' => '1. PHP is the best!'
    ]);

    $flash->add('messages', [
        'alert' => $alerts[array_rand($alerts)],
        'text' => '2. Slim Framework is amazing!'
    ]);

    $flash->add('messages', [
        'alert' => $alerts[array_rand($alerts)],
        'text' => '3. Lorem ipsum!'
    ]);

    return $view->render($response, 'template.html.twig', [
        'page' => 'Slim Flash Messages',
    ]);
});

$app->run();
