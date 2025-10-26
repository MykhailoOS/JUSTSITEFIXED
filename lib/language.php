<?php
/**
 * Language Management System
 * Handles multi-language support for JustSite
 */

class LanguageManager {
    private static $currentLanguage = 'ru';
    private static $availableLanguages = [
        'ru' => ['name' => 'RU', 'flag' => 'text', 'locale' => 'ru_RU'],
        'en' => ['name' => 'English', 'flag' => 'images/icons/eng.svg', 'locale' => 'en_US'],
        'ua' => ['name' => 'Українська', 'flag' => 'images/icons/ua.svg', 'locale' => 'uk_UA'],
        'pl' => ['name' => 'Polski', 'flag' => 'images/icons/pl.svg', 'locale' => 'pl_PL']
    ];
    
    private static $translations = [];
    
    /**
     * Initialize language system
     * Priority: 1. URL parameter, 2. Session, 3. Cookie, 4. Database, 5. Browser, 6. Default
     */
    public static function init() {
        // Set UTF-8 encoding
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');
        // Start session if not started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // 1. Check URL parameter (highest priority for manual switching)
        if (isset($_GET['lang']) && array_key_exists($_GET['lang'], self::$availableLanguages)) {
            self::$currentLanguage = $_GET['lang'];
            $_SESSION['language'] = self::$currentLanguage;
            // Set cookie for 30 days
            setcookie('language', self::$currentLanguage, time() + (30 * 24 * 60 * 60), '/');
        }
        // 2. Check session
        elseif (isset($_SESSION['language']) && array_key_exists($_SESSION['language'], self::$availableLanguages)) {
            self::$currentLanguage = $_SESSION['language'];
        }
        // 3. Check cookie
        elseif (isset($_COOKIE['language']) && array_key_exists($_COOKIE['language'], self::$availableLanguages)) {
            self::$currentLanguage = $_COOKIE['language'];
            $_SESSION['language'] = self::$currentLanguage;
        }
        // 4. Try to get language from database if user is logged in
        elseif (isset($_SESSION['user_id'])) {
            try {
                require_once __DIR__ . '/db.php';
                $pdo = get_db_connection();
                $stmt = $pdo->prepare("SELECT language FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if ($user && $user['language'] && array_key_exists($user['language'], self::$availableLanguages)) {
                    self::$currentLanguage = $user['language'];
                    $_SESSION['language'] = self::$currentLanguage;
                    // Set cookie for 30 days
                    setcookie('language', self::$currentLanguage, time() + (30 * 24 * 60 * 60), '/');
                }
            } catch (Exception $e) {
                // If database fails, continue with browser detection
                error_log("Language detection DB error: " . $e->getMessage());
            }
        }
        
        // 5. Try browser language (Accept-Language header)
        if (self::$currentLanguage === 'ru' && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptedLanguages = self::parseAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($acceptedLanguages as $lang) {
                if (array_key_exists($lang, self::$availableLanguages)) {
                    self::$currentLanguage = $lang;
                    $_SESSION['language'] = self::$currentLanguage;
                    // Set cookie for 30 days
                    setcookie('language', self::$currentLanguage, time() + (30 * 24 * 60 * 60), '/');
                    break;
                }
            }
        }
        
        // Load translations
        self::loadTranslations();
        
        // Set locale
        self::setLocale();
    }
    
    /**
     * Parse Accept-Language header
     * @param string $acceptLanguage
     * @return array Sorted array of language codes by preference
     */
    private static function parseAcceptLanguage($acceptLanguage) {
        $languages = [];
        
        // Parse the Accept-Language header
        $parts = explode(',', $acceptLanguage);
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^([a-z]{2})(?:-[A-Z]{2})?(?:;q=([0-9.]+))?$/', $part, $matches)) {
                $lang = $matches[1];
                $quality = isset($matches[2]) ? (float)$matches[2] : 1.0;
                $languages[$lang] = $quality;
            }
        }
        
        // Sort by quality (preference)
        arsort($languages);
        
        return array_keys($languages);
    }
    
    /**
     * Load translation files
     */
    private static function loadTranslations() {
        $langFile = __DIR__ . '/../translations/' . self::$currentLanguage . '.php';
        if (file_exists($langFile)) {
            // Ensure UTF-8 encoding when loading file
            $content = file_get_contents($langFile);
            if (mb_check_encoding($content, 'UTF-8')) {
                self::$translations = include $langFile;
            } else {
                // Convert to UTF-8 if needed
                $content = mb_convert_encoding($content, 'UTF-8', 'auto');
                file_put_contents($langFile, $content);
                self::$translations = include $langFile;
            }
        } else {
            // Fallback to Russian
            $fallbackFile = __DIR__ . '/../translations/ru.php';
            if (file_exists($fallbackFile)) {
                self::$translations = include $fallbackFile;
            }
        }
    }
    
    /**
     * Set locale for the current language
     */
    private static function setLocale() {
        $locale = self::$availableLanguages[self::$currentLanguage]['locale'];
        setlocale(LC_ALL, $locale . '.UTF-8', $locale, 'C');
    }
    
    /**
     * Get translation for a key
     */
    public static function t($key, $params = []) {
        $translation = self::$translations[$key] ?? $key;
        
        // Replace parameters
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace('{' . $param . '}', $value, $translation);
            }
        }
        
        return $translation;
    }
    
    /**
     * Get current language
     */
    public static function getCurrentLanguage() {
        return self::$currentLanguage;
    }
    
    /**
     * Get available languages
     */
    public static function getAvailableLanguages() {
        return self::$availableLanguages;
    }
    
    /**
     * Get current language info
     */
    public static function getCurrentLanguageInfo() {
        return self::$availableLanguages[self::$currentLanguage];
    }
    
    /**
     * Change language
     */
    public static function changeLanguage($lang) {
        if (array_key_exists($lang, self::$availableLanguages)) {
            $_SESSION['language'] = $lang;
            self::$currentLanguage = $lang;
            self::loadTranslations();
            self::setLocale();
            return true;
        }
        return false;
    }
    
    /**
     * Get language selector HTML
     */
    public static function getLanguageSelector() {
        $current = self::getCurrentLanguageInfo();
        $languages = self::getAvailableLanguages();
        
        $html = '<div class="language-selector">';
        $html .= '<button class="language-current" id="languageToggle">';
        
        if ($current['flag'] === 'text') {
            $html .= '<span class="language-flag-text">' . $current['name'] . '</span>';
        } else {
            $html .= '<img src="' . $current['flag'] . '" alt="' . $current['name'] . '" class="language-flag-icon">';
        }
        
        $html .= '<svg class="language-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none">';
        $html .= '<path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>';
        $html .= '</svg>';
        $html .= '</button>';
        
        $html .= '<div class="language-dropdown" id="languageDropdown">';
        foreach ($languages as $code => $info) {
            $active = $code === self::$currentLanguage ? ' active' : '';
            $html .= '<a href="?lang=' . $code . '" class="language-option' . $active . '">';
            
            if ($info['flag'] === 'text') {
                $html .= '<span class="language-flag-text">' . $info['name'] . '</span>';
            } else {
                $html .= '<img src="' . $info['flag'] . '" alt="' . $info['name'] . '" class="language-flag-icon">';
            }
            
            $html .= '<span class="language-name">' . $info['name'] . '</span>';
            $html .= '</a>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}

// Initialize if not already done
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

LanguageManager::init();
?>
