<?php
include "connect_db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = mysqli_real_escape_string($link, $_POST['id']);

    // Update status barang menjadi 'Dikembalikan'
    $query = "UPDATE barang SET dikembalikan = 'Sudah', status = 'Selesai Diperbaiki' WHERE ID_Service = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($link)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

mysqli_close($link);