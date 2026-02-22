# Dependency Injection Container (v1)

AurexEngine includes a lightweight service container.

## Features

- Constructor injection
- Singleton binding
- Instance binding
- Automatic dependency resolution
- Controller method injection

---

## Example

```php
$app->singleton(Logger::class, function ($app) {
    return new FileLogger($app->basePath('storage/logs/app.log'));
});

# Automatic resolution:
class UserController
{
    public function __construct(Logger $logger) {}
}

# Controller method injection:
public function show(Request $request, int $id)