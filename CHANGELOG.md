# Changelog

All notable changes to G8Connect will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.0 - 2026-03-12

### G8Connect 1.0.0

Data Source to API in Minutes — connect any database or file, introspect the schema,
and auto-generate OpenAPI specs with governance built in.

#### Features

**Data Source Connectors**

- PostgreSQL, MySQL, MSSQL, SQLite (database)
- CSV, JSON, Excel with multi-sheet support (file)

**Wizard Modes**

- Simple Mode — pick table, auto-generate full CRUD spec
- Guided Mode — field selection, methods, filters, pagination per-table
- Advanced SQL Mode — write SELECT queries, generate named GET endpoints

**Dynamic API Runtime**

- Deployed specs become live CRUD endpoints at `/api/connect/{slug}`
- Multi-table grouped specs with nested resource endpoints
- Field & table remapping — clean API names, raw DB names never exposed
- Input validation with schema-derived rules on write operations
- Pagination, filtering, and sorting via query params

**OpenAPI Spec Generation**

- Valid OpenAPI 3.1 specs
- Scalar API Reference interactive viewer
- Immutable spec versioning — regenerate creates new version, never overwrites

**PII Detection**

- Auto-flag sensitive columns (password, IC number, SSN, credit card, etc.)
- Flagged columns excluded by default — explicit opt-in required

**API Security**

- API key authentication per spec with rate limiting (429 + Retry-After)
- Security headers (X-Content-Type-Options, X-Frame-Options) always present
- Custom response headers per spec
- RBAC with Spatie Permission (superadmin, administrator, developer, viewer)

**Security Hardening**

- Credentials encrypted at rest (encrypted:array cast)
- SQL whitelist parser — only SELECT/WITH allowed, 28 keywords blocked
- Read-only DB connections enforced at connector level
- Hardcoded query timeout (10s) and row cap (1000)
- Audit logging on every connection attempt

**Developer Experience**

- In-app documentation with sidebar navigation
- Keycloak SSO (primary) with local fallback for dev
- Docker services (MySQL, Redis, Mailpit, Meilisearch, MinIO)
- CI: Tests, Rector, Pint, Changelog workflows

## Unreleased
