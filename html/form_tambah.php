<!-- form_tambah.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex justify-center items-center h-screen bg-blue-950">
    <div class="flex flex-col justify-center items-center space-y-4">
        <form action="/php/admin_akun.php" method="post" class="bg-blue-900 opacity-80 p-8 rounded-lg shadow-lg w-96">
            <h1 class="text-white font-semibold text-center text-2xl mb-6">Add User</h1>
            <?php
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
                if ($error == 'invalid_role') {
                    echo "<p class='text-center text-red-500'>Invalid role selected.</p>";
                } else {
                    echo "<p class='text-center text-red-500'>Error: " . htmlspecialchars($error) . "</p>";
                }
            }
            ?>
            <input type="text" name="name" placeholder="Name" class="block w-full rounded-md px-4 py-3 mb-4 bg-gray-100 focus:outline-none focus:bg-gray-200" required>
            <input type="text" name="username" placeholder="Username" class="block w-full rounded-md px-4 py-3 mb-4 bg-gray-100 focus:outline-none focus:bg-gray-200" required>
            <input type="text" name="no_hp" placeholder="No HP" class="block w-full rounded-md px-4 py-3 mb-4 bg-gray-100 focus:outline-none focus:bg-gray-200" required>
            <select name="role" class="block w-full rounded-md px-4 py-3 mb-4 bg-gray-100 focus:outline-none focus:bg-gray-200" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="kasir">Kasir</option>
                <option value="teknisi">Teknisi</option>
            </select>
            <input type="password" name="password" placeholder="Password" class="block w-full rounded-md px-4 py-3 mb-4 bg-gray-100 focus:outline-none focus:bg-gray-200" required>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold w-full py-3 rounded focus:outline-none focus:shadow-outline">Add User</button>
        </form>
    </div>
</body>

</html>