<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Gallery';
$pageDesc  = 'Photos from the Sabor Street Kitchen storefront and kitchen in Merewether NSW.';
$activeNav = 'gallery';
$canonical = 'gallery.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">Behind the Grill</span>
    <h1>Gallery</h1>
    <p>A look at the kitchen, the counter, and a few of the plates that keep people coming back. Click any photo to see it larger.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="gallery-grid">
      <?php
      $galleryPhotos = [
          ['gallery-1.jpg', 'The grill station mid-service on a busy Friday night'],
          ['gallery-2.jpg', 'Hand-pressing corn tortillas fresh for the evening service'],
          ['gallery-3.jpg', 'The salsa bar, restocked every few hours'],
          ['gallery-4.jpg', 'A packed order ready for pickup at the counter'],
          ['gallery-5.jpg', 'Regulars sharing a table on the patio at sunset'],
          ['gallery-6.jpg', "Fresh produce delivery, sorted for the day's prep"],
          ['gallery-7.jpg', 'The storefront sign lit up as the sun goes down'],
          ['gallery-8.jpg', 'Diego plating a fresh batch of quesabirria'],
      ];
      foreach ($galleryPhotos as [$file, $caption]): ?>
        <button type="button" class="gallery-item" data-full="images/<?= h($file) ?>" data-caption="<?= h($caption) ?>">
          <img src="images/<?= h($file) ?>" alt="<?= h($caption) ?>" loading="lazy" />
        </button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-tint">
  <div class="container text-center">
    <span class="eyebrow">See It Live</span>
    <h2>Watch our Kitchen Tour</h2>
    <video controls width="100%" style="max-width:720px;border-radius:14px;box-shadow:var(--shadow-card);margin-top:1rem;" poster="images/gallery-1.jpg">
      <source src="video/kitchen_tour.mp4" type="video/mp4" />
      Your browser doesn't support embedded video. <a href="video/kitchen_tour.mp4">Download the video</a> instead.
    </video>
  </div>
</section>

<!-- Lightbox modal -->
<div class="lightbox" data-lightbox aria-hidden="true" role="dialog" aria-modal="true" aria-label="Enlarged photo viewer">
  <div class="lightbox-content">
    <button type="button" class="lightbox-close" aria-label="Close photo viewer">&times;</button>
    <img src="" alt="" />
    <p class="lightbox-caption" data-lightbox-caption></p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
