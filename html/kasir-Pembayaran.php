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
    <!-- Jam -->
    <div id="clock" class="absolute bottom-1 left-1/2 transform -translate-x-1/2 -translate-y-1/2 font-bold lg:block text-center text-white"></div>
  </aside>
  <!-- Content Area -->
  <div class="ml-64 p-8">
    <div class="container mx-auto py-8">
      <h1 class="text-3xl font-bold mb-4 text-center">Pembayaran</h1>
      <div class="mt-8">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-400">
                <thead>
                    <tr class="divide-x divide-gray-400">
                        <th class="px-4 py-2">No</th>
                        <th class="px-4 py-2">Kode</th>
                        <th class="px-4 py-2">Nama Pemilik</th>
                        <th class="px-4 py-2">Tipe Barang</th>
                        <th class="px-4 py-2">Tanggal Masuk</th> <th class="px-4 py-2">Total Harga</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr class='divide-x divide-gray-400'>";
                            echo "<td class='border border-gray-400 px-4 py-2'>" . $no++ . "</td>";
                            echo "<td class='border border-gray-400 px-4 py-2'>" . $row["id_barang"] . "</td>";
                            echo "<td class='border border-gray-400 px-4 py-2'>" . $row["nama"] . "</td>";
                            echo "<td class='border border-gray-400 px-4 py-2'>" . $row["nama_barang"] . " (" . $row["jenis_barang"] . ")</td>";
                            echo "<td class='border border-gray-400 px-4 py-2'>" . $row["tanggal_input"] . "</td>"; // Menampilkan tanggal masuk
                            echo "<td class='border border-gray-400 px-4 py-2'>Rp " . number_format($row["total_harga"], 0, ',', '.') . "</td>";
                            echo "<td class='border border-gray-400 px-4 py-2'><a href='detailPembayaran.php?id=" . $row["id_barang"] . "' class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'>Edit</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>Tidak ada data pembayaran.</td></tr>"; // Perhatikan colspan='7'
                    }
                    ?>
                </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>

  <script>
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
</body>

</html>
