<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

seed_demo_users();

$pdo = get_db();
$featured = $pdo->query(
    "SELECT m.*, c.name AS category_name
     FROM menu_items m
     JOIN categories c ON c.id = m.category_id
     WHERE m.is_featured = 1 AND m.is_available = 1
     ORDER BY m.id
     LIMIT 4"
)->fetchAll();

$pageTitle = 'Late-Night Tacos in Merewether';
$pageDesc  = 'Sabor Street Kitchen brings night-market tacos, elote, and aguas frescas to Merewether NSW. Order online for pickup.';
$activeNav = 'home';
$canonical = 'index.php';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-inner">
    <span class="eyebrow">Open Wed&ndash;Mon &middot; 4pm&ndash;11pm</span>
    <h1>Late-Night <span class="flicker">Sabor</span>, Served Street-Side</h1>
    <p class="hero-lede">
      Sabor Street Kitchen started as a single taco cart at the Riverside Night Market and grew into a
      full storefront, without losing the char, the crowd, or the 1am cravings. Handmade tortillas,
      slow-smoked meats, and salsas built from a family recipe box, every single night.
    </p>
    <div class="hero-cta">
      <a class="btn btn-primary" href="menu.php">Order Online</a>
      <a class="btn btn-secondary" href="about.php">Our Story</a>
    </div>
    <div class="hero-badges">
      <div><strong>7+</strong> years slinging tacos</div>
      <div><strong>40+</strong> scratch-made salsas &amp; sauces</div>
      <div><strong>4.8</strong> average night-market rating</div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Crowd Favorites</span>
      <h2>Tonight's Ticket Picks</h2>
      <p>A handful of the orders that keep the line out the door. See the full menu and build your own order any time.</p>
    </div>

    <div class="ticket-grid">
      <?php foreach ($featured as $item): ?>
        <article class="ticket">
          <div class="ticket-media">
            <img src="images/<?= h($item['image']) ?>" alt="<?= h($item['name']) ?> &mdash; <?= h($item['category_name']) ?>" loading="lazy" />
            <?php
              $tags = $item['tags'] ? array_map('trim', explode(',', $item['tags'])) : [];
              $stamp = null;
              foreach ($tags as $t) { if (strcasecmp($t, 'new') === 0) $stamp = 'New'; }
              if (!$stamp && $item['name'] === 'Carne Asada Tacos') $stamp = 'Best Seller';
            ?>
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
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <p class="text-center mt-lg">
      <a class="btn btn-outline-dark" href="menu.php">See Full Menu &amp; Start an Order</a>
    </p>
  </div>
</section>

<section class="section section-dark on-dark">
  <div class="container about-grid">
    <div class="about-media">
      <img src="images/about-truck.jpg" alt="The original Sabor Street Kitchen food truck parked at the Riverside Night Market" loading="lazy" />
    </div>
    <div>
      <span class="eyebrow">Since 2018</span>
      <h2>From One Cart to a Full Kitchen</h2>
      <p>
        What began as a folding table and a propane grill at the Riverside Night Market is now a
        proper storefront kitchen, same recipes, same char, same late hours. Every tortilla is
        still pressed by hand and every salsa still gets made from scratch, daily.
      </p>
      <a class="btn btn-secondary" href="about.php">Read Our Full Story</a>
    </div>
  </div>
</section>

<section class="section section-tint">
        <div class="container">
          <div class="section-head">
            <span class="eyebrow">What the Regulars Say</span>
            <h2>Straight From the Line</h2>
          </div>
          <div class="testimonial-strip">
            <blockquote class="testimonial">
              <p>
                "The quesabirria alone is worth the drive across town.
                Consistently the best tacos in the city, hands down."
              </p>
              <div class="testimonial-author">
                <img
                  src="images/maria.jpg"
                  alt="Maria T."
                  class="testimonial-avatar"
                />
                <cite>&mdash; Maria T., regular since 2019</cite>
              </div>
            </blockquote>

            <blockquote class="testimonial">
              <p>
                "Ordering online for pickup is dangerously easy. I've never
                waited more than ten minutes."
              </p>
              <div class="testimonial-author">
                <img
                  src="images/jordan.jpg"
                  alt="Jordan K."
                  class="testimonial-avatar"
                />
                <cite>&mdash; Jordan K.</cite>
              </div>
            </blockquote>

            <blockquote class="testimonial">
              <p>
                "They remembered my order was dairy-free after one visit. That's
                the kind of place this is."
              </p>
              <div class="testimonial-author">
                <img
                  src="images/priya.jpg"
                  alt="Priya S."
                  class="testimonial-avatar"
                />
                <cite>&mdash; Priya S.</cite>
              </div>
            </blockquote>
          </div>
        </div>
      </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
