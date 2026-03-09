# Advanced SQL Mode

Advanced SQL Mode (v0.4) lets developers write custom SELECT queries that become named GET
endpoints in the generated OpenAPI spec. This is ideal for complex reads — joins, aggregations,
computed columns — that go beyond simple CRUD.

## Architecture

```text
ConnectWizard (Livewire)
    ↓
SqlValidator          → whitelist parser, blocks writes & system tables
    ↓
SqlQueryExecutor      → dry-run (LIMIT 1) to detect result columns
    ↓
SqlSpecGenerator      → generates OpenAPI 3.1 GET endpoints from result shape
    ↓
ApiSpecTable          → each query stored as a table entry with isSqlQuery()
    ↓
DynamicApiController  → resolves SQL tables, delegates to SqlQueryExecutor
```

## Key Components

### SqlValidator

Location: `app/Services/SqlValidator.php`

Whitelist parser that enforces:

- Only `SELECT` and `WITH` (CTE) allowed
- Blocks: INSERT, UPDATE, DELETE, DROP, TRUNCATE, ALTER, CREATE, GRANT, REVOKE, etc.
- Blocks system tables: information_schema, pg_catalog, sys, mysql, sqlite_master
- Max query length: 10,000 characters
- Strips comments and string literals before validation
- Extracts both `:named` and `?` positional parameters

### SqlQueryExecutor

Location: `app/Services/ApiRuntime/SqlQueryExecutor.php`

Executes SQL queries at runtime with hardcoded safety limits:

- **10-second timeout** — set per-driver (MySQL MAX_EXECUTION_TIME, PG statement_timeout,
  MSSQL LOCK_TIMEOUT)
- **1,000 row cap** — wraps query in `SELECT * FROM (...) AS _capped LIMIT 1000`
- **Read-only** — enforced at connector level
- `execute(ApiSpec, ApiSpecTable, Request)` — full execution with parameter binding
- `dryRun(ApiSpec, string $sql)` — LIMIT 1 to detect column names and types

### SqlSpecGenerator

Location: `app/Services/SpecGenerator/SqlSpecGenerator.php`

- `generate()` — single query → single endpoint spec
- `generateForTables()` — multiple queries → combined spec with merged paths and schemas
- PII columns automatically excluded via PiiScanner
- User-defined parameters are `required: true`, pagination params are `required: false`
- Response schemas include 200, 400, 408 (timeout), 422 (execution error)

## Multi-Endpoint Support

Each SQL query is stored as an `ApiSpecTable` entry with:

| Column | Purpose |
|---|---|
| `sql_query` | The SELECT statement |
| `sql_parameters` | Extracted parameter names |
| `result_columns` | Column names and types from dry-run |
| `resource_name` | Endpoint name (URL segment) |
| `table_name` | `_sql_{endpoint_name}` (internal reference) |

The `isSqlQuery()` method on `ApiSpecTable` checks `!empty($this->sql_query)` to distinguish
SQL entries from regular table entries.

## Runtime Flow

```text
GET /api/connect/{slug}/{endpoint-name}?param=value
    ↓
DynamicApiController::index()
    ↓
resolveTable() → finds ApiSpecTable by resource_name
    ↓
$table->isSqlQuery() → true
    ↓
executeSqlEndpoint() → SqlQueryExecutor::execute()
    ↓
validate → bind params → connect → set timeout → apply row cap → execute → paginate
```

## Wizard UI

Step 5 of the ConnectWizard shows the multi-query SQL editor:

- Tabbed interface for multiple queries
- Each tab shows endpoint name and validation status (green/red dot)
- "Add Query" button to add more endpoints
- Per-query validation with dry-run
- All queries must be validated before proceeding
- `wire:key` on editor section ensures proper Livewire re-rendering on tab switch

## Testing

Tests are located in:

- `tests/Feature/Services/SqlValidatorTest.php` — 29 tests covering all blocked patterns
- `tests/Feature/Services/SqlSpecGeneratorTest.php` — 6 tests for spec generation

Run SQL-related tests:

```bash
php artisan test --filter=Sql
```
