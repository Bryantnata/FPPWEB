<?php
include "connect_db.php";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = $_GET['id'];

    $query = "SELECT dk.keterangan_awal FROM detail_keluhan dk WHERE dk.ID_Service = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(["success" => true, "keterangan_awal" => $row['keterangan_awal']]);
    } else {
        echo json_encode(["success" => false, "error" => "Keterangan awal tidak ditemukan"]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>