<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\SqlValidationResult;

class SqlValidator
{
    /**
     * Blocked SQL statements — only SELECT and WITH (CTE) are allowed.
     */
    private const array BLOCKED_STATEMENTS = [
        'INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'ALTER',
        'CREATE', 'GRANT', 'REVOKE', 'EXEC', 'EXECUTE', 'CALL',
        'MERGE', 'REPLACE', 'LOAD', 'COPY', 'VACUUM', 'REINDEX',
        'CLUSTER', 'COMMENT', 'LOCK', 'UNLOCK', 'SET', 'RESET',
        'SHOW', 'DESCRIBE', 'EXPLAIN', 'USE', 'BEGIN', 'COMMIT',
        'ROLLBACK', 'SAVEPOINT',
    ];

    /**
     * Blocked system tables/schemas — never allow access.
     */
    private const array BLOCKED_TABLES = [
        'information_schema',
        'pg_catalog',
        'pg_toast',
        'pg_temp',
        'sys',
        'mysql',
        'performance_schema',
        'sqlite_master',
        'sqlite_temp_master',
    ];

    /**
     * Maximum query length in characters.
     */
    private const int MAX_QUERY_LENGTH = 10_000;

    public function validate(string $sql): SqlValidationResult
    {
        $sql = trim($sql);
        $errors = [];

        if ($sql === '') {
            return new SqlValidationResult(valid: false, errors: ['SQL query cannot be empty.']);
        }

        if (strlen($sql) > self::MAX_QUERY_LENGTH) {
            return new SqlValidationResult(valid: false, errors: ['SQL query exceeds maximum length of 10,000 characters.']);
        }

        // Remove SQL comments before validation
        $cleanSql = $this->removeComments($sql);

        // Check for multiple statements (semicolons)
        if ($this->hasMultipleStatements($cleanSql)) {
            $errors[] = 'Multiple SQL statements are not allowed. Use a single SELECT query.';
        }

        // Check that the query starts with SELECT or WITH
        if (! $this->startsWithAllowedKeyword($cleanSql)) {
            $errors[] = 'Only SELECT and WITH (CTE) queries are allowed.';
        }

        // Check for blocked statements within the query
        $blockedFound = $this->findBlockedStatements($cleanSql);
        if (! empty($blockedFound)) {
            $errors[] = 'Blocked statements found: '.implode(', ', $blockedFound).'.';
        }

        // Check for blocked system tables
        $blockedTables = $this->findBlockedTables($cleanSql);
        if (! empty($blockedTables)) {
            $errors[] = 'Access to system tables is not allowed: '.implode(', ', $blockedTables).'.';
        }

        if (! empty($errors)) {
            return new SqlValidationResult(valid: false, errors: $errors);
        }

        // Extract parameters and referenced tables
        $parameters = $this->extractParameters($cleanSql);
        $tables = $this->extractReferencedTables($cleanSql);

        return new SqlValidationResult(
            valid: true,
            parameters: $parameters,
            tables: $tables,
        );
    }

    /**
     * Remove SQL comments (single-line and multi-line).
     */
    protected function removeComments(string $sql): string
    {
        // Remove multi-line comments /* ... */
        $sql = (string) preg_replace('/\/\*.*?\*\//s', ' ', $sql);

        // Remove single-line comments -- ...
        $sql = (string) preg_replace('/--[^\n]*/', ' ', $sql);

        return trim($sql);
    }

    /**
     * Check for multiple statements separated by semicolons.
     */
    protected function hasMultipleStatements(string $sql): bool
    {
        // Strip string literals to avoid false positives from semicolons inside strings
        $stripped = $this->stripStringLiterals($sql);

        // Remove trailing semicollon if it's the last character
        $stripped = rtrim($stripped, "; \t\n\r");

        return str_contains($stripped, ';');
    }

    /**
     * Check that query starts with SELECT or WITH (CTE).
     */
    protected function startsWithAllowedKeyword(string $sql): bool
    {
        $upper = strtoupper(ltrim($sql));

        return str_starts_with($upper, 'SELECT') || str_starts_with($upper, 'WITH');
    }

    /**
     * Find any blocked statements in the SQL.
     */
    protected function findBlockedStatements(string $sql): array
    {
        $stripped = $this->stripStringLiterals($sql);
        $upper = strtoupper($stripped);
        $found = [];

        foreach (self::BLOCKED_STATEMENTS as $statement) {
            // Match as a whole word (not part of column/alias names)
            if (preg_match('/\b'.$statement.'\b/', $upper)) {
                $found[] = $statement;
            }
        }

        return array_unique($found);
    }

    /**
     * Find any blocked system tables referenced in the SQL.
     */
    protected function findBlockedTables(string $sql): array
    {
        $stripped = $this->stripStringLiterals($sql);
        $lower = strtolower($stripped);
        $found = [];

        foreach (self::BLOCKED_TABLES as $table) {
            if (preg_match('/\b'.preg_quote($table, '/').'\b/', $lower)) {
                $found[] = $table;
            }
        }

        return array_unique($found);
    }

    /**
     * Extract named parameters (:param) and positional placeholders (?) from the query.
     */
    protected function extractParameters(string $sql): array
    {
        $parameters = [];

        // Named parameters :param_name
        if (preg_match_all('/:([a-zA-Z_][a-zA-Z0-9_]*)/', $sql, $matches)) {
            foreach ($matches[1] as $param) {
                $parameters[] = $param;
            }
        }

        // Positional placeholders ?
        $stripped = $this->stripStringLiterals($sql);
        $positionalCount = substr_count($stripped, '?');
        for ($i = 0; $i < $positionalCount; $i++) {
            $parameters[] = 'param_'.($i + 1);
        }

        return array_unique($parameters);
    }

    /**
     * Extract table names referenced in FROM and JOIN clauses.
     */
    protected function extractReferencedTables(string $sql): array
    {
        $stripped = $this->stripStringLiterals($sql);
        $tables = [];

        // Match FROM table_name and JOIN table_name patterns
        if (preg_match_all('/\b(?:FROM|JOIN)\s+([a-zA-Z_][a-zA-Z0-9_.]*)/i', $stripped, $matches)) {
            foreach ($matches[1] as $table) {
                // Remove schema prefix if present (e.g., public.users -> users)
                $parts = explode('.', $table);
                $tables[] = end($parts);
            }
        }

        return array_unique($tables);
    }

    /**
     * Strip string literals to avoid false positives in validation.
     */
    protected function stripStringLiterals(string $sql): string
    {
        // Replace single-quoted strings
        $sql = (string) preg_replace("/'(?:[^'\\\\]|\\\\.)*'/", "''", $sql);

        // Replace double-quoted identifiers
        $sql = (string) preg_replace('/"(?:[^"\\\\]|\\\\.)*"/', '""', $sql);

        return $sql;
    }
}
