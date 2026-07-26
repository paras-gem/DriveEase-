<?php
require 'config/db.php';
try {
    $pdo->exec("INSERT INTO employees (name, email, password) VALUES ('Test Employee', 'test2@employee.com', 'hashedpassword')");
    echo 'Success';
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
