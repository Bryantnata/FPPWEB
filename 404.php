<!-- 4040.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-red-600">404</h1>
        <p class="text-2xl mt-4">Oops! Halaman tidak ditemukan.</p>
        <p class="mt-2 text-gray-600">Halaman yang Anda cari tidak tersedia. SIlakan <a href="http://fppweb.bry/" class="text-blue-500 underline">Refresh</a> atau hubungi administrator untuk bantuan lebih lanjut.</p>
        <p class="mt-4 text-gray-700">Hubungi administrator: <a href="mailto:bryantnata@students.amikom.ac.id" class="text-blue-500 underline">admin</a></p>
        <p class="mt-4 text-gray-700">
            <?php include '/laragon/www/FPPWEB/php/connect_db.php';
            $error_message = mysqli_connect_error(); ?>
        </p>


    </div>
</body>

</html>