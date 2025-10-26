<?php
/**
 * Multilingual AI Content Generator
 * Generates website content based on user language
 */

class AIContentGenerator {
    
    private static $styleDescriptions = [
        'ru' => [
            'modern' => 'современный и чистый дизайн с яркими акцентами',
            'minimalist' => 'минималистичный дизайн с большим количеством белого пространства',
            'corporate' => 'профессиональный корпоративный стиль',
            'creative' => 'креативный и художественный подход',
            'elegant' => 'элегантный и изысканный дизайн'
        ],
        'en' => [
            'modern' => 'modern and clean design with bright accents',
            'minimalist' => 'minimalist design with lots of white space',
            'corporate' => 'professional corporate style',
            'creative' => 'creative and artistic approach',
            'elegant' => 'elegant and sophisticated design'
        ],
        'ua' => [
            'modern' => 'сучасний та чистий дизайн з яскравими акцентами',
            'minimalist' => 'мінімалістичний дизайн з великою кількістю білого простору',
            'corporate' => 'професійний корпоративний стиль',
            'creative' => 'креативний та художній підхід',
            'elegant' => 'елегантний та вишуканий дизайн'
        ],
        'pl' => [
            'modern' => 'nowoczesny i czysty design z jasnymi akcentami',
            'minimalist' => 'minimalistyczny design z dużą ilością białej przestrzeni',
            'corporate' => 'profesjonalny styl korporacyjny',
            'creative' => 'kreatywne i artystyczne podejście',
            'elegant' => 'elegancki i wyrafinowany design'
        ]
    ];
    
    private static $templates = [
        'ru' => [
            'ecommerce' => [
                ['type' => 'text', 'content' => 'Добро пожаловать в наш интернет-магазин', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Качественные товары по доступным ценам', 'style' => 'subtitle'],
                ['type' => 'product', 'content' => 'Популярный товар', 'style' => 'default'],
                ['type' => 'button', 'content' => 'Перейти к каталогу', 'style' => 'button-primary'],
                ['type' => 'text', 'content' => 'Быстрая доставка по всей стране', 'style' => 'description']
            ],
            'restaurant' => [
                ['type' => 'text', 'content' => 'Добро пожаловать в наш ресторан', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Изысканная кухня и уютная атмосфера', 'style' => 'subtitle'],
                ['type' => 'image', 'content' => 'Фото интерьера ресторана', 'style' => 'default'],
                ['type' => 'text', 'content' => 'Наше меню включает блюда европейской и азиатской кухни', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Забронировать столик', 'style' => 'button-primary']
            ],
            'portfolio' => [
                ['type' => 'text', 'content' => 'Мое портфолио', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Творческие работы и проекты', 'style' => 'subtitle'],
                ['type' => 'image', 'content' => 'Примеры работ', 'style' => 'default'],
                ['type' => 'text', 'content' => 'Профессиональный подход к каждому проекту', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Связаться со мной', 'style' => 'button-primary']
            ],
            'business' => [
                ['type' => 'text', 'content' => 'Наша компания', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Профессиональные услуги для вашего бизнеса', 'style' => 'subtitle'],
                ['type' => 'text', 'content' => 'Мы предоставляем качественные услуги уже более 5 лет', 'style' => 'description'],
                ['type' => 'list', 'content' => 'Наши преимущества', 'style' => 'default'],
                ['type' => 'button', 'content' => 'Получить консультацию', 'style' => 'button-primary']
            ],
            'default' => [
                ['type' => 'text', 'content' => 'Добро пожаловать на наш сайт', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Мы рады видеть вас здесь', 'style' => 'subtitle'],
                ['type' => 'text', 'content' => 'Здесь вы найдете всю необходимую информацию', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Узнать больше', 'style' => 'button-primary']
            ]
        ],
        'en' => [
            'ecommerce' => [
                ['type' => 'text', 'content' => 'Welcome to our online store', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Quality products at affordable prices', 'style' => 'subtitle'],
                ['type' => 'product', 'content' => 'Popular product', 'style' => 'default'],
                ['type' => 'button', 'content' => 'Browse catalog', 'style' => 'button-primary'],
                ['type' => 'text', 'content' => 'Fast delivery nationwide', 'style' => 'description']
            ],
            'restaurant' => [
                ['type' => 'text', 'content' => 'Welcome to our restaurant', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Exquisite cuisine and cozy atmosphere', 'style' => 'subtitle'],
                ['type' => 'image', 'content' => 'Restaurant interior photo', 'style' => 'default'],
                ['type' => 'text', 'content' => 'Our menu includes European and Asian cuisine', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Reserve a table', 'style' => 'button-primary']
            ],
            'portfolio' => [
                ['type' => 'text', 'content' => 'My Portfolio', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Creative works and projects', 'style' => 'subtitle'],
                ['type' => 'image', 'content' => 'Work examples', 'style' => 'default'],
                ['type' => 'text', 'content' => 'Professional approach to every project', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Contact me', 'style' => 'button-primary']
            ],
            'business' => [
                ['type' => 'text', 'content' => 'Our Company', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Professional services for your business', 'style' => 'subtitle'],
                ['type' => 'text', 'content' => 'We have been providing quality services for over 5 years', 'style' => 'description'],
                ['type' => 'list', 'content' => 'Our advantages', 'style' => 'default'],
                ['type' => 'button', 'content' => 'Get consultation', 'style' => 'button-primary']
            ],
            'default' => [
                ['type' => 'text', 'content' => 'Welcome to our website', 'style' => 'header'],
                ['type' => 'text', 'content' => 'We are glad to see you here', 'style' => 'subtitle'],
                ['type' => 'text', 'content' => 'Here you will find all the necessary information', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Learn more', 'style' => 'button-primary']
            ]
        ],
        'ua' => [
            'ecommerce' => [
                ['type' => 'text', 'content' => 'Ласкаво просимо до нашого інтернет-магазину', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Якісні товари за доступними цінами', 'style' => 'subtitle'],
                ['type' => 'product', 'content' => 'Популярний товар', 'style' => 'default'],
                ['type' => 'button', 'content' => 'Переглянути каталог', 'style' => 'button-primary'],
                ['type' => 'text', 'content' => 'Швидка доставка по всій країні', 'style' => 'description']
            ],
            'restaurant' => [
                ['type' => 'text', 'content' => 'Ласкаво просимо до нашого ресторану', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Вишукана кухня та затишна атмосфера', 'style' => 'subtitle'],
                ['type' => 'image', 'content' => 'Фото інтер\'єру ресторану', 'style' => 'default'],
                ['type' => 'text', 'content' => 'Наше меню включає страви європейської та азіатської кухні', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Забронювати столик', 'style' => 'button-primary']
            ],
            'portfolio' => [
                ['type' => 'text', 'content' => 'Моє портфоліо', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Творчі роботи та проекти', 'style' => 'subtitle'],
                ['type' => 'image', 'content' => 'Приклади робіт', 'style' => 'default'],
                ['type' => 'text', 'content' => 'Професійний підхід до кожного проекту', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Зв\'язатися зі мною', 'style' => 'button-primary']
            ],
            'business' => [
                ['type' => 'text', 'content' => 'Наша компанія', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Професійні послуги для вашого бізнесу', 'style' => 'subtitle'],
                ['type' => 'text', 'content' => 'Ми надаємо якісні послуги вже понад 5 років', 'style' => 'description'],
                ['type' => 'list', 'content' => 'Наші переваги', 'style' => 'default'],
                ['type' => 'button', 'content' => 'Отримати консультацію', 'style' => 'button-primary']
            ],
            'default' => [
                ['type' => 'text', 'content' => 'Ласкаво просимо на наш сайт', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Ми раді бачити вас тут', 'style' => 'subtitle'],
                ['type' => 'text', 'content' => 'Тут ви знайдете всю необхідну інформацію', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Дізнатися більше', 'style' => 'button-primary']
            ]
        ],
        'pl' => [
            'ecommerce' => [
                ['type' => 'text', 'content' => 'Witamy w naszym sklepie internetowym', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Jakościowe produkty w przystępnych cenach', 'style' => 'subtitle'],
                ['type' => 'product', 'content' => 'Popularny produkt', 'style' => 'default'],
                ['type' => 'button', 'content' => 'Przeglądaj katalog', 'style' => 'button-primary'],
                ['type' => 'text', 'content' => 'Szybka dostawa w całym kraju', 'style' => 'description']
            ],
            'restaurant' => [
                ['type' => 'text', 'content' => 'Witamy w naszej restauracji', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Wykwintna kuchnia i przytulna atmosfera', 'style' => 'subtitle'],
                ['type' => 'image', 'content' => 'Zdjęcie wnętrza restauracji', 'style' => 'default'],
                ['type' => 'text', 'content' => 'Nasze menu obejmuje kuchnię europejską i azjatycką', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Zarezerwuj stolik', 'style' => 'button-primary']
            ],
            'portfolio' => [
                ['type' => 'text', 'content' => 'Moje Portfolio', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Kreatywne prace i projekty', 'style' => 'subtitle'],
                ['type' => 'image', 'content' => 'Przykłady prac', 'style' => 'default'],
                ['type' => 'text', 'content' => 'Profesjonalne podejście do każdego projektu', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Skontaktuj się ze mną', 'style' => 'button-primary']
            ],
            'business' => [
                ['type' => 'text', 'content' => 'Nasza Firma', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Profesjonalne usługi dla Twojego biznesu', 'style' => 'subtitle'],
                ['type' => 'text', 'content' => 'Świadczymy wysokiej jakości usługi od ponad 5 lat', 'style' => 'description'],
                ['type' => 'list', 'content' => 'Nasze zalety', 'style' => 'default'],
                ['type' => 'button', 'content' => 'Uzyskaj konsultację', 'style' => 'button-primary']
            ],
            'default' => [
                ['type' => 'text', 'content' => 'Witamy na naszej stronie', 'style' => 'header'],
                ['type' => 'text', 'content' => 'Cieszymy się, że nas odwiedzasz', 'style' => 'subtitle'],
                ['type' => 'text', 'content' => 'Tutaj znajdziesz wszystkie potrzebne informacje', 'style' => 'description'],
                ['type' => 'button', 'content' => 'Dowiedz się więcej', 'style' => 'button-primary']
            ]
        ]
    ];
    
    public static function generateContent($description, $style, $language = 'ru') {
        $description = strtolower(trim($description));
        
        // Get language-specific templates
        $templates = self::$templates[$language] ?? self::$templates['ru'];
        
        // Determine template based on description
        $template = 'default';
        
        if (strpos($description, 'магазин') !== false || strpos($description, 'товар') !== false || 
            strpos($description, 'shop') !== false || strpos($description, 'store') !== false ||
            strpos($description, 'ecommerce') !== false) {
            $template = 'ecommerce';
        } elseif (strpos($description, 'ресторан') !== false || strpos($description, 'кафе') !== false || 
                  strpos($description, 'restaurant') !== false || strpos($description, 'cafe') !== false) {
            $template = 'restaurant';
        } elseif (strpos($description, 'портфолио') !== false || strpos($description, 'фотограф') !== false || 
                  strpos($description, 'portfolio') !== false || strpos($description, 'photographer') !== false) {
            $template = 'portfolio';
        } elseif (strpos($description, 'компания') !== false || strpos($description, 'бизнес') !== false || 
                  strpos($description, 'company') !== false || strpos($description, 'business') !== false) {
            $template = 'business';
        }
        
        return $templates[$template] ?? $templates['default'];
    }
    
    public static function getStyleDescription($style, $language = 'ru') {
        $descriptions = self::$styleDescriptions[$language] ?? self::$styleDescriptions['ru'];
        return $descriptions[$style] ?? $descriptions['modern'];
    }
}
?>
