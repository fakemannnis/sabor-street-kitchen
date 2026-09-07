<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

start_secure_session();
seed_demo_users();

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'account.php');
}

$errors  = [];
$email   = '';
$redirectTo = $_GET['redirect'] ?? $_POST['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try submitting the form again.';
    }

    $email    = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both your email and password.';
    }

    if (!$errors) {
        $pdo  = get_db();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Incorrect email or password.';
        } else {
            login_user($user);
            set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');

            $isSafeRedirect = $redirectTo !== ''
                && !str_starts_with($redirectTo, '//')
                && !str_contains($redirectTo, '://');
            if ($isSafeRedirect) {
                redirect($redirectTo);
            }
            redirect($user['role'] === 'admin' ? 'admin/index.php' : 'account.php');
        }
    }
}

$pageTitle = 'Log In';
$pageDesc  = 'Log in to your Sabor Street Kitchen account.';
$activeNav = 'login';
$canonical = 'login.php';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <span class="eyebrow">Welcome Back</span>
    <h1>Log In</h1>
    <p>New here? <a href="register.php">Create an account</a> instead.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:480px;">
    <?php if ($errors): ?>
      <div class="form-feedback is-visible error" role="alert">
        <ul style="margin:0; padding-left:1.1em;">
          <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate class="form-grid">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
      <input type="hidden" name="redirect" value="<?= h($redirectTo) ?>" />

      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= h($email) ?>" autocomplete="email" />
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password" />
      </div>

      <button type="submit" class="btn btn-primary btn-block">Log In</button>
    </form>

    <p class="field-hint mt-lg">
      Demo accounts:<br>
      Admin: <code>admin@sabor.example</code> / <code>Admin123!</code><br>
      Member: <code>member@sabor.example</code> / <code>Member123!</code>
    </p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
