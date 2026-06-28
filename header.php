<?php
require_once __DIR__ . '/db.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Reporting Portal</title>
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>assets/css/style.css">
</head>
<body>

<?php if (isLoggedIn()): ?>
<nav class="navbar">
    <div class="nav-brand">
        <span class="brand-icon">&#9741;</span>
        Issue Portal
    </div>
    <div class="nav-links">
        <?php if (isAdmin()): ?>
            <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/admin/dashboard.php" class="<?= in_array($current_page, ['dashboard.php']) ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/admin/manage_issues.php" class="<?= $current_page === 'manage_issues.php' ? 'active' : '' ?>">All Issues</a>
            <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/admin/manage_users.php" class="<?= $current_page === 'manage_users.php' ? 'active' : '' ?>">Users</a>
        <?php else: ?>
            <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/report_issue.php" class="<?= $current_page === 'report_issue.php' ? 'active' : '' ?>">Report Issue</a>
            <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/my_issues.php" class="<?= $current_page === 'my_issues.php' ? 'active' : '' ?>">My Issues</a>
        <?php endif; ?>
    </div>
    <div class="nav-user">
        <span>&#128100; <?= htmlspecialchars($_SESSION['name']) ?></span>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>logout.php" class="btn-logout">Logout</a>
    </div>
</nav>
<?php endif; ?>

<div class="main-content">