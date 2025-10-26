<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

// Check if user is logged in and is admin
$uid = current_user_id();
if (!$uid) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
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
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

// Sample templates data
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

try {
    // Clear existing templates
    $pdo->exec('DELETE FROM templates');
    
    // Insert sample templates
    $stmt = $pdo->prepare('
        INSERT INTO templates (name, description, category, type, template, sample_data, status, user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
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
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Sample templates created successfully',
        'count' => count($sampleTemplates)
    ]);
    
} catch (Exception $e) {
    error_log('Error seeding templates: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error creating sample templates: ' . $e->getMessage()
    ]);
}
?>
