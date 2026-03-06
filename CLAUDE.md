# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**G8Connect** is a data-source-to-API platform that accelerates API development by auto-generating
OpenAPI specs from any connected data source. Generated specs are submitted to
[G8Stack](https://g8stack.dev) for governance approval before deployment to Kong API Gateway.

> **Core Principle**: Speed up creation, don't skip governance. G8Connect generates specs — never
> deploys directly. All exposure still requires approval through G8Stack's governance workflow.

- **Framework**: Laravel 12+ with PHP 8.4+
- **Frontend**: Livewire 4 + TailwindCSS v4 + Alpine.js
- **Testing**: Pest PHP (not PHPUnit syntax)
- **Database**: MySQL with UUID primary keys
- **Auth**: Keycloak SSO (primary), local fallback for dev

## Product Context

### What G8Connect Does

```
Data Source
    ↓
[Connect & Introspect]    ← validates credentials, reads schema
    ↓
[Preview & Configure]     ← user selects fields, methods, filters
    ↓
[PII Detection]           ← flags sensitive columns automatically
    ↓
[Generate OpenAPI Draft]  ← outputs spec, never deploys
    ↓
[Push to G8Stack]         ← enters governance/approval workflow
    ↓
[Kong Deployment]         ← only after approval
```

### Target Users

Developers, data scientists, and backend engineers who need to expose data via API without
writing boilerplate — but within a governed, approved deployment process.

### Wizard Modes

G8Connect exposes three modes in the wizard — all produce the same OpenAPI draft output,
complexity is abstracted per user type.

| Mode | User | How |
|---|---|---|
| **Simple** | Non-technical | Pick table → auto CRUD draft, zero config |
| **Guided** | Backend developer | Pick table → choose fields, methods, filters, pagination |
| **Advanced** | Data scientist | Write SELECT SQL → becomes named GET endpoint |

> **Decision**: Advanced (SQL) mode generates **GET endpoints only**. No INSERT/UPDATE via SQL.
> Write operations are handled by Simple/Guided CRUD modes which have proper field mapping
> and validation. SQL mode's value is **complex reads** — joins, aggregations, computed columns.

### SQL Mode Constraints (hardcoded, non-configurable)

- **Whitelist parser** — only `SELECT` and `WITH` (CTE) allowed
- **Blocked statements**: `INSERT`, `UPDATE`, `DELETE`, `DROP`, `TRUNCATE`, `ALTER`
- **Blocked tables**: `information_schema`, `pg_catalog`, `sys`, `mysql`
- **Query timeout**: 10 seconds maximum
- **Row cap**: 1000 rows maximum regardless of query
- **Always read-only connection** — enforced at connector level, not just validation
- **Named endpoints**: developer sets name → `/api/{name}`
- **Parameter binding**: `:param` or `?` in SQL → OpenAPI query parameter

> **Gotcha:** Removing the row cap or timeout from SQL mode is never allowed, even for admins.
> These are hardcoded safety limits, not Settings values.

### Implementation Phases

| Phase | Name | Key Deliverable | Status |
|---|---|---|---|
| v0.1 | Foundation | Auth + DB connectors + Simple Mode (local specs only) | Done |
| v0.2 | Guided Mode | Field selection, methods, filters, spec versioning | Done |
| v0.2.1 | Dynamic Runtime | Serve deployed specs as live CRUD endpoints | Done |
| v0.2.2 | Runtime Hardening | Validation, security, headers, grouped specs | Planned |
| v0.3 | File Sources | CSV, JSON, Excel → read-only specs | Planned |
| v0.4 | Advanced Mode | SELECT SQL → named GET endpoint specs | Planned |
| v0.5 | G8Stack Push | Spec submission, webhook, status tracking | Planned |
| v1.0 | GA Release | Hardening, audit UI, multi-org, full docs | Planned |

> **Rule**: Never build ahead of phase. If a feature belongs to v0.3, don't implement it in v0.1
> even if it seems easy. Phases exist to keep scope controlled for a solo founder.

### Data Source Roadmap by Phase

**v0.1–v0.2 (DB sources)**
- PostgreSQL, MySQL, MSSQL, SQLite

**v0.3 (File sources)**
- CSV, JSON, Excel (.xlsx)

**Future (post-v1.0)**
- MongoDB, Redis
- XML, Parquet
- REST/SOAP endpoint wrapping
- S3/MinIO, Google Sheets, SFTP

---

## Architecture & Key Concepts

### Models - CRITICAL

**ALL models MUST extend `App\Models\Base`** instead of `Illuminate\Database\Eloquent\Model`.

```php
namespace App\Models;

use App\Models\Base as Model;

class DataSource extends Model
{
    // UUID primary keys — automatic
    // Auditing — automatic (critical for data source access trail)
    // User tracking (created_by, updated_by) — automatic
}
```

The Base model provides:
- UUID primary keys (`InteractsWithUuid`)
- Auditing via owen-it/laravel-auditing
- Media attachments via Spatie Media Library
- User tracking (created_by, updated_by)
- Resource route helpers

### Core Domain Models

| Model | Purpose | Phase |
|---|---|---|
| `DataSource` | Connection config (type, credentials ref, status) | v0.1 |
| `DataSourceSchema` | Introspected schema snapshot (tables, columns, types) | v0.1 |
| `ApiSpec` | Generated OpenAPI spec (with slug for runtime endpoint) | v0.1 |
| `ConnectionAudit` | Every connect/introspect/preview action logged | v0.1 |
| `ApiSpecField` | Per-field config (exposed, excluded, renamed, PII-flagged) | v0.2 |
| `ApiSpecVersion` | Versioned specs — regenerate creates new version, never overwrites | v0.2 |

### Credential Handling — CRITICAL

**Never store raw credentials.** All data source credentials must be:
- Encrypted at rest using Laravel's `encrypted:array` cast on the model
- Enforced as **read-only service accounts** (validated on connection)
- Never logged, dumped, or included in error messages
- Never persisted beyond the session if user opts for "session only" mode

```php
// DO — use encrypted:array cast on the model (handles encrypt/decrypt automatically)
protected function casts(): array
{
    return ['credentials' => 'encrypted:array'];
}

// DON'T — never manually encrypt() when the cast already handles it (causes double-encryption)
$dataSource->credentials = encrypt($validated['credentials']);
```

> **Gotcha:** Using `encrypt()` manually when the model already has `encrypted:array` cast
> causes double-encryption. The cast handles encryption transparently — just assign the plain array.

> **Gotcha:** Audit logs must NEVER include credential values, even encrypted ones.
> Log connection attempts with user, timestamp, source type, and outcome only.

### PII Detection

Auto-flag columns matching known sensitive patterns **before** generating any API draft.
Flagged columns are excluded by default — user must explicitly opt-in to expose them.

```php
// Patterns to flag (add to config/pii.php)
$sensitivePatterns = [
    'password', 'secret', 'token', 'ic_number', 'nric', 'mykad',
    'passport', 'ssn', 'credit_card', 'card_number', 'cvv',
    'bank_account', 'pin', 'private_key', 'api_key',
];
```

### Dynamic API Runtime

G8Connect serves deployed specs as live CRUD endpoints. When an `ApiSpec` status is `DEPLOYED`,
it becomes accessible as a real API under `/api/connect/{slug}`.

```
GET    /api/connect/{slug}       → list (paginated, filterable, sortable)
POST   /api/connect/{slug}       → create
GET    /api/connect/{slug}/{id}  → show
PUT    /api/connect/{slug}/{id}  → update
DELETE /api/connect/{slug}/{id}  → delete
```

**Key components:**
- `DynamicApiController` — resolves spec by slug, enforces allowed methods
- `ApiQueryService` — builds runtime queries against the spec's data source
- `ApiResponseTransformer` — maps column names to display names, filters input

> **Preference:** No version numbers in URI paths. API versioning is handled via headers
> (`X-API-Version` / `Accept` header) using `cleaniquecoders/laravel-api-version` middleware.
> Dynamic endpoints always use `/api/connect/` prefix.

> **Rule:** Never expose raw database table or column names in API responses. All table names
> must be remapped to clean resource names (e.g. `tbl_emp_records` → `employees`) and all
> columns to domain-relevant field names (e.g. `usr_email_addr` → `email`). This applies to
> response payloads, error messages, validation messages, and URL paths. Internal DB structure
> should never leak through the API surface.

### G8Stack Integration

G8Connect pushes generated OpenAPI specs to G8Stack via API. The integration must:
- Use a configurable G8Stack endpoint (per environment)
- Authenticate via API token (stored in Spatie Settings, not `.env`)
- Submit specs with metadata: source type, generated by, timestamp
- Handle push failures gracefully — queue with retry, never silent fail

```php
// app/Services/G8StackService.php
class G8StackService
{
    public function pushSpec(ApiSpec $spec): PushResult;
    public function getApprovalStatus(string $specId): SpecStatus;
}
```

### Authorization

Use **Spatie Laravel Permission** with Keycloak as the identity provider.
RBAC controls what each user can do within G8Connect:

| Permission | Description |
|---|---|
| `datasource.connect` | Connect a new data source |
| `datasource.introspect` | Read schema from connected source |
| `datasource.preview` | View sample data |
| `spec.generate` | Generate OpenAPI spec |
| `spec.push` | Push spec to G8Stack |
| `spec.view` | View generated specs |

Default roles: `superadmin`, `administrator`, `developer`, `viewer`

> **Gotcha:** `datasource.preview` exposes actual data rows. Restrict this to trusted roles.
> Preview should show max 5 rows, never full table dumps.

### Application Settings (Spatie Laravel Settings)

```php
// app/Settings/G8StackSettings.php
class G8StackSettings extends Settings
{
    public string $endpoint;
    public string $api_token;
    public bool $push_enabled;
    public static function group(): string { return 'g8stack'; }
}

// app/Settings/ConnectionSettings.php
class ConnectionSettings extends Settings
{
    public int $max_preview_rows;      // default: 5
    public int $connection_timeout;   // default: 30
    public bool $enforce_readonly;    // default: true
    public static function group(): string { return 'connection'; }
}
```

**What stays in `.env`**: `APP_ENV`, `APP_DEBUG`, DB credentials, Redis, Keycloak client secret.
**What goes in Settings**: G8Stack endpoint, API tokens, preview limits, feature flags.

---

## Common Commands

```bash
# Development
composer dev              # Start server, queue, logs, and Vite concurrently
npm run dev               # Vite dev server with HMR
npm run build             # Build production assets

# Testing
composer test             # Run all tests
composer test-arch        # Run architecture tests only
composer test-coverage    # Run tests with coverage

# Code Quality
composer format           # Format code with Laravel Pint
composer analyse          # Run PHPStan static analysis
composer rector           # Run Rector for automated refactoring
composer lint             # Check PHP syntax

# Database
php artisan migrate       # Run migrations
php artisan reload:db     # Drop, migrate, and seed (fresh start)
```

---

## Database Conventions

- **Primary keys**: Always UUID (`$table->uuid('id')->primary()`)
- **Soft deletes**: Use for all user-facing models
- **Column naming**: snake_case
- **Credentials columns**: Always cast with `encrypted:array` cast (not manual `encrypt()`)

---

## Enums

Use enums for all status/type fields. Place in `app/Enums/`:

```php
namespace App\Enums;

use CleaniqueCoders\Traitify\Contracts\Enum as Contract;
use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;

enum DataSourceType: string implements Contract
{
    use InteractsWithEnum;

    case POSTGRESQL = 'postgresql';
    case MYSQL = 'mysql';
    case MSSQL = 'mssql';
    case SQLITE = 'sqlite';
    case CSV = 'csv';
    case JSON = 'json';
    case EXCEL = 'excel';
}

enum DraftStatus: string implements Contract
{
    use InteractsWithEnum;

    case PENDING = 'pending';       // Generated, not yet pushed
    case PUSHED = 'pushed';         // Sent to G8Stack
    case APPROVED = 'approved';     // Approved in G8Stack
    case REJECTED = 'rejected';     // Rejected in G8Stack
    case DEPLOYED = 'deployed';     // Live on Kong
}

enum WizardMode: string implements Contract
{
    use InteractsWithEnum;

    case SIMPLE = 'simple';       // v0.1 — pick table, auto CRUD
    case GUIDED = 'guided';       // v0.2 — field selection, methods, filters
    case ADVANCED = 'advanced';   // v0.4 — SQL query → GET endpoint
}
```

---

## Testing with Pest

Use Pest syntax (not PHPUnit):

```php
it('flags pii columns before generating draft', function () {
    $schema = DataSourceSchema::factory()->withColumns([
        'id', 'name', 'ic_number', 'email', 'password'
    ])->create();

    $result = app(PiiDetectionService::class)->scan($schema);

    expect($result->flagged)->toContain('ic_number', 'password')
        ->and($result->safe)->toContain('id', 'name', 'email');
});

it('never stores raw credentials', function () {
    $dataSource = DataSource::factory()->create([
        'credentials' => encrypt(['password' => 'secret']),
    ]);

    expect($dataSource->getRawOriginal('credentials'))
        ->not->toContain('secret');
});
```

---

## File Organization

```text
app/
├── Concerns/           # Traits (InteractsWithLivewireAlert, etc.)
├── Enums/              # DataSourceType, SpecStatus, ConnectionStatus, WizardMode
├── Http/Controllers/Api/V1/  # Dynamic API runtime controller
├── Livewire/           # Livewire components
│   ├── DataSource/     # Connect, Introspect, Preview wizards
│   ├── ApiSpec/        # Spec review, configure, versions
│   └── Settings/       # G8Stack connection settings
├── Models/             # Eloquent models (extend Base)
├── Policies/           # Authorization policies
├── Services/
│   ├── ApiRuntime/     # Dynamic API query + response transformer
│   ├── Connectors/     # Per-source connection adapters (v0.1: DB, v0.3: files)
│   ├── Introspectors/  # Schema introspection per source type
│   ├── PiiDetection/   # Column PII scanning (v0.1)
│   ├── SqlValidator.php        # SELECT whitelist parser (v0.4)
│   ├── SpecGenerator/          # OpenAPI spec generation
│   │   ├── CrudSpecGenerator.php       # Simple Mode (v0.1)
│   │   ├── GuidedSpecGenerator.php     # Guided Mode (v0.2)
│   │   └── SqlSpecGenerator.php        # Advanced Mode (v0.4)
│   ├── SpecVersioning/         # Immutable spec version management
│   └── G8StackService.php     # Push specs to G8Stack (v0.5)
support/                # Helper functions
routes/web/             # Modular web routes
routes/api.php          # Dynamic API runtime routes (/api/connect/)
stubs/                  # Custom Artisan stubs
```

### Connector Pattern

Each data source type gets its own connector implementing a shared contract:

```php
// app/Contracts/DataSourceConnector.php
interface DataSourceConnector
{
    public function connect(array $credentials): ConnectionResult;
    public function introspect(): SchemaResult;
    public function preview(string $table, int $limit = 5): PreviewResult;
    public function isReadOnly(): bool;
}

// app/Services/Connectors/PostgresConnector.php
class PostgresConnector implements DataSourceConnector { ... }

// app/Services/Connectors/CsvConnector.php
class CsvConnector implements DataSourceConnector { ... }
```

> **Gotcha:** `Schema::getTables()` returns tables from all schemas/databases the user can access.
> Each driver uses `$table['schema']` differently:
> - **MySQL/MSSQL**: `schema` = database name → filter by `getDatabaseName()`
> - **PostgreSQL**: `schema` = schema name (e.g. `public`) → filter by connection `schema` config
> - **SQLite**: `schema` = `main` → hardcode filter
> This applies to both `AbstractDatabaseConnector::introspect()` and `DatabaseIntrospector::getTables()`.

---

## Livewire Patterns

> **Gotcha:** Livewire 4 does not support the `rules()` method for dynamic validation.
> Calling `$this->validate()` without rules throws `MissingRulesException`.
> Always pass rules inline: `$this->validate($rules)`.

### Alerts and Confirmations

```php
use App\Concerns\InteractsWithLivewireAlert;
use App\Concerns\InteractsWithLivewireConfirm;

class ConnectDataSource extends Component
{
    use InteractsWithLivewireAlert;
    use InteractsWithLivewireConfirm;

    public function connect()
    {
        // ... connection logic
        $this->alert('Success', 'Data source connected.');
    }
}
```

---

## Important Conventions

### DO

- Extend `App\Models\Base` for all models
- Use UUID primary keys
- Use enums for status/type fields (especially `DataSourceType`, `DraftStatus`)
- Use Pest syntax for tests
- Use policies for authorization
- Use Form Requests for validation
- Encrypt all credential data before persisting
- Enforce read-only DB connections for introspection
- Flag PII columns before any draft generation
- Limit data preview to max 5 rows
- Audit every data source connection attempt
- Queue G8Stack push jobs — never synchronous in request cycle
- Remap table names to clean resource names and column names to domain-relevant field names
- Auto-suggest clean names in Simple Mode (strip prefixes like `tbl_`, `tb_`, pluralise)
- Default to read-only for dynamic API — write operations (C, U, D) must be explicitly opted-in per resource

### DON'T

- Extend `Illuminate\Database\Eloquent\Model` directly
- Use auto-increment IDs
- Store raw credentials — always encrypt
- Log credential values, even encrypted
- Allow preview to return full table data
- Push directly to Kong — always via G8Stack governance
- Use `url()` helper — use `route()` instead
- Use `dd()`, `dump()` in production code
- Use raw SQL — use Eloquent
- Use PHPUnit syntax — use Pest
- Write to `.env` at runtime — use Spatie Settings
- Allow SQL mode to generate anything other than `GET` endpoints
- Make SQL row cap or timeout configurable — these are hardcoded safety limits
- Build features ahead of their designated phase — scope discipline is critical
- Put API version numbers in URI paths — use header-based versioning instead
- Expose raw database table or column names in API responses, errors, or URLs

---

## Packages

### Core

- **spatie/laravel-permission**: Roles and permissions
- **spatie/laravel-settings**: Application settings stored in database
- **spatie/laravel-medialibrary**: File/media management (file-based sources)
- **owen-it/laravel-auditing**: Audit trail (critical — every connection logged)
- **cleaniquecoders/traitify**: Common traits and contracts
- **cleaniquecoders/laravel-api-version**: Header-based API versioning (not URI-based)

### Development

- **laravel/telescope**: Debugging (access via /telescope)
- **laravel/horizon**: Queue monitoring (access via /horizon)
- **barryvdh/laravel-debugbar**: Debug toolbar

### Frontend

- **livewire/livewire**: Reactive components
- **livewire/flux**: UI components
- **mallardduck/blade-lucide-icons**: Icons via `@svg('lucide-icon-name')`

---

## Docker Services

| Service     | Port(s)        | Description              |
| ----------- | -------------- | ------------------------ |
| MySQL       | 3306           | Database server          |
| Redis       | 6379           | Cache & session store    |
| Mailpit     | 1025, 8025     | Mail testing (SMTP + UI) |
| Meilisearch | 7700           | Full-text search engine  |
| MinIO       | 9000, 9001     | S3-compatible storage    |

```bash
docker compose up -d
docker compose down
docker compose logs -f
```

Access points:
- **Mailpit UI**: http://localhost:8025
- **MinIO Console**: http://localhost:9001 (minioadmin/minioadmin)
- **Meilisearch**: http://localhost:7700

---

## Environment Variables

```env
# Superadmin (seeded on fresh install)
SUPERADMIN_NAME="Admin"
SUPERADMIN_EMAIL="admin@example.com"
SUPERADMIN_PASSWORD=password

# Features
ACCESS_CONTROL_ENABLED=true
TELESCOPE_ENABLED=true

# G8Stack Integration (via Spatie Settings in DB — not here)
# Configure at Admin > Settings > G8Stack after first run

# Keycloak
KEYCLOAK_BASE_URL=
KEYCLOAK_REALM=
KEYCLOAK_CLIENT_ID=
KEYCLOAK_CLIENT_SECRET=

# Docker Services
DB_ROOT_PASSWORD=root
MAILPIT_UI_PORT=8025
MEILI_MASTER_KEY=masterKey
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin
```

---

## Quick Reference

```bash
php artisan make:model DataSource          # Extends Base automatically
php artisan make:policy DataSourcePolicy --model=DataSource
php artisan make:test DataSourceTest --pest

bin/deploy -b main                         # Deploy specific branch
bin/backup-app                             # Backup application
```

---

## Claude Self-Update Practice — CRITICAL

This file is a **living document**. Claude must update `CLAUDE.md` whenever:

1. **User corrects a mistake** — e.g., "jangan guna MySQL, kita pakai PostgreSQL"
2. **User expresses a preference** — e.g., "aku tak suka pattern ni, guna cara lain"
3. **A better pattern is discovered** during implementation
4. **A gotcha or edge case is found** that could cause future mistakes
5. **A new data source type is added** — update phases and enums

### How to Update

1. Apply the fix to the current task
2. Immediately update the relevant section in `CLAUDE.md`
3. If it's a DO/DON'T, add to the **DO / DON'T** section
4. If it's architectural, update the relevant architecture section
5. If it's a new gotcha, add under the relevant section with a `> **Gotcha:**` callout

### Format for Gotchas

```markdown
> **Gotcha:** PostgreSQL connections must use a dedicated read-only service account.
> Validate `pg_roles.rolcanlogin = true` and no write grants on connect.
```

### What NOT to Record

- One-off task-specific decisions that don't affect future work
- Things already covered by Laravel or package documentation
- Preferences already obvious from existing conventions

> **Rule**: When in doubt — record it. A slightly redundant note is better than repeating a mistake.
