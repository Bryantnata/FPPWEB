<?php
session_start();
include "/laragon/www/FPPWEB/php/connect_db.php"; // Sesuaikan path

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'kasir') {
  header("Location: role.php");
  exit();
}

$sql = "SELECT b.ID_Service AS id_barang, p.nama, b.nama_barang, b.jenis_barang, b.tanggal_input, SUM(rk.total) AS total_harga 
        FROM barang b 
        JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
        JOIN detail_keluhan dk ON b.ID_Service = dk.ID_Service
        JOIN rincian_keluhan rk ON dk.id_keluhan = rk.id_keluhan
        WHERE b.status = 'Selesai Diperbaiki' AND b.lunas = 'Belum'";

// Penanganan form submission (filter pencarian)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
  $searchKeyword = $_POST['search'];
  $stmt = $link->prepare($sql . " AND (b.ID_Service LIKE ? OR p.nama LIKE ?) GROUP BY b.ID_Service, p.nama, b.nama_barang, b.jenis_barang");
  $searchKeyword = "%$searchKeyword%";
  $stmt->bind_param("ss", $searchKeyword, $searchKeyword);
} else {
  $stmt = $link->prepare($sql . " GROUP BY b.ID_Service, p.nama, b.nama_barang, b.jenis_barang");
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pembayaran - Kasir</title>
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
    </div>
    <!-- Sidebar Content -->
    <nav class="mt-4">
      <ul>
        <li>
          <a href="/html/kasir-Dashboard.php" class="block py-2 px-4 hover:bg-gray-700" id="dashboardBtn">Dashboard</a>
        </li>
        <li>
          <a href="/html/kasir-Transaksi.php" class="block py-2 px-4 hover:bg-gray-700" id="transaksiBtn">Transaksi</a>
        </li>
        <li>
          <a href="/html/kasir-Pembayaran.php" class="block py-2 px-4 text-gray-800 bg-gray-500" id="pembayaranBtn">Pembayaran</a>
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
  <!-- Content Area -->
  <div class="ml-64 p-8">
    <div class="container mx-auto py-8">
      <h1 class="text-3xl font-bold mb-4 text-center">Pembayaran</h1>
      <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4 flex justify-end">
          <input id="searchInput" type="text" class="w-1/4 px-3 py-2 border border-gray-300 rounded-md" placeholder="Cari berdasarkan nama pelanggan" />
        </div>
        <div class="overflow-x-auto">
          <table class="w-full border-collapse border border-gray-400">
            <thead>
              <tr class="bg-gray-200">
                <th class="px-4 py-2 border">No</th>
                <th class="px-4 py-2 border">Kode</th>
                <th class="px-4 py-2 border">Nama Pemilik</th>
                <th class="px-4 py-2 border">Tipe Barang</th>
                <th class="px-4 py-2 border">Tanggal Masuk</th>
                <th class="px-4 py-2 border">Total Harga</th>
                <th class="px-4 py-2 border">Aksi</th>
              </tr>
            </thead>
            <tbody id="pembayaranList">
              <?php
              if ($result->num_rows > 0) {
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                  echo "<tr class='divide-x divide-gray-400'>";
                  echo "<td class='px-4 py-2 border text-center'>" . $no++ . "</td>";
                  echo "<td class='px-4 py-2 border text-center'>" . $row["id_barang"] . "</td>";
                  echo "<td class='px-4 py-2 border '>" . $row["nama"] . "</td>";
                  echo "<td class='px-4 py-2 border '>" . $row["nama_barang"] . " (" . $row["jenis_barang"] . ")</td>";
                  echo "<td class='px-4 py-2 border text-center'>" . $row["tanggal_input"] . "</td>"; // Menampilkan tanggal masuk
                  echo "<td class='px-4 py-2 border text-right'>Rp " . number_format($row["total_harga"], 0, ',', '.') . "</td>";
                  echo "<td class='px-4 py-2 border text-center'><a href='detailPembayaran.php?id=" . $row["id_barang"] . "' class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'>Edit</a></td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='7' class='text-center px-4 py-2 border'>Tidak ada data pembayaran.</td></tr>"; // Perhatikan colspan='7'
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const searchInput = document.getElementById('searchInput');
      const pembayaranList = document.getElementById('pembayaranList');
      const rows = pembayaranList.getElementsByTagName('tr');

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