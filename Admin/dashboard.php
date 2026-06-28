<?php
// admin/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$total = $conn->query("SELECT COUNT(*) c FROM issues")->fetch_assoc()['c'];
$open  = $conn->query("SELECT COUNT(*) c FROM issues WHERE status='Open'")->fetch_assoc()['c'];
$prog  = $conn->query("SELECT COUNT(*) c FROM issues WHERE status='In Progress'")->fetch_assoc()['c'];
$res   = $conn->query("SELECT COUNT(*) c FROM issues WHERE status='Resolved'")->fetch_assoc()['c'];
$users = $conn->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch_assoc()['c'];
$recent = $conn->query("SELECT i.*, u.name user_name FROM issues i JOIN users u ON i.user_id=u.id ORDER BY i.created_at DESC LIMIT 8");
$active = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – Issue Portal</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include 'includes/admin-sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="page-title">
                <h1>Admin Dashboard</h1>
                <p>System-wide overview of all issues</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📋</div>
                <div><div class="stat-value"><?= $total ?></div><div class="stat-label">Total Issues</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">🔵</div>
                <div><div class="stat-value"><?= $open ?></div><div class="stat-label">Open</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow">🟡</div>
                <div><div class="stat-value"><?= $prog ?></div><div class="stat-label">In Progress</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">🟢</div>
                <div><div class="stat-value"><?= $res ?></div><div class="stat-label">Resolved</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">👥</div>
                <div><div class="stat-value"><?= $users ?></div><div class="stat-label">Employees</div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Recent Issues</h3>
                <a href="issues.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>#</th><th>Title</th><th>Reported By</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                            <td><?= $row['category'] ?></td>
                            <td><span class="badge badge-<?= strtolower($row['priority']) ?>"><?= $row['priority'] ?></span></td>
                            <td><span class="badge badge-<?= strtolower(str_replace(' ','',$row['status'])) ?>"><?= $row['status'] ?></span></td>
                            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <select class="btn btn-outline btn-sm" style="padding:.3rem .6rem"
                                    onchange="updateStatus(<?= $row['id'] ?>, this.value)">
                                    <?php foreach(['Open','In Progress','Resolved','Closed'] as $s): ?>
                                    <option <?= $row['status']===$s?'selected':'' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
async function updateStatus(id, status) {
    const res  = await fetch('../api/admin.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({action:'update_status', id, status})
    });
    const data = await res.json();
    if (!data.success) alert('Failed to update status.');
}
</script>
</body>
</html>
