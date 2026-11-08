<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "db_kopinyantuy");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi gagal"]);
    exit;
}

// Hanya menerima request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $menu_nama = $conn->real_escape_string($data['menu_nama'] ?? '');
    $rating_value = (int)($data['rating_value'] ?? 0);
    $komentar = $conn->real_escape_string($data['komentar'] ?? '');

    if ($menu_nama && $rating_value > 0 && $rating_value <= 5) {
        // Simpan ke tabel reviews
        $sql = "INSERT INTO reviews (menu_nama, rating_value, komentar) VALUES ('$menu_nama', $rating_value, '$komentar')";
        
        if ($conn->query($sql) === TRUE) {
            // Update atau Insert ke tabel kalkulasi menu
            $sql_update = "INSERT INTO menus_rating (menu_nama, average_rating, total_reviews) 
                           VALUES ('$menu_nama', $rating_value, 1) 
                           ON DUPLICATE KEY UPDATE 
                           average_rating = (average_rating * total_reviews + $rating_value) / (total_reviews + 1),
                           total_reviews = total_reviews + 1";
            $conn->query($sql_update);

            echo json_encode(["status" => "success", "message" => "Ulasan berhasil disimpan!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan ulasan."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Data tidak valid."]);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Mengambil data rating untuk ditampilkan di frontend
    $result = $conn->query("SELECT * FROM menus_rating");
    $ratings = [];
    while($row = $result->fetch_assoc()) {
        $ratings[$row['menu_nama']] = $row;
    }
    echo json_encode($ratings);
}
$conn->close();
?>