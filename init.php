<?php
/**
 * Run once to create / reset the SQLite database.
 * Visit: /broken-access-control-labs/init.php
 */
require_once __DIR__ . '/db.php';

initDatabase();

echo "<h1>Database initialized successfully</h1>";
echo "<p>Users created:</p><ul>";
echo "<li><strong>admin</strong> / 123456 (role=admin, balance=10000)</li>";
echo "<li><strong>user1</strong> / 123456 (role=user, balance=1500)</li>";
echo "<li><strong>user2</strong> / 123456 (role=user, balance=800)</li>";
echo "</ul>";
echo "<p><a href='/login.php'>Go to Login</a></p>";  