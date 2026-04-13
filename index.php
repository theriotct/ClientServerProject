<?php
  session_start();
  include('connection.php');
  include('functions.php');

  $user_data = check_login($con);

  function get_posts($con){
    $query = "WITH RECURSIVE thread AS( SELECT postID, parentID, date, postID AS rootID FROM posts WHERE parentID IS NULL UNION ALL SELECT p.postID, p.parentID, p.date, t.rootID FROM posts p JOIN thread t ON p.parentID = t.postID), latest AS ( SELECT rootID, MAX(date) AS last_activity FROM thread GROUP BY rootID ) SELECT p.postID, p.title, p.date, l.last_activity FROM posts p JOIN latest l ON p.postID = l.rootID ORDER BY l.last_activity DESC LIMIT 10;";
    $result = mysqli_query($con, $query);

    if($result){
        if($result && mysqli_num_rows($result) > 0)
        {
            foreach($result as $post){
                if($post){
                  echo '<div class="row post"><div class="col-sm-6"><a href="thread.php?postID='.$post['postID'].'">'.$post['title'].'</a></div><div class="col-sm-3">Date Created: '.$post['date'].'</div><div class="col-sm-3">Recent Activity: '.$post['last_activity'].'</div></div>';
                }
            }
        }
    }else{
        echo 'Error fetching posts';
    }
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Awesome Site</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <style>
      .post {background-color: white; margin: 5px; padding: 5px;}
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


    <div class="p-5 bg-primary text-white rounded text-center">
      <h1 style="color: orange;">The Awesome Site</h1>
      <p style="color: orange;">Discussions of things that are awesome!</p>
    </div>
    <div class="container">
      <div>Awesome Topics
        <?php
          if(!is_null($user_data['userID'])){
            echo '<a href="post.php" class="btn btn-sm btn-default" style="float: right;">New Thread</a>';
          }
        ?>
      </div>
        <?php get_posts($con); ?>
      </div>
    </div>
  </body>
</html>
