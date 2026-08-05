<?php
/**
 * LAB 5 — Force Browsing on Uploaded Files (SECURE VERSION)
 *
 * Fixed version with proper access controls:
 * 1. Files are served through a PHP script that checks authentication
 * 2. Users can only access their own uploaded files
 * 3. Uploads directory is protected from direct access
 */
require_once __DIR__ . '/../../auth.php';
requireLogin();

$user = currentUser();

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/uploads_secure';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Create .htaccess to prevent direct access
$htaccess = $uploadDir . '/.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "Deny from all\n");
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Basic validation
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            $message = 'Invalid file type. Only images and PDF allowed.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $message = 'File too large. Max 2MB.';
        } else {
            // Generate unique filename with user ID
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . $user['id'] . '.' . $extension;
            $destination = $uploadDir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $message = 'File uploaded successfully!';
            } else {
                $message = 'Upload failed.';
            }
        }
    } else {
        $message = 'Upload error: ' . $file['error'];
    }
}

// List uploaded files for current user only
$files = [];
if (is_dir($uploadDir)) {
    $allFiles = array_diff(scandir($uploadDir), ['.', '..', '.htaccess']);
    // Filter to show only current user's files
    foreach ($allFiles as $file) {
        if (strpos($file, '_' . $user['id'] . '.') !== false) {
            $files[] = $file;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 5 — Force Browsing | File Upload (SECURE)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        nav a:hover { color: #f1f5f9; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1.5rem; }
        h1 { margin-bottom: .5rem; }
        .hint { background: #1e293b; border-left: 4px solid #10b981; padding: 1rem; margin: 1.5rem 0; border-radius: 0 8px 8px 0; font-size: .9rem; color: #6ee7b7; }
        .hint code { background: #334155; padding: .2rem .4rem; border-radius: 4px; }
        .upload-form { background: #1e293b; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0; }
        .upload-form input[type="file"] { margin: 1rem 0; }
        .upload-form button { padding: .6rem 1.2rem; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; }
        .upload-form button:hover { background: #2563eb; }
        .message { padding: .75rem; margin: 1rem 0; border-radius: 6px; }
        .message.success { background: #065f46; color: #a7f3d0; }
        .message.error { background: #7f1d1d; color: #fca5a5; }
        .files-list { margin-top: 2rem; }
        .files-list h3 { margin-bottom: 1rem; }
        .file-item { background: #1e293b; padding: .75rem; margin: .5rem 0; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; }
        .file-item code { color: #94a3b8; font-size: .85rem; }
        .file-item a { color: #3b82f6; text-decoration: none; }
        .file-item a:hover { text-decoration: underline; }
        .back { display: inline-block; margin-bottom: 1.5rem; color: #94a3b8; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 5 — Force Browsing (SECURE)</span>
        <div>
            <a href="../../dashboard.php">Dashboard</a>
            <a href="../../logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <a class="back" href="../../dashboard.php">← Dashboard</a>
        <h1>File Upload (SECURE)</h1>
        <p style="color:#94a3b8;margin-bottom:1rem;">Logged in as <?= htmlspecialchars($user['username']) ?></p>

        <div class="hint">
            <strong>Security Fixes:</strong><br>
            1. Files stored in protected directory with <code>.htaccess</code> (Deny from all)<br>
            2. Files served through authenticated PHP script that checks ownership<br>
            3. Users can only access their own uploaded files<br>
            4. Filenames include user ID for ownership tracking
        </div>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'success') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="upload-form">
            <h2>Upload a file</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="file" required accept="image/*,.pdf">
                <button type="submit">Upload</button>
            </form>
        </div>

        <div class="files-list">
            <h3>My Uploaded Files (<?= count($files) ?>)</h3>
            <?php if (empty($files)): ?>
                <p style="color:#94a3b8;">No files uploaded yet.</p>
            <?php else: ?>
                <?php foreach ($files as $file): ?>
                    <div class="file-item">
                        <code><?= htmlspecialchars($file) ?></code>
                        <a href="serve_file.php?file=<?= htmlspecialchars($file) ?>" target="_blank">View File</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
