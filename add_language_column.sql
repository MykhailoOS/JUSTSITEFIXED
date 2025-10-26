-- Add language column to users table
-- This script adds a language column to store user's preferred language

-- Check if column already exists and add if not
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'language') = 0,
    'ALTER TABLE users ADD COLUMN language VARCHAR(5) DEFAULT "ru"',
    'SELECT "Column language already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing users to have default language
UPDATE users SET language = 'ru' WHERE language IS NULL OR language = '';

-- Show result
SELECT 'Language column added successfully' as result;
