<!-- admin-tambah.php -->
<?php
include "connect_db.php";

// Dapatkan data dari form
$name = $_POST['name'];
$username = $_POST['username'];
$no_hp = $_POST['no_hp'];
$role = $_POST['role'];
$password = $_POST['password'];

// Validasi role
$valid_roles = ['admin', 'kasir', 'teknisi'];
if (!in_array($role, $valid_roles)) {
    header("Location: /html/form_tambah.php?error=invalid_role");
    exit();
}

// Query untuk menambahkan pengguna baru
$query = "INSERT INTO user (nama, username, no_hp, role, password) VALUES (?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "sssss", $name, $username, $no_hp, $role, $password);

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
