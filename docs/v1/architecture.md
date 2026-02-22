# AurexEngine v1.0.0 Architecture

AurexEngine is a lightweight PHP runtime engine designed to power custom frameworks and applications.

It provides a stable and minimal core focused on:

- Dependency Injection Container
- HTTP Runtime (Request / Response)
- Middleware Pipeline
- Routing System
- Controller Resolution
- Configuration System
- Environment Loader (.env)
- Logging System
- Centralized Exception Handling

AurexEngine is **not a full-stack framework**.

It intentionally does not include:

- ORM / Database abstraction
- Authentication system
- Session management
- Templating engine
- CLI tooling
- Queue system

These concerns are expected to be implemented by frameworks built on top of AurexEngine.

---

## Core Components

### Container
- Constructor-based dependency injection
- Singleton bindings
- Instance bindings
- Automatic dependency resolution
- Controller method dependency injection

### HTTP Layer
- Request abstraction
- Response abstraction
- JSON response helper
- Redirect helper
- HTTP Kernel
- Terminable middleware support

### Middleware System
- Global middleware
- Route-specific middleware
- Container-resolved middleware
- Middleware priority sorting

### Routing System
- GET / POST route registration
- Route matching
- Route parameters (`/users/{id}`)
- Route grouping
- Route prefixing
- Route naming
- URL generation from named routes

### Configuration
- File-based config (`config/*.php`)
- Dot notation access (`app.debug`)
- Environment-based configuration

### Environment
- `.env` loader
- Values available via `getenv()` and `$_ENV`

### Logging
- File-based logger
- Automatic exception logging
- Pluggable Logger interface

### Error Handling
- Centralized exception handler
- Debug mode support
- Production-safe error responses

---

AurexEngine v1.0.0 guarantees a stable public API for the core runtime.