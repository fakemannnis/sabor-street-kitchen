<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'mark_read') {
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        set_flash('success', 'Message deleted.');
    }
    redirect('messages.php');
}

$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();

$base = '../';
$pageTitle = 'Contact Messages';
$activeNav = 'admin';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow"><a href="index.php" style="color:inherit;">&larr; Back to Dashboard</a></span>
    <h1>Contact Messages</h1>
    <p><?= count($messages) ?> message(s) received.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$messages): ?>
      <p class="empty-cart-note">No messages yet.</p>
    <?php endif; ?>

    <?php foreach ($messages as $msg): ?>
      <div class="value-card mt-lg" style="<?= $msg['is_read'] ? 'opacity:0.75;' : 'border-color:var(--marigold);' ?>">
        <div class="ticket-title-row">
          <h3><?= h($msg['name']) ?> <?php if (!$msg['is_read']): ?><span class="tag spicy">New</span><?php endif; ?></h3>
          <span class="field-hint"><?= h(date('j M Y, g:i A', strtotime($msg['created_at']))) ?></span>
        </div>
        <p class="field-hint">
          <a href="mailto:<?= h($msg['email']) ?>"><?= h($msg['email']) ?></a>
          <?php if ($msg['phone']): ?> &middot; <?= h($msg['phone']) ?><?php endif; ?>
          <?php if ($msg['party_size']): ?> &middot; Party of <?= (int) $msg['party_size'] ?><?php endif; ?>
        </p>
        <p><?= nl2br(h($msg['message'])) ?></p>

        <div style="display:flex; gap:0.8rem;">
          <?php if (!$msg['is_read']): ?>
            <form method="post">
              <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
              <input type="hidden" name="action" value="mark_read" />
              <input type="hidden" name="id" value="<?= (int) $msg['id'] ?>" />
              <button type="submit" class="btn btn-outline-dark">Mark as Read</button>
            </form>
          <?php endif; ?>
          <form method="post" onsubmit="return confirm('Delete this message?');">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="id" value="<?= (int) $msg['id'] ?>" />
            <button type="submit" class="remove" style="background:none;border:none;color:var(--chili-dark);text-decoration:underline;cursor:pointer;font-family:inherit;">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
