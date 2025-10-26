# 🎯 Простая система Templates

## ✅ Что получилось

### Простая админ-панель (`templates_simple.php`)
- **Добавление шаблонов**: Простая форма с полями
- **Список шаблонов**: Таблица с кнопками Preview/Edit/Delete
- **Превью**: Простое модальное окно
- **Никаких сложностей**: Обычный HTML + PHP

### Простая интеграция в конструктор
- **Кнопка Templates**: В панели инструментов
- **Простое модальное окно**: Список шаблонов
- **Вставка**: Один клик - шаблон в проекте
- **Никаких сложных компонентов**: Обычный JavaScript

## 🚀 Как использовать

### Для админа (управление шаблонами):
1. Перейти на `templates_simple.php`
2. Заполнить форму:
   - **Name**: Название шаблона
   - **Category**: Категория (landing, blog, portfolio)
   - **HTML Code**: HTML код шаблона
   - **Sample Data**: JSON данные (опционально)
3. Нажать "Add Template"
4. Шаблон появится в списке

### Для пользователя (использование в конструкторе):
1. Открыть конструктор (`index.php`)
2. Нажать кнопку **"Templates"** (иконка блока)
3. Выбрать шаблон из списка
4. Нажать **"Insert"**
5. Шаблон добавится в проект

## 📝 Примеры шаблонов

### Простой Hero Section:
```html
<section style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 20px; text-align: center; color: white;">
    <h1 style="font-size: 3rem; margin-bottom: 20px;">{{title}}</h1>
    <p style="font-size: 1.2rem; margin-bottom: 30px;">{{subtitle}}</p>
    <button style="background: #ff6b6b; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 1.1rem;">{{buttonText}}</button>
</section>
```

### Карточка товара:
```html
<div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; max-width: 300px;">
    <img src="{{image}}" alt="{{title}}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px;">
    <h3 style="margin: 15px 0 10px 0;">{{title}}</h3>
    <p style="color: #666; margin-bottom: 15px;">{{description}}</p>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 1.5rem; font-weight: bold; color: #333;">${{price}}</span>
        <button style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px;">Add to Cart</button>
    </div>
</div>
```

## 🎨 Что можно делать

### Создавать шаблоны для:
- **Hero секций** - красивые заголовки
- **Карточек товаров** - для магазинов
- **Форм обратной связи** - контакты
- **Галерей** - портфолио
- **Блогов** - статьи и новости

### Использовать переменные:
```html
<!-- Простые переменные -->
<h1>{{title}}</h1>
<p>{{description}}</p>

<!-- С данными -->
{
    "title": "Мой заголовок",
    "description": "Мое описание"
}
```

## 🔧 Технические детали

### Файлы системы:
- `templates_simple.php` - Админ-панель
- `api/templates_simple.php` - API
- `components/templates_simple.php` - Интеграция
- `index.php` - Обновлен для интеграции

### База данных:
- Таблица `templates` (уже создана)
- Поля: id, name, category, template, sample_data, user_id, created_at

### API endpoints:
- `GET api/templates_simple.php?action=list` - Список шаблонов
- `GET api/templates_simple.php?action=get&id={id}` - Один шаблон
- `POST api/templates_simple.php` - Добавить шаблон
- `GET api/templates_simple.php?delete={id}` - Удалить шаблон

## 🚨 Если что-то не работает

### Проблема: "Templates не загружаются"
**Решение:**
1. Проверить, что таблица `templates` создана
2. Перейти на `/init_templates.php` для создания примеров
3. Проверить права доступа

### Проблема: "Не могу вставить шаблон"
**Решение:**
1. Убедиться, что проект открыт
2. Проверить консоль браузера на ошибки
3. Попробовать обновить страницу

### Проблема: "Админ-панель не открывается"
**Решение:**
1. Проверить, что вы админ
2. Убедиться, что файл `templates_simple.php` существует
3. Проверить права доступа к файлам

## 🎉 Готово!

**Простая система Templates готова к использованию!**

- ✅ **Простая админ-панель** для управления
- ✅ **Простая интеграция** в конструктор  
- ✅ **Никаких сложностей** - обычный HTML/PHP/JS
- ✅ **Быстро работает** - минимум кода
- ✅ **Легко понимать** - простая логика

**Теперь можно создавать и использовать шаблоны без заморочек!** 🚀
