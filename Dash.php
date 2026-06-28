<?php
require_once '../../includes/db.php';
if (!isLoggedIn() || !isAdmin()) redirect('../login.php');

// Stats
$total      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM issues"))['c'];
$open       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM issues WHERE status = 'open'"))['c'];
$in_prog    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM issues WHERE status = 'in_progress'"))['c'];
$resolved   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM issues WHERE status = 'resolved'"))['c'];
$users_total= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role = 'employee'"))['c'];

// Category breakdown
$cats = mysqli_query($conn, "SELECT category, COUNT(*) AS cnt FROM issues GROUP BY category ORDER BY cnt DESC LIMIT 5");

// Recent issues (last 8)
$recent = mysqli_query($conn,
    "SELECT i.*, u.name AS reporter
     FROM issues i JOIN users u ON i.user_id = u.id
     ORDER BY i.created_at DESC LIMIT 8");

include '../../includes/header.php';
?>
<script src="../../assets/js/main.js"></script>

<div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Overview of all issues and system activity.</p>
</div>

<div class="stats-grid">
    <div class="stat-card blue">
        <span class="stat-label">Total Issues</span>
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
    <div class="stat-card purple">
        <span class="stat-label">Employees</span>
        <span class="stat-value"><?= $users_total ?></span>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:28px;" class="dashboard-grid">

    <!-- Recent Issues -->
    <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:600;">Recent Issues</span>
            <a href="manage_issues.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Title</th><th>Reporter</th><th>Priority</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['reporter']) ?></td>
                        <td><span class="badge badge-<?= $row['priority'] ?>"><?= ucfirst($row['priority']) ?></span></td>
                        <td><span class="badge badge-<?= $row['status'] ?>"><?= ucwords(str_replace('_',' ',$row['status'])) ?></span></td>
                        <td><?= date('d M', strtotime($row['created_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="card">
        <div class="card-title">Issues by Category</div>
        <?php while ($cat = mysqli_fetch_assoc($cats)):
            $pct = $total > 0 ? round(($cat['cnt'] / $total) * 100) : 0;
        ?>
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                <span><?= htmlspecialchars($cat['category']) ?></span>
                <span style="color:var(--muted)"><?= $cat['cnt'] ?></span>
            </div>
            <div style="background:var(--border);border-radius:4px;height:6px;">
                <div style="background:var(--primary);width:<?= $pct ?>%;height:6px;border-radius:4px;transition:width .4s;"></div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if ($total === 0): ?>
            <p style="color:var(--muted);font-size:13px;">No issues yet.</p>
        <?php endif; ?>
    </div>

</div>

<style>
@media(max-width:768px){.dashboard-grid{grid-template-columns:1fr!important;}}
</style>

<?php include '../../includes/footer.php'; ?>