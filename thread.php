<?php
  session_start();
  include 'connection.php';
  include 'functions.php';

  if(isset($_GET['postID'])){
      $postID = (int)$_GET['postID'];

      // Climb to root — if a reply postID is linked directly, redirect to root thread
      $currentID = $postID;
      while(true){
          $res = mysqli_query($con, "SELECT parentID FROM posts WHERE postID = $currentID LIMIT 1");
          $row = mysqli_fetch_assoc($res);
          if(!$row || $row['parentID'] === NULL) break;
          $currentID = (int)$row['parentID'];
      }
      if($currentID != $postID){
          header("Location: thread.php?postID=" . $currentID);
          exit;
      }

      $query = "WITH RECURSIVE thread AS (
                  SELECT * FROM posts WHERE postID = $postID
                  UNION ALL
                  SELECT p.* FROM posts p
                  INNER JOIN thread t ON p.parentID = t.postID
                )
                SELECT t.*, u.username,
                  COALESCE(r.likes, 0)    AS totalLikes,
                  COALESCE(r.dislikes, 0) AS totalDislikes
                FROM thread t
                JOIN `user` u ON u.userID = t.authorID
                LEFT JOIN (
                  SELECT refPostID,
                    SUM(CASE WHEN `like/dislike` = 1 THEN 1 ELSE 0 END) AS likes,
                    SUM(CASE WHEN `like/dislike` = 0 THEN 1 ELSE 0 END) AS dislikes
                  FROM `like`
                  GROUP BY refPostID
                ) r ON r.refPostID = t.postID
                ORDER BY t.date ASC";

      $result = mysqli_query($con, $query);
      $posts   = mysqli_fetch_all($result, MYSQLI_ASSOC);

      if(count($posts) == 0){
          header("HTTP/1.1 404 Not Found");
          include('404.html');
          die;
      }
  }else{
      header("HTTP/1.1 404 Not Found");
      include('404.html');
      die;
  }

  $user_data = check_login($con);

  // Build set of postIDs already reported by this user
  $myReports = [];
  if($user_data){
      $rStmt = mysqli_prepare($con, "SELECT postID FROM reports WHERE reporterID = ?");
      mysqli_stmt_bind_param($rStmt, 'i', $user_data['userID']);
      mysqli_stmt_execute($rStmt);
      $rResult = mysqli_stmt_get_result($rStmt);
      while($r = mysqli_fetch_assoc($rResult)){
          $myReports[] = (int)$r['postID'];
      }
  }

  if($_SERVER['REQUEST_METHOD'] == "POST"){
      if(isset($_SESSION['userID'])){

          // Reply
          if(isset($_POST['reply'])){
              $reply    = $_POST['reply'];
              $userID   = $user_data['userID'];
              $parentID = $posts[count($posts) - 1]['postID'];
              $query    = "INSERT INTO `posts` (`parentID`, `authorID`, `title`, `body`, `date`)
                           VALUES ('$parentID', '$userID', NULL, '$reply', CURRENT_TIMESTAMP)";
              $result   = mysqli_query($con, $query);
              if($result){
                  header('Location: thread.php?postID=' . $postID);
                  exit;
              }else{
                  alert('Error posting reply');
              }
          }

          // Like
          elseif(isset($_POST['like'])){
              $postLikedID = (int)$_POST['postID'];
              $userID      = (int)$user_data['userID'];
              $result      = mysqli_query($con, "SELECT `like/dislike` FROM `like`
                                                 WHERE userID = $userID AND refPostID = $postLikedID");
              if(mysqli_num_rows($result) > 0){
                  $row = mysqli_fetch_assoc($result);
                  $query = $row['like/dislike'] == 1
                      ? "DELETE FROM `like` WHERE userID = $userID AND refPostID = $postLikedID"
                      : "UPDATE `like` SET `like/dislike` = 1 WHERE userID = $userID AND refPostID = $postLikedID";
              }else{
                  $query = "INSERT INTO `like` (userID, refPostID, `like/dislike`) VALUES ($userID, $postLikedID, 1)";
              }
              mysqli_query($con, $query);
              header('Location: thread.php?postID=' . $postID);
              exit;
          }

          // Dislike
          elseif(isset($_POST['dislike'])){
              $postLikedID = (int)$_POST['postID'];
              $userID      = (int)$user_data['userID'];
              $result      = mysqli_query($con, "SELECT `like/dislike` FROM `like`
                                                 WHERE userID = $userID AND refPostID = $postLikedID");
              if(mysqli_num_rows($result) > 0){
                  $row = mysqli_fetch_assoc($result);
                  $query = $row['like/dislike'] == 0
                      ? "DELETE FROM `like` WHERE userID = $userID AND refPostID = $postLikedID"
                      : "UPDATE `like` SET `like/dislike` = 0 WHERE userID = $userID AND refPostID = $postLikedID";
              }else{
                  $query = "INSERT INTO `like` (userID, refPostID, `like/dislike`) VALUES ($userID, $postLikedID, 0)";
              }
              mysqli_query($con, $query);
              header('Location: thread.php?postID=' . $postID);
              exit;
          }

          // Report
          elseif(isset($_POST['report_post'])){
              $reportPostID = (int)$_POST['report_post_id'];
              $reporterID   = (int)$user_data['userID'];
              $stmt = mysqli_prepare($con,
                  "INSERT IGNORE INTO reports (postID, reporterID) VALUES (?, ?)");
              mysqli_stmt_bind_param($stmt, 'ii', $reportPostID, $reporterID);
              if(mysqli_stmt_execute($stmt)){
                  header('Location: thread.php?postID=' . $postID);
                  exit;
              }
          }

          // Inline edit
          elseif(isset($_POST['edit_post'])){
              $editPostID = (int)$_POST['edit_post_id'];
              $editBody   = trim($_POST['edit_body'] ?? '');
              $isAdmin    = isset($user_data['isAdmin']) && $user_data['isAdmin'] !== null;
              $checkStmt  = mysqli_prepare($con,
                  "SELECT authorID, title FROM posts WHERE postID = ? LIMIT 1");
              mysqli_stmt_bind_param($checkStmt, 'i', $editPostID);
              mysqli_stmt_execute($checkStmt);
              $editPost = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));
              if($editPost && ((int)$editPost['authorID'] === (int)$user_data['userID'] || $isAdmin)){
                  if(isset($_POST['edit_title']) && $editPost['title'] !== null){
                      $editTitle  = trim($_POST['edit_title'] ?? '');
                      $updateStmt = mysqli_prepare($con,
                          "UPDATE posts SET body = ?, title = ? WHERE postID = ?");
                      mysqli_stmt_bind_param($updateStmt, 'ssi', $editBody, $editTitle, $editPostID);
                  }else{
                      $updateStmt = mysqli_prepare($con,
                          "UPDATE posts SET body = ? WHERE postID = ?");
                      mysqli_stmt_bind_param($updateStmt, 'si', $editBody, $editPostID);
                  }
                  if(mysqli_stmt_execute($updateStmt)){
                      header('Location: thread.php?postID=' . $postID);
                      exit;
                  }
              }
          }

          // Delete
          elseif(isset($_POST['delete'])){
              $deleteID = (int)$_POST['postID'];
              $res      = mysqli_query($con,
                  "SELECT parentID FROM posts WHERE postID = $deleteID");
              $row      = mysqli_fetch_assoc($res);
              if(!$row){
                  header('Location: thread.php?postID=' . $postID);
                  exit;
              }
              if($row['parentID'] === NULL){
                  // Root thread — delete entire tree
                  mysqli_query($con,
                      "WITH RECURSIVE thread AS (
                          SELECT postID FROM posts WHERE postID = $deleteID
                          UNION ALL
                          SELECT p.postID FROM posts p
                          INNER JOIN thread t ON p.parentID = t.postID
                       )
                       DELETE FROM posts WHERE postID IN (SELECT postID FROM thread)");
                  header('Location: index.php');
                  exit;
              }
              // Reply — re-parent its children then delete
              $parentValue = (int)$row['parentID'];
              mysqli_query($con,
                  "UPDATE posts SET parentID = $parentValue WHERE parentID = $deleteID");
              mysqli_query($con, "DELETE FROM posts WHERE postID = $deleteID");
              header('Location: thread.php?postID=' . $postID);
              exit;
          }
      }
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>The Power of Awesome Ideas</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        body { background-color: #fed8b1; }
        .right { float: right; margin-left: 10px; }
        .error-input {
            border: 2px solid #f44336 !important;
            background-color: #ffe6e6 !important;
            box-shadow: 0 0 5px rgba(244,67,54,0.3);
        }
        .error-message { color: #f44336; font-size: 13px; margin-top: 5px; display: block; font-weight: bold; }
        .success-input { border: 2px solid #4CAF50 !important; background-color: #e6ffe6 !important; }
        .global-error {
            background-color: #f44336; color: white;
            padding: 10px 15px; border-radius: 4px;
            margin-bottom: 15px; display: none; font-weight: bold;
        }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
        .edit-box { display: none; padding: 10px 15px; background-color: #f8f9fa; border-top: 1px solid #ddd; }
    </style>
  </head>
  <body>
    <?php set_header(); ?>

    <div class="container" style="margin-top:20px;">

      <!-- Original Post -->
      <?php
        $canEditOriginal = $user_data && (
            (int)$user_data['userID'] === (int)$posts[0]['authorID'] ||
            (isset($user_data['isAdmin']) && $user_data['isAdmin'] !== null)
        );
        $isAdmin = $user_data && isset($user_data['isAdmin']) && $user_data['isAdmin'] !== null;
      ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3><?php echo htmlspecialchars($posts[0]['title']); ?></h3>
          <small>Posted by <?php echo htmlspecialchars($posts[0]['username']); ?>
            &bull; <?php echo htmlspecialchars($posts[0]['date']); ?></small>
        </div>
        <div class="panel-body">
          <p><?php echo nl2br(htmlspecialchars($posts[0]['body'])); ?></p>
        </div>
        <div class="panel-footer">

          <!-- Like / Dislike -->
          <form action="" method="POST" style="display:inline;">
            <input type="hidden" name="postID" value="<?php echo (int)$posts[0]['postID']; ?>">
            <input class="btn btn-sm btn-default" type="submit" name="like"
                   value="Like: <?php echo $posts[0]['totalLikes']; ?>"
                   <?php echo (!$user_data ? 'disabled' : ''); ?>>
            <input class="btn btn-sm btn-default" type="submit" name="dislike"
                   value="Dislike: <?php echo $posts[0]['totalDislikes']; ?>"
                   <?php echo (!$user_data ? 'disabled' : ''); ?>>
          </form>

          <!-- Report -->
          <?php if(!$user_data): ?>
            <a href="login.php" class="btn btn-sm btn-default">Report</a>
          <?php elseif(in_array((int)$posts[0]['postID'], $myReports)): ?>
            <span class="btn btn-sm btn-default disabled">Reported</span>
          <?php else: ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="report_post" value="1">
              <input type="hidden" name="report_post_id" value="<?php echo (int)$posts[0]['postID']; ?>">
              <button type="submit" class="btn btn-sm btn-default">Report</button>
            </form>
          <?php endif; ?>

          <!-- Delete / Edit (author or admin) -->
          <?php if($canEditOriginal): ?>
            <form method="POST" style="display:inline;"
                  onsubmit="return confirm('Delete this thread and all replies?');">
              <input type="hidden" name="postID" value="<?php echo (int)$posts[0]['postID']; ?>">
              <button type="submit" name="delete" class="btn btn-sm btn-default btn-danger right">Delete</button>
            </form>
            <button type="button" class="btn btn-sm btn-default right"
                    onclick="toggleEdit('edit-post-0')">Edit</button>
          <?php endif; ?>
        </div>

        <?php if($canEditOriginal): ?>
        <div id="edit-post-0" class="edit-box">
          <form method="POST">
            <input type="hidden" name="edit_post" value="1">
            <input type="hidden" name="edit_post_id" value="<?php echo (int)$posts[0]['postID']; ?>">
            <div class="form-group">
              <input type="text" class="form-control" name="edit_title"
                     value="<?php echo htmlspecialchars($posts[0]['title']); ?>"
                     maxlength="255" style="margin-bottom:6px;" placeholder="Title">
            </div>
            <div class="form-group">
              <textarea class="form-control" name="edit_body" rows="4"
                        maxlength="1000"><?php echo htmlspecialchars($posts[0]['body']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-primary" style="margin-top:6px;">Save</button>
            <button type="button" class="btn btn-sm btn-default" style="margin-top:6px;"
                    onclick="toggleEdit('edit-post-0')">Cancel</button>
          </form>
        </div>
        <?php endif; ?>
      </div>

      <!-- Replies -->
      <h4>Replies</h4>

      <?php for($i = 1; $i < count($posts); $i++):
          $canEdit    = $user_data && (
              (int)$user_data['userID'] === (int)$posts[$i]['authorID'] ||
              (isset($user_data['isAdmin']) && $user_data['isAdmin'] !== null)
          );
          $isReported    = in_array((int)$posts[$i]['postID'], $myReports);
          $replyPostID   = (int)$posts[$i]['postID'];
          $replyAuthorID = (int)$posts[$i]['authorID'];
      ?>
      <div class="panel panel-info">
        <div class="panel-heading">
          <strong>
            <a href="profile.php?userID=<?php echo $replyAuthorID; ?>">
              <?php echo htmlspecialchars($posts[$i]['username']); ?>
            </a>
          </strong>
          &bull; <?php echo htmlspecialchars($posts[$i]['date']); ?>
        </div>
        <div class="panel-body">
          <?php echo nl2br(htmlspecialchars($posts[$i]['body'])); ?>
        </div>
        <div class="panel-footer">

          <!-- Like / Dislike -->
          <form action="" method="POST" style="display:inline;">
            <input type="hidden" name="postID" value="<?php echo $replyPostID; ?>">
            <input class="btn btn-sm btn-default" type="submit" name="like"
                   value="Like: <?php echo (int)$posts[$i]['totalLikes']; ?>"
                   <?php echo (!$user_data ? 'disabled' : ''); ?>>
            <input class="btn btn-sm btn-default" type="submit" name="dislike"
                   value="Dislike: <?php echo (int)$posts[$i]['totalDislikes']; ?>"
                   <?php echo (!$user_data ? 'disabled' : ''); ?>>
          </form>

          <!-- Report -->
          <?php if(!$user_data): ?>
            <a href="login.php" class="btn btn-sm btn-default">Report</a>
          <?php elseif($isReported): ?>
            <span class="btn btn-sm btn-default disabled">Reported</span>
          <?php else: ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="report_post" value="1">
              <input type="hidden" name="report_post_id" value="<?php echo $replyPostID; ?>">
              <button type="submit" class="btn btn-sm btn-default">Report</button>
            </form>
          <?php endif; ?>

          <!-- Delete / Edit -->
          <?php if($canEdit): ?>
            <form method="POST" style="display:inline;"
                  onsubmit="return confirm('Delete this reply?');">
              <input type="hidden" name="postID" value="<?php echo $replyPostID; ?>">
              <button type="submit" name="delete" class="btn btn-sm btn-default btn-danger right">Delete</button>
            </form>
            <button type="button" class="btn btn-sm btn-default right"
                    onclick="toggleEdit('edit-post-<?php echo $i; ?>')">Edit</button>
          <?php endif; ?>
        </div>

        <?php if($canEdit): ?>
        <div id="edit-post-<?php echo $i; ?>" class="edit-box">
          <form method="POST">
            <input type="hidden" name="edit_post" value="1">
            <input type="hidden" name="edit_post_id" value="<?php echo $replyPostID; ?>">
            <div class="form-group">
              <textarea class="form-control" name="edit_body" rows="3"
                        maxlength="1000"><?php echo htmlspecialchars($posts[$i]['body']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-primary" style="margin-top:6px;">Save</button>
            <button type="button" class="btn btn-sm btn-default" style="margin-top:6px;"
                    onclick="toggleEdit('edit-post-<?php echo $i; ?>')">Cancel</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
      <?php endfor; ?>

      <!-- Reply Form -->
      <div class="panel panel-default">
        <div class="panel-heading"><h4>Add a Reply</h4></div>
        <div class="panel-body">
          <div class="global-error" id="globalError"></div>

          <?php if(!$user_data): ?>
            <form action="login.php" method="get">
          <?php else: ?>
            <form method="post" id="replyForm">
          <?php endif; ?>

            <div class="form-group">
              <?php if(!$user_data): ?>
                <textarea class="form-control" id="reply" name="reply" rows="5"
                          placeholder="Please log in to reply" disabled></textarea>
              <?php else: ?>
                <textarea class="form-control" id="reply" name="reply" rows="5"
                          maxlength="1000" placeholder="Write your reply..."></textarea>
              <?php endif; ?>
            </div>

            <span class="error-message" id="replyError"></span>
            <span id="charCount">0/1000</span><br><br>

            <?php if(!$user_data): ?>
              <input type="submit" class="btn btn-primary" value="Click Here To Login">
            <?php else: ?>
              <input type="submit" class="btn btn-primary" id="submitBtn" value="Post Reply">
            <?php endif; ?>
          </form>
        </div>
      </div>

    </div>

    <script>
      function toggleEdit(id) {
        var el = document.getElementById(id);
        if(el) el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
      }

      <?php if($user_data): ?>
        const textArea    = document.getElementById("reply");
        const charCounter = document.getElementById("charCount");
        const submitBtn   = document.getElementById("submitBtn");
        const replyForm   = document.getElementById("replyForm");
        const replyError  = document.getElementById("replyError");
        const globalError = document.getElementById("globalError");
        const maxChars    = 1000;

        function removeError() {
          textArea.classList.remove('error-input','success-input');
          replyError.textContent = '';
          globalError.style.display = 'none';
        }
        function addError(msg) {
          textArea.classList.add('error-input');
          textArea.classList.remove('success-input');
          replyError.textContent = msg;
        }
        function addSuccess() {
          textArea.classList.remove('error-input');
          textArea.classList.add('success-input');
          replyError.textContent = '';
        }
        function validateReply() {
          const t = textArea.value.trim();
          if(t === '')        { addError('Reply cannot be empty.');               return false; }
          if(t.length < 3)    { addError('Reply must be at least 3 characters.'); return false; }
          addSuccess();
          return true;
        }

        textArea.addEventListener("input", () => {
          const len = textArea.value.length;
          charCounter.textContent = `${len}/${maxChars}`;
          charCounter.style.color = len >= maxChars - 10 ? "red" : len >= maxChars - 200 ? "orange" : "black";
          const t = textArea.value.trim();
          if(!t) removeError();
          else if(t.length >= 3) addSuccess();
          else addError('Reply must be at least 3 characters.');
          if(globalError.style.display === 'block') globalError.style.display = 'none';
        });

        replyForm.addEventListener('submit', function(e) {
          if(!validateReply()) {
            e.preventDefault();
            globalError.textContent = 'Please fix the errors above before submitting.';
            globalError.style.display = 'block';
            submitBtn.disabled = true;
            setTimeout(() => { submitBtn.disabled = false; }, 2000);
          }
          if(textArea.value.length > maxChars) { e.preventDefault(); }
        });
      <?php else: ?>
        const textArea = document.getElementById("reply");
        if(textArea){
          const charCounter = document.getElementById("charCount");
          textArea.addEventListener("input", () => {
            const len = textArea.value.length;
            charCounter.textContent = `${len}/1000`;
            charCounter.style.color = len >= 990 ? "red" : len >= 800 ? "orange" : "black";
          });
        }
      <?php endif; ?>
    </script>
  </body>
</html>
