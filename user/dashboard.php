<!DOCTYPE html>
<html>
<head>
    <title>The Awesome Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
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
            <form action="logout.php" method="post">
                <input class="btn btn-secondary" type="submit" value="Logout">
            </form> 
            <a href="/logout.php">Logout</a>
        </div>

    </div>
</body>