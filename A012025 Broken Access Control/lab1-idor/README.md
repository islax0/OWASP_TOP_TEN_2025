# Lab 1 — IDOR (Insecure Direct Object Reference)

**Goal:** View other users' orders by changing the `id` parameter.

## Files

- List (safe): `/lab1-idor/orders.php`
- Detail (vulnerable): `/lab1-idor/order.php?id=1`

## Vulnerable Code

```php
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
```

## Attack

1. Login as `user2`
2. Open `order.php?id=1`, `?id=2`, etc.
3. View other users' orders

## Fix

```php
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['id']]);
```

Add a check to ensure the order belongs to the current user.
