<?php 
session_start();

include("connection.php");
include("functions.php");

$user_data = check_login($con);

if($user_data){
    header("Location: index.php");
    die;
}

if($_SERVER['REQUEST_METHOD'] == "POST")
{
    //Something was posted
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];

    $pepper = getenv('PASSWORD_PEPPER');

    if(!empty($user_name) && !empty($password) && !is_numeric($user_name))
    {
        //read from database
        $query = "select * from user where username = '$user_name' limit 1";

        $result = mysqli_query($con, $query);
        if($result){
            if($result && mysqli_num_rows($result) > 0)
            {
                $user_data = mysqli_fetch_assoc($result);

                if(password_verify($password . $pepper, $user_data['password'])){
                    $_SESSION['userID'] = $user_data['userID'];
                    $_SESSION['username'] = $user_name;
                    $_SESSION['isAdmin'] = $user_data['isAdmin'];
                    $query = "UPDATE `user` SET `lastLogin` = CURRENT_TIMESTAMP WHERE `userID` = ".$user_data['userID'];
                    mysqli_query($con, $query);

                    if($user_data['isAdmin'] == 1){
                        header("Location: admin/dashboard.php");
                        exit;
                    }
                    header("Location: user/dashboard.php");
                    exit;
                }
            }
        }else{
            $query = "select * from login where email = '$user_name' limit 1";
            $result = mysqli_query($con, $query);
            if($result && mysqli_num_rows($result) > 0)
            {
                $user_data = mysqli_fetch_assoc($result);
                if(password_verify($password . $pepper, $user_data['password'])){
                    $_SESSION['userID'] = $user_data['userID'];
                    $_SESSION['username'] = $user_name;
                    $_SESSION['isAdmin'] = $user_data['isAdmin'];
                    $query = "UPDATE `user` SET `lastLogin` = CURRENT_TIMESTAMP WHERE `userID` = ".$user_data['userID'];
                    mysqli_query($con, $query);

                    if($user_data['isAdmin'] == 1){
                        header("Location: admin/dashboard.php");
                        exit;
                    }
                    header("Location: user/dashboard.php");
                    exit;
                }
            }
            alert("Wrong Username Or Password!");
        }
    }else{
        alert("Wrong Username Or Password!");
    }
}
?>


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
        <a class="navbar-brand fw-bold" href="/index.php">Awesome Site</a>

        <div class="navbar-nav ms-auto">
          <a class="nav-link" href="/index.php">Home</a>
          <a class="nav-link" href="/profile.php">My Profile</a>
          <a class="nav-link" href="/login.php">Login</a>
          <a class="nav-link" aria-current="page" href="/register.php">Register</a>
        </div>
      </div>
    </nav>
<div class="container mt-5">
    <!-- LOGIN SECTION -->
    <div id="loginSection">
        <h2>The Awesome Site</h2>
        <p>Please log in</p>

        <form method="post">
            <div class="mb-3">
                <label>Username / Email</label>
                <input type="text" class="form-control" name="user_name">
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" class="form-control" name="password">
            </div>

            <input type="submit" class="btn btn-primary mb-3" value="Log In">
        </form>
        <p>
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>

</div>

</body>
</html>