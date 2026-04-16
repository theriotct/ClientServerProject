<?php
  include('../connection.php');
  include('../functions.php');
  
  $user_data = check_login($con);
?>
<!DOCTYPE html>
<html>
<head>
    <title>The Awesome Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <Style>
      body {background-color: #ffe1ca;}
    </Style>
</head>

<body>
    <!-- Navbar -->
    <?php set_header(); ?>

    <div class="container mt-5">
        
        <!-- USER SECTION (Hidden Initially) -->
        <div id="userSection" >
            <h2>User Dashboard</h2>

            <ul class="list-group mt-3">
                <li class="list-group-item" style="background-color: #00b7eb;">View Home Page</li>
                <li class="list-group-item" style="background-color: #00b7eb;">Browse Marketplace</li>
                <li class="list-group-item" style="background-color: #00b7eb;">View Profile</li>
                <li class="list-group-item" style="background-color: #00b7eb;">Messages</li>
            </ul>

            <br>
            <a class="btn btn-primary" href="/logout.php">Logout</a>
        </div>

    </div>
</body>
