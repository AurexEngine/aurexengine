<?php

require __DIR__ . '/../vendor/autoload.php';

use AurexEngine\Http\Request;
use AurexEngine\Http\Kernel;

/** @var \AurexEngine\Foundation\Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';

/**
 * If you still want temporary test routes, do it here (optional)
 * so bootstrap/app.php stays clean.
 */
$router = $app->make(\AurexEngine\Routing\Router::class);
$router->get('/', fn() => "Aurex Engine is alive ✅\n");

// $router->group([
//     'prefix' => 'admin',
//     'middleware' => [AdminAuth::class],
//     'as' => 'admin.',
// ], function (Router $router) {
//     $router->get('/users', 'UserController@index')->name('users.index');
//     // full path: /admin/users
//     // full name: admin.users.index
// });

// $router->url('admin.users.show', ['id' => 10]); // "/admin/users/10" (if route path contains {id})

$request = Request::capture();

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);