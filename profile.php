<?php
  include "connection.php";
  include "functions.php";

  $user_data = check_login($con);
  if (!$user_data) {
    not_found();
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>My Profile</title>
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
        <div class="" style="background-color: rgb(234, 234, 234); padding: 10px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
            <h4>The Power of Awesome Ideas</h4>
            <p>Today I explored how small creative ideas can turn into truly awesome projects with just a bit of persistence.</p>
            <p>March 1, 2026</p>
        </div>
        <div class="" style="background-color: rgb(234, 234, 234); padding: 10px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
            <h4>The Power of Awesome Ideas</h4>
            <p>Today I explored how small creative ideas can turn into truly awesome projects with just a bit of persistence.</p>
            <p>March 1, 2026</p>
        </div>
        <div class="" style="background-color: rgb(234, 234, 234); padding: 10px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
            <h4>The Power of Awesome Ideas</h4>
            <p>Today I explored how small creative ideas can turn into truly awesome projects with just a bit of persistence.</p>
            <p>March 1, 2026</p>
        </div>
        <div class="" style="background-color: rgb(234, 234, 234); padding: 10px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
            <h4>The Power of Awesome Ideas</h4>
            <p>Today I explored how small creative ideas can turn into truly awesome projects with just a bit of persistence.</p>
            <p>March 1, 2026</p>
        </div>
    </div>
  </body>
</html>
