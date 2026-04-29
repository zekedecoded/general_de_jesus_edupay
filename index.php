<?php
    session_start();
    // include "./admin/dashboard.php";

    $role = $_SESSION['roleID'] ?? 0;

    switch ($role) {
        case 3:
            include "admin/dashboard.php";
            break;

        case 2:
            include "merchant/dashboard.php";
            break;

        case 1:
            include "student/dashboard.php";
            break;

        default:
            echo "Unknown Role";
            break;
    }
    
    
?>