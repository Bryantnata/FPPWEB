<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

// Pagination
$limit = 10; // Jumlah item per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Query untuk mengambil data dari barang_keluar
$query = "SELECT bk.id_barang_keluar, bk.id_service, bk.tanggal_keluar,
          b.tanggal_input AS tanggal_masuk, b.nama_barang, p.nama AS nama_pemilik, 
          dk.keterangan_akhir
          FROM barang_keluar bk
          JOIN barang b ON bk.id_service = b.ID_Service
          JOIN pelanggan p ON bk.id_pelanggan = p.id_pelanggan
          LEFT JOIN detail_keluhan dk ON bk.id_service = dk.ID_Service
          ORDER BY bk.tanggal_keluar DESC
          LIMIT $start, $limit";

$result = mysqli_query($link, $query);

// Count total rows for pagination
$total_query = "SELECT COUNT(*) as total FROM barang_keluar";
$total_result = mysqli_query($link, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $limit);

if (!$result) {
    die("Query error: " . mysqli_error($link));
}

$riwayat = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir-Riwayat</title>
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
                    <a href="/html/kasir-Transaksi.php" class="block py-2 px-4 hover:bg-gray-700" id="transaksiBtn">Transaksi</a>
                </li>
                <li>
                    <a href="/html/kasir-Pembayaran.php" class="block py-2 px-4 hover:bg-gray-700" id="pembayaranBtn">Pembayaran</a>
                </li>
                <li>
                    <a href="/html/kasir-Riwayat.php" class="block py-2 px-4 text-gray-800 bg-gray-500" id="riwayatBtn">Riwayat</a>
                </li>
            </ul>
        </nav>
        <!-- Logout Button -->
        <div class="absolute bottom-10 left-0 w-full font-bold lg:block">
            <a href="#" id="logoutBtn" class="block w-2/3 py-3 mx-auto text-sm text-white text-center bg-red-600 hover:bg-red-700 rounded-md z-10">Log Out</a>
    </aside>
    <!-- Content Area: Riwayat -->
    <div class="ml-64 p-8">
        <div class="container mx-auto py-8">
            <h1 class="text-3xl font-bold mb-8 text-center">Daftar Riwayat</h1>
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="mb-4 flex justify-end">
                    <input id="searchInput" type="text" class="w-1/4 px-3 py-2 border border-gray-300 rounded-md" placeholder="Cari berdasarkan nama pemilik atau ID Service" />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-400">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="px-4 py-2 border">No</th>
                                <th class="px-4 py-2 border">Tanggal Masuk</th>
                                <th class="px-4 py-2 border">Tanggal Keluar</th>
                                <th class="px-4 py-2 border">ID Service</th>
                                <th class="px-4 py-2 border">Nama Pemilik</th>
                                <th class="px-4 py-2 border">Nama Barang</th>
                                <th class="px-4 py-2 border">Keterangan Akhir</th>
                                <th class="px-4 py-2 border">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="riwayatList">
                            <?php foreach ($riwayat as $index => $item) : ?>
                                <tr>
                                    <td class="px-4 py-2 border"><?php echo $start + $index + 1; ?></td>
                                    <td class="px-4 py-2 border"><?php echo date('d-m-Y', strtotime($item['tanggal_masuk'])); ?></td>
                                    <td class="px-4 py-2 border"><?php echo date('d-m-Y', strtotime($item['tanggal_keluar'])); ?></td>
                                    <td class="px-4 py-2 border"><?php echo $item['id_service']; ?></td>
                                    <td class="px-4 py-2 border"><?php echo $item['nama_pemilik']; ?></td>
                                    <td class="px-4 py-2 border"><?php echo $item['nama_barang']; ?></td>
                                    <td class="px-4 py-2 border"><?php echo $item['keterangan_akhir']; ?></td>
                                    <td class="px-4 py-2 border">
                                        <a href="/html/nota.php?id=<?php echo $item['id_service']; ?>&from=riwayat" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="mt-4 flex justify-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <a href="?page=<?php echo $i; ?>" class="mx-1 px-3 py-2 bg-gray-500 text-white rounded hover:bg-blue-600"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById('searchInput');
            const riwayatList = document.getElementById('riwayatList');
            const rows = riwayatList.getElementsByTagName('tr');

            searchInput.addEventListener('keyup', function() {
                const searchTerm = searchInput.value.toLowerCase();

                for (let i = 0; i < rows.length; i++) {
                    const namaPemilik = rows[i].getElementsByTagName('td')[4].textContent.toLowerCase();
                    const idService = rows[i].getElementsByTagName('td')[3].textContent.toLowerCase();
                    if (namaPemilik.includes(searchTerm) || idService.includes(searchTerm)) {
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