<?php
session_start();
include "connect_db.php"; // Ensure this file sets up the $link variable

$role = $_POST['role'];
$username = $_POST['username'];
$password = $_POST['password'];

// Debugging
error_log("Username: $username");
error_log("Password: $password");

// Check if $link is defined
if (!isset($link)) {
    redirectWithErrorMessage('database_error');
    exit();
}

// Menggunakan prepared statement untuk menghindari SQL injection
$query = "SELECT * FROM user WHERE username=? AND role=?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "ss", $username, $role);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ($password === $row['password']) { // Menggunakan == karena belum di-hash
        // Memeriksa apakah role yang dimiliki oleh pengguna adalah role yang valid
        $valid_roles = ['admin', 'kasir', 'teknisi'];
        if (!in_array($row['role'], $valid_roles)) {
            redirectWithErrorMessage('invalid_role');
        }

        // Password sesuai, mulai session
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $row['role'];

        // Redirect berdasarkan peran
        switch ($role) {
            case 'admin':
                if ($row['role'] === 'admin') {
                    redirectWithMessage('admin');
                } else {
                    redirectWithErrorMessage('invalid_role');
                }
                break;
            case 'kasir':
                if ($row['role'] === 'kasir') {
                    redirectWithMessage('kasir');
                } else {
                    redirectWithErrorMessage('invalid_role');
                }
                break;
            case 'teknisi':
                if ($row['role'] === 'teknisi') {
                    redirectWithMessage('teknisi');
                } else {
                    redirectWithErrorMessage('invalid_role');
                }
                break;
            default:
                redirectWithErrorMessage('invalid_role');
                break;
        }
    } else {
        // Password tidak sesuai
        redirectWithErrorMessage('invalid_credentials');
    }
} else {
    // User tidak ditemukan
    redirectWithErrorMessage('invalid_credentials');
}

mysqli_stmt_close($stmt);
mysqli_close($link);

function redirectWithMessage($role)
{
    $dashboardUrl = '';

    switch ($role) {
        case 'admin':
            $dashboardUrl = '/html/admin-Dashboard.php';
            $message = 'Anda akan dialihkan ke Dashboard Admin';
            break;
        case 'kasir':
            $dashboardUrl = '/html/kasir-Dashboard.php';
            $message = 'Anda akan dialihkan ke Dashboard Kasir';
            break;
        case 'teknisi':
            $dashboardUrl = '/html/teknisi-Dashboard.php';
            $message = 'Anda akan dialihkan ke Dashboard Teknisi';
            break;
    }

    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil',
                    text: '$message',
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                }).then(() => {
                    window.location.href = '$dashboardUrl';
                });
            });
          </script>";
    exit();
}

function redirectWithErrorMessage($errorType)
{
    global $link; // Ensure $link is accessible
    $errorMessage = '';
    switch ($errorType) {
        case 'invalid_role':
            $errorMessage = 'Role tidak valid. Silakan hubungi administrator.';
            break;
        case 'invalid_credentials':
            $errorMessage = 'Username atau Password salah';
            break;
        case 'database_error':
            $errorMessage = 'Kesalahan Database: ' . mysqli_error($link);
            break;
        default:
            $errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
            break;
    }

    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: '$errorMessage',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = '/html/login.php?error=" . urlencode($errorType) . "';
                });
            });
          </script>";
    exit();
}
