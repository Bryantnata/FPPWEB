<?php
include "/laragon/www/FPPWEB/php/connect_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Query to check if pricing is filled
    $query = "SELECT COUNT(*) as count FROM rincian_keluhan rk
              JOIN detail_keluhan dk ON rk.id_keluhan = dk.id_keluhan
              WHERE dk.ID_Service = ? AND rk.harga > 0";

    $stmt = $link->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $pricingFilled = $row['count'] > 0;

    echo json_encode(['pricingFilled' => $pricingFilled]);
} else {
    echo json_encode(['error' => 'Invalid request']);
}

$link->close();
