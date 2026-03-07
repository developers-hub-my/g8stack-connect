<?php

declare(strict_types=1);

use App\Services\Connectors\ExcelConnector;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

beforeEach(function () {
    $this->excelPath = tempnam(sys_get_temp_dir(), 'excel_test_').'.xlsx';

    $spreadsheet = new Spreadsheet;

    // Sheet 1: employees
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setTitle('Employees');
    $sheet1->fromArray([
        ['id', 'name', 'department'],
        [1, 'Alice', 'Engineering'],
        [2, 'Bob', 'Marketing'],
        [3, 'Charlie', 'Engineering'],
    ]);

    // Sheet 2: departments
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Departments');
    $sheet2->fromArray([
        ['code', 'name', 'budget'],
        ['ENG', 'Engineering', 500000],
        ['MKT', 'Marketing', 300000],
    ]);

    $writer = new Xlsx($spreadsheet);
    $writer->save($this->excelPath);
});

afterEach(function () {
    if (file_exists($this->excelPath)) {
        unlink($this->excelPath);
    }
});

it('connects to an excel file', function () {
    $connector = new ExcelConnector;
    $result = $connector->connect(['file_path' => $this->excelPath]);

    expect($result->success)->toBeTrue()
        ->and($result->metadata['file_type'])->toBe('excel');
});

it('introspects multiple sheets as tables', function () {
    $connector = new ExcelConnector;
    $connector->connect(['file_path' => $this->excelPath]);

    $result = $connector->introspect();

    expect($result->success)->toBeTrue()
        ->and($result->tables)->toHaveCount(2)
        ->and($result->tables)->toContain('employees')
        ->and($result->tables)->toContain('departments');
});

it('previews rows from the default sheet', function () {
    $connector = new ExcelConnector;
    $connector->connect(['file_path' => $this->excelPath]);

    $result = $connector->preview('employees', 2);

    expect($result->success)->toBeTrue()
        ->and($result->count)->toBe(2)
        ->and($result->columns)->toBe(['id', 'name', 'department']);
});

it('can select a different sheet', function () {
    $connector = new ExcelConnector;
    $connector->connect(['file_path' => $this->excelPath]);
    $connector->selectSheet('departments');

    $result = $connector->preview('departments', 5);

    expect($result->success)->toBeTrue()
        ->and($result->count)->toBe(2)
        ->and($result->columns)->toBe(['code', 'name', 'budget']);
});

it('returns sheet names', function () {
    $connector = new ExcelConnector;
    $connector->connect(['file_path' => $this->excelPath]);

    expect($connector->getSheetNames())->toBe(['Employees', 'Departments']);
});

it('is always read only', function () {
    $connector = new ExcelConnector;

    expect($connector->isReadOnly())->toBeTrue();
});
