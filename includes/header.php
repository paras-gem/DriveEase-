<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure BASE_URL doesn't have an extra space at the end
define('BASE_URL', 'https://driveease-portal.free.je/');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveEase Support Desk</title>
    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Use relative path to ensure local CSS is loaded instead of the remote production CSS -->
    <link rel="stylesheet" href="assets/css/style.css">      
    <script>
        // Apply theme early to prevent flash
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
</head>
<body>
    <div class="app-container">