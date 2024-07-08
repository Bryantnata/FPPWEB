<?php
// Pastikan tidak ada output sebelum header
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

include "connect_db.php";
require_once "../config/config.php";

// Tangkap error dan kembalikan sebagai JSON
function handleError($errno, $errstr, $errfile, $errline)
{
    $error = [
        'success' => false,
        'message' => "PHP Error: [$errno] $errstr in $errfile on line $errline"
    ];
    echo json_encode($error);
    exit;
}
set_error_handler("handleError");

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'];
    $username = $data['username'];
    $no_hp = $data['no_hp'];
    $role = $data['role'];
    $password = $data['password'];

    // Validasi input
    if (empty($name) || empty($username) || empty($no_hp) || empty($role) || empty($password)) {
        throw new Exception('Semua field harus diisi');
    }

    // Validasi role
    $valid_roles = ['kasir', 'teknisi', 'admin'];
    if (!in_array($role, $valid_roles)) {
        throw new Exception('Role tidak valid');
    }

    // Hash password dengan pepper sebelum disimpan
    $password_peppered = hash_hmac("sha256", $password, PEPPER);
    $hashed_password = password_hash($password_peppered, PASSWORD_ARGON2ID);

    // Query untuk menambahkan pengguna baru
    $query = "INSERT INTO user (nama, username, no_hp, role, password) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "sssss", $name, $username, $no_hp, $role, $hashed_password);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Akun berhasil ditambahkan']);
        } else {
            throw new Exception('Gagal menambahkan akun: ' . mysqli_error($link));
        }
        mysqli_stmt_close($stmt);
    } else {
        throw new Exception('Gagal mempersiapkan query: ' . mysqli_error($link));
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($link);
