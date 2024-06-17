// Fungsi untuk memvalidasi form login
function validateForm() {
  var id = document.getElementById("id").value;
  var password = document.getElementById("password").value;

  var idError = document.getElementById("id-error");
  var passwordError = document.getElementById("password-error");
  var commonError = document.getElementById("common-error");

  idError.innerHTML = "";
  passwordError.innerHTML = "";
  commonError.innerHTML = "";

  if (id === "" && password === "") {
    commonError.innerHTML = "ID dan password belum diisi!";
    commonError.classList.remove("hidden");
    return false;
  }

  if (id === "") {
    idError.innerHTML = "Silakan masukkan ID anda!";
    return false;
  }

  if (password === "") {
    passwordError.innerHTML = "Silakan masukkan password anda!";
    return false;
  }

  return true;
}

// Fungsi untuk logout
const logoutButton = document.getElementById("logoutBtn");
if (logoutButton) {
  logoutButton.addEventListener("click", function (event) {
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
