<?php
// api/issues.php – CRUD API for issues
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success'=>false,'message'=>'Not authenticated.']); exit;
}

$uid = currentUserId();

// ─── GET ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get') {
        $id   = (int)($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM issues WHERE id=? AND user_id=?");
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
        $issue = $stmt->get_result()->fetch_assoc();
        if ($issue) echo json_encode(['success'=>true,'issue'=>$issue]);
        else        echo json_encode(['success'=>false,'message'=>'Issue not found.']);
        exit;
    }

    if ($action === 'list') {
        $status = $_GET['status'] ?? '';
        $where  = "WHERE user_id=$uid";
        if ($status) $where .= " AND status='".mysqli_real_escape_string($conn,$status)."'";
        $result = $conn->query("SELECT * FROM issues $where ORDER BY created_at DESC");
        $issues = [];
        while ($r = $result->fetch_assoc()) $issues[] = $r;
        echo json_encode(['success'=>true,'issues'=>$issues]);
        exit;
    }
}

// ─── POST ─────────────────────────────────────────────────
$input  = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'create') {
    $title       = trim($input['title']       ?? '');
    $description = trim($input['description'] ?? '');
    $category    = $input['category']    ?? '';
    $priority    = $input['priority']    ?? 'Medium';

    if (!$title || !$description || !$category) {
        echo json_encode(['success'=>false,'message'=>'Title, description and category are required.']); exit;
    }

    $allowed_cat  = ['IT','HR','Finance','Operations','Other'];
    $allowed_pri  = ['Low','Medium','High'];
    if (!in_array($category,$allowed_cat)) { echo json_encode(['success'=>false,'message'=>'Invalid category.']); exit; }
    if (!in_array($priority,$allowed_pri)) { echo json_encode(['success'=>false,'message'=>'Invalid priority.']); exit; }

    $stmt = $conn->prepare("INSERT INTO issues (user_id, title, description, category, priority) VALUES (?,?,?,?,?)");
    $stmt->bind_param('issss', $uid, $title, $description, $category, $priority);

    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'id'=>$conn->insert_id]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Database error.']);
    }
    exit;
}

if ($action === 'delete') {
    $id   = (int)($input['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM issues WHERE id=? AND user_id=? AND status='Open'");
    $stmt->bind_param('ii', $id, $uid);
    $stmt->execute();
    if ($stmt->affected_rows > 0) echo json_encode(['success'=>true]);
    else echo json_encode(['success'=>false,'message'=>'Cannot delete this issue.']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid action.']);
