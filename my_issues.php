<?php
require_once '../includes/db.php';
if (!isLoggedIn() || isAdmin()) redirect('login.php');

$uid = (int)$_SESSION['user_id'];

// Filters
$where   = "WHERE i.user_id = $uid";
$status  = clean($conn, $_GET['status']   ?? '');
$priority= clean($conn, $_GET['priority'] ?? '');
$search  = clean($conn, $_GET['search']   ?? '');

if ($status)   $where .= " AND i.status = '$status'";
if ($priority) $where .= " AND i.priority = '$priority'";
if ($search)   $where .= " AND (i.title LIKE '%$search%' OR i.description LIKE '%$search%')";

$issues = mysqli_query($conn, "SELECT * FROM issues i $where ORDER BY i.created_at DESC");
$total  = mysqli_num_rows($issues);

include '../includes/header.php';
?>
<script src="../assets/js/main.js"></script>

<div class="page-header">
    <h1>My Issues</h1>
    <p>All <?= $total ?> issue(s) you have submitted.</p>
</div>

<div style="margin-bottom:16px;">
    <a href="report_issue.php" class="btn btn-primary">&#43; Report New Issue</a>
</div>

<!-- Filter bar -->
<form method="GET" action="">
    <div class="filter-bar">
        <input type="text" name="search" placeholder="Search issues..."
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <select name="status">
            <option value="">All Statuses</option>
            <option value="open"        <?= ($status === 'open')        ? 'selected' : '' ?>>Open</option>
            <option value="in_progress" <?= ($status === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
            <option value="resolved"    <?= ($status === 'resolved')    ? 'selected' : '' ?>>Resolved</option>
            <option value="closed"      <?= ($status === 'closed')      ? 'selected' : '' ?>>Closed</option>
        </select>
        <select name="priority">
            <option value="">All Priorities</option>
            <option value="low"      <?= ($priority === 'low')      ? 'selected' : '' ?>>Low</option>
            <option value="medium"   <?= ($priority === 'medium')   ? 'selected' : '' ?>>Medium</option>
            <option value="high"     <?= ($priority === 'high')     ? 'selected' : '' ?>>High</option>
            <option value="critical" <?= ($priority === 'critical') ? 'selected' : '' ?>>Critical</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="my_issues.php" class="btn btn-outline btn-sm">Clear</a>
    </div>
</form>

<div class="card" style="padding:0;">
    <?php if ($total > 0): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                    <th>Attachment</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($issues)): ?>
                <tr>
                    <td style="color:var(--muted)"><?= $row['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['title']) ?></strong>
                        <div style="font-size:12px;color:var(--muted);margin-top:2px;">
                            <?= htmlspecialchars(substr($row['description'], 0, 80)) ?>...
                        </div>
                    </td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><span class="badge badge-<?= $row['priority'] ?>"><?= ucfirst($row['priority']) ?></span></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucwords(str_replace('_', ' ', $row['status'])) ?></span></td>
                    <td><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                    <td>
                        <?php if ($row['file_path']): ?>
                            <a href="../uploads/<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-outline btn-sm">View</a>
                        <?php else: ?>
                            <span style="color:var(--muted);font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">&#128196;</div>
        <h3>No issues found</h3>
        <p>No issues match your filters, or you haven't reported any yet.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>