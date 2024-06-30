<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $query = "UPDATE barang SET hubungi_ambil = 'Sudah' WHERE ID_Service = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($link)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID tidak diberikan']);
}
