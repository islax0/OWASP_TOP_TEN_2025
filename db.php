<?php
require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
    return $pdo;
}

/**
 * Initialize / reset the database with sample data.
 * Call this once (or from init.php) to create tables and seed users/orders.
 */
function initDatabase(): void {
    $pdo = getDB();

    $pdo->exec("DROP TABLE IF EXISTS orders");
    $pdo->exec("DROP TABLE IF EXISTS users");

    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            balance REAL NOT NULL DEFAULT 0
        )
    ");

    $pdo->exec("
        CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            product TEXT NOT NULL,
            price REAL NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    // Seed users (plain-text passwords for lab simplicity)
    $users = [
        ['admin',   '123456', 'admin', 10000],
        ['user1',   '123456', 'user',  1500],
        ['user2',   '123456', 'user',  800],
    ];

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, balance) VALUES (?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmt->execute($u);
    }

    // Seed orders for IDOR lab
    $orders = [
        [1, 'MacBook Pro', 2499],
        [1, 'iPhone 15',   999],
        [2, 'Gaming Chair', 299],
        [2, 'Mechanical Keyboard', 150],
        [3, 'Monitor 27"', 450],
        [3, 'Webcam HD',   89],
        [1, 'AirPods Pro', 249],
        [2, 'USB-C Hub',   45],
    ];

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product, price) VALUES (?, ?, ?)");
    foreach ($orders as $o) {
        $stmt->execute($o);
    }
}