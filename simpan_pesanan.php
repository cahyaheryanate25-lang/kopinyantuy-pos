<?php
header('Content-Type: application/json');

// Koneksi ke Database Laragon
$conn = new mysqli("localhost", "root", "", "db_kopinyantuy");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $kode_order = $data['id'];
    $meja = $data['meja'];
    $bayar = $data['bayar'];
    $rincian = $data['rincian'];
    $total = $data['total'];

    $stmt = $conn->prepare("INSERT INTO pesanan (kode_order, nomor_meja, metode_bayar, rincian_menu, total_harga) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $kode_order, $meja, $bayar, $rincian, $total);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Pesanan berhasil disimpan"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal menyimpan pesanan"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Data tidak valid"]);
}
$conn->close();
?>