<?php
include "connect_db.php";
require_once "../config/config.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$name = $data['nama'];
$username = $data['username'];
$no_hp = $data['no_hp'];
$role = $data['role'];
$password = $data['password'];

// Validasi input
if (empty($name) || empty($username) || empty($no_hp) || empty($role) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit();
}

// Validasi role
$valid_roles = ['kasir', 'teknisi', 'admin'];
if (!in_array($role, $valid_roles)) {
    echo json_encode(['success' => false, 'message' => 'Role tidak valid']);
    exit();
}

// Hash password dengan pepper sebelum disimpan
$password_peppered = hash_hmac("sha256", $password, PEPPER);
$hashed_password = password_hash($password_peppered, PASSWORD_ARGON2ID);

// Query untuk menambahkan pengguna baru
$query = "INSERT INTO user (nama, username, no_hp, role, password) VALUES (?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "sssss", $name, $username, $no_hp, $role, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        echo json_encode(['success' => true, 'message' => 'Akun berhasil ditambahkan']);
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan akun: ' . mysqli_error($link)]);
    }
} else {
    mysqli_close($link);
    echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan query: ' . mysqli_error($link)]);
}
