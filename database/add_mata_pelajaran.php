<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "absensi_sekolah");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if column already exists
$result = $conn->query("SHOW COLUMNS FROM absen_siswa LIKE 'mata_pelajaran'");
if ($result->num_rows == 0) {
    // Add mata_pelajaran column if it doesn't exist
    $sql = "ALTER TABLE absen_siswa ADD COLUMN mata_pelajaran VARCHAR(100) NOT NULL DEFAULT '' AFTER status";
    
    if ($conn->query($sql) === TRUE) {
        echo "Column mata_pelajaran added successfully";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column mata_pelajaran already exists";
}

$conn->close();
?>
