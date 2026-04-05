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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  </head>
  <body>
    <div style="background-color: white; height: 20px; position: fixed; top: 0; width: 100%; z-index: 1000;">
      <a href="index.php" style="color: black; text-decoration: none; padding: 10px 10px 10px 10px;">Home</a>
      <a href="login.php" style="color: black; text-decoration: none; padding: 10px 10px 10px 10px;">Login</a>
      <a href="register.php" style="color: black; text-decoration: none; padding: 10px 10px 10px 10px;">Register</a>
      <a href="profile.php" style="color: black; text-decoration: none; padding: 10px 10px 10px 10px;">My Profile</a>
    </div>
    <body style="padding-top:40px; background-color:#f5f5f5;">

  <!-- Top Navigation -->
  <div style="background-color: white; height: 40px; position: fixed; top: 0; width: 100%; z-index: 1000; border-bottom:1px solid #ddd;">
    <a href="index.php" style="color: black; text-decoration: none; padding: 10px;">Home</a>
    <a href="login.php" style="color: black; text-decoration: none; padding: 10px;">Login</a>
    <a href="register.php" style="color: black; text-decoration: none; padding: 10px;">Register</a>
    <a href="profile.php" style="color: black; text-decoration: none; padding: 10px;">My Profile</a>
  </div>

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
