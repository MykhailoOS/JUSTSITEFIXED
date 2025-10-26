<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
$uid = current_user_id();
if (!$uid) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGet($uid, $action);
            break;
        case 'POST':
            handlePost($uid);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log('Templates Constructor API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function handleGet($uid, $action) {
    $pdo = DatabaseConnectionProvider::getConnection();
    
    switch ($action) {
        case 'list':
            listTemplatesForConstructor($pdo);
            break;
        case 'get':
            getTemplateForConstructor($pdo);
            break;
        case 'preview':
            previewTemplateForConstructor($pdo);
            break;
        case 'categories':
            getTemplateCategories($pdo);
            break;
        default:
            listTemplatesForConstructor($pdo);
    }
}

function handlePost($uid) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        return;
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'insert':
            insertTemplateIntoProject($uid, $input);
            break;
        case 'render':
            renderTemplateWithData($input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function listTemplatesForConstructor($pdo) {
    try {
        $category = $_GET['category'] ?? '';
        $type = $_GET['type'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $sql = 'SELECT id, name, description, category, type, template, sample_data, downloads, created_at FROM templates WHERE status = "active"';
        $params = [];
        
        if ($category) {
            $sql .= ' AND category = ?';
            $params[] = $category;
        }
        
        if ($type) {
            $sql .= ' AND type = ?';
            $params[] = $type;
        }
        
        if ($search) {
            $sql .= ' AND (name LIKE ? OR description LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= ' ORDER BY downloads DESC, created_at DESC LIMIT 50';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process templates for constructor
        foreach ($templates as &$template) {
            // Decode sample data
            if ($template['sample_data']) {
                $template['sample_data'] = json_decode($template['sample_data'], true);
            }
            
            // Generate preview HTML
            $template['preview_html'] = generatePreviewHtml($template['template'], $template['sample_data']);
            
            // Add metadata for constructor
            $template['constructor_ready'] = true;
            $template['insertion_type'] = getInsertionType($template['type']);
        }
        
        echo json_encode([
            'success' => true,
            'templates' => $templates,
            'total' => count($templates)
        ]);
    } catch (Exception $e) {
        error_log('Error listing templates for constructor: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching templates']);
    }
}

function getTemplateForConstructor($pdo) {
    $id = $_GET['id'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('
            SELECT id, name, description, category, type, template, sample_data, downloads, created_at
            FROM templates 
            WHERE id = ? AND status = "active"
        ');
        $stmt->execute([$id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        // Decode sample data
        if ($template['sample_data']) {
            $template['sample_data'] = json_decode($template['sample_data'], true);
        }
        
        // Generate preview HTML
        $template['preview_html'] = generatePreviewHtml($template['template'], $template['sample_data']);
        
        // Add constructor metadata
        $template['constructor_ready'] = true;
        $template['insertion_type'] = getInsertionType($template['type']);
        $template['variables'] = extractTemplateVariables($template['template']);
        
        echo json_encode([
            'success' => true,
            'template' => $template
        ]);
    } catch (Exception $e) {
        error_log('Error getting template for constructor: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching template']);
    }
}

function previewTemplateForConstructor($pdo) {
    $id = $_GET['id'] ?? '';
    $data = $_GET['data'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('SELECT template FROM templates WHERE id = ? AND status = "active"');
        $stmt->execute([$id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        // Parse custom data if provided
        $templateData = [];
        if ($data) {
            $templateData = json_decode($data, true) ?: [];
        }
        
        // Generate HTML
        $html = generateHtmlFromTemplate($template['template'], $templateData);
        
        echo json_encode([
            'success' => true,
            'html' => $html,
            'template_id' => $id
        ]);
    } catch (Exception $e) {
        error_log('Error previewing template: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error generating preview']);
    }
}

function getTemplateCategories($pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT category, COUNT(*) as count 
            FROM templates 
            WHERE status = "active" 
            GROUP BY category 
            ORDER BY count DESC
        ');
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (Exception $e) {
        error_log('Error getting categories: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching categories']);
    }
}

function insertTemplateIntoProject($uid, $input) {
    $templateId = $input['template_id'] ?? '';
    $projectId = $input['project_id'] ?? '';
    $position = $input['position'] ?? 'end';
    $customData = $input['data'] ?? [];
    
    if (!$templateId || !$projectId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID and Project ID required']);
        return;
    }
    
    try {
        $pdo = DatabaseConnectionProvider::getConnection();
        
        // Get template
        $stmt = $pdo->prepare('SELECT template, sample_data FROM templates WHERE id = ? AND status = "active"');
        $stmt->execute([$templateId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        // Get project
        $stmt = $pdo->prepare('SELECT content FROM projects WHERE id = ? AND user_id = ?');
        $stmt->execute([$projectId, $uid]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            return;
        }
        
        // Merge custom data with sample data
        $sampleData = $template['sample_data'] ? json_decode($template['sample_data'], true) : [];
        $finalData = array_merge($sampleData, $customData);
        
        // Generate HTML from template
        $templateHtml = generateHtmlFromTemplate($template['template'], $finalData);
        
        // Insert into project content
        $currentContent = $project['content'];
        $newContent = '';
        
        if ($position === 'start') {
            $newContent = $templateHtml . "\n" . $currentContent;
        } else {
            $newContent = $currentContent . "\n" . $templateHtml;
        }
        
        // Update project
        $stmt = $pdo->prepare('UPDATE projects SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
        $stmt->execute([$newContent, $projectId, $uid]);
        
        // Increment template downloads
        $stmt = $pdo->prepare('UPDATE templates SET downloads = downloads + 1 WHERE id = ?');
        $stmt->execute([$templateId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Template inserted successfully',
            'html' => $templateHtml,
            'position' => $position
        ]);
    } catch (Exception $e) {
        error_log('Error inserting template: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error inserting template']);
    }
}

function renderTemplateWithData($input) {
    $templateId = $input['template_id'] ?? '';
    $data = $input['data'] ?? [];
    
    if (!$templateId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    try {
        $pdo = DatabaseConnectionProvider::getConnection();
        
        $stmt = $pdo->prepare('SELECT template FROM templates WHERE id = ? AND status = "active"');
        $stmt->execute([$templateId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        $html = generateHtmlFromTemplate($template['template'], $data);
        
        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
    } catch (Exception $e) {
        error_log('Error rendering template: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error rendering template']);
    }
}

function generatePreviewHtml($template, $data) {
    try {
        return generateHtmlFromTemplate($template, $data ?: []);
    } catch (Exception $e) {
        return '<div class="text-red-500 p-4">Error generating preview: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

function getInsertionType($type) {
    switch ($type) {
        case 'html':
            return 'full-page';
        case 'component':
            return 'component';
        case 'layout':
            return 'layout';
        default:
            return 'component';
    }
}

function extractTemplateVariables($template) {
    $variables = [];
    
    // Find all {{variable}} patterns
    preg_match_all('/\{\{([^}]+)\}\}/', $template, $matches);
    
    foreach ($matches[1] as $match) {
        $var = trim($match);
        if (!in_array($var, $variables)) {
            $variables[] = $var;
        }
    }
    
    return $variables;
}

function generateHtmlFromTemplate($template, $data) {
    // Simple Handlebars-like template engine
    $html = $template;
    
    // Replace {{variable}} with data
    $html = preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($data) {
        $key = trim($matches[1]);
        
        // Handle nested properties (e.g., user.name)
        $keys = explode('.', $key);
        $value = $data;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return ''; // Property not found
            }
        }
        
        return htmlspecialchars($value);
    }, $html);
    
    // Handle {{#each}} loops
    $html = preg_replace_callback('/\{\{#each\s+([^}]+)\}\}(.*?)\{\{\/each\}\}/s', function($matches) use ($data) {
        $arrayKey = trim($matches[1]);
        $template = $matches[2];
        
        if (!isset($data[$arrayKey]) || !is_array($data[$arrayKey])) {
            return '';
        }
        
        $result = '';
        foreach ($data[$arrayKey] as $item) {
            $itemHtml = $template;
            $itemHtml = preg_replace_callback('/\{\{([^}]+)\}\}/', function($itemMatches) use ($item) {
                $key = trim($itemMatches[1]);
                return htmlspecialchars($item[$key] ?? '');
            }, $itemHtml);
            $result .= $itemHtml;
        }
        
        return $result;
    }, $html);
    
    // Handle {{#if}} conditionals
    $html = preg_replace_callback('/\{\{#if\s+([^}]+)\}\}(.*?)\{\{\/if\}\}/s', function($matches) use ($data) {
        $condition = trim($matches[1]);
        $template = $matches[2];
        
        // Simple condition evaluation
        $keys = explode('.', $condition);
        $value = $data;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return ''; // Condition not met
            }
        }
        
        return $value ? $template : '';
    }, $html);
    
    return $html;
}
?>
