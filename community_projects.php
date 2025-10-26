<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/language.php';

// Initialize language system
LanguageManager::init();

start_session_if_needed();
header('Content-Type: text/html; charset=utf-8');

// Check if user is logged in
$uid = current_user_id();
if (!$uid) {
    header('Location: login.php');
    exit;
}

$pdo = DatabaseConnectionProvider::getConnection();

// Handle search and filters (for AJAX)
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query
$sql = 'SELECT p.*, u.name as author_name, u.email as author_email 
        FROM projects p 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE p.community_featured = 1';
$params = [];

if ($search) {
    $sql .= ' AND (p.title LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= ' AND p.category = ?';
    $params[] = $category;
}

switch ($sort) {
    case 'popular':
        $sql .= ' ORDER BY p.community_downloads DESC, p.created_at DESC';
        break;
    case 'oldest':
        $sql .= ' ORDER BY p.created_at ASC';
        break;
    default: // newest
        $sql .= ' ORDER BY p.created_at DESC';
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="<?php echo LanguageManager::getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Projects — JustSite</title>
    
    <!-- Material Design Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .search-container {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }
        
        .project-card {
            transition: all 0.3s ease;
            border-radius: 16px;
            overflow: hidden;
            background: white;
            height: 100%;
        }
        
        .project-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .project-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }
        
        .category-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255,255,255,0.95);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            background: #f0f2f5;
            border-radius: 12px;
            font-size: 12px;
            color: #65676b;
        }
        
        .copy-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .copy-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .search-input {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .filter-btn {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .filter-btn:hover, .filter-btn.active {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .loading {
            text-align: center;
            padding: 60px 20px;
            color: white;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .header-gradient {
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .empty-state-icon {
            font-size: 72px;
            color: #667eea;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header-gradient text-center">
            <h1 class="display-4 fw-bold mb-3">
                <span class="material-icons align-middle" style="font-size: 48px; color: #667eea;">public</span>
                Community Projects
            </h1>
            <p class="lead text-muted">Discover amazing projects created by our community</p>
        </div>

        <!-- Search and Filter -->
        <div class="search-container">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0">
                            <span class="material-icons">search</span>
                        </span>
                        <input type="text" id="searchInput" class="form-control search-input border-start-0" placeholder="Search projects...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="categoryFilter" class="form-select filter-btn">
                        <option value="">All Categories</option>
                        <option value="landing">🚀 Landing Page</option>
                        <option value="blog">📝 Blog</option>
                        <option value="portfolio">💼 Portfolio</option>
                        <option value="ecommerce">🛒 E-commerce</option>
                        <option value="business">🏢 Business</option>
                        <option value="personal">👤 Personal</option>
                        <option value="general">⭐ General</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="sortFilter" class="form-select filter-btn">
                        <option value="newest">Newest First</option>
                        <option value="popular">Most Popular</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button onclick="applyFilters()" class="btn w-100 filter-btn active">
                        <span class="material-icons align-middle">filter_alt</span> Apply
                    </button>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div id="projectsContainer" class="row g-4">
            <div class="col-12">
                <div class="loading">
                    <div class="spinner"></div>
                    <h4>Loading projects...</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Material Design Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    
    <script>
        let allProjects = <?php echo json_encode($projects); ?>;
        
        // Load projects on page load
        document.addEventListener('DOMContentLoaded', function() {
            displayProjects(allProjects);
        });
        
        // Display projects
        function displayProjects(projects) {
            const container = document.getElementById('projectsContainer');
            
            if (projects.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <span class="material-icons" style="font-size: 72px;">inbox</span>
                            </div>
                            <h3>No projects found</h3>
                            <p class="text-muted">Try adjusting your search or filters</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = projects.map(project => {
                const categoryEmoji = {
                    'landing': '🚀',
                    'blog': '📝',
                    'portfolio': '💼',
                    'ecommerce': '🛒',
                    'business': '🏢',
                    'personal': '👤',
                    'general': '⭐'
                };
                
                return `
                    <div class="col-lg-4 col-md-6">
                        <div class="card project-card border-0 shadow">
                            <div class="position-relative">
                                <div class="project-image">
                                    <span class="material-icons" style="font-size: 72px;">${categoryEmoji[project.category] || '📄'}</span>
                                </div>
                                <span class="category-badge">${project.category || 'General'}</span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-2">${escapeHtml(project.title)}</h5>
                                <p class="card-text text-muted small mb-3">${escapeHtml(project.description || 'No description')}</p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="stats-badge">
                                        <span class="material-icons" style="font-size: 14px;">person</span>
                                        ${escapeHtml(project.author_name || 'Anonymous')}
                                    </span>
                                    <span class="stats-badge">
                                        <span class="material-icons" style="font-size: 14px;">download</span>
                                        ${project.community_downloads || 0}
                                    </span>
                                    <span class="stats-badge">
                                        <span class="material-icons" style="font-size: 14px;">event</span>
                                        ${new Date(project.created_at).toLocaleDateString()}
                                    </span>
                                </div>
                                
                                <button onclick="copyProject(${project.id})" class="copy-btn">
                                    <span class="material-icons align-middle" style="font-size: 18px;">content_copy</span>
                                    Copy Project
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Apply filters
        function applyFilters() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const category = document.getElementById('categoryFilter').value;
            const sort = document.getElementById('sortFilter').value;
            
            let filtered = allProjects.filter(project => {
                const matchesSearch = search === '' || 
                    project.title.toLowerCase().includes(search) || 
                    (project.description && project.description.toLowerCase().includes(search));
                const matchesCategory = category === '' || project.category === category;
                return matchesSearch && matchesCategory;
            });
            
            // Sort
            if (sort === 'popular') {
                filtered.sort((a, b) => (b.community_downloads || 0) - (a.community_downloads || 0));
            } else if (sort === 'oldest') {
                filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            } else {
                filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            }
            
            displayProjects(filtered);
        }
        
        // Real-time search
        document.getElementById('searchInput').addEventListener('input', function() {
            applyFilters();
        });
        
        document.getElementById('categoryFilter').addEventListener('change', function() {
            applyFilters();
        });
        
        document.getElementById('sortFilter').addEventListener('change', function() {
            applyFilters();
        });
        
        // Copy project
        function copyProject(projectId) {
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="material-icons align-middle animate-spin">refresh</span> Copying...';
            btn.disabled = true;
            
            fetch('api/projects_community.php?id=' + projectId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.project) {
                        if (window.opener && window.opener.createProjectFromCommunity) {
                            window.opener.createProjectFromCommunity(data.project);
                            showNotification('Project copied successfully! Redirecting...', 'success');
                            setTimeout(() => window.close(), 1500);
                        } else {
                            // Store in localStorage and redirect
                            localStorage.setItem('communityProject', JSON.stringify(data.project));
                            showNotification('Project copied successfully! Redirecting...', 'success');
                            setTimeout(() => {
                                window.location.href = 'index.php';
                            }, 1500);
                        }
                    } else {
                        showNotification('Error loading project: ' + (data.message || 'Unknown error'), 'error');
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error copying project', 'error');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
        }
        
        // Show notification
        function showNotification(message, type) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white border-0 position-fixed top-0 end-0 m-3';
            toast.style.zIndex = '9999';
            toast.style.background = type === 'success' ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : '#dc3545';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <span class="material-icons align-middle me-2">${type === 'success' ? 'check_circle' : 'error'}</span>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new mdb.Toast(toast);
            bsToast.show();
            setTimeout(() => toast.remove(), 5000);
        }
        
        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
