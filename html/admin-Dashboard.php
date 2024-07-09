<?php
session_start();
include "/laragon/www/FPPWEB/php/connect_db.php";

// Periksa apakah pengguna sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: role.php");
  exit();
}

// Fungsi untuk mendapatkan jumlah dari query
function getCount($link, $query)
{
  $result = mysqli_query($link, $query);
  if (!$result) {
    die("ERROR " . mysqli_error($link));
  }
  $row = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $row['total'];  // Hapus ?? 0
}

// Dapatkan total pendapatan
$queryTotalRevenue = "SELECT SUM(total) AS total FROM rincian_keluhan";
$totalRevenue = getCount($link, $queryTotalRevenue);

// Dapatkan total pelanggan
$queryTotalCustomers = "SELECT COUNT(DISTINCT id_pelanggan) AS total FROM barang";
$totalCustomers = getCount($link, $queryTotalCustomers);

// Dapatkan total perbaikan
$queryTotalRepairs = "SELECT COUNT(*) AS total FROM barang WHERE status = 'Selesai Diperbaiki'";
$totalRepairs = getCount($link, $queryTotalRepairs);

// Dapatkan rata-rata waktu perbaikan
$queryAvgRepairTime = "SELECT AVG(TIMESTAMPDIFF(SECOND, tanggal_input, tanggal_selesai)) AS total FROM barang WHERE status = 'Selesai Diperbaiki'";
$avgRepairTime = getCount($link, $queryAvgRepairTime);
$avgRepairTime = $avgRepairTime ? round($avgRepairTime) : 0; // Bulatkan ke detik terdekat

// Dapatkan data barang keluar dengan total nominal
$queryBarangKeluar = "SELECT bk.id_service, p.nama AS nama_pemilik, b.nama_barang, 
COALESCE(SUM(rk.total), 0) AS nominal_total,
MAX(bk.tanggal_keluar) AS tanggal_keluar
FROM barang_keluar bk
JOIN barang b ON bk.id_service = b.ID_Service
JOIN pelanggan p ON bk.id_pelanggan = p.id_pelanggan
LEFT JOIN detail_keluhan dk ON dk.ID_Service = b.ID_Service
LEFT JOIN rincian_keluhan rk ON rk.id_keluhan = dk.id_keluhan
GROUP BY bk.id_service, p.nama, b.nama_barang
ORDER BY tanggal_keluar DESC
LIMIT 10";

$resultBarangKeluar = mysqli_query($link, $queryBarangKeluar);
if (!$resultBarangKeluar) {
  die("Error in query: " . mysqli_error($link));
}
$barangKeluar = [];
while ($row = mysqli_fetch_assoc($resultBarangKeluar)) {
  $barangKeluar[] = $row;
}
mysqli_free_result($resultBarangKeluar);

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
  <!-- Sidebar -->
  <aside class="sidebar bg-gray-800 text-gray-400 w-64 min-h-screen fixed top-0 left-0 z-50">
    <!-- Logo -->
    <div class="flex items-center justify-center h-20 mt-4 mb-4">
      <img src="/assets/logopweb.png" alt="Logo" class="h-16 w-auto" />
    </div>
    <!-- Sidebar Content -->
    <nav class="mt-4">
      <ul>
        <li>
          <a href="/html/admin-Dashboard.php" class="block py-2 px-4 text-white bg-blue-600 hover:bg-blue-700" id="dashboardBtn">Dashboard</a>
        </li>
        <li>
          <a href="/html/admin-akun.php" class="block py-2 px-4 hover:bg-gray-700" id="akunBtn">Akun</a>
        </li>
        <li>
          <a href="/html/admin-riwayat.php" class="block py-2 px-4 hover:bg-gray-700" id="riwayatBtn">Riwayat</a>
        </li>
      </ul>
    </nav>
    <!-- Logout Button -->
    <div class="absolute bottom-10 left-0 w-full font-bold lg:block">
      <a href="#" id="logoutBtn" class="block w-2/3 py-3 mx-auto text-sm text-white text-center bg-red-600 hover:bg-red-700 rounded-md z-10">Log Out</a>
    </div>
  </aside>

  <!-- Content Area -->
  <div class="ml-64 p-8">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">Dashboard Admin</h1>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <h2 class="text-xl font-semibold mb-2 text-gray-700">Total Pendapatan</h2>
        <p class="text-3xl font-bold text-green-600">Rp<?php echo number_format($totalRevenue, 0, ',', '.'); ?></p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <h2 class="text-xl font-semibold mb-2 text-gray-700">Total Pelanggan</h2>
        <p class="text-3xl font-bold text-blue-600"><?php echo $totalCustomers; ?></p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <h2 class="text-xl font-semibold mb-2 text-gray-700">Total Perbaikan</h2>
        <p class="text-3xl font-bold text-purple-600"><?php echo $totalRepairs; ?></p>
      </div>
    </div>

    <!-- Daftar Barang Keluar -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
      <h2 class="text-2xl font-semibold mb-4 text-gray-800">Daftar 10 Transasksi Terakhir</h2>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-200">
              <th class="px-4 py-2 text-center text-gray-600">ID Barang</th>
              <th class="px-4 py-2 text-center text-gray-600">Nama Pemilik</th>
              <th class="px-4 py-2 text-center text-gray-600">Nama Barang</th>
              <th class="px-4 py-2 text-right text-gray-600">Nominal Total</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (count($barangKeluar) > 0) {
              foreach ($barangKeluar as $item) {
                echo "<tr class='border-b hover:bg-gray-50'>";
                echo "<td class='px-4 py-2 text-center'>" . htmlspecialchars($item['id_service']) . "</td>";
                echo "<td class='px-4 py-2 text-center'>" . htmlspecialchars($item['nama_pemilik']) . "</td>";
                echo "<td class='px-4 py-2 text-center'>" . htmlspecialchars($item['nama_barang']) . "</td>";
                echo "<td class='px-4 py-2 text-right'>Rp " . number_format($item['nominal_total'], 0, ',', '.') . "</td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='4' class='text-center px-4 py-2'>Tidak ada data barang keluar.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    // Fungsi logout
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Anda yakin ingin keluar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, keluar',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = '/index.html';
        }
      });
    });
  </script>
</body>

</html>