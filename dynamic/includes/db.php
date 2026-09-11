<?php
/**
 * Database connection (PDO / MySQL).
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'sabor_street_kitchen');
define('DB_USER', 'root');   
define('DB_PASS', '');       

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Please check the database configuration in includes/db.php and confirm MySQL is running.');
        }
    }
    return $pdo;
}


function seed_demo_users(): void {
    $pdo = get_db();
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return;
    }
    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute(['Site Admin', 'admin@sabor.example', password_hash('Admin123!', PASSWORD_DEFAULT), null, 'admin']);
    $stmt->execute(['Demo Member', 'member@sabor.example', password_hash('Member123!', PASSWORD_DEFAULT), '0400000000', 'member']);
}
