<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = get_db();
$categories = $pdo->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll();
$imageFiles = list_available_images();

$errors = [];
$values = [
    'name' => '', 'category_id' => $categories[0]['id'] ?? '', 'price' => '',
    'description' => '', 'image' => $imageFiles[0] ?? '', 'tags' => '',
    'is_featured' => false, 'is_available' => true,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    $values['name']        = trim($_POST['name'] ?? '');
    $values['category_id'] = $_POST['category_id'] ?? '';
    $values['price']       = trim($_POST['price'] ?? '');
    $values['description'] = trim($_POST['description'] ?? '');
    $values['image']       = $_POST['image'] ?? '';
    $values['tags']        = trim($_POST['tags'] ?? '');
    $values['is_featured'] = isset($_POST['is_featured']);
    $values['is_available']= isset($_POST['is_available']);

    if (mb_strlen($values['name']) < 2) $errors[] = 'Please enter an item name.';
    if (!is_numeric($values['price']) || (float) $values['price'] <= 0) $errors[] = 'Please enter a valid price greater than 0.';
    if (!in_array($values['image'], $imageFiles, true)) $errors[] = 'Please choose a valid image file.';
    $validCategoryIds = array_column($categories, 'id');
    if (!in_array((int) $values['category_id'], $validCategoryIds, true)) $errors[] = 'Please choose a valid category.';

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO menu_items (category_id, name, description, price, image, tags, is_featured, is_available)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $values['category_id'], $values['name'], $values['description'] ?: null,
            (float) $values['price'], $values['image'], $values['tags'] ?: null,
            $values['is_featured'] ? 1 : 0, $values['is_available'] ? 1 : 0,
        ]);
        set_flash('success', h($values['name']) . ' was added to the menu.');
        redirect('index.php');
    }
}

$base = '../';
$pageTitle = 'Add Menu Item';
$activeNav = 'admin';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow"><a href="index.php" style="color:inherit;">&larr; Back to Dashboard</a></span>
    <h1>Add Menu Item</h1>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:640px;">
    <?php if ($errors): ?>
      <div class="form-feedback is-visible error" role="alert">
        <ul style="margin:0; padding-left:1.1em;"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>
    <?php $mode = 'add'; require __DIR__ . '/_menu_item_form.php'; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
