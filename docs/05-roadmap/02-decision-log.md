# Decision Log

Key architectural and product decisions for G8Connect, with rationale documented for future reference.

## Decisions

| # | Decision | Rationale | Phase |
|---|---|---|---|
| 1 | SQL Mode = GET only | Write operations handled by Simple/Guided CRUD; SQL mode value is complex reads (joins, aggregations, computed columns) | v0.4 |
| 2 | No direct Kong push | All deployments via G8Stack governance — G8Connect never touches Kong directly | All |
| 3 | File sources read-only | Files have no write target; CRUD only meaningful for live DB connections | v0.3 |
| 4 | Push via queue | G8Stack push must not block UI; failures need retry without user involvement | v0.5 |
| 5 | Credentials always encrypted | Data sources may connect to production DBs; no plaintext credential ever persists | All |
| 6 | PII excluded by default | User must opt-in to expose sensitive columns; safer default for governed environments | v0.1+ |
| 7 | Row cap and timeout hardcoded | SQL mode safety limits (1000 rows, 10s timeout) are not configurable — even for admins | v0.4 |
| 8 | Draft versioning, not overwriting | Regenerating a draft creates a new version to preserve history and enable comparison | v0.2 |
| 9 | Read-only DB connections enforced | Validated at connector level, not just application logic — defence in depth | v0.1+ |
| 10 | Audit logs never include credentials | Even encrypted values excluded from audit trail — credential references only | v0.1+ |
| 11 | Operations are per-table, not global | Each `ApiSpecTable` has its own `operations` (list, show, create, update, delete); allows fine-grained control per resource | v0.2.2 |
| 12 | Combined spec generation across all tables | Saving config for one table regenerates the full OpenAPI spec for ALL tables; prevents partial spec output | v0.2 |
| 13 | Scalar API Reference for spec rendering | CDN-based Scalar viewer in standalone page, embedded via iframe to avoid Livewire/Alpine conflicts | v0.2 |
| 14 | No Flux Pro features | Flux UI tabs (`flux:tab.group`) are Pro-only; use Alpine.js x-data/x-show tabs with URL deep linking instead | v0.2 |

## Architecture Decision Records

For significant decisions requiring detailed context, create ADRs in the format below.

### ADR-001: Governance-First Architecture

**Status**: Accepted

**Context**: G8Connect generates API specs from data sources. The question is whether to deploy
APIs directly or route through governance.

**Decision**: All generated specs are drafts. Nothing deploys without G8Stack approval.
G8Connect never communicates with Kong directly.

**Consequences**:

- Slower path to live API (requires approval step)
- Safer for regulated environments — every API exposure is reviewed
- Clear separation of concerns: G8Connect = creation, G8Stack = governance, Kong = runtime

### ADR-002: SQL Mode Limited to GET Endpoints

**Status**: Accepted

**Context**: Advanced users want to write custom SQL. Supporting INSERT/UPDATE/DELETE via SQL
introduces risk of data modification through API generation.

**Decision**: SQL mode only generates GET endpoints. Write operations use Simple/Guided CRUD
modes with proper field mapping and validation.

**Consequences**:

- SQL mode is focused on complex reads (joins, aggregations, CTEs)
- Write operations get proper validation and field-level control via Guided Mode
- Reduces attack surface — SQL mode connections are always read-only

## Next Steps

- [Implementation Roadmap](01-implementation-roadmap.md)
- [Architecture Overview](../03-architecture/README.md)
