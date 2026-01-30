<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

/**
 * Checks if the current admin has super_admin role.
 */
function isSuperAdmin()
{
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

/**
 * Redirects if the user is not a super_admin.
 */
function restrictToSuperAdmin()
{
    if (!isSuperAdmin()) {
        header('Location: index.php');
        exit();
    }
}
?>