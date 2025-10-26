<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';

// Check if user is logged in and is admin
$uid = current_user_id();
if (!$uid) {
    header('Location: login.php');
    exit;
}

$pdo = DatabaseConnectionProvider::getConnection();
$stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

$isAdmin = $user && (
    strpos($user['email'], 'admin') !== false || 
    $user['email'] === 'admin@justsite.com' ||
    $uid == 1
);

if (!$isAdmin) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><title>403 - Access Denied</title></head><body><h1>403 - Access Denied</h1><p>Admin access required.</p></body></html>';
    exit;
}

// Initialize templates directly
try {
    // Include the seed templates logic directly
    $sampleTemplates = [
        [
            'name' => 'Hero Section',
            'description' => 'Modern hero section with call-to-action button',
            'category' => 'landing',
            'type' => 'component',
            'template' => '<section class="hero min-h-screen bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
    <div class="hero-content text-center text-white">
        <div class="max-w-md">
            <h1 class="text-5xl font-bold mb-4">{{title}}</h1>
            <p class="text-xl mb-8">{{subtitle}}</p>
            <button class="btn btn-primary btn-lg">{{buttonText}}</button>
        </div>
    </div>
</section>',
            'sample_data' => [
                'title' => 'Welcome to Our Platform',
                'subtitle' => 'Build amazing websites with our powerful tools',
                'buttonText' => 'Get Started'
            ],
            'status' => 'active'
        ],
        [
            'name' => 'Feature Cards',
            'description' => 'Responsive feature cards with icons and descriptions',
            'category' => 'landing',
            'type' => 'component',
            'template' => '<div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-12">
    {{#each features}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body text-center">
            <div class="text-4xl mb-4">{{icon}}</div>
            <h3 class="card-title justify-center mb-2">{{title}}</h3>
            <p class="text-gray-600">{{description}}</p>
        </div>
    </div>
    {{/each}}
</div>',
            'sample_data' => [
                'features' => [
                    [
                        'icon' => '🚀',
                        'title' => 'Fast Performance',
                        'description' => 'Lightning-fast loading times for better user experience'
                    ],
                    [
                        'icon' => '🔒',
                        'title' => 'Secure',
                        'description' => 'Enterprise-grade security to protect your data'
                    ],
                    [
                        'icon' => '📱',
                        'title' => 'Mobile Ready',
                        'description' => 'Responsive design that works on all devices'
                    ]
                ]
            ],
            'status' => 'active'
        ],
        [
            'name' => 'Blog Post Card',
            'description' => 'Blog post card with image, title, excerpt and metadata',
            'category' => 'blog',
            'type' => 'component',
            'template' => '<article class="card bg-base-100 shadow-xl">
    <figure>
        <img src="{{image}}" alt="{{title}}" class="w-full h-48 object-cover">
    </figure>
    <div class="card-body">
        <h2 class="card-title">{{title}}</h2>
        <p class="text-gray-600">{{excerpt}}</p>
        <div class="card-actions justify-between items-center mt-4">
            <div class="flex items-center space-x-4 text-sm text-gray-500">
                <span>👤 {{author}}</span>
                <span>📅 {{date}}</span>
                <span>👁️ {{views}} views</span>
            </div>
            <button class="btn btn-primary btn-sm">Read More</button>
        </div>
    </div>
</article>',
            'sample_data' => [
                'title' => 'Getting Started with Web Development',
                'excerpt' => 'Learn the fundamentals of modern web development with our comprehensive guide...',
                'image' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=400',
                'author' => 'John Doe',
                'date' => '2024-01-15',
                'views' => 1250
            ],
            'status' => 'active'
        ],
        [
            'name' => 'Pricing Table',
            'description' => 'Three-tier pricing table with features and call-to-action',
            'category' => 'landing',
            'type' => 'component',
            'template' => '<div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-12">
    {{#each plans}}
    <div class="card bg-base-100 shadow-xl {{#if featured}}ring-2 ring-primary{{/if}}">
        <div class="card-body text-center">
            {{#if featured}}
            <div class="badge badge-primary mb-4">Most Popular</div>
            {{/if}}
            <h3 class="text-2xl font-bold mb-2">{{name}}</h3>
            <div class="text-4xl font-bold mb-4">
                ${{price}}<span class="text-lg text-gray-500">/{{period}}</span>
            </div>
            <p class="text-gray-600 mb-6">{{description}}</p>
            <ul class="space-y-2 mb-6">
                {{#each features}}
                <li class="flex items-center">
                    <span class="text-green-500 mr-2">✓</span>
                    {{this}}
                </li>
                {{/each}}
            </ul>
            <button class="btn {{#if featured}}btn-primary{{else}}btn-outline{{/if}} w-full">
                {{buttonText}}
            </button>
        </div>
    </div>
    {{/each}}
</div>',
            'sample_data' => [
                'plans' => [
                    [
                        'name' => 'Starter',
                        'price' => '9',
                        'period' => 'month',
                        'description' => 'Perfect for individuals',
                        'features' => ['5 Projects', '10GB Storage', 'Email Support'],
                        'buttonText' => 'Get Started',
                        'featured' => false
                    ],
                    [
                        'name' => 'Professional',
                        'price' => '29',
                        'period' => 'month',
                        'description' => 'Best for growing businesses',
                        'features' => ['Unlimited Projects', '100GB Storage', 'Priority Support', 'Advanced Analytics'],
                        'buttonText' => 'Start Free Trial',
                        'featured' => true
                    ],
                    [
                        'name' => 'Enterprise',
                        'price' => '99',
                        'period' => 'month',
                        'description' => 'For large organizations',
                        'features' => ['Everything in Pro', 'Custom Integrations', 'Dedicated Support', 'SLA Guarantee'],
                        'buttonText' => 'Contact Sales',
                        'featured' => false
                    ]
                ]
            ],
            'status' => 'active'
        ],
        [
            'name' => 'Contact Form',
            'description' => 'Professional contact form with validation',
            'category' => 'landing',
            'type' => 'component',
            'template' => '<div class="card bg-base-100 shadow-xl max-w-2xl mx-auto">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-6">{{title}}</h2>
        <form class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">First Name</span>
                    </label>
                    <input type="text" class="input input-bordered" required>
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Last Name</span>
                    </label>
                    <input type="text" class="input input-bordered" required>
                </div>
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Email</span>
                </label>
                <input type="email" class="input input-bordered" required>
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Subject</span>
                </label>
                <input type="text" class="input input-bordered" required>
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Message</span>
                </label>
                <textarea class="textarea textarea-bordered h-32" required></textarea>
            </div>
            <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary">{{submitText}}</button>
            </div>
        </form>
    </div>
</div>',
            'sample_data' => [
                'title' => 'Get in Touch',
                'submitText' => 'Send Message'
            ],
            'status' => 'active'
        ]
    ];

    // Create templates table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100) DEFAULT 'landing',
            type VARCHAR(50) DEFAULT 'html',
            template LONGTEXT NOT NULL,
            sample_data JSON,
            status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
            downloads INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            user_id INT,
            INDEX idx_category (category),
            INDEX idx_type (type),
            INDEX idx_status (status),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Clear existing templates
    $pdo->exec('DELETE FROM templates');
    
    // Insert sample templates
    $stmt = $pdo->prepare('
        INSERT INTO templates (name, description, category, type, template, sample_data, status, user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $count = 0;
    foreach ($sampleTemplates as $template) {
        $stmt->execute([
            $template['name'],
            $template['description'],
            $template['category'],
            $template['type'],
            $template['template'],
            json_encode($template['sample_data']),
            $template['status'],
            $uid
        ]);
        $count++;
    }
    
    $data = [
        'success' => true,
        'message' => 'Sample templates created successfully',
        'count' => $count
    ];
    
} catch (Exception $e) {
    $data = [
        'success' => false,
        'message' => 'Error creating sample templates: ' . $e->getMessage()
    ];
}

if ($data && $data['success']) {
    $message = "✅ Successfully created {$data['count']} sample templates!";
    $type = 'success';
} else {
    $message = "❌ Error creating sample templates: " . ($data['message'] ?? 'Unknown error');
    $type = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initialize Templates</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center <?php echo $type === 'success' ? 'bg-green-100' : 'bg-red-100'; ?>">
                <?php if ($type === 'success'): ?>
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                <?php else: ?>
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                <?php endif; ?>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                <?php echo $type === 'success' ? 'Templates Initialized!' : 'Error'; ?>
            </h1>
            
            <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($message); ?></p>
            
            <div class="space-y-3">
                <a href="templates.php" class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                    View Templates
                </a>
                <a href="admin.php" class="block w-full bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                    Back to Admin
                </a>
            </div>
        </div>
    </div>
</body>
</html>
