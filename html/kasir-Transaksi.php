<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

// Query untuk mengambil barang yang belum diperbaiki dan sedang diperbaiki
$query = "SELECT b.ID_Service, b.tanggal_input, p.nama AS nama_pemilik, b.nama_barang, b.status
FROM barang b
JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
JOIN detail_keluhan dk ON b.ID_Service = dk.ID_Service
WHERE b.status IN ('Belum Diperbaiki', 'Sedang Diperbaiki') 
AND dk.konfirmasi_keterangan = 'Eksekusi'
ORDER BY b.tanggal_input ASC;";


$result = mysqli_query($link, $query);

if (!$result) {
  die("Query error: " . mysqli_error($link));
}

$transaksi = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);
mysqli_close($link);
?>
<!-- kasir-Transaksi.html -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Transaksi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
          <a href="/html/kasir-Dashboard.php" class="block py-2 px-4 hover:bg-gray-700" id="dashboardBtn">Dashboard</a>
        </li>
        <li>
          <a href="/html/kasir-Transaksi.php" class="block py-2 px-4 text-gray-800 bg-gray-500" id="transaksiBtn">Transaksi</a>
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
  </aside>
  <!-- Content Area: Transaksi -->
  <div class="ml-64 p-8">
    <div class="container mx-auto py-8">
      <h1 class="text-3xl font-bold mb-8 text-center">Daftar Transaksi</h1>
      <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4 flex justify-end">
          <input id="searchInput" type="text" class="w-1/4 px-3 py-2 border border-gray-300 rounded-md" placeholder="Cari berdasarkan nama pelanggan" />
        </div>
        <div class="overflow-x-auto">
          <table class="w-full border-collapse border border-gray-400">
            <thead>
              <tr class="bg-gray-200">
                <th class="px-4 py-2 border">No</th>
                <th class="px-4 py-2 border">Tanggal</th>
                <th class="px-4 py-2 border">Kode</th>
                <th class="px-4 py-2 border">Nama Pemilik</th>
                <th class="px-4 py-2 border">Nama Barang</th>
                <th class="px-4 py-2 border">Status</th>
              </tr>
            </thead>
            <tbody id="transaksiList">
              <?php if (!empty($transaksi)) : ?>
                <?php foreach ($transaksi as $index => $item) : ?>
                  <tr>
                    <td class="px-4 py-2 border"><?php echo $index + 1; ?></td>
                    <td class="px-4 py-2 border"><?php echo date('d-m-Y', strtotime($item['tanggal_input'])); ?></td>
                    <td class="px-4 py-2 border"><?php echo $item['ID_Service']; ?></td>
                    <td class="px-4 py-2 border"><?php echo $item['nama_pemilik']; ?></td>
                    <td class="px-4 py-2 border"><?php echo $item['nama_barang']; ?></td>
                    <td class="px-4 py-2 border"><?php echo $item['status']; ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else : ?>
                <tr>
                  <td class="px-4 py-2 border text-center" colspan="6">Tidak ada transaksi.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const searchInput = document.getElementById('searchInput');
      const transaksiList = document.getElementById('transaksiList');
      const rows = transaksiList.getElementsByTagName('tr');

      searchInput.addEventListener('keyup', function() {
        const searchTerm = searchInput.value.toLowerCase();

        for (let i = 0; i < rows.length; i++) {
          const namaPemilik = rows[i].getElementsByTagName('td')[3].textContent.toLowerCase();
          if (namaPemilik.includes(searchTerm)) {
            rows[i].style.display = '';
          } else {
            rows[i].style.display = 'none';
          }
        }
      });

      // Fungsi untuk logout
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
    });
  </script>
</body>

</html>