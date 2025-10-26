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

// Simple admin check
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

// Create templates table if it doesn't exist
try {
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
} catch (Exception $e) {
    error_log('Error creating templates table: ' . $e->getMessage());
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo, $action);
            break;
        case 'POST':
            handlePost($pdo);
            break;
        case 'PUT':
            handlePut($pdo);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log('Templates API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function handleGet($pdo, $action) {
    switch ($action) {
        case 'list':
            listTemplates($pdo);
            break;
        case 'get':
            getTemplate($pdo);
            break;
        case 'download':
            downloadTemplate($pdo);
            break;
        case 'export':
            exportTemplates($pdo);
            break;
        case 'preview':
            previewTemplate($pdo);
            break;
        default:
            listTemplates($pdo);
    }
}

function handlePost($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        return;
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'create':
            createTemplate($pdo, $input);
            break;
        case 'update':
            updateTemplate($pdo, $input);
            break;
        case 'duplicate':
            duplicateTemplate($pdo, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePut($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        return;
    }
    
    updateTemplate($pdo, $input);
}

function handleDelete($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    deleteTemplate($pdo, $input['id']);
}

function listTemplates($pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT 
                id, name, description, category, type, template, sample_data, 
                status, downloads, created_at, updated_at, user_id
            FROM templates 
            ORDER BY created_at DESC
        ');
        $stmt->execute();
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON fields
        foreach ($templates as &$template) {
            if ($template['sample_data']) {
                $template['sample_data'] = json_decode($template['sample_data'], true);
            }
        }
        
        echo json_encode([
            'success' => true,
            'templates' => $templates
        ]);
    } catch (Exception $e) {
        error_log('Error listing templates: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching templates']);
    }
}

function getTemplate($pdo) {
    $id = $_GET['id'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                id, name, description, category, type, template, sample_data, 
                status, downloads, created_at, updated_at, user_id
            FROM templates 
            WHERE id = ?
        ');
        $stmt->execute([$id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        if ($template['sample_data']) {
            $template['sample_data'] = json_decode($template['sample_data'], true);
        }
        
        echo json_encode([
            'success' => true,
            'template' => $template
        ]);
    } catch (Exception $e) {
        error_log('Error getting template: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching template']);
    }
}

function createTemplate($pdo, $input) {
    $template = $input['template'] ?? [];
    
    if (!$template || !isset($template['name']) || !isset($template['template'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name and template content are required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO templates (name, description, category, type, template, sample_data, status, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $sampleData = isset($template['sampleData']) ? json_encode($template['sampleData']) : null;
        
        $stmt->execute([
            $template['name'],
            $template['description'] ?? '',
            $template['category'] ?? 'landing',
            $template['type'] ?? 'html',
            $template['template'],
            $sampleData,
            $template['status'] ?? 'active',
            current_user_id()
        ]);
        
        $templateId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Template created successfully',
            'template_id' => $templateId
        ]);
    } catch (Exception $e) {
        error_log('Error creating template: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating template']);
    }
}

function updateTemplate($pdo, $input) {
    $template = $input['template'] ?? [];
    $id = $template['id'] ?? $input['id'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    if (!$template || !isset($template['name']) || !isset($template['template'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name and template content are required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('
            UPDATE templates 
            SET name = ?, description = ?, category = ?, type = ?, template = ?, 
                sample_data = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ');
        
        $sampleData = isset($template['sampleData']) ? json_encode($template['sampleData']) : null;
        
        $stmt->execute([
            $template['name'],
            $template['description'] ?? '',
            $template['category'] ?? 'landing',
            $template['type'] ?? 'html',
            $template['template'],
            $sampleData,
            $template['status'] ?? 'active',
            $id
        ]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Template updated successfully'
        ]);
    } catch (Exception $e) {
        error_log('Error updating template: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating template']);
    }
}

function deleteTemplate($pdo, $id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('DELETE FROM templates WHERE id = ?');
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Template deleted successfully'
        ]);
    } catch (Exception $e) {
        error_log('Error deleting template: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting template']);
    }
}

function downloadTemplate($pdo) {
    $id = $_GET['id'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('
            SELECT name, template, sample_data 
            FROM templates 
            WHERE id = ?
        ');
        $stmt->execute([$id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        // Increment download count
        $stmt = $pdo->prepare('UPDATE templates SET downloads = downloads + 1 WHERE id = ?');
        $stmt->execute([$id]);
        
        // Generate HTML from template
        $sampleData = $template['sample_data'] ? json_decode($template['sample_data'], true) : [];
        $html = generateHtmlFromTemplate($template['template'], $sampleData);
        
        // Set headers for download
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $template['name'] . '.html"');
        echo $html;
        exit;
    } catch (Exception $e) {
        error_log('Error downloading template: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error downloading template']);
    }
}

function exportTemplates($pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT id, name, description, category, type, template, sample_data, 
                   status, downloads, created_at, updated_at
            FROM templates 
            ORDER BY created_at DESC
        ');
        $stmt->execute();
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON fields
        foreach ($templates as &$template) {
            if ($template['sample_data']) {
                $template['sample_data'] = json_decode($template['sample_data'], true);
            }
        }
        
        $export = [
            'export_date' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'templates' => $templates
        ];
        
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="templates-export-' . date('Y-m-d') . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT);
        exit;
    } catch (Exception $e) {
        error_log('Error exporting templates: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error exporting templates']);
    }
}

function previewTemplate($pdo) {
    $id = $_GET['id'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('
            SELECT template, sample_data 
            FROM templates 
            WHERE id = ?
        ');
        $stmt->execute([$id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        $sampleData = $template['sample_data'] ? json_decode($template['sample_data'], true) : [];
        $html = generateHtmlFromTemplate($template['template'], $sampleData);
        
        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
    } catch (Exception $e) {
        error_log('Error previewing template: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error previewing template']);
    }
}

function generateHtmlFromTemplate($template, $data) {
    // Simple Handlebars-like template engine
    // This is a basic implementation - you might want to use a proper Handlebars PHP library
    
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
