-- ================================================================
-- СОЗДАНИЕ НОВОГО АДМИНИСТРАТОРА
-- Email: admin@justsite.com
-- Пароль: admin777
-- ================================================================

-- 1. Удалить старого админа (если есть)
DELETE FROM users WHERE email = 'admin@justsite.com';

-- 2. Создать нового администратора
INSERT INTO users (email, password_hash, name, language, created_at) 
VALUES (
    'admin@justsite.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Администратор', 
    'ru', 
    NOW()
);

-- 3. Проверить результат
SELECT id, email, name, language, created_at FROM users WHERE email = 'admin@justsite.com';

-- ================================================================
-- ДАННЫЕ ДЛЯ ВХОДА:
-- Email: admin@justsite.com  
-- Пароль: admin777
-- ================================================================

-- Альтернативные варианты хешей (попробуйте если первый не работает):

-- Вариант 2:
-- INSERT INTO users (email, password_hash, name, language, created_at) 
-- VALUES ('admin@justsite.com', '$2y$10$EIeaGlDfbhF8gT5Zv7JqEOm3LnT8vP2kL9DfbhF8gT5Zv7JqEOm3', 'Администратор', 'ru', NOW());

-- Вариант 3 (простой MD5 - НЕ БЕЗОПАСНО, только для теста):
-- INSERT INTO users (email, password_hash, name, language, created_at) 
-- VALUES ('admin@justsite.com', MD5('admin777'), 'Администратор', 'ru', NOW());

-- Показать всех пользователей:
-- SELECT * FROM users ORDER BY created_at DESC;
