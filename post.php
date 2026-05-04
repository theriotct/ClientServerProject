<?php
  session_start();
  include("connection.php");
  include("functions.php");

  $user_data = check_login($con);

	$editingPost = false;
	$editPostID = 0;
	$editTitle = "";
	$editBody = "";

	if(isset($_GET['editPostID'])){
		$editPostID = (int)$_GET['editPostID'];

		$query = "SELECT * FROM posts WHERE postID = '$editPostID' LIMIT 1";
		$result = mysqli_query($con, $query);

		if($result && mysqli_num_rows($result) > 0){
			$post_data = mysqli_fetch_assoc($result);

			if($post_data['authorID'] == $user_data['userID']){
				$editingPost = true;
				$editTitle = $post_data['title'];
				$editBody = $post_data['body'];
			}else{
				echo "You are not allowed to edit this post.";
				die;
			}
		}else{
			echo "Post not found.";
			die;
		}
	}
  
  if($_SERVER['REQUEST_METHOD'] == "POST"){
	$title = $_POST['title'];
	$body = $_POST['description'];

	if(!empty($title)){
		$user_id = $user_data['userID'];

		if(isset($_POST['editPostID']) && !empty($_POST['editPostID'])){
			$editPostID = (int)$_POST['editPostID'];

			$query = "UPDATE posts 
					  SET title = '$title', body = '$body' 
					  WHERE postID = '$editPostID' 
					  AND authorID = '$user_id'";

			mysqli_query($con, $query);

			header("Location: thread.php?postID=".$editPostID);
			die;
		}else{
			$query = "INSERT INTO posts (authorID, title, body) VALUES ('$user_id', '$title', '$body')";
			mysqli_query($con, $query);

			header("Location: index.php");
			die;
		}
	}else{
		echo "Please enter a title";
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
					
					<h1><?php echo $editingPost ? "Edit post" : "Create post"; ?></h1>
					
					<form action="" method="POST">
						<?php if($editingPost): ?>
							<input type="hidden" name="editPostID" value="<?php echo $editPostID; ?>">
						<?php endif; ?>
						
						<div class="form-group">
							<label for="title">Title <span class="require">*</span></label>
							<input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($editTitle);?>"
						</div>
						
						<div class="form-group">
							<label for="description">Description</label>
							<textarea rows="5" class="form-control" name="description"><?php echo htmlspecialchars($editBody); ?></textarea>
						</div>
						
						<div class="form-group">
							<p><span class="require">*</span> - required fields</p>
						</div>
						
						<div class="form-group">
							<button type="submit" class="btn btn-primary">
								<?php echo $editingPost ? "Save Changes" : "Create"; ?>
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
