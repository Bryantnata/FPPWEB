<?php
include '/laragon/www/FPPWEB/php/connect_db.php'; // Sesuaikan path

$id_barang = $_GET['id'];

$sql_barang = "SELECT b.*, p.nama, p.alamat 
               FROM barang b 
               JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
               WHERE b.ID_Service = ?";
$stmt_barang = $link->prepare($sql_barang);
$stmt_barang->bind_param("i", $id_barang);
$stmt_barang->execute();
$result_barang = $stmt_barang->get_result();

if ($result_barang->num_rows > 0) {
    $row_barang = $result_barang->fetch_assoc();

    $sql_keluhan = "SELECT rk.*, dk.keterangan_akhir, dk.ID_Service AS id_barang_det 
                   FROM rincian_keluhan rk
                   JOIN detail_keluhan dk ON rk.id_keluhan = dk.id_keluhan
                   WHERE dk.ID_Service = ?";
    $stmt_keluhan = $link->prepare($sql_keluhan);
    $stmt_keluhan->bind_param("i", $id_barang);
    $stmt_keluhan->execute();
    $result_keluhan = $stmt_keluhan->get_result();

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detail Pembayaran</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    </head>

    <body class="bg-gray-100 text-gray-800">
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
                <div class="flex items-center justify-between mb-8">
                    <a href="/html/kasir-Pembayaran.php" class="text-red-500 hover:text-red-700 font-semibold focus:outline-none">
                        Kembali
                    </a>
                    <div class="flex-grow">
                        <h1 class="text-3xl font-bold text-center">Detail Pembayaran</h1>
                    </div>
                </div>

                <div class="bg-white rounded shadow-md p-6 mx-auto mb-8 w-full max-w-6xl">

                    <table class="table-auto w-full">
                        <tbody>
                            <tr>
                                <td class="font-semibold pr-4">Nama Pemilik</td>
                                <td class="font-semibold">:</td>
                                <td><?php echo $row_barang["nama"]; ?></td>
                            </tr>
                            <tr>
                                <td class="font-semibold pr-4">Alamat</td>
                                <td class="font-semibold">:</td>
                                <td><?php echo $row_barang["alamat"]; ?></td>
                            </tr>
                            <tr>
                                <td class="font-semibold pr-4">Nama Barang</td>
                                <td class="font-semibold">:</td>
                                <td><?php echo $row_barang["nama_barang"]; ?></td>
                            </tr>
                            <tr>
                                <td class="font-semibold pr-4">Merk</td>
                                <td class="font-semibold">:</td>
                                <td><?php echo $row_barang["merk_barang"]; ?></td>
                            </tr>
                            <tr>
                                <td class="font-semibold pr-4">Tipe</td>
                                <td class="font-semibold">:</td>
                                <td><?php echo $row_barang["jenis_barang"]; ?></td>
                            </tr>
                            <tr>
                                <td class="font-semibold pr-4">Keluhan Barang</td>
                                <td class="font-semibold">:</td>
                                <td><?php echo $row_barang["keluhan_barang"]; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded shadow-md p-6 mx-auto w-full max-w-6xl">
                    <h2 class="text-2xl font-bold mb-4">Detail Perbaikan</h2>

                    <?php if ($result_keluhan->num_rows > 0) { ?>
                        <!-- Display dk.keterangan_akhir here -->
                        <div class="mt-4">
                            <?php
                            // Fetch the first row of $result_keluhan to get keterangan_akhir
                            $row_keluhan = $result_keluhan->fetch_assoc();
                            ?>
                        </div>
                        <div id="tableContainer" class="w-full mt-4">
                            <form id="updateHargaForm" method="POST">
                                <table class="w-full table-auto border-collapse border border-gray-400">
                                    <thead>
                                        <tr class="divide-x divide-gray-400">
                                            <th class="px-4 py-2">Jumlah</th>
                                            <th class="px-4 py-2">Nama</th>
                                            <th class="px-4 py-2">Tipe</th>
                                            <th class="px-4 py-2">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Reset result set pointer
                                        $result_keluhan->data_seek(0);

                                        // Initialize total harga
                                        $total_harga = 0;
                                        while ($row_keluhan = $result_keluhan->fetch_assoc()) {
                                            echo "<tr class='divide-x divide-gray-400'>";
                                            echo "<td class='border border-gray-400 px-4 py-2'>" . $row_keluhan["jumlah"] . "</td>";
                                            echo "<td class='border border-gray-400 px-4 py-2'>" . $row_keluhan["nama"] . "</td>";
                                            echo "<td class='border border-gray-400 px-4 py-2'>" . $row_keluhan["tipe"] . "</td>";
                                            echo "<td class='border border-gray-400 px-4 py-2'>";
                                            echo "<input type='number' name='harga_" . $row_keluhan["id_rincian"] . "' value='" . $row_keluhan["harga"] . "' class='w-full px-2 py-1 border border-gray-300 rounded'>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <div class="flex justify-start mt-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-4">Simpan</button>
                                <a href="nota.php?id=<?php echo $id_barang; ?>" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Lihat Nota</a>
                            </div>
                            </form>
                            <div class="mt-4 text-right">
                                <?php
                                // Hitung total harga
                                $total_harga = 0;
                                $result_keluhan->data_seek(0); // Reset result set pointer

                                while ($row_keluhan = $result_keluhan->fetch_assoc()) {
                                    $subtotal = $row_keluhan["jumlah"] * $row_keluhan["harga"];
                                    $total_harga += $subtotal;
                                }
                                ?>
                                <span class="font-semibold">Total Harga: Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    <?php } else { ?>
                        <p class="text-center">Detail keluhan tidak ditemukan.</p>
                    <?php } ?>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('updateHargaForm').addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(this);

                fetch('/php/update_harga.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil', 'Harga berhasil diupdate', 'success');
                            // Anda mungkin ingin memperbarui total harga di halaman ini secara dinamis
                        } else {
                            Swal.fire('Error', 'Terjadi kesalahan saat mengupdate harga', 'error');
                        }
                    });
            });

            const logoutButton = document.getElementById('logoutBtn');
            if (logoutButton) {
                logoutButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Apakah kamu yakin ingin keluar?',
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
            }

            document.getElementById('lunasBtn').addEventListener('click', function(event) {
                event.preventDefault();

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin menandai barang ini sebagai lunas?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, lunas',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Get id_barang (assuming it's defined earlier in your script)
                        const id_barang = <?php echo $id_barang; ?>;

                        // Prepare FormData with id_barang
                        const formData = new FormData();
                        formData.append('id_barang', id_barang);

                        // Make fetch request to proses_lunas.php
                        fetch('/php/proses_lunas.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data); // Log response for debugging

                                // Check if request was successful
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Berhasil',
                                        text: data.message,
                                        icon: 'success'
                                    }).then(() => {
                                        window.location.href = '/html/kasir-Pembayaran.php';
                                    });
                                } else {
                                    Swal.fire('Gagal', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching data:', error);
                                Swal.fire('Error', 'Terjadi kesalahan saat melakukan permintaan.', 'error');
                            });
                    }
                });
            });
        </script>
    </body>

    </html>

<?php
} else {
    echo "Barang tidak ditemukan.";
}

// Tutup koneksi dan statement
$stmt_barang->close();
$stmt_keluhan->close();
$link->close();
?>