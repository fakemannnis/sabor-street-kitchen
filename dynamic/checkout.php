<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

start_secure_session();
$pdo = get_db();
$errors = [];
$showSuccess = false;
$successOrder = null;

/* ---------------- Handle order submission ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try submitting the form again.';
    }

    $customerName  = trim($_POST['name'] ?? '');
    $customerPhone = trim($_POST['phone'] ?? '');
    $pickupTime    = trim($_POST['pickupTime'] ?? '');
    $payment       = $_POST['payment'] ?? '';
    $notes         = trim($_POST['notes'] ?? '');
    $cartRaw       = $_POST['cart_data'] ?? '[]';
    $cartInput     = json_decode($cartRaw, true);

    if (mb_strlen($customerName) < 2) {
        $errors[] = 'Please enter your full name.';
    }
    if (!is_valid_phone($customerPhone)) {
        $errors[] = 'Please enter a valid phone number.';
    }
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $pickupTime)) {
        $errors[] = 'Please choose a valid pickup time.';
    }
    if (!in_array($payment, ['card', 'cash'], true)) {
        $errors[] = 'Please choose a payment method.';
    }
    if (!is_array($cartInput) || count($cartInput) === 0) {
        $errors[] = 'Your order is empty. Please add items from the menu first.';
    }

    // Re-price every line against the database — never trust client-submitted prices.
    $lineItems = [];
    if (!$errors) {
        foreach ($cartInput as $line) {
            if (!isset($line['id'], $line['qty'])) {
                continue;
            }
            $qty = (int) $line['qty'];
            if ($qty < 1 || $qty > 20) {
                continue;
            }
            // Ticket ids are rendered as "item-<id>" in menu.php.
            $menuItemId = (int) str_replace('item-', '', (string) $line['id']);
            $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ? AND is_available = 1');
            $stmt->execute([$menuItemId]);
            $dbItem = $stmt->fetch();
            if ($dbItem) {
                $lineItems[] = [
                    'menu_item_id' => $dbItem['id'],
                    'name'         => $dbItem['name'],
                    'price'        => (float) $dbItem['price'],
                    'qty'          => $qty,
                ];
            }
        }
        if (!$lineItems) {
            $errors[] = 'Your order could not be matched to current menu items. Please rebuild your order from the menu.';
        }
    }

    if (!$errors) {
        $subtotal = array_reduce($lineItems, fn($carry, $li) => $carry + $li['price'] * $li['qty'], 0.0);
        $tax      = round($subtotal * 0.08, 2);
        $total    = round($subtotal + $tax, 2);
        $user     = current_user();

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO orders (user_id, customer_name, customer_phone, pickup_time, payment_method, notes, subtotal, tax, total)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $user['id'] ?? null,
                $customerName,
                $customerPhone,
                $pickupTime,
                $payment,
                $notes ?: null,
                $subtotal,
                $tax,
                $total,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, menu_item_id, item_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($lineItems as $li) {
                $itemStmt->execute([$orderId, $li['menu_item_id'], $li['name'], $li['price'], $li['qty']]);
            }

            $pdo->commit();

            // Store only the order id in the session so the confirmation page
            // can't be viewed by guessing/incrementing an ID in the URL.
            $_SESSION['last_order_id'] = $orderId;
            redirect('checkout.php?success=1');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong while placing your order. Please try again.';
        }
    }
}

/* ---------------- Handle post-redirect success view ---------------- */
if (isset($_GET['success']) && !empty($_SESSION['last_order_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$_SESSION['last_order_id']]);
    $successOrder = $stmt->fetch();

    if ($successOrder) {
        $itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $itemStmt->execute([$successOrder['id']]);
        $successOrder['items'] = $itemStmt->fetchAll();
        $showSuccess = true;
    }
    unset($_SESSION['last_order_id']);
}

$pageTitle = 'Checkout';
$pageDesc  = 'Confirm your Sabor Street Kitchen order and choose a pickup time.';
$activeNav = 'menu';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">Step 2 of 2</span>
    <h1>Checkout</h1>
    <p>Confirm your items and tell us when to expect you. Orders are saved to our database and validated on the server.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width: 760px;">

    <!-- Plays a short chime once the order is confirmed. -->
    <audio id="order-sound" preload="auto" aria-hidden="true">
      <source src="audio/order-confirmation.wav" type="audio/wav" />
    </audio>

    <?php if ($showSuccess): ?>
      <div class="form-feedback is-visible success" role="alert">
        Order #<?= (int) $successOrder['id'] ?> placed! We'll have it ready around
        <strong><?= h(date('g:i A', strtotime($successOrder['pickup_time']))) ?></strong>.
        A text confirmation would be sent to <?= h($successOrder['customer_phone']) ?> here in a full deployment.
      </div>

      <table class="summary-table">
        <caption class="visually-hidden">Confirmed order summary</caption>
        <thead><tr><th scope="col">Item</th><th scope="col">Qty</th><th scope="col">Price</th><th scope="col">Total</th></tr></thead>
        <tbody>
          <?php foreach ($successOrder['items'] as $li): ?>
            <tr>
              <td><?= h($li['item_name']) ?></td>
              <td><?= (int) $li['quantity'] ?></td>
              <td><?= format_money((float) $li['unit_price']) ?></td>
              <td><?= format_money((float) $li['unit_price'] * (int) $li['quantity']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td colspan="3">Subtotal</td><td><?= format_money((float) $successOrder['subtotal']) ?></td></tr>
          <tr><td colspan="3">Tax</td><td><?= format_money((float) $successOrder['tax']) ?></td></tr>
          <tr><td colspan="3">Total</td><td><?= format_money((float) $successOrder['total']) ?></td></tr>
        </tfoot>
      </table>

      <p class="text-center mt-lg"><a class="btn btn-primary" href="menu.php">Order Something Else</a></p>

      <script>
        // Order is now safely stored server-side — clear the local cart & badge.
        try { window.localStorage.removeItem("sabor_cart_v1"); } catch (e) {}
        document.addEventListener("DOMContentLoaded", function () {
          var badge = document.querySelector("[data-cart-count]");
          if (badge) badge.textContent = "0";
          var audio = document.querySelector("#order-sound");
          if (audio) { audio.currentTime = 0; audio.play().catch(function () {}); }
        });
      </script>

    <?php else: ?>

      <?php if ($errors): ?>
        <div class="form-feedback is-visible error" role="alert">
          <ul style="margin:0; padding-left:1.1em;">
            <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <p class="empty-cart-note" data-empty-cart hidden>
        Your order is empty. <a href="menu.php">Head back to the menu</a> to add something delicious.
      </p>

      <table class="summary-table" data-summary-table>
        <caption class="visually-hidden">Order summary</caption>
        <thead><tr><th scope="col">Item</th><th scope="col">Qty</th><th scope="col">Price</th><th scope="col">Total</th></tr></thead>
        <tbody></tbody>
        <tfoot>
          <tr><td colspan="3">Subtotal</td><td data-summary-subtotal>$0.00</td></tr>
          <tr><td colspan="3">Est. tax (8%)</td><td data-summary-tax>$0.00</td></tr>
          <tr><td colspan="3">Total</td><td data-summary-total>$0.00</td></tr>
        </tfoot>
      </table>

      <form id="checkout-form" method="post" novalidate class="form-grid mt-lg">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
        <input type="hidden" name="cart_data" value="" />

        <div class="form-row">
          <div class="field">
            <label for="co-name">Full name <span class="required">*</span></label>
            <input type="text" id="co-name" name="name" required autocomplete="name" placeholder="Jamie Rivera" value="<?= h($_POST['name'] ?? '') ?>" />
          </div>
          <div class="field">
            <label for="co-phone">Phone <span class="required">*</span></label>
            <input type="tel" id="co-phone" name="phone" required autocomplete="tel" placeholder="(555) 555-1234" value="<?= h($_POST['phone'] ?? '') ?>" />
          </div>
        </div>

        <div class="form-row">
          <div class="field">
            <label for="co-time">Pickup time <span class="required">*</span></label>
            <input type="time" id="co-time" name="pickupTime" required value="<?= h($_POST['pickupTime'] ?? '') ?>" />
          </div>
          <div class="field">
            <label for="co-payment">Payment method <span class="required">*</span></label>
            <select id="co-payment" name="payment" required>
              <option value="">Choose one&hellip;</option>
              <option value="card" <?= ($_POST['payment'] ?? '') === 'card' ? 'selected' : '' ?>>Pay by card at pickup</option>
              <option value="cash" <?= ($_POST['payment'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash at pickup</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="co-notes">Order notes (optional)</label>
          <textarea id="co-notes" name="notes" rows="3" placeholder="Allergies, spice preferences, etc."><?= h($_POST['notes'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Place Order</button>
        <p class="field-hint text-center">A short confirmation chime plays once your order is placed.</p>
      </form>

    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
