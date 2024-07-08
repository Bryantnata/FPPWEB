<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

$serviceId = $_GET['id'];

// Ambil data barang, pelanggan, dan detail keluhan
$query = "SELECT b.*, p.nama AS nama_pemilik, p.alamat, p.no_hp, dk.keterangan_akhir, dk.keterangan_awal, dk.kondisi, u.nama AS nama_teknisi
          FROM barang b
          JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan 
          LEFT JOIN detail_keluhan dk ON b.ID_Service = dk.ID_Service
          LEFT JOIN user u ON dk.id_user = u.id_user
          WHERE b.ID_Service = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "i", $serviceId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Tutup statement
mysqli_stmt_close($stmt);

// Ambil data rincian keluhan jika ada
$query_rincian = "SELECT rk.* 
                  FROM rincian_keluhan rk
                  JOIN detail_keluhan dk ON rk.id_keluhan = dk.id_keluhan
                  WHERE dk.ID_Service = ?";
$stmt_rincian = mysqli_prepare($link, $query_rincian);
mysqli_stmt_bind_param($stmt_rincian, "i", $serviceId);
mysqli_stmt_execute($stmt_rincian);
$result_rincian = mysqli_stmt_get_result($stmt_rincian);

$rincian_items = [];
while ($row = mysqli_fetch_assoc($result_rincian)) {
  $rincian_items[] = $row;
}

// Tutup statement
mysqli_stmt_close($stmt_rincian);

// Tutup koneksi database
mysqli_close($link);

?>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Perbaikan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
  <div class="container mx-auto p-4 max-w-4xl">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Detail Perbaikan</h1>
    </div>
    <div class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-6">
      <table class="w-full">
        <tr>
          <td class="font-semibold py-2">Nama Pemilik</td>
          <td>:</td>
          <td><?php echo $data['nama_pemilik']; ?></td>
        </tr>
        <tr>
          <td class="font-semibold py-2">Alamat</td>
          <td>:</td>
          <td><?php echo $data['alamat']; ?></td>
        </tr>
        <tr>
          <td class="font-semibold py-2">No. HP</td>
          <td>:</td>
          <td><?php echo $data['no_hp']; ?></td>
        </tr>
        <tr>
          <td class="font-semibold py-2">Nama Barang</td>
          <td>:</td>
          <td><?php echo $data['nama_barang']; ?></td>
        </tr>
        <tr>
          <td class="font-semibold py-2">Merk Barang</td>
          <td>:</td>
          <td><?php echo $data['merk_barang']; ?></td>
        </tr>
        <tr>
          <td class="font-semibold py-2">Jenis Barang</td>
          <td>:</td>
          <td><?php echo $data['jenis_barang']; ?></td>
        </tr>
        <tr>
          <td class="font-semibold py-2">Keluhan</td>
          <td>:</td>
          <td><?php echo $data['keluhan_barang']; ?></td>
        </tr>
        <tr>
          <td class="font-semibold py-2">Diagnosa Awal</td>
          <td>:</td>
          <td><?php echo $data['keterangan_awal']; ?></td>
        </tr>
        <tr>
          <td class="font-semibold py-2">Nama Teknisi</td>
          <td >:</td>
          <td><?php echo $data['nama_teknisi'] ?? $_SESSION['nama']; ?></td>
        </tr>
      </table>
    </div>

    <div class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-6">
      <h2 class="text-2xl font-bold mb-4 text-gray-800">Penanganan</h2>
      <textarea id="keteranganAkhir" class="w-full p-2 border rounded resize-none" rows="4" placeholder="Masukkan keterangan akhir..."></textarea>
      <button onclick="generateTable()" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
        Rincian
      </button>
    </div>

    <div id="tableContainer" class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-6 hidden">
      <!-- Tabel rincian akan ditampilkan di sini -->
    </div>

    <div class="flex justify-between">
      <button onclick="kembali()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-300">
        kembali
      </button>
      <button onclick="selesaikanPerbaikan()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300">
        Selesaikan Perbaikan
      </button>
    </div>
  </div>

  <script>
    let rincianItems = [];

    function kembali() {
      const keteranganAkhir = document.getElementById('keteranganAkhir').value.trim();
      window.location.href = 'teknisi-Dashboard.php';
    }
    function generateTable() {
      const keteranganAkhir = document.getElementById('keteranganAkhir').value.trim();

      if (!keteranganAkhir) {
        Swal.fire({
          title: 'Error',
          text: 'Harap isi keterangan akhir terlebih dahulu',
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return;
      }

      const tableContainer = document.getElementById('tableContainer');
      tableContainer.innerHTML = `
        <table class="w-full">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Jumlah</th>
                    <th>Tipe</th>
                    <th>Harga</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody id="rincianTableBody">
            </tbody>
        </table>
        <button onclick="tambahRincian()" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Tambah Rincian
        </button>
    `;
      tableContainer.classList.remove('hidden');
      tambahRincian();
    }

    function tambahRincian() {
      const tbody = document.getElementById('rincianTableBody');
      const rowCount = tbody.childElementCount;
      const newRow = tbody.insertRow();
      newRow.innerHTML = `
        <td>${rowCount + 1}</td>
        <td><input type="text" class="border rounded p-1 w-full" name="nama"></td>
        <td><input type="number" class="border rounded p-1 w-full" name="jumlah" min="1" value="1" oninput="hitungTotal(this)"></td>
        <td><input type="text" class="border rounded p-1 w-full" name="tipe"></td>
        <td><input type="number" class="border rounded p-1 w-full" name="harga" step="0.01" value="0" readonly oninput="hitungTotal(this)"></td>
        <td><input type="number" class="border rounded p-1 w-full" name="total" step="0.01" readonly></td>
        <td><button onclick="hapusBaris(this)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Hapus</button></td>
    `;
      rincianItems.push({
        nama: '',
        jumlah: 1,
        tipe: '',
        harga: 0,
        total: 0
      });
      hitungTotal(newRow.querySelector('input[name="jumlah"]'));
    }

    function hitungTotal(input) {
      const row = input.closest('tr');
      const jumlah = parseFloat(row.querySelector('input[name="jumlah"]').value) || 0;
      const harga = parseFloat(row.querySelector('input[name="harga"]').value) || 0;
      const total = jumlah * harga;
      row.querySelector('input[name="total"]').value = total.toFixed(2);
    }

    function selesaikanPerbaikan() {
      const keteranganAkhir = document.getElementById('keteranganAkhir').value.trim();
      if (!keteranganAkhir) {
        Swal.fire({
          title: 'Error',
          text: 'Harap isi keterangan akhir terlebih dahulu',
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return;
      }

      const serviceId = <?php echo json_encode($serviceId); ?>;

      // Kumpulkan data dari tabel rincian
      const rows = document.querySelectorAll('#rincianTableBody tr');
      const rincianItems = Array.from(rows).map((row, index) => {
        return {
          nama: row.querySelector('input[name="nama"]').value,
          jumlah: parseInt(row.querySelector('input[name="jumlah"]').value),
          tipe: row.querySelector('input[name="tipe"]').value,
          harga: parseFloat(row.querySelector('input[name="harga"]').value) || 0
        };
      });

      if (rincianItems.length === 0) {
        Swal.fire({
          title: 'Error',
          text: 'Harap tambahkan setidaknya satu rincian perbaikan',
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return;
      }

      // Kirim data ke server
      fetch('/php/selesaikan_perbaikan.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            serviceId: serviceId,
            keteranganAkhir: keteranganAkhir,
            rincianItems: rincianItems
          }),
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            Swal.fire('Sukses', 'Perbaikan telah diselesaikan', 'success')
              .then(() => {
                window.location.href = 'teknisi-Dashboard.php';
              });
          } else {
            throw new Error(data.error || 'Gagal menyelesaikan perbaikan');
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          Swal.fire('Error', `Terjadi kesalahan: ${error.message}`, 'error');
        });
    }

    function hapusBaris(button) {
      const row = button.closest('tr');
      const tbody = row.parentNode;
      tbody.removeChild(row);

      // Perbarui nomor urut
      const rows = tbody.querySelectorAll('tr');
      rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
      });

      // Perbarui array rincianItems
      rincianItems.splice(row.rowIndex - 1, 1);
    }

    function batalGenerateTable() {
      const tableContainer = document.getElementById('tableContainer');
      tableContainer.innerHTML = '';
      tableContainer.classList.add('hidden');
      rincianItems = []; // Reset array rincianItems
    }
  </script>
</body>

</html>