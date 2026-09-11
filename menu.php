<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$categories = $pdo->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll();

$itemsByCategory = [];
$itemStmt = $pdo->prepare('SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY id');
foreach ($categories as $cat) {
    $itemStmt->execute([$cat['id']]);
    $itemsByCategory[$cat['id']] = $itemStmt->fetchAll();
}

$pageTitle = 'Menu & Order';
$pageDesc  = 'Browse the full Sabor Street Kitchen menu — tacos, antojitos, bebidas, and postres — and build your pickup order online.';
$activeNav = 'menu';
$canonical = 'menu.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">Pickup Only &middot; Ready in ~15 Min</span>
    <h1>Build Your Order</h1>
    <p>Tap a category, add what sounds good, and check out below. The menu below is loaded live from our database.</p>
  </div>
</section>

<section class="section">
  <div class="container menu-layout menu-layout--order">
    <div>
      <div class="menu-tabs" role="tablist" aria-label="Menu categories">
        <?php foreach ($categories as $i => $cat): ?>
          <button class="menu-tab" type="button" role="tab"
                  aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                  aria-controls="cat-<?= h($cat['slug']) ?>" id="tab-<?= h($cat['slug']) ?>">
            <?= h($cat['name']) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <?php foreach ($categories as $i => $cat): ?>
        <div id="cat-<?= h($cat['slug']) ?>" class="menu-category<?= $i === 0 ? ' is-active' : '' ?>" role="tabpanel" aria-labelledby="tab-<?= h($cat['slug']) ?>">
          <div class="ticket-grid">
            <?php foreach ($itemsByCategory[$cat['id']] as $item): ?>
              <?php
                $tags = $item['tags'] ? array_map('trim', explode(',', $item['tags'])) : [];
                $stamp = null;
                foreach ($tags as $t) { if (strcasecmp($t, 'new') === 0) $stamp = 'New'; }
                if (!$stamp && $item['is_featured']) $stamp = 'Best Seller';
              ?>
              <article class="ticket" data-id="item-<?= (int) $item['id'] ?>" data-name="<?= h($item['name']) ?>" data-price="<?= h((string) $item['price']) ?>">
                <div class="ticket-media">
                  <img src="images/<?= h($item['image']) ?>" alt="<?= h($item['name']) ?>" loading="lazy" />
                  <?php if ($stamp): ?><span class="ticket-stamp"><?= h($stamp) ?></span><?php endif; ?>
                </div>
                <div class="ticket-body">
                  <div class="ticket-title-row">
                    <h3><?= h($item['name']) ?></h3>
                    <span class="ticket-price"><?= format_money((float) $item['price']) ?></span>
                  </div>
                  <p class="ticket-desc"><?= h($item['description']) ?></p>
                  <div class="ticket-tags">
                    <?php foreach ($tags as $t): if (strcasecmp($t, 'new') === 0) continue; ?>
                      <span class="tag<?= stripos($t, 'spic') !== false ? ' spicy' : '' ?>"><?= h($t) ?></span>
                    <?php endforeach; ?>
                  </div>
                  <div class="qty-row">
                    <div class="qty-control" aria-label="Quantity for <?= h($item['name']) ?>">
                      <button type="button" class="qty-minus" aria-label="Decrease quantity">&minus;</button>
                      <span class="qty-value">1</span>
                      <button type="button" class="qty-plus" aria-label="Increase quantity">+</button>
                    </div>
                    <button type="button" class="btn btn-primary add-to-cart">Add to order</button>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
            <?php if (!$itemsByCategory[$cat['id']]): ?>
              <p>No items available in this category right now.</p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <audio id="cart-add-sound" preload="auto" aria-hidden="true">
      <source src="audio/cart-add.wav" type="audio/wav" />
    </audio>

    <aside class="cart-panel" id="order-summary" aria-label="Your order">
      <h2>Your Order</h2>
      <ul class="cart-items" data-cart-list aria-live="polite"></ul>
      <div class="cart-totals">
        <div class="cart-row"><span>Subtotal</span><span data-cart-subtotal>$0.00</span></div>
        <div class="cart-row"><span>Est. tax (8%)</span><span data-cart-tax>$0.00</span></div>
        <div class="cart-row grand"><span>Total</span><span data-cart-total>$0.00</span></div>
      </div>
      <a class="btn btn-primary btn-block mt-lg" href="checkout.php" data-checkout-link style="justify-content:center;">Proceed to Checkout</a>
    </aside>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
