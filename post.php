<?php
  session_start();
  include("connection.php");
  include("functions.php");

  $user_data = check_login($con);
  $GETpost_data = null;
  if(!$user_data){
	header("Location: login.php");
	die;
  }
  if($_SERVER['REQUEST_METHOD'] == "GET"){
	if(isset($_GET['postID'])){
	  $postID = (int)$_GET['postID'];
	  $query = "SELECT * FROM posts WHERE postID = '$postID' LIMIT 1";
	  $result = mysqli_query($con, $query);
	  if($result && mysqli_num_rows($result) > 0){
		$GETpost_data = mysqli_fetch_assoc($result);
	  
		if(is_null($user_data['isAdmin'])){
			if(($user_data["userID"] != $GETpost_data['authorID'])){
			forbidden();
			}
		}
	  }
	}
  }


  if($_SERVER['REQUEST_METHOD'] == "POST"){
	$title = trim($_POST['title'] ?? '');
	$body  = trim($_POST['description'] ?? '');	

	if(!isset($_POST['postID'])){
		if(!empty($title)){
		$user_id = $user_data['userID'];
		$query = "INSERT INTO posts (authorID, title, body) VALUES ('$user_id', '$title', '$body')";
		mysqli_query($con, $query);
		header("Location: index.php");
		die;
		}else{
		echo "Please enter a title";
		}
	}else{
		if(!empty($title)){
			$postID = (int)$_POST['postID'];

			$query = "UPDATE posts SET title = '$title', body = '$body' WHERE postID = $postID";
			
			mysqli_query($con, $query);
			header("Location: index.php");
			die;
		}else{
			$postID = (int)$_POST['postID'];
			$query = "UPDATE posts SET body = '$body' WHERE postID = '$postID'";
			mysqli_query($con, $query);
			header("Location: index.php");
			die;
		}
	}
}

?>

<html>
    <head>
      <title>Create an awesome post</title>
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
      	<?php set_header(); ?>
      
		<div class="container">
			<div class="row">
				
				<div class="col-md-8 col-md-offset-2">
					
					<h1>Create post</h1>
					
					<form action="" method="POST">
						<?php if(isset($GETpost_data['postID'])): ?>
							<input type="hidden" name="postID" value="<?php echo $GETpost_data['postID']; ?>">
						<?php endif; ?>
						
						<div class="form-group">
							<label for="title">Title <span class="require">*</span></label>
							<input type="text" class="form-control" name="title"
								value="<?php echo isset($GETpost_data['title']) || $GETpost_data == null  ? htmlspecialchars($GETpost_data['title']).'"' : '" disabled'; ?>>
						</div>
						
						<div class="form-group">
							<label for="description">Description</label>
							<textarea rows="5" class="form-control" name="description"><?php 
								if(isset($GETpost_data['body'])){
									echo $GETpost_data['body'];
								}
								?></textarea>
						</div>
						
						<div class="form-group">
							<p><span class="require">*</span> - required fields</p>
						</div>
						
						<div class="form-group">
							<button type="submit" class="btn btn-primary">
								Create
							</button>
							<button class="btn btn-default">
								Cancel
							</button>
						</div>
						
					</form>
				</div>
				
			</div>
		</div>
	</body>
</html>
