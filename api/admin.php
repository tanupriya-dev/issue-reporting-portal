<?php
// api/admin.php – Admin-only API
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized.']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ─── GET ─────────────────────────────────────────────────
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    // All issues with filters
    if ($action === 'issues') {
        $where = [];
        if (!empty($_GET['status']))   $where[] = "i.status='".mysqli_real_escape_string($conn,$_GET['status'])."'";
        if (!empty($_GET['category'])) $where[] = "i.category='".mysqli_real_escape_string($conn,$_GET['category'])."'";
        if (!empty($_GET['priority'])) $where[] = "i.priority='".mysqli_real_escape_string($conn,$_GET['priority'])."'";
        if (!empty($_GET['search']))   $where[] = "(i.title LIKE '%".mysqli_real_escape_string($conn,$_GET['search'])."%' OR u.name LIKE '%".mysqli_real_escape_string($conn,$_GET['search'])."%')";

        $sql = "SELECT i.*, u.name AS user_name, u.email AS user_email
                FROM issues i JOIN users u ON i.user_id=u.id"
             . ($where ? " WHERE ".implode(' AND ',$where) : '')
             . " ORDER BY i.created_at DESC";
        $result = $conn->query($sql);
        $issues = [];
        while ($r = $result->fetch_assoc()) $issues[] = $r;
        echo json_encode(['success'=>true,'issues'=>$issues]);
        exit;
    }

    // All users
    if ($action === 'users') {
        $result = $conn->query("SELECT id, name, email, role, created_at,
            (SELECT COUNT(*) FROM issues WHERE user_id=users.id) AS issue_count
            FROM users ORDER BY created_at DESC");
        $users = [];
        while ($r = $result->fetch_assoc()) $users[] = $r;
        echo json_encode(['success'=>true,'users'=>$users]);
        exit;
    }

    // Dashboard stats
    if ($action === 'stats') {
        $total   = $conn->query("SELECT COUNT(*) c FROM issues")->fetch_assoc()['c'];
        $open    = $conn->query("SELECT COUNT(*) c FROM issues WHERE status='Open'")->fetch_assoc()['c'];
        $prog    = $conn->query("SELECT COUNT(*) c FROM issues WHERE status='In Progress'")->fetch_assoc()['c'];
        $res     = $conn->query("SELECT COUNT(*) c FROM issues WHERE status='Resolved'")->fetch_assoc()['c'];
        $users   = $conn->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch_assoc()['c'];
        echo json_encode(['success'=>true,'stats'=>compact('total','open','prog','res','users')]);
        exit;
    }
}

// ─── POST ─────────────────────────────────────────────────
$input  = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// Update issue status
if ($action === 'update_status') {
    $id     = (int)($input['id']     ?? 0);
    $status = $input['status'] ?? '';
    $allowed = ['Open','In Progress','Resolved','Closed'];
    if (!in_array($status,$allowed)) { echo json_encode(['success'=>false,'message'=>'Invalid status.']); exit; }
    $stmt = $conn->prepare("UPDATE issues SET status=? WHERE id=?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    echo json_encode(['success'=>true]);
    exit;
}

// Delete user
if ($action === 'delete_user') {
    $id = (int)($input['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='user'");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) echo json_encode(['success'=>true]);
    else echo json_encode(['success'=>false,'message'=>'Cannot delete this user.']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid action.']);
