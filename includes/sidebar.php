<?php
// Sidebar component for PHP version
function renderSidebar($currentPage = 'dashboard') {
    $navItems = [
        ['name' => 'Dashboard', 'icon' => '📊', 'url' => 'index.php'],
        ['name' => 'Bugs', 'icon' => '🐛', 'url' => 'bugs.php'],
        ['name' => 'Projects', 'icon' => '📁', 'url' => 'projects.php'],
        ['name' => 'Teams', 'icon' => '👥', 'url' => 'teams.php'],
        ['name' => 'Settings', 'icon' => '⚙️', 'url' => 'settings.php'],
    ];
    
    echo '<div class="sidebar">';
    echo '<div class="sidebar-header">';
    echo '<h2>_bugforge</h2>';
    echo '</div>';
    echo '<nav class="sidebar-nav">';
    
    foreach ($navItems as $item) {
        $activeClass = ($currentPage === strtolower($item['name'])) ? 'active' : '';
        echo '<a href="' . $item['url'] . '" class="nav-item ' . $activeClass . '">';
        echo '<span class="nav-icon">' . $item['icon'] . '</span>';
        echo '<span class="nav-text">' . $item['name'] . '</span>';
        echo '</a>';
    }
    
    echo '</nav>';
    echo '<div class="sidebar-footer">';
    echo '<div class="user-info">';
    echo '<div class="user-avatar">👤</div>';
    echo '<div class="user-details">';
    echo '<div class="user-name">Admin User</div>';
    echo '<div class="user-role">Administrator</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>