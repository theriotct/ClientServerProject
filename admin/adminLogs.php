<?php
    session_start();
    include '../connection.php';
    include '../functions.php';

    $user_data = check_admin($con, true); // super admin only

    $perPage = 50;
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $offset  = ($page - 1) * $perPage;

    $total      = mysqli_fetch_assoc(
        mysqli_query($con, "SELECT COUNT(*) AS cnt FROM admin_logs"))['cnt'];
    $totalPages = (int)ceil($total / $perPage);

    $stmt = mysqli_prepare($con,
        "SELECT al.*, u.username AS adminUsername
         FROM admin_logs al
         JOIN user u ON u.userID = al.adminID
         ORDER BY al.createdOn DESC
         LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, 'ii', $perPage, $offset);
    mysqli_stmt_execute($stmt);
    $logsResult = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Logs — Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #ff746c; }</style>
</head>
<body>
    <?php set_header(); ?>
    <div class="container mt-4">
        <h2>Admin Logs</h2>
        <a href="dashboard.php" class="btn btn-sm btn-secondary mb-3">← Back to Dashboard</a>
        <p class="text-white">Showing page <?php echo $page; ?> of <?php echo max(1, $totalPages); ?>
           (<?php echo (int)$total; ?> total entries)</p>

        <div style="background-color:white; border-radius:8px; padding:15px; overflow-x:auto;">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th><th>Admin</th><th>Action</th>
                    <th>Target Type</th><th>Target ID</th><th>Note</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php while($log = mysqli_fetch_assoc($logsResult)): ?>
                <tr>
                    <td><?php echo (int)$log['logID']; ?></td>
                    <td><?php echo htmlspecialchars($log['adminUsername']); ?></td>
                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                    <td><?php echo htmlspecialchars($log['targetType']); ?></td>
                    <td><?php echo $log['targetID'] !== null ? (int)$log['targetID'] : '—'; ?></td>
                    <td><?php echo htmlspecialchars($log['note'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($log['createdOn']); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>

        <?php if($totalPages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination">
                <?php if($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">← Prev</a>
                    </li>
                <?php endif; ?>
                <?php for($p = max(1, $page - 3); $p <= min($totalPages, $page + 3); $p++): ?>
                    <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next →</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
