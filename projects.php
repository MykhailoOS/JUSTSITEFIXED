<?php
require_once 'includes/functions.php';
require_once __DIR__ . '/lib/language.php';

// Initialize language system
LanguageManager::init();

$pageTitle = "Project Management";
$pageDescription = "Manage software projects and track progress";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                createProject(
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['status'],
                    $_POST['progress'],
                    $_POST['start_date'],
                    $_POST['end_date']
                );
                header('Location: projects.php');
                exit;
                
            case 'update':
                updateProject(
                    $_POST['id'],
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['status'],
                    $_POST['progress'],
                    $_POST['start_date'],
                    $_POST['end_date']
                );
                header('Location: projects.php');
                exit;
                
            case 'delete':
                deleteProject($_POST['id']);
                header('Location: projects.php');
                exit;
        }
    }
}

// Get all projects
$projects = getAllProjects();
$teamMembers = getAllTeamMembers();

include 'includes/layout.php';
ob_start();
?>

<div class="form-container">
    <h2>Create New Project</h2>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-group">
            <label for="name">Project Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="planning">Planning</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="on-hold">On Hold</option>
            </select>
        </div>
        <div class="form-group">
            <label for="progress">Progress (%)</label>
            <input type="number" id="progress" name="progress" class="form-control" min="0" max="100" value="0">
        </div>
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-control">
        </div>
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-control">
        </div>
        <button type="submit" class="btn btn-block">Create Project</button>
    </form>
</div>

<div class="table-container">
    <div class="table-header d-flex justify-between align-center">
        <h3>Existing Projects</h3>
        <span><?= count($projects) ?> projects</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Team</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td>
                        <div><strong><?= htmlspecialchars($project['name']) ?></strong></div>
                        <div><?= htmlspecialchars($project['description']) ?></div>
                    </td>
                    <td><span class="status-badge <?= getStatusBadgeClass($project['status']) ?>"><?= ucfirst($project['status']) ?></span></td>
                    <td>
                        <div class="d-flex align-center">
                            <div style="width: 100px; height: 8px; background: #ecf0f1; border-radius: 4px; margin-right: 10px;">
                                <div style="width: <?= $project['progress'] ?>%; height: 100%; background: #3498db; border-radius: 4px;"></div>
                            </div>
                            <span><?= $project['progress'] ?>%</span>
                        </div>
                    </td>
                    <td><?= $project['start_date'] ?></td>
                    <td><?= $project['end_date'] ?></td>
                    <td><?= $project['team_count'] ?> members</td>
                    <td>
                        <button onclick="editProject(<?= $project['id'] ?>)" class="btn">Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $project['id'] ?>">
                            <button type="submit" class="btn" onclick="return confirm('Are you sure you want to delete this project?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function editProject(id) {
    alert('Edit functionality would be implemented here. Project ID: ' + id);
    // In a full implementation, this would open a modal or redirect to an edit page
}
</script>

<?php
$content = ob_get_clean();
echo str_replace('{{CONTENT}}', $content, file_get_contents('includes/layout.php'));
?>