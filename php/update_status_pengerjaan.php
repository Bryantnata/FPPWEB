<?php
include "connect_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    $query = "UPDATE barang SET status = 'Sedang Diperbaiki' WHERE ID_Service = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => mysqli_error($link)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>