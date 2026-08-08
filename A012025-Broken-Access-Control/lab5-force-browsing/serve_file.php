<?php
/**
 * SECURE file serving script
 * 
 * This script serves uploaded files with proper access control:
 * 1. Requires authentication
 * 2. Checks file ownership (user can only access their own files)
 * 3. Prevents directory traversal attacks
 */
require_once __DIR__ . '/../../auth.php';
requireLogin();

$user = currentUser();

// Get filename from request
$filename = $_GET['file'] ?? '';

// Security: Validate filename to prevent directory traversal
if (empty($filename) || strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
    http_response_code(400);
    die('Invalid filename');
}

// Security: Check that file belongs to current user
// Filenames are in format: uniqid_userid.extension
if (strpos($filename, '_' . $user['id'] . '.') === false) {
    http_response_code(403);
    die('Access denied: You can only access your own files');
}

$filepath = __DIR__ . '/uploads_secure/' . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    die('File not found');
}

// Determine content type
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'pdf' => 'application/pdf'
];

$contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

// Serve file
header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($filepath));
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: no-cache');

readfile($filepath);
exit;
