<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$pdo  = get_db();
$user = current_user();
$errors = [];

/* ---------------- Update profile (CRUD: Update) ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    $fullName = trim($_POST['full_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    if (mb_strlen($fullName) < 2) {
        $errors[] = 'Please enter your full name.';
    }
    if ($phone !== '' && !is_valid_phone($phone)) {
        $errors[] = 'Please enter a valid phone number, or leave it blank.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?');
        $stmt->execute([$fullName, $phone ?: null, $user['id']]);
        $_SESSION['user']['name'] = $fullName;
        set_flash('success', 'Your profile has been updated.');
        redirect('account.php');
    }
}

/* ---------------- Change password (CRUD: Update) ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    $current = (string) ($_POST['current_password'] ?? '');
    $new     = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        $errors[] = 'Current password is incorrect.';
    }
    if (mb_strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
        $errors[] = 'New password must be at least 8 characters with a letter and a number.';
    }
    if ($new !== $confirm) {
        $errors[] = 'New passwords do not match.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        set_flash('success', 'Your password has been changed.');
        redirect('account.php');
    }
}

/* ---------------- Load current data ---------------- */
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

$orderStmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$orderStmt->execute([$user['id']]);
$orders = $orderStmt->fetchAll();

$itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');

$pageTitle = 'My Account';
$pageDesc  = 'Manage your Sabor Street Kitchen account and view your order history.';
$activeNav = 'account';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">Welcome Back</span>
    <h1><?= h($profile['full_name']) ?></h1>
    <p><?= h($profile['email']) ?></p>
  </div>
</section>

<?php if ($errors): ?>
  <div class="container" style="padding-top:1rem;">
    <div class="form-feedback is-visible error" role="alert">
      <ul style="margin:0; padding-left:1.1em;"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
    </div>
  </div>
<?php endif; ?>

<section class="section">
  <div class="container menu-layout menu-layout--split">
    <div>
      <h2>Update Profile</h2>
      <form method="post" novalidate class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
        <input type="hidden" name="action" value="update_profile" />
        <div class="field">
          <label for="full_name">Full name</label>
          <input type="text" id="full_name" name="full_name" required minlength="2" value="<?= h($profile['full_name']) ?>" />
        </div>
        <div class="field">
          <label for="phone">Phone</label>
          <input type="tel" id="phone" name="phone" value="<?= h($profile['phone'] ?? '') ?>" />
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>

      <h2 class="mt-lg">Change Password</h2>
      <form method="post" novalidate class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
        <input type="hidden" name="action" value="change_password" />
        <div class="field">
          <label for="current_password">Current password</label>
          <input type="password" id="current_password" name="current_password" required autocomplete="current-password" />
        </div>
        <div class="form-row">
          <div class="field">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password" />
          </div>
          <div class="field">
            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password" />
          </div>
        </div>
        <button type="submit" class="btn btn-outline-dark">Update Password</button>
      </form>
    </div>

    <div>
      <h2>Order History</h2>
      <?php if (!$orders): ?>
        <p class="empty-cart-note">You haven't placed any orders yet. <a href="menu.php">Browse the menu</a>.</p>
      <?php else: ?>
        <?php foreach ($orders as $order): ?>
          <?php
            $itemStmt->execute([$order['id']]);
            $items = $itemStmt->fetchAll();
          ?>
          <div class="value-card mt-lg">
            <div class="ticket-title-row">
              <h3>Order #<?= (int) $order['id'] ?></h3>
              <span class="tag"><?= h(ucfirst($order['status'])) ?></span>
            </div>
            <p class="field-hint">
              Placed <?= h(date('j M Y, g:i A', strtotime($order['created_at']))) ?>
              &middot; Pickup <?= h(date('g:i A', strtotime($order['pickup_time']))) ?>
            </p>
            <ul>
              <?php foreach ($items as $li): ?>
                <li><?= (int) $li['quantity'] ?> &times; <?= h($li['item_name']) ?> (<?= format_money((float) $li['unit_price']) ?>)</li>
              <?php endforeach; ?>
            </ul>
            <p><strong>Total: <?= format_money((float) $order['total']) ?></strong></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
