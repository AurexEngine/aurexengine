# Configuration

## Overview

Documentation for Configuration in AurexEngine v1.

---

# 📁 docs/v1/configuration.md

```md
# Configuration (v1)

Configuration files live in:

### config/*.php - Example:
```php
return [
    'debug' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOL),
];
```
### Access values:
```php
$config->get('app.debug');
```

### Environment Variables - .env file is automatically loaded.

```php
APP_DEBUG=true
```
