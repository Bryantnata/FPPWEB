<?php
include "connect_db.php";

header('Content-Type: application/json');

// Query untuk mengambil daftar admin
$query = "SELECT id_user, nama FROM user WHERE role = 'admin' ORDER BY nama";
$result = mysqli_query($link, $query);

if (!$result) {
    echo json_encode(['error' => 'Query error: ' . mysqli_error($link)]);
    exit();
}

$adminList = [];
while ($row = mysqli_fetch_assoc($result)) {
    $adminList[] = $row;
}

mysqli_free_result($result);
mysqli_close($link);

echo json_encode($adminList);
