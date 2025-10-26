-- MySQL schema for JustSite

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(120) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL DEFAULT '',
  slug VARCHAR(100) NOT NULL DEFAULT '',
  description TEXT DEFAULT '',
  privacy ENUM('public', 'unlisted', 'private') NOT NULL DEFAULT 'public',
  html MEDIUMTEXT NOT NULL,
  view_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_projects_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_projects_user ON projects(user_id, updated_at);
CREATE UNIQUE INDEX idx_projects_user_slug ON projects(user_id, slug);
CREATE INDEX idx_projects_privacy ON projects(privacy, created_at);

-- Table for view statistics (to track unique views)
CREATE TABLE IF NOT EXISTS project_views (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  visitor_ip VARCHAR(45) NOT NULL,
  user_agent TEXT,
  referer TEXT,
  viewed_at DATETIME NOT NULL,
  CONSTRAINT fk_views_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_views_project ON project_views(project_id, viewed_at);
CREATE INDEX idx_views_ip ON project_views(visitor_ip, viewed_at);


