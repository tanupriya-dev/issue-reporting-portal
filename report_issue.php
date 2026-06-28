<?php
require_once '../includes/db.php';
if (!isLoggedIn() || isAdmin()) redirect('login.php');

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid         = (int)$_SESSION['user_id'];
    $title       = clean($conn, $_POST['title'] ?? '');
    $description = clean($conn, $_POST['description'] ?? '');
    $category    = clean($conn, $_POST['category'] ?? '');
    $priority    = clean($conn, $_POST['priority'] ?? 'medium');
    $file_path   = '';

    if (empty($title) || empty($description) || empty($category)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($title) < 5) {
        $error = 'Title must be at least 5 characters.';
    } elseif (strlen($description) < 10) {
        $error = 'Description must be at least 10 characters.';
    } else {
        // Handle file upload (Phase 4 enhancement)
        if (!empty($_FILES['attachment']['name'])) {
            $upload_dir  = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $allowed     = ['jpg','jpeg','png','gif','pdf','doc','docx','txt'];
            $ext         = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'File type not allowed. Allowed: jpg, png, gif, pdf, doc, docx, txt.';
            } elseif ($_FILES['attachment']['size'] > 5 * 1024 * 1024) {
                $error = 'File size must be under 5MB.';
            } else {
                $filename  = uniqid() . '_' . basename($_FILES['attachment']['name']);
                $dest      = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                    $file_path = $filename;
                } else {
                    $error = 'File upload failed. Please try again.';
                }
            }
        }

        if (empty($error)) {
            $fp_esc = clean($conn, $file_path);
            $sql = "INSERT INTO issues (user_id, title, description, category, priority, status, file_path)
                    VALUES ($uid, '$title', '$description', '$category', '$priority', 'open', '$fp_esc')";
            if (mysqli_query($conn, $sql)) {
                $success = 'Your issue has been submitted successfully!';
            } else {
                $error = 'Database error: ' . mysqli_error($conn);
            }
        }
    }
}

include '../includes/header.php';
?>
<script src="../assets/js/main.js"></script>

<div class="page-header">
    <h1>Report an Issue</h1>
    <p>Describe your issue clearly so the admin team can resolve it quickly.</p>
</div>

<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<div class="card" style="max-width:720px;">
    <form method="POST" action="" enctype="multipart/form-data" id="reportIssueForm">

        <div class="form-group">
            <label for="title">Issue Title <span style="color:var(--danger)">*</span></label>
            <input type="text" id="title" name="title" class="form-control"
                   placeholder="Brief summary of the issue"
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="category">Category <span style="color:var(--danger)">*</span></label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">— Select category —</option>
                    <option value="IT / Hardware"     <?= (($_POST['category'] ?? '') === 'IT / Hardware')     ? 'selected' : '' ?>>IT / Hardware</option>
                    <option value="IT / Software"     <?= (($_POST['category'] ?? '') === 'IT / Software')     ? 'selected' : '' ?>>IT / Software</option>
                    <option value="Network / Internet"<?= (($_POST['category'] ?? '') === 'Network / Internet') ? 'selected' : '' ?>>Network / Internet</option>
                    <option value="HR"                <?= (($_POST['category'] ?? '') === 'HR')                ? 'selected' : '' ?>>HR</option>
                    <option value="Facilities"        <?= (($_POST['category'] ?? '') === 'Facilities')        ? 'selected' : '' ?>>Facilities</option>
                    <option value="Finance"           <?= (($_POST['category'] ?? '') === 'Finance')           ? 'selected' : '' ?>>Finance</option>
                    <option value="Other"             <?= (($_POST['category'] ?? '') === 'Other')             ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low"      <?= (($_POST['priority'] ?? 'medium') === 'low')      ? 'selected' : '' ?>>Low</option>
                    <option value="medium"   <?= (($_POST['priority'] ?? 'medium') === 'medium')   ? 'selected' : '' ?>>Medium</option>
                    <option value="high"     <?= (($_POST['priority'] ?? 'medium') === 'high')     ? 'selected' : '' ?>>High</option>
                    <option value="critical" <?= (($_POST['priority'] ?? 'medium') === 'critical') ? 'selected' : '' ?>>Critical</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description <span style="color:var(--danger)">*</span></label>
            <textarea id="description" name="description" class="form-control"
                      placeholder="Describe the issue in detail — what happened, when, and any steps to reproduce."
                      rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="attachment">Attachment <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
            <input type="file" id="attachment" name="attachment" class="form-control"
                   accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
            <p class="form-hint">Allowed: jpg, png, gif, pdf, doc, docx, txt &mdash; max 5MB</p>
        </div>

        <div style="display:flex;gap:12px;align-items:center;">
            <button type="submit" class="btn btn-primary">Submit Issue</button>
            <a href="dashboard.php" class="btn btn-outline">Cancel</a>
        </div>

    </form>
</div>

<?php include '../includes/footer.php'; ?>