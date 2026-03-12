# Docker

The project uses two Docker Compose files: one for core app
services and one for test databases used in connector testing.

## Core Services (`docker-compose.yml`)

| Service     | Port(s)        | Description              |
| ----------- | -------------- | ------------------------ |
| MySQL       | 3306           | Application database     |
| Redis       | 6379           | Cache & session store    |
| Mailpit     | 1025, 8025     | Mail testing (SMTP + UI) |
| Meilisearch | 7700           | Full-text search engine  |
| MinIO       | 9000, 9001     | S3-compatible storage    |

```bash
# Start core services
docker compose up -d

# Stop core services
docker compose down

# View logs
docker compose logs -f

# View logs for a specific service
docker compose logs -f mysql
```

## Test Databases (`docker-compose.databases.yml`)

These databases are used for testing G8Connect's data source
connectors. Each is pre-seeded with sample data (employees,
departments, projects tables).

| Service    | Port | Credentials                  |
| ---------- | ---- | ---------------------------- |
| PostgreSQL | 5432 | db=g8test user=g8test        |
| MSSQL      | 1433 | db=g8test user=sa            |
| Oracle     | 1521 | db=FREEPDB1 user=g8test      |

Images used:

- PostgreSQL: `postgres:16-alpine`
- MSSQL: `mcr.microsoft.com/mssql/server:2022-latest`
- Oracle: `gvenzl/oracle-free:slim`

> MySQL connector testing uses the app's own MySQL
> from `docker-compose.yml` (port 3306).

```bash
# Start test databases
bin/databases-up

# Stop test databases
bin/databases-down

# Reset with fresh seed data (removes volumes)
bin/databases-reset
```

### Seed Data

All test databases are seeded with identical sample schemas located in `docker/seeds/`:

- `docker/seeds/postgres/01-init.sql`
- `docker/seeds/mysql/01-init.sql`
- `docker/seeds/mssql/01-init.sql` (with `docker/seeds/mssql/setup.sh` init wrapper)
- `docker/seeds/oracle/01-init.sql`

Each seed includes an `ic_number` column for PII detection testing.

### Platform Notes

- **MSSQL on Apple Silicon**: Runs via Rosetta emulation
  (`platform: linux/amd64`). Functional but slower.
- **Oracle startup**: Takes 60-90 seconds to initialize on
  first run. The healthcheck `start_period` accounts for this.
- **Port conflicts**: All test database ports are configurable
  via `.env` (`G8_PG_PORT`, `G8_MSSQL_PORT`, `G8_ORACLE_PORT`).

## Access Points

- **MySQL**: `localhost:3306` (root/root or configured user)
- **Redis**: `localhost:6379`
- **Mailpit UI**: `http://localhost:8025`
- **MinIO Console**: `http://localhost:9001` (minioadmin/minioadmin)
- **Meilisearch**: `http://localhost:7700`

## Environment Variables

Configure Docker services via `.env`:

```env
# MySQL (app database)
DB_PORT=3306
DB_ROOT_PASSWORD=root
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=secret

# Redis
REDIS_PORT=6379

# Mailpit
MAIL_PORT=1025
MAILPIT_UI_PORT=8025

# Meilisearch
MEILI_PORT=7700
MEILI_MASTER_KEY=masterKey

# MinIO
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin

# Test Database Ports
G8_PG_PORT=5432
G8_MSSQL_PORT=1433
G8_ORACLE_PORT=1521
```

## Connecting Laravel to Docker Services

Update your `.env` to connect to Docker services:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=secret

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
```
