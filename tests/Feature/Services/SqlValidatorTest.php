<?php

use App\Services\SqlValidator;

beforeEach(function () {
    $this->validator = new SqlValidator;
});

// === Valid queries ===

it('allows a simple SELECT query', function () {
    $result = $this->validator->validate('SELECT * FROM users');

    expect($result->valid)->toBeTrue()
        ->and($result->errors)->toBeEmpty()
        ->and($result->tables)->toContain('users');
});

it('allows SELECT with WHERE clause', function () {
    $result = $this->validator->validate('SELECT id, name FROM employees WHERE department = :dept');

    expect($result->valid)->toBeTrue()
        ->and($result->parameters)->toContain('dept')
        ->and($result->tables)->toContain('employees');
});

it('allows SELECT with JOINs', function () {
    $result = $this->validator->validate(
        'SELECT e.name, d.name AS department FROM employees e JOIN departments d ON e.dept_id = d.id'
    );

    expect($result->valid)->toBeTrue()
        ->and($result->tables)->toContain('employees', 'departments');
});

it('allows WITH (CTE) queries', function () {
    $result = $this->validator->validate(
        'WITH active AS (SELECT * FROM users WHERE active = 1) SELECT * FROM active'
    );

    expect($result->valid)->toBeTrue()
        ->and($result->tables)->toContain('users', 'active');
});

it('allows subqueries', function () {
    $result = $this->validator->validate(
        'SELECT * FROM orders WHERE user_id IN (SELECT id FROM users WHERE role = :role)'
    );

    expect($result->valid)->toBeTrue()
        ->and($result->parameters)->toContain('role')
        ->and($result->tables)->toContain('orders', 'users');
});

it('allows aggregate functions', function () {
    $result = $this->validator->validate(
        'SELECT department, COUNT(*) as total, AVG(salary) as avg_salary FROM employees GROUP BY department HAVING COUNT(*) > 5'
    );

    expect($result->valid)->toBeTrue();
});

it('allows trailing semicolon', function () {
    $result = $this->validator->validate('SELECT * FROM users;');

    expect($result->valid)->toBeTrue();
});

// === Parameter extraction ===

it('extracts named parameters', function () {
    $result = $this->validator->validate('SELECT * FROM users WHERE status = :status AND role = :role');

    expect($result->valid)->toBeTrue()
        ->and($result->parameters)->toContain('status', 'role');
});

it('extracts positional parameters', function () {
    $result = $this->validator->validate('SELECT * FROM users WHERE status = ? AND role = ?');

    expect($result->valid)->toBeTrue()
        ->and($result->parameters)->toContain('param_1', 'param_2');
});

it('extracts mixed parameters', function () {
    $result = $this->validator->validate('SELECT * FROM users WHERE status = :status AND active = ?');

    expect($result->valid)->toBeTrue()
        ->and($result->parameters)->toContain('status', 'param_1');
});

// === Blocked statements ===

it('blocks INSERT statements', function () {
    $result = $this->validator->validate('INSERT INTO users (name) VALUES (\'test\')');

    expect($result->valid)->toBeFalse()
        ->and($result->errors)->not->toBeEmpty();
});

it('blocks UPDATE statements', function () {
    $result = $this->validator->validate('UPDATE users SET name = \'test\'');

    expect($result->valid)->toBeFalse();
});

it('blocks DELETE statements', function () {
    $result = $this->validator->validate('DELETE FROM users WHERE id = 1');

    expect($result->valid)->toBeFalse();
});

it('blocks DROP statements', function () {
    $result = $this->validator->validate('DROP TABLE users');

    expect($result->valid)->toBeFalse();
});

it('blocks TRUNCATE statements', function () {
    $result = $this->validator->validate('TRUNCATE TABLE users');

    expect($result->valid)->toBeFalse();
});

it('blocks ALTER statements', function () {
    $result = $this->validator->validate('ALTER TABLE users ADD COLUMN email VARCHAR(255)');

    expect($result->valid)->toBeFalse();
});

it('blocks CREATE statements', function () {
    $result = $this->validator->validate('CREATE TABLE evil (id INT)');

    expect($result->valid)->toBeFalse();
});

it('blocks multiple statements', function () {
    $result = $this->validator->validate('SELECT * FROM users; DROP TABLE users');

    expect($result->valid)->toBeFalse()
        ->and(implode(' ', $result->errors))->toContain('Multiple SQL statements');
});

// === Blocked system tables ===

it('blocks information_schema access', function () {
    $result = $this->validator->validate('SELECT * FROM information_schema.tables');

    expect($result->valid)->toBeFalse()
        ->and(implode(' ', $result->errors))->toContain('information_schema');
});

it('blocks pg_catalog access', function () {
    $result = $this->validator->validate('SELECT * FROM pg_catalog.pg_tables');

    expect($result->valid)->toBeFalse()
        ->and(implode(' ', $result->errors))->toContain('pg_catalog');
});

it('blocks mysql system database access', function () {
    $result = $this->validator->validate('SELECT * FROM mysql.user');

    expect($result->valid)->toBeFalse()
        ->and(implode(' ', $result->errors))->toContain('mysql');
});

it('blocks sys schema access', function () {
    $result = $this->validator->validate('SELECT * FROM sys.processlist');

    expect($result->valid)->toBeFalse()
        ->and(implode(' ', $result->errors))->toContain('sys');
});

it('blocks sqlite_master access', function () {
    $result = $this->validator->validate('SELECT * FROM sqlite_master');

    expect($result->valid)->toBeFalse()
        ->and(implode(' ', $result->errors))->toContain('sqlite_master');
});

// === Edge cases ===

it('rejects empty query', function () {
    $result = $this->validator->validate('');

    expect($result->valid)->toBeFalse()
        ->and($result->errors)->toContain('SQL query cannot be empty.');
});

it('rejects query exceeding max length', function () {
    $result = $this->validator->validate('SELECT ' . str_repeat('a', 10_001));

    expect($result->valid)->toBeFalse()
        ->and(implode(' ', $result->errors))->toContain('maximum length');
});

it('ignores blocked keywords inside string literals', function () {
    $result = $this->validator->validate("SELECT * FROM users WHERE name = 'DELETE FROM evil'");

    expect($result->valid)->toBeTrue();
});

it('handles SQL comments safely', function () {
    $result = $this->validator->validate("SELECT * FROM users -- DROP TABLE users\nWHERE id = 1");

    expect($result->valid)->toBeTrue()
        ->and($result->tables)->toContain('users');
});

it('handles multi-line comments safely', function () {
    $result = $this->validator->validate("SELECT * FROM users /* DROP TABLE users */ WHERE id = 1");

    expect($result->valid)->toBeTrue();
});

it('extracts schema-qualified table names', function () {
    $result = $this->validator->validate('SELECT * FROM public.users');

    expect($result->valid)->toBeTrue()
        ->and($result->tables)->toContain('users');
});
