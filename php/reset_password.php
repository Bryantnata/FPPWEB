<?php
include "connect_db.php";
require_once "../config/config.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['userId'];
$newPassword = $data['newPassword'];

if (empty($userId) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'User ID dan password baru harus diisi']);
    exit();
}

// Hash password baru dengan pepper sebelum disimpan
$password_peppered = hash_hmac("sha256", $newPassword, PEPPER);
$hashed_password = password_hash($password_peppered, PASSWORD_ARGON2ID);

// Query untuk update password
$query = "UPDATE user SET password = ? WHERE id_user = ?";

if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $userId);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        echo json_encode(['success' => true, 'message' => 'Password berhasil direset']);
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        echo json_encode(['success' => false, 'message' => 'Gagal mereset password: ' . mysqli_error($link)]);
    }
} else {
    mysqli_close($link);
    echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan query: ' . mysqli_error($link)]);
}
