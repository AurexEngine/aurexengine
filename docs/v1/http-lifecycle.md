# HTTP Lifecycle (v1.0.0)

The AurexEngine HTTP lifecycle follows a predictable runtime flow:

1. Request captured (`Request::capture`)
2. Kernel receives request
3. Global middleware executed
4. Route matching
5. Route-specific middleware executed
6. Controller resolved via container
7. Controller method dependency injection applied
8. Response returned
9. Terminable middleware executed

---

## Kernel Responsibilities

The Kernel:

- Dispatches the request
- Applies middleware pipeline
- Handles uncaught exceptions
- Delegates rendering to the Exception Handler
- Executes terminable middleware

---

## Error Flow

If an exception is thrown during request handling:

1. Kernel catches `Throwable`
2. Exception Handler renders a Response
3. If debug enabled → detailed error page
4. If production mode → generic 500 response
5. Exception is logged

---

The lifecycle is intentionally minimal and predictable.