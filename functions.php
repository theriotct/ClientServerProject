<?php
function check_login($con)
{
    if(isset($_SESSION['userID']))
    {
        $id = $_SESSION['userID'];
        $query = "select * from user where userID = $id limit 1";

        $result = mysqli_query($con,$query);
        if($result && mysqli_num_rows($result) > 0)
        {
            $user_data = mysqli_fetch_assoc($result);
            return $user_data;
        }
    }
    //redirect to login
    return null;
}

function log_maker($username, $query, $con)
{
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $page = $_SERVER['SCRIPT_NAME'].$_SERVER['QUERY_STRING'];
    $url = json_decode(file_get_contents('http://api.ip2location.io/?key=E89A580165383D0B1A85CCCCAF8B814C'));
    $ip = ''.$_SERVER['REMOTE_ADDR']."&format=json";
    $country = $url->country_name;
    $city = $url->city_name;
    $zip = $url->zip_code;
    $region =  $url->region_name;
    $latitude = $url->latitude;
    $longitude = $url->longitude;

    $insertQuery = "INSERT INTO `logs` (`userID`, `ip`, `page`, `country`, `region`, `city`, `zip`, `latitude`, `longitude`) VALUES ('$username', '$ip_address','$page','$country','$region','$city','$zip','$latitude','$longitude')";
    echo $insertQuery;
    $result = mysqli_query($con, $insertQuery);
    if($result){
        return;
    }else{
        echo 'error making log';
    }

}

function alert($message){
    echo "<script>alert('$message');</script>";
}

function check_login_user($con)
{

    if(isset($_SESSION['userID']))
    {
        $id = $_SESSION['userID'];
    }else{
        //redirect to login
        header("Location: login.php");
        die;
    }
}

function forbidden(){
    header("HTTP/1.1 403 Forbidden");
    include('403.html');
    die;
}
function not_found(){
    header("HTTP/1.1 404 Not Found");
    include('404.html');
    die;
}

function set_header(){
    echo '<nav class="navbar navbar-expand border-bottom" style="background-color: #e3f2fd;">
      <div class="container">
        <a class="navbar-brand fw-bold link-primary" href="/index.php">Awesome Site</a>

        <div class="navbar-nav ms-auto">';
    if(isset($_SESSION['userID'])){
        echo '<a class="nav-link" href="/index.php">Home</a>';
        if(isset($_SESSION['isAdmin']) && ($_SESSION['isAdmin'] == 1 || $_SESSION['isAdmin'] == 0)){
            echo '<a class="nav-link" href="/admin/dashboard.php">Admin Panel</a>';
        }else{
            echo '<a class="nav-link" href="/user/dashboard.php">User Dashboard</a>';
        }
        echo '<a class="nav-link" href="/profile.php">My Profile</a>';
        echo '<a class="nav-link" href="/message.php">Messages</a>';
        echo '<a class="nav-link" href="/logout.php">Logout</a>';
    }else{
        echo '<a class="nav-link" href="/index.php">Home</a>';
        echo '<a class="nav-link" href="/login.php">Login</a>';
        echo '<a class="nav-link" href="/register.php">Register</a>';
    }
    echo '</div></div></nav>';
}