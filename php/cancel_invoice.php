<?php
include 'connect_db.php';

header('Content-Type: application/json');

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_service = $data['id_service'] ?? '';

    if ($id_service) {
        mysqli_begin_transaction($link);

        try {
            // Hapus data dari tabel barang
            $stmt = mysqli_prepare($link, "DELETE FROM barang WHERE ID_Service = ?");
            mysqli_stmt_bind_param($stmt, "i", $id_service);
            
            if (mysqli_stmt_execute($stmt)) {
                // Hapus data terkait dari tabel lain jika diperlukan
                // Misalnya: detail_keluhan, rincian_keluhan, dll.
                
                mysqli_commit($link);
                $response["success"] = true;
                $response["message"] = "Invoice berhasil dibatalkan.";
            } else {
                throw new Exception("Gagal menghapus data barang.");
            }
        } catch (Exception $e) {
            mysqli_rollback($link);
            $response["message"] = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $response["message"] = "ID Service tidak valid.";
    }
} else {
    $response["message"] = "Invalid request method";
}

mysqli_close($link);
echo json_encode($response);