<?php
include('connection.php');

$userID = isset($_GET['userID']) ? (int)$_GET['userID'] : 0;

if(isset($_GET['id'])) {
    $userID = (int)$_GET['id'];
} 

if ($userID <= 0) {
    http_response_code(400);
    exit("Invalid request");
}

if(isset($_GET['id'])) {
    $query = "SELECT ImageData FROM marketplace_items WHERE itemID = ?";
} else {
    $query = "SELECT profilePicture FROM user WHERE userID = ?";
}
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {

    if(isset($_GET['id'])) {
        $image = $row['ImageData'];
    } else {
        $image = $row['profilePicture'];
    }

    if ($image !== null) {

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $image);

        header("Content-Type: " . $mime);
        echo $image;
        exit;
    }
}

// fallback (VERY important so broken images don’t happen)
header("Content-Type: image/png");
readfile("images/avatar1.png");
exit;