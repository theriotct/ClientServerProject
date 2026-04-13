<html>
    <head>
      <title>Create an awesome post</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
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
      
      
      
      
  <div class="container">
	  <div class="row">
	    
	    <div class="col-md-8 col-md-offset-2">
	        
    		<h1>Create post</h1>
    		
    		<form action="" method="POST">
    		    
    		    <div class="form-group">
    		        <label for="title">Title <span class="require">*</span></label>
    		        <input type="text" class="form-control" name="title" />
    		    </div>
    		    
    		    <div class="form-group">
    		        <label for="description">Description</label>
    		        <textarea rows="5" class="form-control" name="description" ></textarea>
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
