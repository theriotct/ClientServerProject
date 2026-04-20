<?php
  session_start();
  include('../connection.php');
  include('../functions.php');
  
  $user_data = check_login($con);
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Awesome Marketplace</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <style>
      .post {background-color: white; margin: 5px; padding: 5px;}
      body {background-color: #fbbf77;}
    </style>
  </head>
  <body>
    <!-- Navbar -->
    <?php set_header(); ?>

    <div class="p-5 bg-warning text-white rounded text-center">
      <h1 style="color: blue;">The Awesome Marketplace</h1>
      <p style="color: blue;">Selling things that are awesome!</p>
    </div><br>
    <div class="container">
      <div>Awesome Products
        <?php
          if($user_data){
            echo '<a href="post.php" class="btn btn-sm btn-default" style="float: right;">New Thread</a>';
          }
        ?>
      </div><br>
        
      </div>
    </div>
  </body>
</html>
