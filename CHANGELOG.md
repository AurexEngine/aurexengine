```md
# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-02-22
### Added
- Core container (singleton/instance binding, constructor DI)
- HTTP runtime (Request/Response, JSON + Redirect helpers)
- Kernel middleware pipeline (global + route middleware, terminable middleware)
- Router (GET/POST, matching, params, groups/prefixes/names)
- Controller resolver with method dependency injection
- Config loader (LoadConfiguration) + dot notation repository
- Env loader (.env)
- File logger
- Test suite + PHPStan clean