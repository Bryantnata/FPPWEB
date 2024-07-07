<?php
include '/laragon/www/FPPWEB/php/connect_db.php'; // Adjust path as needed
$from = isset($_GET['from']) ? $_GET['from'] : '';


$id_barang = $_GET['id'];

// Fetch barang, pelanggan, detail_keluhan, and user data
$sql_barang = "SELECT b.*, p.nama, p.alamat, dk.keterangan_akhir, u.nama AS teknisi_nama
               FROM barang b 
               JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
               LEFT JOIN detail_keluhan dk ON b.ID_Service = dk.ID_Service
               LEFT JOIN user u ON dk.id_user = u.id_user
               WHERE b.ID_Service = ?";
$stmt_barang = $link->prepare($sql_barang);
$stmt_barang->bind_param("i", $id_barang);
$stmt_barang->execute();
$result_barang = $stmt_barang->get_result();
$row_barang = $result_barang->fetch_assoc();

// Fetch keluhan data
$sql_keluhan = "SELECT rk.* 
               FROM rincian_keluhan rk
               JOIN detail_keluhan dk ON rk.id_keluhan = dk.id_keluhan
               WHERE dk.ID_Service = ?";
$stmt_keluhan = $link->prepare($sql_keluhan);
$stmt_keluhan->bind_param("i", $id_barang);
$stmt_keluhan->execute();
$result_keluhan = $stmt_keluhan->get_result();

// Calculate total
$total_harga = 0;
while ($row_keluhan = $result_keluhan->fetch_assoc()) {
  $total_harga += $row_keluhan['jumlah'] * $row_keluhan['harga'];
}
$result_keluhan->data_seek(0); // Reset result pointer

// Check if the payment is already marked as lunas
$is_lunas = $row_barang['status'] === 'Lunas';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nota</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="container mx-auto py-8 px-4">
    <div id="nota" class="bg-white rounded shadow-md p-6 mx-auto mb-8 w-full max-w-4xl">
      <h1 class="text-3xl font-bold text-center mb-6">Nota Pembayaran</h1>

      <div class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Informasi </h2>
        <table class="min-w-full divide-y divide-gray-200">
          <tbody>
            <tr>
              <td class="font-semibold">ID Service</td>
              <td class="font-semibold">:</td>
              <td><?php echo $row_barang['ID_Service']; ?></td>
            </tr>
            <tr>
              <td class="font-semibold">Nama Pemilik</td>
              <td class="font-semibold">:</td>
              <td><?php echo $row_barang['nama']; ?></td>
            </tr>
            <tr>
              <td class="font-semibold">Alamat:</td>
              <td class="font-semibold">:</td>
              <td><?php echo $row_barang['alamat']; ?></td>
            </tr>
            <tr>
              <td class="font-semibold">Nama Barang:</td>
              <td class="font-semibold">:</td>
              <td><?php echo $row_barang['nama_barang']; ?></td>
            </tr>
            <tr>
              <td class="font-semibold">Merk:</td>
              <td class="font-semibold">:</td>
              <td><?php echo $row_barang['merk_barang']; ?></td>
            </tr>
            <tr>
              <td class="font-semibold">Jenis:</td>
              <td class="font-semibold">:</td>
              <td><?php echo $row_barang['jenis_barang']; ?></td>
            </tr>
            <tr>
              <td class="font-semibold">Nama Teknisi:</td>
              <td class="font-semibold">:</td>
              <td><?php echo $row_barang['teknisi_nama'] ?? 'Belum ditentukan'; ?></td>
            </tr>
            <tr>
              <td class="font-semibold">Keterangan:</td>
              <td class="font-semibold">:</td>
              <td><?php echo $row_barang['keterangan_akhir']; ?></td>
            </tr>
            <tr>
              <td class="font-semibold">Tanggal Selesai:</td>
              <td class="font-semibold">:</td>
              <td>
                <?php
                if ($row_barang['status'] === 'Selesai Diperbaiki') {
                  echo date('d-m-Y', strtotime($row_barang['status_updated_at']));
                } else {
                  echo 'Belum selesai';
                }
                ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Rincian</h2>
        <table class="w-full table-auto border-collapse border border-gray-400">
          <thead>
            <tr class="bg-gray-200">
              <th class="px-4 py-2 border">Jumlah</th>
              <th class="px-4 py-2 border">Nama</th>
              <th class="px-4 py-2 border">Tipe</th>
              <th class="px-4 py-2 border">Harga</th>
              <th class="px-4 py-2 border">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row_keluhan = $result_keluhan->fetch_assoc()) { ?>
              <tr>
                <td class="border px-4 py-2"><?php echo $row_keluhan['jumlah']; ?></td>
                <td class="border px-4 py-2"><?php echo $row_keluhan['nama']; ?></td>
                <td class="border px-4 py-2"><?php echo $row_keluhan['tipe']; ?></td>
                <td class="border px-4 py-2">Rp <?php echo number_format($row_keluhan['harga'], 0, ',', '.'); ?></td>
                <td class="border px-4 py-2">Rp <?php echo number_format($row_keluhan['jumlah'] * $row_keluhan['harga'], 0, ',', '.'); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <div class="text-right mb-6">
        <p class="text-xl font-semibold">Total: Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></p>
      </div>
    </div>

    <div class="flex justify-center space-x-4">
      <button onclick="downloadPDF()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Unduh Nota
      </button>
      <button onclick="window.print()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        Cetak Nota
      </button>
      <?php if (!$is_lunas && $from !== 'riwayat') : ?>
        <button id="lunasBtn" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
          Lunas
        </button>
      <?php endif; ?>
      <a href="<?php echo $from === 'riwayat' ? '/html/kasir-Riwayat.php' : '/html/kasir-Pembayaran.php'; ?>" 
       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
        Kembali
    </a>
    </div>
  </div>

  <script>
    function downloadPDF() {
      const element = document.getElementById('nota');
      const filename = `<?php echo $row_barang['ID_Service'] . '_' . str_replace(' ', '_', $row_barang['nama']); ?>.pdf`;

      const opt = {
        margin: 10,
        filename: filename,
        image: {
          type: 'jpeg',
          quality: 0.98
        },
        html2canvas: {
          scale: 2
        },
        jsPDF: {
          unit: 'mm',
          format: 'a4',
          orientation: 'portrait'
        }
      };

      html2pdf().from(element).set(opt).save();
    }

    // Print only the nota content
    window.onbeforeprint = function() {
      document.body.innerHTML = document.getElementById('nota').outerHTML;
    };

    window.onafterprint = function() {
      location.reload();
    };

    <?php if (!$is_lunas && $from !== 'riwayat')  : ?>
      document.getElementById('lunasBtn').addEventListener('click', function() {
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
            const formData = new FormData();
            formData.append('id_barang', <?php echo $id_barang; ?>);

            fetch('/php/proses_lunas.php', {
                method: 'POST',
                body: formData
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Berhasil',
                    text: data.message,
                    icon: 'success'
                  }).then(() => {
                    window.location.href = '/html/kasir-Dashboard.php'; // Redirect to kasir dashboard
                  });
                } else {
                  Swal.fire('Gagal', data.message, 'error');
                }
              })
              .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat melakukan permintaan.', 'error');
              });
          }
        });
      });
    <?php endif; ?>
  </script>
</body>

</html>