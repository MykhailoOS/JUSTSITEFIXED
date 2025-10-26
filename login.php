<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/language.php';

// Initialize language system
LanguageManager::init();

$errors = [];
$successMessage = '';

// Check for logout success message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $successMessage = LanguageManager::t('success_logout');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        $pdo = DatabaseConnectionProvider::getConnection();
        $user = find_user_by_email($pdo, $email);
        if (!$user || !verify_user_password($password, $user['password_hash'])) {
            $errors[] = LanguageManager::t('error_invalid_credentials');
        } else {
            login_user((int)$user['id']);
            header('Location: index.php');
            exit;
        }
    } catch (Throwable $e) {
        $errors[] = LanguageManager::t('error_server') . ': ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo LanguageManager::t('login_title'); ?> — <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/main.css?v=<?php echo time(); ?>">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #1e40af 100%);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr;
            overflow: hidden;
            position: relative;
        }

        @media (min-width: 768px) {
            body {
                grid-template-columns: 1fr 1fr;
            }
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .left-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 4rem;
            color: white;
            position: relative;
            z-index: 1;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(45deg, #60a5fa, #a78bfa);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
            color: white;
        }

        .brand-text {
            font-size: 2rem;
            font-weight: 800;
        }

        .welcome-text {
            margin-bottom: 2rem;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }

        .right-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .auth-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .auth-logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            font-weight: 700;
        }
        
        .auth-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 8px 0;
            letter-spacing: -0.02em;
        }
        
        .auth-subtitle {
            font-size: 16px;
            color: #6b7280;
            margin: 0;
            font-weight: 400;
        }
        
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-input {
            padding: 16px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
            background: white;
            transition: all 0.2s ease;
            outline: none;
        }
        
        .form-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-input::placeholder {
            color: #9ca3af;
        }
        
        .auth-button {
            padding: 16px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }
        
        .auth-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.3);
        }
        
        .auth-button:active {
            transform: translateY(0);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        
        .auth-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .auth-link:hover {
            color: #5a67d8;
        }
        
        .error-message {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 1px solid #f87171;
            color: #dc2626;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .success-message {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 1px solid #34d399;
            color: #065f46;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .back-link {
            position: absolute;
            top: 24px;
            left: 24px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
        }
        
        .back-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        @media (max-width: 640px) {
            .auth-container {
                padding: 32px 24px;
                margin: 16px;
                border-radius: 20px;
            }
            
            .auth-title {
                font-size: 28px;
            }
            
            .back-link {
                top: 16px;
                left: 16px;
            }
        }
        .auth-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.3);
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        .auth-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .auth-links a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .success-message {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                grid-template-columns: 1fr;
                padding: 1rem;
            }

            .left-panel {
                display: none;
            }

            .right-panel {
                padding: 1rem;
            }

            .auth-container {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="left-panel">
        <div class="brand">
            <div class="brand-icon">J</div>
            <div class="brand-text">JustSite</div>
        </div>
        
        <div class="welcome-text">
            <h1 class="welcome-title">Добро пожаловать<br>обратно</h1>
            <p class="welcome-subtitle">Войдите в свой аккаунт и продолжайте создавать потрясающие сайты</p>
        </div>
    </div>

    <div class="right-panel">
        <div class="auth-container">
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <input class="form-input" type="email" name="email" placeholder="Username/Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <input class="form-input" type="password" name="password" placeholder="Password" required>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <button class="login-btn" type="submit">Login</button>
            </form>
            
            <div class="auth-links">
                <a href="#" onclick="alert('Функция восстановления пароля будет добавлена позже')">Forgot password?</a>
                <a href="register.php">Register</a>
            </div>
        </div>
    </div>
</body>
</html>


