<?php
/**
 * Скрипт для сброса пароля администратора на "admin777"
 * Запустите этот скрипт на сервере через браузер или командную строку
 */

// Подключение к базе данных
require_once 'lib/db.php';

// Получаем подключение к БД
$pdo = DatabaseConnectionProvider::getConnection();

try {
    // Проверяем подключение к БД
    if (!isset($pdo)) {
        die("❌ Ошибка: не удалось подключиться к базе данных\n");
    }
    
    echo "✅ Подключение к базе данных установлено\n\n";
    
    // Генерируем хеш для пароля "admin777"
    $new_password = 'admin777';
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    echo "🔐 Новый пароль: $new_password\n";
    echo "🔑 Сгенерированный хеш: $password_hash\n\n";
    
    // Ищем существующего админа
    $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE email = ? OR email LIKE '%admin%' LIMIT 5");
    $stmt->execute(['admin@justsite.com']);
    $existing_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "👥 Найденные пользователи:\n";
    if (empty($existing_users)) {
        echo "   Пользователи не найдены\n\n";
    } else {
        foreach ($existing_users as $user) {
            echo "   ID: {$user['id']}, Email: {$user['email']}, Имя: {$user['name']}\n";
        }
        echo "\n";
    }
    
    // Проверяем, есть ли admin@justsite.com
    $admin_exists = false;
    foreach ($existing_users as $user) {
        if ($user['email'] === 'admin@justsite.com') {
            $admin_exists = true;
            break;
        }
    }
    
    if ($admin_exists) {
        // Обновляем пароль существующего админа
        echo "🔄 Обновляем пароль существующего админа...\n";
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $result = $stmt->execute([$password_hash, 'admin@justsite.com']);
        
        if ($result) {
            echo "✅ Пароль успешно обновлен!\n";
        } else {
            echo "❌ Ошибка при обновлении пароля\n";
        }
    } else {
        // Создаем нового админа
        echo "👤 Создаем нового администратора...\n";
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, created_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute(['admin@justsite.com', $password_hash, 'Администратор']);
        
        if ($result) {
            echo "✅ Новый администратор создан!\n";
        } else {
            echo "❌ Ошибка при создании администратора\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📋 ИТОГОВАЯ ИНФОРМАЦИЯ:\n";
    echo "   Email: admin@justsite.com\n";
    echo "   Пароль: admin777\n";
    echo "   Статус: " . ($result ? "✅ Готов к использованию" : "❌ Ошибка") . "\n";
    echo str_repeat("=", 50) . "\n\n";
    
    // SQL запросы для ручного выполнения (на всякий случай)
    echo "📄 SQL запросы для ручного выполнения:\n\n";
    echo "-- Обновить пароль:\n";
    echo "UPDATE users SET password_hash = '$password_hash' WHERE email = 'admin@justsite.com';\n\n";
    echo "-- Создать нового админа:\n";
    echo "INSERT INTO users (email, password_hash, name, created_at) VALUES ('admin@justsite.com', '$password_hash', 'Администратор', NOW());\n\n";
    echo "-- Проверить результат:\n";
    echo "SELECT id, email, name, created_at FROM users WHERE email = 'admin@justsite.com';\n\n";
    
    // Показываем всех пользователей для справки
    $stmt = $pdo->prepare("SELECT id, email, name, created_at FROM users ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "👥 Последние 10 пользователей в системе:\n";
    foreach ($all_users as $user) {
        echo "   {$user['id']} | {$user['email']} | {$user['name']} | {$user['created_at']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Ошибка базы данных: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Общая ошибка: " . $e->getMessage() . "\n";
}

echo "\n🔚 Скрипт завершен\n";
echo "💡 Совет: после входа в админку смените пароль на более безопасный!\n";
?>
