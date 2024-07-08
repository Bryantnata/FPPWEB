<?php
session_start();
// Include file untuk koneksi ke database
include "connect_db.php"; // Pastikan file ini mengatur variabel $link

// Require file konfigurasi untuk pepper
require_once "../config/config.php"; // Pastikan file ini mengatur variabel $link dan config.php";

$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

// Hash password dengan pepper
$password_peppered = hash_hmac("sha256", $password, PEPPER);

// Ambil hashed password dari database
$query = "SELECT password, role FROM user WHERE username=?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $hashed_password = $row['password'];
    $user_role = $row['role'];

    // Verifikasi password menggunakan password_verify
    if (password_verify($password_peppered, $hashed_password)) {
        // Check if the role from the form matches the role in the database
        if ($role !== $user_role) {
            redirectWithErrorMessage('invalid_role_selection');
        }

        // Password cocok, mulai session
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user_role;

        // Redirect berdasarkan role
        switch ($user_role) {
            case 'admin':
                redirectWithMessage('admin');
                break;
            case 'kasir':
                redirectWithMessage('kasir');
                break;
            case 'teknisi':
                redirectWithMessage('teknisi');
                break;
            default:
                redirectWithErrorMessage('invalid_role');
                break;
        }
    } else {
        // Password tidak cocok
        redirectWithErrorMessage('invalid_credentials');
    }
} else {
    // Username tidak ditemukan
    redirectWithErrorMessage('invalid_credentials');
}

mysqli_stmt_close($stmt);
mysqli_close($link);

function redirectWithMessage($role)
{
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
        default:
            $dashboardUrl = '/';
            $message = 'Redirect tidak valid';
            break;
    }

    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    timerProgressBar: true,
                    title: 'Login Berhasil',
                    text: '$message',
                    timer: 1500,
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
    switch ($errorType) {
        case 'invalid_role_selection':
            $errorMessage = 'Role yang dipilih tidak sesuai. Silakan coba lagi.';
            break;
        case 'invalid_role':
            $errorMessage = 'Role tidak valid. Silakan hubungi administrator.';
            break;
        case 'invalid_credentials':
            $errorMessage = 'Username atau Password salah';
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
                    window.history.back();
                });
            });
          </script>";
    exit();
}
