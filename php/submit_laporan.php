<?php
include 'connect_db.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $address = mysqli_real_escape_string($link, $_POST['address']);
    $phone = mysqli_real_escape_string($link, $_POST['phone']);
    $itemName = mysqli_real_escape_string($link, $_POST['itemName']);
    $brand = mysqli_real_escape_string($link, $_POST['brand']);
    $type = mysqli_real_escape_string($link, $_POST['type']);
    $complaint = mysqli_real_escape_string($link, $_POST['complaint']);
    $id_pelanggan = isset($_POST['id_pelanggan']) ? mysqli_real_escape_string($link, $_POST['id_pelanggan']) : null;

    if (!empty($name) && !empty($address) && !empty($phone) && !empty($itemName) && !empty($brand) && !empty($type) && !empty($complaint)) {
        if ($id_pelanggan) {
            // Update data pelanggan yang sudah ada
            $sql_pelanggan = "UPDATE pelanggan SET nama = '$name', no_hp = '$phone', alamat = '$address' WHERE id_pelanggan = '$id_pelanggan'";
            mysqli_query($link, $sql_pelanggan);
            $last_id = $id_pelanggan;
        } else {
            // Tambah pelanggan baru
            $sql_pelanggan = "INSERT INTO pelanggan (nama, no_hp, alamat) VALUES ('$name', '$phone', '$address')";
            if (mysqli_query($link, $sql_pelanggan)) {
                $last_id = mysqli_insert_id($link);
            } else {
                $response["message"] = "Gagal menambahkan identitas pelanggan: " . mysqli_error($link);
                echo json_encode($response);
                exit;
            }
        }

        // Menyimpan data ke dalam tabel barang
        $sql_barang = "INSERT INTO barang (nama_barang, jenis_barang, merk_barang, keluhan_barang, id_pelanggan, status) 
                       VALUES ('$itemName', '$type', '$brand', '$complaint', '$last_id', 'Belum Diperbaiki')";

        if (mysqli_query($link, $sql_barang)) {
            $response["success"] = true;
            $response["message"] = "Laporan berhasil disimpan.";
        } else {
            $response["message"] = "Gagal menambahkan identitas barang: " . mysqli_error($link);
        }
    } else {
        $response["message"] = "Mohon lengkapi semua data.";
    }
} else {
    $response["message"] = "Invalid request method";
}

mysqli_close($link);
echo json_encode($response);
