<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

start_secure_session();
seed_demo_users();

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'account.php');
}

$errors = [];
$values = ['full_name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try submitting the form again.';
    }

    $values['full_name'] = trim($_POST['full_name'] ?? '');
    $values['email']     = trim($_POST['email'] ?? '');
    $values['phone']     = trim($_POST['phone'] ?? '');
    $password             = (string) ($_POST['password'] ?? '');
    $confirmPassword      = (string) ($_POST['confirm_password'] ?? '');

    if (mb_strlen($values['full_name']) < 2) {
        $errors[] = 'Please enter your full name (at least 2 characters).';
    }
    if (!is_valid_email($values['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($values['phone'] !== '' && !is_valid_phone($values['phone'])) {
        $errors[] = 'Please enter a valid phone number, or leave it blank.';
    }
    if (mb_strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must include at least one letter and one number.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$values['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists. Try logging in instead.';
        }
    }

    if (!$errors) {
        $pdo = get_db();
        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $values['full_name'],
            $values['email'],
            password_hash($password, PASSWORD_DEFAULT),
            $values['phone'] ?: null,
            'member',
        ]);

        $userId = (int) $pdo->lastInsertId();
        login_user(['id' => $userId, 'full_name' => $values['full_name'], 'email' => $values['email'], 'role' => 'member']);
        set_flash('success', 'Welcome, ' . $values['full_name'] . '! Your account has been created.');
        redirect('account.php');
    }
}

$pageTitle = 'Register';
$pageDesc  = 'Create a Sabor Street Kitchen account to track your orders and check out faster.';
$activeNav = 'register';
$canonical = 'register.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">Join Us</span>
    <h1>Create an Account</h1>
    <p>Register to save your details and view your order history. You can still order as a guest without an account.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:560px;">
    <?php if ($errors): ?>
      <div class="form-feedback is-visible error" role="alert">
        <ul style="margin:0; padding-left:1.1em;">
          <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate class="form-grid" autocomplete="on">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />

      <div class="field">
        <label for="full_name">Full name <span class="required">*</span></label>
        <input type="text" id="full_name" name="full_name" required minlength="2" value="<?= h($values['full_name']) ?>" autocomplete="name" />
      </div>

      <div class="field">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" id="email" name="email" required value="<?= h($values['email']) ?>" autocomplete="email" />
      </div>

      <div class="field">
        <label for="phone">Phone (optional)</label>
        <input type="tel" id="phone" name="phone" value="<?= h($values['phone']) ?>" autocomplete="tel" />
      </div>

      <div class="form-row">
        <div class="field">
          <label for="password">Password <span class="required">*</span></label>
          <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" aria-describedby="password-hint" />
          <p class="field-hint" id="password-hint">At least 8 characters, with a letter and a number.</p>
        </div>
        <div class="field">
          <label for="confirm_password">Confirm password <span class="required">*</span></label>
          <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password" />
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Create Account</button>
      <p class="text-center field-hint">Already have an account? <a href="login.php">Log in</a></p>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
