<?php
include "/laragon/www/FPPWEB/php/connect_db.php";
require_once "../config/config.php"; // Pastikan file ini ada dan mengatur variabel PEPPER

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'];

if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Password tidak boleh kosong']);
    exit();
}

// Query untuk mendapatkan password admin dari database
$query = "SELECT password FROM user WHERE role = 'admin' LIMIT 1";
$result = mysqli_query($link, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $hashed_password = $row['password'];

    // Verifikasi password
    $password_peppered = hash_hmac("sha256", $password, PEPPER);
    if (password_verify($password_peppered, $hashed_password)) {
        echo json_encode(['success' => true, 'message' => 'Verifikasi berhasil']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Password salah']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Tidak dapat menemukan akun admin']);
}

mysqli_close($link);
