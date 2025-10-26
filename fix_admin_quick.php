<?php
/**
 * БЫСТРОЕ ИСПРАВЛЕНИЕ АДМИНА
 * Простой скрипт без диагностики - просто создает/обновляет админа
 */

require_once 'lib/db.php';

// Получаем подключение к БД
$pdo = DatabaseConnectionProvider::getConnection();

$email = 'admin@justsite.com';
$password = 'admin777';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "🚀 Быстрое исправление админа<br>";
echo "Email: $email<br>";
echo "Пароль: $password<br><br>";

try {
    // Удаляем старого
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
    echo "🗑️ Старые записи удалены<br>";
    
    // Создаем нового (проверяем наличие колонки language)
    try {
        $pdo->prepare("INSERT INTO users (email, password_hash, name, language, created_at) VALUES (?, ?, ?, ?, NOW())")
            ->execute([$email, $hash, 'Администратор', 'ru']);
    } catch (PDOException $e) {
        // Если нет колонки language, пробуем без неё
        $pdo->prepare("INSERT INTO users (email, password_hash, name, created_at) VALUES (?, ?, ?, NOW())")
            ->execute([$email, $hash, 'Администратор']);
    }
    
    echo "✅ <strong>Админ создан!</strong><br>";
    echo "Теперь логинься: $email / $password<br>";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>
