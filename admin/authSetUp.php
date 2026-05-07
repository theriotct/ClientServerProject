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
    $secret = $authenticator->createSecret();

    $website = 'http://awesomesite/';
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/' . $username . '?secret=' . $secret . '&issuer=' . $website;

    $query = "UPDATE `user` SET `auth_key` = '$secret' WHERE `username`='$username' LIMIT 1";
    mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setup 2FA</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #141e30, #243b55);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            width: 350px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }

        h2 {
            margin-bottom: 10px;
            color: #333;
        }

        p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        img {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .secret-box {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 15px;
            word-break: break-all;
            color: #333;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #243b55;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background: #141e30;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Set Up 2FA</h2>
        <p>Scan this QR code with Google Authenticator</p>

        <img src="<?php echo $qrCodeUrl; ?>" width="200">

        <p>If you can't scan, use this code:</p>
        <div class="secret-box"><?php echo $secret; ?></div>

        <form method="post">
            <input type="submit" name="back" value="Go Back To Login">
        </form>
    </div>
</body>
</html>
