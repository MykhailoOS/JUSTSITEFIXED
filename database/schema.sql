-- BugForge Database Schema for infinityfree hosting

-- Create database (Note: On infinityfree, the database is already created for you)
-- USE if0_39948852_XXX;

-- Users/Team Members table
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('planning', 'active', 'completed', 'on-hold') DEFAULT 'planning',
    progress INT DEFAULT 0,
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bugs table
CREATE TABLE IF NOT EXISTS bugs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('open', 'in-progress', 'resolved', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    assignee_id INT,
    project_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignee_id) REFERENCES team_members(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Project assignments table (many-to-many relationship between team members and projects)
CREATE TABLE IF NOT EXISTS project_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    project_id INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES team_members(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (member_id, project_id)
);

-- Insert sample data
INSERT INTO team_members (name, email, role) VALUES
('John Doe', 'john.doe@company.com', 'Frontend Developer'),
('Jane Smith', 'jane.smith@company.com', 'Backend Developer'),
('Mike Johnson', 'mike.johnson@company.com', 'Project Manager'),
('Sarah Williams', 'sarah.williams@company.com', 'UI/UX Designer');

INSERT INTO projects (name, description, status, progress, start_date, end_date) VALUES
('E-commerce Platform', 'Redesign of the company\'s online store', 'active', 65, '2025-09-01', '2025-12-31'),
('Mobile App Development', 'Development of iOS and Android applications', 'planning', 10, '2025-10-01', '2026-03-31'),
('Database Migration', 'Migrating legacy database to cloud solution', 'completed', 100, '2025-07-01', '2025-09-15');

INSERT INTO project_assignments (member_id, project_id) VALUES
(1, 1), -- John assigned to E-commerce Platform
(1, 2), -- John assigned to Mobile App
(2, 1), -- Jane assigned to E-commerce Platform
(2, 3), -- Jane assigned to Database Migration
(3, 1), -- Mike assigned to E-commerce Platform
(4, 2); -- Sarah assigned to Mobile App

INSERT INTO bugs (title, description, status, priority, assignee_id, project_id) VALUES
('Login button not working', 'The login button on the homepage does not respond when clicked', 'open', 'high', 1, 1),
('Mobile layout broken', 'The layout is not responsive on mobile devices', 'in-progress', 'medium', 4, 2),
('Database connection timeout', 'Application fails to connect to database after 30 seconds', 'resolved', 'critical', 2, 3);