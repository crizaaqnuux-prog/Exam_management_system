<?php
session_start();

// Function to check if user is logged in
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

// Function to check if user is admin
function checkAdmin() {
    checkLogin();
    if ($_SESSION['role'] !== 'admin') {
        echo "Access Denied: You do not have permission to view this page.";
        exit;
    }
}

// Function to check if user is invigilator
function checkInvigilator() {
    checkLogin();
    if ($_SESSION['role'] !== 'invigilator') {
        echo "Access Denied: You do not have permission to view this page.";
        exit;
    }
}
