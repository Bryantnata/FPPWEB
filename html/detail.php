<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

$serviceId = $_GET['id'];

// Ambil data barang, pelanggan, dan teknisi
$query = "SELECT b.*, p.nama AS nama_pemilik, p.alamat, u.nama AS nama_user 
          FROM barang b 
          JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan 
          LEFT JOIN user u ON b.id_user = u.id_user
          WHERE b.ID_Service = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "i", $serviceId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);


// HTML untuk halaman detail perbaikan
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Perbaikan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Detail Perbaikan</h1>
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
      <div class="flex py-2">
        <strong class="w-1/3 flex-none">Nama Pemilik:</strong>
        <span class="w-2/3 flex-grow"><?php echo $data['nama_pemilik']; ?></span>
      </div>
      <div class="flex py-2">
        <strong class="w-1/3 flex-none">Alamat:</strong>
        <span class="w-2/3 flex-grow"><?php echo $data['alamat']; ?></span>
      </div>
      <div class="flex py-2">
        <strong class="w-1/3 flex-none">Nama Barang:</strong>
        <span class="w-2/3 flex-grow"><?php echo $data['nama_barang']; ?></span>
      </div>
      <div class="flex py-2">
        <strong class="w-1/3 flex-none">Merk Barang:</strong>
        <span class="w-2/3 flex-grow"><?php echo $data['merk_barang']; ?></span>
      </div>
      <div class="flex py-2">
        <strong class="w-1/3 flex-none">Jenis Barang:</strong>
        <span class="w-2/3 flex-grow"><?php echo $data['jenis_barang']; ?></span>
      </div>
      <div class="flex py-2">
        <strong class="w-1/3 flex-none">Keluhan:</strong>
        <span class="w-2/3 flex-grow"><?php echo $data['keluhan_barang']; ?></span>
      </div>
    </div>
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
      <h2 class="text-xl font-bold mb-4">Keterangan Akhir</h2>
      <textarea id="keteranganAkhir" class="w-full p-2 border rounded" rows="4" placeholder="Masukkan keterangan akhir..."></textarea>
      <button onclick="generateTable()" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Rincian
      </button>
    </div>
    <div id="tableContainer" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 hidden">
      <!-- Tabel rincian akan ditampilkan di sini -->
    </div>
    <button onclick="selesaikanPerbaikan()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
      Selesaikan Perbaikan
    </button>
  </div>

  <script>
    let rincianItems = [];

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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="rincianTableBody">
            </tbody>
        </table>
        <div class="mt-4">
            <button onclick="tambahRincian()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                Tambah Rincian
            </button>
            <button onclick="batalGenerateTable()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Batal
            </button>
        </div>
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
        <td><input type="number" class="border rounded p-1 w-full" name="harga" step="0.01" value="0" oninput="hitungTotal(this)"></td>
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