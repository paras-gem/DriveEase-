<?php
require 'config/db.php';
echo "VEHICLES:\n";
print_r($pdo->query('SHOW COLUMNS FROM vehicles')->fetchAll(PDO::FETCH_ASSOC));
echo "\nTICKETS:\n";
print_r($pdo->query('SHOW COLUMNS FROM support_tickets')->fetchAll(PDO::FETCH_ASSOC));
