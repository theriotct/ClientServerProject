<?php
    session_start();
    include 'connection.php';
    include 'functions.php';

    if($_GET['postID']){
        $postID = (int)$_GET['postID'];
        $query = "WITH RECURSIVE thread AS (
                      SELECT * FROM posts WHERE postID = $postID
                      UNION ALL
                      SELECT p.* 
                      FROM posts p
                      INNER JOIN thread t ON p.parentID = t.postID
                  )
                  SELECT 
                      t.*, 
                      u.username,
                      COALESCE(r.likes, 0) AS totalLikes,
                      COALESCE(r.dislikes, 0) AS totalDislikes
                  FROM thread t
                  JOIN `user` u ON u.userID = t.authorID
                  LEFT JOIN (
                      SELECT 
                          refPostID,
                          SUM(CASE WHEN `like/dislike` = 1 THEN 1 ELSE 0 END) AS likes,
                          SUM(CASE WHEN `like/dislike` = 0 THEN 1 ELSE 0 END) AS dislikes
                      FROM `like`
                      GROUP BY refPostID
                  ) r ON r.refPostID = t.postID
                  ORDER BY t.date ASC;
                  ";
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
        if(isset($_POST['like'])){

        }
        if(isset($_POST['dislike'])){

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

        /* Validation error styles */
        .error-input {
          border: 2px solid #f44336 !important;
          background-color: #ffe6e6 !important;
          box-shadow: 0 0 5px rgba(244, 67, 54, 0.3);
        }

        .error-message {
          color: #f44336;
          font-size: 13px;
          margin-top: 5px;
          display: block;
          font-weight: bold;
        }

        .success-input {
          border: 2px solid #4CAF50 !important;
          background-color: #e6ffe6 !important;
        }

        .global-error {
          background-color: #f44336;
          color: white;
          padding: 10px 15px;
          border-radius: 4px;
          margin-bottom: 15px;
          display: none;
          font-weight: bold;
        }

        .btn-primary:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
    </style>
  </head>
  <body>
    <!-- Navbar -->
    <?php set_header(); ?>

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
        <form action="" method="POST">
          <input type="text" value="<?php echo $posts[0]['postID']?>" name="postID" hidden>
          <input class="btn btn-sm btn-default" type="submit" name="like" value="Like: '.$posts[$i]['totalLikes'].'">
          <input class="btn btn-sm btn-default" type="submit" name="dislike" value="Dislike: '.$posts[$i]['totalDislikes'].'">
          <input class="btn btn-sm btn-default" type="submit" name="dislike" value="Report">
          <input class="btn btn-sm btn-default btn-danger right" type="submit" name="dislike" value="Delete">
          <input class="btn btn-sm btn-default right" type="submit" name="dislike" value="Edit">
        </form>
      </div>
    </div>

    <!-- Replies Section -->
    <h4>Replies</h4>

    <?php
      for($i = 1; $i < count($posts); $i++){
        echo '<div class="panel panel-info">
                <div class="panel-heading">
                  <strong><a href="profile.php?userID='.$posts[$i]['authorID'].'">'.$posts[$i]['username'].'</a></strong> • '.$posts[$i]['date'].'
                </div>
                <div class="panel-body">
                  '.$posts[$i]['body'].'
                </div>
                <div class="panel-footer">
                  <form action="" method="POST">
                    <input class="btn btn-sm btn-default" type="submit" name="like" value="Like: '.$posts[$i]['totalLikes'].'">
                    <input class="btn btn-sm btn-default" type="submit" name="dislike" value="Dislike: '.$posts[$i]['totalDislikes'].'">
                    <input class="btn btn-sm btn-default" type="submit" name="dislike" value="Report">
                    <input class="btn btn-sm btn-default btn-danger right" type="submit" name="dislike" value="Delete">
                    <input class="btn btn-sm btn-default right" type="submit" name="dislike" value="Edit">
                  </form>
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
        <div class="global-error" id="globalError"></div>

        <?php if($user_data == null){ ?>
          <form action="login.php" method="get">
        <?php } else { ?>
          <form method="post" id="replyForm">
        <?php } ?>
          <div class="form-group">
            <?php if($user_data == null){ ?>
              <textarea class="form-control" id="reply" name="reply" rows="5" placeholder="Please log in to reply" disabled></textarea>
            <?php } else { ?>
              <textarea class="form-control" id="reply" name="reply" rows="5" maxlength="1000" placeholder="Write your reply..."></textarea>
            <?php } ?>
          </div>

          <span class="error-message" id="replyError"></span>

          <span id="charCount">0/1000</span><br><br>

          <?php if($user_data == null){ ?>
            <input type="submit" class="btn btn-primary" value="Click Here To Login" >
          <?php } else { ?>
            <input type="submit" class="btn btn-primary" id="submitBtn" value="Post Reply">
          <?php } ?>
        </form>
      </div>
    </div>

  </div>


  <script>
  <?php if($user_data != null){ ?>
    // Get form elements
    const textArea = document.getElementById("reply");
    const charCounter = document.getElementById("charCount");
    const submitBtn = document.getElementById("submitBtn");
    const replyForm = document.getElementById("replyForm");
    const replyError = document.getElementById("replyError");
    const globalError = document.getElementById("globalError");
    const maxChars = 1000;

    // Function to remove error styling
    function removeError() {
      textArea.classList.remove('error-input');
      textArea.classList.remove('success-input');
      replyError.textContent = '';
      globalError.style.display = 'none';
    }

    // Function to add error styling
    function addError(message) {
      textArea.classList.add('error-input');
      textArea.classList.remove('success-input');
      replyError.textContent = message;
    }

    // Function to add success styling
    function addSuccess() {
      textArea.classList.remove('error-input');
      textArea.classList.add('success-input');
      replyError.textContent = '';
    }

    // Validate the reply
    function validateReply() {
      const replyText = textArea.value.trim();

      // Check if reply is empty or only whitespace
      if (replyText === '') {
        addError('Reply cannot be empty. Please enter a message.');
        return false;
      }

      // Check minimum length (optional - adjust as needed)
      if (replyText.length < 3) {
        addError('Reply is too short. Please enter at least 3 characters.');
        return false;
      }

      // Check if only whitespace characters (redundant but thorough)
      if (replyText.length > 0 && replyText.replace(/\s/g, '').length === 0) {
        addError('Reply cannot contain only spaces. Please enter a meaningful message.');
        return false;
      }

      // All validation passed
      addSuccess();
      globalError.style.display = 'none';
      return true;
    }

    // Real-time validation as user types
    textArea.addEventListener("input", () => {
      const enteredChars = textArea.value.length;
      const remainingChars = maxChars - enteredChars;
      charCounter.textContent = `${enteredChars}/${maxChars}`;

      // Change color if limit is approached
      if (remainingChars < 10) {
        charCounter.style.color = "red";
      } else if (remainingChars <= 200) {
        charCounter.style.color = "orange";
      } else {
        charCounter.style.color = "black";
      }

      // Real-time validation
      const replyText = textArea.value;

      if (replyText.trim() === '') {
        removeError();
      } else if (replyText.trim().length >= 3 && replyText.trim().replace(/\s/g, '').length > 0) {
        addSuccess();
      } else if (replyText.trim().length < 3 && replyText.trim().length > 0) {
        addError('Reply is too short. Please enter at least 3 characters.');
      }

      // Clear global error when user starts typing
      if (globalError.style.display === 'block') {
        globalError.style.display = 'none';
      }
    });

    // Handle form submission
    if (replyForm) {
      replyForm.addEventListener('submit', function(event) {
        // Validate before submitting
        if (!validateReply()) {
          event.preventDefault(); // Stop form submission

          // Scroll to the error message
          document.querySelector('.panel-default').scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });

          // Optional: Show a global error as well
          globalError.textContent = 'Please fix the errors above before submitting.';
      globalError.style.display = 'block';

      // Disable submit button briefly to prevent multiple clicks
      submitBtn.disabled = true;
      setTimeout(() => {
        submitBtn.disabled = false;
      }, 2000);
        }
      });
    }

    // Add character limit warning before submit
    function checkCharacterLimit() {
      const replyText = textArea.value;
      if (replyText.length > maxChars) {
        addError(`Reply exceeds ${maxChars} characters. Please shorten your message.`);
        return false;
      }
      return true;
    }

    // Prevent form submission if character limit is exceeded
    if (replyForm) {
      replyForm.addEventListener('submit', function(event) {
        if (!checkCharacterLimit()) {
          event.preventDefault();
        }
      });
    }

    // Uncomment to enable auto-resizing
    // textArea.addEventListener('input', autoResize);

    <?php } else { ?>
      // For logged-out users, just maintain the character counter
      const textArea = document.getElementById("reply");
      if(textArea) {
        const charCounter = document.getElementById("charCount");
        const maxChars = 1000;
        textArea.addEventListener("input", () => {
          const enteredChars = textArea.value.length;
          const remainingChars = maxChars - enteredChars;
          charCounter.textContent = `${enteredChars}/${maxChars}`;
          if (remainingChars < 10) {
            charCounter.style.color = "red";
          } else if (remainingChars <= 200) {
            charCounter.style.color = "orange";
          } else {
            charCounter.style.color = "black";
          }
        });
      }
      <?php } ?>
</script>
</body>
