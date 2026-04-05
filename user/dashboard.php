<!DOCTYPE html>
<html>
<head>
    <title>The Awesome Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand bg-white border-bottom">
      <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Awesome Site</a>

        <div class="navbar-nav ms-auto">
          <a class="nav-link" href="index.php">Home</a>
          <a class="nav-link" href="profile.php">My Profile</a>
          <a class="nav-link" href="login.php">Login</a>
          <a class="nav-link active" aria-current="page" href="register.php"
            >Register</a
          >
        </div>
      </div>
    </nav>

    <div class="container mt-5">
        
        <!-- USER SECTION (Hidden Initially) -->
        <div id="userSection" >
            <h2>User Dashboard</h2>

            <ul class="list-group mt-3">
                <li class="list-group-item">View Home Page</li>
                <li class="list-group-item">Browse Marketplace</li>
                <li class="list-group-item">View Profile</li>
                <li class="list-group-item">Messages</li>
            </ul>

            <br>
            <a class="btn btn-secondary" href="/logout.php">Logout</a>
        </div>

    </div>
</body>