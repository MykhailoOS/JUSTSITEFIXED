<?php
/**
 * Простой скрипт для создания админа
 * Запустите через браузер: yourdomain.com/create_admin_simple.php
 */

// Подключение к базе данных
require_once 'lib/db.php';

// Получаем подключение к БД
$pdo = DatabaseConnectionProvider::getConnection();

// Данные админа
$email = 'admin@justsite.com';
$password = 'admin777';
$name = 'Администратор';
$language = 'ru';

echo "<h2>🔐 Создание администратора</h2>";
echo "<p><strong>Email:</strong> $email</p>";
echo "<p><strong>Пароль:</strong> $password</p>";
echo "<hr>";

try {
    // Генерируем хеш
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<p>✅ Хеш сгенерирован: <code>$password_hash</code></p>";
    
    // Удаляем старого админа если есть
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute([$email]);
    echo "<p>🗑️ Старые записи админа удалены</p>";
    
    // Создаем нового админа
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, language, created_at) VALUES (?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$email, $password_hash, $name, $language]);
    
    if ($result) {
        echo "<p>✅ <strong>Администратор успешно создан!</strong></p>";
        
        // Проверяем результат
        $stmt = $pdo->prepare("SELECT id, email, name, language, created_at FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "<h3>📋 Данные созданного админа:</h3>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> {$admin['id']}</li>";
            echo "<li><strong>Email:</strong> {$admin['email']}</li>";
            echo "<li><strong>Имя:</strong> {$admin['name']}</li>";
            echo "<li><strong>Язык:</strong> {$admin['language']}</li>";
            echo "<li><strong>Создан:</strong> {$admin['created_at']}</li>";
            echo "</ul>";
        }
        
        echo "<hr>";
        echo "<h3>🚀 Готово к использованию!</h3>";
        echo "<p>Теперь можете войти в админку с данными:</p>";
        echo "<p><strong>Email:</strong> $email<br>";
        echo "<strong>Пароль:</strong> $password</p>";
        
    } else {
        echo "<p>❌ Ошибка при создании администратора</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Ошибка базы данных: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Общая ошибка: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>💡 Совет: После входа в админку удалите этот файл и смените пароль!</em></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
p { margin: 10px 0; }
code { background: #eee; padding: 2px 5px; border-radius: 3px; }
ul { background: white; padding: 15px; border-radius: 5px; }
hr { margin: 20px 0; }
</style>
