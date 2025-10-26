-- Add language column to existing users table
-- Run this if you already have a database with users

-- Add language column to users table
ALTER TABLE users ADD COLUMN language VARCHAR(5) NOT NULL DEFAULT 'ru' AFTER name;

-- Update existing users to have default language
UPDATE users SET language = 'ru' WHERE language IS NULL OR language = '';

-- Show result
SELECT 'Language column added successfully to existing users table' as result;
