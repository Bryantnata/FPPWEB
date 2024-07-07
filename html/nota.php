<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

$id = $_GET['id'];

// Ambil data barang, pelanggan, dan rincian keluhan
$query = "SELECT b.*, p.nama AS nama_pemilik, p.alamat, dk.keterangan_akhir, 
          rk.nama AS nama_item, rk.jumlah, rk.tipe, rk.harga, rk.total, u.nama AS nama_user
          FROM barang b 
          JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan 
          LEFT JOIN detail_keluhan dk ON b.ID_Service = dk.ID_Service
          LEFT JOIN rincian_keluhan rk ON dk.id_keluhan = rk.id_keluhan
          LEFT JOIN user u ON dk.id_user = u.id_user
          WHERE b.ID_Service = ?";

$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$barangData = mysqli_fetch_assoc($result);
$rincianItems = [];

while ($row = mysqli_fetch_assoc($result)) {
  $rincianItems[] = [
    'nama' => $row['nama_item'],
    'jumlah' => $row['jumlah'],
    'tipe' => $row['tipe'],
    'harga' => $row['harga'],
    'total' => $row['total']
  ];
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nota</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="container mx-auto py-8 px-4 md:px-8 lg:px-16">
    <h1 class="text-3xl font-bold text-center mb-8">Nota</h1>

    <div class="bg-white rounded shadow-md p-6 mx-auto mb-8 w-full max-w-6xl">
      <table class="table-auto w-full mb-6">
        <tbody>
          <tr>
            <td class="font-semibold pr-4">Nama Pemilik</td>
            <td class="font-semibold">:</td>
            <td><?php echo $barangData['nama_pemilik']; ?></td>
          </tr>
          <tr>
            <td class="font-semibold pr-4">Alamat</td>
            <td class="font-semibold">:</td>
            <td><?php echo $barangData['alamat']; ?></td>
          </tr>
          <tr>
            <td class="font-semibold pr-4">Nama Barang</td>
            <td class="font-semibold">:</td>
            <td><?php echo $barangData['nama_barang']; ?></td>
          </tr>
          <tr>
            <td class="font-semibold pr-4">Merk</td>
            <td class="font-semibold">:</td>
            <td><?php echo $barangData['merk_barang']; ?></td>
          </tr>
          <tr>
            <td class="font-semibold pr-4">Jenis Barang</td>
            <td class="font-semibold">:</td>
            <td><?php echo $barangData['jenis_barang']; ?></td>
          </tr>
          <tr>
            <td class="font-semibold pr-4">Keluhan</td>
            <td class="font-semibold">:</td>
            <td><?php echo $barangData['keluhan_barang']; ?></td>
          </tr>
          <tr>
            <td class="font-semibold pr-4">Keterangan</td>
            <td class="font-semibold">:</td>
            <td><?php echo $barangData['keterangan_akhir']; ?></td>
          </tr>
          <tr>
            <td class="font-semibold pr-4">Nama Teknisi</td>
            <td class="font-semibold">:</td>
            <td><?php echo $barangData['nama_user']; ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="bg-white rounded shadow-md p-6 mx-auto w-full max-w-6xl">
      <h2 class="text-xl font-bold mb-4">Rincian</h2>
      <div id="tableContainer" class="w-full">
        <table class="w-full table-auto border-collapse border border-gray-400">
          <thead>
            <tr class="bg-gray-200">
              <th class="px-4 py-2 border">No.</th>
              <th class="px-4 py-2 border">Nama</th>
              <th class="px-4 py-2 border">Jumlah</th>
              <th class="px-4 py-2 border">Tipe</th>
              <th class="px-4 py-2 border">Harga</th>
              <th class="px-4 py-2 border">Total</th>
            </tr>
          </thead>
          <tbody id="rincianTableBody">
            <?php foreach ($rincianItems as $index => $item) : ?>
              <tr>
                <td class="border px-4 py-2"><?php echo $index + 1; ?></td>
                <td class="border px-4 py-2"><?php echo $item['nama_item']; ?></td>
                <td class="border px-4 py-2"><?php echo $item['jumlah']; ?></td>
                <td class="border px-4 py-2"><?php echo $item['tipe']; ?></td>
                <td class="border px-4 py-2">
                  <input type="number" class="w-full p-1 border rounded" name="harga" value="<?php echo $item['harga']; ?>" onchange="updateTotal(this)">
                </td>
                <td class="border px-4 py-2" name="total"><?php echo $item['total']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="mt-4 text-right">
        <span class="font-semibold">Total Harga: Rp<span id="totalHarga">0</span></span>
      </div>
      <div class="mt-4 text-center">
        <button onclick="saveNota()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Simpan Nota
        </button>
      </div>
    </div>
  </div>

  <script>
    function updateTotal(input) {
      const row = input.closest('tr');
      const jumlah = parseInt(row.cells[2].textContent);
      const harga = parseFloat(input.value) || 0;
      const total = jumlah * harga;
      row.querySelector('[name="total"]').textContent = total.toFixed(2);

      calculateTotalHarga();
    }

    function calculateTotalHarga() {
      const totals = Array.from(document.querySelectorAll('[name="total"]'))
        .map(el => parseFloat(el.textContent) || 0);
      const totalHarga = totals.reduce((sum, current) => sum + current, 0);
      document.getElementById("totalHarga").textContent = totalHarga.toFixed(2);
    }

    function saveNota() {
      const rincianItems = Array.from(document.querySelectorAll('#rincianTableBody tr')).map(row => ({
        nama: row.cells[1].textContent,
        jumlah: parseInt(row.cells[2].textContent),
        tipe: row.cells[3].textContent,
        harga: parseFloat(row.querySelector('[name="harga"]').value) || 0,
        total: parseFloat(row.querySelector('[name="total"]').textContent) || 0
      }));

      fetch('/php/save_nota.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            id: <?php echo $id; ?>,
            rincianItems: rincianItems,
            totalHarga: parseFloat(document.getElementById("totalHarga").textContent)
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Sukses', 'Nota berhasil disimpan', 'success').then(() => {
              window.location.href = 'kasir-Dashboard.php';
            });
          } else {
            Swal.fire('Error', 'Gagal menyimpan nota', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire('Error', 'Terjadi kesalahan', 'error');
        });
    }

    // Inisialisasi total harga saat halaman dimuat
    calculateTotalHarga();
  </script>
</body>

</html>