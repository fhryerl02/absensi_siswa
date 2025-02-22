<?php
// ...existing bootstrap code like database connection...
$conn = new mysqli("localhost", "root", "", "absensi_sekolah");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Check if column exists first
$check = $conn->query("SHOW COLUMNS FROM absen_siswa LIKE 'mata_pelajaran'");
if ($check->num_rows == 0) {
    // Column doesn't exist, so add it
    $sql = "ALTER TABLE absen_siswa ADD mata_pelajaran VARCHAR(100) NOT NULL DEFAULT ''";
    if ($conn->query($sql) === TRUE) {
        echo "Kolom 'mata_pelajaran' berhasil ditambahkan.";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Kolom 'mata_pelajaran' sudah ada.";
}
$conn->close();
?>
