<?php
include "connect_db.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];
$rincianItems = $data['rincianItems'];
$totalHarga = $data['totalHarga'];

// Start transaction
mysqli_begin_transaction($link);

try {
    // Update total harga di tabel barang
    $updateBarangQuery = "UPDATE barang SET total_harga = ? WHERE ID_Service = ?";
    $stmt = mysqli_prepare($link, $updateBarangQuery);
    mysqli_stmt_bind_param($stmt, "di", $totalHarga, $id);
    mysqli_stmt_execute($stmt);

    // Update harga dan total di tabel rincian_keluhan
    $updateRincianQuery = "UPDATE rincian_keluhan SET harga = ?, total = ? WHERE id_keluhan = ? AND nama = ?";
    $stmt = mysqli_prepare($link, $updateRincianQuery);

    foreach ($rincianItems as $item) {
        mysqli_stmt_bind_param($stmt, "ddis", $item['harga'], $item['total'], $id, $item['nama']);
        mysqli_stmt_execute($stmt);
    }

    // Commit transaction
    mysqli_commit($link);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($link);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

mysqli_close($link);