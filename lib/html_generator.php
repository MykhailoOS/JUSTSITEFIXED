<?php
/**
 * Optimized HTML generation functions for JustSite
 * This file contains reusable functions for generating optimized HTML output
 */

// Optimized HTML generation function
function generateOptimizedHTML($title, $canvas) {
    $titleTag = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $base = rtrim(APP_BASE_URL, '/') . '/';
    
    // Optimized CSS links with versioning
    $cssLinks = [
        $base . 'styles/main.css?v=' . (file_exists(__DIR__ . '/../styles/main.css') ? filemtime(__DIR__ . '/../styles/main.css') : time()),
    ];
    
    // Pre-compiled head section with SEO optimization
    $head = "<meta charset=\"UTF-8\">" .
            "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">" .
            "<title>{$titleTag}</title>" .
            "<meta name=\"description\" content=\"" . htmlspecialchars(substr(strip_tags($canvas), 0, 160), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\">" .
            "<meta name=\"robots\" content=\"index, follow\">" .
            "<meta name=\"generator\" content=\"JustSite\">" .
            implode('', array_map(fn($h)=>"<link rel=\"stylesheet\" href=\"{$h}\">", $cssLinks));
    
    // Optimized body with minimal inline styles and responsive design
    $body = "<body style=\"margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica Neue,Arial,sans-serif;line-height:1.6;\">" .
            "<div class=\"export_container\" style=\"padding:24px;max-width:1200px;margin:0 auto;min-height:100vh;\">" .
            $canvas .
            "</div>" .
            "</body>";
    
    return "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n{$head}\n</head>\n{$body}\n</html>";
}

// Function to generate minimal HTML for quick saves
function generateMinimalHTML($title, $canvas) {
    $titleTag = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $base = rtrim(APP_BASE_URL, '/') . '/';
    
    $head = "<meta charset=\"UTF-8\">" .
            "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">" .
            "<title>{$titleTag}</title>" .
            "<link rel=\"stylesheet\" href=\"{$base}styles/main.css?v=" . time() . "\">";
    
    $body = "<body style=\"margin:0;\">" .
            "<div style=\"padding:24px;\">" .
            $canvas .
            "</div>" .
            "</body>";
    
    return "<!DOCTYPE html>\n<html>\n<head>\n{$head}\n</head>\n{$body}\n</html>";
}

// Function to extract canvas content from full HTML
function extractCanvasFromHTML($html) {
    // Remove any BOM or whitespace at the beginning
    $html = trim($html);
    
    // Use DOMDocument for more reliable parsing
    $dom = new DOMDocument();
    
    // Add UTF-8 meta tag to ensure proper encoding
    $htmlWithEncoding = '<?xml encoding="UTF-8">' . $html;
    $dom->loadHTML($htmlWithEncoding, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
    
    // Find elements with class containing "general_canva" (legacy)
    $xpath = new DOMXPath($dom);
    $canvasNodes = $xpath->query('//div[contains(@class, "general_canva")]');
    
    if ($canvasNodes->length > 0) {
        $canvasNode = $canvasNodes->item(0);
        $innerHTML = '';
        foreach ($canvasNode->childNodes as $child) {
            $innerHTML .= $dom->saveHTML($child);
        }
        return trim($innerHTML);
    }
    
    // Try to find export_container (current system)
    if (preg_match('/<div[^>]*class="[^"]*export_container[^"]*"[^>]*>(.*?)<\/div>/s', $html, $matches)) {
        return trim($matches[1]);
    }
    
    // Try to find elements with data-element-id (new editor)
    if (preg_match_all('/<div[^>]*data-element-id="[^"]*"[^>]*>.*?<\/div>/s', $html, $matches)) {
        return implode('', $matches[0]);
    }
    
    // Fallback: collect all elements with el_ classes using more precise regex
    if (preg_match_all('/<div[^>]*class="[^"]*el_[^"]*"[^>]*>(?:[^<]++|<(?!\/div>))*+<\/div>/s', $html, $matches)) {
        return implode('', $matches[0]);
    }
    
    // More aggressive fallback: find all div elements that look like components
    if (preg_match_all('/<div[^>]*(?:class="[^"]*el_|data-id=|data-element-id=)[^>]*>.*?<\/div>/s', $html, $matches)) {
        return implode('', $matches[0]);
    }
    
    // Last resort: try to find any content between body tags, excluding scripts and styles
    if (preg_match('/<body[^>]*>(.*?)<\/body>/s', $html, $matches)) {
        $bodyContent = $matches[1];
        // Remove scripts and styles
        $bodyContent = preg_replace('/<script[^>]*>.*?<\/script>/s', '', $bodyContent);
        $bodyContent = preg_replace('/<style[^>]*>.*?<\/style>/s', '', $bodyContent);
        return trim($bodyContent);
    }
    
    // If all else fails, return cleaned HTML
    $cleaned = strip_tags($html, '<div><span><p><h1><h2><h3><h4><h5><h6><a><img><ul><ol><li><button><input><textarea><select><option><form><table><tr><td><th><thead><tbody><tfoot><br><hr><strong><em><b><i><u><small><big><sub><sup><code><pre><blockquote><cite><abbr><acronym><address><del><ins><s><strike>');
    return trim($cleaned);
}

// Function to validate and sanitize canvas content
function sanitizeCanvasContent($canvas) {
    // Don't sanitize if the content looks like it contains valid elements
    if (strpos($canvas, 'class="el_') !== false || 
        strpos($canvas, 'data-id=') !== false || 
        strpos($canvas, 'data-element-id=') !== false) {
        // Just trim whitespace for valid canvas content
        return trim($canvas);
    }
    
    // Remove any potentially dangerous content only if it doesn't look like canvas content
    $canvas = strip_tags($canvas, '<div><span><p><h1><h2><h3><h4><h5><h6><a><img><ul><ol><li><button><input><textarea><select><option><form><table><tr><td><th><thead><tbody><tfoot><br><hr><strong><em><b><i><u><small><big><sub><sup><code><pre><blockquote><cite><abbr><acronym><address><del><ins><s><strike>');
    
    // Clean up any empty elements, but be careful not to remove elements with classes
    $canvas = preg_replace('/<(\w+)[^>]*>\s*<\/\1>/', '', $canvas);
    
    return trim($canvas);
}
?>
