function showInvoice(data) {
  const invoiceHtml = `
      <div class="font-sans max-w-3xl mx-auto p-6 bg-white shadow-lg rounded-lg">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-3xl font-bold text-gray-800">INVOICE</h1>
          <img src="/assets/logopweb.png" alt="Logo" class="h-12 w-auto">
        </div>
        
        <div class="grid grid-cols-1 gap-8 mb-8">
          <div class="bg-gray-100 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700 mb-2">Untuk:</h2>
            <p>${data.name}</p>
            <p>${data.address}</p>
            <p>${data.phone}</p>
          </div>
        </div>
        
        <table class="w-full mb-8">
          <tr class="bg-gray-200">
            <th class="text-left text-center p-2">Deskripsi</th>
            <th class="text-left text-center p-2">Detail</th>
          </tr>
          <tr>
            <td class="border-b p-2 font-semibold">Barang</td>
            <td class="border-b p-2">${data.itemName}</td>
          </tr>
          <tr>
            <td class="border-b p-2 font-semibold">Merk</td>
            <td class="border-b p-2">${data.brand}</td>
          </tr>
          <tr>
            <td class="border-b p-2 font-semibold">Tipe</td>
            <td class="border-b p-2">${data.type}</td>
          </tr>
          <tr>
            <td class="border-b p-2 font-semibold">Keluhan</td>
            <td class="border-b p-2">${data.complaint}</td>
          </tr>
        </table>
        
        <div class="text-center text-sm text-gray-600 mt-8">
          Invoice ini dibuat secara otomatis dan sah tanpa tanda tangan.
        </div>
      </div>
    `;

  Swal.fire({
    title: "Invoice",
    html: invoiceHtml,
    width: "50rem",
    showCancelButton: true,
    confirmButtonText: "Submit",
    cancelButtonText: "Batal",
    customClass: {
      container: "swal-custom-container",
      popup: "swal-custom-popup",
      header: "swal-custom-header",
      title: "swal-custom-title",
      closeButton: "swal-custom-close",
      content: "swal-custom-content",
      input: "swal-custom-input",
      actions: "swal-custom-actions",
      confirmButton: "swal-custom-confirm",
      cancelButton: "swal-custom-cancel",
      footer: "swal-custom-footer",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      submitAndPrintInvoice(data, invoiceHtml);
    }
  });
}

function submitAndPrintInvoice(data, invoiceHtml) {
  // Buat FormData object
  const formData = new FormData();

  // Tambahkan semua data ke FormData
  for (const [key, value] of Object.entries(data)) {
    formData.append(key, value);
  }

  // Submit to database
  fetch("../php/submit_laporan.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        // Print invoice
        const printWindow = window.open("", "_blank");
        printWindow.document.write(
          "<html>" +
            "<head>" +
            "<title>Invoice</title>" +
            '<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">' +
            "</head>" +
            '<body class="bg-gray-100 flex justify-center items-center min-h-screen">' +
            invoiceHtml +
            "<script>" +
            "window.onload = function() {" +
            "  window.print();" +
            "  window.onafterprint = function() {" +
            "    window.close();" +
            "    if (window.opener && !window.opener.closed) {" +
            "      window.opener.showSuccessMessage();" +
            "    }" +
            "  }" +
            "}" +
            "</script>" +
            "</body>" +
            "</html>"
        );
        printWindow.document.close();

        // Tambahkan event listener untuk mendeteksi ketika jendela cetak ditutup
        const checkClosedInterval = setInterval(function () {
          if (printWindow.closed) {
            clearInterval(checkClosedInterval);
            showSuccessMessage();
          }
        }, 1000);
      } else {
        Swal.fire({
          title: "Error!",
          text: result.message || "Terjadi kesalahan saat menyimpan laporan.",
          icon: "error",
        });
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire({
        title: "Error!",
        text: "Terjadi kesalahan: " + error.message,
        icon: "error",
      });
    });
}

function showSuccessMessage() {
  Swal.fire({
    title: "Berhasil!",
    text: "Laporan telah disubmit dan invoice telah dicetak.",
    icon: "success",
    customClass: {
      confirmButton:
        "bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded",
    },
  }).then(() => {
    window.location.href = "kasir-Dashboard.php";
  });
}
