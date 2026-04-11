<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

function checkTable($db, $table) {
    echo "=== TABLE: $table ===\n";
    if ($table === 'admin') {
        $query = "SELECT id, nom, email FROM $table";
    } else {
        $query = "SELECT id, nom, email, verification_status FROM $table";
    }
    $stmt = $db->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = isset($row['verification_status']) ? $row['verification_status'] : 'N/A';
        echo "ID: {$row['id']} | Email: {$row['email']} | Status: $status\n";
    }
    echo "\n";
}

checkTable($db, 'patient');
checkTable($db, 'medecin');
checkTable($db, 'admin');
