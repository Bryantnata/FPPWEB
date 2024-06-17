<?php
// Memanggil file koneksi database
include 'connect_db.php';

// Inisialisasi respons JSON
$response = ["success" => false, "message" => ""];

// Memastikan bahwa permintaan yang diterima adalah metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Melakukan escape terhadap nilai yang diterima dari formulir
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $address = mysqli_real_escape_string($link, $_POST['address']);
    $phone = mysqli_real_escape_string($link, $_POST['phone']);
    $itemName = mysqli_real_escape_string($link, $_POST['itemName']);
    $brand = mysqli_real_escape_string($link, $_POST['brand']);
    $type = mysqli_real_escape_string($link, $_POST['type']);
    $complaint = mysqli_real_escape_string($link, $_POST['complaint']);

    // Memastikan bahwa semua data diterima dari formulir
    if (!empty($name) && !empty($address) && !empty($phone) && !empty($itemName) && !empty($brand) && !empty($type) && !empty($complaint)) {
        // Menyimpan data ke dalam tabel pelanggan
        $sql_pelanggan = "INSERT INTO pelanggan (nama, no_hp, alamat) VALUES ('$name', '$phone', '$address')";

        if (mysqli_query($link, $sql_pelanggan)) {
            $last_id = mysqli_insert_id($link);

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
            $response["message"] = "Gagal menambahkan identitas pelanggan: " . mysqli_error($link);
        }
    } else {
        $response["message"] = "Mohon lengkapi semua data.";
    }
} else {
    $response["message"] = "Invalid request method";
}

// Menutup koneksi database
mysqli_close($link);

// Mengembalikan respons dalam format JSON
echo json_encode($response);
