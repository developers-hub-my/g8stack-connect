CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    ic_number VARCHAR(20),
    department VARCHAR(100),
    salary DECIMAL(10,2),
    hired_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO employees (name, email, ic_number, department, salary, hired_at) VALUES
('Ali Ahmad', 'ali@example.com', '880101-01-1234', 'Engineering', 8500.00, '2020-03-15'),
('Siti Aminah', 'siti@example.com', '900202-02-5678', 'Marketing', 7200.00, '2021-06-01'),
('Kumar Raj', 'kumar@example.com', '850303-03-9012', 'Finance', 9100.00, '2019-11-20'),
('Mei Ling', 'meiling@example.com', '920404-04-3456', 'Engineering', 8800.00, '2022-01-10'),
('Ahmad Razak', 'razak@example.com', '870505-05-7890', 'Operations', 7500.00, '2023-04-05');

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) UNIQUE NOT NULL,
    budget DECIMAL(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO departments (name, code, budget) VALUES
('Engineering', 'ENG', 500000.00),
('Marketing', 'MKT', 300000.00),
('Finance', 'FIN', 250000.00),
('Operations', 'OPS', 400000.00);

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    department_code VARCHAR(10),
    status VARCHAR(20) DEFAULT 'active',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_code) REFERENCES departments(code)
);

INSERT INTO projects (name, department_code, status, start_date, end_date) VALUES
('API Gateway v2', 'ENG', 'active', '2025-01-01', '2025-12-31'),
('Brand Refresh', 'MKT', 'active', '2025-03-01', '2025-09-30'),
('Q1 Audit', 'FIN', 'completed', '2025-01-01', '2025-03-31');
