<?php
  session_start();
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
                <li class="list-group-item" style="background-color: #00b7eb;"><a style="color: black;" href="../index.php" class="text-decoration-none">View Home Page</a></li>
                <li class="list-group-item" style="background-color: #00b7eb;"><a style="color: black;" href="../marketplace.php" class="text-decoration-none">Browse Marketplace</a></li>
                <li class="list-group-item" style="background-color: #00b7eb;"><a style="color: black;" href="../profile.php" class="text-decoration-none">View Profile</a></li>
                <li class="list-group-item" style="background-color: #00b7eb;"><a style="color: black;" href="../message.php" class="text-decoration-none">Messages</a></li>
            </ul>

            <br>
            <a class="btn btn-primary" href="/logout.php">Logout</a>
        </div>

    </div>
</body>
