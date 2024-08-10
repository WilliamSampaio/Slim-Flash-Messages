# Slim Flash Messages

This library allows you to use temporary messages in your Slim project. It is easily integrated with the Twig template system, through an extension that provides functions to grab and use in the template. It is not limited to creating simple message strings but also allows the use of other data types such as arrays.

## Install

```bash
composer require williamsampaio/slim-flash-messages
```

## Usage Examples (Slim 4)

### Simplest possible

Twig integration is not mandatory, as you can see in this example, where the focus is on demonstrating the messaging provider API.

```php
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use WilliamSampaio\SlimFlashMessages\MessageProvider;
use WilliamSampaio\SlimFlashMessages\SlimFlashMiddleware;

require __DIR__ . '/../vendor/autoload.php';

session_start();
// Important! if the storage is not passed to the constructor, 
// $_SESSION will be used
$flash = new MessageProvider();

// Create App
$app = AppFactory::create();
$app->setBasePath('/example1'); // Optional

// Add SlimFlashMiddleware
$app->add(new SlimFlashMiddleware($flash, 'flash'));

$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {

    // Get MessageProvider from request
    // SlimFlashMiddleware previously took care of adding the MessageProvider to the request
    $flash = MessageProvider::fromRequest($request, 'flash');

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

```

### With Container

This example uses the [php-di](https://php-di.org/doc/getting-started.html) container and [slim/twig-view](https://www.slimframework.com/docs/v4/features/twig-view.html).

```php
<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use WilliamSampaio\SlimFlashMessages\MessageProvider;
use WilliamSampaio\SlimFlashMessages\SlimFlashMiddleware;
use WilliamSampaio\SlimFlashMessages\SlimFlashTwigExtension;

require __DIR__ . '/../vendor/autoload.php';

// Create a new DI Container
$container = new Container();

// Add a MessageProvider to the container
$container->set('flash', function () {

    session_start();

    // Important! if the storage is not passed to the constructor, 
    // $_SESSION will be used
    return new MessageProvider();
});

// Set container to create App with on AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->setBasePath('/example2'); // Optional

// Add SlimFlashMiddleware from container
$app->add(SlimFlashMiddleware::createFromContainer($app, 'flash'));

// Create Twig and add SlimFlashTwigExtension
$twig = Twig::create(__DIR__ . '/templates', ['cache' => false]);
$twig->addExtension(SlimFlashTwigExtension::createFromContainer($app, 'flash'));

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {

    // Get Twig and MessageProvider from request
    $view = Twig::fromRequest($request);
    // SlimFlashMiddleware previously took care of adding the MessageProvider to the request
    $flash = MessageProvider::fromRequest($request, 'flash');

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

```

### Running these examples

The easiest way to run these examples is through docker.

```bash
git clone https://github.com/WilliamSampaio/Slim-Flash-Messages.git
cd Slim-Flash-Messages
docker compose up -d --build
```

When you complete the up process, access:

- [http://localhost:8080/example1/](http://localhost:8080/example1/)
- [http://localhost:8080/example2/](http://localhost:8080/example2/)

## Custom Twig Functions

`SlimFlashTwigExtension` provides these functions to your Twig templates.

### flash()

It receives two optional parameters, `key` *(string/null = ***null***)* and `clear` *(bool = ***true***)*.

- `key`: If is not specified, an array with all the data in storage will be returned, otherwise only the array with the data indexed by the key value will be returned.
- `clear`: If is false, the items will not be removed from storage after the function is called.

```twig
{% for msg in flash('messages', false) %}
<div class="alert alert-{{ msg.alert }} alert-dismissible fade show" role="alert">
    {{ msg.text }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
{% endfor %}
```

### flash_first()

It receives two parameters, `key` *(string)* and `remove` *(bool = ***true***)*.

- `key`: First item from array with the data indexed by the key value will be returned.
- `remove` (optional): If is false, the item will not be removed from storage after the function is called.

```twig
{% set first = flash_first('messages') %}
<div class="alert alert-{{ first.alert }} alert-dismissible fade show" role="alert">
    {{ first.text }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
```

### flash_last()

It receives two parameters, `key` *(string)* and `remove` *(bool = ***true***)*.

- `key`: Last item from array with the data indexed by the key value will be returned.
- `remove` (optional): If is false, the item will not be removed from storage after the function is called.

```twig
{% set last = flash_last('messages') %}
<div class="alert alert-{{ last.alert }} alert-dismissible fade show" role="alert">
    {{ last.text }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
```

### flash_has()

### flash_clear()

### flash_clear_all()

## Tests

To execute the test suite, you'll need to clone the repository and install the dependencies.

```bash
git clone https://github.com/WilliamSampaio/Slim-Flash-Messages.git
cd Slim-Flash-Messages
composer install
composer test
# Or
# composer coverage
```

## License

The MIT License (MIT). Please see [License File](https://raw.githubusercontent.com/WilliamSampaio/Slim-Flash-Messages/master/LICENSE) for more information.
