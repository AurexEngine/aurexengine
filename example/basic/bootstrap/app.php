<?php

use AurexEngine\Foundation\Application;
use AurexEngine\Foundation\Bootstrap\LoadConfiguration;
use AurexEngine\Foundation\Bootstrap\LoadEnvironment;
use AurexEngine\Http\Kernel;
use AurexEngine\Routing\Dispatcher;
use AurexEngine\Routing\Router;
use AurexEngine\Support\FileLogger;
use AurexEngine\Support\Logger;

/** @var Application $app */
$app = new Application(dirname(__DIR__));

/**
 * Bootstrappers (order matters)
 * - env first
 * - config second
 */
$app->bootstrapWith([
    LoadEnvironment::class,
    LoadConfiguration::class,
]);

/**
 * Core bindings
 */

// Router
$app->singleton(Router::class, function () {
    $router = new Router();
    $router->setControllerNamespace('App\\Http\\Controllers'); // change if needed
    return $router;
});

// Dispatcher
$app->singleton(Dispatcher::class, function (Application $app) {
    return new Dispatcher(
        $app->make(Router::class),
        $app // Application extends Container
    );
});

// Logger
$app->singleton(Logger::class, function (Application $app) {
    return new FileLogger($app->basePath('storage/logs/app.log'));
});

// Kernel (NOTE: requires Handler injected via container auto-resolve)
$app->singleton(Kernel::class, function (Application $app) {
    return new Kernel(
        $app->make(Dispatcher::class),
        $app->make(\AurexEngine\Foundation\Exceptions\Handler::class)
    );
});

return $app;