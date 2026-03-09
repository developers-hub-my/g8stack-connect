# Architecture

This section covers code standards, patterns, conventions, and architectural decisions for maintaining a high-quality codebase.

## Overview

This project follows Laravel best practices with additional opinionated standards for code quality, testing, and maintainability.

## Table of Contents

1. [Code Quality](01-code-quality.md) - Linting, static analysis, testing standards
2. [Connector Architecture](02-connectors.md) - Data source connectors (DB + file)

## Key Principles

### Code Standards

- **PHP Version**: 8.3+ (configured in Rector)
- **Laravel Version**: 11+ (level set in Rector)
- **Formatting**: Laravel Pint with relaxed PHPDoc rules
- **Static Analysis**: PHPStan for type checking
- **Testing**: Pest with Architecture tests

### Design Patterns

- **Repository Pattern**: Not enforced, use Eloquent directly
- **Service Layer**: For complex business logic
- **Form Requests**: For validation rules
- **Resources**: For API transformations
- **Events/Listeners**: For decoupled actions

### Database Conventions

- **Primary Keys**: UUIDs (not auto-incrementing integers)
- **Base Model**: Extend `App\Models\Base` for auditing and media support
- **Migrations**: Use descriptive names with timestamps
- **Relationships**: Define in models with type hints

### Testing Strategy

- **Unit Tests**: For models, services, helpers
- **Feature Tests**: For controllers, API endpoints
- **Architecture Tests**: Enforce coding standards
- **Coverage**: Aim for 80%+ on critical paths

## Code Quality Tools

### Laravel Pint

Code formatter following Laravel conventions:

```bash
# Format all code
composer lint

# Check without fixing
./vendor/bin/pint --test
```

Configuration: `pint.json`

### PHPStan

Static analysis for type safety:

```bash
# Run analysis
composer analyse

# With JSON output
./vendor/bin/phpstan --error-format=json
```

Configuration: Level 5, custom rules in `phpstan.neon`

### Rector

Automated refactoring and upgrades:

```bash
# Run rector
./vendor/bin/rector

# Dry run
./vendor/bin/rector --dry-run
```

Configuration: `rector.php` (PHP 8.3, Laravel 11)

### Pest

Modern testing framework:

```bash
# Run tests
composer test

# With coverage
composer test-coverage

# Architecture tests only
./vendor/bin/pest --filter=Architecture
```

## File Organization

### Custom Stubs

Located in `stubs/`:

- `model.stub` - Extends `App\Models\Base`
- `migration.create.stub` - UUID primary keys
- `pest.stub` - Pest test syntax
- `policy.stub` - Standard policy methods

### Helper Functions

Organized by domain in `support/`:

- One file per domain (e.g., `user.php`, `media.php`)
- Auto-loaded via `support/helpers.php`
- No class-based helpers

### Scripts

Utility scripts in `bin/`:

- `install` - Project setup
- `deploy` - Production deployment
- `backup-app`, `backup-media` - Backup utilities
- `build-fe-assets` - Frontend compilation

## Architectural Decisions

### Why UUIDs?

- No sequential ID guessing
- Distributed system friendly
- Merge conflicts reduced
- Better for public APIs

### Why Extend Base Model?

- Centralized audit trail
- Consistent media handling
- Global scopes management
- Shared model behavior

### Why Pest Over PHPUnit?

- More readable syntax
- Better error messages
- Architecture testing built-in
- Modern PHP features

## Connector Architecture

G8Connect uses a connector pattern to support multiple data source types. All connectors implement the `DataSourceConnector` contract.

### Database Connectors (v0.1)

`AbstractDatabaseConnector` provides shared logic for SQL databases:

- **PostgresConnector** — PostgreSQL via `pgsql` driver
- **MySqlConnector** — MySQL via `mysql` driver
- **MssqlConnector** — SQL Server via `sqlsrv` driver
- **SqliteConnector** — SQLite via `sqlite` driver

### File Connectors (v0.3)

`AbstractFileConnector` provides shared logic for file-based sources:

- **CsvConnector** — CSV parsing via `league/csv`
- **JsonConnector** — JSON arrays and `{ "data": [...] }` wrapper format
- **ExcelConnector** — Multi-sheet `.xlsx` via `phpoffice/phpspreadsheet`

Key design decisions:

- File connectors are **read-only** — only list and show operations
- Column types are inferred by sampling up to 100 rows (integer, decimal, boolean, date, datetime, varchar)
- Excel sheets are treated as separate tables for multi-table spec generation
- `originalFilename` is passed through credentials for consistent table name derivation
- `ConnectorFactory::make()` returns `DataSourceConnector` (not `AbstractDatabaseConnector`) to support both types

### Spec Regeneration Service

`SpecRegenerationService` is the single source of truth for OpenAPI spec generation. It consolidates logic previously duplicated across:

- `ConnectWizard` (initial spec generation)
- `GuidedConfigWizard` (configuration save)
- `Manage` (resource save)

The service loads all `ApiSpecTable` records with their `ApiSpecField` entries, resolves `DataSourceSchema` for each table, and delegates to `GuidedSpecGenerator::generateForTables()` for the combined OpenAPI output.

## Cross-References

- **Development**: [Database](../02-development/01-database.md), [Testing Workflows](../02-development/README.md#testing)
- **Getting Started**: [Installation](../01-getting-started/01-installation.md)
- **Deployment**: [Production Standards](../04-deployment/01-deployment.md)

## Additional Resources

- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [PHPStan Documentation](https://phpstan.org)
- [Pest Documentation](https://pestphp.com)
- [Rector Documentation](https://getrector.com)
