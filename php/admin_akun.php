<?php
include "connect_db.php";
require_once "../config/config.php"; // Pastikan file ini mengatur variabel $link dan PEPPER

// Dapatkan data dari form
$name = $_POST['name'];
$username = $_POST['username'];
$no_hp = $_POST['no_hp'];
$role = $_POST['role'];
$password = $_POST['password'];

// Validasi input
if (empty($name) || empty($username) || empty($no_hp) || empty($role) || empty($password)) {
    header("Location: /html/form_tambah.php?error=empty_fields");
    exit();
}

// Validasi role
$valid_roles = ['admin', 'kasir', 'teknisi'];
if (!in_array($role, $valid_roles)) {
    header("Location: /html/form_tambah.php");
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
        header("Location: /html/admin-dashboard.php?success=user_added");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        header("Location: /html/form_tambah.php?error=" . urlencode(mysqli_error($link)));
        exit();
    }
} else {
    mysqli_close($link);
    header("Location: /html/form_tambah.php?error=" . urlencode(mysqli_error($link)));
    exit();
}
