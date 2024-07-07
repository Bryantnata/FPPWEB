<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
include "connect_db.php";

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['serviceId']) || !isset($data['keteranganAwal']) || !isset($data['kondisi'])) {
        throw new Exception('Invalid input data');
    }

    $serviceId = $data['serviceId'];
    $keteranganAwal = $data['keteranganAwal'];
    $kondisi = $data['kondisi'];
    $username = $_SESSION['username'] ?? null;

    if (!$username) {
        throw new Exception('User not authenticated');
    }

    // Dapatkan id_user berdasarkan username
    $userQuery = "SELECT id_user FROM user WHERE username = ?";
    $userStmt = mysqli_prepare($link, $userQuery);
    mysqli_stmt_bind_param($userStmt, "s", $username);
    mysqli_stmt_execute($userStmt);
    $userResult = mysqli_stmt_get_result($userStmt);
    $userData = mysqli_fetch_assoc($userResult);

    if (!$userData) {
        throw new Exception('User not found');
    }

    $id_user = $userData['id_user'];

    // Set keterangan_akhir sama dengan keterangan_awal jika tidak bisa diperbaiki
    $keteranganAkhir = ($kondisi === 'tidak bisa diperbaiki') ? $keteranganAwal : '';

    // Simpan keterangan awal ke database
    $query = "INSERT INTO detail_keluhan (ID_Service, keterangan_awal, keterangan_akhir, kondisi, id_user, konfirmasi_keterangan) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $query);
    $konfirmasi_keterangan = ($kondisi === 'tidak bisa diperbaiki') ? 'Jangan Dieksekusi' : null;
    mysqli_stmt_bind_param($stmt, "isssis", $serviceId, $keteranganAwal, $keteranganAkhir, $kondisi, $id_user, $konfirmasi_keterangan);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($link));
    }

    // Update status barang
    if ($kondisi === 'tidak bisa diperbaiki') {
        $status = 'Belum Diperbaiki';
        $hubungi_kondisi = 'Sudah';
    } elseif ($kondisi === 'bisa diperbaiki') {
        $status = 'Belum Diperbaiki';
        $hubungi_kondisi = 'Belum';
    } else {
        $status = 'Dalam Proses';
        $hubungi_kondisi = 'Sudah';
    }

    $updateQuery = "UPDATE barang SET status = ?, hubungi_kondisi = ? WHERE ID_Service = ?";
    $updateStmt = mysqli_prepare($link, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "ssi", $status, $hubungi_kondisi, $serviceId);

    if (!mysqli_stmt_execute($updateStmt)) {
        throw new Exception(mysqli_error($link));
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($link)) {
        mysqli_close($link);
    }
}
