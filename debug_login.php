<?php
/**
 * Диагностический скрипт для отладки проблем с входом
 * УДАЛИТЕ ЭТОТ ФАЙЛ ПОСЛЕ ИСПОЛЬЗОВАНИЯ!
 */

require_once 'lib/db.php';
require_once 'lib/auth.php';

// Получаем подключение к БД
$pdo = DatabaseConnectionProvider::getConnection();

echo "<h2>🔍 Диагностика входа в систему</h2>";

// Тестовые данные
$test_email = 'admin@justsite.com';
$test_password = 'admin777';

echo "<p><strong>Тестируем вход:</strong></p>";
echo "<p>Email: <code>$test_email</code></p>";
echo "<p>Пароль: <code>$test_password</code></p>";
echo "<hr>";

try {
    // 1. Генерируем новый хеш
    $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
    echo "<h3>1️⃣ Генерация нового хеша</h3>";
    echo "<p>Новый хеш: <code>$new_hash</code></p>";
    
    // 2. Проверяем, есть ли пользователь с таким email
    echo "<h3>2️⃣ Поиск пользователя</h3>";
    $user = find_user_by_email($pdo, $test_email);
    
    if ($user) {
        echo "<p>✅ Пользователь найден:</p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$user['id']}</li>";
        echo "<li><strong>Email:</strong> {$user['email']}</li>";
        echo "<li><strong>Имя:</strong> {$user['name']}</li>";
        echo "<li><strong>Текущий хеш:</strong> <code>{$user['password_hash']}</code></li>";
        echo "</ul>";
        
        // 3. Проверяем текущий пароль
        echo "<h3>3️⃣ Проверка текущего пароля</h3>";
        $current_valid = verify_user_password($test_password, $user['password_hash']);
        echo "<p>Текущий пароль '$test_password' " . ($current_valid ? "✅ работает" : "❌ НЕ работает") . "</p>";
        
        // 4. Проверяем новый хеш
        echo "<h3>4️⃣ Проверка нового хеша</h3>";
        $new_valid = verify_user_password($test_password, $new_hash);
        echo "<p>Новый хеш " . ($new_valid ? "✅ работает" : "❌ НЕ работает") . "</p>";
        
        if (!$current_valid) {
            echo "<h3>🔧 Исправление пароля</h3>";
            echo "<p>Обновляем хеш пароля...</p>";
            
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
            $update_result = $stmt->execute([$new_hash, $test_email]);
            
            if ($update_result) {
                echo "<p>✅ Пароль успешно обновлен!</p>";
                
                // Проверяем еще раз
                $updated_user = find_user_by_email($pdo, $test_email);
                $final_check = verify_user_password($test_password, $updated_user['password_hash']);
                echo "<p>Финальная проверка: " . ($final_check ? "✅ Пароль работает!" : "❌ Все еще не работает") . "</p>";
            } else {
                echo "<p>❌ Ошибка обновления пароля</p>";
            }
        }
        
    } else {
        echo "<p>❌ Пользователь не найден. Создаем нового...</p>";
        
        echo "<h3>👤 Создание нового пользователя</h3>";
        
        // Проверяем, есть ли колонка language
        $columns_query = $pdo->query("DESCRIBE users");
        $columns = $columns_query->fetchAll(PDO::FETCH_COLUMN);
        $has_language = in_array('language', $columns);
        
        if ($has_language) {
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, language, created_at) VALUES (?, ?, ?, ?, NOW())");
            $create_result = $stmt->execute([$test_email, $new_hash, 'Администратор', 'ru']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, created_at) VALUES (?, ?, ?, NOW())");
            $create_result = $stmt->execute([$test_email, $new_hash, 'Администратор']);
        }
        
        if ($create_result) {
            echo "<p>✅ Пользователь создан!</p>";
            $new_user = find_user_by_email($pdo, $test_email);
            echo "<p>ID нового пользователя: {$new_user['id']}</p>";
        } else {
            echo "<p>❌ Ошибка создания пользователя</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>📋 Итоговая информация</h3>";
    echo "<p><strong>Для входа используйте:</strong></p>";
    echo "<p>Email: <code>$test_email</code></p>";
    echo "<p>Пароль: <code>$test_password</code></p>";
    
    // Показываем всех пользователей
    echo "<h3>👥 Все пользователи в системе</h3>";
    $all_users = $pdo->query("SELECT id, email, name, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    
    if ($all_users) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Имя</th><th>Создан</th></tr>";
        foreach ($all_users as $u) {
            echo "<tr>";
            echo "<td>{$u['id']}</td>";
            echo "<td>{$u['email']}</td>";
            echo "<td>{$u['name']}</td>";
            echo "<td>{$u['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Пользователей нет</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Ошибка: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p style='color: red;'><strong>⚠️ ВАЖНО: Удалите этот файл после использования!</strong></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
code { background: #eee; padding: 2px 5px; border-radius: 3px; font-size: 12px; }
table { background: white; margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background: #f0f0f0; }
ul { background: white; padding: 15px; border-radius: 5px; }
hr { margin: 20px 0; }
</style>
