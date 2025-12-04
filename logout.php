<?php
/**
 * Logout Page
 * Customer & Real-Time Trading Management System
 */

session_start();

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: login.php');
exit;
