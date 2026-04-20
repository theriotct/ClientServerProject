<?php 
    session_start();
    include '../connection.php';
    include '../functions.php';
    require_once 'AuthGen/PHPGangsta/GoogleAuthenticator.php';

    $user_data = check_login($con);

    if (!class_exists('PHPGangsta_GoogleAuthenticator')) {
        class PHPGangsta_GoogleAuthenticator
        {
            public function verifyCode($secret, $otp, $tolerance = 1) { return false; }
        }
    }

    $authenticator = new PHPGangsta_GoogleAuthenticator();
    $secret = $user_data['auth_key'];

    if($_SERVER['REQUEST_METHOD'] == "POST")
    {
        $otp = trim($_POST['code']);
        $tolerance = 1;
        
        $checkResult = $authenticator->verifyCode($secret, $otp, $tolerance);    
        
        if ($checkResult) 
        {
            $_SESSION['2fa_verified'] = true;
            header('Location: dashboard.php');
            exit;
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>2FA Verification</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #89afbb, #ADD8E6);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            width: 320px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }

        input[type="password"] {
            width: 90%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            text-align: center;
            letter-spacing: 4px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #2a5298;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background: #1e3c72;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>2FA Verification</h2>
        <form method="post">
            <p>Enter your 6-digit code from Google Authenticator</p>
            <?php
                if (isset($_POST['submit']) && !$checkResult) {
                    echo '<input type="password" placeholder="123456" name="code" required style="border-color: red;">';
                }else{
                    echo '<input type="password" placeholder="123456" name="code" required>';
                }
            ?>
            <input type="submit" name="submit" value="Verify">
        </form>
    </div>
</body>
</html>
