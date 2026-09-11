<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logout_user();
start_secure_session();
set_flash('success', 'You have been logged out.');
redirect('index.php');
