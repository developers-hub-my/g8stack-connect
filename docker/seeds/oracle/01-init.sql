CREATE TABLE employees (
    id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR2(100) NOT NULL,
    email VARCHAR2(255),
    ic_number VARCHAR2(20),
    department VARCHAR2(100),
    salary NUMBER(10,2),
    hired_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO employees (name, email, ic_number, department, salary, hired_at) VALUES
('Ali Ahmad', 'ali@example.com', '880101-01-1234', 'Engineering', 8500.00, DATE '2020-03-15');
INSERT INTO employees (name, email, ic_number, department, salary, hired_at) VALUES
('Siti Aminah', 'siti@example.com', '900202-02-5678', 'Marketing', 7200.00, DATE '2021-06-01');
INSERT INTO employees (name, email, ic_number, department, salary, hired_at) VALUES
('Kumar Raj', 'kumar@example.com', '850303-03-9012', 'Finance', 9100.00, DATE '2019-11-20');
INSERT INTO employees (name, email, ic_number, department, salary, hired_at) VALUES
('Mei Ling', 'meiling@example.com', '920404-04-3456', 'Engineering', 8800.00, DATE '2022-01-10');
INSERT INTO employees (name, email, ic_number, department, salary, hired_at) VALUES
('Ahmad Razak', 'razak@example.com', '870505-05-7890', 'Operations', 7500.00, DATE '2023-04-05');

CREATE TABLE departments (
    id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR2(100) NOT NULL,
    code VARCHAR2(10) UNIQUE NOT NULL,
    budget NUMBER(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO departments (name, code, budget) VALUES ('Engineering', 'ENG', 500000.00);
INSERT INTO departments (name, code, budget) VALUES ('Marketing', 'MKT', 300000.00);
INSERT INTO departments (name, code, budget) VALUES ('Finance', 'FIN', 250000.00);
INSERT INTO departments (name, code, budget) VALUES ('Operations', 'OPS', 400000.00);

CREATE TABLE projects (
    id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR2(200) NOT NULL,
    department_code VARCHAR2(10) REFERENCES departments(code),
    status VARCHAR2(20) DEFAULT 'active',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO projects (name, department_code, status, start_date, end_date) VALUES
('API Gateway v2', 'ENG', 'active', DATE '2025-01-01', DATE '2025-12-31');
INSERT INTO projects (name, department_code, status, start_date, end_date) VALUES
('Brand Refresh', 'MKT', 'active', DATE '2025-03-01', DATE '2025-09-30');
INSERT INTO projects (name, department_code, status, start_date, end_date) VALUES
('Q1 Audit', 'FIN', 'completed', DATE '2025-01-01', DATE '2025-03-31');

COMMIT;
