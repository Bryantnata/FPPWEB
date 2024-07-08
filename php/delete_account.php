<?php
include "connect_db.php";
require_once "../config/config.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['userId'];
$role = $data['role'];

// Validasi input
if (empty($userId) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'User ID dan role harus diisi']);
    exit();
}

// Validasi role
$valid_roles = ['kasir', 'teknisi', 'admin'];
if (!in_array($role, $valid_roles)) {
    echo json_encode(['success' => false, 'message' => 'Role tidak valid']);
    exit();
}

// Query untuk menghapus pengguna
$query = "DELETE FROM user WHERE id_user = ? AND role = ?";

if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "is", $userId, $role);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        echo json_encode(['success' => true, 'message' => 'Akun berhasil dihapus']);
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus akun: ' . mysqli_error($link)]);
    }
} else {
    mysqli_close($link);
    echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan query: ' . mysqli_error($link)]);
}
