<?php
include 'connect_db.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fungsi untuk membersihkan input
    function sanitize_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    // Sanitasi input
    $name = sanitize_input($_POST['name'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $itemName = sanitize_input($_POST['itemName'] ?? '');
    $brand = sanitize_input($_POST['brand'] ?? '');
    $type = sanitize_input($_POST['type'] ?? '');
    $complaint = sanitize_input($_POST['complaint'] ?? '');
    $id_pelanggan = filter_input(INPUT_POST, 'id_pelanggan', FILTER_VALIDATE_INT);

    if ($name && $address && $phone && $itemName && $brand && $type && $complaint) {
        mysqli_begin_transaction($link);

        try {
            if ($id_pelanggan) {
                // Update data pelanggan yang sudah ada
                $stmt = mysqli_prepare($link, "UPDATE pelanggan SET nama = ?, no_hp = ?, alamat = ? WHERE id_pelanggan = ?");
                mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $address, $id_pelanggan);
                mysqli_stmt_execute($stmt);
                $last_id = $id_pelanggan;
            } else {
                // Tambah pelanggan baru
                $stmt = mysqli_prepare($link, "INSERT INTO pelanggan (nama, no_hp, alamat) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sss", $name, $phone, $address);
                mysqli_stmt_execute($stmt);
                $last_id = mysqli_insert_id($link);
            }

            // Menyimpan data ke dalam tabel barang
            $stmt = mysqli_prepare($link, "INSERT INTO barang (nama_barang, jenis_barang, merk_barang, keluhan_barang, id_pelanggan, status, tanggal_input) VALUES (?, ?, ?, ?, ?, 'Belum Diperbaiki', NOW())");
            mysqli_stmt_bind_param($stmt, "ssssi", $itemName, $type, $brand, $complaint, $last_id);
            mysqli_stmt_execute($stmt);
            $id_service = mysqli_insert_id($link);

            // Mengambil tanggal input
            $stmt = mysqli_prepare($link, "SELECT tanggal_input FROM barang WHERE ID_Service = ?");
            mysqli_stmt_bind_param($stmt, "i", $id_service);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $tanggal_masuk = $row['tanggal_input'];

            mysqli_commit($link);

            $response["success"] = true;
            $response["message"] = "Laporan berhasil disimpan.";
            $response["id_service"] = $id_service;
            $response["tanggal_masuk"] = $tanggal_masuk;
            $response["name"] = $name;
            $response["address"] = $address;
            $response["phone"] = $phone;
            $response["itemName"] = $itemName;
            $response["brand"] = $brand;
            $response["type"] = $type;
            $response["complaint"] = $complaint;
        } catch (Exception $e) {
            mysqli_rollback($link);
            $response["message"] = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $response["message"] = "Mohon lengkapi semua data.";
    }
} else {
    $response["message"] = "Invalid request method";
}

mysqli_close($link);
echo json_encode($response);
