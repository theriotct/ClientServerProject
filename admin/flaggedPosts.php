<?php
    session_start();
    include '../connection.php';
    include '../functions.php';

    $user_data = check_admin($con);
    $errors  = [];
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action    = $_POST['action'] ?? '';
        $reportID  = (int)($_POST['reportID']  ?? 0);
        $adminNote = trim($_POST['adminNote'] ?? '');
        $adminID   = (int)$user_data['userID'];

        if ($action === 'mark_reviewed' && $reportID > 0) {
            $s = mysqli_prepare($con,
                "UPDATE reports
                 SET status = 'Reviewed', reviewedOn = NOW(), reviewedBy = ?, adminNote = ?
                 WHERE reportID = ?");
            mysqli_stmt_bind_param($s, 'isi', $adminID, $adminNote, $reportID);
            if (mysqli_stmt_execute($s)) {
                log_admin_action($con, $adminID, 'Marked report reviewed',
                    'report', $reportID, $adminNote ?: null);
                header('Location: flaggedPosts.php?updated=1');
                exit;
            }
            $errors[] = 'Error updating report.';
        }

        if ($action === 'dismiss' && $reportID > 0) {
            $s = mysqli_prepare($con,
                "UPDATE reports
                 SET status = 'Dismissed', reviewedOn = NOW(), reviewedBy = ?, adminNote = ?
                 WHERE reportID = ?");
            mysqli_stmt_bind_param($s, 'isi', $adminID, $adminNote, $reportID);
            if (mysqli_stmt_execute($s)) {
                log_admin_action($con, $adminID, 'Dismissed report',
                    'report', $reportID, $adminNote ?: null);
                header('Location: flaggedPosts.php?updated=1');
                exit;
            }
            $errors[] = 'Error updating report.';
        }

        if ($action === 'delete_post' && $reportID > 0) {
            $targetPostID = (int)($_POST['targetPostID'] ?? 0);
            if ($targetPostID > 0) {
                $checkStmt = mysqli_prepare($con,
                    "SELECT parentID FROM posts WHERE postID = ? LIMIT 1");
                mysqli_stmt_bind_param($checkStmt, 'i', $targetPostID);
                mysqli_stmt_execute($checkStmt);
                $post = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));

                if ($post) {
                    if (is_null($post['parentID'])) {
                        $s = mysqli_prepare($con,
                            "DELETE FROM reports WHERE postID IN
                             (SELECT postID FROM posts WHERE parentID = ?)");
                        mysqli_stmt_bind_param($s, 'i', $targetPostID);
                        mysqli_stmt_execute($s);

                        $s = mysqli_prepare($con, "DELETE FROM posts WHERE parentID = ?");
                        mysqli_stmt_bind_param($s, 'i', $targetPostID);
                        mysqli_stmt_execute($s);
                    }

                    // Mark the triggering report as Removed (keep for audit trail)
                    $s = mysqli_prepare($con,
                        "UPDATE reports
                         SET status = 'Removed', reviewedOn = NOW(), reviewedBy = ?, adminNote = ?
                         WHERE reportID = ?");
                    mysqli_stmt_bind_param($s, 'isi', $adminID, $adminNote, $reportID);
                    mysqli_stmt_execute($s);

                    // Delete remaining reports for this post
                    $s = mysqli_prepare($con,
                        "DELETE FROM reports WHERE postID = ? AND reportID != ?");
                    mysqli_stmt_bind_param($s, 'ii', $targetPostID, $reportID);
                    mysqli_stmt_execute($s);

                    // Delete the post
                    $s = mysqli_prepare($con, "DELETE FROM posts WHERE postID = ?");
                    mysqli_stmt_bind_param($s, 'i', $targetPostID);
                    if (mysqli_stmt_execute($s)) {
                        log_admin_action($con, $adminID,
                            'Deleted post via report', 'post', $targetPostID,
                            $adminNote ?: null);
                        header('Location: flaggedPosts.php?deleted=1');
                        exit;
                    }
                    $errors[] = 'Error deleting post.';
                } else {
                    $errors[] = 'Post not found (may have already been deleted).';
                }
            }
        }
    }

    if (isset($_GET['updated']))      $success = 'Report updated.';
    elseif (isset($_GET['deleted']))  $success = 'Post deleted and report marked Removed.';

    $statusFilter  = trim($_GET['status'] ?? '');
    alert($statusFilter);
    $validStatuses = ['Pending', 'Reviewed', 'Dismissed', 'Removed'];
    if (!in_array($statusFilter, $validStatuses)) $statusFilter = '';

    $params = [];
    $types  = '';
    $query  = "SELECT r.*,
                      LEFT(p.body, 80) AS bodyPreview,
                      p.parentID       AS postParentID,
                      pa.username      AS postAuthor,
                      rp.username      AS reporter
               FROM reports r
               JOIN posts p  ON p.postID  = r.postID
               JOIN user pa  ON pa.userID = p.authorID
               JOIN user rp  ON rp.userID = r.reporterID
               WHERE 1=1";

    if ($statusFilter !== '') {
        $query   .= " AND r.status = ?";
        $params[] = $statusFilter;
        $types   .= 's';
    }

    $query .= " ORDER BY r.createdOn DESC";

    $stmt = mysqli_prepare($con, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $reportsResult = mysqli_stmt_get_result($stmt);

    $badgeMap = [
        'Pending'   => 'bg-warning text-dark',
        'Reviewed'  => 'bg-success',
        'Dismissed' => 'bg-secondary',
        'Removed'   => 'bg-danger',
    ];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Flagged Posts — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #ff746c; }</style>
</head>
<body>
    <?php set_header(); ?>
    <div class="container mt-4">
        <h2>Flagged Posts</h2>
        <a href="dashboard.php" class="btn btn-sm btn-secondary mb-3">← Back to Dashboard</a>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>

        <!-- Status Filter -->
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <a href="flaggedPosts.php"
               class="btn btn-sm <?php echo $statusFilter === '' ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
            <?php foreach($validStatuses as $s): ?>
                <a href="flaggedPosts.php?status=<?php echo urlencode($s); ?>"
                   class="btn btn-sm <?php echo $statusFilter === $s ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    <?php echo $s; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php
        $rows = mysqli_fetch_all($reportsResult, MYSQLI_ASSOC);
        if (empty($rows)): ?>
            <div style="background-color:white; border-radius:8px; padding:20px;" class="text-center">
                <p class="mb-0">No reports found.</p>
            </div>
        <?php else: ?>
        <div class="d-flex flex-column gap-3">
        <?php foreach($rows as $r): ?>
            <?php
                $badge     = $badgeMap[$r['status']] ?? 'bg-secondary';
                $isReply   = !is_null($r['postParentID']);
                $threadID  = $isReply ? (int)$r['postParentID'] : (int)$r['postID'];
                $isPending = $r['status'] === 'Pending';
            ?>
            <div style="background-color:white; border-radius:8px; padding:15px;">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <strong>Report #<?php echo (int)$r['reportID']; ?></strong>
                        — Post #<?php echo (int)$r['postID']; ?>
                        <span class="badge <?php echo $badge; ?> ms-1"><?php echo $r['status']; ?></span>
                    </div>
                    <small class="text-muted"><?php echo htmlspecialchars($r['createdOn']); ?></small>
                </div>

                <p class="mt-2 mb-1">
                    <em>"<?php echo htmlspecialchars($r['bodyPreview']); ?>…"</em>
                </p>
                <p class="mb-1">
                    <strong>Post author:</strong> <?php echo htmlspecialchars($r['postAuthor']); ?> &nbsp;
                    <strong>Reported by:</strong> <?php echo htmlspecialchars($r['reporter']); ?>
                </p>
                <?php if (!empty($r['reason'])): ?>
                    <p class="mb-1"><strong>Reason:</strong> <?php echo htmlspecialchars($r['reason']); ?></p>
                <?php endif; ?>
                <?php if (!empty($r['adminNote'])): ?>
                    <p class="mb-1"><strong>Admin note:</strong> <?php echo htmlspecialchars($r['adminNote']); ?></p>
                <?php endif; ?>

                <div class="d-flex gap-2 flex-wrap mt-2">
                    <a href="../thread.php?postID=<?php echo $threadID; ?>"
                       class="btn btn-sm btn-info" target="_blank">View Thread</a>

                    <?php if ($isPending): ?>
                        <!-- Mark Reviewed -->
                        <form method="POST" class="d-flex gap-1 align-items-center">
                            <input type="hidden" name="action"   value="mark_reviewed">
                            <input type="hidden" name="reportID" value="<?php echo (int)$r['reportID']; ?>">
                            <input type="text" name="adminNote" class="form-control form-control-sm"
                                   placeholder="Optional note" style="width:180px;">
                            <button type="submit" class="btn btn-sm btn-success">Mark Reviewed</button>
                        </form>

                        <!-- Dismiss -->
                        <form method="POST" class="d-flex gap-1 align-items-center">
                            <input type="hidden" name="action"   value="dismiss">
                            <input type="hidden" name="reportID" value="<?php echo (int)$r['reportID']; ?>">
                            <input type="text" name="adminNote" class="form-control form-control-sm"
                                   placeholder="Optional note" style="width:180px;">
                            <button type="submit" class="btn btn-sm btn-secondary">Dismiss</button>
                        </form>

                        <!-- Delete Post -->
                        <form method="POST" class="d-flex gap-1 align-items-center"
                              onsubmit="return confirm('Delete this post<?php echo !$isReply ? ' and ALL its replies' : ''; ?>?');">
                            <input type="hidden" name="action"       value="delete_post">
                            <input type="hidden" name="reportID"     value="<?php echo (int)$r['reportID']; ?>">
                            <input type="hidden" name="targetPostID" value="<?php echo (int)$r['postID']; ?>">
                            <input type="text" name="adminNote" class="form-control form-control-sm"
                                   placeholder="Optional note" style="width:180px;">
                            <button type="submit" class="btn btn-sm btn-danger">Delete Post</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
