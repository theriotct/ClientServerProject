<?php
  session_start();
  include "connection.php";
  include "functions.php";

  $user_data = check_login($con);
  if (!$user_data) {
    not_found();
  }
  function get_posts($con, $userID){
    try {
        $query = "WITH RECURSIVE thread AS ( SELECT p.postID AS replyID, p.parentID, p.title, p.body, p.authorID, p.date, p.postID AS originalReplyID FROM posts p WHERE p.authorID = $userID UNION ALL SELECT parent.postID, parent.parentID, parent.title, parent.body, parent.authorID, parent.date, t.originalReplyID FROM posts parent JOIN thread t ON t.parentID = parent.postID ) SELECT root.postID AS parentID, root.title AS parentTitle, root.authorID AS parentAuthorID, root.date AS parentDate, reply.postID AS replyID, reply.parentID AS replyParentID, reply.authorID AS replyAuthorID, reply.body AS replyBody, reply.title AS replyTitle, reply.date AS replyDate FROM thread t JOIN posts root ON root.postID = t.postID AND root.parentID IS NULL JOIN posts reply ON reply.postID = t.originalReplyID;";
        $result = mysqli_query($con, $query);
    } catch (Exception $e) {
        echo 'Error executing query: ',  $e->getMessage(), "\n";
        return;
    }

    if($result){
        if($result && mysqli_num_rows($result) > 0)
        {
            foreach($result as $post){
                if($post){
                  echo '<div class="" style="background-color: rgb(234, 234, 234); padding: 10px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;"><h4><a href="thread.php?postID='.$post['parentID'].'">'.$post['parentTitle'].'</a></h4><p>'.$post['replyBody'].'</p><p>'.$post['replyDate'].'</p></div>';
                }
            }
        }
    }else{
        echo 'Error fetching posts';
    }  
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
      body {background-color: lightblue;}
    </style>
  </head>
  <body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand border-bottom" style="background-color: #e3f2fd;">
      <div class="container">
        <a class="navbar-brand fw-bold link-primary" href="/index.php">Awesome Site</a>

        <div class="navbar-nav ms-auto">
          <a class="nav-link" href="/index.php">Home</a>
          <a class="nav-link" href="/profile.php">My Profile</a>
          <a class="nav-link" href="/login.php">Login</a>
          <a class="nav-link active" aria-current="page" href="/register.php">Register</a>
        </div>
      </div>
    </nav>

    <div class="col-sm-3" style=" height: 200px;">
        <div class="col-xs-4 col-sm-12" style="height: 200px; text-align: center; align-content: center;">
            <img src="images/download.jpg" style="width: 150px; padding-top: 10px;" class="img-circle">
        </div>
        <div class="col-xs-8 col-sm-12" style=" height: 200px; align-content: center;">
            <h4 class="text-center" style="margin-bottom: 0px;"><?php echo $user_data['fname'] . " " . $user_data['lname']; ?></h4>
            <h6 class="text-center" style="margin-top: 0px;"><?php echo $user_data['username']; ?></h6>
            <div style=" vertical-align: middle; text-align: center;">
                <button> Follow</button>
                <button> Message </button>
                <button> Report </button>
            </div>
            
        </div>
    </div>
    <div class="col-sm-9">
        <?php get_posts($con, $user_data['userID']); ?>
    </div>
  </body>
</html>
