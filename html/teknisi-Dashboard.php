<?php
session_start();
include "/laragon/www/FPPWEB/php/connect_db.php";

// Cek apakah pengguna telah login dan apakah perannya adalah 'kasir'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teknisi') {
  header("Location: role.php");
  exit();
}

// Query untuk mengambil data laporan barang dengan status terbaru dan dibatasi 10 row
$query = "
SELECT 
    b.ID_Service AS ID_Service, 
    b.tanggal_input AS Tanggal_Masuk, 
    p.nama AS Nama_Pemilik, 
    b.nama_barang AS Nama_Barang, 
    b.merk_barang AS Merk_Barang, 
    p.no_hp AS no_hp,
    b.status,
    b.keluhan_barang AS Deskripsi_Keluhan,
    b.hubungi_kondisi,
    dk.deskripsi AS Deskripsi,
    dk.kondisi AS Kondisi,
    dk.keterangan_awal AS Keterangan_Awal,
    dk.konfirmasi_keterangan AS Penjelasan,
    dk.keterangan_akhir AS Keterangan_Akhir
FROM 
    barang b 
INNER JOIN 
    pelanggan p ON b.id_pelanggan = p.id_pelanggan 
LEFT JOIN 
    detail_keluhan dk ON b.ID_Service = dk.id_barang
WHERE 
    (b.status IN ('Belum Diperbaiki', 'Sedang Diperbaiki'))
    AND DATEDIFF(CURDATE(), b.tanggal_input) >= 3
    AND b.hubungi_kondisi = 'Belum'
ORDER BY 
    b.tanggal_input ASC
";

$queryPengambilan = "
SELECT 
    b.ID_Service AS ID_Service, 
    b.tanggal_input AS Tanggal_Masuk, 
    p.nama AS Nama_Pemilik, 
    b.nama_barang AS Nama_Barang, 
    b.merk_barang AS Merk_Barang, 
    p.no_hp AS no_hp,
    b.status,
    dk.keterangan_awal AS Keterangan_Awal,
    dk.konfirmasi_keterangan AS Penjelasan,
    dk.keterangan_akhir AS Keterangan_Akhir,
    b.hubungi_ambil
FROM 
    barang b 
INNER JOIN 
    pelanggan p ON b.id_pelanggan = p.id_pelanggan 
LEFT JOIN 
    detail_keluhan dk ON b.ID_Service = dk.id_barang
WHERE 
    b.status = 'Selesai Diperbaiki'
ORDER BY 
    b.status_updated_at DESC
";

$resultPengambilan = mysqli_query($link, $queryPengambilan);

if (!$resultPengambilan) {
  die("ERROR " . mysqli_error($link));
}

$barangListPengambilan = [];
while ($row = mysqli_fetch_assoc($resultPengambilan)) {
  $barangListPengambilan[] = $row;
}
mysqli_free_result($resultPengambilan);

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
          <a href="/html/kasir-Dashboard.html" class="block py-2 px-4 text-gray-800 bg-gray-500" id="dashboardBtn">Dashboard</a>
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
              <?php echo $totalSelesaiDiperbaiki; ?>
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
              <?php echo $totalSelesaiDiperbaiki; ?>
            </p>
          </div>
        </div>
      </div>
    </div>
    <!-- Daftar Laporan  -->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Laporan Masuk</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-400 rounded-lg">
          <thead>
            <tr class="divide-x divide-gray-400">
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
          <tbody id="barangList">
            <?php
            $no = 1;
            $page = isset($_GET['page_status']) ? (int)$_GET['page_status'] : 1;
            $maxLaporan = 5;
            $offset = ($page - 1) * $maxLaporan;
            $totalPages = ceil(count($barangList) / $maxLaporan);
            $paginatedList = array_slice($barangList, $offset, $maxLaporan);

            if (count($paginatedList) > 0) {
              foreach ($paginatedList as $row) {
                $status = $row["status"];
                $kondisi = $row["Kondisi_Keluhan"] ?? 'Belum diperiksa'; // Default jika kosong

                // Apply styles based on status
                $statusClass = match ($status) {
                  'Belum Diperbaiki' => 'bg-red-800 text-white font-bold',
                  'Sedang Diperbaiki' => 'bg-orange-700 text-white font-bold',
                  default => '',
                };

                echo "<tr class='hover:bg-gray-50' data-id='" . $row["ID_Service"] . "'>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $no . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $row["ID_Service"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Nama_Pemilik"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Nama_Barang"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>";
                echo "<button class='show-contact' data-nama='" . $row["Nama_Pemilik"] . "' data-nohp='" . $row["no_hp"] . "' data-description='" . $row["Keterangan_Awal"] . "' data-id='" . $row["ID_Service"] . "' data-type='pemberitahuan'>";
                echo "<i class='fas fa-envelope'></i></button>";
                echo "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>";
                if (!empty($row["Keterangan_Awal"])) {
                  echo "<button class='show-description-with-options' 
                  data-description='" . htmlspecialchars($row["Keterangan_Awal"], ENT_QUOTES) . "' 
                  data-id='" . $row["ID_Service"] . "' 
                  data-konfirmasi='" . htmlspecialchars($row["Penjelasan"], ENT_QUOTES) . "'
                  data-hubungi='belum'>";
                  echo "<i class='fas fa-file-alt'></i></button>";
                } else {
                  echo "-";
                }
                echo "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $kondisi . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center $statusClass'>" . $status . "</td>";
                echo "</tr>";
                $no++;
              }
            } else {
              echo "<tr><td colspan='8' class='px-4 py-2 text-center'>Tidak ada data barang yang perlu dihubungi.</td></tr>";
            }
            ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center">
          <nav class="inline-flex">
            <?php if ($page > 1) : ?>
              <a href="?page_status=<?php echo $page - 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
              <a href="?page_status=<?php echo $i; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300 <?php if ($i == $page) echo 'bg-gray-300'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages) : ?>
              <a href="?page_status=<?php echo $page + 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </div>

    <!-- Laporan Pengerjaan -->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Laporan Pengerjaaan</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-400 rounded-lg">
          <thead>
            <tr class="divide-x divide-gray-400">
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
          <tbody id="barangListPengerjaan">
            <?php
            $no = 1;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $maxLaporan = 5;
            $offset = ($page - 1) * $maxLaporan;
            $totalPages = ceil(count($barangListPengambilan) / $maxLaporan);
            $paginatedList = array_slice($barangListPengambilan, $offset, $maxLaporan);

            if (count($paginatedList) > 0) {
              foreach ($paginatedList as $row) {
                $hubungiAmbilClass = $row["hubungi_ambil"] == 'Sudah' ? 'bg-white' : 'bg-pink-200';

                echo "<tr class='hover:bg-gray-50' data-id='" . $row["ID_Service"] . "'>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $no . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $row["ID_Service"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Nama_Pemilik"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Nama_Barang"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center $hubungiAmbilClass'>";
                echo "<button class='show-contact' data-nama='" . $row["Nama_Pemilik"] . "' data-nohp='" . $row["no_hp"] . "' data-description='" . $row["Keterangan_Akhir"] . "' data-id='" . $row["ID_Service"] . "' data-type='pengambilan'>";
                echo "<i class='fas fa-envelope'></i></button>";
                echo "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>";
                if (!empty($row["Keterangan_Akhir"])) {
                  echo "<button class='show-description' data-description='" . htmlspecialchars($row["Keterangan_Akhir"], ENT_QUOTES) . "'>";
                  echo "<i class='fas fa-file-alt'></i></button>";
                } else {
                  echo "-";
                }
                echo "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . (isset($row["Kondisi"]) ? $row["Kondisi"] : 'Belum Diperiksa') . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center bg-green-500 text-white font-bold'>" . $row["status"] . "</td>";
                echo "<td class='border py-2 border-gray-400 text-center'>";
                if ($row["hubungi_ambil"] == 'Sudah') {
                  echo "<button class='confirm-pickup bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded' data-id='" . $row["ID_Service"] . "'>Selesai</button>";
                }
                echo "</td>";
                echo "</tr>";
                $no++;
              }
            } else {
              echo "<tr><td colspan='9' class='px-4 py-2 text-center'>Tidak ada data barang yang siap diambil.</td></tr>";
            }
            ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center">
          <nav class="inline-flex">
            <?php if ($page > 1) : ?>
              <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
              <a href="?page=<?php echo $i; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300 <?php if ($i == $page) echo 'bg-gray-300'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages) : ?>
              <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </div>

    <!-- Laporan Pengerjaan -->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Laporan Dibatalkan</h2>
      <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-400 rounded-lg">
          <thead>
            <tr class="divide-x divide-gray-400">
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
            $no = 1;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $maxLaporan = 5;
            $offset = ($page - 1) * $maxLaporan;
            $totalPages = ceil(count($barangListPengambilan) / $maxLaporan);
            $paginatedList = array_slice($barangListPengambilan, $offset, $maxLaporan);

            if (count($paginatedList) > 0) {
              foreach ($paginatedList as $row) {
                $hubungiAmbilClass = $row["hubungi_ambil"] == 'Sudah' ? 'bg-white' : 'bg-pink-200';

                echo "<tr class='hover:bg-gray-50' data-id='" . $row["ID_Service"] . "'>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $no . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . $row["ID_Service"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Nama_Pemilik"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400'>" . $row["Nama_Barang"] . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center $hubungiAmbilClass'>";
                echo "<button class='show-contact' data-nama='" . $row["Nama_Pemilik"] . "' data-nohp='" . $row["no_hp"] . "' data-description='" . $row["Keterangan_Akhir"] . "' data-id='" . $row["ID_Service"] . "' data-type='pengambilan'>";
                echo "<i class='fas fa-envelope'></i></button>";
                echo "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>";
                if (!empty($row["Keterangan_Akhir"])) {
                  echo "<button class='show-description' data-description='" . htmlspecialchars($row["Keterangan_Akhir"], ENT_QUOTES) . "'>";
                  echo "<i class='fas fa-file-alt'></i></button>";
                } else {
                  echo "-";
                }
                echo "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center'>" . (isset($row["Kondisi"]) ? $row["Kondisi"] : 'Belum Diperiksa') . "</td>";
                echo "<td class='border px-4 py-2 border-gray-400 text-center bg-green-500 text-white font-bold'>" . $row["status"] . "</td>";
                echo "<td class='border py-2 border-gray-400 text-center'>";
                if ($row["hubungi_ambil"] == 'Sudah') {
                  echo "<button class='confirm-pickup bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded' data-id='" . $row["ID_Service"] . "'>Selesai</button>";
                }
                echo "</td>";
                echo "</tr>";
                $no++;
              }
            } else {
              echo "<tr><td colspan='9' class='px-4 py-2 text-center'>Tidak ada data barang yang siap diambil.</td></tr>";
            }
            ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center">
          <nav class="inline-flex">
            <?php if ($page > 1) : ?>
              <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
              <a href="?page=<?php echo $i; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300 <?php if ($i == $page) echo 'bg-gray-300'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages) : ?>
              <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
            <?php endif; ?>
          </nav>
        </div>
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