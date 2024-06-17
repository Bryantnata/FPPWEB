<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex justify-center items-center h-screen bg-blue-950">
  <div class="flex flex-col justify-center items-center space-y-4">
    <form name="loginForm" onsubmit="return validateForm()" action="/php/autentikasi.php" method="post" class="bg-blue-900 opacity-80 p-8 rounded-lg shadow-lg w-96">
      <button onclick="goRole()" type="button" class="text-white hover:text-red-500 font-semibold focus:outline-none">
        Kembali
      </button>
      <img src="/assets/logopweb.png" alt="logo" class="w-20 mx-auto mb-4" />
      <h1 class="text-white font-semibold text-center text-2xl mb-6">
        Login
      </h1>
      <input type="hidden" name="role" value="<?php echo isset($_GET['role']) ? htmlspecialchars($_GET['role']) : ''; ?>">
      <?php if (isset($_GET['error'])) : ?>
        <div id="common-error" class="text-red-500 mb-4">
          <?php echo htmlspecialchars($_GET['error'] ?? ''); ?>
        </div>
      <?php endif; ?>
      <div id="id-error" class="text-red-500 mb-2"></div>
      <input type="text" id="id" name="username" class="block w-full rounded-md px-4 py-3 mb-4 bg-gray-100 focus:outline-none focus:bg-gray-200" placeholder="ID" />
      <div id="password-error" class="text-red-500 mb-2"></div>
      <input type="password" id="password" name="password" class="block w-full rounded-md px-4 py-3 mb-4 bg-gray-100 focus:outline-none focus:bg-gray-200" placeholder="Password" />
      <button type="submit" value="Login" class="bg-blue-600 hover:bg-blue-700 text-white font-bold w-full py-3 rounded focus:outline-none focus:shadow-outline">
        Login
      </button>
    </form>
  </div>
  <script>
    function goRole() {
      window.location.href = 'role.php'; // Ganti dengan halaman yang sesuai
    }

    function validateForm() {
      let isValid = true;
      const username = document.getElementById('id').value;
      const password = document.getElementById('password').value;
      document.getElementById('common-error').classList.add('hidden');
      document.getElementById('id-error').textContent = '';
      document.getElementById('password-error').textContent = '';

      if (username === '') {
        document.getElementById('id-error').textContent = 'ID tidak boleh kosong';
        isValid = false;
      }
      if (password === '') {
        document.getElementById('password-error').textContent = 'Password tidak boleh kosong';
        isValid = false;
      }
      if (!isValid) {
        document.getElementById('common-error').textContent = 'Ada kesalahan dalam formulir Anda';
        document.getElementById('common-error').classList.remove('hidden');
      }
      return isValid;
    }
  </script>
</body>

</html>