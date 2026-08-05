-- Reference schema (actual DB is SQLite, created by init.php / db.php)

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    balance REAL NOT NULL DEFAULT 0
);

CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    product TEXT NOT NULL,
    price REAL NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Seed
INSERT INTO users (username, password, role, balance) VALUES
('admin',   '123456', 'admin', 10000),
('user1',   '123456', 'user',  1500),
('user2', '123456', 'user',  800);

INSERT INTO orders (user_id, product, price) VALUES
(1, 'MacBook Pro', 2499),
(1, 'iPhone 15', 999),
(2, 'Gaming Chair', 299),
(2, 'Mechanical Keyboard', 150),
(3, 'Monitor 27"', 450),
(3, 'Webcam HD', 89),
(1, 'AirPods Pro', 249),
(2, 'USB-C Hub', 45);