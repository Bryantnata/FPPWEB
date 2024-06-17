<!-- role.php -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Role</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex justify-center items-center h-screen bg-blue-950">
  <div class="flex flex-col justify-center items-center space-y-4">
    <div class="p-6 sm:p-9 bg-blue-900 rounded-lg shadow-lg text-center">
      <img src="/assets/logopweb.png" alt="logo" class="max-w-60 w-full mx-auto mb-4" />
      <button id="adminBtn" onclick="selectRole('admin')" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 sm:py-4 sm:px-8 rounded focus:outline-none focus:shadow-outline my-2">
        Admin
      </button>
      <button id="kasirBtn" onclick="selectRole('kasir')" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-6 sm:py-4 sm:px-8 rounded focus:outline-none focus:shadow-outline my-2">
        Kasir
      </button>
      <button id="teknisiBtn" onclick="selectRole('teknisi')" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 sm:py-4 sm:px-8 rounded focus:outline-none focus:shadow-outline my-2">
        Teknisi
      </button>
    </div>
  </div>
  <script>
    function selectRole(role) {
      window.location.href = `login.php?role=${role}`;
    }
  </script>
</body>

</html>