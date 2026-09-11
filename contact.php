<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

start_secure_session();
$pdo = get_db();
$errors = [];
$values = ['name' => '', 'email' => '', 'phone' => '', 'partySize' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try submitting the form again.';
    }

    foreach ($values as $key => $_) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    if (mb_strlen($values['name']) < 2) {
        $errors[] = 'Please enter your full name.';
    }
    if (!is_valid_email($values['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($values['phone'] !== '' && !is_valid_phone($values['phone'])) {
        $errors[] = 'Please enter a valid phone number, or leave it blank.';
    }
    $partySize = null;
    if ($values['partySize'] !== '') {
        if (!ctype_digit($values['partySize']) || (int) $values['partySize'] < 1 || (int) $values['partySize'] > 20) {
            $errors[] = 'Party size should be between 1 and 20.';
        } else {
            $partySize = (int) $values['partySize'];
        }
    }
    if (mb_strlen($values['message']) < 10) {
        $errors[] = 'Message should be at least 10 characters.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO messages (name, email, phone, party_size, message) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $values['name'],
            $values['email'],
            $values['phone'] ?: null,
            $partySize,
            $values['message'],
        ]);
        redirect('contact.php?sent=1');
    }
}

$sent = isset($_GET['sent']);

$pageTitle = 'Contact & Reservations';
$pageDesc  = 'Get in touch with Sabor Street Kitchen — questions, catering, or reservations for our Merewether storefront.';
$activeNav = 'contact';
$canonical = 'contact.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">We'd Love to Hear From You</span>
    <h1>Contact &amp; Reservations</h1>
    <p>Questions, catering requests, group reservations, or just want to say hi reach us any of the ways below.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="info-grid">
      <div class="info-card">
        <h3>Visit</h3>
        <p>99 Frederick St<br />Merewether NSW 2291</p>
      </div>
      <div class="info-card">
        <h3>Call</h3>
        <p><a href="tel:+61244440123">(02) 4444 0123</a></p>
      </div>
      <div class="info-card">
        <h3>Email</h3>
        <p><a href="mailto:mailto:hellosabor@gmail.com">hellosabor@gmail.com</a></p>
      </div>
      <div class="info-card">
        <h3>Follow</h3>
        <p>
          <a href="https://www.instagram.com/koiaustralia" target="_blank" rel="noopener">Instagram</a> &middot;
          <a href="https://www.facebook.com/KOIAustralia" target="_blank" rel="noopener">Facebook</a> &middot;
          <a href="https://www.tiktok.com/@kings.own.institute" target="_blank" rel="noopener">TikTok</a>
        </p>
      </div>
    </div>

    <div class="menu-layout menu-layout--split">
      <div>
        <h2>Send a Message</h2>

        <audio id="message-sent-sound" preload="auto" aria-hidden="true">
          <source src="audio/message-sent.wav" type="audio/wav" />
        </audio>

        <?php if ($sent): ?>
          <div class="form-feedback is-visible success" role="alert" tabindex="-1">
            Thanks! Your message has been saved and we'll get back to you within 1 business day.
          </div>
          <script>
            document.addEventListener("DOMContentLoaded", function () {
              var audio = document.querySelector("#message-sent-sound");
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

          <form id="contact-form" method="post" novalidate class="form-grid">
            <div class="form-feedback" role="alert"></div>
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />

            <div class="form-row">
              <div class="field">
                <label for="name">Full name <span class="required">*</span></label>
                <input type="text" id="name" name="name" required minlength="2" autocomplete="name" placeholder="Rekha Nachiappan" value="<?= h($values['name']) ?>" aria-describedby="name-error" />
                <p class="field-error" id="name-error" data-error-for="name" aria-live="polite"></p>
              </div>
              <div class="field">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="rekha.nachiappan@koi.edu.au" value="<?= h($values['email']) ?>" aria-describedby="email-error" />
                <p class="field-error" id="email-error" data-error-for="email" aria-live="polite"></p>
              </div>
            </div>

            <div class="form-row">
              <div class="field">
                <label for="phone">Phone (optional)</label>
                <input type="tel" id="phone" name="phone" autocomplete="tel" placeholder="(61) 000-000-000" value="<?= h($values['phone']) ?>" aria-describedby="phone-error" />
                <p class="field-error" id="phone-error" data-error-for="phone" aria-live="polite"></p>
              </div>
              <div class="field">
                <label for="partySize">Party size (if reservation)</label>
                <input type="number" id="partySize" name="partySize" min="1" max="20" placeholder="4" value="<?= h($values['partySize']) ?>" aria-describedby="partySize-error" />
                <p class="field-error" id="partySize-error" data-error-for="partySize" aria-live="polite"></p>
              </div>
            </div>

            <div class="field">
              <label for="message">Message <span class="required">*</span></label>
              <textarea id="message" name="message" required minlength="10" rows="5" placeholder="Tell us what's up..." aria-describedby="message-error"><?= h($values['message']) ?></textarea>
              <p class="field-hint">Minimum 10 characters.</p>
              <p class="field-error" id="message-error" data-error-for="message" aria-live="polite"></p>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Send Message</button>
          </form>
        <?php endif; ?>
      </div>

      <div>
        <h2>Find Us</h2>
        <div class="map-frame">
          <iframe
            title="Map showing Sabor Street Kitchen location at 99 Frederick St, Merewether NSW 2291"
            src="https://www.openstreetmap.org/export/embed.html?bbox=151.75240195%2C-32.95045237%2C151.75840195%2C-32.94745237&amp;layer=mapnik&amp;marker=-32.94895237%2C151.75540195"
            loading="lazy">
          </iframe>
        </div>
        <p class="field-hint">
          <a href="https://www.openstreetmap.org/?mlat=-32.94895237&amp;mlon=151.75540195#map=17/-32.94895237/151.75540195" target="_blank" rel="noopener">View larger map</a>
        </p>
        <p class="field-hint mt-lg">
          Street parking is available along Frederick St and Ridge St after 5pm. Bike racks are out front.
        </p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
