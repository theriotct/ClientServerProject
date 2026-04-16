<?php
    session_start();
    include '../functions.php';
    include '../connection.php';
    require_once 'AuthGen/PHPGangsta/GoogleAuthenticator.php';

    if(isset($_POST['back'])) {
        header("Location: /logout.php");
        exit;
    }
    
    $user_data = check_login($con);
    $username = $user_data['username'];
    
    $authenticator = new PHPGangsta_GoogleAuthenticator();
    $secret = $authenticator->createSecret(); //This is used to generate QR code
    $website = 'http://awesomesite/'; //Your Website
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/' . $username . '?secret=' . $secret . '&issuer=' . $website;
    $query = "UPDATE `user` SET `auth_key` = '$secret' WHERE `username`='$username' LIMIT 1";
        $result = mysqli_query($con, $query);

    alert($secret);
    
    echo '<p>Scan this QR Code With Google Authenicator</p>
    <img style="width: 200px;" src="'.$qrCodeUrl.'"><br><br>
    <form method="post">
        <input type="submit" name="back" value="Go Back To Login">
    </form>';
?>