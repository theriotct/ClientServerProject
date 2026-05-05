<?php
include('connection.php');

$id = (int)($_GET['id'] ?? 0);

$query = "SELECT ImageData FROM marketplace_items WHERE itemID = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    header("Content-Type: image/*"); // you can improve this later
    echo $row['ImageData'];
}
?>