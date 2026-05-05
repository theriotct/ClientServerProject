<?php
include('connection.php');

$id = (int)($_GET['id'] ?? 0);

$query = "SELECT imageData FROM marketplace_items WHERE itemID = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {

    $image = $row['imageData'];

    if ($image !== null) {

        // Try to detect image type (basic but useful)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $image);
        finfo_close($finfo);

        header("Content-Type: " . $mime);
        header("Content-Length: " . strlen($image));

        echo $image;
        exit;
    }
}

// fallback image (optional)
http_response_code(404);
echo "Image not found";
?>