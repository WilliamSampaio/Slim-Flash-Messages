# Slim Flash Messages

![Coveralls](https://img.shields.io/coverallsCoverage/github/WilliamSampaio/Slim-Flash-Messages?style=flat-square&logo=coveralls&kill_cache=1)
![Packagist Version](https://img.shields.io/packagist/v/WilliamSampaio/Slim-Flash-Messages?style=flat-square&logo=packagist)
![Packagist Downloads](https://img.shields.io/packagist/dt/WilliamSampaio/Slim-Flash-Messages?style=flat-square&logo=packagist)
![GitHub License](https://img.shields.io/github/license/WilliamSampaio/Slim-Flash-Messages?style=flat-square&logo=github)

This library allows you to use temporary messages in your Slim project. It is easily integrated with the Twig template system, through an extension that provides functions to grab and use in the template. It is not limited to creating simple message strings but also allows the use of other data types such as arrays.

## Install

```bash
composer require williamsampaio/slim-flash-messages
```

## Usage Examples (Slim 4)

```php
// app/dependencies.php

//...
use SlimFlashMessages\Flash;
use SlimFlashMessages\FlashProviderInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        //...
        FlashProviderInterface::class => function () {
            return Flash::getInstance();
        },
    ]);
};
```

```php
// app/middleware.php

//...
// use SlimFlashMessages\FlashMiddleware;
use SlimFlashMessages\FlashTwigExtension;

return function (App $app) {
    //...
    // Optional if you are working with dependency injection,
    // using the middleware is only useful if you need to obtain the Flash instance from request.
    // $app->add(FlashMiddleware::createFromContainer($app));

    // With Twig
    $twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
    $twig->addExtension(FlashTwigExtension::createFromContainer($app));
    $app->add(TwigMiddleware::create($app, $twig));
};
```

```php
// Your controller

//...
use Slim\Views\Twig;
// use SlimFlashMessages\FlashProvider;
use SlimFlashMessages\FlashProviderInterface;

class YourController
{
    private $flash;
    private $view;

    public function __construct(FlashProviderInterface $flash, Twig $view)
    {
        $this->flash = $flash;
        $this->view = $view;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        // If you are working with middleware instead of dependency injection it will be this way.
        // $flash = FlashProvider::fromRequest($request);

        $this->flash->add('messages', 'Hello!');

        return $this->view->render($response, 'template.twig');
    }

    //...
}
```

```twig
{# template.twig #}
{% for msg in flash('messages') %}
    {{ msg }}
{% endfor %}
```

### Simplest possible

Twig integration is not mandatory, as you can see in this example, where the focus is on demonstrating the messaging provider API.

```php
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use SlimFlashMessages\Flash;
use SlimFlashMessages\FlashMiddleware;
use SlimFlashMessages\FlashProvider;

require __DIR__ . '/../vendor/autoload.php';

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
    var_dump($flash->get_first('simple'));
    // or to pick up and remove first item.
    // var_dump($flash->get_first('simple', true));

    // Get last item from key
    // var_dump($flash->get_last('simple'));
    // or to pick up and remove last item.
    var_dump($flash->get_last('simple', true));

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
use SlimFlashMessages\Flash;
use SlimFlashMessages\FlashMiddleware;
use SlimFlashMessages\FlashProvider;
use SlimFlashMessages\FlashProviderInterface;
use SlimFlashMessages\FlashTwigExtension;

require __DIR__ . '/../vendor/autoload.php';

// Create a new DI Container
$container = new Container();

// Add a FlashProvider to the container
$container->set(FlashProviderInterface::class, function () {
    // Important! if the storage is not passed to the constructor,
    // $_SESSION will be used
    return Flash::getInstance();
});

// Set container to create App with on AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->setBasePath('/example2');  // Optional

// Add FlashMiddleware from container
$app->add(FlashMiddleware::createFromContainer($app));

// Create Twig and add FlashTwigExtension
$twig = Twig::create(__DIR__ . '/templates', ['cache' => false]);
$twig->addExtension(FlashTwigExtension::createFromContainer($app));

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

```

In template:

```twig
{% for msg in flash('messages') %}
<div class="alert alert-{{ msg.alert }} alert-dismissible fade show" role="alert">
    {{ msg.text }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
{% endfor %}
```

### Running these examples

The easiest way to run these examples is through docker.

```bash
git clone https://github.com/WilliamSampaio/Slim-Flash-Messages.git
cd Slim-Flash-Messages
docker compose up -d --build
```

When you complete the up process, access:

- Example 1: [http://localhost:8080/example1/](http://localhost:8080/example1/)
- Example 2: [http://localhost:8080/example2/](http://localhost:8080/example2/)
- Code Coverage: [http://localhost:8080/coverage/index.html](http://localhost:8080/coverage/index.html)

## Custom Twig Functions

`FlashTwigExtension` provides these functions to your Twig templates.

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

It receives one parameters, `key` *(string)*. Checks if a key is defined in the storage. Return `true` or `false`.

- `key`: The key that will be checked.

```twig
{{ flash_has('messages') ? 'exists!' : "it doesn't exist..." }}
```

### flash_clear()

It receives one optional parameters, `key` *(string)*. Removes data from storage. Return `void`.

- `key` (optional): The key that will be removed. If not defined, it removes all data from the storage.

```twig
{{ flash_clear('messages') }}
```

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
