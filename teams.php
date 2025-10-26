<?php
require_once 'includes/functions.php';

$pageTitle = "Team Management";
$pageDescription = "Manage team members and assignments";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                createTeamMember(
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['role']
                );
                header('Location: teams.php');
                exit;
                
            case 'update':
                updateTeamMember(
                    $_POST['id'],
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['role']
                );
                header('Location: teams.php');
                exit;
                
            case 'delete':
                deleteTeamMember($_POST['id']);
                header('Location: teams.php');
                exit;
        }
    }
}

// Get all team members
$teamMembers = getAllTeamMembers();
$projects = getAllProjects();

include 'includes/layout.php';
ob_start();
?>

<div class="form-container">
    <h2>Add New Team Member</h2>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <input type="text" id="role" name="role" class="form-control" placeholder="e.g., Developer, Designer, Manager">
        </div>
        <button type="submit" class="btn btn-block">Add Team Member</button>
    </form>
</div>

<div class="table-container">
    <div class="table-header d-flex justify-between align-center">
        <h3>Team Members</h3>
        <span><?= count($teamMembers) ?> members</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Projects</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teamMembers as $member): ?>
                <tr>
                    <td>
                        <div class="d-flex align-center">
                            <div class="user-avatar" style="width: 40px; height: 40px; background: #3498db; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 10px;">
                                <?= strtoupper(substr($member['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div><strong><?= htmlspecialchars($member['name']) ?></strong></div>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($member['email']) ?></td>
                    <td><?= htmlspecialchars($member['role']) ?></td>
                    <td>
                        <?php 
                        $assignments = getProjectAssignmentsForMember($member['id']);
                        if (count($assignments) > 0):
                        ?>
                            <div class="d-flex" style="flex-wrap: wrap; gap: 5px;">
                                <?php foreach ($assignments as $assignment): ?>
                                    <span class="status-badge" style="background: #e1f0fa; color: #3498db;"><?= htmlspecialchars($assignment['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span style="color: #95a5a6;">No projects assigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="editMember(<?= $member['id'] ?>)" class="btn">Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $member['id'] ?>">
                            <button type="submit" class="btn" onclick="return confirm('Are you sure you want to delete this team member?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function editMember(id) {
    alert('Edit functionality would be implemented here. Member ID: ' + id);
    // In a full implementation, this would open a modal or redirect to an edit page
}
</script>

<?php
$content = ob_get_clean();
echo str_replace('{{CONTENT}}', $content, file_get_contents('includes/layout.php'));
?>