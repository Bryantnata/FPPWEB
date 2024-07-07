<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

// Query untuk mengambil data akun kasir dan teknisi
$query = "SELECT id_user, nama, no_hp, role FROM user WHERE role IN ('kasir', 'teknisi') ORDER BY role, nama";
$result = mysqli_query($link, $query);

if (!$result) {
    die("Query error: " . mysqli_error($link));
}

$accounts = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
    <!-- Sidebar -->
    <aside class="sidebar bg-gray-800 text-gray-400 w-64 min-h-screen fixed top-0 left-0 z-50">
        <!-- Logo -->
        <div class="flex items-center justify-center h-20 mt-4 mb-4">
            <img src="/assets/logopweb.png" alt="Logo" class="h-16 w-auto" />
            <!-- Mengurangi tinggi logo agar tidak terlalu besar -->
        </div>
        <!-- Sidebar Content -->
        <nav class="mt-4">
            <ul>
                <li>
                    <a href="/html/admin-Dashboard.php" class="block py-2 px-4 hover:bg-gray-700" id="dashboardBtn">Dashboard</a>
                </li>
                <li>
                    <a href="/html/admin-akun.php" class="block py-2 px-4 text-gray-800 bg-gray-500" id="akunBtn">Akun</a>
                </li>
                <li>
                    <a href="/html/admin-riwayat.php" class="block py-2 px-4 hover:bg-gray-700" id="riwayatBtn">Riwayat</a>
                </li>
            </ul>
        </nav>
        <!-- Logout Button -->
        <div class="absolute bottom-10 left-0 w-full font-bold lg:block">
            <a href="#" id="logoutBtn" class="block w-2/3 py-3 mx-auto text-sm text-white text-center bg-red-600 hover:bg-red-700 rounded-md z-10">Log Out</a>
    </aside>
    <!-- Content Area -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Manajemen Akun</h1>
            <div>
                <button onclick="showAddAccountModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                    Tambah Akun
                </button>
                <button onclick="showSuperUserModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Superuser
                </button>
            </div>
        </div>

        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Nama
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            No. Telepon
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account) : ?>
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?php echo htmlspecialchars($account['nama']); ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?php echo htmlspecialchars($account['no_hp']); ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?php echo ucfirst(htmlspecialchars($account['role'])); ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <button onclick="showResetPasswordModal(<?php echo $account['id_user']; ?>)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded">
                                    Reset Password
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function showAddAccountModal() {
            Swal.fire({
                title: 'Tambah Akun Baru',
                html: '<input id="nama" class="swal2-input" placeholder="Nama">' +
                    '<input id="no_hp" class="swal2-input" placeholder="Nomor Telepon">' +
                    '<select id="role" class="swal2-select">' +
                    '<option value="kasir">Kasir</option>' +
                    '<option value="teknisi">Teknisi</option>' +
                    '</select>' +
                    '<input id="password" type="password" class="swal2-input" placeholder="Password">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Tambah',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    // Here you would normally send this data to the server
                    return {
                        nama: document.getElementById('nama').value,
                        no_hp: document.getElementById('no_hp').value,
                        role: document.getElementById('role').value,
                        password: document.getElementById('password').value
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Here you would handle the server response
                    Swal.fire('Sukses', 'Akun baru berhasil ditambahkan', 'success');
                }
            });
        }

        function showSuperUserModal() {
            Swal.fire({
                title: 'Verifikasi Admin',
                input: 'password',
                inputPlaceholder: 'Masukkan password admin',
                showCancelButton: true,
                confirmButtonText: 'Verifikasi',
                cancelButtonText: 'Batal',
                preConfirm: (password) => {
                    // Here you would verify the admin password
                    // For this example, we'll just show the superuser table
                    showSuperUserTable();
                }
            });
        }

        function showSuperUserTable() {
            // Here you would fetch the admin data from the server
            // For this example, we'll use dummy data
            const adminData = [{
                    id: 1,
                    nama: 'Admin 1'
                },
                {
                    id: 2,
                    nama: 'Admin 2'
                }
            ];

            let tableHtml = '<table class="min-w-full leading-normal">';
            tableHtml += '<thead><tr><th>Nama</th><th>Aksi</th></tr></thead><tbody>';
            adminData.forEach(admin => {
                tableHtml += `<tr>
                <td>${admin.nama}</td>
                <td><button onclick="resetAdminPassword(${admin.id})" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded">Reset Password</button></td>
            </tr>`;
            });
            tableHtml += '</tbody></table>';

            Swal.fire({
                title: 'Daftar Admin',
                html: tableHtml,
                showCloseButton: true,
                showConfirmButton: false,
                footer: '<button onclick="addAdminAccount()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Tambah Admin</button>'
            });
        }

        function resetAdminPassword(adminId) {
            Swal.fire({
                title: 'Verifikasi Admin',
                input: 'password',
                inputPlaceholder: 'Masukkan password admin',
                showCancelButton: true,
                confirmButtonText: 'Reset Password',
                cancelButtonText: 'Batal',
                preConfirm: (password) => {
                    // Here you would verify the admin password and reset the password
                    Swal.fire('Sukses', 'Password admin berhasil direset', 'success');
                }
            });
        }

        function addAdminAccount() {
            Swal.fire({
                title: 'Verifikasi Admin',
                input: 'password',
                inputPlaceholder: 'Masukkan password admin',
                showCancelButton: true,
                confirmButtonText: 'Verifikasi',
                cancelButtonText: 'Batal',
                preConfirm: (password) => {
                    // Here you would verify the admin password
                    showAddAdminForm();
                }
            });
        }

        function showAddAdminForm() {
            Swal.fire({
                title: 'Tambah Akun Admin Baru',
                html: '<input id="nama" class="swal2-input" placeholder="Nama">' +
                    '<input id="no_hp" class="swal2-input" placeholder="Nomor Telepon">' +
                    '<input id="password" type="password" class="swal2-input" placeholder="Password">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Tambah',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    // Here you would normally send this data to the server
                    return {
                        nama: document.getElementById('nama').value,
                        no_hp: document.getElementById('no_hp').value,
                        password: document.getElementById('password').value
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Here you would handle the server response
                    Swal.fire('Sukses', 'Akun admin baru berhasil ditambahkan', 'success');
                }
            });
        }

        function showResetPasswordModal(userId) {
            Swal.fire({
                title: 'Verifikasi Admin',
                input: 'password',
                inputPlaceholder: 'Masukkan password admin',
                showCancelButton: true,
                confirmButtonText: 'Verifikasi',
                cancelButtonText: 'Batal',
                preConfirm: (password) => {
                    // Here you would verify the admin password
                    showNewPasswordForm(userId);
                }
            });
        }

        function showNewPasswordForm(userId) {
            Swal.fire({
                title: 'Reset Password',
                input: 'password',
                inputPlaceholder: 'Masukkan password baru',
                showCancelButton: true,
                confirmButtonText: 'Reset',
                cancelButtonText: 'Batal',
                preConfirm: (newPassword) => {
                    // Here you would send the new password to the server
                    Swal.fire('Sukses', 'Password berhasil direset', 'success');
                }
            });
        }
    </script>

</body>

</html>