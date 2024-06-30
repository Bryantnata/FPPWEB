<?php
include 'connect_db.php';

$search = isset($_GET['q']) ? mysqli_real_escape_string($link, $_GET['q']) : '';

$query = "SELECT id_pelanggan, nama, alamat, no_hp FROM pelanggan 
          WHERE nama LIKE '%$search%' OR no_hp LIKE '%$search%' 
          LIMIT 10";

$result = mysqli_query($link, $query);

$customers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $customers[] = [
        'id' => $row['id_pelanggan'],
        'text' => $row['nama'] . ' (' . $row['no_hp'] . ')',
        'name' => $row['nama'],
        'address' => $row['alamat'],
        'phone' => $row['no_hp']
    ];
}

echo json_encode($customers);

mysqli_close($link);
