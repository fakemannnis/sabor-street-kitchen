<?php
function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function format_money(float $n): string {
    return '$' . number_format($n, 2);
}

function is_valid_email(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_valid_phone(string $phone): bool {
    return (bool) preg_match('/^[0-9+()\-.\s]{7,20}$/', trim($phone));
}

function list_available_images(): array {
    $imagesDir = __DIR__ . '/../images';
    $extensions = ['svg', 'jpg', 'jpeg', 'png', 'webp', 'gif'];
    $files = [];
    foreach ($extensions as $ext) {
        foreach (glob($imagesDir . '/*.' . $ext) ?: [] as $path) {
            $files[] = basename($path);
        }
        foreach (glob($imagesDir . '/*.' . strtoupper($ext)) ?: [] as $path) {
            $files[] = basename($path);
        }
    }
    $files = array_unique($files);
    sort($files, SORT_STRING | SORT_FLAG_CASE);
    return $files;
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}
