<?php
session_start();
$conn = new mysqli("localhost", "root", "", "absensi_sekolah");

if ($conn->connect_error) {
    die(json_encode(["error" => "Koneksi gagal: " . $conn->connect_error]));
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM data_siswa WHERE id = ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "Data tidak ditemukan untuk ID: $id"]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(["error" => "Query gagal: " . $conn->error]);
    }
} else {
    echo json_encode(["error" => "ID tidak valid."]);
}

$conn->close();
?>
