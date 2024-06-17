<!-- teknisi-Dashboard.html -->
<?php
session_start();
include "/laragon/www/FPPWEB/php/connect_db.php";

// Cek apakah pengguna telah login dan apakah perannya adalah 'teknisi'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teknisi') {
  header("Location: role.php");
  exit();
}

// Query untuk mengambil data laporan barang
$query = "SELECT * FROM barang";
$result = mysqli_query($link, $query);

$barangList = [];

if (!$result) {
  $error_message = mysqli_error($link);
  echo "<script>
          setTimeout(function () {
              Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: 'Query gagal dijalankan: " . addslashes($error_message) . "',
                  background: '#1e3a8a', // bg-blue-950
                  color: '#ffffff',
                  showConfirmButton: true,
                  confirmButtonText: 'OK'
              });
          }, 100);
        </script>";
} else {
  // Memproses hasil query
  while ($row = mysqli_fetch_assoc($result)) {
    $barangList[] = $row;
  }
  mysqli_free_result($result);
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Teknisi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
  <body class="bg-gray-100">
    <!-- Sidebar -->
    <aside
      class="sidebar bg-gray-800 text-gray-400 w-64 min-h-screen fixed top-0 left-0 z-50"
    >
      <!-- Logo -->
      <div class="flex items-center justify-center h-20 mb-4">
        <img src="/asset/logopweb.png" alt="Logo" class="h-16 w-auto" />
        <!-- Mengurangi tinggi logo agar tidak terlalu besar -->
      </div>
      <!-- Sidebar Content -->
      <nav class="mt-4">
        <ul>
          <li>
            <a
              href="/html/teknisi-Dashboard.html"
              class="block py-2 px-4 hover:bg-gray-700 active:bg-blue-500"
              id="dashboardBtn"
              >Dashboard</a
            >
          </li>
          <li>
            <a
              href="/html/teknisi-laporan.html"
              class="block py-2 px-4 hover:bg-gray-700"
              id="transaksiBtn"
              >Laporan</a
            >
          </li>
          <li>
            <a
              href="/html/teknisi-riwayat.html"
              class="block py-2 px-4 hover:bg-gray-700"
              id="pembayaranBtn"
              >Riwayat</a
            >
          </li>
        </ul>
      </nav>
      <!-- Logout Button -->
      <div class="absolute bottom-10 left-0 w-full font-bold lg:block">
        <a href="#" id="logoutBtn" class="block w-2/3 py-3 mx-auto text-sm text-white text-center bg-red-600 hover:bg-red-700 rounded-md z-10">Log Out</a>
      </div>
      <!-- Jam -->
<div id="clock" class="absolute bottom-1 left-1/2 transform -translate-x-1/2 -translate-y-1/2 font-bold lg:block text-center text-white"></div>
    </aside>
    <!-- Content Area -->
    <div class="ml-64 p-8">
      <!-- Content Goes Here -->
      <div class="grid grid-cols-3 gap-6">
        <!-- Column 1: Barang Masuk -->
        <div class="col-span-1 text-center h-full">
          <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
          <div
            class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center"
          >
            <div>
              <h2 class="text-lg font-semibold mb-2">Barang Masuk</h2>
              <p id="total-laporan" class="text-3xl font-bold text-red-500"></p>
            </div>
          </div>
          <div
            class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center"
          >
            <div>
              <h2 class="text-lg font-semibold mb-2">Barang Keluar</h2>
              <p id="AngkbrgKeluar" class="text-3xl font-bold text-green-500">
                128
              </p>
            </div>
          </div>
        </div>
        <!-- Column 2: Jumlah Transaksi -->
        <div class="col-span-1 text-center h-full">
          <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
          <div
            class="p-20 bg-white rounded-lg shadow-md p-4 border border-gray-200 h-full flex justify-center items-center"
          >
            <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
            <div class="flex flex-col items-center justify-center h-full">
              <!-- Tambahkan flex-col untuk mengatur posisi vertikal ke tengah -->
              <h2 class="text-lg font-semibold mb-2">
                Jumlah Transaksi Hari Ini
              </h2>
              <p id="laporan-hari-ini" class="text-3xl font-bold"></p>
              <!-- Hapus text-center dan ganti dengan flex-grow untuk meletakkan teks ke tengah vertikal -->
            </div>
          </div>
        </div>
        <!-- Column 3: Barang Keluar -->
        <div class="col-span-1 text-center h-full">
          <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
          <div
            class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center flex-col"
          >
            <!-- Tambahkan flex-col untuk mengatur posisi vertikal ke tengah -->
            <div>
              <h2 class="text-lg font-semibold mb-2">On-Proses</h2>
              <!-- Tambahkan text-center untuk mengatur posisi horizontal ke tengah -->
              <p id="sedang-diperbaiki" class="text-3xl font-bold text-yellow-500">
                235
              </p>
            </div>
          </div>
          <div
            class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center flex-col"
          >
            <!-- Tambahkan flex-col untuk mengatur posisi vertikal ke tengah -->
            <div>
              <h2 class="text-lg font-semibold mb-2">Selesai</h2>
              <!-- Tambahkan text-center untuk mengatur posisi horizontal ke tengah -->
              <p id="selesai-diperbaiki" class="text-3xl font-bold text-blue-800">
                128
              </p>
            </div>
          </div>
        </div>
      </div>
      <!-- Daftar Laporan Hari Ini-->
      <div class="mt-8">
        <h2 class="text-lg font-semibold mb-4">Laporan Hari Ini</h2>
        <div class="overflow-x-auto">
          <table class="w-full bg-white border border-gray-400 rounded-lg">
            <!-- Menggunakan ID yang sama -->
            <thead>
              <tr class="divide-x divide-gray-400">
                <th class="px-4 py-2">No</th>
                <th class="px-4 py-2">Tanggal</th>
                <th class="px-4 py-2">Kode</th>
                <th class="px-4 py-2">Nama Pemilik</th>
                <th class="px-4 py-2">Tipe Barang</th>
                <th class="px-4 py-2">Status</th>
              </tr>
            </thead>
            <tbody id="notifikasiList">
              <!-- Isi tabel akan ditambahkan melalui JavaScript -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- Content Area: End -->
    <!-- JavaScript -->
    <script src="/js/script.js"></script>
    <!-- Panggil fungsi displayReports untuk menampilkan laporan di kasir-dashboard.html -->
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        displayReports("dashboard");
      });
    </script>
  </body>
</html>
