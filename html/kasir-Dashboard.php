<?php
session_start();
include "/laragon/www/FPPWEB/php/connect_db.php";

// Cek apakah pengguna telah login dan apakah perannya adalah 'teknisi'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'kasir') {
  header("Location: role.php");
  exit();
}

// Query untuk mengambil jumlah barang yang keluar
$queryBarangKeluar = "SELECT COUNT(*) AS total FROM barang_keluar";
$resultBarangKeluar = mysqli_query($link, $queryBarangKeluar);

if (!$resultBarangKeluar) {
  die("ERROR " . mysqli_error($link));
}

// Ambil jumlah barang keluar
$rowBarangKeluar = mysqli_fetch_assoc($resultBarangKeluar);
$totalBarangKeluar = $rowBarangKeluar['total'];

// Bebaskan memori hasil query
mysqli_free_result($resultBarangKeluar);


// Query untuk mengambil jumlah transaksi hari ini
$queryTransaksiHariIni = "SELECT COUNT(*) AS total FROM barang WHERE DATE(tanggal_input) = CURDATE()";
$resultTransaksiHariIni = mysqli_query($link, $queryTransaksiHariIni);

if (!$resultTransaksiHariIni) {
  die("ERROR " . mysqli_error($link));
}

// Ambil jumlah transaksi hari ini
$rowTransaksiHariIni = mysqli_fetch_assoc($resultTransaksiHariIni);
$totalTransaksiHariIni = $rowTransaksiHariIni['total'];

// Bebaskan memori hasil query
mysqli_free_result($resultTransaksiHariIni);


// Query untuk menghitung barang yang sedang diperbaiki
$querySedangDiperbaiki = "SELECT COUNT(*) AS total FROM barang WHERE status = 'Sedang Diperbaiki'";
$resultSedangDiperbaiki = mysqli_query($link, $querySedangDiperbaiki);

if (!$resultSedangDiperbaiki) {
  die("Query gagal dijalankan: " . mysqli_error($link));
}

// Ambil jumlah barang yang sedang diperbaiki
$rowSedangDiperbaiki = mysqli_fetch_assoc($resultSedangDiperbaiki);
$totalSedangDiperbaiki = $rowSedangDiperbaiki['total'];

// Bebaskan memori hasil query
mysqli_free_result($resultSedangDiperbaiki);


// Query untuk menghitung barang yang selesai diperbaiki
$querySelesaiDiperbaiki = "SELECT COUNT(*) AS total FROM barang WHERE status = 'Selesai Diperbaiki'";
$resultSelesaiDiperbaiki = mysqli_query($link, $querySelesaiDiperbaiki);

if (!$resultSelesaiDiperbaiki) {
  die("Query gagal dijalankan: " . mysqli_error($link));
}

// Ambil jumlah barang yang selesai diperbaiki
$rowSelesaiDiperbaiki = mysqli_fetch_assoc($resultSelesaiDiperbaiki);
$totalSelesaiDiperbaiki = $rowSelesaiDiperbaiki['total'];

// Bebaskan memori hasil query
mysqli_free_result($resultSelesaiDiperbaiki);


// Query untuk mengambil data laporan barang dengan status terbaru dan dibatasi 10 row
$query = "SELECT b.id_barang AS ID_Barang, b.tanggal_input AS Tanggal_Masuk, p.nama AS Nama_Pemilik, b.nama_barang AS Nama_Barang, b.merk_barang AS Merk_Barang, b.jenis_barang AS Tipe_barang, b.status FROM barang b INNER JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan WHERE b.status IN ('Belum Diperbaiki', 'Sedang Diperbaiki', 'Selesai Diperbaiki') ORDER BY CASE WHEN b.status = 'Selesai Diperbaiki' THEN b.status_updated_at ELSE b.tanggal_input END DESC, b.tanggal_input DESC LIMIT 10";
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
  <title>Kasir</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/js/script.js"></script>
</head>

<body class="bg-gray-100">
  <!-- Sidebar -->
  <aside class="sidebar bg-gray-800 text-gray-400 w-64 min-h-screen fixed top-0 left-0 z-50">
    <!-- Logo -->
    <div class="flex items-center justify-center h-20 mb-4">
      <img src="/assets/logopweb.png" alt="Logo" class="h-16 w-auto" />
      <!-- Mengurangi tinggi logo agar tidak terlalu besar -->
    </div>
    <!-- Sidebar Content -->
    <nav class="mt-4">
      <ul>
        <li>
          <a href="/html/kasir-Dashboard.html" class="block py-2 px-4 hover:bg-gray-700 active:bg-blue-500" id="dashboardBtn">Dashboard</a>
        </li>
        <li>
          <a href="/html/kasir-Transaksi.html" class="block py-2 px-4 hover:bg-gray-700" id="transaksiBtn">Transaksi</a>
        </li>
        <li>
          <a href="/html/kasir-Pembayaran.html" class="block py-2 px-4 hover:bg-gray-700" id="pembayaranBtn">Pembayaran</a>
        </li>
        <li>
          <a href="/html/kasir-Riwayat.html" class="block py-2 px-4 hover:bg-gray-700" id="riwayatBtn">Riwayat</a>
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
        <div class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center">
          <div>
            <h2 class="text-lg font-semibold mb-2">Barang Masuk</h2>
            <p id="total-laporan" class="text-3xl font-bold text-red-500">
              <?php echo count($barangList); ?>
            </p>
          </div>
        </div>
        <div class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center">
          <div>
            <h2 class="text-lg font-semibold mb-2">Barang Keluar</h2>
            <p id="AngkbrgKeluar" class="text-3xl font-bold text-green-500">
              <?php echo $totalBarangKeluar; ?>
            </p>
          </div>
        </div>
      </div>
      <!-- Column 2: Jumlah Transaksi -->
      <div class="col-span-1 text-center h-full">
        <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
        <div class="p-20 bg-white rounded-lg shadow-md p-4 border border-gray-200 h-full flex justify-center items-center">
          <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
          <div class="flex flex-col items-center justify-center h-full">
            <!-- Tambahkan flex-col untuk mengatur posisi vertikal ke tengah -->
            <h2 class="text-lg font-semibold mb-2">
              Jumlah Transaksi Hari Ini
            </h2>
            <p id="laporan-hari-ini" class="text-3xl font-bold">
              <?php echo $totalTransaksiHariIni; ?>
            </p>
          </div>
        </div>
      </div>
      <!-- Column 3: Barang Keluar -->
      <div class="col-span-1 text-center h-full">
        <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
        <div class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center flex-col">
          <!-- Tambahkan flex-col untuk mengatur posisi vertikal ke tengah -->
          <div>
            <h2 class="text-lg font-semibold mb-2">On-Proses</h2>
            <!-- Tambahkan text-center untuk mengatur posisi horizontal ke tengah -->
            <p id="sedang-diperbaiki" class="text-3xl font-bold text-yellow-500">
              <?php echo $totalSedangDiperbaiki; ?>
            </p>
          </div>
        </div>
        <div class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center flex-col">
          <!-- Tambahkan flex-col untuk mengatur posisi vertikal ke tengah -->
          <div>
            <h2 class="text-lg font-semibold mb-2">Selesai</h2>
            <!-- Tambahkan text-center untuk mengatur posisi horizontal ke tengah -->
            <p id="selesai-diperbaiki" class="text-3xl font-bold text-blue-800">
              <?php echo $totalSelesaiDiperbaiki; ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tombol Tambah Barang -->
    <div class="flex justify-end mt-4">
      <button type="button" onclick="tambahBtn()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Tambah Barang
      </button>
    </div>
    <!-- Daftar Laporan Hari Ini-->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Notifikasi Update Status</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-400 rounded-lg">
          <thead>
            <tr class="divide-x divide-gray-400">
              <th class="px-4 py-2">No</th>
              <th class="px-4 py-2">ID barang</th>
              <th class="px-4 py-2">Tanggal Masuk</th>
              <th class="px-4 py-2">Nama Pemilik</th>
              <th class="px-4 py-2">Nama Barang</th>
              <th class="px-4 py-2">Merk Barang</th>
              <th class="px-4 py-2">Jenis Barang</th>
              <th class="px-4 py-2">Status</th>
            </tr>
          </thead>
          <tbody id="barangList">
            <?php
            $no = 1; // Initialize row number variable

            if (count($barangList) > 0) {
              foreach ($barangList as $row) {
                $status = $row["status"];

                // Apply styles based on status
                $statusClass = match ($status) {
                  'Belum Diperbaiki' => 'bg-red-800 text-white font-bold',
                  'Sedang Diperbaiki' => 'bg-orange-700 text-white font-bold',
                  'Selesai Diperbaiki' => 'bg-green-500 text-white font-bold',
                  default => '',
                };

                echo "<tr class='hover:bg-gray-50'>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $no . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $row["ID_Barang"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $row["Tanggal_Masuk"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Nama_Pemilik"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Nama_Barang"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Merk_Barang"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Tipe_barang"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center $statusClass'>" . $status . "</td>";
                echo "</tr>";
                $no++;
              }
            } else {
              echo "<tr><td colspan='8' class='px-4 py-2 text-center'>Tidak ada data barang.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
<script>
  function tambahBtn() {
    window.location.href = "/html/laporan.php"; // Ganti dengan path menuju halaman laporan.php yang benar
  }
  // Fungsi untuk logout
  const logoutButton = document.getElementById("logoutBtn");
  if (logoutButton) {
    logoutButton.addEventListener("click", function(event) {
      event.preventDefault(); // Mencegah aksi default dari anchor tag

      // Tampilkan sweetalert2 dialog
      Swal.fire({
        title: "Apakah kamu yakin ingin keluar?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, keluar",
        cancelButtonText: "Batal",
      }).then((result) => {
        if (result.isConfirmed) {
          // Jika pengguna menekan tombol "Ya, keluar", arahkan ke halaman index.html
          window.location.href = "/index.html";
        }
      });
    });
  }
</script>

</html>