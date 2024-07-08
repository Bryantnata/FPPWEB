<?php
include "/laragon/www/FPPWEB/php/connect_db.php";
require_once "../config/config.php"; // Pastikan file ini ada dan mengatur variabel PEPPER

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
        </div>
    </aside>
    <!-- Content Area -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Manajemen Akun</h1>
            <div>
                <button onclick="showAddAccountModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                    Tambah Akun
                </button>
                <button onclick="showSuperUserModal()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
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
                                <button onclick="showResetPasswordModal(<?php echo $account['id_user']; ?>)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded mr-2">
                                    Reset Password
                                </button>
                                <button onclick="deleteAccount(<?php echo $account['id_user']; ?>, '<?php echo $account['role']; ?>')" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Fungsi untuk logout
        const logoutButton = document.getElementById("logoutBtn");
        if (logoutButton) {
            logoutButton.addEventListener("click", function(event) {
                event.preventDefault();
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
                        window.location.href = "/index.html";
                    }
                });
            });
        }

        async function showAddAccountModal() {
            try {
                const {
                    value: formValues
                } = await Swal.fire({
                    title: 'Tambah Akun Baru',
                    html: `
                <input id="name" class="swal2-input" placeholder="Nama">
                <input id="username" class="swal2-input" placeholder="Username">
                <input id="no_hp" class="swal2-input" placeholder="Nomor Telepon">
                <select id="role" class="swal2-select">
                    <option value="kasir">Kasir</option>
                    <option value="teknisi">Teknisi</option>
                </select>
                <input id="password" type="password" class="swal2-input" placeholder="Password">
            `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Tambah',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        return {
                            name: document.getElementById('name').value,
                            username: document.getElementById('username').value,
                            no_hp: document.getElementById('no_hp').value,
                            role: document.getElementById('role').value,
                            password: document.getElementById('password').value
                        };
                    }
                });

                if (formValues) {
                    const response = await fetch('/php/add_account.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formValues)
                    });

                    const contentType = response.headers.get("content-type");
                    if (!contentType || !contentType.includes("application/json")) {
                        throw new Error("Oops, we haven't got JSON!");
                    }

                    const result = await response.json();

                    if (result.success) {
                        await Swal.fire('Sukses', 'Akun baru berhasil ditambahkan', 'success');
                        location.reload();
                    } else {
                        throw new Error(result.message || 'Terjadi kesalahan saat menambahkan akun');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                await Swal.fire('Error', error.message || 'Terjadi kesalahan saat menambahkan akun', 'error');
            }
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

        async function showSuperUserTable() {
            try {
                const response = await fetch('/php/get_admin_list.php');
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const adminData = await response.json();

                let tableHtml = '<table class="min-w-full leading-normal">';
                tableHtml += '<thead><tr><th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th><th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th></tr></thead><tbody>';
                adminData.forEach(admin => {
                    tableHtml += `<tr>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${admin.nama}</td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <button onclick="resetAdminPassword(${admin.id_user})" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded mr-2">Reset Password</button>
                    <button onclick="deleteAccount(${admin.id_user}, 'admin')" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Hapus</button>
                </td>
            </tr>`;
                });
                tableHtml += '</tbody></table>';

                Swal.fire({
                    title: 'Daftar Admin',
                    html: tableHtml,
                    showCloseButton: true,
                    showConfirmButton: false,
                    footer: '<button onclick="addAdminAccount()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Tambah Admin</button>',
                    customClass: {
                        container: 'swal-wide',
                    }
                });
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Gagal mengambil data admin', 'error');
            }
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

        async function showAddAdminForm() {
            const {
                value: formValues
            } = await Swal.fire({
                title: 'Tambah Akun Admin Baru',
                html: '<input id="nama" class="swal2-input" placeholder="Nama">' +
                    '<input id="username" class="swal2-input" placeholder="Username">' +
                    '<input id="no_hp" class="swal2-input" placeholder="Nomor Telepon">' +
                    '<input id="password" type="password" class="swal2-input" placeholder="Password">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Tambah',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    return {
                        nama: document.getElementById('nama').value,
                        username: document.getElementById('username').value,
                        no_hp: document.getElementById('no_hp').value,
                        password: document.getElementById('password').value,
                        role: 'admin'
                    }
                }
            });

            if (formValues) {
                try {
                    const response = await fetch('/php/add_account.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formValues)
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire('Sukses', 'Akun admin baru berhasil ditambahkan', 'success');
                        showSuperUserTable(); // Refresh the superuser table
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Terjadi kesalahan saat menambahkan akun admin', 'error');
                }
            }
        }

        async function showResetPasswordModal(userId) {
            const {
                value: adminPassword
            } = await Swal.fire({
                title: 'Verifikasi Admin',
                input: 'password',
                inputPlaceholder: 'Masukkan password admin',
                showCancelButton: true,
                confirmButtonText: 'Verifikasi',
                cancelButtonText: 'Batal'
            });

            if (adminPassword) {
                try {
                    const verifyResponse = await fetch('/php/verify_admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            password: adminPassword
                        })
                    });

                    const verifyResult = await verifyResponse.json();

                    if (verifyResult.success) {
                        showNewPasswordForm(userId);
                    } else {
                        Swal.fire('Error', 'Verifikasi gagal', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Terjadi kesalahan saat verifikasi', 'error');
                }
            }
        }

        async function resetAdminPassword(userId) {
            const {
                value: adminPassword
            } = await Swal.fire({
                title: 'Verifikasi Admin',
                input: 'password',
                inputPlaceholder: 'Masukkan password admin Anda',
                showCancelButton: true,
                confirmButtonText: 'Verifikasi',
                cancelButtonText: 'Batal'
            });

            if (adminPassword) {
                try {
                    const verifyResponse = await fetch('/php/verify_admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            password: adminPassword
                        })
                    });

                    const verifyResult = await verifyResponse.json();

                    if (verifyResult.success) {
                        const {
                            value: newPassword
                        } = await Swal.fire({
                            title: 'Reset Password Admin',
                            input: 'password',
                            inputPlaceholder: 'Masukkan password baru',
                            showCancelButton: true,
                            confirmButtonText: 'Reset',
                            cancelButtonText: 'Batal'
                        });

                        if (newPassword) {
                            const resetResponse = await fetch('/php/reset_password.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    userId,
                                    newPassword
                                })
                            });

                            const resetResult = await resetResponse.json();

                            if (resetResult.success) {
                                Swal.fire('Sukses', 'Password admin berhasil direset', 'success');
                            } else {
                                Swal.fire('Error', resetResult.message, 'error');
                            }
                        }
                    } else {
                        Swal.fire('Error', 'Verifikasi gagal', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Terjadi kesalahan saat mereset password', 'error');
                }
            }
        }

        async function showNewPasswordForm(userId) {
            const {
                value: newPassword
            } = await Swal.fire({
                title: 'Reset Password',
                input: 'password',
                inputPlaceholder: 'Masukkan password baru',
                showCancelButton: true,
                confirmButtonText: 'Reset',
                cancelButtonText: 'Batal'
            });

            if (newPassword) {
                try {
                    const response = await fetch('/php/reset_password.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            userId,
                            newPassword
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire('Sukses', 'Password berhasil direset', 'success');
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Terjadi kesalahan saat mereset password', 'error');
                }
            }
        }

        async function deleteAccount(userId, role) {
            const result = await Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Anda akan menghapus akun ${role} ini. Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                const {
                    value: adminPassword
                } = await Swal.fire({
                    title: 'Verifikasi Admin',
                    input: 'password',
                    inputPlaceholder: 'Masukkan password admin',
                    inputAttributes: {
                        autocapitalize: 'off',
                        autocorrect: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Verifikasi',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: async (password) => {
                        try {
                            const response = await fetch('/php/verify_admin.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    password
                                })
                            });
                            const data = await response.json();
                            if (!data.success) {
                                throw new Error(data.message || 'Verifikasi gagal');
                            }
                            return true;
                        } catch (error) {
                            Swal.showValidationMessage(`Verifikasi gagal: ${error}`);
                        }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });

                if (adminPassword) {
                    try {
                        const response = await fetch('/php/delete_account.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                userId,
                                role
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            Swal.fire('Terhapus!', 'Akun telah berhasil dihapus.', 'success');
                            if (role === 'admin') {
                                showSuperUserTable(); // Refresh the superuser table
                            } else {
                                location.reload(); // Reload the page for non-admin accounts
                            }
                        } else {
                            Swal.fire('Error', result.message, 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Terjadi kesalahan saat menghapus akun', 'error');
                    }
                }
            }
        }
    </script>

</body>

</html>