<?php
require_once __DIR__ . '/db.php';

function requireLogin(): void {
    if (!isset($_SESSION['user'])) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        die('<h1>403 Forbidden</h1><p>Admin access required.</p><a href="/dashboard.php">Back to dashboard</a>');
    }
}

function currentUser(): ?array {
    if (!isset($_SESSION['user'])) {
        return null;
    }
    return [
        'id'       => $_SESSION['id'],
        'username' => $_SESSION['user'],
        'role'     => $_SESSION['role'] ?? 'user',
    ];
}

function loginUser(string $username, string $password): bool {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['id']   = $user['id'];
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}