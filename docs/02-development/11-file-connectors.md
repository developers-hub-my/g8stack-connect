# File Source Connectors

G8Connect supports CSV, JSON, and Excel files as data sources (v0.3). File sources generate **read-only** API specs (GET list + GET single only).

## Supported Formats

| Format | Package | Multi-Table | Notes |
|---|---|---|---|
| CSV | `league/csv` | No | Single table per file |
| JSON | Native PHP | No | Arrays or `{ "data": [...] }` wrapper |
| Excel | `phpoffice/phpspreadsheet` | Yes | Each sheet = separate table |

## Adding a File Data Source

The existing ConnectWizard handles file sources — no separate wizard. The flow:

1. **Step 1** — Enter name, select type (CSV/JSON/Excel)
2. **Step 2** — Upload file (max 50MB), test file loads correctly
3. **Step 3** — Select wizard mode (Simple/Guided)
4. **Step 4** — Select tables (single for CSV/JSON, multi-sheet for Excel)
5. **Step 5** — Preview schema columns with inferred types
6. **Step 6** — PII scan on all selected columns
7. **Step 7** — Generate read-only API spec

## DataSourceType Helpers

```php
DataSourceType::CSV->isFile();      // true
DataSourceType::MYSQL->isFile();    // false
DataSourceType::EXCEL->isDatabase(); // false
```

## Writing a New File Connector

Extend `AbstractFileConnector` and implement `parseFile()` and `getFileType()`:

```php
class XmlConnector extends AbstractFileConnector
{
    protected function getFileType(): string
    {
        return 'xml';
    }

    protected function parseFile(string $filePath): void
    {
        // Parse the file and populate:
        // $this->parsedHeaders = ['col1', 'col2', ...];
        // $this->parsedRows = [['col1' => 'val', 'col2' => 'val'], ...];
    }
}
```

Then register it in `ConnectorFactory`:

```php
DataSourceType::XML => new XmlConnector,
```

And add the case to `DataSourceType` enum with `isFile()` returning `true`.

## Column Type Inference

Types are inferred by sampling up to 100 rows:

| Inferred Type | Detection Rule |
|---|---|
| `integer` | All non-null values are numeric, no decimal point |
| `decimal` | All non-null values are numeric |
| `boolean` | Values are `true/false`, `yes/no`, `1/0` |
| `date` | Matches `YYYY-MM-DD` |
| `datetime` | Matches `YYYY-MM-DD HH:MM:SS` |
| `varchar` | Fallback |

## Test Data

Dummy data files are available in `storage/data/dummy/` for manual testing:

| File | Description |
|---|---|
| `employees.csv` | 15 rows, 8 columns (int, decimal, date, boolean) |
| `products.csv` | 12 rows, Malaysian products with SKU |
| `sales_transactions.csv` | 15 rows with datetime and status enums |
| `employees.json` | 10 rows, array format with nested skills |
| `api_responses.json` | 8 rows, `{ "data": [...] }` wrapper (IoT sensors) |
| `students.json` | 8 rows with PII fields (ic_number, phone) |
| `company_data.xlsx` | 3 sheets: Employees, Departments, Projects |
| `inventory_management.xlsx` | 2 sheets: Inventory, Suppliers |

## Common Gotchas

- Livewire temp files are cleaned up between steps — capture `getClientOriginalName()` early
- The `local` disk root is `storage/app/`, not `storage/app/private/`
- `deriveTableName()` uses `originalFilename` for consistency across requests
- Excel `getColumnsForTable()` must call `selectSheet()` first to switch context
- File sources always get read-only operations (list + show); create/update/delete are disabled

## Cross-References

- [Connector Architecture](../03-architecture/02-connectors.md) — full connector hierarchy and design
- [Implementation Roadmap — v0.3](../05-roadmap/01-implementation-roadmap.md#v03--file-sources-)
