<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

header('Content-Type: application/json');

$query = "DELETE FROM barang_keluar";
$result = mysqli_query($link, $query);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Semua riwayat berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus semua riwayat: ' . mysqli_error($link)]);
}

mysqli_close($link);
