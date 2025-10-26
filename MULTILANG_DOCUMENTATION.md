# 🌍 Документация по мультиязычности JustSite

## 📋 Обзор

Проект JustSite полностью поддерживает 4 языка:
- 🇷🇺 Русский (ru) - язык по умолчанию
- 🇺🇸 Английский (en)
- 🇺🇦 Украинский (ua)
- 🇵🇱 Польский (pl)

## 🎯 Приоритет определения языка

Система автоматически определяет язык пользователя в следующем порядке:

1. **URL параметр** (`?lang=en`) - наивысший приоритет
2. **Session** - сохраненный язык в текущей сессии
3. **Cookie** - сохраненный язык (30 дней)
4. **База данных** - язык пользователя (для авторизованных)
5. **Браузер** - Accept-Language header
6. **По умолчанию** - русский (ru)

## 📁 Структура файлов

```
/lib/language.php                    # Основной класс LanguageManager
/translations/
  ├── ru.php                         # Русские переводы
  ├── en.php                         # Английские переводы
  ├── ua.php                         # Украинские переводы
  └── pl.php                         # Польские переводы
/api/change_language.php             # API смены языка
/api/ai_content_generator.php        # Мультиязычная AI генерация
```

## 🔧 Использование в PHP

### Инициализация

```php
<?php
require_once __DIR__ . '/lib/language.php';

// Инициализация системы языков
LanguageManager::init();
?>
```

### Получение переводов

```php
// Простой перевод
<?php echo LanguageManager::t('key_name'); ?>

// С HTML-экранированием
<?php echo htmlspecialchars(LanguageManager::t('key_name')); ?>

// Использование в title
<title><?php echo LanguageManager::t('page_title'); ?> — JustSite</title>

// Использование в атрибутах
<input placeholder="<?php echo LanguageManager::t('placeholder_email'); ?>">
```

### Получение текущего языка

```php
$currentLang = LanguageManager::getCurrentLanguage(); // 'ru', 'en', 'ua', или 'pl'
```

### Получение доступных языков

```php
$languages = LanguageManager::getAvailableLanguages();
// Возвращает массив:
// [
//   'ru' => ['name' => 'RU', 'flag' => 'text', 'locale' => 'ru_RU'],
//   'en' => ['name' => 'English', 'flag' => 'images/icons/eng.svg', 'locale' => 'en_US'],
//   ...
// ]
```

## 🔄 Смена языка

### Через профиль пользователя

1. Пользователь заходит в профиль
2. Выбирает язык из выпадающего списка
3. AJAX запрос на `/api/change_language.php`
4. Перенаправление на главную страницу

### Через URL

```
https://yoursite.com/?lang=en    # Переключает на английский
https://yoursite.com/?lang=ua    # Переключает на украинский
```

### Программно (JavaScript)

```javascript
$.ajax({
    url: 'api/change_language.php',
    method: 'POST',
    data: { language: 'en' },
    dataType: 'json',
    success: function(response) {
        if (response.success) {
            window.location.reload();
        }
    }
});
```

## ➕ Добавление новых переводов

### 1. Добавить ключ перевода

В файл `/translations/ru.php`:
```php
'new_key' => 'Новый текст',
```

### 2. Добавить перевод для всех языков

В `/translations/en.php`:
```php
'new_key' => 'New text',
```

В `/translations/ua.php`:
```php
'new_key' => 'Новий текст',
```

В `/translations/pl.php`:
```php
'new_key' => 'Nowy tekst',
```

### 3. Использовать в коде

```php
<?php echo LanguageManager::t('new_key'); ?>
```

## 🤖 AI Генерация контента

AI генератор автоматически создает контент на языке пользователя:

```php
require_once __DIR__ . '/api/ai_content_generator.php';

$language = LanguageManager::getCurrentLanguage();
$elements = AIContentGenerator::generateContent($description, $style, $language);
```

### Поддерживаемые типы сайтов:
- **ecommerce** - интернет-магазин
- **restaurant** - ресторан/кафе
- **portfolio** - портфолио
- **business** - бизнес-сайт
- **default** - универсальный шаблон

## 📊 Статистика переводов

| Категория | Количество ключей |
|-----------|-------------------|
| Навигация | 20+ |
| Кнопки | 25+ |
| Формы | 30+ |
| Модальные окна | 15+ |
| Сообщения | 35+ |
| Ошибки | 15+ |
| Элементы UI | 40+ |
| AI контент | 50+ |
| **ВСЕГО** | **200+ ключей** |

## 🔍 Отладка

### Проверка текущего языка

```php
echo "Current language: " . LanguageManager::getCurrentLanguage();
```

### Проверка загруженных переводов

```php
echo "Translation for 'key': " . LanguageManager::t('key');
```

### Логирование

Система автоматически логирует ошибки в error_log:
```php
error_log("Language detection DB error: " . $e->getMessage());
```

## ⚠️ Важные замечания

1. **Всегда инициализируйте** `LanguageManager::init()` в начале каждой страницы
2. **Cookie устанавливается автоматически** при смене языка (30 дней)
3. **Session сохраняется** для текущего посещения
4. **База данных обновляется** для авторизованных пользователей
5. **Не используйте жестко закодированные строки** - всегда через `LanguageManager::t()`

## 🚀 Производительность

- Переводы загружаются **один раз** при инициализации
- Кэшируются в статической переменной класса
- Минимальное влияние на производительность
- Cookie уменьшает количество запросов к БД

## 📝 Примеры использования

### Полная страница с мультиязычностью

```php
<?php
require_once __DIR__ . '/lib/language.php';
LanguageManager::init();

$currentLang = LanguageManager::getCurrentLanguage();
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo LanguageManager::t('page_title'); ?></title>
</head>
<body>
    <h1><?php echo LanguageManager::t('welcome'); ?></h1>
    <p><?php echo LanguageManager::t('description'); ?></p>
    
    <button><?php echo LanguageManager::t('btn_submit'); ?></button>
</body>
</html>
```

## 🎨 Рекомендации по переводам

1. **Используйте понятные ключи**: `btn_save` вместо `bs1`
2. **Группируйте по категориям**: `error_`, `btn_`, `nav_`, `form_`
3. **Избегайте HTML в переводах** (если возможно)
4. **Учитывайте контекст** - один термин может иметь разные переводы
5. **Проверяйте длину текста** - переводы могут быть длиннее оригинала

## 🔐 Безопасность

- Всегда используйте `htmlspecialchars()` для вывода пользовательского контента
- Валидация языкового кода перед установкой
- Защита от SQL injection в запросах к БД
- XSS защита через экранирование

## 📞 Поддержка

При возникновении проблем:
1. Проверьте, что `LanguageManager::init()` вызван
2. Убедитесь, что ключ перевода существует во всех файлах
3. Проверьте логи ошибок PHP
4. Убедитесь, что cookie не заблокированы браузером

---

**Версия документации**: 1.0  
**Последнее обновление**: 2025-01-29  
**Статус**: ✅ Готово к production
