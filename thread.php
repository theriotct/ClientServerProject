<?php 
    session_start();
    include 'connection.php';
    include 'functions.php';

    if($_GET['postID']){
        $postID = (int)$_GET['postID'];
        $query = "WITH RECURSIVE thread AS ( SELECT * FROM posts WHERE postID = $postID UNION ALL SELECT p.* FROM posts p INNER JOIN thread t ON p.parentID = t.postID ) SELECT t.*, u.username FROM thread t JOIN `user` u ON u.userID = t.authorID ORDER BY date ASC;";
        $result = mysqli_query($con, $query);
        $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
        if(count($posts) == 0 && $posts[0]['title'] === NULL){
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

    if($_SERVER['REQUEST_METHOD'] == "POST")
    {
        if(isset($_POST['reply'])){
            $reply = $_POST['reply'];
            $userID = $user_data['userID'];
            $i = count($posts)-1;
            $parentID = $posts[$i]['postID'];
            $query = "INSERT INTO `posts` (`parentID`, `authorID`, `title`, `body`, `date`) VALUES ('$parentID ', '$userID', NULL, '$reply', CURRENT_TIMESTAMP);";
            $result = mysqli_query($con, $query);
            if($result){
                header('Location: thread.php?postID='.$postID);
                exit;
            }else{
                alert('Error posting reply');
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
        body {background-color: #fed8b1;}

        .right {
            float: right;
            margin-left: 10px;
        }


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

  <!-- Thread Content -->
  <div class="container" style="margin-top:20px;">

    <!-- Original Post -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3><?php echo $posts[0]['title']; ?></h3>
        <small>Posted by <?php echo $posts[0]['username']; ?> • <?php echo $posts[0]['date']; ?></small>
      </div>
      <div class="panel-body">
        <p>
          <?php echo $posts[0]['body']; ?>
        </p>
      </div>
      <div class="panel-footer">
        <a href="#" class="btn btn-sm btn-default">Like: ###</a>
        <a href="#" class="btn btn-sm btn-default">Dislike: ###</a>
        <a href="#" class="btn btn-sm btn-default">Report</a>
        <a href="#" class="btn btn-sm btn-default btn-danger right">Delete</a>
        <a href="#" class="btn btn-sm btn-default right">Edit</a>
      </div>
    </div>

    <!-- Replies Section -->
    <h4>Replies</h4>

    <?php
      for($i = 1; $i < count($posts); $i++){
        echo '<div class="panel panel-info">
                <div class="panel-heading">
                  <strong>'.$posts[$i]['username'].'</strong> • '.$posts[$i]['date'].'
                </div>
                <div class="panel-body">
                  '.$posts[$i]['body'].'
                </div>
                <div class="panel-footer">
                  <a href="#" class="btn btn-sm btn-default">Like: ###</a>
                  <a href="#" class="btn btn-sm btn-default">Dislike: ###</a>
                  <a href="#" class="btn btn-sm btn-default">Report</a>
                  <a href="#" class="btn btn-sm btn-default btn-danger right" >Delete</a>
                  <a href="#" class="btn btn-sm btn-default right">Edit</a>
                </div>
              </div>';
      }
    ?>

    <!-- Reply Form -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4>Add a Reply</h4>
      </div>
      <div class="panel-body">
        <?php if($user_data == null){ ?>
          <form action="login.php" method="get">
        <?php } else { ?>
          <form method="post">
        <?php } ?>
          <div class="form-group">
            <?php if($user_data == null){ ?>
              <textarea class="form-control" id="reply" name="reply" rows="5" placeholder="Please log in to reply" disabled></textarea>
            <?php } else { ?>
              <textarea class="form-control" id="reply" name="reply" rows="5" maxlength="1000" placeholder="Write your reply..."></textarea>
            <?php } ?>
          </div>

          <span id="charCount">0/1000</span><br><br>

          <?php if($user_data == null){ ?>
            <input type="submit" class="btn btn-primary" value="Click Here To Login" >
          <?php } else { ?>
            <input type="submit" class="btn btn-primary" value="Post Reply">
          <?php } ?>
        </form>
      </div>
    </div>

  </div>


  <script>
    const textArea = document.getElementById("reply");
    const charCounter = document.getElementById("charCount");
    const maxChars = 1000;
    textArea.addEventListener("input", () => {
      const enteredChars = textArea.value.length;
      const remainingChars = maxChars - enteredChars;
      charCounter.textContent = `${enteredChars}/${maxChars}`;
      // Change color if limit is exceeded
      if (remainingChars < 10) {
          charCounter.style.color = "red";
      } else if (remainingChars <= 200) {
          charCounter.style.color = "orange";
      } else {
          charCounter.style.color = "black";
      }
    });
</script>
</body>
