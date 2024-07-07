<?php
include '/laragon/www/FPPWEB/php/connect_db.php'; // Sesuaikan path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'harga_') === 0) {
            $id_rincian = substr($key, 6); // Ambil ID rincian dari nama input
            $hargaBaru = floatval($value);

            // Query untuk update harga berdasarkan id rincian
            $update_harga_sql = "UPDATE rincian_keluhan SET harga = ? WHERE id_rincian = ?";
            $stmt_update_harga = $link->prepare($update_harga_sql);
            $stmt_update_harga->bind_param("di", $hargaBaru, $id_rincian);
            $stmt_update_harga->execute();
        }
    }

    // Mengirim respons JSON bahwa update berhasil
    echo json_encode(['success' => true]);
} else {
    // Mengirim respons JSON bahwa metode request tidak sesuai
    echo json_encode(['success' => false]);
}

// Tutup koneksi dan statement
$stmt_update_harga->close();
$link->close();
