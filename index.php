<?php
  include('connection.php');
  include('functions.php');

  function get_posts($con){
    $query = "select * from posts where parentID is null order by date desc limit 10";
    $result = mysqli_query($con, $query);

    if($result){
        if($result && mysqli_num_rows($result) > 0)
        {
            foreach($result as $post){
                if($post){
                  echo '<div class="row post"><div class="col-sm-8"><a href="thread.html">'.$post['title'].'</a></div><div class="col-sm-2">'.$post['date'].'</div><div class="col-sm-2">Date: '.$post['date'].'</div></div>';
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
      .post {background-color: lightgray; margin: 5px; padding: 5px;}
    </style>
  </head>
  <body>
    <ul class="nav">
      <li class="nav-item">
        <a class="nav-link" href="login.html">Login</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="register.html">Register</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="profile.html">My Profile</a>
      </li>
    </ul>
    <div class="p-5 bg-primary text-white rounded text-center">
      <h1>The Awesome Site</h1>
      <p>Discussions of things that are awesome!</p> 
    </div>
    <div class="container">
      <div>Awesome Topics</div>
        <?php get_posts($con); ?>
      </div>
    </div>
  </body>
</html>
