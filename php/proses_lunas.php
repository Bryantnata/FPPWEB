<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_barang'])) {
    $id_barang = $_POST['id_barang'];

    // Start transaction
    mysqli_begin_transaction($link);

    try {
        // Fetch current status of barang
        $status_query = "SELECT status FROM barang WHERE ID_Service = ?";
        $stmt = mysqli_prepare($link, $status_query);
        mysqli_stmt_bind_param($stmt, "i", $id_barang);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $current_status = mysqli_fetch_assoc($result)['status'];

        // Update barang status to 'Selesai Diperbaiki' if it's not already
        if ($current_status !== 'Selesai Diperbaiki') {
            $update_query = "UPDATE barang SET status = 'Selesai Diperbaiki' WHERE ID_Service = ?";
            $stmt = mysqli_prepare($link, $update_query);
            mysqli_stmt_bind_param($stmt, "i", $id_barang);
            mysqli_stmt_execute($stmt);
        }

        // Update lunas, diambil, and dibayar status
        $update_status_query = "UPDATE barang SET lunas = 'Lunas', diambil = 'Sudah', dibayar = 'Sudah' WHERE ID_Service = ?";
        $stmt = mysqli_prepare($link, $update_status_query);
        mysqli_stmt_bind_param($stmt, "i", $id_barang);
        mysqli_stmt_execute($stmt);

        // Fetch barang and pelanggan data
        $select_query = "SELECT b.*, p.id_pelanggan, dk.id_user 
                         FROM barang b 
                         JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan 
                         LEFT JOIN detail_keluhan dk ON b.ID_Service = dk.ID_Service
                         WHERE b.ID_Service = ?";
        $stmt = mysqli_prepare($link, $select_query);
        mysqli_stmt_bind_param($stmt, "i", $id_barang);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        // Insert into barang_keluar
        $insert_query = "INSERT INTO barang_keluar (id_service, id_pelanggan, id_user, tanggal_keluar) 
                         VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($link, $insert_query);
        mysqli_stmt_bind_param($stmt, "iii", $id_barang, $row['id_pelanggan'], $row['id_user']);
        mysqli_stmt_execute($stmt);

        // Commit transaction
        mysqli_commit($link);

        echo json_encode(['success' => true, 'message' => 'Barang berhasil ditandai sebagai lunas, diambil, dan dibayar. Barang telah dipindahkan ke barang keluar.']);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($link);
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }

    mysqli_close($link);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
