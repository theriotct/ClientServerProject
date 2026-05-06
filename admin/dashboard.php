<?php
    session_start();
    include '../connection.php';
    include '../functions.php';

    $user_data = check_admin($con);

    $totalUsers   = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS cnt FROM user"))['cnt'];
    $totalPosts   = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS cnt FROM posts"))['cnt'];
    $totalReports = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS cnt FROM reports"))['cnt'];
    $pendingReports = mysqli_fetch_assoc(mysqli_query($con,
        "SELECT COUNT(*) AS cnt FROM reports WHERE status = 'Pending'"))['cnt'];

    $recentUsersResult = mysqli_query($con,
        "SELECT userID, username, createdOn FROM user ORDER BY createdOn DESC LIMIT 5");

    $recentReportsResult = mysqli_query($con,
        "SELECT r.reportID, r.status, r.createdOn,
                LEFT(p.body, 60) AS bodyPreview,
                u.username AS reporter
         FROM reports r
         JOIN posts p ON p.postID = r.postID
         JOIN user  u ON u.userID = r.reporterID
         ORDER BY r.createdOn DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #ff746c; }</style>
</head>
<body>
    <?php set_header(); ?>
    <div class="container mt-5">
        <h2>Admin Dashboard</h2>

        <!-- Stats -->
        <div class="row g-3 mt-2">
            <?php foreach([
                ['Total Users',    $totalUsers],
                ['Total Posts',    $totalPosts],
                ['Total Reports',  $totalReports],
                ['Pending Reports',$pendingReports],
            ] as [$label, $val]): ?>
            <div class="col-md-3">
                <div class="card text-center" style="background-color:#00b7eb;">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo (int)$val; ?></h3>
                        <p class="card-text mb-0"><?php echo $label; ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Navigation -->
        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Moderation</h4>
                <ul class="list-group">
                    <li class="list-group-item" style="background-color:#00b7eb;">
                        <a href="manageUsers.php" style="color:black;" class="text-decoration-none">Manage Users</a>
                    </li>
                    <li class="list-group-item" style="background-color:#00b7eb;">
                        <a href="managePosts.php" style="color:black;" class="text-decoration-none">Manage Posts</a>
                    </li>
                    <li class="list-group-item" style="background-color:#00b7eb;">
                        <a href="flaggedPosts.php" style="color:black;" class="text-decoration-none">
                            Flagged Posts
                            <?php if($pendingReports > 0): ?>
                                <span class="badge bg-danger ms-1"><?php echo (int)$pendingReports; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
            <?php if((int)$user_data['isAdmin'] === 1): ?>
            <div class="col-md-6">
                <h4>Super Admin</h4>
                <ul class="list-group">
                    <li class="list-group-item" style="background-color:#00b7eb;">
                        <a href="adminLogs.php" style="color:black;" class="text-decoration-none">Admin Logs</a>
                    </li>
                    <li class="list-group-item" style="background-color:#00b7eb;">
                        <a href="super/sql_injector.php" style="color:black;" class="text-decoration-none">SQL Injector</a>
                    </li>
                    <li class="list-group-item" style="background-color:#00b7eb;">
                        <a href="manageAdmins.php" style="color:black;" class="text-decoration-none">Manage Admins Injector</a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Recent Users</h4>
                <div style="background-color:white; border-radius:8px; padding:10px;">
                <table class="table table-sm mb-0">
                    <thead><tr><th>ID</th><th>Username</th><th>Joined</th></tr></thead>
                    <tbody>
                    <?php while($u = mysqli_fetch_assoc($recentUsersResult)): ?>
                        <tr>
                            <td><?php echo (int)$u['userID']; ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['createdOn']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Recent Reports</h4>
                <div style="background-color:white; border-radius:8px; padding:10px;">
                <table class="table table-sm mb-0">
                    <thead><tr><th>ID</th><th>Post</th><th>Reporter</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php while($r = mysqli_fetch_assoc($recentReportsResult)): ?>
                        <?php
                            $badgeClass = match($r['status']) {
                                'Pending'   => 'bg-warning text-dark',
                                'Reviewed'  => 'bg-success',
                                'Dismissed' => 'bg-secondary',
                                'Removed'   => 'bg-danger',
                                default     => 'bg-secondary',
                            };
                        ?>
                        <tr>
                            <td><?php echo (int)$r['reportID']; ?></td>
                            <td><?php echo htmlspecialchars($r['bodyPreview']); ?>…</td>
                            <td><?php echo htmlspecialchars($r['reporter']); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $r['status']; ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Account Links -->
        <div class="mt-4">
            <h4>Account</h4>
            <ul class="list-group">
                <li class="list-group-item" style="background-color:#00b7eb;">
                    <a href="../index.php" style="color:black;" class="text-decoration-none">View Home Page</a>
                </li>
                <li class="list-group-item" style="background-color:#00b7eb;">
                    <a href="../marketplace.php" style="color:black;" class="text-decoration-none">Browse Marketplace</a>
                </li>
                <li class="list-group-item" style="background-color:#00b7eb;">
                    <a href="../profile.php" style="color:black;" class="text-decoration-none">View Profile</a>
                </li>
                <li class="list-group-item" style="background-color:#00b7eb;">
                    <a href="../message.php" style="color:black;" class="text-decoration-none">Messages</a>
                </li>
                <li class="list-group-item" style="background-color: #00b7eb;">
                    <a style="color: black;" href="../following.php" class="text-decoration-none">Following</a></li>
                <li class="list-group-item" style="background-color: #00b7eb;">
                    <a style="color: black;" href="../followers.php" class="text-decoration-none">Followers</a></li>
            </ul>
            <br>
            <a class="btn btn-primary" href="/logout.php">Logout</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
