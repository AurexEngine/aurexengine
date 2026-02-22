# Routing

## Overview

Documentation for the Routing system in AurexEngine v1.

---

# 📁 docs/v1/routing.md
```md
# Routing System (v1)

## Basic Routes

```php
$router->get('/users', ...);
$router->post('/users', ...);

#Route Parameters
$router->get('/users/{id}', 'UserController@show');

#Route Grouping
$router->group([
    'prefix' => 'admin',
    'middleware' => [AdminMiddleware::class],
    'as' => 'admin.',
], function ($router) {
    $router->get('/users', 'UserController@index')->name('users.index');
});

#Route Naming
$router->get('/users', ...)->name('users.index');

#URL generation:
$router->url('users.index');