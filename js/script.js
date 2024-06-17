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


