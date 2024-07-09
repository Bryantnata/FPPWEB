<?php
session_start();
include "/laragon/www/FPPWEB/php/connect_db.php";

// Cek apakah pengguna telah login dan apakah perannya adalah 'kasir'
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
$querySelesaiDiperbaiki = "SELECT COUNT(*) AS total FROM barang WHERE status = 'Selesai Diperbaiki' AND lunas = 'Belum'";
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
    dk.kondisi AS Kondisi,
    dk.keterangan_awal AS Keterangan_Awal,
    dk.konfirmasi_keterangan AS Penjelasan,
    dk.keterangan_akhir AS Keterangan_Akhir
FROM 
    barang b 
INNER JOIN 
    pelanggan p ON b.id_pelanggan = p.id_pelanggan 
LEFT JOIN 
    detail_keluhan dk ON b.ID_Service = dk.ID_Service
WHERE 
    (
        (b.status IN ('Belum Diperbaiki', 'Sedang Diperbaiki'))
        AND (
            DATEDIFF(CURDATE(), b.tanggal_input) >= 3
            OR dk.kondisi = 'bisa diperbaiki'
        )
        AND b.hubungi_kondisi = 'Belum'
    )
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
    dk.kondisi AS Kondisi,
    b.hubungi_ambil,
    b.dikembalikan
FROM 
    barang b 
INNER JOIN 
    pelanggan p ON b.id_pelanggan = p.id_pelanggan 
LEFT JOIN 
    detail_keluhan dk ON b.ID_Service = dk.ID_Service
WHERE 
    (b.status = 'Selesai Diperbaiki' OR b.dikembalikan = 'Sudah') AND b.lunas = 'Belum'
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
  <title>Kasir-Dashboard</title>
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
          <a href="/html/kasir-Dashboard.php" class="block py-2 px-4 text-gray-800 bg-gray-500" id="dashboardBtn">Dashboard</a>
        </li>
        <li>
          <a href="/html/kasir-Transaksi.php" class="block py-2 px-4 hover:bg-gray-700" id="transaksiBtn">Transaksi</a>
        </li>
        <li>
          <a href="/html/kasir-Pembayaran.php" class="block py-2 px-4 hover:bg-gray-700" id="pembayaranBtn">Pembayaran</a>
        </li>
        <li>
          <a href="/html/kasir-Riwayat.php" class="block py-2 px-4 hover:bg-gray-700" id="riwayatBtn">Riwayat</a>
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
            <h2 class="text-lg font-semibold mb-2">Konfirmasi Pelanggan</h2>
            <p id="total-laporan" class="text-3xl font-bold text-red-500">
              <?php echo count($barangList); ?>
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
            <h2 class="text-lg font-semibold mb-2">Selesai</h2>
            <!-- Tambahkan text-center untuk mengatur posisi horizontal ke tengah -->
            <p id="sedang-diperbaiki" class="text-3xl font-bold text-yellow-500">
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
    <!-- Daftar Laporan  -->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Laporan Pemberitahuan</h2>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-400">
          <thead>
            <tr class="bg-gray-200">
              <th class="px-4 py-2 border">No</th>
              <th class="px-4 py-2 border">ID Service</th>
              <th class="px-4 py-2 border">Nama Pemilik</th>
              <th class="px-4 py-2 border">Nama Barang</th>
              <th class="px-4 py-2 border">No. Hp</th>
              <th class="px-4 py-2 border">Keterangan</th>
              <th class="px-4 py-2 border">Kondisi</th>
              <th class="px-4 py-2 border">Status</th>
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

                echo "<tr class='hover:bg-gray-50 text-center' data-id='" . $row["ID_Service"] . "'>";
                echo "<td class='px-4 py-2 border border text-center'>" . $no . "</td>";
                echo "<td class='px-4 py-2 border border text-center'>" . $row["ID_Service"] . "</td>";
                echo "<td class='px-4 py-2 border border'>" . $row["Nama_Pemilik"] . "</td>";
                echo "<td class='px-4 py-2 border border'>" . $row["Nama_Barang"] . "</td>";
                echo "<td class='px-4 py-2 border border text-center'>";
                echo "<button class='show-contact' data-nama='" . $row["Nama_Pemilik"] . "' data-nohp='" . $row["no_hp"] . "' data-description='" . $row["Keterangan_Awal"] . "' data-id='" . $row["ID_Service"] . "' data-type='pemberitahuan'>";
                echo "<i class='fas fa-envelope'></i></button>";
                echo "</td>";
                echo "<td class='px-4 py-2 border border text-center'>";
                if (!empty($row["Keterangan_Awal"])) {
                  echo "<button class='show-description-with-options' 
                  data-description='" . htmlspecialchars($row["Keterangan_Awal"], ENT_QUOTES) . "' 
                  data-id='" . $row["ID_Service"] . "' 
                  data-konfirmasi=''
                  data-hubungi='belum'>";
                  echo "<i class='fas fa-file-alt'></i></button>";
                } else {
                  echo "-";
                }
                echo "</td>";

                echo "<td class='px-4 py-2 border border text-center'>" . $kondisi . "</td>";
                echo "<td class='px-4 py-2 border border text-center $statusClass'>" . $status . "</td>";
                echo "</tr>";
                $no++;
              }
            } else {
              echo "<tr><td colspan='8' class='px-4 py-2 border text-center'>Tidak ada data barang yang perlu dihubungi.</td></tr>";
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

    <!-- Laporan Pengambilan -->
    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-4">Laporan Pengambilan</h2>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-400">
          <thead>
            <tr class="bg-gray-200">
              <th class="px-4 py-2 border">No</th>
              <th class="px-4 py-2 border">ID Service</th>
              <th class="px-4 py-2 border">Nama Pemilik</th>
              <th class="px-4 py-2 border">Nama Barang</th>
              <th class="px-4 py-2 border">No. Hp</th>
              <th class="px-4 py-2 border">Keterangan</th>
              <th class="px-4 py-2 border">Kondisi</th>
              <th class="px-4 py-2 border">Status</th>
              <th class="px-4 py-2 border">Aksi</th>
            </tr>
          </thead>
          <tbody id="barangListPengambilan">
            <?php
            $no = 1;
            $pagePengambilan = isset($_GET['pagePengambilan']) ? (int)$_GET['pagePengambilan'] : 1;
            $maxLaporan = 5;
            $offsetPengambilan = ($pagePengambilan - 1) * $maxLaporan;
            $totalPagesPengambilan = ceil(count($barangListPengambilan) / $maxLaporan);
            $paginatedListPengambilan = array_slice($barangListPengambilan, $offsetPengambilan, $maxLaporan);

            if (count($paginatedListPengambilan) > 0) {
              foreach ($paginatedListPengambilan as $row) {
                $hubungiAmbilClass = $row["hubungi_ambil"] == 'Sudah' ? 'bg-white' : 'bg-pink-200';

                echo "<tr class='hover:bg-gray-50 text-center' data-id='" . $row["ID_Service"] . "'>";
                echo "<td class='px-4 py-2 border  text-center'>" . $no . "</td>";
                echo "<td class='px-4 py-2 border  text-center'>" . $row["ID_Service"] . "</td>";
                echo "<td class='px-4 py-2 border '>" . $row["Nama_Pemilik"] . "</td>";
                echo "<td class='px-4 py-2 border '>" . $row["Nama_Barang"] . "</td>";
                echo "<td class='px-4 py-2 border  text-center $hubungiAmbilClass'>";
                echo "<button class='show-contact' data-nama='" . $row["Nama_Pemilik"] . "' data-nohp='" . $row["no_hp"] . "' data-description='" . $row["Keterangan_Akhir"] . "' data-id='" . $row["ID_Service"] . "' data-type='pengambilan'>";
                echo "<i class='fas fa-envelope'></i></button>";
                echo "</td>";
                echo "<td class='px-4 py-2 border  text-center'>";
                if (!empty($row["Keterangan_Akhir"])) {
                  echo "<button class='show-description' data-description='" . htmlspecialchars($row["Keterangan_Akhir"], ENT_QUOTES) . "'>";
                  echo "<i class='fas fa-file-alt'></i></button>";
                } else {
                  echo "-";
                }
                echo "</td>";
                echo "<td class='px-4 py-2 border  text-center'>" . (isset($row["Kondisi"]) ? $row["Kondisi"] : 'Belum Diperiksa') . "</td>";
                echo "<td class='px-4 py-2 border  text-center bg-green-500 text-white font-bold'>" . $row["status"] . "</td>";
                echo "<td class='border py-2 px-4 text-center'>";
                if ($row["hubungi_ambil"] == 'Sudah') {
                  echo "<button class='confirm-pickup bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded' data-id='" . $row["ID_Service"] . "'>Konfirmasi Pengambilan</button>";
                }
                echo "</td>";
                echo "</tr>";
                $no++;
              }
            } else {
              echo "<tr><td colspan='9' class='px-4 py-2 border text-center'>Tidak ada data barang yang siap diambil.</td></tr>";
            }
            ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center">
          <nav class="inline-flex">
            <?php if ($pagePengambilan > 1) : ?>
              <a href="?pagePengambilan=<?php echo $pagePengambilan - 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPagesPengambilan; $i++) : ?>
              <a href="?pagePengambilan=<?php echo $i; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300 <?php if ($i == $page) echo 'bg-gray-300'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($pagePengambilan < $totalPagesPengambilan) : ?>
              <a href="?pagePengambilan=<?php echo $pagePengambilan + 1; ?>" class="px-3 py-2 mx-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </div>
  </div>
</body>

<script>
  function tambahBtn() {
      window.location.href = "/html/laporan.php";
    }
    
  document.addEventListener('DOMContentLoaded', function() {
    // Fungsi-fungsi utilitas

    function removeProcessedItems() {
      const barangListPengambilan = document.getElementById('barangListPengambilan');
      if (barangListPengambilan) {
        const rows = barangListPengambilan.getElementsByTagName('tr');
        for (let i = rows.length - 1; i >= 0; i--) {
          const statusCell = rows[i].querySelector('td:nth-child(8)');
          if (statusCell && statusCell.textContent.trim() === 'Lunas') {
            rows[i].remove();
          }
        }
      }
    }

    // Fungsi-fungsi untuk menangani interaksi UI
    function showPopup(nama, noHp, description, id, type, row) {
      Swal.fire({
        title: 'Kontak Pemilik',
        html: `
        <div class="text-left">
          <p class="font-bold">Nama:</p>
          <p>${nama}</p>
          <p class="font-bold">Nomor HP:</p>
          <p>${noHp}</p>
          <p class="font-bold">Deskripsi:</p>
          <div class="border p-2 rounded-md">${description}</div>
        </div>
      `,
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Hubungi via WhatsApp',
        denyButtonText: 'Sudah Dihubungi',
        cancelButtonText: 'Tutup',
        customClass: {
          popup: 'rounded-lg shadow-lg p-6',
          title: 'text-lg font-semibold',
          htmlContainer: 'text-gray-700',
          confirmButton: 'bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded',
          denyButton: 'bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded',
          cancelButton: 'bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          if (!noHp.startsWith('62')) {
            noHp = '62' + noHp.slice(1);
          }
          var whatsappUrl = `https://wa.me/${noHp}`;
          window.open(whatsappUrl, '_blank');
          showPopup(nama, noHp, description, id, type, row);
        } else if (result.isDenied) {
          if (type === 'pemberitahuan') {
            updateHubungiKondisi(id);
            row.querySelector('.show-description-with-options').setAttribute('data-hubungi', 'sudah');
            Swal.fire('Kontak telah dihubungi!', '', 'success');
          } else {
            updateHubungiAmbil(id, row);
          }
        }
      });
    }

    function konfirmasiPengambilan(id) {
      Swal.fire({
        title: 'Konfirmasi Pengambilan',
        text: 'Apakah Anda yakin ingin mengkonfirmasi pengambilan barang ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Konfirmasi',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          checkPricingAndRedirect(id);
        }
      });
    }

    // Fungsi-fungsi untuk menangani permintaan ke server
    function updateHubungiAmbil(id, row) {
      fetch('/php/update_hubungi_ambil.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'id=' + id
        }).then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Kontak telah dihubungi!', '', 'success').then(() => {
              row.querySelector('td:nth-child(5)').classList.remove('bg-pink-200');
              row.querySelector('td:nth-child(5)').classList.add('bg-white');
              row.querySelector('td:last-child').innerHTML = "<button class='confirm-pickup bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded' data-id='" + id + "'>Konfirmasi Pengambilan</button>";
            });
          } else {
            Swal.fire('Error!', 'Gagal mengupdate status.', 'error');
          }
        });
    }

    function updateHubungiKondisi(id) {
      fetch('/php/update_hubungi_kondisi.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'id=' + id
        }).then(response => response.json())
        .then(data => {
          if (data.success) {
            var contactButton = document.querySelector(`.show-contact[data-id="${id}"]`);
            if (contactButton) {
              contactButton.setAttribute('data-hubungi', 'sudah');
            }
          } else {
            Swal.fire('Error!', 'Gagal mengupdate status.', 'error');
          }
        });
    }

    function updateKonfirmasiKeterangan(id, konfirmasi) {
      fetch('/php/update_konfirmasi_keterangan.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'id=' + id + '&konfirmasi=' + konfirmasi
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Sukses', 'Konfirmasi keterangan berhasil diupdate', 'success').then(() => {
              const row = document.querySelector(`tr[data-id="${id}"]`);
              if (row) {
                row.remove();
              }
            });
          } else {
            Swal.fire('Error', 'Gagal mengupdate konfirmasi keterangan', 'error');
          }
        });
    }

    function checkPricingAndRedirect(id) {
      fetch('/php/check_pricing.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
          if (data.pricingFilled) {
            window.location.href = `/html/nota.php?id=${id}`;
          } else {
            window.location.href = `/html/detailPembayaran.php?id=${id}`;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire('Error', 'Terjadi kesalahan saat memeriksa harga', 'error');
        });
    }

    // Event listeners
    const logoutButton = document.getElementById("logoutBtn");
    if (logoutButton) {
      logoutButton.addEventListener("click", function(event) {
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
      });
    }

    document.querySelectorAll('.show-contact').forEach(function(button) {
      button.addEventListener('click', function() {
        var nama = this.getAttribute('data-nama');
        var noHp = this.getAttribute('data-nohp');
        var description = this.getAttribute('data-description');
        var id = this.getAttribute('data-id');
        var type = this.getAttribute('data-type');
        var row = this.closest('tr');
        showPopup(nama, noHp, description, id, type, row);
      });
    });

    document.querySelectorAll('.show-description-with-options').forEach(function(button) {
      button.addEventListener('click', function() {
        var description = this.getAttribute('data-description');
        var id = this.getAttribute('data-id');
        var currentKonfirmasi = this.getAttribute('data-konfirmasi') || ''; // Tambahkan nilai default jika kosong

        Swal.fire({
          title: 'Konfirmasi',
          html: `
        <div class="text-left border p-2 rounded-md mb-4">${description}</div>
        <div class="mt-4">
          <label for="konfirmasi" class="block mb-2">Konfirmasi Keterangan:</label>
          <select id="konfirmasi" class="w-full p-2 border rounded">
            <option value="Eksekusi" ${currentKonfirmasi === 'Eksekusi' ? 'selected' : ''}>Eksekusi</option>
            <option value="Jangan Dieksekusi" ${currentKonfirmasi === 'Jangan Dieksekusi' ? 'selected' : ''}>Jangan Dieksekusi</option>
          </select>
        </div>
      `,
          showCancelButton: true,
          confirmButtonText: 'Kirim',
          cancelButtonText: 'Tutup',
          customClass: {
            popup: 'rounded-lg shadow-lg p-6',
            title: 'text-lg font-semibold',
            htmlContainer: 'text-gray-700',
            confirmButton: 'bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded',
            cancelButton: 'bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded'
          },
          preConfirm: () => {
            const konfirmasi = Swal.getPopup().querySelector('#konfirmasi').value;
            if (!konfirmasi) {
              Swal.showValidationMessage('Silakan pilih konfirmasi');
            }
            return {
              konfirmasi: konfirmasi
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            // Cek apakah pelanggan sudah dihubungi
            var contactButton = this.closest('tr').querySelector('.show-contact');
            var hubungi = contactButton.getAttribute('data-hubungi') || 'belum';

            if (hubungi === 'belum') {
              Swal.fire({
                title: 'Peringatan',
                text: 'Anda belum menghubungi Pelanggan',
                icon: 'warning',
                confirmButtonText: 'OK'
              });
            } else {
              updateKonfirmasiKeterangan(id, result.value.konfirmasi);
            }
          }
        });
      });
    });

    document.querySelectorAll('.show-description').forEach(function(button) {
      button.addEventListener('click', function() {
        var description = this.getAttribute('data-description');
        Swal.fire({
          title: 'Deskripsi Perbaikan',
          html: `<div class="text-left border p-2 rounded-md">${description}</div>`,
          confirmButtonText: 'Tutup',
          customClass: {
            popup: 'rounded-lg shadow-lg p-6',
            title: 'text-lg font-semibold',
            htmlContainer: 'text-gray-700',
            confirmButton: 'bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded'
          }
        });
      });
    });

    // Menggunakan delegasi event untuk tombol "Konfirmasi Pengambilan"
    document.body.addEventListener('click', function(event) {
      if (event.target && event.target.classList.contains('confirm-pickup')) {
        const id = event.target.getAttribute('data-id');
        konfirmasiPengambilan(id);
      }
    });

    // Panggil fungsi ini saat halaman dimuat
    removeProcessedItems();
  });
</script>



</html>