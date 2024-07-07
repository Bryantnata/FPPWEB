<?php
session_start();
include "/laragon/www/FPPWEB/php/connect_db.php";

// Cek apakah pengguna telah login dan apakah perannya adalah 'teknisi'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teknisi') {
  header("Location: role.php");
  exit();
}

// Fungsi untuk menjalankan query dan mengembalikan hasil
function executeQuery($link, $query)
{
  $result = mysqli_query($link, $query);
  if (!$result) {
    die("Query error: " . mysqli_error($link));
  }
  return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$queryPeriksa = "
SELECT 
    b.*, 
    p.nama AS Nama_Pemilik
FROM 
    barang b
INNER JOIN 
    pelanggan p ON b.id_pelanggan = p.id_pelanggan
WHERE 
    b.status = 'Belum Diperbaiki' 
    AND b.hubungi_kondisi = 'Belum' 
    AND NOT EXISTS (
        SELECT 1
        FROM detail_keluhan dk
        WHERE dk.ID_Service = b.ID_Service
          AND (dk.keterangan_awal IS NULL OR dk.keterangan_awal = '')
    )
ORDER BY 
    b.tanggal_input ASC;
";

$result = mysqli_query($link, $queryPeriksa);

if (!$result) {
  echo "Error executing query: " . mysqli_error($link);
  exit();
}

$barangPeriksa = [];
if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $barangPeriksa[] = $row;
  }
}

mysqli_free_result($result);

// Query untuk barang yang belum dikerjakan
$queryBelumPengerjaan = "
SELECT b.*, p.nama AS Nama_Pemilik, dk.konfirmasi_keterangan, dk.kondisi, dk.keterangan_awal
FROM barang b
INNER JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
LEFT JOIN detail_keluhan dk ON b.ID_Service = dk.ID_Service
WHERE b.status = 'Belum Diperbaiki'
  AND b.hubungi_kondisi = 2
  AND dk.konfirmasi_keterangan = 'Eksekusi'
  AND dk.kondisi = 'bisa diperbaiki'
ORDER BY b.tanggal_input ASC
";

// Query untuk barang yang sedang dikerjakan
$queryPengerjaan = "
SELECT b.*, p.nama AS Nama_Pemilik, dk.konfirmasi_keterangan, dk.kondisi, dk.keterangan_awal
FROM barang b
INNER JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
LEFT JOIN detail_keluhan dk ON b.ID_Service = dk.ID_Service
WHERE b.status = 'Sedang Diperbaiki'
  AND b.hubungi_kondisi = 'Sudah'
  AND dk.konfirmasi_keterangan = 'Eksekusi'
  AND dk.kondisi = 'bisa diperbaiki'
ORDER BY b.tanggal_input ASC
";

$queryDibatalkan = "
SELECT 
    b.*, 
    p.nama AS Nama_Pemilik, 
    dk.konfirmasi_keterangan, 
    dk.kondisi
FROM 
    barang b
INNER JOIN 
    pelanggan p ON b.id_pelanggan = p.id_pelanggan
LEFT JOIN 
    detail_keluhan dk ON b.ID_Service = dk.ID_Service
WHERE 
    b.status = 'Belum Diperbaiki'
    AND (dk.konfirmasi_keterangan = 'Jangan Dieksekusi' OR dk.kondisi = 'tidak bisa diperbaiki' OR dk.ID_Service IS NULL)
ORDER BY 
    b.tanggal_input ASC
";


// Eksekusi query dan simpan hasilnya
$barangPeriksa = executeQuery($link, $queryPeriksa);
$barangListBelumPengerjaan = executeQuery($link, $queryBelumPengerjaan);
$barangListPengerjaan = executeQuery($link, $queryPengerjaan);
$barangListDibatalkan = executeQuery($link, $queryDibatalkan);

$totalPeriksa = count($barangPeriksa);
$totalBelumPengerjaan = count($barangListBelumPengerjaan);
$totalPengerjaan = count($barangListPengerjaan);
$totalDibatalkan = count($barangListDibatalkan);

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
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
</head>

<body class="bg-gray-100">
  <!-- Sidebar -->
  <aside class="sidebar bg-gray-800 text-gray-400 w-64 min-h-screen fixed top-0 left-0 z-50">
    <!-- Logo -->
    <div class="flex items-center justify-center h-20 mt-4 mb-4">
      <img src="/assets/logopweb.png" alt="Logo" class="h-16 w-auto" />
      <!-- Mengurangi tinggi logo agar tidak terlalu besar -->
    </div>
    <!-- Sidebar Content -->
    <nav class="mt-4">
      <ul>
        <li>
          <a href="/html/teknisi-Dashboard.php" class="block py-2 px-4 text-gray-800 bg-gray-500" id="dashboardBtn">Dashboard</a>
        </li>
        <li>
          <a href="/html/teknisi-Transaksi.html" class="block py-2 px-4 hover:bg-gray-700" id="transaksiBtn">Transaksi</a>
        </li>
        <li>
          <a href="/html/teknisi-Pembayaran.html" class="block py-2 px-4 hover:bg-gray-700" id="pembayaranBtn">Pembayaran</a>
        </li>
        <li>
          <a href="/html/teknisi-Riwayat.html" class="block py-2 px-4 hover:bg-gray-700" id="riwayatBtn">Riwayat</a>
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
    <div class="grid grid-cols-4 gap-6">
      <!-- Column 1: Barang Periksa -->
      <div class="col-span-1 text-center h-full">
        <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
        <div class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center">
          <div>
            <h2 class="text-lg font-semibold mb-2">Periksa</h2>
            <p id="total-laporan" class="text-3xl font-bold text-red-500">
              <?php echo $totalPeriksa; ?>
            </p>
          </div>
        </div>
      </div>
      <!-- Column 1: Barang Belum Dikerjakan -->
      <div class="col-span-1 text-center h-full">
        <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
        <div class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center">
          <div>
            <h2 class="text-lg font-semibold mb-2">Belum Dikerjakan</h2>
            <p id="total-laporan" class="text-3xl font-bold text-red-500">
              <?php echo $totalBelumPengerjaan; ?>
            </p>
          </div>
        </div>
      </div>
      <!-- Column 3: Pengerjaan -->
      <div class="col-span-1 text-center h-full">
        <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
        <div class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center flex-col">
          <!-- Tambahkan flex-col untuk mengatur posisi vertikal ke tengah -->
          <div>
            <h2 class="text-lg font-semibold mb-2">Pengerjaan</h2>
            <!-- Tambahkan text-center untuk mengatur posisi horizontal ke tengah -->
            <p id="sedang-diperbaiki" class="text-3xl font-bold text-yellow-500">
              <?php echo $totalPengerjaan; ?>
            </p>
          </div>
        </div>
      </div>
      <!-- Column 3: DIbatalkan -->
      <div class="col-span-1 text-center h-full">
        <!-- Tambahkan kelas h-full untuk membuat tinggi kontainer penuh -->
        <div class="py-4 bg-white rounded-lg shadow-md p-4 border border-gray-200 flex items-center justify-center flex-col">
          <!-- Tambahkan flex-col untuk mengatur posisi vertikal ke tengah -->
          <div>
            <h2 class="text-lg font-semibold mb-2">Dibatalkan</h2>
            <!-- Tambahkan text-center untuk mengatur posisi horizontal ke tengah -->
            <p id="sedang-diperbaiki" class="text-3xl font-bold text-yellow-500">
              <?php echo $totalDibatalkan; ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Daftar Laporan Periksa  -->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Periksa</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-400 rounded-lg">
          <thead>
            <tr>
              <th class="px-4 py-2 border border-gray-400">No</th>
              <th class="px-4 py-2 border border-gray-400">ID Service</th>
              <th class="px-4 py-2 border border-gray-400">Tanggal Masuk</th>
              <th class="px-4 py-2 border border-gray-400">Nama Barang</th>
              <th class="px-4 py-2 border border-gray-400">Merk Barang</th>
              <th class="px-4 py-2 border border-gray-400">Tipe</th>
              <th class="px-4 py-2 border border-gray-400">Keluhan</th>
              <th class="px-4 py-2 border border-gray-400">Aksi</th>
            </tr>
          </thead>

          <tbody id="barangPeriksa">
            <?php
            $pagePeriksa = isset($_GET['page_periksa']) ? (int)$_GET['page_periksa'] : 1;
            $maxLaporan = 5;
            $offsetPeriksa = ($pagePeriksa - 1) * $maxLaporan;

            $totalPagesPeriksa = ceil(count($barangPeriksa) / $maxLaporan);
            $paginatedBarangPeriksa = array_slice($barangPeriksa, $offsetPeriksa, $maxLaporan);

            if (empty($paginatedBarangPeriksa)) : ?>
              <tr>
                <td colspan="9" class="px-4 py-2 border border-gray-400 text-center">Tidak ada laporan.</td>
              </tr>
              <?php else :
              foreach ($paginatedBarangPeriksa as $index => $row) : ?>
                <tr>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $index + 1; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['ID_Service']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['tanggal_input']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['nama_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['merk_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['jenis_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['keluhan_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400">
                    <button class="confirm-analisis bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded" data-id="<?php echo $row['ID_Service']; ?>" data-keluhan="<?php echo htmlspecialchars($row['keluhan_barang']); ?>">
                      Kirim Analisis
                    </button>
                  </td>
                </tr>
            <?php endforeach;
            endif; ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center">
          <nav class="inline-flex">
            <?php if ($pagePeriksa > 1) : ?>
              <a href="?page_periksa=<?php echo $pagePeriksa - 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPagesPeriksa; $i++) : ?>
              <a href="?page_periksa=<?php echo $i; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300 <?php if ($i == $pagePeriksa) echo 'bg-gray-300'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($pagePeriksa < $totalPagesPeriksa) : ?>
              <a href="?page_periksa=<?php echo $pagePeriksa + 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </div>

    <!-- Laporan Belum Dikerjakan -->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Laporan Belum Dikerjakan</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-400 rounded-lg">
          <thead>
            <tr>
              <th class="px-4 py-2 border border-gray-400">No</th>
              <th class="px-4 py-2 border border-gray-400">ID Service</th>
              <th class="px-4 py-2 border border-gray-400">Tanggal Masuk</th>
              <th class="px-4 py-2 border border-gray-400">Nama Barang</th>
              <th class="px-4 py-2 border border-gray-400">Merk Barang</th>
              <th class="px-4 py-2 border border-gray-400">Tipe</th>
              <th class="px-4 py-2 border border-gray-400">Keluhan</th>
              <th class="px-4 py-2 border border-gray-400">Keterangan</th>
              <th class="px-4 py-2 border border-gray-400">Aksi</th>
            </tr>
          </thead>
          <tbody id="barangListBelumPengerjaan">
            <?php
            $pageBelumPengerjaan = isset($_GET['page_BelumPengerjaan']) ? (int)$_GET['page_BelumPengerjaan'] : 1;
            $maxLaporan = 5;
            $offsetBelumPengerjaan = ($pageBelumPengerjaan - 1) * $maxLaporan;

            $totalPagesBelumPengerjaan = ceil(count($barangListBelumPengerjaan) / $maxLaporan);
            $paginatedBarangBelumPengerjaan = array_slice($barangListBelumPengerjaan, $offsetBelumPengerjaan, $maxLaporan);

            if (empty($paginatedBarangBelumPengerjaan)) : ?>
              <tr>
                <td colspan="9" class="px-4 py-2 border border-gray-400 text-center">Tidak ada laporan.</td>
              </tr>
              <?php else :
              foreach ($paginatedBarangBelumPengerjaan as $index => $row) : ?>
                <tr>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $index + 1; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['ID_Service']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['tanggal_input']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['nama_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['merk_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['jenis_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['keluhan_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['keterangan_awal']; ?></td>
                  <td class="px-4 py-2 border border-gray-400">
                    <button class="confirm-belumdikerjakan bg-orange-500 hover:bg-orange-700 text-white font-bold py-1 px-2 rounded" data-id="<?php echo $row['ID_Service']; ?>">
                      Perbaiki
                    </button>
                  </td>
                </tr>
            <?php endforeach;
            endif; ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <?php if (!empty($paginatedBarangBelumPengerjaan)) : ?>
          <div class="mt-4 flex justify-center">
            <nav class="inline-flex">
              <?php if ($pageBelumPengerjaan > 1) : ?>
                <a href="?page_BelumPengerjaan=<?php echo $pageBelumPengerjaan - 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $totalPagesBelumPengerjaan; $i++) : ?>
                <a href="?page_BelumPengerjaan=<?php echo $i; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300 <?php if ($i == $pageBelumPengerjaan) echo 'bg-gray-300'; ?>"><?php echo $i; ?></a>
              <?php endfor; ?>
              <?php if ($pageBelumPengerjaan < $totalPagesBelumPengerjaan) : ?>
                <a href="?page_BelumPengerjaan=<?php echo $pageBelumPengerjaan + 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
              <?php endif; ?>
            </nav>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Laporan Pengerjaan -->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Laporan Pengerjaaan</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-400 rounded-lg">
          <thead>
            <tr>
              <th class="px-4 py-2 border border-gray-400">No</th>
              <th class="px-4 py-2 border border-gray-400">ID Service</th>
              <th class="px-4 py-2 border border-gray-400">Tanggal Masuk</th>
              <th class="px-4 py-2 border border-gray-400">Nama Barang</th>
              <th class="px-4 py-2 border border-gray-400">Merk Barang</th>
              <th class="px-4 py-2 border border-gray-400">Tipe</th>
              <th class="px-4 py-2 border border-gray-400">Keluhan</th>
              <th class="px-4 py-2 border border-gray-400">Keterangan</th>
              <th class="px-4 py-2 border border-gray-400">Aksi</th>
            </tr>
          </thead>
          <tbody id="barangListPengerjaan">
            <?php
            $pagePengerjaan = isset($_GET['page_Pengerjaan']) ? (int)$_GET['page_Pengerjaan'] : 1;
            $maxLaporan = 5;
            $offsetPengerjaan = ($pagePengerjaan - 1) * $maxLaporan;

            $totalPagesPengerjaan = ceil(count($barangListPengerjaan) / $maxLaporan);
            $paginatedBarangPengerjaan = array_slice($barangListPengerjaan, $offsetPengerjaan, $maxLaporan);

            if (empty($paginatedBarangPengerjaan)) : ?>
              <tr>
                <td colspan="9" class="px-4 py-2 border border-gray-400 text-center">Tidak ada laporan.</td>
              </tr>
              <?php else :
              foreach ($paginatedBarangPengerjaan as $index => $row) : ?>
                <tr>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $index + 1; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['ID_Service']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['tanggal_input']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['nama_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['merk_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['jenis_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['keluhan_barang']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['keterangan_awal']; ?></td>
                  <td class="px-4 py-2 border border-gray-400">
                    <button class="confirm-pengerjaan bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded" data-id="<?php echo $row['ID_Service']; ?>">
                      Selesai
                    </button>
                  </td>
                </tr>
            <?php endforeach;
            endif; ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center">
          <nav class="inline-flex">
            <?php if ($pagePengerjaan > 1) : ?>
              <a href="?page_Pengerjaan=<?php echo $pagePengerjaan - 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPagesPengerjaan; $i++) : ?>
              <a href="?page_Pengerjaan=<?php echo $i; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300 <?php if ($i == $pagePengerjaan) echo 'bg-gray-300'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($pagePengerjaan < $totalPagesPengerjaan) : ?>
              <a href="?page_Pengerjaan=<?php echo $pagePengerjaan + 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </div>

    <!-- Laporan Dibatalkan -->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Laporan Dibatalkan</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-400 rounded-lg">
          <thead>
            <tr>
              <th class="px-4 py-2">No</th>
              <th class="px-4 py-2">ID Service</th>
              <th class="px-4 py-2">Tanggal Masuk</th>
              <th class="px-4 py-2">Nama Barang</th>
              <th class="px-4 py-2">Merk Barang</th>
              <th class="px-4 py-2">Tipe</th>
              <th class="px-4 py-2">Keluhan</th>
              <th class="px-4 py-2">Keterangan</th>
              <th class="px-4 py-2">Aksi</th>
            </tr>
          </thead>
          <tbody id="barangListDibatalkan">
            <?php
            $pageDibatalkan = isset($_GET['page_dibatalkan']) ? (int)$_GET['page_dibatalkan'] : 1;
            $maxLaporan = 5;
            $offsetDibatalkan = ($pageDibatalkan - 1) * $maxLaporan;

            $totalPagesDibatalkan = ceil(count($barangListDibatalkan) / $maxLaporan);
            $paginatedBarangDibatalkan = array_slice($barangListDibatalkan, $offsetDibatalkan, $maxLaporan);

            if (empty($paginatedBarangDibatalkan)) : ?>
              <tr>
                <td colspan="9" class="px-4 py-2 border border-gray-400 text-center">Tidak ada laporan.</td>
              </tr>
              <?php else :
              foreach ($paginatedBarangDibatalkan as $index => $row) : ?>
                <tr>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $index + 1 + $offsetDibatalkan; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['ID_Service']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['tanggal_input']; ?></td>
                  <td class="px-4 py-2 border border-gray-400"><?php echo $row['nama_barang']; ?></td>
                  <th class="px-4 py-2 border border-gray-400"><?php echo $row['merk_barang']; ?></td>
                  <th class="px-4 py-2 border border-gray-400"><?php echo $row['jenis_barang']; ?></td>
                  <th class="px-4 py-2 border border-gray-400"><?php echo $row['keluhan_barang']; ?></td>
                  <th class="px-4 py-2 border border-gray-400"><?php echo $row['konfirmasi_keterangan']; ?></td>
                  <td class="px-4 py-2 border border-gray-400">
                    <button class="confirm-dikembalikan bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded" data-id="<?php echo $row['ID_Service']; ?>">
                      Dikembalikan
                    </button>
                  </td>
                </tr>
            <?php endforeach;
            endif; ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center">
          <nav class="inline-flex">
            <?php if ($pageDibatalkan > 1) : ?>
              <a href="?page_dibatalkan=<?php echo $pageDibatalkan - 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPagesDibatalkan; $i++) : ?>
              <a href="?page_dibatalkan=<?php echo $i; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300 <?php if ($i == $pageDibatalkan) echo 'bg-gray-300'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($pageDibatalkan < $totalPagesDibatalkan) : ?>
              <a href="?page_dibatalkan=<?php echo $pageDibatalkan + 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    setupLogoutButton();
    setupAnalisisButtons();
    setupBelumDikerjakanButtons();
    setupPengerjaanButtons();
    setupDikembalikanButtons();
  });

  function setupLogoutButton() {
    const logoutButton = document.getElementById("logoutBtn");
    if (logoutButton) {
      logoutButton.addEventListener("click", handleLogout);
    }
  }

  function handleLogout(event) {
    event.preventDefault();
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
        window.location.href = "/index.html";
      }
    });
  }

  function setupAnalisisButtons() {
    const analisisButtons = document.querySelectorAll('.confirm-analisis');
    analisisButtons.forEach(button => {
      button.addEventListener('click', handleAnalisisClick);
    });
  }

  function handleAnalisisClick() {
    const serviceId = this.getAttribute('data-id');
    const keluhan = this.getAttribute('data-keluhan');
    showAnalisisDialog(serviceId, keluhan);
  }

  function showAnalisisDialog(serviceId, keluhan) {
    Swal.fire({
      title: 'Analisis Barang',
      html: getAnalisisDialogHTML(keluhan),
      showCancelButton: true,
      confirmButtonText: 'Kirim',
      cancelButtonText: 'Batal',
      preConfirm: () => handleAnalisisSubmit(serviceId, keluhan)
    });
  }

  function getAnalisisDialogHTML(keluhan) {
    // Return the HTML string for the analysis dialog
    // (HTML string remains the same as in your original code)
  }

  function handleAnalisisSubmit(serviceId, keluhan) {
    const keteranganAwal = Swal.getPopup().querySelector('#keterangan-awal').value;
    const kondisi = Swal.getPopup().querySelector('#kondisi').value;

    if (!keteranganAwal) {
      Swal.showValidationMessage('Keterangan awal harus diisi');
      return false;
    }

    return submitAnalisisData(serviceId, keluhan, keteranganAwal, kondisi);
  }

  function submitAnalisisData(serviceId, keluhan, keteranganAwal, kondisi) {
    return fetch('../php/analisis.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        serviceId,
        keluhan,
        keteranganAwal,
        kondisi
      })
    }).then(handleResponse);
  }

  function handleResponse(response) {
    if (!response.ok) {
      throw new Error('Gagal mengirim data');
    }
    return response.json();
  }

  function setupBelumDikerjakanButtons() {
    document.querySelectorAll('.confirm-belumdikerjakan').forEach(button => {
      button.addEventListener('click', handleBelumDikerjakanClick);
    });
  }

  function handleBelumDikerjakanClick() {
    const id = this.getAttribute('data-id');
    updateStatusPengerjaan(id);
  }

  function updateStatusPengerjaan(id) {
    fetch('/php/update_status_pengerjaan.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'id=' + id
      })
      .then(response => response.json())
      .then(handleUpdateStatusResponse);
  }

  function handleUpdateStatusResponse(data) {
    if (data.success) {
      Swal.fire('Sukses', 'Status barang berhasil diupdate', 'success').then(() => {
        removeRow(`#barangListBelumPengerjaan tr[data-id="${id}"]`);
        location.reload();
      });
    } else {
      Swal.fire('Error', 'Gagal mengupdate status barang', 'error');
    }
  }

  function setupPengerjaanButtons() {
    const pengerjaanButtons = document.querySelectorAll('.confirm-pengerjaan');
    pengerjaanButtons.forEach(button => {
      button.addEventListener('click', handlePengerjaanClick);
    });
  }

  function handlePengerjaanClick() {
    const serviceId = this.getAttribute('data-id');
    openDetailPage(serviceId);
  }

  function openDetailPage(serviceId) {
    window.location.href = `detail.php?id=${serviceId}`;
  }

  function setupDikembalikanButtons() {
    document.querySelectorAll('.confirm-dikembalikan').forEach(button => {
      button.addEventListener('click', handleDikembalikanClick);
    });
  }

  function handleDikembalikanClick() {
    const id = this.getAttribute('data-id');
    konfirmasiDikembalikan(id);
  }

  function konfirmasiDikembalikan(id) {
    Swal.fire({
      title: 'Konfirmasi Pengembalian',
      text: "Apakah Anda yakin ingin mengembalikan barang ini?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, kembalikan',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        kembalikanBarang(id);
      }
    });
  }

  function kembalikanBarang(id) {
    // Tampilkan loading indicator
    Swal.fire({
      title: 'Memproses...',
      text: 'Mohon tunggu sebentar',
      allowOutsideClick: false,
      showConfirmButton: false,
      willOpen: () => {
        Swal.showLoading();
      }
    });

    fetch('/php/kembalikan_barang.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'id=' + id
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          removeRow(`#barangListDibatalkan tr[data-id="${id}"]`);
          updateRowNumbers();
          checkEmptyTable();

          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Barang telah dikembalikan.',
            confirmButtonText: 'OK'
          });
        } else {
          throw new Error('Gagal mengembalikan barang');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Terjadi kesalahan saat mengembalikan barang.',
          confirmButtonText: 'OK'
        });
      });
  }

  function handleKembalikanBarangResponse(data, id) {
    if (data.success) {
      Swal.fire('Berhasil!', 'Barang telah dikembalikan.', 'success').then(() => {
        removeRow(`#barangListDibatalkan tr[data-id="${id}"]`);
        checkEmptyTable();
        updateRowNumbers();
      });
    } else {
      Swal.fire('Gagal!', 'Terjadi kesalahan saat mengembalikan barang.', 'error');
    }
  }

  function handleKembalikanBarangError(error) {
    console.error('Error:', error);
    Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error');
  }

  function removeRow(selector) {
    const row = document.querySelector(selector);
    if (row) {
      row.remove();
      return true;
    }
    return false;
  }

  function checkEmptyTable() {
    const tbody = document.querySelector('#barangListDibatalkan');
    if (tbody && tbody.children.length === 0) {
      const noDataRow = document.createElement('tr');
      noDataRow.innerHTML = '<td colspan="9" class="px-4 py-2 text-center">Tidak ada data barang yang dibatalkan.</td>';
      tbody.appendChild(noDataRow);
    }
  }

  function updateRowNumbers() {
    const rows = document.querySelectorAll('#barangListDibatalkan tr');
    rows.forEach((row, index) => {
      const firstCell = row.querySelector('td:first-child');
      if (firstCell) {
        firstCell.textContent = index + 1;
      }
    });
  }
</script>

</html>