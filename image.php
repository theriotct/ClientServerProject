<?php
include('connection.php');

$userID = isset($_GET['userID']) ? (int)$_GET['userID'] : null;
$itemID = isset($_GET['id']) ? (int)$_GET['id'] : null;

$image = null;

if ($userID !== null && $userID > 0) {

    $query = "SELECT profilePicture FROM user WHERE userID = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userID);

} elseif ($itemID !== null && $itemID > 0) {

    $query = "SELECT imageData FROM marketplace_items WHERE itemID = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'i', $itemID);

} else {
    http_response_code(400);
    exit("Invalid request");
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {

    // pick correct column dynamically
    $image = $row['profilePicture'] ?? $row['imageData'] ?? null;

    if ($image) {

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $image);
        finfo_close($finfo);

        header("Content-Type: " . $mime);
        header("Content-Length: " . strlen($image));

        echo $image;
        exit;
    }
}

http_response_code(404);
echo "Image not found";
?>