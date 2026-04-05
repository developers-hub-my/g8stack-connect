# Connector Architecture

G8Connect uses a connector pattern to abstract data source access. All connectors implement the `DataSourceConnector` contract, enabling uniform connect, introspect, and preview operations regardless of source type.

## Contract

```php
interface DataSourceConnector
{
    public function connect(array $credentials): ConnectionResult;
    public function introspect(): SchemaResult;
    public function preview(string $table, int $limit = 5): PreviewResult;
    public function isReadOnly(): bool;
}
```

## Connector Hierarchy

```text
DataSourceConnector (contract)
├── AbstractDatabaseConnector
│   ├── PostgresConnector
│   ├── MySqlConnector
│   ├── MssqlConnector
│   ├── SqliteConnector
│   └── OracleConnector
└── AbstractFileConnector
    ├── CsvConnector
    ├── JsonConnector
    └── ExcelConnector
```

## Database Connectors (v0.1)

`AbstractDatabaseConnector` provides shared logic for SQL databases:

- Dynamic PDO connection via Laravel's `DatabaseManager`
- Schema-aware introspection (filters tables by database/schema per driver)
- Read-only connection enforcement
- Audit logging on connect/introspect/preview

### Driver-Specific Schema Filtering

| Driver | `$table['schema']` | Filter By |
|---|---|---|
| MySQL | Database name | `getDatabaseName()` |
| MSSQL | Database name | `getDatabaseName()` |
| PostgreSQL | Schema name (e.g. `public`) | Connection `schema` config |
| SQLite | `main` | Hardcoded filter |
| Oracle | Owner/schema name | Connected username (case-insensitive) |

### OracleConnector

- Uses `yajra/laravel-oci8` (OCI8 extension, not PDO)
- Driver name: `oracle`
- Default port: `1521`, charset: `AL32UTF8`
- Supports both **Service Name** (preferred) and **SID** via the `service_name` credential field
- Schema filtering matches the connected user's schema (owner) case-insensitively
- `yajra/laravel-oci8` returns lowercase table/column names despite Oracle's native uppercase convention

## File Connectors (v0.3)

`AbstractFileConnector` provides shared logic for file-based data sources:

### Key Properties

| Property | Type | Description |
|---|---|---|
| `$filePath` | `?string` | Path to the loaded file |
| `$originalFilename` | `?string` | Original upload filename (for table name derivation) |
| `$parsedHeaders` | `array` | Column headers extracted from file |
| `$parsedRows` | `array` | Parsed data rows |

### Type Inference

Column types are inferred by sampling up to 100 rows. Detection order:

1. **Integer** — all non-null values are numeric with no decimal point
2. **Decimal** — all non-null values are numeric (with decimal)
3. **Boolean** — values match `true/false`, `yes/no`, `1/0`
4. **Date** — values match `YYYY-MM-DD` pattern
5. **Datetime** — values match `YYYY-MM-DD HH:MM:SS` pattern
6. **Varchar** — fallback for everything else

### CsvConnector

- Uses `league/csv` with header offset 0
- Single table per file (table name derived from filename)

### JsonConnector

- Supports two formats:
  - Top-level array: `[{...}, {...}]`
  - Wrapper format: `{ "data": [{...}, {...}] }`
- Nested array values are flattened to JSON strings for tabular display
- Single table per file

### ExcelConnector

- Uses `phpoffice/phpspreadsheet`
- **Multi-sheet support** — each sheet becomes a separate table
- Sheet names converted to snake_case for table names
- `selectSheet()` method switches the active sheet context
- `getColumnsForTable()` auto-switches sheets before reading columns

## ConnectorFactory

```php
ConnectorFactory::make(DataSourceType $type): DataSourceConnector
```

Returns the appropriate connector based on `DataSourceType`. The return type is `DataSourceConnector` (not `AbstractDatabaseConnector`) to support both database and file connectors.

## Spec Regeneration Service

`SpecRegenerationService` is the single source of truth for OpenAPI spec generation, consolidating logic from three Livewire components:

```php
class SpecRegenerationService
{
    public function regenerate(ApiSpec $apiSpec, array $overrides = []): array;
}
```

### Flow

1. Load all `ApiSpecTable` records with their `ApiSpecField` entries
2. Resolve `DataSourceSchema` for each table
3. Build per-table config (resource name, operations, fields, pagination)
4. Delegate to `GuidedSpecGenerator::generateForTables()` for combined OpenAPI output
5. Save generated spec to `ApiSpec.openapi_spec`

### Field Resolution

Fields are resolved in priority order:

1. **ApiSpecField records** — if the table has configured fields, use those
2. **Schema columns** — fallback to `DataSourceSchema.columns` with sensible defaults

## File Upload Lifecycle

```text
User uploads file (Livewire WithFileUploads)
    ↓
Temp file created in livewire-tmp/
    ↓
testFileUpload() validates and parses (captures original_filename)
    ↓
introspect() re-connects with original_filename for consistent table names
    ↓
generateFileSpec() stores file permanently → storage/app/data-sources/
    ↓
Temp file cleaned up by Livewire
```

> **Gotcha:** Livewire temp files are cleaned up between request cycles. Always capture
> `getClientOriginalName()` early. Use `filesize()` on the stored path, not `getSize()` on
> the temp upload object.

## Cross-References

- [Implementation Roadmap — v0.3](../05-roadmap/01-implementation-roadmap.md#v03--file-sources-)
- [Development — File Connectors](../02-development/11-file-connectors.md)
- [Architecture Overview](README.md)
