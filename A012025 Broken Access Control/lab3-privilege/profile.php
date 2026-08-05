<?php
/**
 * LAB 3 — Privilege Escalation (Vulnerable)
 *
 * Vertical (parameter tampering):
 *   POST role=admin  → UPDATE users SET role=? WHERE id=?
 *   Attacker changes their own role to admin.
 *
 * Horizontal:
 *   GET /profile.php?id=2  → view another user's profile (no ownership check)
 *
 * Fix:
 *   - Never take role from user input: unset($_POST['role']) or hardcode role='user'
 *   - For viewing: only allow own id unless admin
 */
require_once __DIR__ . '/../../auth.php';
requireLogin();

$user = currentUser();
$pdo = getDB();
$message = '';
$error = '';

// Target profile: default to self, or ?id= for horizontal IDOR
$targetId = isset($_GET['id']) && ctype_digit((string)$_GET['id'])
    ? (int)$_GET['id']
    : (int)$user['id'];

// ========== VULNERABLE: no check that targetId == current user (unless admin) ==========
// FIXED would check:
// if ($targetId !== (int)$user['id'] && $user['role'] !== 'admin') {
//     http_response_code(403);
//     die('Forbidden');
// }

$stmt = $pdo->prepare("SELECT id, username, role, balance FROM users WHERE id = ?");
$stmt->execute([$targetId]);
$profile = $stmt->fetch();

if (!$profile) {
    die('User not found');
}

// Handle profile update (role tampering)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $targetId === (int)$user['id']) {
    // ========== VULNERABLE: accepts role from POST ==========
    $newRole = $_POST['role'] ?? $profile['role'];

    // FIXED:
    // unset($_POST['role']);
    // $newRole = $profile['role']; // keep existing, never take from client

    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $user['id']]);

    // Refresh session role
    $_SESSION['role'] = $newRole;
    $user['role'] = $newRole;
    $profile['role'] = $newRole;

    $message = 'Profile updated. New role: ' . htmlspecialchars($newRole);
    if ($newRole === 'admin') {
        $message .= ' — Privilege Escalation successful!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — Lab 3</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 560px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; }
        h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        .row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid #334155; }
        .label { color: #94a3b8; }
        label { display: block; margin: 1rem 0 .35rem; font-size: .875rem; color: #cbd5e1; }
        select, input { width: 100%; padding: .6rem; border: 1px solid #334155; border-radius: 8px; background: #0f172a; color: #f1f5f9; }
        button { margin-top: 1rem; padding: .65rem 1.25rem; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .msg { background: #14532d; color: #bbf7d0; padding: .75rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .hint { background: #1e293b; border-left: 4px solid #f59e0b; padding: 1rem; margin: 1.25rem 0; border-radius: 0 8px 8px 0; font-size: .85rem; color: #fbbf24; }
        .links { margin-top: 1.5rem; font-size: .9rem; }
        .links a { color: #3b82f6; margin-right: 1rem; }
        .warn { background: #7f1d1d; color: #fecaca; padding: .75rem; border-radius: 8px; margin-top: 1rem; font-size: .85rem; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 3 — Privilege Escalation</span>
        <div>
            <a href="users.php">Users list</a>
            <a href="../../dashboard.php">Dashboard</a>
            <a href="../../logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h1>Profile #<?= (int)$profile['id'] ?></h1>

            <?php if ($message): ?>
                <div class="msg"><?= $message ?></div>
            <?php endif; ?>

            <div class="row"><span class="label">Username</span><span><?= htmlspecialchars($profile['username']) ?></span></div>
            <div class="row"><span class="label">Role</span><span><?= htmlspecialchars($profile['role']) ?></span></div>
            <div class="row"><span class="label">Balance</span><span>$<?= number_format($profile['balance'], 2) ?></span></div>

            <?php if ((int)$profile['id'] !== (int)$user['id']): ?>
                <div class="warn">
                    <strong>Horizontal privilege escalation / IDOR!</strong>
                    You are viewing another user's profile (id=<?= (int)$profile['id'] ?>).
                    Your id is <?= (int)$user['id'] ?>.
                </div>
            <?php endif; ?>

            <?php if ((int)$profile['id'] === (int)$user['id']): ?>
                <form method="POST" style="margin-top:1.25rem;">
                    <label>Change role (parameter tampering)</label>
                    <select name="role">
                        <option value="user" <?= $profile['role'] === 'user' ? 'selected' : '' ?>>user</option>
                        <option value="admin" <?= $profile['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                    </select>
                    <button type="submit">Update Profile</button>
                </form>

                <div class="hint">
                    <strong>Vertical escalation:</strong> Select <code>admin</code> and submit.
                    The server blindly does <code>UPDATE users SET role=?</code> with the value from the form.
                </div>
            <?php endif; ?>
        </div>

        <div class="links">
            <a href="profile.php?id=1">View profile id=1</a>
            <a href="profile.php?id=2">View profile id=2</a>
            <a href="profile.php?id=3">View profile id=3</a>
            <a href="users.php">Users list (vertical)</a>
        </div>
    </div>
</body>
</html>