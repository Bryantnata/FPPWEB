<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .select2-container--default .select2-selection--single {
      background-color: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 0.25rem;
      height: 38px;
      padding: 0;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #4a5568;
      line-height: 38px;
      padding-left: 0.75rem;
      padding-right: 20px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 36px;
      position: absolute;
      top: 1px;
      right: 1px;
      width: 20px;
    }

    .select2-dropdown {
      border: 1px solid #e2e8f0;
      border-radius: 0.25rem;
    }

    .select2-results__option {
      padding: 0.5rem 0.75rem;
      font-size: 0.875rem;
      line-height: 1.25rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #4299e1;
    }

    .select2-search--dropdown .select2-search__field {
      border: 1px solid #e2e8f0;
      border-radius: 0.25rem;
      padding: 0.25rem 0.5rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
      color: #a0aec0;
    }
  </style>
</head>

<body class="bg-white text-black">
  <div class="container mx-auto py-8">
    <button onclick="goToDashboard()" type="button" class="text-red-500 hover:text-red-700 font-semibold focus:outline-none">
      Kembali
    </button>
    <h1 class="text-3xl font-bold mb-4 text-center">Laporan</h1>
    <div class="flex justify-between mb-4"></div>
    <form id="reportForm" class="bg-gray-100 shadow-md rounded px-8 pt-6 pb-8 mb-4">
      <!-- Kontainer untuk identitas pemilik -->
      <div class="bg-white rounded p-4 mb-4">
        <h2 class="text-xl font-bold mb-2">Identitas Pemilik</h2>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="customerSearch">Cari Pelanggan:</label>
          <select id="customerSearch" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="customerSearch">
            <option value="">-- Pilih Pelanggan --</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Nama:</label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" type="text" placeholder="Masukkan nama pemilik" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="address">Alamat:</label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="address" name="address" type="text" placeholder="Masukkan alamat pemilik" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Nomor HP:</label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" name="phone" type="text" placeholder="Masukkan nomor HP pemilik" />
        </div>
      </div>
      <!-- Kontainer untuk identitas barang -->
      <div class="bg-white rounded p-4 mb-4">
        <h2 class="text-xl font-bold mb-2">Identitas Barang</h2>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="itemName">Nama Barang:</label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="itemName" name="itemName" type="text" placeholder="Masukkan nama barang" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="brand">Merk:</label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="brand" name="brand" type="text" placeholder="Masukkan merk barang" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="type">Tipe:</label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="type" name="type" type="text" placeholder="Masukkan tipe barang" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="complaint">Keluhan Barang:</label>
          <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="complaint" name="complaint" placeholder="Masukkan keluhan barang"></textarea>
        </div>
      </div>
      <div class="flex items-center justify-end">
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="button" onclick="submitReport()">
          Submit
        </button>
      </div>
    </form>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  let selectedCustomerId = null;

  $(document).ready(function() {
    $('#customerSearch').select2({
      ajax: {
        url: '/php/search_customer.php',
        dataType: 'json',
        delay: 250,
        data: function(params) {
          return {
            q: params.term
          };
        },
        processResults: function(data) {
          return {
            results: data
          };
        },
        cache: true
      },
      minimumInputLength: 1,
      placeholder: 'Cari nama atau nomor telepon pelanggan',
      templateResult: formatCustomer,
      templateSelection: formatCustomerSelection
    });

    function formatCustomer(customer) {
      if (!customer.id) {
        return customer.text;
      }
      return $(`
    <div class="flex items-center py-1">
      <div>
        <p class="text-sm font-semibold">${customer.name}</p>
        <p class="text-xs text-gray-600">${customer.phone}</p>
      </div>
    </div>
    `);
    }

    function formatCustomerSelection(customer) {
      if (!customer.id) {
        return customer.text;
      }
      return $(`<span class="text-sm">${customer.name} (${customer.phone})</span>`);
    }

    $('#customerSearch').on('select2:select', function(e) {
      var data = e.params.data;
      $('#name').val(data.name);
      $('#address').val(data.address);
      $('#phone').val(data.phone);
      selectedCustomerId = data.id;
    });

    $('#customerSearch').on('select2:clear', function(e) {
      clearCustomerForm();
    });

    // Tambahkan event listener untuk input manual
    $('#name, #address, #phone').on('input', function() {
      if (selectedCustomerId) {
        selectedCustomerId = null;
        $('#customerSearch').val(null).trigger('change');
      }
    });
  });

  function clearCustomerForm() {
    $('#name').val('');
    $('#address').val('');
    $('#phone').val('');
    selectedCustomerId = null;
  }

  function submitReport() {
    const form = document.getElementById('reportForm');
    const name = form.name.value.trim();
    const address = form.address.value.trim();
    const phone = form.phone.value.trim();
    const itemName = form.itemName.value.trim();
    const brand = form.brand.value.trim();
    const type = form.type.value.trim();
    const complaint = form.complaint.value.trim();

    if (!name || !address || !phone || !itemName || !brand || !type || !complaint) {
      Swal.fire({
        title: "Error!",
        text: "Mohon lengkapi semua data.",
        icon: "error",
      });
      return;
    }

    if (!/^\d+$/.test(phone)) {
      Swal.fire({
        title: "Error!",
        text: "Nomor HP harus berupa angka.",
        icon: "error",
      });
      return;
    }

    const formData = new FormData(form);

    if (selectedCustomerId) {
      formData.append('id_pelanggan', selectedCustomerId);
    } else {
      formData.append('new_customer', 'true');
    }

    fetch('../php/submit_laporan.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            title: "Success!",
            text: data.message,
            icon: "success",
          }).then(() => {
            window.location.href = "kasir-Dashboard.php";
          });
        } else {
          Swal.fire({
            title: "Error!",
            text: data.message,
            icon: "error",
          });
        }
      })
      .catch(error => {
        Swal.fire({
          title: "Error!",
          text: "Terjadi kesalahan: " + error.message,
          icon: "error",
        });
      });
  }

  function goToDashboard() {
    window.location.href = 'kasir-Dashboard.php';
  }
</script>

</html>