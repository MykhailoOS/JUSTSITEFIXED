<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BugForge - Bug Tracking & Project Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar-container">
            <?php include 'sidebar.php'; ?>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-content">
                    <h1><?php echo isset($pageTitle) ? $pageTitle : 'BugForge'; ?></h1>
                    <p><?php echo isset($pageDescription) ? $pageDescription : 'Bug Tracking & Project Management System'; ?></p>
                </div>
            </header>
            <div class="content-wrapper">
                {{CONTENT}}
            </div>
            <footer class="main-footer">
                <p>&copy; 2025 BugForge - All rights reserved</p>
            </footer>
        </main>
    </div>
</body>
</html>