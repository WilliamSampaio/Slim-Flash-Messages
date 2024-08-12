<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use WilliamSampaio\SlimFlashMessages\Flash;
use WilliamSampaio\SlimFlashMessages\FlashMiddleware;
use WilliamSampaio\SlimFlashMessages\FlashProvider;

require __DIR__ . '/../vendor/autoload.php';

session_start();
// Important! if the storage is not passed to the constructor,
// $_SESSION will be used
$flash = Flash::getInstance();

// Create App
$app = AppFactory::create();
$app->setBasePath('/example1');  // Optional

// Add FlashMiddleware
$app->add(new FlashMiddleware($flash));

$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {
    // Get FlashProvider from request
    // FlashMiddleware previously took care of adding the FlashProvider to the request
    $flash = FlashProvider::fromRequest($request);

    // Clear all stored values
    $flash->clearAll();

    // The 'add' method allows you to add a flash message or data (as an array, if you prefer!)
    $flash->add('simple', 'Hello World! 1');

    $flash->add('messages', [
        'status' => 'success',
        'text' => '1. PHP is the best!'
    ]);

    echo '<pre>';

    var_dump($flash->getAll());

    // Checks if the key is defined in the storage
    var_dump($flash->has('messages'));

    // Clear a key defined
    $flash->clear('messages');

    var_dump($flash->getAll());
    var_dump($flash->has('messages'));

    $flash->add('simple', 'Hello World! 2');
    $flash->add('simple', 'Hello World! 3');

    var_dump($flash->getAll());

    // Get first item from key
    var_dump($flash->getFirst('simple'));
    // or to pick up and remove first item.
    // var_dump($flash->getFirst('simple', true));

    // Get last item from key
    // var_dump($flash->getLast('simple'));
    // or to pick up and remove last item.
    var_dump($flash->getLast('simple', true));

    var_dump($flash->get('simple'));

    return $response;
});

$app->run();
