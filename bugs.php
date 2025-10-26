<?php
require_once 'includes/functions.php';

$pageTitle = "Bug Tracking";
$pageDescription = "Manage and track software bugs";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                createBug(
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['status'],
                    $_POST['priority'],
                    $_POST['assignee_id'] ?? null,
                    $_POST['project_id'] ?? null
                );
                header('Location: bugs.php');
                exit;
                
            case 'update':
                updateBug(
                    $_POST['id'],
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['status'],
                    $_POST['priority'],
                    $_POST['assignee_id'] ?? null,
                    $_POST['project_id'] ?? null
                );
                header('Location: bugs.php');
                exit;
                
            case 'delete':
                deleteBug($_POST['id']);
                header('Location: bugs.php');
                exit;
        }
    }
}

// Get all bugs
$bugs = getAllBugs();
$teamMembers = getAllTeamMembers();
$projects = getAllProjects();

include 'includes/layout.php';
ob_start();
?>

<div class="form-container">
    <h2>Create New Bug</h2>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-group">
            <label for="title">Bug Title</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="open">Open</option>
                <option value="in-progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
        </div>
        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" class="form-control">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
            </select>
        </div>
        <div class="form-group">
            <label for="assignee_id">Assignee</label>
            <select id="assignee_id" name="assignee_id" class="form-control">
                <option value="">Unassigned</option>
                <?php foreach ($teamMembers as $member): ?>
                    <option value="<?= $member['id'] ?>"><?= $member['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="project_id">Project</label>
            <select id="project_id" name="project_id" class="form-control">
                <option value="">No Project</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?= $project['id'] ?>"><?= $project['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-block">Create Bug Report</button>
    </form>
</div>

<div class="table-container">
    <div class="table-header d-flex justify-between align-center">
        <h3>Existing Bugs</h3>
        <span><?= count($bugs) ?> bugs</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Assignee</th>
                <th>Project</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bugs as $bug): ?>
                <tr>
                    <td>#<?= $bug['id'] ?></td>
                    <td><?= htmlspecialchars($bug['title']) ?></td>
                    <td><span class="status-badge <?= getStatusBadgeClass($bug['status']) ?>"><?= ucfirst($bug['status']) ?></span></td>
                    <td><span class="status-badge <?= getPriorityBadgeClass($bug['priority']) ?>"><?= ucfirst($bug['priority']) ?></span></td>
                    <td><?= $bug['assignee_name'] ?? 'Unassigned' ?></td>
                    <td><?= $bug['project_name'] ?? 'No Project' ?></td>
                    <td><?= date('Y-m-d', strtotime($bug['created_at'])) ?></td>
                    <td>
                        <button onclick="editBug(<?= $bug['id'] ?>)" class="btn">Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $bug['id'] ?>">
                            <button type="submit" class="btn" onclick="return confirm('Are you sure you want to delete this bug?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function editBug(id) {
    alert('Edit functionality would be implemented here. Bug ID: ' + id);
    // In a full implementation, this would open a modal or redirect to an edit page
}
</script>

<?php
$content = ob_get_clean();
echo str_replace('{{CONTENT}}', $content, file_get_contents('includes/layout.php'));
?>
