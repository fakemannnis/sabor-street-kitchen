<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
    set_flash('error', 'Invalid request.');
    redirect('index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$pdo = get_db();

try {
    $stmt = $pdo->prepare('DELETE FROM menu_items WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('success', 'Menu item deleted. Past orders that included it keep their original item name and price, since those are stored directly on the order.');
} catch (PDOException $e) {
    set_flash('error', 'This item could not be deleted due to a database error. Please try again.');
}

redirect('index.php');
