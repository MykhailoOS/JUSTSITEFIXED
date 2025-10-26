<?php
// Генерация правильного хэша для пароля "admin"
$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Пароль: " . $password . "\n";
echo "Хэш: " . $hash . "\n";
echo "\nSQL запрос:\n";
echo "INSERT INTO users (email, password_hash, name, created_at) VALUES ('admin@justsite.com', '$hash', 'Администратор', NOW());\n";
?>
