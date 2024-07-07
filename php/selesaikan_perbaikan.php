<?php
header('Content-Type: application/json');
include "connect_db.php";

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $serviceId = $data['serviceId'];
    $keteranganAkhir = $data['keteranganAkhir'];
    $rincianItems = $data['rincianItems'];

    // Update status barang
    $updateBarangQuery = "UPDATE barang SET status = 'Selesai Diperbaiki' WHERE ID_Service = ?";
    $stmt = mysqli_prepare($link, $updateBarangQuery);
    mysqli_stmt_bind_param($stmt, "i", $serviceId);
    $updateBarangResult = mysqli_stmt_execute($stmt);

    // Update keterangan akhir
    $updateKeluhanQuery = "UPDATE detail_keluhan SET keterangan_akhir = ? WHERE ID_Service = ?";
    $stmt = mysqli_prepare($link, $updateKeluhanQuery);
    mysqli_stmt_bind_param($stmt, "si", $keteranganAkhir, $serviceId);
    $updateKeluhanResult = mysqli_stmt_execute($stmt);

    // Dapatkan id_keluhan dari detail_keluhan
    $getIdKeluhanQuery = "SELECT id_keluhan FROM detail_keluhan WHERE ID_Service = ?";
    $stmt = mysqli_prepare($link, $getIdKeluhanQuery);
    mysqli_stmt_bind_param($stmt, "i", $serviceId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $idKeluhan = $row['id_keluhan'];

    // Simpan rincian keluhan
    $insertRincianQuery = "INSERT INTO rincian_keluhan (id_keluhan, jumlah, nama, tipe, harga) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $insertRincianQuery);

    foreach ($rincianItems as $item) {
        mysqli_stmt_bind_param($stmt, "iissd", $idKeluhan, $item['jumlah'], $item['nama'], $item['tipe'], $item['harga']);
        mysqli_stmt_execute($stmt);
    }

    if ($updateBarangResult && $updateKeluhanResult) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Gagal mengupdate data");
    }
} catch (Exception $e) {
    error_log("Error in selesaikan_perbaikan.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

mysqli_close($link);
