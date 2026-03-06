# Implementation Roadmap

G8Connect accelerates API creation from any data source while enforcing governance
through G8Stack. Every phase outputs drafts — nothing deploys without G8Stack approval.

> **Core principle**: Speed up API creation, don't skip governance.

## Phase Summary

| Phase | Name | Scope | Target |
|---|---|---|---|
| v0.1 | Foundation | Auth + DB connections + Simple Mode | Internal / Dev |
| v0.2 | Guided Mode | Field selection, methods, filters | Beta |
| v0.3 | File Sources | CSV, JSON, Excel | Beta |
| v0.4 | Advanced Mode | SQL queries to GET endpoints | GA prep |
| v0.5 | G8Stack Push | Draft submission + status tracking | GA |
| v1.0 | GA Release | Polish, audit, PII hardening, docs | Public |

## Timeline

```mermaid
gantt
  title G8Connect Implementation Roadmap
  dateFormat YYYY-MM-DD
  section Foundation
    v0.1 Foundation         :2026-03-10, 2026-04-15
  section Core Modes
    v0.2 Guided Mode        :2026-04-16, 2026-05-15
    v0.3 File Sources       :2026-05-16, 2026-06-15
    v0.4 Advanced SQL Mode  :2026-06-16, 2026-07-15
  section Integration
    v0.5 G8Stack Push       :2026-07-16, 2026-08-15
  section Release
    v1.0 GA Release         :2026-08-16, 2026-09-30
```

## v0.1 — Foundation

**Goal**: Connect to a database, introspect schema, expose all fields as a CRUD draft automatically. Simple Mode only.

### Scope

- Project scaffolding (Laravel 12, Livewire, roles/permissions, Keycloak SSO skeleton)
- `DataSource` model — store connection config (type, encrypted credentials)
- Connectors: **PostgreSQL**, **MySQL**, **MSSQL**, **SQLite**
- Introspection: read tables, columns, data types
- Simple Mode wizard:
  - Step 1: Connect (type, credentials, validate)
  - Step 2: Introspect (list tables)
  - Step 3: Pick table — auto-generate full CRUD draft (no config)
  - Step 4: PII column scan (flag, exclude by default)
  - Step 5: Review generated OpenAPI spec (read-only preview)
- `ApiDraft` model — store generated spec, status (`pending`)
- Basic RBAC: `superadmin`, `administrator`, `developer`, `viewer`
- Audit log: every connect + introspect action recorded

### Deliverables

```text
app/Services/Connectors/PostgresConnector.php
app/Services/Connectors/MySqlConnector.php
app/Services/Connectors/MssqlConnector.php
app/Services/Connectors/SqliteConnector.php
app/Services/Introspectors/DatabaseIntrospector.php
app/Services/PiiDetectionService.php
app/Services/DraftGenerator/CrudDraftGenerator.php
app/Livewire/DataSource/ConnectWizard.php
app/Livewire/Draft/DraftReview.php
app/Models/DataSource.php
app/Models/ApiDraft.php
app/Models/ConnectionAudit.php
```

### Exit Criteria

- [ ] All four DB connectors connect and introspect successfully
- [ ] Simple Mode wizard generates valid OpenAPI 3.1 spec
- [ ] PII columns auto-flagged and excluded from draft
- [ ] Audit log records every connection attempt
- [ ] Credentials encrypted at rest, never logged
- [ ] RBAC enforced on all data source operations

### What's NOT in v0.1

- No G8Stack push (drafts stay local)
- No field selection (all fields auto-included, minus PII)
- No file sources
- No SQL mode

## v0.2 — Guided Mode

**Goal**: Give developers control — pick tables, choose which fields to expose, select HTTP methods, add basic filters.

### Scope

- Extend wizard with **Guided Mode** option after introspection
- Field configurator:
  - Toggle expose/exclude per column
  - Rename field (API name vs DB column name)
  - Mark as required / optional / read-only
- Method selector: choose which of `GET list`, `GET single`, `POST`, `PUT`, `PATCH`, `DELETE` to generate
- Basic filter config: allow filtering by selected columns (query params)
- Pagination config: page size, max limit
- `DraftField` model — store per-field config per draft
- Preview: show 5-row sample based on field selection
- Draft versioning — regenerate draft if config changes (new version, not overwrite)

### Deliverables

```text
app/Services/DraftGenerator/GuidedDraftGenerator.php
app/Livewire/DataSource/GuidedConfigWizard.php
app/Livewire/Draft/FieldConfigurator.php
app/Models/DraftField.php
app/Models/DraftVersion.php
```

### Exit Criteria

- [ ] Guided Mode wizard allows field-level configuration
- [ ] Method selection generates correct OpenAPI operations
- [ ] Filter and pagination config reflected in spec
- [ ] Draft versioning creates new version on regenerate
- [ ] Preview limited to 5 rows maximum

### What's NOT in v0.2

- No G8Stack push yet
- No file sources
- No SQL mode
- No relationship traversal (foreign keys to nested resources)

## v0.3 — File Sources

**Goal**: Upload CSV, JSON, or Excel files — get a read-only API draft from the data.

### Scope

- File connector: upload via UI, store via Spatie MediaLibrary
- Supported: **CSV**, **JSON**, **Excel (.xlsx)**
- File introspection:
  - CSV/Excel: detect headers, infer column types
  - JSON: detect top-level array structure, infer field types
- Auto-generate read-only draft (GET list + GET single only — no writes from files)
- Row limit enforced at draft level (configurable via Settings)
- File-sourced drafts labelled clearly — approvers in G8Stack can see source type
- Temp file cleanup after introspection (don't persist raw uploads long-term)

### Deliverables

```text
app/Services/Connectors/CsvConnector.php
app/Services/Connectors/JsonConnector.php
app/Services/Connectors/ExcelConnector.php
app/Services/Introspectors/FileIntrospector.php
app/Livewire/DataSource/FileUploadWizard.php
```

### Exit Criteria

- [ ] CSV, JSON, and Excel files upload and introspect correctly
- [ ] Column types inferred accurately from file content
- [ ] Generated drafts are read-only (GET endpoints only)
- [ ] Source type label included in draft metadata
- [ ] Temp files cleaned up after processing

### Notes

> File sources only support Simple Mode and Guided Mode field selection.
> SQL Mode does not apply to file sources.

## v0.4 — Advanced Mode (SQL to GET)

**Goal**: Developers write a SELECT query — it becomes a named GET endpoint with query parameters.

### Scope

- SQL editor in wizard (Advanced Mode)
- Query parser — validate only SELECT statements:
  - Block: `INSERT`, `UPDATE`, `DELETE`, `DROP`, `TRUNCATE`, `ALTER`
  - Block: access to `information_schema`, `pg_catalog`, `sys`, `mysql`
  - Allow: `SELECT`, `WITH` (CTE), `JOIN`, subqueries on app tables only
- Named query: developer sets endpoint name — `/api/{name}`
- Parameter binding: `?` or `:param` in SQL — becomes query param in OpenAPI spec
- Query dry-run: execute with LIMIT 5, show result shape (no data shown to user)
- Row cap: enforce max rows via Settings even on custom SQL
- PII scan on result columns (same service, same rules)
- Only generates `GET` endpoint — no writes

### Deliverables

```text
app/Services/SqlValidator.php
app/Services/DraftGenerator/SqlDraftGenerator.php
app/Livewire/DataSource/SqlQueryWizard.php
```

### Security Constraints (hardcoded, not configurable)

| Constraint | Value |
|---|---|
| Parser mode | Whitelist — reject anything not explicitly allowed |
| Connection | Always read-only (enforced at connector level) |
| Query timeout | 10 seconds max |
| Result cap | 1000 rows max regardless of query |

### Exit Criteria

- [ ] SQL validator blocks all non-SELECT statements
- [ ] System table access blocked (`information_schema`, `pg_catalog`, etc.)
- [ ] Named endpoints generate correct OpenAPI spec
- [ ] Parameter binding produces query parameters in spec
- [ ] Timeout and row cap enforced at query level
- [ ] PII scan applied to result columns

## v0.5 — G8Stack Push

**Goal**: Submit drafts to G8Stack governance workflow. Track approval status.

### Scope

- `G8StackService` — push OpenAPI draft via G8Stack API
- Push is **queued** (Laravel job) — never synchronous in request cycle
- Draft status tracking: `pending` to `pushed` to `approved` / `rejected` to `deployed`
- Webhook receiver: G8Stack posts back status updates
- UI: draft list shows current status, last pushed at, approved/rejected by
- Re-push on rejection: developer can revise config and resubmit
- G8Stack connection settings UI (Admin > Settings > G8Stack):
  - Endpoint URL
  - API token (encrypted via Spatie Settings)
  - Push enabled toggle

### Deliverables

```text
app/Services/G8StackService.php
app/Jobs/PushDraftToG8Stack.php
app/Http/Controllers/Webhook/G8StackWebhookController.php
app/Livewire/Draft/DraftList.php
app/Settings/G8StackSettings.php
```

### Draft Status Flow

```mermaid
stateDiagram-v2
    [*] --> pending: Draft generated
    pending --> pushed: Push to G8Stack
    pushed --> approved: G8Stack approves
    pushed --> rejected: G8Stack rejects
    rejected --> pending: Revise and regenerate
    approved --> deployed: Kong deployment
    deployed --> [*]
```

### Exit Criteria

- [ ] Drafts push to G8Stack via queued job
- [ ] Failed jobs retry 3 times with exponential backoff
- [ ] Webhook updates draft status in real-time
- [ ] UI shows current status for all drafts
- [ ] Push failures surface clearly — never silent fail
- [ ] Admin notified on final retry failure

## v1.0 — GA Release

**Goal**: Production-ready. Hardened security, full audit, documented, demo-ready for prospects.

### Scope

- Full audit trail UI (who connected, introspected, generated, pushed)
- PII detection improvements — configurable patterns per organisation
- Connection health check — periodic ping to verify data source still reachable
- Draft expiry — drafts older than X days prompt re-validation before push
- Multi-org support (basic) — data sources scoped to team/organisation
- Read-only connection enforcement validator — warn if account has write grants
- Rate limiting on introspection and preview endpoints
- Full test coverage (feature + unit, Pest)
- Docs: user guide, admin guide, G8Stack integration guide
- Demo seed data — realistic but fake data sources for prospects

### Hardening Checklist

- [ ] All credentials encrypted at rest
- [ ] No credential values in logs (tested)
- [ ] Preview max rows enforced at DB query level (not just UI)
- [ ] SQL validator test suite — known attack patterns blocked
- [ ] PII patterns reviewed against Malaysian data sensitivity context (NRIC, passport, bank)
- [ ] Audit log immutable (append-only, no soft deletes)
- [ ] Webhook endpoint signature verification (G8Stack signs payloads)

### Exit Criteria

- [ ] All hardening checklist items pass
- [ ] Full test suite with coverage target met
- [ ] User guide, admin guide, and integration guide complete
- [ ] Demo environment with seed data operational
- [ ] Multi-org data isolation verified

## Dependency Map

```mermaid
graph LR
    v01["v0.1 Foundation"] --> v02["v0.2 Guided Mode"]
    v01 --> v03["v0.3 File Sources"]
    v01 --> v04["v0.4 Advanced SQL"]
    v02 --> v03
    v04 --> v05["v0.5 G8Stack Push"]
    v03 --> v05
    v02 --> v05
    v05 --> v10["v1.0 GA Release"]
```

## Data Source Roadmap

| Phase | Sources |
|---|---|
| v0.1-v0.2 | PostgreSQL, MySQL, MSSQL, SQLite |
| v0.3 | CSV, JSON, Excel (.xlsx) |
| Post-v1.0 | MongoDB, Redis, XML, Parquet, REST/SOAP, S3/MinIO, Google Sheets, SFTP |

## Next Steps

- [Decision Log](02-decision-log.md)
- [Architecture Overview](../03-architecture/README.md)
