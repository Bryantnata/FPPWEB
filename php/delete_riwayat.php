<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];

$query = "DELETE FROM barang_keluar WHERE id_barang_keluar = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Riwayat berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus riwayat: ' . mysqli_error($link)]);
}

mysqli_stmt_close($stmt);
mysqli_close($link);
