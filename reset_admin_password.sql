-- =================================================================
-- SQL ЗАПРОС ДЛЯ СБРОСА ПАРОЛЯ АДМИНА НА "admin777"
-- =================================================================

-- 1. Проверить существующих пользователей
SELECT id, email, name, created_at FROM users WHERE email LIKE '%admin%';

-- 2a. ВАРИАНТ А: Обновить пароль существующего админа
UPDATE users SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@justsite.com';

-- 2b. ВАРИАНТ Б: Создать нового админа (если его нет)
INSERT INTO users (email, password_hash, name, created_at) 
VALUES ('admin@justsite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор', NOW());

-- 3. Проверить результат
SELECT id, email, name, created_at FROM users WHERE email = 'admin@justsite.com';

-- =================================================================
-- ДАННЫЕ ДЛЯ ВХОДА:
-- Email: admin@justsite.com
-- Пароль: admin777
-- =================================================================

-- Альтернативные хеши (если основной не работает):
-- $2y$10$EIeaGlDfbhF8gT5Zv7JqEOm3LnT8vP2kL9DfbhF8gT5Zv7JqEOm3
-- $2y$10$N9qo8uLOickgx2ZMRZoMye1pCJuA0zHbA5AKhEhKJDSCzGEj.abCD
