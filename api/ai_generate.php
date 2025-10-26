<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user
ini_set('log_errors', 1); // Log errors to server log

try {
    session_start();
    header('Content-Type: application/json; charset=utf-8');

    // Simple auth check - just check if session exists
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
        exit;
    }

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON input']);
    exit;
}

$description = trim($input['description'] ?? '');
$style = trim($input['style'] ?? 'modern');

if (empty($description)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Description is required']);
    exit;
}

// OpenRouter API configuration
$apiKey = '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';
$model = 'deepseek/deepseek-chat-v3.1:free';
$apiUrl = 'https://openrouter.ai/api/v1/chat/completions';

// Fallback to working version if API fails
$useFallback = false;

// Prepare the enhanced prompt for AI
$styleDescriptions = [
    'modern' => 'современный и чистый дизайн с яркими акцентами',
    'minimalist' => 'минималистичный дизайн с большим количеством белого пространства',
    'corporate' => 'профессиональный корпоративный стиль',
    'creative' => 'креативный и художественный подход',
    'elegant' => 'элегантный и изысканный дизайн'
];

$styleDesc = $styleDescriptions[$style] ?? 'современный дизайн';

// Get current language for AI generation
$currentLang = 'ru'; // Default
if (isset($_SESSION['language'])) {
    $currentLang = $_SESSION['language'];
}

// Language-specific prompts
$languagePrompts = [
    'ru' => "Ты - эксперт по созданию веб-сайтов. Пользователь хочет создать сайт со следующим описанием: \"$description\"\n\nСтиль дизайна: $styleDesc",
    'en' => "You are a web development expert. The user wants to create a website with the following description: \"$description\"\n\nDesign style: $styleDesc",
    'ua' => "Ти - експерт зі створення веб-сайтів. Користувач хоче створити сайт з наступним описом: \"$description\"\n\nСтиль дизайну: $styleDesc",
    'pl' => "Jesteś ekspertem w tworzeniu stron internetowych. Użytkownik chce stworzyć stronę z następującym opisem: \"$description\"\n\nStyl designu: $styleDesc"
];

$prompt = $languagePrompts[$currentLang] ?? $languagePrompts['ru'];

$prompt .= "

Твоя задача - создать логичную и полную структуру элементов для конструктора сайтов. Доступные типы элементов:
- text: текстовые блоки (заголовки, описания, параграфы)
- button: кнопки действий
- image: изображения и медиа
- block: декоративные блоки
- group: группы элементов для организации контента
- product: карточки товаров/услуг
- link: ссылки
- list: списки
- separator: разделители секций

ВАЖНО: Верни ТОЛЬКО валидный JSON массив без дополнительного текста. Каждый объект должен содержать:
- type: тип элемента (обязательно один из доступных)
- content: конкретное содержимое (не общие фразы, а реальный текст)
- style: стиль элемента ('header', 'subtitle', 'description', 'button-primary', 'button-secondary')

Создай структуру из 5-8 элементов, включая:
1. Заголовок страницы
2. Описание/подзаголовок
3. Основной контент (текст, изображения, продукты)
4. Призыв к действию (кнопка)
5. Дополнительную информацию при необходимости

Пример правильного ответа:
[
  {\"type\": \"text\", \"content\": \"Профессиональная разработка сайтов\", \"style\": \"header\"},
  {\"type\": \"text\", \"content\": \"Создаем современные веб-решения для вашего бизнеса\", \"style\": \"subtitle\"},
  {\"type\": \"image\", \"content\": \"Изображение команды разработчиков\", \"style\": \"default\"},
  {\"type\": \"text\", \"content\": \"Наша команда имеет более 5 лет опыта в создании качественных веб-сайтов\", \"style\": \"description\"},
  {\"type\": \"button\", \"content\": \"Заказать консультацию\", \"style\": \"button-primary\"}
]";

// Prepare the request data
$requestData = [
    'model' => $model,
    'messages' => [
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'max_tokens' => 1000,
    'temperature' => 0.7
];

// Make the API request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json',
    'HTTP-Referer: ' . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'https://localhost'),
    'X-Title: JustSite AI Helper'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    error_log("OpenRouter API cURL error: " . $curlError);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'API connection error']);
    exit;
}

if ($httpCode !== 200) {
    error_log("OpenRouter API HTTP error: " . $httpCode . " Response: " . $response);
    $useFallback = true;
}

$apiResponse = json_decode($response, true);
if (!$apiResponse || !isset($apiResponse['choices'][0]['message']['content'])) {
    error_log("OpenRouter API invalid response: " . $response);
    $useFallback = true;
}

// Process API response or use fallback
$elements = [];
$jsonString = '';

if (!$useFallback) {
    $aiContent = trim($apiResponse['choices'][0]['message']['content']);
    
    // Try to extract and parse JSON from the response
    if (preg_match('/\[[\s\S]*\]/s', $aiContent, $matches)) {
        $jsonString = $matches[0];
        $elements = json_decode($jsonString, true);
        
        // Log the parsing attempt
        error_log("AI JSON parsing attempt: " . $jsonString);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON parsing error: " . json_last_error_msg());
            $useFallback = true;
        }
    } else {
        $useFallback = true;
    }
}

// Validate and clean the elements
if (is_array($elements) && !empty($elements)) {
    $validElements = [];
    $validTypes = ['text', 'button', 'image', 'block', 'group', 'product', 'link', 'list', 'separator'];
    
    foreach ($elements as $element) {
        if (is_array($element) && 
            isset($element['type']) && 
            in_array($element['type'], $validTypes) &&
            !empty($element['content'])) {
            
            $validElements[] = [
                'type' => $element['type'],
                'content' => trim($element['content']),
                'style' => $element['style'] ?? 'default'
            ];
        }
    }
    
    if (!empty($validElements)) {
        $elements = $validElements;
    } else {
        $elements = [];
    }
}

// Enhanced fallback based on description analysis
if (empty($elements) || $useFallback) {
    error_log("AI generation failed, using enhanced fallback for: " . $description);
    
    // Analyze description for better fallback
    $descLower = strtolower($description);
    $elements = [];
    
    // Language-specific fallback content
    $fallbackContent = [
        'ru' => [
            'ecommerce' => [
                'header' => 'Добро пожаловать в наш интернет-магазин',
                'subtitle' => 'Качественные товары по доступным ценам',
                'button' => 'Перейти к каталогу',
                'description' => 'Быстрая доставка по всей стране'
            ],
            'restaurant' => [
                'header' => 'Добро пожаловать в наш ресторан',
                'subtitle' => 'Изысканная кухня и уютная атмосфера',
                'button' => 'Забронировать столик',
                'description' => 'Наше меню включает блюда европейской и азиатской кухни'
            ],
            'portfolio' => [
                'header' => 'Мое портфолио',
                'subtitle' => 'Творческие работы и проекты',
                'button' => 'Связаться со мной',
                'description' => 'Профессиональный подход к каждому проекту'
            ],
            'business' => [
                'header' => 'Наша компания',
                'subtitle' => 'Профессиональные услуги для вашего бизнеса',
                'button' => 'Получить консультацию',
                'description' => 'Мы предоставляем качественные услуги уже более 5 лет'
            ],
            'generic' => [
                'header' => 'Добро пожаловать на наш сайт',
                'subtitle' => 'Мы рады видеть вас здесь',
                'button' => 'Узнать больше',
                'description' => 'Здесь вы найдете всю необходимую информацию'
            ]
        ],
        'en' => [
            'ecommerce' => [
                'header' => 'Welcome to our online store',
                'subtitle' => 'Quality products at affordable prices',
                'button' => 'Browse catalog',
                'description' => 'Fast delivery nationwide'
            ],
            'restaurant' => [
                'header' => 'Welcome to our restaurant',
                'subtitle' => 'Exquisite cuisine and cozy atmosphere',
                'button' => 'Reserve a table',
                'description' => 'Our menu includes European and Asian dishes'
            ],
            'portfolio' => [
                'header' => 'My portfolio',
                'subtitle' => 'Creative works and projects',
                'button' => 'Contact me',
                'description' => 'Professional approach to every project'
            ],
            'business' => [
                'header' => 'Our company',
                'subtitle' => 'Professional services for your business',
                'button' => 'Get consultation',
                'description' => 'We have been providing quality services for over 5 years'
            ],
            'generic' => [
                'header' => 'Welcome to our website',
                'subtitle' => 'We are glad to see you here',
                'button' => 'Learn more',
                'description' => 'Here you will find all the necessary information'
            ]
        ],
        'ua' => [
            'ecommerce' => [
                'header' => 'Ласкаво просимо до нашого інтернет-магазину',
                'subtitle' => 'Якісні товари за доступними цінами',
                'button' => 'Перейти до каталогу',
                'description' => 'Швидка доставка по всій країні'
            ],
            'restaurant' => [
                'header' => 'Ласкаво просимо до нашого ресторану',
                'subtitle' => 'Вишукана кухня та затишна атмосфера',
                'button' => 'Забронювати столик',
                'description' => 'Наше меню включає страви європейської та азійської кухні'
            ],
            'portfolio' => [
                'header' => 'Моє портфоліо',
                'subtitle' => 'Творчі роботи та проекти',
                'button' => 'Зв\'язатися зі мною',
                'description' => 'Професійний підхід до кожного проекту'
            ],
            'business' => [
                'header' => 'Наша компанія',
                'subtitle' => 'Професійні послуги для вашого бізнесу',
                'button' => 'Отримати консультацію',
                'description' => 'Ми надаємо якісні послуги вже понад 5 років'
            ],
            'generic' => [
                'header' => 'Ласкаво просимо на наш сайт',
                'subtitle' => 'Ми раді бачити вас тут',
                'button' => 'Дізнатися більше',
                'description' => 'Тут ви знайдете всю необхідну інформацію'
            ]
        ],
        'pl' => [
            'ecommerce' => [
                'header' => 'Witamy w naszym sklepie internetowym',
                'subtitle' => 'Jakościowe produkty w przystępnych cenach',
                'button' => 'Przejdź do katalogu',
                'description' => 'Szybka dostawa w całym kraju'
            ],
            'restaurant' => [
                'header' => 'Witamy w naszej restauracji',
                'subtitle' => 'Wykwintna kuchnia i przytulna atmosfera',
                'button' => 'Zarezerwuj stolik',
                'description' => 'Nasze menu zawiera dania kuchni europejskiej i azjatyckiej'
            ],
            'portfolio' => [
                'header' => 'Moje portfolio',
                'subtitle' => 'Kreatywne prace i projekty',
                'button' => 'Skontaktuj się ze mną',
                'description' => 'Profesjonalne podejście do każdego projektu'
            ],
            'business' => [
                'header' => 'Nasza firma',
                'subtitle' => 'Profesjonalne usługi dla Twojego biznesu',
                'button' => 'Uzyskaj konsultację',
                'description' => 'Świadczymy jakościowe usługi od ponad 5 lat'
            ],
            'generic' => [
                'header' => 'Witamy na naszej stronie',
                'subtitle' => 'Cieszymy się, że nas odwiedzasz',
                'button' => 'Dowiedz się więcej',
                'description' => 'Tutaj znajdziesz wszystkie niezbędne informacje'
            ]
        ]
    ];
    
    $content = $fallbackContent[$currentLang] ?? $fallbackContent['ru'];
    
    // Determine site type and create appropriate structure
    if (strpos($descLower, 'магазин') !== false || strpos($descLower, 'товар') !== false || strpos($descLower, 'продукт') !== false || 
        strpos($descLower, 'store') !== false || strpos($descLower, 'shop') !== false || strpos($descLower, 'product') !== false) {
        // E-commerce site
        $elements = [
            ['type' => 'text', 'content' => $content['ecommerce']['header'], 'style' => 'header'],
            ['type' => 'text', 'content' => $content['ecommerce']['subtitle'], 'style' => 'subtitle'],
            ['type' => 'product', 'content' => $content['ecommerce']['button'], 'style' => 'default'],
            ['type' => 'button', 'content' => $content['ecommerce']['button'], 'style' => 'button-primary'],
            ['type' => 'text', 'content' => $content['ecommerce']['description'], 'style' => 'description']
        ];
    } elseif (strpos($descLower, 'ресторан') !== false || strpos($descLower, 'кафе') !== false || strpos($descLower, 'меню') !== false ||
              strpos($descLower, 'restaurant') !== false || strpos($descLower, 'cafe') !== false || strpos($descLower, 'menu') !== false) {
        // Restaurant site
        $elements = [
            ['type' => 'text', 'content' => $content['restaurant']['header'], 'style' => 'header'],
            ['type' => 'text', 'content' => $content['restaurant']['subtitle'], 'style' => 'subtitle'],
            ['type' => 'image', 'content' => $content['restaurant']['description'], 'style' => 'default'],
            ['type' => 'text', 'content' => $content['restaurant']['description'], 'style' => 'description'],
            ['type' => 'button', 'content' => $content['restaurant']['button'], 'style' => 'button-primary']
        ];
    } elseif (strpos($descLower, 'портфолио') !== false || strpos($descLower, 'фотограф') !== false || strpos($descLower, 'дизайнер') !== false ||
              strpos($descLower, 'portfolio') !== false || strpos($descLower, 'photographer') !== false || strpos($descLower, 'designer') !== false) {
        // Portfolio site
        $elements = [
            ['type' => 'text', 'content' => $content['portfolio']['header'], 'style' => 'header'],
            ['type' => 'text', 'content' => $content['portfolio']['subtitle'], 'style' => 'subtitle'],
            ['type' => 'image', 'content' => $content['portfolio']['description'], 'style' => 'default'],
            ['type' => 'text', 'content' => $content['portfolio']['description'], 'style' => 'description'],
            ['type' => 'button', 'content' => $content['portfolio']['button'], 'style' => 'button-primary']
        ];
    } elseif (strpos($descLower, 'компания') !== false || strpos($descLower, 'бизнес') !== false || strpos($descLower, 'услуги') !== false ||
              strpos($descLower, 'company') !== false || strpos($descLower, 'business') !== false || strpos($descLower, 'services') !== false) {
        // Business site
        $elements = [
            ['type' => 'text', 'content' => $content['business']['header'], 'style' => 'header'],
            ['type' => 'text', 'content' => $content['business']['subtitle'], 'style' => 'subtitle'],
            ['type' => 'text', 'content' => $content['business']['description'], 'style' => 'description'],
            ['type' => 'list', 'content' => $content['business']['button'], 'style' => 'default'],
            ['type' => 'button', 'content' => $content['business']['button'], 'style' => 'button-primary']
        ];
    } else {
        // Generic site
        $elements = [
            ['type' => 'text', 'content' => $content['generic']['header'], 'style' => 'header'],
            ['type' => 'text', 'content' => $content['generic']['subtitle'], 'style' => 'subtitle'],
            ['type' => 'text', 'content' => $content['generic']['description'], 'style' => 'description'],
            ['type' => 'button', 'content' => $content['generic']['button'], 'style' => 'button-primary']
        ];
    }
}

// Validate and clean elements
$validTypes = ['text', 'button', 'image', 'block', 'group', 'product', 'link', 'list', 'separator'];
$cleanedElements = [];

foreach ($elements as $element) {
    if (is_array($element) && isset($element['type']) && in_array($element['type'], $validTypes)) {
        $cleanedElements[] = [
            'type' => $element['type'],
            'content' => $element['content'] ?? '',
            'style' => $element['style'] ?? 'default'
        ];
    }
}

if (empty($cleanedElements)) {
    // Ultimate fallback
    $cleanedElements = [
        ['type' => 'text', 'content' => 'Добро пожаловать!', 'style' => 'header'],
        ['type' => 'text', 'content' => 'Это ваш новый сайт', 'style' => 'subtitle'],
        ['type' => 'button', 'content' => 'Начать', 'style' => 'button-primary']
    ];
}

echo json_encode([
    'ok' => true,
    'elements' => $cleanedElements,
    'description' => $description,
    'style' => $style
]);

} catch (Exception $e) {
    error_log("AI Generate Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Exception: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("AI Generate Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Fatal Error: ' . $e->getMessage()]);
}
?>
