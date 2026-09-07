<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Privacy Notice';
$pageDesc  = 'How Sabor Street Kitchen collects, uses, and protects your information.';
$activeNav = '';
$canonical = 'privacy.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">Transparency</span>
    <h1>Privacy Notice</h1>
    <p>A plain-language explanation of what information we collect, why, and how it's protected.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:760px;">

    <h2>What We Collect</h2>
    <p>We only collect information you choose to give us:</p>
    <ul>
      <li><strong>Account details</strong> (registration): full name, email address, and an optional phone number.</li>
      <li><strong>Order details</strong> (checkout): name, phone number, pickup time, payment method preference, and any order notes you add.</li>
      <li><strong>Contact messages</strong> (contact form): name, email, optional phone number and party size, and your message.</li>
    </ul>

    <h2>How We Use It</h2>
    <p>
      Your details are used only to process your order or reply to your enquiry, and if you register to let you view your own past orders. We do not sell, rent, or share your information with third
      parties for marketing purposes.
    </p>

    <h2>How It's Protected</h2>
    <ul>
      <li>Passwords are never stored in plain text they're hashed with PHP's <code>password_hash()</code> before being saved.</li>
      <li>All database queries use parameterized (prepared) statements to prevent SQL injection.</li>
      <li>Forms are protected against cross-site request forgery (CSRF) with a unique per-session token.</li>
      <li>Access to order and message records is restricted by role: only logged-in admins can view all customers' data; members can only view their own order history.</li>
    </ul>

    <h2>Your Choices</h2>
    <p>
      You can order as a guest without creating an account. If you do register, you can contact us at
      <a href="mailto:hellosabor@gmail.com">hellosabor@gmail.com</a> to request that
      your account and associated data be deleted.
    </p>

    <h2>Cookies &amp; Sessions</h2>
    <p>
      We use a single session cookie to keep you logged in and to remember your cart while you browse.
      This cookie is deleted automatically when you log out or close your browser session. No third-party
      tracking or advertising cookies are used on this site.
    </p>

    <p class="field-hint mt-lg">Last updated: <?= date('F Y') ?>. This notice covers this prototype site only.</p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
