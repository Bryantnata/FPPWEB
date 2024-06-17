<!-- index.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaporService</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex justify-center items-center h-screen bg-blue-950">
    <?php include '/laragon/www/FPPWEB/php/connect_db.php';
    if (!$link) {
        echo "<script>
                setTimeout(function () {
                    Swal.fire({
                        icon: 'error',
                        title: '<span style=\"color: #ffffff;\">Oops...</span>',
                        html: '<span style=\"color: #ffffff;\">Terjadi masalah saat menghubungkan ke database. Silakan menghubungi administrator.',
                        background: '#1e3a8a', // bg-blue-950
                        color: '#ffffff',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(function() {
                        window.location = '/404.php';
                    });
                }, 100);
              </script>";
        exit();
    } else {
        echo "<script>
                 setTimeout(function () {
                    Swal.fire({
                        title: '<span style=\"color: #ffffff;\">LaporService</span>',
                        html: '<img src=\"/assets/logopweb.png\" alt=\"Logo\" class=\"w-24 h-24 mx-auto\"><br><br><span style=\"color: #ffffff;\">Tunggu Sebentar...</span>',
                        background: '#1e3a8a', // bg-blue-950
                        color: '#ffffff',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = '/html/role.php'; 
                    });
                }, 100);
              </script>";
    }
    ?>
</body>

</html>