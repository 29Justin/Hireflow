<?php
session_start();

// Find where PHP is saving all the session files on your server
$sessionPath = session_save_path();

// If it's empty, use the default system temporary directory
if (empty($sessionPath)) {
    $sessionPath = sys_get_temp_dir();
}

// Scan the folder for session files (they usually start with 'sess_')
$files = glob($sessionPath . '/sess_*');

$deletedCount = 0;
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file); // Delete the session file
        $deletedCount++;
    }
}

echo "Successfully killed {$deletedCount} active sessions across the server. Everyone is now logged out.";
?>