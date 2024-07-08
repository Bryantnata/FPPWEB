<?php
include "connect_db.php";
require_once "../config/config.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';

if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Password tidak boleh kosong']);
    exit();
}

// Query untuk mengambil semua akun admin
$query = "SELECT id_user, username, password FROM user WHERE role = 'admin'";
$result = mysqli_query($link, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . mysqli_error($link)]);
    exit();
}

$verified = false;

while ($row = mysqli_fetch_assoc($result)) {
    $password_peppered = hash_hmac("sha256", $password, PEPPER);
    if (password_verify($password_peppered, $row['password'])) {
        $verified = true;
        break;
    }
}

mysqli_free_result($result);
mysqli_close($link);

if ($verified) {
    echo json_encode(['success' => true, 'message' => 'Verifikasi berhasil']);
} else {
    echo json_encode(['success' => false, 'message' => 'Verifikasi gagal']);
}
