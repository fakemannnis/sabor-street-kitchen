<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = get_db();

$menuItems = $pdo->query(
    "SELECT m.*, c.name AS category_name
     FROM menu_items m JOIN categories c ON c.id = m.category_id
     ORDER BY c.sort_order, m.name"
)->fetchAll();

$counts = [
    'users'    => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'orders'   => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'messages' => (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn(),
    'items'    => count($menuItems),
];

$base = '../';
$pageTitle = 'Admin Dashboard';
$activeNav = 'admin';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">Staff Only</span>
    <h1>Admin Dashboard</h1>
    <p>Logged in as <?= h(current_user()['name']) ?> (admin).</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="info-grid">
      <div class="info-card"><h3>Menu Items</h3><p><?= $counts['items'] ?></p></div>
      <div class="info-card"><h3>Registered Users</h3><p><?= $counts['users'] ?></p></div>
      <div class="info-card"><h3>Total Orders</h3><p><a href="orders.php"><?= $counts['orders'] ?> &rarr; View all</a></p></div>
      <div class="info-card"><h3>Unread Messages</h3><p><a href="messages.php"><?= $counts['messages'] ?> &rarr; View all</a></p></div>
    </div>

    <div class="section-head" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
      <h2 style="margin:0;">Manage Menu Items</h2>
      <a class="btn btn-primary" href="menu-add.php">+ Add New Item</a>
    </div>

    <div style="overflow-x:auto;">
      <table class="summary-table">
        <thead>
          <tr>
            <th scope="col">Name</th>
            <th scope="col">Category</th>
            <th scope="col">Price</th>
            <th scope="col">Featured</th>
            <th scope="col">Available</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($menuItems as $item): ?>
            <tr>
              <td><?= h($item['name']) ?></td>
              <td><?= h($item['category_name']) ?></td>
              <td><?= format_money((float) $item['price']) ?></td>
              <td><?= $item['is_featured'] ? 'Yes' : 'No' ?></td>
              <td><?= $item['is_available'] ? 'Yes' : 'No' ?></td>
              <td style="display:flex; gap:0.6rem;">
                <a href="menu-edit.php?id=<?= (int) $item['id'] ?>">Edit</a>
                <form method="post" action="menu-delete.php" onsubmit="return confirm('Delete this menu item? This cannot be undone.');" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
                  <input type="hidden" name="id" value="<?= (int) $item['id'] ?>" />
                  <button type="submit" class="remove" style="background:none;border:none;color:var(--chili-dark);text-decoration:underline;cursor:pointer;font-family:inherit;">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
