<?php

// retrieves data from vehicles table 

require_once('../config/db.php');

// defining response header as json

header('Content-Type: application/json');

// request method determination

try {
    // createing statement to get the data from the table  

    
$stmt = $pdo->query("SELECT * FROM vehicles");

    // Execute statement

    $stmt = $pdo->query($sql);

    // fetching all data in associative array

    $vehicles = $stmt ->fetchAll(PDO::FETCH_ASSOC);

    // json encoding and output

    echo json_encode($vehicles);
} catch(PDOException $e){
    http_response_code(500);  // Internal Server Error
    echo json_encode(['error' => 'Failed to fetch vehicles: ' . $e->getMessage()]);
    }