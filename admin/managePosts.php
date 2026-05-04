<?php
    session_start();
    include '../connection.php';
    include '../functions.php';

    $user_data = check_admin($con);
    $errors  = [];
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_post') {
        $targetPostID = (int)($_POST['targetPostID'] ?? 0);
        if ($targetPostID > 0) {
            $checkStmt = mysqli_prepare($con,
                "SELECT parentID FROM posts WHERE postID = ? LIMIT 1");
            mysqli_stmt_bind_param($checkStmt, 'i', $targetPostID);
            mysqli_stmt_execute($checkStmt);
            $post = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));

            if ($post) {
                if (is_null($post['parentID'])) {
                    // Root thread: cascade delete replies and their reports
                    $s = mysqli_prepare($con,
                        "DELETE FROM reports WHERE postID IN
                         (SELECT postID FROM posts WHERE parentID = ?)");
                    mysqli_stmt_bind_param($s, 'i', $targetPostID);
                    mysqli_stmt_execute($s);

                    $s = mysqli_prepare($con, "DELETE FROM posts WHERE parentID = ?");
                    mysqli_stmt_bind_param($s, 'i', $targetPostID);
                    mysqli_stmt_execute($s);
                }
                // Delete reports for the post itself
                $s = mysqli_prepare($con, "DELETE FROM reports WHERE postID = ?");
                mysqli_stmt_bind_param($s, 'i', $targetPostID);
                mysqli_stmt_execute($s);

                // Delete the post
                $s = mysqli_prepare($con, "DELETE FROM posts WHERE postID = ?");
                mysqli_stmt_bind_param($s, 'i', $targetPostID);
                if (mysqli_stmt_execute($s)) {
                    log_admin_action($con, (int)$user_data['userID'],
                        'Deleted post', 'post', $targetPostID);
                    header('Location: managePosts.php?deleted=1');
                    exit;
                }
                $errors[] = 'Error deleting post.';
            } else {
                $errors[] = 'Post not found.';
            }
        }
    }

    if (isset($_GET['deleted'])) $success = 'Post deleted successfully.';

    $search = trim($_GET['search'] ?? '');
    $params = [];
    $types  = '';

    $query = "SELECT p.postID, p.parentID, p.title, p.body, p.date,
                     u.username AS author,
                     COUNT(DISTINCT r.reportID) AS reportCount
              FROM posts p
              JOIN user u ON u.userID = p.authorID
              LEFT JOIN reports r ON r.postID = p.postID
              WHERE 1=1";

    if ($search !== '') {
        $query .= " AND (p.title LIKE ? OR p.body LIKE ? OR u.username LIKE ?)";
        $like   = '%' . $search . '%';
        $params = [$like, $like, $like];
        $types  = 'sss';
    }

    $query .= " GROUP BY p.postID ORDER BY p.date DESC";

    $stmt = mysqli_prepare($con, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $postsResult = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Posts — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #ff746c; }</style>
</head>
<body>
    <?php set_header(); ?>
    <div class="container mt-4">
        <h2>Manage Posts</h2>
        <a href="dashboard.php" class="btn btn-sm btn-secondary mb-3">← Back to Dashboard</a>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>

        <form method="GET" class="d-flex gap-2 mb-3">
            <input type="text" name="search" class="form-control w-auto"
                   placeholder="Search title, body, or author"
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if($search !== ''): ?>
                <a href="managePosts.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>

        <div style="background-color:white; border-radius:8px; padding:15px; overflow-x:auto;">
        <table class="table table-striped table-sm align-middle">
            <thead>
                <tr>
                    <th>ID</th><th>Type</th><th>Title / Preview</th>
                    <th>Author</th><th>Date</th><th>Reports</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($p = mysqli_fetch_assoc($postsResult)): ?>
                <?php
                    $isReply   = !is_null($p['parentID']);
                    $typeLabel = $isReply
                        ? '<span class="badge bg-info text-dark">Reply ↩ #'.(int)$p['parentID'].'</span>'
                        : '<span class="badge bg-primary">Thread</span>';
                    $titleText = $isReply
                        ? htmlspecialchars(mb_substr($p['body'], 0, 60)) . '…'
                        : htmlspecialchars($p['title'] ?? '(no title)');
                    $threadLink = $isReply
                        ? '../thread.php?postID=' . (int)$p['parentID']
                        : '../thread.php?postID=' . (int)$p['postID'];
                ?>
                <tr>
                    <td><?php echo (int)$p['postID']; ?></td>
                    <td><?php echo $typeLabel; ?></td>
                    <td><?php echo $titleText; ?></td>
                    <td><?php echo htmlspecialchars($p['author']); ?></td>
                    <td><?php echo htmlspecialchars($p['date']); ?></td>
                    <td>
                        <?php if((int)$p['reportCount'] > 0): ?>
                            <span class="badge bg-warning text-dark"><?php echo (int)$p['reportCount']; ?></span>
                        <?php else: ?>
                            0
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                        <a href="<?php echo $threadLink; ?>" class="btn btn-sm btn-info"
                           target="_blank">View Thread</a>
                        <form method="POST"
                              onsubmit="return confirm('Delete this post<?php echo !$isReply ? ' and ALL its replies' : ''; ?>? This cannot be undone.');">
                            <input type="hidden" name="action" value="delete_post">
                            <input type="hidden" name="targetPostID" value="<?php echo (int)$p['postID']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
