# 🎯 Как пользоваться системой Templates

## 📋 Пошаговая инструкция

### Шаг 1: Инициализация (только один раз)
1. Перейдите на: `http://your-domain.com/init_templates.php`
2. Нажмите кнопку для создания примеров шаблонов
3. Должно появиться сообщение "✅ Successfully created 5 sample templates!"

### Шаг 2: Доступ к Templates
**Вариант A - Через админ-панель:**
1. Войдите в админ-панель: `http://your-domain.com/admin.php`
2. В правом верхнем углу найдите ссылку "📄 Templates"
3. Нажмите на неё

**Вариант B - Прямой доступ:**
1. Перейдите на: `http://your-domain.com/templates.php`

### Шаг 3: Просмотр шаблонов
После входа вы увидите:
- **Статистику**: количество шаблонов, активных, скачиваний
- **Сетку шаблонов**: карточки с примерами
- **Кнопки действий**: Create, Import, Export

## 🛠️ Основные функции

### 1. Создание нового шаблона
1. Нажмите **"Create New Template"**
2. Заполните форму:
   - **Name**: название шаблона
   - **Description**: описание
   - **Category**: категория (landing, blog, portfolio, etc.)
   - **Type**: тип (html, component, layout)
3. В поле **"Template Code"** введите Handlebars код:
```handlebars
<div class="card">
    <h2>{{title}}</h2>
    <p>{{content}}</p>
    {{#if featured}}
    <span class="badge">Featured</span>
    {{/if}}
</div>
```
4. В поле **"Sample Data"** введите JSON:
```json
{
    "title": "Мой заголовок",
    "content": "Содержимое статьи",
    "featured": true
}
```
5. Нажмите **"Save Template"**

### 2. Редактирование шаблона
1. На карточке шаблона нажмите **"⋮"** (три точки)
2. Выберите **"Edit"**
3. Внесите изменения
4. Нажмите **"Save Template"**

### 3. Превью шаблона
1. На карточке шаблона нажмите **"Preview"**
2. Или нажмите **"⋮"** → **"Preview"**
3. Увидите:
   - **Rendered HTML**: как выглядит результат
   - **Source Code**: исходный HTML код

### 4. Скачивание шаблона
1. На карточке шаблона нажмите **"⋮"**
2. Выберите **"Download"**
3. Файл скачается как HTML

### 5. Дублирование шаблона
1. На карточке шаблона нажмите **"⋮"**
2. Выберите **"Duplicate"**
3. Создастся копия с названием "(Copy)"

## 🎨 Handlebars синтаксис

### Переменные
```handlebars
<h1>{{title}}</h1>
<p>{{description}}</p>
```

### Вложенные свойства
```handlebars
<h2>{{user.name}}</h2>
<p>{{user.email}}</p>
```

### Циклы
```handlebars
{{#each items}}
<div class="item">
    <h3>{{title}}</h3>
    <p>{{description}}</p>
</div>
{{/each}}
```

### Условия
```handlebars
{{#if featured}}
<div class="badge">Featured</div>
{{/if}}

{{#if user.isAdmin}}
<button>Admin Panel</button>
{{/if}}
```

### Примеры JSON данных
```json
{
    "title": "Заголовок",
    "description": "Описание",
    "featured": true,
    "user": {
        "name": "Иван",
        "email": "ivan@example.com",
        "isAdmin": true
    },
    "items": [
        {
            "title": "Элемент 1",
            "description": "Описание 1"
        },
        {
            "title": "Элемент 2", 
            "description": "Описание 2"
        }
    ]
}
```

## 🎯 Практические примеры

### Пример 1: Простая карточка
**Template:**
```handlebars
<div class="card bg-white shadow-lg rounded-lg p-6">
    <h3 class="text-xl font-bold mb-2">{{title}}</h3>
    <p class="text-gray-600 mb-4">{{description}}</p>
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500">{{date}}</span>
        <button class="btn btn-primary btn-sm">{{buttonText}}</button>
    </div>
</div>
```

**Sample Data:**
```json
{
    "title": "Новости",
    "description": "Последние новости компании",
    "date": "2024-01-15",
    "buttonText": "Читать"
}
```

### Пример 2: Список с циклом
**Template:**
```handlebars
<div class="space-y-4">
    {{#each products}}
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded">
        <div>
            <h4 class="font-semibold">{{name}}</h4>
            <p class="text-sm text-gray-600">{{description}}</p>
        </div>
        <div class="text-right">
            <div class="font-bold">${{price}}</div>
            {{#if inStock}}
            <span class="text-green-600 text-sm">В наличии</span>
            {{else}}
            <span class="text-red-600 text-sm">Нет в наличии</span>
            {{/if}}
        </div>
    </div>
    {{/each}}
</div>
```

**Sample Data:**
```json
{
    "products": [
        {
            "name": "iPhone 15",
            "description": "Новый iPhone",
            "price": "999",
            "inStock": true
        },
        {
            "name": "MacBook Pro",
            "description": "Профессиональный ноутбук",
            "price": "1999",
            "inStock": false
        }
    ]
}
```

## 🚨 Решение проблем

### Проблема: "Templates не загружаются"
**Решение:**
1. Проверьте, что вы админ
2. Перейдите на `/init_templates.php` для инициализации
3. Обновите страницу

### Проблема: "Превью не работает"
**Решение:**
1. Проверьте синтаксис Handlebars
2. Убедитесь, что JSON валидный
3. Проверьте, что все переменные есть в данных

### Проблема: "Ссылка Templates не видна"
**Решение:**
1. Обновите страницу админ-панели
2. Очистите кэш браузера
3. Проверьте, что вы вошли как админ

## 🎉 Готово!

Теперь вы знаете, как:
- ✅ Создавать шаблоны
- ✅ Редактировать их
- ✅ Использовать Handlebars
- ✅ Просматривать превью
- ✅ Скачивать HTML файлы

**Начните с примеров шаблонов и экспериментируйте!** 🚀
