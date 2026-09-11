<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    if (csrf_check()) {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $status  = $_POST['status'] ?? '';
        if (in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$status, $orderId]);
            set_flash('success', "Order #$orderId marked as $status.");
        }
    }
    redirect('orders.php');
}

$orders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
$itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');

$base = '../';
$pageTitle = 'All Orders';
$activeNav = 'admin';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow"><a href="index.php" style="color:inherit;">&larr; Back to Dashboard</a></span>
    <h1>All Orders</h1>
    <p><?= count($orders) ?> order(s) placed so far.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$orders): ?>
      <p class="empty-cart-note">No orders have been placed yet.</p>
    <?php endif; ?>

    <?php foreach ($orders as $order): ?>
      <?php $itemStmt->execute([$order['id']]); $items = $itemStmt->fetchAll(); ?>
      <div class="value-card mt-lg">
        <div class="ticket-title-row">
          <h3>Order #<?= (int) $order['id'] ?> &mdash; <?= h($order['customer_name']) ?></h3>
          <span class="ticket-price"><?= format_money((float) $order['total']) ?></span>
        </div>
        <p class="field-hint">
          <?= h($order['customer_phone']) ?> &middot;
          Placed <?= h(date('j M Y, g:i A', strtotime($order['created_at']))) ?> &middot;
          Pickup <?= h(date('g:i A', strtotime($order['pickup_time']))) ?> &middot;
          Payment: <?= h(ucfirst($order['payment_method'])) ?>
        </p>
        <ul>
          <?php foreach ($items as $li): ?>
            <li><?= (int) $li['quantity'] ?> &times; <?= h($li['item_name']) ?> (<?= format_money((float) $li['unit_price']) ?> ea.)</li>
          <?php endforeach; ?>
        </ul>
        <?php if ($order['notes']): ?><p><em>Note: <?= h($order['notes']) ?></em></p><?php endif; ?>

        <form method="post" style="display:flex; gap:0.6rem; align-items:center; flex-wrap:wrap;">
          <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
          <input type="hidden" name="action" value="update_status" />
          <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>" />
          <label for="status-<?= (int) $order['id'] ?>" class="field-hint" style="margin:0;">Status:</label>
          <select id="status-<?= (int) $order['id'] ?>" name="status" onchange="this.form.submit()">
            <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
          <noscript><button type="submit" class="btn btn-outline-dark">Update</button></noscript>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
