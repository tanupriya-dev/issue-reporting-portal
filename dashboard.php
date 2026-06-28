<?php
require_once '../includes/db.php';
if (!isLoggedIn() || isAdmin()) redirect(isAdmin() ? 'admin/dashboard.php' : 'login.php');

$uid = (int)$_SESSION['user_id'];

// Stats
$total      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM issues WHERE user_id = $uid"))['c'];
$open       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM issues WHERE user_id = $uid AND status = 'open'"))['c'];
$in_prog    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM issues WHERE user_id = $uid AND status = 'in_progress'"))['c'];
$resolved   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM issues WHERE user_id = $uid AND status = 'resolved'"))['c'];

// Recent issues
$recent = mysqli_query($conn, "SELECT * FROM issues WHERE user_id = $uid ORDER BY created_at DESC LIMIT 5");

include '../includes/header.php';
?>
<script src="../assets/js/main.js"></script>

<div class="page-header">
    <h1>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h1>
    <p>Here's an overview of your reported issues.</p>
</div>

<div class="stats-grid">
    <div class="stat-card blue">
        <span class="stat-label">Total Reported</span>
        <span class="stat-value"><?= $total ?></span>
    </div>
    <div class="stat-card red">
        <span class="stat-label">Open</span>
        <span class="stat-value"><?= $open ?></span>
    </div>
    <div class="stat-card amber">
        <span class="stat-label">In Progress</span>
        <span class="stat-value"><?= $in_prog ?></span>
    </div>
    <div class="stat-card green">
        <span class="stat-label">Resolved</span>
        <span class="stat-value"><?= $resolved ?></span>
    </div>
</div>

<div style="display:flex;gap:16px;margin-bottom:28px;flex-wrap:wrap;">
    <a href="report_issue.php" class="btn btn-primary">&#43; Report New Issue</a>
    <a href="my_issues.php" class="btn btn-outline">View All My Issues</a>
</div>

<div class="card">
    <div class="card-title">Recent Issues</div>
    <?php if (mysqli_num_rows($recent) > 0): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><span class="badge badge-<?= $row['priority'] ?>"><?= ucfirst($row['priority']) ?></span></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucwords(str_replace('_', ' ', $row['status'])) ?></span></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">&#128196;</div>
        <h3>No issues yet</h3>
        <p>You haven't reported any issues. <a href="report_issue.php">Report your first issue</a>.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>