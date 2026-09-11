<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Our Story';
$pageDesc  = 'The story of Sabor Street Kitchen — from a single food cart at the Riverside Night Market to a full storefront in Merewether NSW.';
$activeNav = 'about';
$canonical = 'about.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">Est. 2018</span>
    <h1>Our Story</h1>
    <p>A folding table, a propane grill, and a family salsa recipe; here's how Sabor Street Kitchen came to be.</p>
  </div>
</section>

<section class="section">
  <div class="container about-grid">
    <div>
      <h2>Started With One Cart</h2>
      <p>
        In 2018, founder Elena Ruiz set up a single folding table at the Riverside Night Market with a
        propane grill, a cooler of marinated skirt steak, and her grandmother's salsa recipes written on
        index cards. Word spread fast. Within a year, the line for "the taco cart with the good salsa"
        wrapped around the block every Friday night.
      </p>
      <p>
        Sabor Street Kitchen moved into its first permanent storefront on Frederick Street in 2021,
        but the goal never changed: cook the way Elena's family cooked on Sunday afternoons, just
        fast enough for a Tuesday night crowd.
      </p>
    </div>
    <div class="about-media">
      <img src="images/about-founder.png" alt="Portrait of Elena Ruiz, founder of Sabor Street Kitchen, in the kitchen" loading="lazy" />
    </div>
  </div>
</section>

<section class="section section-dark on-dark">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">How We Got Here</span>
      <h2>From Cart to Storefront</h2>
    </div>
    <ol class="timeline">
      <li>
        <h3>2018 &mdash; The First Cart</h3>
        <p>One folding table, one grill, and a Friday-night spot at the Riverside Night Market.</p>
      </li>
      <li>
        <h3>2019 &mdash; The Line Around the Block</h3>
        <p>Word of mouth turns a side hustle into a regular gig, five nights a week.</p>
      </li>
      <li>
        <h3>2021 &mdash; A Real Kitchen</h3>
        <p>Sabor Street Kitchen opens its first storefront on Frederick Street, keeping the same late hours.</p>
      </li>
      <li>
        <h3>2024 &mdash; Still Family-Run</h3>
        <p>Elena's brother Diego joins as head of the kitchen; the recipe box grows to forty-plus salsas.</p>
      </li>
    </ol>
  </div>
</section>

<section class="section section-tint">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">What Matters to Us</span>
      <h2>How We Cook</h2>
    </div>
    <div class="values-grid">
      <div class="value-card">
        <h3>Made From Scratch</h3>
        <p>Tortillas pressed daily, salsas built fresh every morning, nothing from a can.</p>
      </div>
      <div class="value-card">
        <h3>Honest Ingredients</h3>
        <p>Locally sourced produce where we can get it, and meat from suppliers we've visited ourselves.</p>
      </div>
      <div class="value-card">
        <h3>Late-Night Friendly</h3>
        <p>Open past most kitchens close, because the best conversations happen after 9pm.</p>
      </div>
      <div class="value-card">
        <h3>Everyone's Invited</h3>
        <p>Vegetarian, vegan, and gluten-free options on every part of the menu, clearly labeled.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container about-grid">
    <div class="about-media">
      <img src="images/about-team.png" alt="The Sabor Street Kitchen team preparing food together in the kitchen" loading="lazy" />
    </div>
    <div>
      <h2>Meet the Crew</h2>
      <p>
        Sabor Street Kitchen is run by a small, tight kitchen crew who've mostly been here since the
        early cart days. Diego runs the grill, Marisol handles every salsa by hand, and Elena still
        drops in most nights to say hello to regulars by name.
      </p>
      <a class="btn btn-primary" href="contact.php">Come Say Hi</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
