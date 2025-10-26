<?php
// Include database connection
require_once 'config/database.php';

// Function to get all bugs
function getAllBugs() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT b.*, tm.name as assignee_name, p.name as project_name 
                         FROM bugs b 
                         LEFT JOIN team_members tm ON b.assignee_id = tm.id 
                         LEFT JOIN projects p ON b.project_id = p.id 
                         ORDER BY b.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get bug by ID
function getBugById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT b.*, tm.name as assignee_name, p.name as project_name 
                           FROM bugs b 
                           LEFT JOIN team_members tm ON b.assignee_id = tm.id 
                           LEFT JOIN projects p ON b.project_id = p.id 
                           WHERE b.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to create a new bug
function createBug($title, $description, $status, $priority, $assignee_id, $project_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO bugs (title, description, status, priority, assignee_id, project_id) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$title, $description, $status, $priority, $assignee_id, $project_id]);
}

// Function to update a bug
function updateBug($id, $title, $description, $status, $priority, $assignee_id, $project_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE bugs 
                           SET title = ?, description = ?, status = ?, priority = ?, assignee_id = ?, project_id = ? 
                           WHERE id = ?");
    return $stmt->execute([$title, $description, $status, $priority, $assignee_id, $project_id, $id]);
}

// Function to delete a bug
function deleteBug($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM bugs WHERE id = ?");
    return $stmt->execute([$id]);
}

// Function to get all projects
function getAllProjects() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT p.*, 
                         (SELECT COUNT(*) FROM project_assignments pa WHERE pa.project_id = p.id) as team_count
                         FROM projects p 
                         ORDER BY p.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get project by ID
function getProjectById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to create a new project
function createProject($name, $description, $status, $progress, $start_date, $end_date) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO projects (name, description, status, progress, start_date, end_date) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $description, $status, $progress, $start_date, $end_date]);
}

// Function to update a project
function updateProject($id, $name, $description, $status, $progress, $start_date, $end_date) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE projects 
                           SET name = ?, description = ?, status = ?, progress = ?, start_date = ?, end_date = ? 
                           WHERE id = ?");
    return $stmt->execute([$name, $description, $status, $progress, $start_date, $end_date, $id]);
}

// Function to delete a project
function deleteProject($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    return $stmt->execute([$id]);
}

// Function to get all team members
function getAllTeamMembers() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM team_members ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get team member by ID
function getTeamMemberById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to create a new team member
function createTeamMember($name, $email, $role) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO team_members (name, email, role) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $email, $role]);
}

// Function to update a team member
function updateTeamMember($id, $name, $email, $role) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE team_members SET name = ?, email = ?, role = ? WHERE id = ?");
    return $stmt->execute([$name, $email, $role, $id]);
}

// Function to delete a team member
function deleteTeamMember($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
    return $stmt->execute([$id]);
}

// Function to get project assignments for a member
function getProjectAssignmentsForMember($member_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT p.* FROM projects p 
                           JOIN project_assignments pa ON p.id = pa.project_id 
                           WHERE pa.member_id = ?");
    $stmt->execute([$member_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get team members for a project
function getTeamMembersForProject($project_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT tm.* FROM team_members tm 
                           JOIN project_assignments pa ON tm.id = pa.member_id 
                           WHERE pa.project_id = ?");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper function to get status badge class
function getStatusBadgeClass($status) {
    $classes = [
        'open' => 'status-open',
        'in-progress' => 'status-in-progress',
        'resolved' => 'status-resolved',
        'closed' => 'status-closed'
    ];
    return isset($classes[$status]) ? $classes[$status] : '';
}

// Helper function to get priority badge class
function getPriorityBadgeClass($priority) {
    $classes = [
        'low' => 'priority-low',
        'medium' => 'priority-medium',
        'high' => 'priority-high',
        'critical' => 'priority-critical'
    ];
    return isset($classes[$priority]) ? $classes[$priority] : '';
}
?>