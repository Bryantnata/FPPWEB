<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $keteranganAkhir = $_POST['keterangan_akhir'];
    $status = $_POST['status'];

    $query = "UPDATE barang b
              INNER JOIN detail_keluhan dk ON b.ID_Service = dk.ID_Service
              SET b.status = ?, dk.keterangan_akhir = ?
              WHERE b.ID_Service = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $status, $keteranganAkhir, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => mysqli_error($link)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>