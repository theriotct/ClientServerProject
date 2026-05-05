<?php
  session_start();
  include "connection.php";
  include "functions.php";

  $user_data = check_login($con);

  if(isset($_GET['userID'])){
    $userID = $_GET['userID'];
  } else {
    $userID = $user_data['userID'];
  }
  $query = "SELECT `fname`, `lname`, `username` FROM `user` WHERE userID = $userID";
  $profile_result = mysqli_query($con, $query);
  if($profile_result && mysqli_num_rows($profile_result) > 0){
      $profile_user_data = mysqli_fetch_assoc($profile_result);
  }else{
      not_found();
  }

  function get_posts($con, $userID){

    $query = "WITH RECURSIVE thread AS (
                SELECT p.postID AS replyID, p.parentID, p.title, p.body, p.authorID, p.date, p.postID AS originalReplyID
                FROM posts p
                WHERE p.authorID = $userID

                UNION ALL

                SELECT parent.postID AS replyID, parent.parentID, parent.title, parent.body, parent.authorID, parent.date, t.originalReplyID
                FROM posts parent
                JOIN thread t ON t.parentID = parent.postID
              )
              SELECT 
                root.postID AS parentID,
                root.title AS parentTitle,
                root.authorID AS parentAuthorID,
                root.date AS parentDate,
                reply.postID AS replyID,
                reply.parentID AS replyParentID,
                reply.authorID AS replyAuthorID,
                reply.body AS replyBody,
                reply.title AS replyTitle,
                reply.date AS replyDate
              FROM thread t
              JOIN posts root ON root.postID = t.replyID AND root.parentID IS NULL
              JOIN posts reply ON reply.postID = t.originalReplyID
              ORDER BY reply.date DESC";

    $result = mysqli_query($con, $query);

    if (!$result) {
        echo '<div style="background-color:#eee;padding:10px;margin:10px;">
                <h4>SQL Error</h4>
                <p>User Has No Posts</p>
              </div>';
        return;
    }

    if (mysqli_num_rows($result) === 0) {
        echo '<div style="background-color:#eee;padding:10px;margin:10px;">
                <h4>User Has No Posts</h4>
              </div>';
        return;
    }

    while($post = mysqli_fetch_assoc($result)){
        echo '<div style="background-color:#eee;padding:10px;margin:10px;">
                <h4><a href="thread.php?postID='.$post['parentID'].'">'.$post['parentTitle'].'</a></h4>
                <p>'.$post['replyBody'].'</p>
                <p>'.$post['replyDate'].'</p>
              </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($profile['username']); ?> — Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: lightblue; }
        .profile-card {
            background-color: white;
            border: 2px solid #ff5c00;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            text-align: center;
        }
        .profile-img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ff5c00;
            margin-bottom: 12px;
        }
        .profile-name     { color: blue; margin-bottom: 0; font-size: 1.2rem; }
        .profile-username { color: #555; margin-top: 0; font-size: 0.9rem; }
        .content-card {
            background-color: white;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 16px;
            border-left: 4px solid #ff5c00;
        }
        .content-card h6 {
            color: #ff5c00;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
        }
        .activity-item {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            border-left: 3px solid #ff5c00;
        }
        .activity-item a { color: blue; text-decoration: none; font-weight: 600; }
        .activity-item a:hover { text-decoration: underline; }
        .btn-orange { background-color: #ff5c00; color: white; border: none; }
        .btn-orange:hover { background-color: #dc4f00; color: white; }
        .stat-badge {
            background-color: #ff5c00;
            color: white;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.85rem;
            display: inline-block;
            margin: 3px;
        }
        .meta-row { font-size: 0.85rem; color: #555; margin: 4px 0; text-align: left; }
    </style>
</head>
<body>
    <?php set_header(); ?>
    <div class="container mt-4">
        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Left column -->
            <div class="col-md-4">
                <div class="profile-card">
                    <img src="<?php echo htmlspecialchars($avatar); ?>"
                         class="profile-img" alt="Profile Picture">

                    <h5 class="profile-name"><?php echo htmlspecialchars($displayName); ?></h5>
                    <p class="profile-username">@<?php echo htmlspecialchars($profile['username']); ?></p>

                    <?php if (!empty($profile['location'])): ?>
                        <div class="meta-row">📍 <?php echo htmlspecialchars($profile['location']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($profile['major'])): ?>
                        <div class="meta-row">🎓 <?php echo htmlspecialchars($profile['major']); ?></div>
                    <?php endif; ?>

                    <hr>

                    <div class="meta-row"><strong>Joined:</strong> <?php echo $joinedDate; ?></div>
                    <div class="meta-row"><strong>Last active:</strong> <?php echo $lastActiveDate; ?></div>

                    <div class="mt-3">
                        <span class="stat-badge">Posts: <?php echo $postCount; ?></span>
                        <span class="stat-badge">Replies: <?php echo $replyCount; ?></span>
                    </div>

                    <div class="mt-3 d-grid gap-2">
                        <?php if ($isOwnProfile): ?>
                            <a href="editProfile.php" class="btn btn-orange">Edit Profile</a>
                        <?php else: ?>
                            <a href="message.php?userID=<?php echo (int)$profileUserID; ?>"
                               class="btn btn-primary">Message</a>
                            <button class="btn btn-danger">Report User</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="col-md-8 mt-4">
                <?php if (!empty($profile['biography'])): ?>
                <div class="content-card">
                    <h6>About Me</h6>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($profile['biography'])); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($profile['interests'])): ?>
                <div class="content-card">
                    <h6>Interests</h6>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($profile['interests'])); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($profile['signature'])): ?>
                <div class="content-card">
                    <h6>Signature</h6>
                    <p class="mb-0 fst-italic"><?php echo htmlspecialchars($profile['signature']); ?></p>
                </div>
                <?php endif; ?>

                <div class="content-card">
                    <h6>Recent Activity</h6>
                    <?php if (empty($activityPosts)): ?>
                        <p class="mb-0 text-muted">This user has not posted yet.</p>
                    <?php else: ?>
                        <?php foreach ($activityPosts as $post): ?>
                            <div class="activity-item">
                                <a href="thread.php?postID=<?php echo (int)$post['threadID']; ?>">
                                    <?php echo htmlspecialchars($post['threadTitle']); ?>
                                </a>
                                <p class="mb-1 mt-1 small">
                                    <?php echo htmlspecialchars(mb_substr($post['body'], 0, 120)); ?><?php echo mb_strlen($post['body']) > 120 ? '…' : ''; ?>
                                </p>
                                <small class="text-muted"><?php echo htmlspecialchars(date('M j, Y', strtotime($post['date']))); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
